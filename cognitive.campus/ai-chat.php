<?php
include("includes/validation.php");
$userInfo = getUserInfo();
$userEmail = htmlspecialchars($userInfo['email'], ENT_QUOTES, 'UTF-8'); // Sanitize for safe output in JS
$userFirstName = htmlspecialchars($userInfo['name'], ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html
  lang="en"
  class="light-style layout-menu-fixed"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="../assets/"
  data-template="vertical-menu-template-free"
>
<head>
    <?php include('includes/header.php'); ?>
    <title>AI Chatbot - Cognitive Assistant</title>

    <style>
        .chat-container {
            height: calc(100vh - 150px);
            display: flex;
            flex-direction: column;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        #chat-messages {
            height:500px;
            flex-grow: 1;
            overflow-y: auto;
            padding: 20px;
            background: #f5f5f9;
        }

        .message-row {
            display: flex;
            margin-bottom: 15px;
            clear: both;
        }

        .message {
            max-width: 80%;
            padding: 12px 15px;
            border-radius: 8px;
            position: relative;
            line-height: 1.4;
            font-size: 0.95rem;
        }

        .message-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            margin-left: 10px;
        }

        .user-message-row {
            justify-content: flex-end;
        }

        .bot-message-row {
            justify-content: flex-start;
        }

        .user-message {
            background-color: #696cff;
            color: white;
            border-bottom-right-radius: 5px;
        }

        .bot-message {
            background-color: #e7e7ff;
            color: #5a5ad0;
            border-bottom-left-radius: 5px;
        }

        .user-icon {
            background-color: rgba(105, 108, 255, 0.16);
            color: #696cff;
        }

        .bot-icon {
            background-color: rgba(105, 108, 255, 0.16);
            color: #696cff;
        }
        .offcanvas {
        width: 400px;
    }
    
    #chat-history .session-title button:hover {
        background-color: #e7e7ff !important;
    }
    
    #chat-history .session-title button {
        transition: all 0.2s ease-in-out;
    }

    .typing-animation .typing-dots {
    display: flex;
    align-items: center;
}

.typing-animation .typing-dots span {
    font-size: 1.5rem;
    color: #5a5ad0;
    animation: typing 1.4s infinite;
    display: inline-block;
    margin: 0 2px;
}

.typing-animation .typing-dots span:nth-child(2) {
    animation-delay: 0.2s;
}

.typing-animation .typing-dots span:nth-child(3) {
    animation-delay: 0.4s;
}

@keyframes typing {
    0%, 100% { opacity: 0.2; }
    50% { opacity: 1; }
}

pre {
    background-color: #f4f4f4;
    border-radius: 4px;
    padding: 10px;
    margin: 10px 0;
    overflow-x: auto;
}

code {
    font-family: 'Courier New', Courier, monospace;
    font-size: 0.9rem;
}

/* Toggle Switch */
.toggle-switch {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 24px;
    margin-left: 10px;
    vertical-align: middle;
}

.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
    border-radius: 24px;
}

.toggle-slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}

input:checked + .toggle-slider {
    background-color: #696cff;
}

input:checked + .toggle-slider:before {
    transform: translateX(20px);
}
    </style>

</head>

<body class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        <?php include('includes/sidebar-main.php'); ?>

        <div class="layout-page">
            <?php include('includes/navbar.php'); ?>
            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class='bx bx-bot text-primary me-2'></i>Cognitive AI Assistant
                                <label class="toggle-switch">
                                    <input type="checkbox" id="contextMode">
                                    <span class="toggle-slider"></span>
                                </label>
                            </h5>
                            <div>
                                <button onclick="showHistory()" class="btn btn-outline-primary me-2">
                                    <i class='bx bx-history me-1'></i> History
                                </button>
                                <button onclick="startNewChat()" class="btn btn-primary">
                                    <i class='bx bx-plus me-1'></i> New Chat
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="chat-container">
                                    <div id="chat-messages" class="flex-grow-1">
                                        <div class="d-flex flex-column align-items-center justify-content-center h-100 bg-light">
                                            <div class="rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center mb-4" style="width: 100px; height: 100px;">
                                                <i class='bx bx-bot text-primary' style="font-size: 3rem;"></i>
                                            </div>
                                            
                                            <h3 class="mb-2">Welcome to Cognitive AI Assistant</h3>
                                            <p class="text-muted">Type a message below to start your conversation</p>
                                        </div>
                                    </div>
                                  <div class="input-group p-3">
                                  <button id="generate-image-btn" class="btn btn-outline-primary">
                                        <i class='bx bx-image-alt me-1'></i> Generate Image
                                  </button>
                                  <input type="text" id="user-input" class="form-control" placeholder="Type your message...">
                                  <button onclick="handleMessageSend()" class="btn btn-primary">
                                        <i class='bx bx-send me-1'></i> Send
                                  </button>                                    
                                </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="offcanvas offcanvas-end" tabindex="-1" id="chatHistoryOffcanvas" aria-labelledby="chatHistoryOffcanvasLabel">
                    <div class="offcanvas-header">
                        <h5 class="offcanvas-title" id="chatHistoryOffcanvasLabel">
                            <i class='bx bx-history me-2'></i>Chat History
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                    </div>
                    <div class="offcanvas-body">
                        <div id="chat-history">
                           
                        </div>
                    </div>
                </div>


                <div class="content-backdrop fade"></div>
            </div>

        </div>
    </div>
    <div class="layout-overlay layout-menu-toggle"></div>
    <script src="js/ai-chat.js">
    </script>
</body>
</html>