<?php
// File: get_ai_analytics.php
header('Content-Type: application/json');

try {
    // Get user session information
    require_once 'includes/validation.php';
    $userInfo = getUserInfo();
    $userEmail = $userInfo['email'];
    
    if (empty($userEmail)) {
        throw new Exception('User email not available');
    }

    // Escape shell arguments to prevent injection
    $safe_email = escapeshellarg($userEmail);
    
    // Set path to Python script
    $scriptDir = 'E:\xampp\htdocs\cognitive.campus';
    $pythonScript = $scriptDir . DIRECTORY_SEPARATOR . 'ai_student_grade_insights.py';
    
    // Build and execute command
    $command = sprintf('python "%s" %s', str_replace('\\', '/', $pythonScript), $safe_email);
    exec($command . " 2>&1", $output, $return_var);
    
    if ($return_var !== 0) {
        throw new Exception('Error executing Python script: ' . implode("\n", $output));
    }
    
    echo json_encode(['success' => true, 'message' => 'AI insights generated successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
exit;
?>