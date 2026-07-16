/**
 * AI Chatbot Frontend
 * NexMart E-Commerce
 */

class NexmartChatbot {
    constructor() {
        this.conversationId = this.getOrCreateConversationId();
        this.isOpen = false;
        this.isTyping = false;
        this.init();
    }

    init() {
        this.createChatbotUI();
        this.attachEventListeners();
        this.showWelcomeMessage();
    }

    getOrCreateConversationId() {
        let id = localStorage.getItem('nexmart_chat_id');
        if (!id) {
            id = 'chat_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            localStorage.setItem('nexmart_chat_id', id);
        }
        return id;
    }

    createChatbotUI() {
        const chatbotHTML = `
            <div class="chatbot-container" id="nexmartChatbot">
                <!-- Chat Button -->
                <button class="chatbot-toggle" id="chatbotToggle" aria-label="Open chat">
                    <svg class="chat-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    </svg>
                    <svg class="close-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                    <span class="chatbot-badge">1</span>
                </button>

                <!-- Chat Window -->
                <div class="chatbot-window" id="chatbotWindow">
                    <div class="chatbot-header">
                        <div class="chatbot-header-info">
                            <div class="chatbot-avatar">
                                <i class="fas fa-robot"></i>
                            </div>
                            <div>
                                <h3>NexMart Assistant</h3>
                                <span class="chatbot-status">
                                    <span class="status-dot"></span>
                                    Online
                                </span>
                            </div>
                        </div>
                        <button class="chatbot-close" id="chatbotClose" aria-label="Close chat">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="chatbot-messages" id="chatbotMessages">
                        <!-- Messages will be added here -->
                    </div>

                    <div class="chatbot-suggestions" id="chatbotSuggestions">
                        <!-- Suggestion chips will be added here -->
                    </div>

                    <div class="chatbot-input-area">
                        <input 
                            type="text" 
                            id="chatbotInput" 
                            class="chatbot-input" 
                            placeholder="Type your message..."
                            autocomplete="off"
                        >
                        <button class="chatbot-send" id="chatbotSend" aria-label="Send message">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>

                    <div class="chatbot-footer">
                        <small>Powered by NexMart AI</small>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', chatbotHTML);
    }

    attachEventListeners() {
        const toggle = document.getElementById('chatbotToggle');
        const close = document.getElementById('chatbotClose');
        const send = document.getElementById('chatbotSend');
        const input = document.getElementById('chatbotInput');

        toggle.addEventListener('click', () => this.toggleChat());
        close.addEventListener('click', () => this.closeChat());
        send.addEventListener('click', () => this.sendMessage());
        
        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                this.sendMessage();
            }
        });

        // Close on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen) {
                this.closeChat();
            }
        });
    }

    toggleChat() {
        if (this.isOpen) {
            this.closeChat();
        } else {
            this.openChat();
        }
    }

    openChat() {
        const window = document.getElementById('chatbotWindow');
        const toggle = document.getElementById('chatbotToggle');
        const badge = toggle.querySelector('.chatbot-badge');
        
        window.classList.add('open');
        toggle.classList.add('active');
        this.isOpen = true;
        
        // Hide badge
        if (badge) {
            badge.style.display = 'none';
        }

        // Focus input
        setTimeout(() => {
            document.getElementById('chatbotInput').focus();
        }, 300);
    }

    closeChat() {
        const window = document.getElementById('chatbotWindow');
        const toggle = document.getElementById('chatbotToggle');
        
        window.classList.remove('open');
        toggle.classList.remove('active');
        this.isOpen = false;
    }

    showWelcomeMessage() {
        setTimeout(() => {
            this.addMessage(
                "Hello! 👋 Welcome to NexMart! I'm your shopping assistant. How can I help you today?",
                'bot',
                [
                    'Show me products',
                    'Track my order',
                    'Contact information'
                ]
            );
        }, 1000);
    }

    async sendMessage() {
        const input = document.getElementById('chatbotInput');
        const message = input.value.trim();

        if (!message || this.isTyping) return;

        // Add user message
        this.addMessage(message, 'user');
        input.value = '';

        // Clear suggestions
        this.clearSuggestions();

        // Show typing indicator
        this.showTyping();

        try {
            // Send to API
            const response = await fetch(this.getApiUrl('api/chatbot.php'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    message: message,
                    conversationId: this.conversationId
                })
            });

            const data = await response.json();

            // Hide typing indicator
            this.hideTyping();

            if (data.success) {
                // Add bot response
                this.addMessage(data.response, 'bot', data.suggestions);
            } else {
                this.addMessage('Sorry, I encountered an error. Please try again.', 'bot');
            }
        } catch (error) {
            console.error('Chatbot error:', error);
            this.hideTyping();
            this.addMessage('Sorry, I\'m having trouble connecting. Please try again later.', 'bot');
        }
    }

    addMessage(text, sender, suggestions = []) {
        const messagesContainer = document.getElementById('chatbotMessages');
        const messageDiv = document.createElement('div');
        messageDiv.className = `chatbot-message ${sender}-message`;

        if (sender === 'bot') {
            messageDiv.innerHTML = `
                <div class="message-avatar">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="message-content">
                    <div class="message-bubble">${this.formatMessage(text)}</div>
                    <span class="message-time">${this.getCurrentTime()}</span>
                </div>
            `;
        } else {
            messageDiv.innerHTML = `
                <div class="message-content">
                    <div class="message-bubble">${this.escapeHtml(text)}</div>
                    <span class="message-time">${this.getCurrentTime()}</span>
                </div>
            `;
        }

        messagesContainer.appendChild(messageDiv);
        this.scrollToBottom();

        // Add suggestions if provided
        if (suggestions && suggestions.length > 0) {
            this.showSuggestions(suggestions);
        }
    }

    formatMessage(text) {
        // Convert newlines to <br>
        text = this.escapeHtml(text);
        text = text.replace(/\n/g, '<br>');
        
        // Make emojis larger
        text = text.replace(/([\u{1F300}-\u{1F9FF}])/gu, '<span class="emoji">$1</span>');
        
        return text;
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    showTyping() {
        this.isTyping = true;
        const messagesContainer = document.getElementById('chatbotMessages');
        const typingDiv = document.createElement('div');
        typingDiv.className = 'chatbot-message bot-message typing-indicator';
        typingDiv.id = 'typingIndicator';
        typingDiv.innerHTML = `
            <div class="message-avatar">
                <i class="fas fa-robot"></i>
            </div>
            <div class="message-content">
                <div class="message-bubble">
                    <span class="typing-dot"></span>
                    <span class="typing-dot"></span>
                    <span class="typing-dot"></span>
                </div>
            </div>
        `;
        messagesContainer.appendChild(typingDiv);
        this.scrollToBottom();
    }

    hideTyping() {
        this.isTyping = false;
        const typingIndicator = document.getElementById('typingIndicator');
        if (typingIndicator) {
            typingIndicator.remove();
        }
    }

    showSuggestions(suggestions) {
        const suggestionsContainer = document.getElementById('chatbotSuggestions');
        suggestionsContainer.innerHTML = '';

        suggestions.forEach(suggestion => {
            const chip = document.createElement('button');
            chip.className = 'suggestion-chip';
            chip.textContent = suggestion;
            chip.addEventListener('click', () => {
                document.getElementById('chatbotInput').value = suggestion;
                this.sendMessage();
            });
            suggestionsContainer.appendChild(chip);
        });
    }

    clearSuggestions() {
        const suggestionsContainer = document.getElementById('chatbotSuggestions');
        suggestionsContainer.innerHTML = '';
    }

    scrollToBottom() {
        const messagesContainer = document.getElementById('chatbotMessages');
        setTimeout(() => {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }, 100);
    }

    getCurrentTime() {
        const now = new Date();
        return now.toLocaleTimeString('en-US', { 
            hour: 'numeric', 
            minute: '2-digit',
            hour12: true 
        });
    }

    getApiUrl(path) {
        // Use relative path from current page location
        // Get the base path from current location
        const currentPath = window.location.pathname;
        const basePath = currentPath.substring(0, currentPath.lastIndexOf('/') + 1);
        
        // If we're in a subdirectory (like admin), go up one level
        if (basePath.includes('/admin/')) {
            return `../${path}`;
        }
        
        // Otherwise use relative path from root
        return path;
    }
}

// Initialize chatbot when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.nexmartChatbot = new NexmartChatbot();
    });
} else {
    window.nexmartChatbot = new NexmartChatbot();
}
