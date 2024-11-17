class ChatApp {
    constructor() {
        // Get DOM elements
        this.userInput = document.getElementById('user-input');
        this.chatLog = document.getElementById('chat-log');
        this.typingIndicator = document.getElementById('typing-indicator');
        this.sendButton = document.querySelector('button');
        
        // Placeholder for responses
        this.responses = {};

        // Bind methods to preserve 'this' context
        this.sendMessage = this.sendMessage.bind(this);

        // Initialize event listeners and load JSON
        this.initializeEventListeners();
        this.loadResponses();
    }

    async loadResponses() {
        try {
            const response = await fetch('responses.json');
            if (!response.ok) {
                throw new Error(`Failed to load responses.json: ${response.status}`);
            }
            this.responses = await response.json();
        } catch (error) {
            console.error("Error loading responses:", error);
            this.addChatLog("System", "Failed to load AI responses.");
        }
    }

    initializeEventListeners() {
        // Add event listener for Enter key
        this.userInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                this.sendMessage();
            }
        });

        // Add event listener for button click
        this.sendButton.addEventListener('click', this.sendMessage);
    }

    escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    showTypingIndicator() {
        this.typingIndicator.style.display = 'block';
    }

    hideTypingIndicator() {
        this.typingIndicator.style.display = 'none';
    }

    addChatLog(sender, message, isUser = false) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${isUser ? 'user-message' : 'ai-message'}`;
        messageDiv.textContent = `${sender}: ${this.escapeHtml(message)}`;
        this.chatLog.appendChild(messageDiv);
        this.chatLog.scrollTop = this.chatLog.scrollHeight;  // Scroll to the bottom
    }

    sendMessage() {
        const message = this.userInput.value.trim();

        if (message === "") return;

        // Clear input and add user message
        this.userInput.value = "";
        this.addChatLog("You", message, true);

        // Show typing indicator
        this.showTypingIndicator();

        setTimeout(() => {
            this.handleResponse(message.toLowerCase());
            this.hideTypingIndicator();
        }, 500); // Simulate delay
    }

    handleResponse(userMessage) {
        const categories = Object.keys(this.responses);
        let reply = this.responses.default[0]; // Default response

        for (const category of categories) {
            if (category === "default") continue;

            const keywords = category.split(",");
            if (keywords.some((keyword) => userMessage.includes(keyword))) {
                reply = this.responses[category][Math.floor(Math.random() * this.responses[category].length)];
                break;
            }
        }

        this.addChatLog("RyanAI", reply);
    }
}

// Initialize the chat application when the DOM is fully loaded
document.addEventListener('DOMContentLoaded', () => {
    window.chatApp = new ChatApp();
});
