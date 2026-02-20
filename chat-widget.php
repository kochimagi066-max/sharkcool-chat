<?php
/**
 * SharkCool - Floating Chat Widget Include File
 * Include this file before </body> in any page:
 * <?php include 'chat-widget.php'; ?>
 */

// Get settings for contact info
if (!isset($settings)) {
    $settings = json_decode(file_get_contents(__DIR__ . '/src/data/settings.json'), true);
}
?>

<!-- ===== SharkCool Floating Chat Widget ===== -->
<style>
    :root {
        --ig-primary-background: #FFFFFF;
        --ig-secondary-background: #FAFAFA;
        --ig-border: #DBDBDB;
        --ig-text-primary: #262626;
        --ig-text-secondary: #8E8E8E;
        --ig-blue: #0095F6;
        --ig-hover: #F5F5F5;
        --message-bg-received: #EFEFEF;
        --message-bg-sent: #0095F6;
        --sharkcool-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    /* Floating Chat Button */
    .floating-chat-button {
        position: fixed;
        bottom: 24px;
        right: 24px;
        width: 60px;
        height: 60px;
        background: var(--sharkcool-gradient);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 9998;
        transition: all 0.3s ease;
    }
    .floating-chat-button:hover { transform: scale(1.1); box-shadow: 0 6px 16px rgba(0,0,0,0.2); }
    .floating-chat-button.active { opacity: 0; pointer-events: none; }
    .chat-icon { font-size: 28px; color: white; animation: chatPulse 2s infinite; }
    @keyframes chatPulse { 0%,100%{transform:scale(1)} 50%{transform:scale(1.05)} }
    .notification-badge {
        position: absolute; top: -2px; right: -2px;
        background: #FF3B30; color: white; font-size: 10px; font-weight: 600;
        padding: 2px 6px; border-radius: 10px; min-width: 18px; text-align: center;
    }

    /* Chat Container */
    .chat-widget-container {
        position: fixed; bottom: 24px; right: 24px;
        width: 380px; height: 600px; max-height: calc(100vh - 48px);
        background: var(--ig-primary-background); border-radius: 16px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.12); z-index: 9999;
        display: none; flex-direction: column; overflow: hidden;
        animation: chatSlideUp 0.3s ease-out;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }
    .chat-widget-container.active { display: flex; }
    @keyframes chatSlideUp { from{opacity:0;transform:translateY(20px) scale(0.95)} to{opacity:1;transform:translateY(0) scale(1)} }

    /* Header */
    .chat-header {
        height: 70px; background: var(--sharkcool-gradient); color: white;
        display: flex; align-items: center; justify-content: space-between;
        padding: 0 20px; border-radius: 16px 16px 0 0;
    }
    .header-left { display: flex; align-items: center; gap: 12px; }
    .user-avatar { width:40px; height:40px; border-radius:50%; background:white; display:flex; align-items:center; justify-content:center; font-size:22px; }
    .user-details h2 { font-size:16px; font-weight:600; margin-bottom:2px; }
    .user-details p { font-size:12px; opacity:0.9; }
    .status-dot { display:inline-block; width:8px; height:8px; background:#34C759; border-radius:50%; margin-right:6px; animation:chatBlink 2s infinite; }
    @keyframes chatBlink { 0%,100%{opacity:1} 50%{opacity:0.3} }
    .header-actions { display:flex; gap:12px; }
    .header-button { width:32px; height:32px; border-radius:50%; background:rgba(255,255,255,0.2); display:flex; align-items:center; justify-content:center; cursor:pointer; transition:background 0.2s; }
    .header-button:hover { background:rgba(255,255,255,0.3); }
    .close-button { font-size:20px; color:white; font-weight:300; }

    /* Messages */
    .chat-messages { flex:1; overflow-y:auto; padding:20px; background:var(--ig-primary-background); display:flex; flex-direction:column; gap:12px; }
    .chat-messages::-webkit-scrollbar { width:6px; }
    .chat-messages::-webkit-scrollbar-thumb { background:var(--ig-border); border-radius:3px; }
    .chat-message { display:flex; animation:msgSlideIn 0.3s ease-out; }
    @keyframes msgSlideIn { from{opacity:0;transform:translateY(10px)} to{opacity:1;transform:translateY(0)} }
    .chat-message.bot { justify-content:flex-start; }
    .chat-message.user { justify-content:flex-end; }
    .message-content { max-width:75%; padding:10px 14px; border-radius:18px; font-size:14px; line-height:1.5; word-wrap:break-word; }
    .chat-message.bot .message-content { background:var(--message-bg-received); color:var(--ig-text-primary); border-radius:18px 18px 18px 4px; }
    .chat-message.user .message-content { background:var(--message-bg-sent); color:white; border-radius:18px 18px 4px 18px; }

    /* Typing */
    .typing-indicator { display:none; justify-content:flex-start; padding:8px 0; }
    .typing-indicator.active { display:flex; }
    .typing-dots { background:var(--message-bg-received); border-radius:18px; padding:12px 16px; display:flex; gap:4px; }
    .typing-dot { width:8px; height:8px; border-radius:50%; background:var(--ig-text-secondary); animation:typingAnim 1.4s infinite; }
    .typing-dot:nth-child(2){animation-delay:0.2s} .typing-dot:nth-child(3){animation-delay:0.4s}
    @keyframes typingAnim { 0%,60%,100%{transform:translateY(0);opacity:0.5} 30%{transform:translateY(-8px);opacity:1} }

    /* Welcome Screen */
    .chat-welcome { text-align:center; padding:20px; }
    .chat-welcome h3 { font-size:18px; font-weight:600; color:var(--ig-text-primary); margin-bottom:8px; }
    .chat-welcome p { font-size:13px; color:var(--ig-text-secondary); line-height:1.6; margin-bottom:20px; }
    .quick-actions { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
    .quick-action-btn { padding:14px; background:var(--ig-primary-background); border:1px solid var(--ig-border); border-radius:12px; cursor:pointer; transition:all 0.2s; text-align:center; }
    .quick-action-btn:hover { background:var(--ig-hover); border-color:var(--ig-blue); transform:translateY(-2px); }
    .quick-action-btn strong { display:block; font-size:24px; margin-bottom:6px; }
    .quick-action-btn span { display:block; font-size:12px; color:var(--ig-text-secondary); font-weight:500; }

    /* Composer */
    .chat-composer { padding:16px 20px; border-top:1px solid var(--ig-border); background:var(--ig-primary-background); display:flex; align-items:flex-end; gap:12px; }
    .composer-input-wrapper { flex:1; position:relative; }
    .composer-input { width:100%; min-height:40px; max-height:100px; padding:10px 14px; border:1px solid var(--ig-border); border-radius:20px; font-family:inherit; font-size:14px; outline:none; resize:none; overflow-y:auto; background:var(--ig-secondary-background); }
    .composer-input:focus { border-color:var(--ig-blue); background:white; }
    .composer-input::placeholder { color:var(--ig-text-secondary); }
    .send-button { width:40px; height:40px; background:var(--sharkcool-gradient); color:white; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; transition:all 0.2s; flex-shrink:0; }
    .send-button:hover { transform:scale(1.1); }
    .send-button.disabled { opacity:0.4; cursor:not-allowed; }
    .send-button.disabled:hover { transform:scale(1); }
    .chat-footer { padding:10px 20px; text-align:center; font-size:11px; color:var(--ig-text-secondary); background:var(--ig-secondary-background); border-top:1px solid var(--ig-border); }
    .chat-footer a { color:var(--ig-blue); text-decoration:none; }

    /* Mobile */
    @media (max-width: 480px) {
        .chat-widget-container { bottom:0; right:0; left:0; width:100%; height:100vh; max-height:100vh; border-radius:0; }
        .floating-chat-button { bottom:20px; right:20px; width:56px; height:56px; }
        .chat-icon { font-size:26px; }
        .chat-header { border-radius:0; }
    }
</style>

<!-- Floating Button -->
<div class="floating-chat-button" id="floatingChatBtn" onclick="openSharkChat()">
    <div class="chat-icon">💬</div>
    <div class="notification-badge">1</div>
</div>

<!-- Chat Widget -->
<div class="chat-widget-container" id="sharkChatWidget">
    <div class="chat-header">
        <div class="header-left">
            <div class="user-avatar">🦈</div>
            <div class="user-details">
                <h2>SharkCool Support</h2>
                <p><span class="status-dot"></span>Online Now</p>
            </div>
        </div>
        <div class="header-actions">
            <div class="header-button" onclick="window.open('tel:<?= $settings['contact']['phone']['primary'] ?>')">
                <svg width="18" height="18" fill="white" viewBox="0 0 24 24"><path d="M18.227 22.912c-4.913 0-9.286-3.627-11.486-5.828C4.486 14.83.731 10.291.921 5.231a3.289 3.289 0 0 1 .908-2.138 17.116 17.116 0 0 1 1.865-1.71 2.307 2.307 0 0 1 3.004.174 13.283 13.283 0 0 1 3.658 5.325 2.551 2.551 0 0 1-.19 1.941l-.455.853a.463.463 0 0 0-.024.387 7.57 7.57 0 0 0 4.077 4.075.455.455 0 0 0 .386-.024l.853-.455a2.548 2.548 0 0 1 1.94-.19 13.278 13.278 0 0 1 5.326 3.658 2.309 2.309 0 0 1 .174 3.003 17.319 17.319 0 0 1-1.71 1.866 3.29 3.29 0 0 1-2.138.91 10.27 10.27 0 0 1-.368-.006Z"/></svg>
            </div>
            <div class="header-button close-button" onclick="closeSharkChat()">×</div>
        </div>
    </div>

    <div class="chat-messages" id="sharkChatMessages">
        <div class="chat-welcome" id="sharkWelcome">
            <h3>👋 Welcome to SharkCool!</h3>
            <p>Hi! I'm your AI assistant. How can I help you today?</p>
            <div class="quick-actions">
                <div class="quick-action-btn" onclick="sharkQuickMsg('My AC is not cooling properly')"><strong>❄️</strong><span>AC Repair</span></div>
                <div class="quick-action-btn" onclick="sharkQuickMsg('My washing machine is making noise')"><strong>🧺</strong><span>Washing Machine</span></div>
                <div class="quick-action-btn" onclick="sharkQuickMsg('Refrigerator not cooling')"><strong>🧊</strong><span>Refrigerator</span></div>
                <div class="quick-action-btn" onclick="sharkQuickMsg('I need installation service')"><strong>🔧</strong><span>Installation</span></div>
            </div>
        </div>
        <div class="typing-indicator" id="sharkTyping">
            <div class="typing-dots">
                <span class="typing-dot"></span>
                <span class="typing-dot"></span>
                <span class="typing-dot"></span>
            </div>
        </div>
    </div>

    <div class="chat-composer">
        <div class="composer-input-wrapper">
            <textarea class="composer-input" id="sharkMsgInput" placeholder="Type a message..." rows="1" autocomplete="off"></textarea>
        </div>
        <div class="send-button disabled" id="sharkSendBtn" onclick="sharkSendMessage()">
            <span style="font-size:18px">➤</span>
        </div>
    </div>

    <div class="chat-footer">Powered by <a href="https://www.sharkcool.in" target="_blank">SharkCool</a></div>
</div>

<script>
(function() {
    const API_URL = '/api/chat-handler.php';
    const widget = document.getElementById('sharkChatWidget');
    const floatBtn = document.getElementById('floatingChatBtn');
    const msgs = document.getElementById('sharkChatMessages');
    const input = document.getElementById('sharkMsgInput');
    const sendBtn = document.getElementById('sharkSendBtn');
    const typing = document.getElementById('sharkTyping');

    window.openSharkChat = function() {
        widget.classList.add('active');
        floatBtn.classList.add('active');
        input.focus();
        const badge = floatBtn.querySelector('.notification-badge');
        if (badge) setTimeout(() => badge.style.display = 'none', 300);
    };

    window.closeSharkChat = function() {
        widget.classList.remove('active');
        floatBtn.classList.remove('active');
    };

    window.sharkQuickMsg = function(message) {
        input.value = message;
        sharkSendMessage();
    };

    input.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 100) + 'px';
        sendBtn.classList.toggle('disabled', !this.value.trim());
    });

    input.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sharkSendMessage(); }
    });

    window.sharkSendMessage = async function() {
        const message = input.value.trim();
        if (!message) return;

        const welcome = document.getElementById('sharkWelcome');
        if (welcome) welcome.style.display = 'none';

        addMsg('user', message);
        input.value = ''; input.style.height = 'auto';
        sendBtn.classList.add('disabled');
        typing.classList.add('active');
        scrollBottom();

        try {
            const res = await fetch(API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ chatInput: message, sessionId: getSession() })
            });
            const data = await res.json();
            typing.classList.remove('active');
            const reply = data.output || data.response || "Sorry, can't connect. Please call <?= $settings['contact']['phone']['primary'] ?>.";
            addMsg('bot', reply);
            if (data.supportAgent) {
                const h2 = widget.querySelector('.user-details h2');
                if (h2) h2.textContent = data.supportAgent;
            }
        } catch(e) {
            typing.classList.remove('active');
            addMsg('bot', "Connection error. Please call <?= $settings['contact']['phone']['primary'] ?>.");
        }
        input.focus();
    };

    function addMsg(type, text) {
        const div = document.createElement('div');
        div.className = 'chat-message ' + type;
        const c = document.createElement('div');
        c.className = 'message-content';
        c.innerHTML = formatMsg(text);
        div.appendChild(c);
        msgs.insertBefore(div, typing);
        scrollBottom();
    }

    function formatMsg(t) {
        t = t.replace(/\n/g, '<br>');
        t = t.replace(/(https?:\/\/[^\s]+)/g, '<a href="$1" target="_blank" style="color:inherit;text-decoration:underline">$1</a>');
        t = t.replace(/(\d{10})/g, '<a href="tel:$1" style="color:inherit;font-weight:600">$1</a>');
        t = t.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
        return t;
    }

    function scrollBottom() { setTimeout(() => msgs.scrollTop = msgs.scrollHeight, 100); }

    function getSession() {
        let s = sessionStorage.getItem('sc_sid');
        if (!s) { s = 'sc_' + Date.now() + '_' + Math.random().toString(36).substr(2,9); sessionStorage.setItem('sc_sid', s); }
        return s;
    }

    document.addEventListener('keydown', e => { if (e.key === 'Escape' && widget.classList.contains('active')) closeSharkChat(); });
})();
</script>
<!-- ===== End SharkCool Chat Widget ===== -->
