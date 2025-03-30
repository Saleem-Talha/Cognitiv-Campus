document.addEventListener('DOMContentLoaded', function() {
    const currentUserEmail = "<?php echo $userEmail; ?>";
    const userName = "<?php echo $userFirstName; ?>";

    const contextModeSwitch = document.getElementById('contextMode');
    const userInput = document.getElementById('user-input');
    const generateImageBtn = document.getElementById('generate-image-btn');

    // Global displayMessage function
    window.displayMessage = function(text, className, sender) {
        const messagesDiv = document.getElementById('chat-messages');
        const messageRow = document.createElement('div');
        const iconDiv = document.createElement('div');
        const messageDiv = document.createElement('div');

        messageRow.className = `message-row ${className === 'user-message' ? 'user-message-row' : 'bot-message-row'}`;
        
        // Create icon
        iconDiv.className = `message-icon ${className === 'user-message' ? 'user-icon' : 'bot-icon'}`;
        iconDiv.innerHTML = `<i class='bx ${className === 'user-message' ? 'bx-user' : 'bx-bot'} fs-5'></i>`;

        // Create message
        messageDiv.className = `message ${className}`;

        // Handle image markdown
        const imageRegex = /!\[([^\]]*)\]\(([^\)]+)\)/;
        const imageMatch = text.match(imageRegex);

        if (imageMatch) {
            const altText = imageMatch[1];
            const imageSrc = imageMatch[2];

            // Replace markdown image with actual img tag
            text = text.replace(imageMatch[0], '');
            messageDiv.innerHTML = `<strong>${sender}</strong><br>`;
            
            const imgElement = document.createElement('img');
            imgElement.src = imageSrc;
            imgElement.alt = altText;
            imgElement.classList.add('img-fluid', 'rounded', 'mt-2', 'mb-2');
            
            messageDiv.appendChild(imgElement);

            // Add any additional text after the image
            if (text.trim()) {
                const additionalTextEl = document.createElement('p');
                additionalTextEl.textContent = text.trim();
                messageDiv.appendChild(additionalTextEl);
            }
        } else {
            // Handle regular text messages
            const codeRegex = /```(\w+)?\n([\s\S]*?)```/;
            const codeMatch = text.match(codeRegex);

            if (codeMatch) {
                const language = codeMatch[1] || 'plaintext';
                const code = codeMatch[2];

                messageDiv.innerHTML = `<strong>${sender}</strong><br>`;
                const preElement = document.createElement('pre');
                const codeElement = document.createElement('code');
                codeElement.className = `language-${language}`;
                codeElement.textContent = code.trim();
                preElement.appendChild(codeElement);
                messageDiv.appendChild(preElement);
            } else {
                messageDiv.innerHTML = `<strong>${sender}</strong><br>${text}`;
            }
        }

        // Append elements
        if (className === 'user-message') {
            messageRow.appendChild(messageDiv);
            messageRow.appendChild(iconDiv);
        } else {
            messageRow.appendChild(iconDiv);
            messageRow.appendChild(messageDiv);
        }

        // Clear welcome message if this is the first chat message
        if (messagesDiv.querySelector('.flex-column')) {
            messagesDiv.innerHTML = '';
        }

        messagesDiv.appendChild(messageRow);
        messagesDiv.scrollTop = messagesDiv.scrollHeight;

        // Highlight code if Prism.js is available
        if (window.Prism && messageDiv.querySelector('code')) {
            Prism.highlightElement(messageDiv.querySelector('code'));
        }
    };

    // Typing indicator functions
    window.showTypingIndicator = function() {
        const messagesDiv = document.getElementById('chat-messages');
        const typingIndicator = document.createElement('div');
        typingIndicator.id = 'typing-indicator';
        typingIndicator.className = 'message-row bot-message-row typing-animation';
        typingIndicator.innerHTML = `
            <div class="message-icon bot-icon">
                <i class='bx bx-bot fs-5'></i>
            </div>
            <div class="message bot-message">
                <div class="typing-dots">
                    <span>.</span>
                    <span>.</span>
                    <span>.</span>
                </div>
            </div>
        `;
        messagesDiv.appendChild(typingIndicator);
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    };

    window.removeTypingIndicator = function() {
        const typingIndicator = document.getElementById('typing-indicator');
        if (typingIndicator) {
            typingIndicator.remove();
        }
    };

    // Start new chat function
    window.startNewChat = function() {
        document.getElementById('chat-messages').innerHTML = '';

        fetch('ai-handle-chat.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'message=New Chat Started&email=' + encodeURIComponent(currentUserEmail) + '&start_new_chat=true'
        })
        .then(response => response.json())
        .then(data => {
            if (data.response === 'upgrade_plan') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Upgrade Required',
                    text: "You have reached your chat limit. Please upgrade your plan to continue."
                });
            } else {
                displayMessage('New chat session started', 'bot-message', 'AI');
            }
        })
        .catch(error => {
            console.error('Error starting new chat:', error);
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Failed to start a new chat. Please try again.'
            });
        });
    };

    // Context mode switch event listener
    contextModeSwitch.addEventListener('change', function() {
        console.log('Context Mode:', this.checked ? 'ON' : 'OFF');
    });

    // Keypress event listener for user input
    userInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            handleMessageSend();
        }
    });

    // Global message send handler
    window.handleMessageSend = function() {
        const contextMode = contextModeSwitch.checked;
        const input = document.getElementById('user-input');
        const message = input.value.trim();
        
        if (!message) return;

        console.log('Sending message in', contextMode ? 'Context Mode' : 'Normal Mode');

        if (contextMode) {
            sendContextMessage(message);
        } else {
            sendMessage(message);
        }
    };

    // Context message sending function
    function sendContextMessage(message) {
        const input = document.getElementById('user-input');
        const trimmedMessage = input.value.trim();
        if (!trimmedMessage) return;

        displayMessage(trimmedMessage, 'user-message', userName);
        input.value = '';
        input.disabled = true;

        showTypingIndicator();

        fetch('ai-handle-context-chat.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'message=' + encodeURIComponent(trimmedMessage) + '&email=' + encodeURIComponent(currentUserEmail)
        })
        .then(response => response.json())
        .then(data => {
            removeTypingIndicator();
            input.disabled = false;
            input.focus();

            if (data.response === 'upgrade_plan') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Upgrade Required',
                    text: "You have reached your chat limit. Please upgrade your plan to continue."
                });
            } else {
                displayMessage(data.response, 'bot-message', 'AI');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            removeTypingIndicator();
            input.disabled = false;
            
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Something went wrong. Please try again.'
            });
        });
    }

    // Standard message sending function
    function sendMessage(message) {
        const input = document.getElementById('user-input');
        const trimmedMessage = input.value.trim();
        if (!trimmedMessage) return;

        displayMessage(trimmedMessage, 'user-message', userName);
        input.value = '';
        input.disabled = true;

        showTypingIndicator();

        fetch('ai-handle-chat.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'message=' + encodeURIComponent(trimmedMessage) + '&email=' + encodeURIComponent(currentUserEmail)
        })
        .then(response => response.json())
        .then(data => {
            removeTypingIndicator();
            input.disabled = false;
            input.focus();

            if (data.response === 'upgrade_plan') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Upgrade Required',
                    text: "You have reached your chat limit. Please upgrade your plan to continue."
                });
            } else {
                displayMessage(data.response, 'bot-message', 'AI');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            removeTypingIndicator();
            input.disabled = false;
            
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Something went wrong. Please try again.'
            });
        });
    }

    // Image generation event listener
    generateImageBtn.addEventListener('click', function() {
        const userPrompt = userInput.value.trim();
        
        // if (!userPrompt) {
        //     Swal.fire({
        //         icon: 'warning',
        //         title: 'Prompt Required',
        //         text: 'Please enter a prompt for image generation'
        //     });
        //     return;
        // }

        // Clear input field
        userInput.value = '';

        // Display user's image generation request in chat
        displayMessage(userPrompt, 'user-message', userName);

        // Show typing indicator
        showTypingIndicator();

        // Disable input during generation
        userInput.disabled = true;

        // Send image generation request
        fetch('ai-handle-image.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `message=${encodeURIComponent(userPrompt)}`
        })
        .then(response => response.json())
        .then(data => {
            // Remove typing indicator
            removeTypingIndicator();
            userInput.disabled = false;
            userInput.focus();

            if (data.success) {
                // Create image message
                const imageMessage = `
                    Generated Image:\n
                    ![Generated Image](${data.image})
                    ${data.cached ? '*Image retrieved from cache*' : '*New image generated*'}
                `;
                
                // Display image message in chat
                displayMessage(imageMessage, 'bot-message', 'AI');
            } else {
                // Display error message in chat
                displayMessage(`Error: ${data.error}`, 'bot-message', 'AI');
            }
        })
        .catch(error => {
            // Remove typing indicator and enable input
            removeTypingIndicator();
            userInput.disabled = false;
            
            // Display network error in chat
            displayMessage(`Network Error: ${error.message}`, 'bot-message', 'AI');
        });
    });

    // Chat history functions
    window.showHistory = function() {
        const historyDiv = document.getElementById('chat-history');

        fetch('ai-fetch_titles.php?email=' + encodeURIComponent(currentUserEmail))
            .then(response => response.json())
            .then(data => {
                historyDiv.innerHTML = '';

                if (data.error) {
                    historyDiv.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
                    return;
                }

                if (!data.length) {
                    historyDiv.innerHTML = `
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class='bx bx-history text-muted' style="font-size: 3rem;"></i>
                            </div>
                            <h6 class="text-muted">No chat history available</h6>
                            <p class="text-muted small">Start a new conversation to see your history here</p>
                        </div>`;
                    return;
                }

                data.forEach(item => {
                    const titleElement = document.createElement('div');
                    titleElement.className = 'session-title mb-2';
                    titleElement.innerHTML = `
                        <button class="btn btn-light w-100 text-start" data-session-id="${item.id}">
                            <div class="d-flex justify-content-between">
                                <span>${item.title}</span>
                                <small class="text-muted ms-2">${item.date}</small>
                            </div>
                        </button>`;
                    
                    // Add click handler for each session
                    titleElement.querySelector('button').addEventListener('click', function() {
                        loadChatSession(this.dataset.sessionId);
                        // Close the offcanvas after selection
                        const offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('chatHistoryOffcanvas'));
                        offcanvas.hide();
                    });
                    
                    historyDiv.appendChild(titleElement);
                });
            })
            .catch(error => {
                console.error('Error fetching chat history:', error);
                historyDiv.innerHTML = `<div class="alert alert-danger">Unable to load chat history</div>`;
            });

        // Show the offcanvas
        const offcanvas = new bootstrap.Offcanvas(document.getElementById('chatHistoryOffcanvas'));
        offcanvas.show();
    };

    // Load specific chat session function
    window.loadChatSession = function(sessionId) {
        const messagesDiv = document.getElementById('chat-messages');
        // Show loading spinner
        messagesDiv.innerHTML = `
            <div class="d-flex justify-content-center p-3">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>`;

        fetch(`ai-fetch_session.php?session_id=${sessionId}`)
            .then(response => response.json())
            .then(messages => {
                // Clear the messages div
                messagesDiv.innerHTML = '';

                // Display each message in the chat
                messages.forEach(msg => {
                    const messageType = msg.sender === 'user' ? 'user-message' : 'bot-message';
                    const senderName = msg.sender === 'user' ? userName : 'AI';
                    displayMessage(msg.message, messageType, senderName);
                });

                // Scroll to bottom of chat
                messagesDiv.scrollTop = messagesDiv.scrollHeight;
            })
            .catch(error => {
                console.error('Error loading chat session:', error);
                messagesDiv.innerHTML = `<div class="alert alert-danger">Unable to load chat session</div>`;
            });
        };
    });