<?php

require_once 'includes/db-connect.php';
require_once 'includes/validation.php';

try {
    $userInfo = getUserInfo();
    if (!$userInfo) {
        throw new Exception('User not authenticated');
    }
    
    $sender_id = $userInfo['email'];
    $project_id = $_POST['project_id'];
    $message = $_POST['message'];
    $type = isset($_FILES['file']) ? 'attachment' : 'text';
    $reply_to = isset($_POST['reply_to']) ? $_POST['reply_to'] : null;
    
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'];
        $file_ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        
        if (!in_array($file_ext, $allowed_ext)) {
            throw new Exception('Invalid file type');
        }

        $file_name = uniqid() . '.' . $file_ext;
        $upload_path = 'attachments/' . $file_name;

        if (!move_uploaded_file($_FILES['file']['tmp_name'], $upload_path)) {
            throw new Exception('Failed to upload file');
        }

        $type = 'attachment';
        $message = $file_name;
    }

    if (empty($message) && $type !== 'attachment') {
        throw new Exception('Message cannot be empty');
    }

    $stmt = $db->prepare("INSERT INTO chat_messages (sender_id, project_id, message, datetime, type, reply_to) 
                         VALUES (?, ?, ?, NOW(), ?, ?)");
    $stmt->bind_param("sissi", $sender_id, $project_id, $message, $type, $reply_to);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Failed to send message');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}