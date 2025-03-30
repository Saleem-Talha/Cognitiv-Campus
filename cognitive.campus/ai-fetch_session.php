<?php
require_once 'includes/db-connect.php';

$session_id = $_GET['session_id'] ?? 0;

$query = "
    SELECT cm.sender, cm.message
    FROM ai_chat_messages cm
    WHERE cm.session_id = ?
    ORDER BY cm.created_at ASC
";

$stmt = $db->prepare($query);
if (!$stmt) {
    echo json_encode(['error' => $db->error]);
    exit;
}

$stmt->bind_param("i", $session_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = ['sender' => $row['sender'], 'message' => $row['message']];
}

$stmt->close();
echo json_encode($messages);
?>
