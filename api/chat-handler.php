<?php
/**
 * SharkCool AI Chat Handler
 * Multi-API Key Fallback System
 * Handles chat requests using Google Gemini API with automatic failover
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display to user
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../src/data/chat-errors.log');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Load configuration
$config = require_once __DIR__ . '/../config/chat-config.php';

// Get request data
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['chatInput']) || !isset($input['sessionId'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields: chatInput and sessionId']);
    exit();
}

$userMessage = trim($input['chatInput']);
$sessionId = $input['sessionId'];

if (empty($userMessage)) {
    http_response_code(400);
    echo json_encode(['error' => 'Empty message']);
    exit();
}

// Load or create conversation
$conversationFile = $config['conversations_dir'] . '/' . $sessionId . '.json';
$conversation = [];

if (file_exists($conversationFile)) {
    $conversation = json_decode(file_get_contents($conversationFile), true) ?? [];
}

// Add user message to conversation
$conversation[] = [
    'role' => 'user',
    'content' => $userMessage,
    'timestamp' => date('Y-m-d H:i:s')
];

// Keep only last N messages to prevent context overflow
if (count($conversation) > $config['max_history_messages'] * 2) {
    $conversation = array_slice($conversation, -($config['max_history_messages'] * 2));
}

// Build Gemini API request
$systemPrompt = getSystemPrompt($config);
$geminiMessages = buildGeminiMessages($conversation, $systemPrompt);

// Call Gemini API with fallback system
try {
    $apiResult = callGeminiAPIWithFallback($config, $geminiMessages);
    
    if (!$apiResult['response']) {
        throw new Exception('Empty response from all API keys');
    }
    
    // Add assistant response to conversation
    $conversation[] = [
        'role' => 'assistant',
        'content' => $apiResult['response'],
        'timestamp' => date('Y-m-d H:i:s'),
        'api_used' => $apiResult['api_name']
    ];
    
    // Save conversation
    file_put_contents($conversationFile, json_encode($conversation, JSON_PRETTY_PRINT));
    
    // Check if booking is complete and save
    checkAndSaveBooking($conversation, $config);
    
    // Return response with API info
    echo json_encode([
        'output' => $apiResult['response'],
        'sessionId' => $sessionId,
        'success' => true,
        'apiUsed' => $apiResult['api_name'],
        'supportAgent' => $apiResult['agent_name']
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'AI service error: ' . $e->getMessage(),
        'output' => "I apologize, but I'm having trouble processing your request right now. Please call us at {$config['business_phone']} for immediate assistance.",
        'sessionId' => $sessionId
    ]);
}

/**
 * Call Gemini API with automatic fallback to backup keys
 * Returns: ['response' => string, 'api_name' => string, 'agent_name' => string]
 */
function callGeminiAPIWithFallback($config, $messages) {
    $apiKeys = $config['gemini_api_keys'];
    $retryAttempts = $config['api_retry_attempts'];
    $retryDelay = $config['api_retry_delay_ms'];
    
    // Sort by priority
    usort($apiKeys, function($a, $b) {
        return $a['priority'] - $b['priority'];
    });
    
    // Try each API key in order
    foreach ($apiKeys as $apiConfig) {
        if (!$apiConfig['active']) {
            continue;
        }
        
        $apiKey = $apiConfig['key'];
        $apiName = $apiConfig['name'];
        
        // Try multiple times per key
        for ($attempt = 1; $attempt <= $retryAttempts; $attempt++) {
            try {
                $response = callGeminiAPI($config, $messages, $apiKey);
                
                if ($response) {
                    // Log successful API usage
                    logAPIUsage($config, $apiKey, $apiName, true, $attempt);
                    
                    return [
                        'response' => $response,
                        'api_name' => $apiKey,
                        'agent_name' => $apiName
                    ];
                }
            } catch (Exception $e) {
                // Log failed attempt
                logAPIUsage($config, $apiKey, $apiName, false, $attempt, $e->getMessage());
                
                // Wait before retry
                if ($attempt < $retryAttempts) {
                    usleep($retryDelay * 1000); // Convert ms to microseconds
                }
            }
        }
    }
    
    // All API keys failed
    throw new Exception('All API keys failed after multiple retry attempts');
}

/**
 * Call Gemini API with specific key
 */
