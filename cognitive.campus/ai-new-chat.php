<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_message = $_POST['message'] ?? '';
    $user_email = 'saleem@gmail.com';
    $start_new_chat = isset($_POST['start_new_chat']) && $_POST['start_new_chat'] === 'true';

    if (!empty($user_message) && !empty($user_email)) {
        $command = sprintf(
            'python.exe chatbot_api.py "%s" "%s" "%s"',
            escapeshellarg($user_message),
            escapeshellarg($user_email),
            escapeshellarg($start_new_chat ? 'true' : 'false')
        );

        file_put_contents('chat_debug.log', "Command: $command\n", FILE_APPEND);
        $output = shell_exec($command);

        file_put_contents('chat_debug.log', "Output: " . print_r($output, true) . "\n", FILE_APPEND);
        echo json_encode(['response' => $output]);
        exit;
    }
}

echo json_encode(['response' => 'Error: Missing parameters']);
