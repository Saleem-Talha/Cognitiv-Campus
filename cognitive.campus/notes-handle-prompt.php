<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_message = $_POST['message'] ?? '';
    
    if (!empty($user_message)) {
        // Escape the user message to prevent command injection
        $escaped_message = escapeshellarg($user_message);
        
        // Use the full path to python if needed
        $command = "python3 ai-notes-response-api.py $escaped_message 2>&1";
        
        // Capture the output
        $output = shell_exec($command);
        
        // Output the response directly
        echo $output;
        exit;
    }
}

echo json_encode(['text' => 'Error: No message received', 'image' => null]);
?>