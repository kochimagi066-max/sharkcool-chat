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
            'key' => 'AIzGi_aaNZATjE',
            'name' => 'SharkCool Support 1',
            'priority' => 1,
            'active' => true
        ],
        [
            'key' => 'AIzaaaKt3ssv7P0u-uZy8NY',
            'name' => 'SharkCool Support 2',
            'priority' => 2,
            'active' => true
        ],
        [
            'key' => 'AIzaSyCiQXsy4',
            'name' => 'SharkCool Support 3',
            'priority' => 3,
            'active' => true
        ],
        [
            'key' => 'AIzaSyzKALxGjTCE',
            'name' => 'SharkCool Support 4',
            'priority' => 4,
            'active' => true
        ],
        [
            'key' => 'AIzaSZfDlN0',
            'name' => 'SharkCool Support 5',
            'priority' => 5,
            'active' => true
        ],
        [
            'key' => 'AIzaSyOm4Lv-p8',
            'name' => 'SharkCool Support 6',
            'priority' => 6,
            'active' => true
        ],
        [
            'key' => 'AIzaSyBH7Kjtj21Q',
            'name' => 'SharkCool Support 7',
            'priority' => 7,
            'active' => true
        ],
        [
            'key' => 'AIzaSyAygDhNM6sBneV0',
            'name' => 'SharkCool Support 8',
            'priority' => 8,
            'active' => true
        ],
        [
            'key' => 'AIzaSyBlhuuwB3Ug',
            'name' => 'SharkCool Support 9',
            'priority' => 9,
            'active' => true
        ],
        [
             'key' => 'AIzasJcVM',
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
