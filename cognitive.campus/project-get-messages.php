<?php

require_once 'includes/db-connect.php';
require_once 'includes/validation.php';

try {
    $userInfo = getUserInfo();
    if (!$userInfo) {
        throw new Exception('User not authenticated');
    }
    
    $project_id = $_GET['project_id'];

    $stmt = $db->prepare("
        SELECT cm.*, u.picture AS sender_picture, u.name AS sender_name,
               rcm.message AS reply_to_message, 
               ru.email AS reply_to_sender, ru.name AS reply_to_name
        FROM chat_messages cm
        LEFT JOIN users u ON cm.sender_id = u.email
        LEFT JOIN chat_messages rcm ON cm.reply_to = rcm.id
        LEFT JOIN users ru ON rcm.sender_id = ru.email
        WHERE cm.project_id = ?
        ORDER BY cm.datetime DESC
        LIMIT 50
    ");
    $stmt->bind_param("i", $project_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $row['formatted_date'] = date('d M Y', strtotime($row['datetime']));
        $messages[] = $row;
    }

    echo json_encode(array_reverse($messages));
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}