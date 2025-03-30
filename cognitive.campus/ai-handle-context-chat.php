<?php
include("includes/validation.php");
$userInfo = getUserInfo();
$userEmail = $userInfo['email'];
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_message = $_POST['message'] ?? '';
    $user_email = $userEmail;
    $start_new_chat = isset($_POST['start_new_chat']) && $_POST['start_new_chat'] === 'true';
    
    if (!empty($user_message)) {
        $command = "python.exe ai-context-chatbot-api.py \"$user_message\" \"$user_email\" " . ($start_new_chat ? "true" : "false");
        
        // Log the command
        file_put_contents('chat_debug.log', "Command: $command\n", FILE_APPEND);
        
        $output = shell_exec($command);
        
        // Log the output
        file_put_contents('chat_debug.log', "Output: " . print_r($output, true) . "\n", FILE_APPEND);
        
        echo json_encode(['response' => $output]);
        exit;
    }
}

echo json_encode(['response' => 'Error: No message received']);
?>