<?php
require 'vendor/autoload.php';
require 'includes/header.php';

$key = GEMINI_API_KEY;
$chatbotKey = $key;
$userInfo = getUserInfo();
$userName = $userInfo['name'];
?>

<!DOCTYPE html>
<html>
<head>
    <style>
    .chat-container {
        height: 100%;
        display: flex;
        flex-direction: column;
        background: #fff;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
    }

    .chat-header {
        background: linear-gradient(to right, #696cff, #8083ff);
        padding: 1rem 1.5rem;
        
    }

    .welcome-card {
        background: rgba(105, 108, 255, 0.08);
        border-radius: 0.5rem;
        padding: 1rem;
        margin-top: 0.75rem;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .chat-title {
        color: #fff;
        font-size: 1.1rem;
        font-weight: 600;
        margin: 0;
    }

    .welcome-text {
        color: rgba(255, 255, 255, 0.9);
        font-size: 0.875rem;
        margin: 0;
    }

    .chat-messages {
        flex: 1;
        overflow-y: auto;
        padding: 1.5rem;
        background: #f8f7fa;
        min-height: 350px;
        max-height: 550px;
    }

    .message {
        margin-bottom: 1rem;
        max-width: 85%;
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
    }

    .message-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .bot-avatar {
        background: #e7e7ff;
        color: #696cff;
    }

    .user-avatar {
        background: #696cff;
        color: #fff;
    }

    .message-content {
        padding: 0.75rem 1rem;
        border-radius: 1rem;
        position: relative;
    }

    .user-message {
        margin-left: auto;
        flex-direction: row-reverse;
    }

    .user-message .message-content {
        background: #696cff;
        color: white;
        border-bottom-right-radius: 0.25rem;
    }

    .bot-message .message-content {
        background: white;
        color: #565970;
        border-bottom-left-radius: 0.25rem;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }

    .chat-input-container {
        padding: 1rem;
        background: white;
        border-top: 1px solid #f0f0f0;
        border-radius: 0 0 0.375rem 0.375rem;
    }

    .chat-input-wrapper {
        display: flex;
        gap: 0.5rem;
        align-items: flex-start;
    }

    .chat-textarea {
        flex: 1;
        border: 1px solid #e7e7e8;
        border-radius: 0.375rem;
        padding: 0.625rem 1rem;
        font-size: 0.875rem;
        line-height: 1.5;
        color: #565970;
        transition: all 0.15s ease-in-out;
        resize: none;
        min-height: 40px;
        max-height: 120px;
        font-family: inherit;
        overflow-y: auto;
    }

    .chat-textarea:focus {
        outline: none;
        border-color: #696cff;
        box-shadow: 0 0 0 0.25rem rgba(105, 108, 255, 0.1);
    }

    .send-button {
        background: #696cff;
        color: white;
        border: none;
        border-radius: 0.375rem;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background-color 0.15s ease-in-out;
        flex-shrink: 0;
    }

    .send-button:hover {
        background: #5f61e6;
    }

    .typing-indicator {
        padding: 0.5rem 1rem;
        color: #697a8d;
        font-size: 0.875rem;
        display: none;
    }

    .typing-indicator.active {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .typing-indicator i {
        color: #696cff;
    }

    /* Custom scrollbar for both chat and textarea */
    .chat-messages::-webkit-scrollbar,
    .chat-textarea::-webkit-scrollbar {
        width: 6px;
    }

    .chat-messages::-webkit-scrollbar-track,
    .chat-textarea::-webkit-scrollbar-track {
        background: #f8f7fa;
    }

    .chat-messages::-webkit-scrollbar-thumb,
    .chat-textarea::-webkit-scrollbar-thumb {
        background: #e7e7e8;
        border-radius: 3px;
    }

    .chat-messages::-webkit-scrollbar-thumb:hover,
    .chat-textarea::-webkit-scrollbar-thumb:hover {
        background: #d4d4d8;
    }

   
    </style>
</head>
<body>


    <div class="offcanvas offcanvas-end custom-offcanvas" tabindex="-1" id="rightOffcanvas" aria-labelledby="rightOffcanvasLabel">
       
        <div class="offcanvas-body p-0">
            <div class="chat-container">
                <div class="chat-header">
                    <div class="d-flex align-items-center gap-2">
                        <i class='bx bx-bot fs-4 text-white'></i>
                        <h5 class="chat-title mb-0">Cognitive AI Assistant </h5>
                        <div style="position:fixed; right: 20px;">
                            <a href="" style="text-decoration:none;" title="New Chat"><i class='bx bx-plus fs-4 text-white'></i></a>
                            <a href="" style="text-decoration:none;" title="History"><i class='bx bx-history fs-4 text-white'></i></a>
                        </div>

                        

                    </div>
                    <div class="welcome-card">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-white p-2 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <img src="img/Logo/Cognitive Campus Logo.png" style="width: 50px; border-radius:50%;" alt="">
                            </div>
                            <div>
                                <p class="welcome-text mb-1 fw-semibold">Welcome <?php echo json_encode($userName)?> </p>
                                <p class="welcome-text opacity-75 small">How can I help you today?</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="chat-messages" id="chatMessages">
                    <div class="message bot-message">
                        <div class="message-avatar bot-avatar">
                            <i class='bx bx-bot'></i>
                        </div>
                        <div class="message-content">
                            <p class="mb-0">Hello! I'm your AI assistant. Feel free to ask me anything.</p>
                        </div>
                    </div>
                </div>
                
                <div class="typing-indicator" id="typingIndicator">
                    <i class='bx bx-dots-horizontal-rounded'></i>
                    <span>Cognitive AI is typing...</span>
                </div>

                <div class="chat-input-container">
                    <form id="chatForm" class="chat-input-wrapper">
                        <textarea 
                            class="chat-textarea" 
                            id="userInput" 
                            placeholder="Type your message... "
                            rows="1"
                            required
                        ></textarea>
                        <button type="submit" class="send-button">
                            <i class='bx bx-send'></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
    const chatbotApiKey = <?php echo json_encode($key); ?>;
</script>
    <script src="js/chatbot.js">
</script>
</body>
</html>