function callGeminiAPI($config, $messages, $apiKey) {
    $url = $config['gemini_api_url'] . '?key=' . $apiKey;
    
    $payload = [
        'contents' => $messages,
        'generationConfig' => [
            'temperature' => 0.7,
            'topK' => 40,
            'topP' => 0.95,
            'maxOutputTokens' => 1024,
        ],
        'safetySettings' => [
            [
                'category' => 'HARM_CATEGORY_HARASSMENT',
                'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
            ],
            [
                'category' => 'HARM_CATEGORY_HATE_SPEECH',
                'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
            ],
            [
                'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
            ],
            [
                'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
            ]
        ]
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, $config['api_timeout_seconds']);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        throw new Exception("cURL error: $error");
    }
    
    if ($httpCode !== 200) {
        throw new Exception("API returned status code: $httpCode");
    }
    
    $data = json_decode($response, true);
    
    if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
        throw new Exception("Invalid API response format");
    }
    
    return $data['candidates'][0]['content']['parts'][0]['text'];
}

/**
 * Log API usage for monitoring
 */
function logAPIUsage($config, $apiKey, $apiName, $success, $attempt, $error = null) {
    $logFile = $config['api_usage_log'];
    
    // Create directory if doesn't exist
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    // Load existing log
    $logs = [];
    if (file_exists($logFile)) {
        $logs = json_decode(file_get_contents($logFile), true) ?? [];
    }
    
    // Add new log entry
    $logs[] = [
        'timestamp' => date('Y-m-d H:i:s'),
        'api_key' => substr($apiKey, 0, 10) . '...' . substr($apiKey, -5), // Mask key
        'api_name' => $apiName,
        'success' => $success,
        'attempt' => $attempt,
        'error' => $error
    ];
    
    // Keep only last 1000 entries
    if (count($logs) > 1000) {
        $logs = array_slice($logs, -1000);
    }
    
    // Save log
    file_put_contents($logFile, json_encode($logs, JSON_PRETTY_PRINT));
}


/**
 * Get system prompt for SharkCool booking assistant
 */
function getSystemPrompt($config) {
    // Load services from JSON
    $servicesData = loadServicesData();
    $servicesText = buildServicesText($servicesData);
    
    return "You are SharkCool's professional appointment booking assistant for home appliance repair services in {$config['service_area']}.

YOUR GOAL:
Collect accurate customer information, understand their appliance issue, suggest appropriate service packages, and help schedule appointments.

ALWAYS respond in a friendly, helpful, and professional tone.
Use step-by-step questioning — never overwhelm customers with too many questions at once.

INFORMATION TO COLLECT (one at a time):
1. Customer Details: Name, Phone (10 digits), Email, Location/Area
2. Service Details: Appliance Type, Brand (if known), Issue Description, Service Type needed
3. Appointment: Preferred Date, Time Slot (Morning 9AM-12PM, Afternoon 12PM-4PM, Evening 4PM-8PM)

AVAILABLE SERVICES AND PACKAGES:
{$servicesText}

BOOKING WORKFLOW:
1. Greet and understand the appliance issue
2. Collect customer details (one question at a time)
3. Recommend service package with pricing
4. Ask for preferred appointment date and time
5. Confirm all details
6. Provide booking confirmation

VALIDATION:
- Phone: 10 digits starting with 6, 7, 8, or 9
- Email: Must contain @ and valid domain
- Location: Must be in Kolkata area

CONTACT INFO:
Phone: {$config['business_phone']}
Email: {$config['business_email']}
Website: {$config['business_website']}
Service Hours: {$config['service_hours']}

RESPONSE STYLE:
- Professional yet friendly
- Concise and clear
- Simple language
- Always confirm understanding
- Show empathy

When all information is collected, say: 'BOOKING_COMPLETE' at the end of your message.";
}

/**
 * Load services data from JSON
 */
function loadServicesData() {
    $servicesFile = __DIR__ . '/../src/data/services.json';
    
    if (!file_exists($servicesFile)) {
        return [];
    }
    
    $json = file_get_contents($servicesFile);
    $data = json_decode($json, true);
    
    return is_array($data) ? $data : [];
}

/**
 * Build services text from services data
 */
