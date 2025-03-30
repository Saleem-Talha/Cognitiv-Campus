
<?php include_once('includes/header.php'); ?>
<div class="layout-wrapper layout-content-navbar">
<div class="layout-container">
<?php include_once('includes/sidebar-main.php'); ?>
<div class="layout-page">
<?php include_once('includes/navbar.php'); ?>
<div class="content-wrapper">

<?php 
require_once 'includes/db-connect.php';
$project_id = $_GET['project_id'];
if(!isset($project_id)){
    header('location: project.php');
}

$select_members = $db->query("SELECT * FROM project_requests WHERE project_id = '$project_id' AND status = 'Accepted'");
if($select_members->num_rows){
    $members = [];
    while($row = $select_members->fetch_assoc()){
        $members[] = $row;
    }
}

$userInfo = getUserInfo();
$userEmail = $userInfo['email']; // This line is missing

// Check if the current user is a member or owner
$current_user_email = $userEmail;
$is_member = false;
foreach ($members as $member) {
    if ($member['email'] == $current_user_email || $member['ownerEmail'] == $userEmail) {
        $is_member = true;
        break;
    }
}

if (!$is_member) {
    echo "You don't have access to this chat.";
    exit;
}

?>



<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="py-3 mb-4"><span class="text-muted fw-light">Project /</span> Group Chat</h4>

    <div class="row">
        <div class="col-md-12">
            <div class="card chat-card">
                <div class="card-body">
                    <style>
                        .chat-container {
                            height: 500px;
                            overflow-y: auto;
                            padding: 1rem;
                        }

                        .message {
                            margin-bottom: 1rem;
                            max-width: 70%;
                            clear: both;
                        }


                        .message.sent {
                            float: right;
                        }

                        .message.received {
                            float: left;
                        }

                        .message-wrapper {
                            display: flex;
                            align-items: flex-start;
                            gap: 0.5rem;
                        }

                        .message.sent .message-wrapper {
                            flex-direction: row-reverse;
                        }

                        .message-content {
                            border-radius: 1rem;
                            padding: 0.75rem;
                            position: relative;
                        }

                        .message.sent .message-content {
                            background-color: var(--bs-primary);
                            color: white;
                        }

                        .message.received .message-content {
                            background-color: #f0f2f5;
                        }

                        .user-avatar {
                            width: 40px;
                            height: 40px;
                            border-radius: 50%;
                            object-fit: cover;
                        }

                        .message-header {
                            margin-bottom: 0.5rem;
                            display: flex;
                            align-items: center;
                            gap: 0.5rem;
                        }

                        .message.sent .message-header {
                            flex-direction: row-reverse;
                        }

                        .message-timestamp {
                            font-size: 0.75rem;
                            opacity: 0.7;
                            display: block;
                            margin-top: 0.25rem;
                        }

                        .replied-message {
                            border-left: 3px solid var(--bs-primary);
                            padding-left: 0.75rem;
                            margin-bottom: 0.5rem;
                            opacity: 0.8;
                        }

                        .message.sent .replied-message {
                            border-left-color: white;
                        }

                        .message-actions {
                            opacity: 0;
                            transition: opacity 0.2s;
                        }

                        .message:hover .message-actions {
                            opacity: 1;
                        }

                        .reply-box {
                            background-color: #f8f9fa;
                            border-radius: 0.5rem;
                            padding: 0.75rem;
                            margin-bottom: 1rem;
                            display: flex;
                            justify-content: space-between;
                            align-items: center;
                        }
                    </style>
                    <div id="chat-messages" class="chat-container"></div>
                    <div id="reply-box" class="reply-box" style="display: none;">
                        <div id="reply-content"></div>
                        <button id="cancel-reply" class="btn btn-sm btn-light rounded-circle">
                            <i class="bx bx-x"></i>
                        </button>
                    </div>
                    <div class="input-group mt-3">
                        <input type="text" id="message-input" class="form-control rounded-pill" placeholder="Type your message...">
                        <label for="file-input" class="btn btn-light rounded-circle ms-2">
                            <i class="bx bx-paperclip"></i>
                        </label>
                        <button id="emoji-button" class="btn btn-light rounded-circle ms-2">
                            <i class="bx bx-smile"></i>
                        </button>
                        <button class="btn btn-primary rounded-pill ms-2" id="send-button">
                            <i class="bx bx-send"></i>
                        </button>
                        
                        <input type="file" id="file-input" style="display: none;">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="content-backdrop fade"></div>
</div>
</div>
</div>

<div class="layout-overlay layout-menu-toggle"></div>
</div>

