<?php
require_once 'includes/db-connect.php';
require_once 'includes/validation.php';

// Ensure user is logged in
$userInfo = getUserInfo();
if (!$userInfo) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$email = $userInfo['email'];

// Validate email input (optional but recommended)
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['error' => 'Invalid email']);
    exit;
}

$query = "
    SELECT cs.id, cs.title, cs.created_at
    FROM users u
    JOIN ai_chat_sessions cs ON u.id = cs.user_id
    WHERE u.email = ?
    ORDER BY cs.created_at DESC
    LIMIT 50  -- Limit to prevent overwhelming the user
";

$stmt = $db->prepare($query);
if (!$stmt) {
    echo json_encode(['error' => 'Database error']);
    exit;
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

$sessions = [];
while ($row = $result->fetch_assoc()) {
    // Format the date 
    $formattedDate = date('M d, Y H:i', strtotime($row['created_at']));
    $sessions[] = [
        'id' => $row['id'], 
        'title' => $row['title'], 
        'date' => $formattedDate
    ];
}

$stmt->close();

// If no sessions found, return a message
if (empty($sessions)) {
    echo json_encode(['message' => 'No chat history found']);
} else {
    echo json_encode($sessions);
}
?>