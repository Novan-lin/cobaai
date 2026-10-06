<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Chat AI') }} - NVIDIA NIM AI</title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">
    
    <!-- Markdown & Highlight.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark-dimmed.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>

    <style>
        :root {
            --bg-body: #0f172a;
            --bg-sidebar: #1e293b;
            --bg-chat: #0f172a;
            --bg-card: #1e293b;
            --bg-user-bubble: #2563eb;
            --bg-ai-bubble: #1e293b;
            --border-color: #334155;
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            --accent: #38bdf8;
            --accent-hover: #0ea5e9;
            --danger: #ef4444;
            --font-sans: 'Inter', system-ui, -apple-system, sans-serif;
            --font-mono: 'Fira Code', monospace;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: var(--font-sans);
            background-color: var(--bg-body);
            color: var(--text-primary);
            height: 100vh;
            display: flex;
            overflow: hidden;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background-color: var(--bg-sidebar);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease;
            z-index: 40;
        }

        .sidebar-header {
            padding: 18px 16px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: var(--text-primary);
            font-weight: 600;
            font-size: 1.05rem;
        }

        .brand-icon {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, #0284c7, #38bdf8);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: 0.95rem;
        }

        .btn-new-chat {
            margin: 14px 16px;
            padding: 10px 14px;
            background-color: rgba(56, 189, 248, 0.1);
            color: var(--accent);
            border: 1px dashed rgba(56, 189, 248, 0.4);
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.9rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .btn-new-chat:hover {
            background-color: rgba(56, 189, 248, 0.2);
            border-color: var(--accent);
        }

        .conversation-list {
            flex: 1;
            overflow-y: auto;
            padding: 8px 12px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .conversation-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 9px 12px;
            border-radius: 6px;
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.875rem;
            transition: background 0.15s ease, color 0.15s ease;
            position: relative;
        }

        .conversation-item:hover, .conversation-item.active {
            background-color: rgba(255, 255, 255, 0.06);
            color: var(--text-primary);
        }

        .conversation-item.active {
            border-left: 3px solid var(--accent);
            background-color: rgba(56, 189, 248, 0.08);
            font-weight: 500;
        }

        .conversation-title {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 190px;
        }

        .btn-delete-conv {
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 4px;
            border-radius: 4px;
            opacity: 0;
            transition: opacity 0.2s ease, color 0.2s ease;
        }

        .conversation-item:hover .btn-delete-conv {
            opacity: 1;
        }

        .btn-delete-conv:hover {
            color: var(--danger);
        }

        .sidebar-footer {
            padding: 14px 16px;
            border-top: 1px solid var(--border-color);
            font-size: 0.75rem;
            color: var(--text-muted);
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        /* Main Chat Area */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            background-color: var(--bg-chat);
            position: relative;
            height: 100vh;
        }

        .chat-header {
            padding: 12px 20px;
            border-bottom: 1px solid var(--border-color);
            background-color: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 10;
        }

        .chat-header-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn-menu-toggle {
            display: none;
            background: none;
            border: none;
            color: var(--text-primary);
            cursor: pointer;
            padding: 6px;
        }

        .chat-title-info h2 {
            font-size: 0.95rem;
            font-weight: 600;
        }

        .chat-title-info span {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .chat-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .model-select {
            background-color: var(--bg-card);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 6px 10px;
            font-size: 0.8rem;
            font-family: var(--font-sans);
            outline: none;
            cursor: pointer;
            transition: border-color 0.15s ease;
        }

        .model-select:focus {
            border-color: var(--accent);
        }

        .btn-icon-action {
            background: none;
            border: 1px solid var(--border-color);
            color: var(--text-secondary);
            border-radius: 6px;
            padding: 6px 10px;
            font-size: 0.8rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.15s ease;
        }

        .btn-icon-action:hover {
            color: var(--text-primary);
            border-color: var(--text-muted);
            background-color: rgba(255, 255, 255, 0.05);
        }

        /* Messages Area */
        .messages-container {
            flex: 1;
            overflow-y: auto;
            padding: 24px 20px;
            display: flex;
            flex-direction: column;
            gap: 20px;
            scroll-behavior: smooth;
        }

        .welcome-screen {
            margin: auto;
            max-width: 580px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 16px;
        }

        .welcome-avatar {
            width: 52px;
            height: 52px;
            background: linear-gradient(135deg, #0ea5e9, #38bdf8);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(14, 165, 233, 0.3);
        }

        .welcome-title {
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .welcome-desc {
            font-size: 0.9rem;
            color: var(--text-secondary);
            line-height: 1.5;
        }

        .prompt-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            width: 100%;
            margin-top: 12px;
        }

        .prompt-btn {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 12px;
            text-align: left;
            color: var(--text-secondary);
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s ease;
            line-height: 1.4;
        }

        .prompt-btn:hover {
            border-color: var(--accent);
            color: var(--text-primary);
            background-color: rgba(56, 189, 248, 0.05);
        }

        /* Message Bubbles */
        .message-row {
            display: flex;
            gap: 12px;
            max-width: 860px;
            width: 100%;
            margin: 0 auto;
        }

        .message-row.user {
            flex-direction: row-reverse;
        }

        .avatar {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .avatar.user-avatar {
            background-color: #3b82f6;
            color: #fff;
        }

        .avatar.ai-avatar {
            background: linear-gradient(135deg, #0284c7, #38bdf8);
            color: #fff;
        }

        .message-bubble {
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 0.95rem;
            line-height: 1.6;
            max-width: calc(100% - 60px);
            word-break: break-word;
        }

        .message-row.user .message-bubble {
            background-color: var(--bg-user-bubble);
            color: #ffffff;
            border-top-right-radius: 4px;
        }

        .message-row.assistant .message-bubble {
            background-color: var(--bg-ai-bubble);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
            border-top-left-radius: 4px;
        }

        /* Markdown Styling inside message bubble */
        .message-bubble p {
            margin-bottom: 8px;
        }

        .message-bubble p:last-child {
            margin-bottom: 0;
        }

        .message-bubble ul, .message-bubble ol {
            margin-left: 20px;
            margin-bottom: 8px;
        }

        .message-bubble code {
            font-family: var(--font-mono);
            background-color: rgba(0, 0, 0, 0.35);
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.88em;
        }

        .message-bubble pre {
            background-color: #0b1120 !important;
            border: 1px solid #1e293b;
            border-radius: 8px;
            padding: 12px;
            margin: 10px 0;
            overflow-x: auto;
            position: relative;
        }

        .message-bubble pre code {
            background-color: transparent !important;
            padding: 0;
            font-size: 0.85rem;
            line-height: 1.5;
        }

        .code-copy-btn {
            position: absolute;
            top: 8px;
            right: 8px;
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: var(--text-secondary);
            font-size: 0.75rem;
            padding: 3px 8px;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .code-copy-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
        }

        .message-bubble table {
            border-collapse: collapse;
            width: 100%;
            margin: 10px 0;
            font-size: 0.85rem;
        }

        .message-bubble th, .message-bubble td {
            border: 1px solid var(--border-color);
            padding: 6px 10px;
            text-align: left;
        }

        .message-bubble th {
            background-color: rgba(255, 255, 255, 0.05);
        }

        .message-bubble blockquote {
            border-left: 3px solid var(--accent);
            padding-left: 12px;
            margin: 8px 0;
            color: var(--text-secondary);
        }

        /* Typing Indicator */
        .typing-indicator {
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 12px 16px;
        }

        .typing-dot {
            width: 6px;
            height: 6px;
            background-color: var(--text-muted);
            border-radius: 50%;
            animation: bounce 1.4s infinite ease-in-out both;
        }

        .typing-dot:nth-child(1) { animation-delay: -0.32s; }
        .typing-dot:nth-child(2) { animation-delay: -0.16s; }

        @keyframes bounce {
            0%, 80%, 100% { transform: scale(0); }
            40% { transform: scale(1.0); }
        }

        /* Input Area */
        .input-area {
            padding: 16px 20px 20px;
            background-color: var(--bg-chat);
            border-top: 1px solid var(--border-color);
        }

        .input-wrapper {
            max-width: 860px;
            margin: 0 auto;
            position: relative;
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 8px 12px;
            display: flex;
            align-items: flex-end;
            gap: 8px;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .input-wrapper:focus-within {
            border-color: var(--accent);
            box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.15);
        }

        textarea#message-input {
            flex: 1;
            background: transparent;
            border: none;
            color: var(--text-primary);
            font-family: var(--font-sans);
            font-size: 0.95rem;
            resize: none;
            max-height: 160px;
            min-height: 24px;
            line-height: 1.5;
            outline: none;
            padding: 4px 0;
        }

        textarea#message-input::placeholder {
            color: var(--text-muted);
        }

        .btn-send {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background-color: var(--accent);
            color: #0f172a;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.15s ease, opacity 0.15s ease;
            flex-shrink: 0;
        }

        .btn-send:hover:not(:disabled) {
            background-color: var(--accent-hover);
        }

        .btn-send:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        .input-hint {
            max-width: 860px;
            margin: 8px auto 0;
            font-size: 0.75rem;
            color: var(--text-muted);
            text-align: center;
        }

        /* Overlay for mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 30;
        }

        /* Mobile responsiveness */
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                top: 0;
                bottom: 0;
                left: 0;
                transform: translateX(-100%);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .sidebar-overlay.open {
                display: block;
            }

            .btn-menu-toggle {
                display: block;
            }

            .prompt-grid {
                grid-template-columns: 1fr;
            }

            .message-bubble {
                max-width: calc(100% - 44px);
            }
        }
    </style>
</head>
<body>

    <!-- Mobile Overlay -->
    <div class="sidebar-overlay" id="sidebar-overlay" onclick="toggleSidebar()"></div>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="{{ route('chat.index') }}" class="brand">
                <div class="brand-icon">AI</div>
                <span>Gemini & AI Chat</span>
            </a>
        </div>

        <form action="{{ route('chat.new') }}" method="POST" id="new-chat-form">
            @csrf
            <button type="submit" class="btn-new-chat">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Obrolan Baru
            </button>
        </form>

        <div class="conversation-list" id="conversation-list">
            @forelse ($conversations as $conv)
                <div class="conversation-item {{ isset($activeConversation) && $activeConversation->id === $conv->id ? 'active' : '' }}" id="conv-item-{{ $conv->id }}">
                    <a href="{{ route('chat.show', $conv) }}" class="conversation-title" style="flex:1; color:inherit; text-decoration:none;">
                        {{ $conv->title }}
                    </a>
                    <button class="btn-delete-conv" title="Hapus obrolan" onclick="deleteConversation(event, {{ $conv->id }})">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                    </button>
                </div>
            @empty
                <div style="padding: 16px; text-align: center; color: var(--text-muted); font-size: 0.8rem;">
                    Belum ada riwayat obrolan.
                </div>
            @endforelse
        </div>

        <div class="sidebar-footer">
            <span style="font-size: 0.75rem; color: var(--text-secondary);">Google Gemini & NVIDIA NIM</span>
            <span style="font-size: 0.7rem; color: var(--text-muted);">Laravel 13 • PHP 8.4</span>
        </div>
    </aside>

    <!-- Main Chat Content -->
    <main class="main-content">
        <header class="chat-header">
            <div class="chat-header-left">
                <button class="btn-menu-toggle" onclick="toggleSidebar()" title="Toggle Menu">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="3" y1="12" x2="21" y2="12"></line>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <line x1="3" y1="18" x2="21" y2="18"></line>
                    </svg>
                </button>
                <div class="chat-title-info">
                    <h2 id="active-chat-title">{{ $activeConversation ? $activeConversation->title : 'Obrolan Baru' }}</h2>
                    <span id="active-model-display">Model Aktif: z-ai/glm-5.3</span>
                </div>
            </div>

            <div class="chat-actions">
                <select id="model-select" class="model-select" onchange="changeModel(this.value)">
                    <option value="meta/llama-3.2-11b-vision-instruct" selected>Meta Llama 3.2 11B (Cepat ⚡)</option>
                    <option value="meta/llama-3.2-90b-vision-instruct">Meta Llama 3.2 90B</option>
                    <option value="meta/llama-guard-4-12b">Meta Llama Guard 4 (12B)</option>
                    <option value="z-ai/glm-5.3">GLM 5.3 (NVIDIA)</option>
                    <option value="deepseek-ai/deepseek-v4.1-flash">DeepSeek v4.1 Flash (NVIDIA)</option>
                    <option value="gemini-flash-latest">Google Gemini Flash</option>
                </select>

                @if ($activeConversation && $activeConversation->messages->count() > 0)
                    <button class="btn-icon-action" id="btn-clear-chat" onclick="clearMessages({{ $activeConversation->id }})" title="Bersihkan pesan">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 6h18"></path>
                            <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                            <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                        </svg>
                        Bersihkan
                    </button>
                @endif
            </div>
        </header>

        <!-- Messages Area -->
        <div class="messages-container" id="messages-container">
            @if (!$activeConversation || $activeConversation->messages->count() === 0)
                <div class="welcome-screen" id="welcome-screen">
                    <div class="welcome-avatar">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                        </svg>
                    </div>
                    <h1 class="welcome-title">Ada yang bisa saya bantu hari ini?</h1>
                    <p class="welcome-desc">
                        Chat AI bertenaga <strong>NVIDIA NIM API</strong>. Tanyakan kode, logika pemrograman, penulisan, atau ide kreatif Anda.
                    </p>

                    <div class="prompt-grid">
                        <button class="prompt-btn" onclick="usePrompt('Jelaskan konsep Dependency Injection di Laravel beserta contoh sederhananya.')">
                            💡 <strong>Laravel DI</strong><br>Jelaskan konsep Dependency Injection di Laravel.
                        </button>
                        <button class="prompt-btn" onclick="usePrompt('Buatkan contoh endpoint REST API CRUD di Laravel 13.')">
                            ⚡ <strong>REST API</strong><br>Buat contoh endpoint API CRUD di Laravel.
                        </button>
                        <button class="prompt-btn" onclick="usePrompt('Which number is larger, 9.11 or 9.8?')">
                            🔢 <strong>Perbandingan Angka</strong><br>Which number is larger, 9.11 or 9.8?
                        </button>
                        <button class="prompt-btn" onclick="usePrompt('Tuliskan fungsi JavaScript untuk validasi form email dan password.')">
                            🔒 <strong>Validasi JS</strong><br>Fungsi validasi email & password di JS.
                        </button>
                    </div>
                </div>
            @else
                @foreach ($activeConversation->messages as $message)
                    <div class="message-row {{ $message->role }}">
                        <div class="avatar {{ $message->role === 'user' ? 'user-avatar' : 'ai-avatar' }}">
                            {{ $message->role === 'user' ? 'U' : 'AI' }}
                        </div>
                        <div class="message-bubble" data-raw="{{ htmlspecialchars($message->content) }}">
                            {!! $message->role === 'assistant' ? '' : e($message->content) !!}
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        <!-- Input Area -->
        <div class="input-area">
            <form id="chat-form" onsubmit="handleSubmit(event)">
                <div class="input-wrapper">
                    <textarea 
                        id="message-input" 
                        rows="1" 
                        placeholder="Ketik pesan Anda di sini... (Enter untuk kirim, Shift+Enter untuk baris baru)"
                        onkeydown="handleKeyDown(event)"
                        oninput="autoResizeTextarea(this)"></textarea>
                    <button type="submit" id="btn-send" class="btn-send" title="Kirim Pesan">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="22" y1="2" x2="11" y2="13"></line>
                            <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                        </svg>
                    </button>
                </div>
                <div class="input-hint" id="footer-model-hint">
                    Model: <code id="hint-model-name">z-ai/glm-5.3</code> • NVIDIA Integrated API
                </div>
            </form>
        </div>
    </main>

    <script>
        let currentConversationId = {{ $activeConversation ? $activeConversation->id : 'null' }};
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const messagesContainer = document.getElementById('messages-container');
        const messageInput = document.getElementById('message-input');
        const btnSend = document.getElementById('btn-send');
        const modelSelect = document.getElementById('model-select');

        // Configure marked.js
        marked.setOptions({
            breaks: true,
            gfm: true,
            highlight: function(code, lang) {
                if (lang && hljs.getLanguage(lang)) {
                    try {
                        return hljs.highlight(code, { language: lang }).value;
                    } catch (e) {}
                }
                return hljs.highlightAuto(code).value;
            }
        });

        // Initialize model from localStorage
        const savedModel = localStorage.getItem('selected_ai_model');
        if (savedModel && modelSelect.querySelector(`option[value="${savedModel}"]`)) {
            modelSelect.value = savedModel;
            updateModelDisplay(savedModel);
        } else {
            updateModelDisplay(modelSelect.value);
        }

        function changeModel(val) {
            localStorage.setItem('selected_ai_model', val);
            updateModelDisplay(val);
        }

        function updateModelDisplay(val) {
            document.getElementById('active-model-display').innerText = 'Model: ' + val;
            document.getElementById('hint-model-name').innerText = val;
        }

        // Initialize markdown rendering for existing AI messages
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.message-row.assistant .message-bubble').forEach(bubble => {
                const rawContent = bubble.getAttribute('data-raw') || bubble.innerText;
                bubble.innerHTML = marked.parse(rawContent);
                addCopyButtons(bubble);
            });
            scrollToBottom();
            messageInput.focus();
        });

        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
            document.getElementById('sidebar-overlay').classList.toggle('open');
        }

        function autoResizeTextarea(textarea) {
            textarea.style.height = 'auto';
            textarea.style.height = Math.min(textarea.scrollHeight, 160) + 'px';
        }

        function handleKeyDown(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                handleSubmit(e);
            }
        }

        function usePrompt(text) {
            messageInput.value = text;
            autoResizeTextarea(messageInput);
            messageInput.focus();
            handleSubmit(new Event('submit'));
        }

        function scrollToBottom() {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        function addCopyButtons(container) {
            container.querySelectorAll('pre').forEach(pre => {
                if (pre.querySelector('.code-copy-btn')) return;
                const btn = document.createElement('button');
                btn.className = 'code-copy-btn';
                btn.innerText = 'Copy';
                btn.onclick = () => {
                    const code = pre.querySelector('code') ? pre.querySelector('code').innerText : pre.innerText;
                    navigator.clipboard.writeText(code).then(() => {
                        btn.innerText = 'Copied!';
                        setTimeout(() => btn.innerText = 'Copy', 2000);
                    });
                };
                pre.appendChild(btn);
            });
        }

        async function handleSubmit(e) {
            e.preventDefault();
            const text = messageInput.value.trim();
            if (!text || btnSend.disabled) return;

            // Hide welcome screen if present
            const welcomeScreen = document.getElementById('welcome-screen');
            if (welcomeScreen) {
                welcomeScreen.remove();
            }

            // Append user message immediately
            appendMessage('user', text);
            messageInput.value = '';
            autoResizeTextarea(messageInput);
            btnSend.disabled = true;

            // Append typing indicator
            const typingRow = appendTypingIndicator();
            scrollToBottom();

            const selectedModel = modelSelect.value;

            try {
                const response = await fetch('{{ route("chat.send") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        conversation_id: currentConversationId,
                        message: text,
                        model: selectedModel
                    })
                });

                const data = await response.json();
                typingRow.remove();

                if (data.success) {
                    currentConversationId = data.conversation_id;
                    appendMessage('assistant', data.assistant_message.content);

                    // Update page title & active chat title if needed
                    if (data.conversation_title) {
                        document.getElementById('active-chat-title').innerText = data.conversation_title;
                        updateSidebarConversation(data.conversation_id, data.conversation_title);
                    }
                } else {
                    appendErrorMessage(data.error || 'Terjadi kesalahan saat memproses jawaban.');
                }
            } catch (err) {
                if (typingRow) typingRow.remove();
                appendErrorMessage('Gagal terhubung ke server: ' + err.message);
            } finally {
                btnSend.disabled = false;
                messageInput.focus();
                scrollToBottom();
            }
        }

        function appendMessage(role, content) {
            const row = document.createElement('div');
            row.className = `message-row ${role}`;

            const avatar = document.createElement('div');
            avatar.className = `avatar ${role === 'user' ? 'user-avatar' : 'ai-avatar'}`;
            avatar.innerText = role === 'user' ? 'U' : 'AI';

            const bubble = document.createElement('div');
            bubble.className = 'message-bubble';
            
            if (role === 'assistant') {
                bubble.innerHTML = marked.parse(content);
                addCopyButtons(bubble);
            } else {
                bubble.innerText = content;
            }

            row.appendChild(avatar);
            row.appendChild(bubble);
            messagesContainer.appendChild(row);
            scrollToBottom();
            return row;
        }

        function appendTypingIndicator() {
            const row = document.createElement('div');
            row.className = 'message-row assistant';
            row.id = 'typing-indicator-row';

            const avatar = document.createElement('div');
            avatar.className = 'avatar ai-avatar';
            avatar.innerText = 'AI';

            const bubble = document.createElement('div');
            bubble.className = 'message-bubble';
            bubble.innerHTML = `
                <div class="typing-indicator">
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                </div>
            `;

            row.appendChild(avatar);
            row.appendChild(bubble);
            messagesContainer.appendChild(row);
            return row;
        }

        function appendErrorMessage(msg) {
            const row = document.createElement('div');
            row.className = 'message-row assistant';

            const avatar = document.createElement('div');
            avatar.className = 'avatar ai-avatar';
            avatar.innerText = 'AI';

            const bubble = document.createElement('div');
            bubble.className = 'message-bubble';
            bubble.style.borderColor = 'var(--danger)';
            bubble.style.color = '#fca5a5';
            bubble.innerHTML = `⚠️ <strong>Pemberitahuan:</strong> ${msg}`;

            row.appendChild(avatar);
            row.appendChild(bubble);
            messagesContainer.appendChild(row);
            scrollToBottom();
        }

        function updateSidebarConversation(id, title) {
            const list = document.getElementById('conversation-list');
            let item = document.getElementById(`conv-item-${id}`);

            if (!item) {
                if (list.innerText.includes('Belum ada riwayat')) {
                    list.innerHTML = '';
                }

                item = document.createElement('div');
                item.className = 'conversation-item active';
                item.id = `conv-item-${id}`;
                item.innerHTML = `
                    <a href="/c/${id}" class="conversation-title" style="flex:1; color:inherit; text-decoration:none;">
                        ${title}
                    </a>
                    <button class="btn-delete-conv" title="Hapus obrolan" onclick="deleteConversation(event, ${id})">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                    </button>
                `;
                list.prepend(item);
            } else {
                item.querySelector('.conversation-title').innerText = title;
            }
        }

        async function deleteConversation(event, id) {
            event.preventDefault();
            event.stopPropagation();
            if (!confirm('Hapus obrolan ini?')) return;

            try {
                const response = await fetch(`/c/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    const item = document.getElementById(`conv-item-${id}`);
                    if (item) item.remove();

                    if (currentConversationId === id) {
                        window.location.href = '{{ route("chat.index") }}';
                    }
                }
            } catch (err) {
                alert('Gagal menghapus obrolan: ' + err.message);
            }
        }

        async function clearMessages(id) {
            if (!confirm('Bersihkan semua pesan dalam obrolan ini?')) return;

            try {
                const response = await fetch(`/c/${id}/clear`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    window.location.reload();
                }
            } catch (err) {
                alert('Gagal membersihkan pesan: ' + err.message);
            }
        }
    </script>
</body>
</html>
