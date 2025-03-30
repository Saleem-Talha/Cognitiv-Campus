<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = $_POST['content'] ?? '';
    
    if (!empty($content)) {
        // Escape the content to prevent command injection
        $escaped_content = escapeshellarg($content);
        
        // Use the full path to python if needed
        $command = "python3 ai-notes-summarize-api.py $escaped_content 2>&1";
        
        // Capture the output
        $output = shell_exec($command);
        
        // Output the response directly
        echo $output;
        exit;
    }
}

echo json_encode(['summary' => 'Error: No content received', 'status' => 'error']);
?>