function buildServicesText($servicesData) {
    if (empty($servicesData)) {
        return "No services available at the moment.";
    }
    
    $text = [];
    
    foreach ($servicesData as $service) {
        $serviceName = strtoupper($service['name'] ?? 'Unknown Service');
        $packages = $service['packages'] ?? [];
        
        if (empty($packages)) {
            continue;
        }
        
        $packageList = [];
        foreach ($packages as $pkg) {
            $name = $pkg['name'] ?? 'Package';
            $price = $pkg['price'] ?? 0;
            $originalPrice = $pkg['originalPrice'] ?? $price;
            
            // Show discount if applicable
            if ($originalPrice > $price) {
                $packageList[] = "  • {$name}: ₹{$price} (was ₹{$originalPrice})";
            } else {
                $packageList[] = "  • {$name}: ₹{$price}";
            }
        }
        
        $text[] = "{$serviceName}:\n" . implode("\n", $packageList);
    }
    
    return implode("\n\n", $text);
}

/**
 * Build Gemini API messages format
 */
function buildGeminiMessages($conversation, $systemPrompt) {
    $contents = [];
    
    // Add system context as first user message
    $contents[] = [
        'role' => 'user',
        'parts' => [['text' => $systemPrompt]]
    ];
    
    $contents[] = [
        'role' => 'model',
        'parts' => [['text' => 'Understood. I am SharkCool\'s booking assistant. I\'ll help customers book appointments professionally and collect all necessary information step by step.']]
    ];
    
    // Add conversation history
    foreach ($conversation as $msg) {
        $role = $msg['role'] === 'user' ? 'user' : 'model';
        $contents[] = [
            'role' => $role,
            'parts' => [['text' => $msg['content']]]
        ];
    }
    
    return $contents;
}

/**
 * Check if booking is complete and save it
 */
function checkAndSaveBooking($conversation, $config) {
    // Get last assistant message
    $lastMessage = end($conversation);
    if ($lastMessage['role'] !== 'assistant') {
        return;
    }
    
    // Check if booking is marked as complete
    if (strpos($lastMessage['content'], 'BOOKING_COMPLETE') === false) {
        return;
    }
    
    // Extract booking information from conversation
    $bookingData = extractBookingData($conversation);
    
    if (empty($bookingData)) {
        return;
    }
    
    // Add booking metadata
    $bookingData['booking_id'] = 'SCB-' . date('Ymd-His') . '-' . substr(md5(uniqid()), 0, 6);
    $bookingData['timestamp'] = date('Y-m-d H:i:s');
    $bookingData['status'] = 'Confirmed';
    $bookingData['source'] = 'Chat Widget';
    $bookingData['api_used'] = $lastMessage['api_used'] ?? 'Unknown';
    
    // Load existing bookings
    $bookingsFile = $config['bookings_file'];
    $bookings = [];
    
    if (file_exists($bookingsFile)) {
        $bookings = json_decode(file_get_contents($bookingsFile), true) ?? [];
    }
    
    // Add new booking
    $bookings[] = $bookingData;
    
    // Save bookings
    file_put_contents($bookingsFile, json_encode($bookings, JSON_PRETTY_PRINT));
}

/**
 * Extract booking data from conversation
 */
function extractBookingData($conversation) {
    $data = [
        'customer_name' => '',
        'phone' => '',
        'email' => '',
        'location' => '',
        'appliance_type' => '',
        'brand' => '',
        'issue' => '',
        'service_type' => '',
        'preferred_date' => '',
        'preferred_time' => '',
        'conversation_summary' => ''
    ];
    
    // Build conversation summary
    $summary = [];
    foreach ($conversation as $msg) {
        $role = ucfirst($msg['role']);
        $summary[] = "$role: {$msg['content']}";
    }
    $data['conversation_summary'] = implode("\n\n", $summary);
    
    // Try to extract key information using simple pattern matching
    $fullText = strtolower(implode(' ', array_column($conversation, 'content')));
    
    // Extract phone number (10 digits)
    if (preg_match('/\b([6-9]\d{9})\b/', $fullText, $matches)) {
        $data['phone'] = $matches[1];
    }
    
    // Extract email
    if (preg_match('/\b[\w\.-]+@[\w\.-]+\.\w+\b/', $fullText, $matches)) {
        $data['email'] = $matches[0];
    }
    
    // Extract appliance type
    $appliances = ['ac', 'refrigerator', 'fridge', 'washing machine', 'microwave', 'chimney', 'water purifier', 'geyser', 'tv', 'dishwasher', 'induction'];
    foreach ($appliances as $appliance) {
        if (strpos($fullText, $appliance) !== false) {
            $data['appliance_type'] = ucwords($appliance);
            break;
        }
    }
    
    return $data;
}
