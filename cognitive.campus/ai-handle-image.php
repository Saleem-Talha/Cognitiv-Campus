<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_message = $_POST['message'] ?? '';
    
    if (!empty($user_message)) {
        // Create img/ai directory if it doesn't exist
        $save_dir = 'img/ai';
        if (!file_exists($save_dir)) {
            mkdir($save_dir, 0755, true);
        }
        
        // Escape the user message to prevent command injection
        $escaped_message = escapeshellarg($user_message);
        
        // Use the full path to python if needed
        $command = "python3 ai-image.py $escaped_message 2>&1";
        
        // Capture the output
        $output = shell_exec($command);
        
        // Decode the Python script's JSON response
        $result = json_decode($output, true);
        
        if ($result && isset($result['success']) && $result['success']) {
            // Ensure the image file exists
            $image_path = $result['image'];
            if (file_exists($image_path)) {
                echo json_encode([
                    'success' => true,
                    'image' => $image_path,
                    'cached' => $result['cached'] ?? false
                ]);
                exit;
            }
        }
        
        // If we get here, something went wrong
        echo json_encode([
            'success' => false,
            'error' => $result['error'] ?? 'Failed to generate or save image'
        ]);
        exit;
    }
}

echo json_encode(['success' => false, 'error' => 'No message received']);
?>