<?php
header('Content-Type: application/json');
include_once('includes/validation.php');
$userInfo = getUserInfo();
$userEmail = $userInfo['email'] ?? null;


// Ensure only PDF files are uploaded
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['pdfFile'])) {
    $allowedTypes = ['application/pdf'];
    $maxFileSize = 10 * 1024 * 1024; // 10MB max

    $fileType = $_FILES['pdfFile']['type'];
    $fileSize = $_FILES['pdfFile']['size'];

    if (in_array($fileType, $allowedTypes) && $fileSize <= $maxFileSize) {
        $uploadDir = 'uploads/';

        // Create uploads directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = $uploadDir . uniqid() . '_' . basename($_FILES['pdfFile']['name']);

        if (move_uploaded_file($_FILES['pdfFile']['tmp_name'], $filename)) {
            // Call Python script to summarize PDF, passing user email
            $command = escapeshellcmd("python3 ai_summarize.py " . escapeshellarg($filename) . " " . escapeshellarg($userEmail));
            $output = shell_exec($command);

            // Return summary as JSON
            echo json_encode([
                'summary' => trim($output),
                'filename' => $filename,
                'userEmail' => $userEmail
            ]);
            exit;
        }
    }
}

// If upload fails
echo json_encode([
    'summary' => 'Error uploading or processing PDF',
    'filename' => null,
    'userEmail' => $userEmail
]);
?>