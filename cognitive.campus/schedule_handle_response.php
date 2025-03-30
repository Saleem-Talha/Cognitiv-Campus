<?php
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $email = $_POST['email'] ?? '';
    
    if (empty($email)) {
        throw new Exception('No email provided');
    }

    // Escape shell arguments to prevent injection
    $safe_email = escapeshellarg($email);
    
    // Use full path to Python interpreter if needed
    $command = "python ai_schedule_api.py $safe_email 2>&1";
    $output = shell_exec($command);

    // Trim any potential whitespace or unexpected characters
    $output = trim($output);

    // Validate JSON
    $result = json_decode($output, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON response: ' . $output);
    }

    echo json_encode($result);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
exit;
?>