<?php include_once('includes/footer-links.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/@joeattardi/emoji-button@3.0.3/dist/index.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/@joeattardi/emoji-button@3.0.3/dist/index.min.js"></script>

<script>
$(document).ready(function() {
    const chatMessages = $('#chat-messages');
    const messageInput = $('#message-input');
    const sendButton = $('#send-button');
    const fileInput = $('#file-input');
    const replyBox = $('#reply-box');
    const replyContent = $('#reply-content');
    const cancelReply = $('#cancel-reply');
    const projectId = <?php echo json_encode($project_id); ?>;
    let replyTo = null;

    // Emoji picker setup
    const picker = new EmojiButton();
    const emojiButton = $('#emoji-button');

    picker.on('emoji', emoji => {
        messageInput.val(messageInput.val() + emoji);
    });

    emojiButton.on('click', () => {
        picker.togglePicker(emojiButton[0]);
    });

    function getProfileImageUrl(picture) {
        if (!picture) return 'img/pfps/default.jpg';
        
        // Check if the picture path already includes 'img/pfps/'
        if (picture.startsWith('img/pfps/')) {
            return picture;
        }
        
        // Check if it's just the filename
        if (!picture.includes('/')) {
            return `img/pfps/${picture}`;
        }
        
        return picture;
    }

    function loadMessages() {
        $.ajax({
            url: 'project-get-messages.php',
            method: 'GET',
            data: { project_id: projectId },
            dataType: 'json',
            success: function(data) {
                if (data.error) {
                    console.error('Server error:', data.error);
                    return;
                }
                chatMessages.empty();
                $.each(data, function(index, message) {
                    let messageContent;
                    if (message.type === 'attachment') {
                        const fileExt = message.message.split('.').pop().toLowerCase();
                        const imageExts = ['jpg', 'jpeg', 'png', 'gif'];
                        if (imageExts.includes(fileExt)) {
                            messageContent = `<img src="attachments/${message.message}" class="img-fluid rounded" alt="Attachment">`;
                        } else {
                            messageContent = `<a href="attachments/${message.message}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bx bx-download"></i> Download ${message.message}</a>`;
                        }
                    } else {
                        messageContent = message.message;
                    }

                    const replyHtml = message.reply_to ? `
                        <div class="replied-message">
                            <small><i class="bx bx-reply"></i> Replying to ${message.reply_to_sender}</small>
                            <p>${message.reply_to_message}</p>
                        </div>
                    ` : '';

                    const profileImageUrl = getProfileImageUrl(message.sender_picture);

                    const messageElement = $(`
                        <div class="message ${message.sender_id === '<?php echo $userEmail; ?>' ? 'sent' : 'received'}" data-id="${message.id}">
                            <div class="message-wrapper d-flex align-items-center">
                                ${message.sender_id === '<?php echo $userEmail; ?>' ? `
                                <div class="message-actions left-actions d-flex align-items-center me-2">
                                    <button class="btn btn-sm btn-light reply-btn">
                                        <i class="bx bx-reply"></i> Reply
                                    </button>
                                </div>
                                ` : ''}
                                <div class="message-content">
                                    <div class="message-header">
                                        <img src="${profileImageUrl}" class="user-avatar" alt="${message.sender_name}" onerror="this.src='img/pfps/default.jpg'">
                                        <strong>${message.sender_name}</strong>
                                    </div>
                                    ${replyHtml}
                                    <div class="message-body">
                                        ${messageContent}
                                    </div>
                                    <span class="message-timestamp">${message.formatted_date}</span>
                                </div>
                                ${message.sender_id !== '<?php echo $userEmail; ?>' ? `
                                <div class="message-actions right-actions d-flex align-items-center ms-2">
                                    <button class="btn btn-sm btn-light reply-btn">
                                        <i class="bx bx-reply"></i> Reply
                                    </button>
                                </div>
                                ` : ''}
                            </div>
                        </div>
                    `);
                    chatMessages.append(messageElement);
                });
                chatMessages.scrollTop(chatMessages[0].scrollHeight);
            },
            error: function(xhr, status, error) {
                console.error('Error loading messages:', error);
                console.log('Response Text:', xhr.responseText);
            }
        });
    }

    function sendMessage(message, file) {
        const formData = new FormData();
        formData.append('project_id', projectId);
        formData.append('message', message);
        if (file) {
            formData.append('file', file);
        }
        if (replyTo) {
            formData.append('reply_to', replyTo);
        }

        $.ajax({
            url: 'project-send-message.php',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(data) {
                if (data.success) {
                    messageInput.val('');
                    fileInput.val('');
                    replyTo = null;
                    replyBox.hide();
                    loadMessages();
                } else {
                    alert('Failed to send message: ' + data.error);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error sending message:', error);
            }
        });
    }

    sendButton.on('click', function() {
        const message = messageInput.val().trim();
        const file = fileInput[0].files[0];
        if (message || file) {
            sendMessage(message, file);
        }
    });

    fileInput.on('change', function() {
        const file = fileInput[0].files[0];
        if (file) {
            sendMessage('', file);
        }
    });

    messageInput.on('keypress', function(e) {
        if (e.which === 13) {
            sendButton.click();
        }
    });

    chatMessages.on('click', '.reply-btn', function() {
        const messageElement = $(this).closest('.message');
        const messageId = messageElement.data('id');
        const senderName = messageElement.find('.message-header strong').text();
        const messageContent = messageElement.find('.message-body').text().trim();

        replyTo = messageId;
        replyContent.html(`<strong>Replying to ${senderName}:</strong> ${messageContent}`);
        replyBox.show();
    });

    cancelReply.on('click', function() {
        replyTo = null;
        replyBox.hide();
    });

    // Load messages every 5 seconds
    loadMessages();
    setInterval(loadMessages, 500);
});
</script>