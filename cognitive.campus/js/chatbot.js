document.addEventListener('DOMContentLoaded', function() {
    const chatForm = document.getElementById('chatForm');
    const chatMessages = document.getElementById('chatMessages');
    const userInput = document.getElementById('userInput');
    const typingIndicator = document.getElementById('typingIndicator');

    const GEMINI_API_KEY = chatbotApiKey;

    // Auto-resize textarea
    function autoResize(textarea) {
        textarea.style.height = 'auto';
        textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
    }

    // Handle textarea input
    userInput.addEventListener('input', function() {
        autoResize(this);
    });

    // Handle keydown events
    userInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            chatForm.dispatchEvent(new Event('submit'));
        }
    });

    chatForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const message = userInput.value.trim();
        if (!message) return;

        addMessage(message, 'user');
        userInput.value = '';
        userInput.style.height = '40px'; // Reset height
        typingIndicator.classList.add('active');

        try {
            const response = await fetch(`https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=${GEMINI_API_KEY}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    contents: [{
                        role: 'user',
                        parts: [{
                            text: message
                        }]
                    }],
                    generationConfig: {
                        maxOutputTokens: 150,
                        temperature: 0.7
                    }
                })
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            typingIndicator.classList.remove('active');

            if (data.candidates?.[0]?.content) {
                addMessage(data.candidates[0].content.parts[0].text, 'bot');
            }
        } catch (error) {
            console.error('Error:', error);
            typingIndicator.classList.remove('active');
            
            let errorMessage = 'Sorry, I encountered an error. Please try again later.';
            if (error.message.includes('401')) {
                errorMessage = 'Authentication error. Please check the API key.';
            } else if (error.message.includes('429')) {
                errorMessage = 'Too many requests. Please wait a moment before trying again.';
            }
            
            addMessage(errorMessage, 'bot');
        }
    });

    function addMessage(content, sender) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${sender}-message`;
        
        const avatarIcon = sender === 'bot' ? 'bx-bot' : 'bx-user';
        messageDiv.innerHTML = `
            <div class="message-avatar ${sender}-avatar">
                <i class='bx ${avatarIcon}'></i>
            </div>
            <div class="message-content">
                <p class="mb-0">${content}</p>
            </div>
        `;
        chatMessages.appendChild(messageDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
});