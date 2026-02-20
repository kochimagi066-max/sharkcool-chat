# 🦈 SharkCool AI Chat Widget

> AI-powered floating chat widget for home appliance repair booking — built for [sharkcool.in](https://www.sharkcool.in)

![Status](https://img.shields.io/badge/Status-Live-brightgreen)
![PHP](https://img.shields.io/badge/PHP-7.4+-blue)
![AI](https://img.shields.io/badge/AI-Google%20Gemini-orange)
![License](https://img.shields.io/badge/License-MIT-yellow)

---

## 📋 Overview

SharkCool Chat is a fully-featured AI-powered customer support chat widget for home appliance repair services in Kolkata. It uses Google Gemini AI with a 14-key fallback system to ensure 24/7 uptime, and collects customer booking details automatically.

**Live Site:** https://www.sharkcool.in  
**Business:** SharkCool — Home Appliance Repair, Kolkata  
**Phone:** 6291228492  
**Hours:** Monday – Sunday, 9:00 AM – 8:00 PM  

---

## 🗂️ File Structure

```
sharkcool-chat/
│
├── chat.php                    # Standalone chat page (full page)
├── chat-widget.php             # Embeddable widget (include in any page)
│
├── api/
│   └── chat-handler.php        # Backend: Gemini AI API handler
│
├── config/
│   └── chat-config.php         # 14 Gemini API keys + all settings
│
└── src/data/
    ├── settings.json           # Site contact info & business settings
    ├── services.json           # Services & pricing packages
    ├── bookings.json           # Customer booking records
    └── faqs.json               # Frequently asked questions
```

---

## ✨ Features

| Feature | Details |
|---|---|
| 🤖 AI Responses | Google Gemini 2.5 Flash |
| 🔄 Fallback System | 14 API keys, auto-rotates on failure |
| 💬 UI Style | Instagram-inspired modern design |
| 📱 Responsive | Full-screen on mobile, 380×600px on desktop |
| ⚡ Quick Actions | AC Repair, Washing Machine, Fridge, Installation |
| 📞 Call Button | Direct phone call from chat header |
| 💾 Session Memory | Conversation history per browser session |
| 📅 Booking System | Auto-extracts and saves booking details |
| ⌨️ Typing Indicator | Animated 3-dot typing animation |
| 🎨 Branding | SharkCool purple gradient throughout |

---

## 🤖 AI Configuration

### Model
- **Model:** `gemini-2.5-flash`
- **API URL:** `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent`

### Fallback System
```
14 API Keys → Priority 1 → 3 retry attempts → 500ms delay
                         ↓ (if fails)
              Priority 2 → 3 retry attempts → 500ms delay
                         ↓ (if fails)
              ... continues through all 14 keys ...
                         ↓ (if ALL fail)
              Show fallback message with phone number
```

### Settings
```php
'api_retry_attempts'   => 3       // retries per key
'api_retry_delay_ms'   => 500     // ms between retries  
'api_timeout_seconds'  => 10      // timeout per request
'max_history_messages' => 20      // conversation context length
```

---

## 🔄 How It Works

```
1. User clicks 💬 floating button
2. Chat widget opens with welcome screen
3. User picks quick action OR types message
4. POST request → /api/chat-handler.php
5. Handler loads conversation history (session-based)
6. Gemini AI generates response using SharkCool system prompt
7. Response returned → displayed in chat
8. If booking complete → saved to bookings.json
9. Conversation saved to /src/data/conversations/{sessionId}.json
```

---

## 🎨 UI Design

### Color Scheme
```css
--sharkcool-gradient:    linear-gradient(135deg, #667eea 0%, #764ba2 100%)
--ig-primary-background: #FFFFFF
--ig-secondary-background: #FAFAFA
--ig-border:             #DBDBDB
--ig-text-primary:       #262626
--ig-text-secondary:     #8E8E8E
--ig-blue:               #0095F6
--message-bg-received:   #EFEFEF
--message-bg-sent:       #0095F6
```

### Responsive Breakpoints
| Screen | Widget Size | Button Size |
|---|---|---|
| Desktop (>480px) | 380×600px fixed | 60×60px |
| Mobile (≤480px) | 100vw × 100vh | 56×56px |

---

## 📡 API Reference

### Endpoint
```
POST /api/chat-handler.php
Content-Type: application/json
```

### Request
```json
{
    "chatInput": "My AC is not cooling",
    "sessionId": "sc_1739734800_abc123xyz"
}
```

### Response
```json
{
    "output": "I can help with that! Could you tell me your name and location first?",
    "supportAgent": "SharkCool Support 1",
    "sessionId": "sc_1739734800_abc123xyz",
    "success": true,
    "apiUsed": "AIzaSy..."
}
```

### Error Response
```json
{
    "error": "AI service error",
    "output": "Please call us at 6291228492 for assistance."
}
```

---

## 🔧 JavaScript Functions

| Function | Description |
|---|---|
| `openSharkChat()` | Opens widget, hides float button |
| `closeSharkChat()` | Closes widget, shows float button |
| `sharkSendMessage()` | Sends message to AI backend |
| `sharkQuickMsg(msg)` | Sends a predefined quick message |
| `addMsg(type, text)` | Adds message bubble to UI |
| `formatMsg(text)` | Formats links, phones, bold text |
| `scrollBottom()` | Auto-scrolls to latest message |
| `getSession()` | Gets/creates browser session ID |

---

## 💾 Data Storage

### Conversations
- **Path:** `/src/data/conversations/{sessionId}.json`
- **Lifetime:** 24 hours
- **Content:** Full message history with timestamps

### Bookings
- **Path:** `/src/data/bookings.json`
- **Triggered by:** AI saying `BOOKING_COMPLETE` in response
- **Contains:** Name, phone, email, location, appliance, date, time

### API Usage Log
- **Path:** `/src/data/api-usage.json`
- **Tracks:** Which key used, success/fail, attempt number
- **Limit:** Last 1000 entries kept

---

## 🚀 Installation

### 1. Include Widget in Any Page
```php
<!-- Before closing </body> tag -->
<?php include 'chat-widget.php'; ?>
```

### 2. Configure API Keys
Edit `config/chat-config.php` and add your Gemini API keys:
```php
'gemini_api_keys' => [
    [
        'key'      => 'YOUR_GEMINI_API_KEY',
        'name'     => 'Support Agent 1',
        'priority' => 1,
        'active'   => true
    ],
    // Add more keys for fallback...
]
```

### 3. Create Required Directories
```bash
mkdir src/data/conversations
chmod 755 src/data/conversations
chmod 644 src/data/bookings.json
```

### 4. Update Business Info
Edit `config/chat-config.php`:
```php
'business_name'    => 'Your Business Name',
'business_phone'   => 'Your Phone Number',
'business_email'   => 'your@email.com',
'business_website' => 'https://yourwebsite.com',
'service_area'     => 'Your City',
'service_hours'    => 'Mon-Sun: 9AM - 8PM',
```

---

## 🤖 AI Booking Flow

The AI assistant collects information in this order:

```
Step 1: Understand the appliance issue
Step 2: Collect customer name
Step 3: Collect phone number (10 digits, starts with 6-9)
Step 4: Collect email address
Step 5: Collect location/area
Step 6: Recommend service package with pricing
Step 7: Ask preferred date & time slot
        Morning (9AM-12PM) / Afternoon (12PM-4PM) / Evening (4PM-8PM)
Step 8: Confirm all details → Save booking → Say BOOKING_COMPLETE
```

---

## 🛠️ Troubleshooting

| Problem | Solution |
|---|---|
| Chat not opening | Check browser console for JS errors |
| AI not responding | Check API key validity in chat-config.php |
| Empty responses | Verify Gemini model URL is correct |
| Bookings not saving | Check write permissions on src/data/ |
| Mobile layout broken | Clear browser cache |

---

## 📊 Services Covered

| Service | Starting Price |
|---|---|
| AC Repair | ₹250 |
| Washing Machine Repair | ₹250 |
| Refrigerator Repair | ₹250 |
| TV Repair | ₹300 |
| Kitchen Chimney Cleaning | ₹499 |
| Water Purifier Service | ₹299 |
| Geyser Repair | ₹349 |
| Microwave Repair | ₹299 |

---

## 📝 Quick Actions

Pre-configured quick message buttons shown on welcome screen:

| Button | Message Sent |
|---|---|
| ❄️ AC Repair | "My AC is not cooling properly" |
| 🧺 Washing Machine | "My washing machine is making noise" |
| 🧊 Refrigerator | "Refrigerator not cooling" |
| 🔧 Installation | "I need installation service" |

---

## 📅 Version History

| Date | Update |
|---|---|
| Feb 20, 2026 | Initial GitHub release — chat system complete |
| Feb 16, 2026 | 14-key fallback system implemented |
| Feb 05, 2026 | Multi-API fallback added (8 keys) |
| Jan 2026 | Initial chat widget created |

---

## 📄 License

MIT License — Free to use and modify.

---

*Built with ❤️ for SharkCool — Kolkata's trusted home appliance repair service*
