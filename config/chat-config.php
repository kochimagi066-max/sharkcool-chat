<?php
/**
 * SharkCool AI Chat Configuration
 * Multi-API Key Fallback System
 * Total: 14 API Keys with Priority-Based Rotation
 */

return [
    // Google Gemini API Configuration - MULTIPLE KEYS WITH FALLBACK
    'gemini_api_keys' => [
        [
            'key' => 'AIzaSyC_4U2sYM89QVPZClnC0TrPGi_aaNZATjE',
            'name' => 'SharkCool Support 1',
            'priority' => 1,
            'active' => true
        ],
        [
            'key' => 'AIzaSyC3fzi2TdlFiHuSaaKt3ssv7P0u-uZy8NY',
            'name' => 'SharkCool Support 2',
            'priority' => 2,
            'active' => true
        ],
        [
            'key' => 'AIzaSyCyfwSJJuDWHt5lLMlaWj-_34qEIiQXsy4',
            'name' => 'SharkCool Support 3',
            'priority' => 3,
            'active' => true
        ],
        [
            'key' => 'AIzaSyARtBeX8Dxaryd97pU8Mfab2zKALxGjTCE',
            'name' => 'SharkCool Support 4',
            'priority' => 4,
            'active' => true
        ],
        [
            'key' => 'AIzaSyD3y-Isjlusdub9fHvMk1Gi_7aPFZfDlN0',
            'name' => 'SharkCool Support 5',
            'priority' => 5,
            'active' => true
        ],
        [
            'key' => 'AIzaSyC0mmNzSeHL7ha93uCFn3lwPpjOm4Lv-p8',
            'name' => 'SharkCool Support 6',
            'priority' => 6,
            'active' => true
        ],
        [
            'key' => 'AIzaSyB6sp9KbgCoirHeNpnFtrkxE6H7Kjtj21Q',
            'name' => 'SharkCool Support 7',
            'priority' => 7,
            'active' => true
        ],
        [
            'key' => 'AIzaSyAygoMOI2ZaP-T2cz3h8oQ-DhNM6sBneV0',
            'name' => 'SharkCool Support 8',
            'priority' => 8,
            'active' => true
        ],
        [
            'key' => 'AIzaSyCrmQEg0UgsSVvKp5CAMHR54BlhuuwB3Ug',
            'name' => 'SharkCool Support 9',
            'priority' => 9,
            'active' => true
        ],
        [
            'key' => 'AIzaSyAMCXJzBekP_laABrxXmj1D1JPxMZ4DveY',
            'name' => 'SharkCool Support 10',
            'priority' => 10,
            'active' => true
        ],
        [
            'key' => 'AIzaSyCdsBFlEiN9YWKqUjVcHuz37M4vUTAVAdY',
            'name' => 'SharkCool Support 11',
            'priority' => 11,
            'active' => true
        ],
        [
            'key' => 'AIzaSyANHCrTw8JD-Jo8pBaDRrhQWkR0ojNJMtw',
            'name' => 'SharkCool Support 12',
            'priority' => 12,
            'active' => true
        ],
        [
            'key' => 'AIzaSyC62VxeEfdtkDmjwaTbdb3ox4yMQwHzPtk',
            'name' => 'SharkCool Support 13',
            'priority' => 13,
            'active' => true
        ],
        [
            'key' => 'AIzaSyAoYLkbQjxYYmBCrIeQ3WqNy5qM01sJcVM',
            'name' => 'SharkCool Support 14',
            'priority' => 14,
            'active' => true
        ]
    ],
    
    // API Retry Settings
    'api_retry_attempts' => 3, // Try 3 times per API key
    'api_retry_delay_ms' => 500, // Wait 500ms between retries
    'api_timeout_seconds' => 10, // Timeout after 10 seconds
    
    // Gemini Model Configuration
    'gemini_model' => 'gemini-2.5-flash',
    'gemini_api_url' => 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent',
    
    // Conversation Settings
    'max_history_messages' => 20,
    'conversation_timeout_hours' => 24,
    
    // File Paths
    'conversations_dir' => __DIR__ . '/../src/data/conversations',
    'bookings_file' => __DIR__ . '/../src/data/bookings.json',
    'api_usage_log' => __DIR__ . '/../src/data/api-usage.json',
    
    // Business Info
    'business_name' => 'SharkCool',
    'business_phone' => '6291228492',
    'business_email' => 'info@quick24service.com',
    'business_website' => 'https://www.sharkcool.in',
    
    // Service Areas
    'service_area' => 'Kolkata',
    'service_hours' => 'Monday - Sunday: 9:00 AM - 8:00 PM',
];
