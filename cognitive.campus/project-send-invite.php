<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
require_once("includes/db-connect.php");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $toEmail = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $fromEmail = $_SESSION['user_email'] ?? 'noreply@yourwebsite.com';
    $fromName = $_SESSION['user_name'] ?? 'Cognitive Campus';

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';              // Specify SMTP server (e.g., smtp.gmail.com)
        $mail->SMTPAuth   = true;                            // Enable SMTP authentication
        $mail->Username   = 'saleemtalha967@gmail.com';        // SMTP username
        $mail->Password   = 'vwjz biua zfog fqfa';            // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($toEmail);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Invitation to Join Cognitive Campus';

        // Email body
        $body = "
        <html>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <h2>You've Been Invited!</h2>
            <p>Hello,</p>
            <p>We're excited to invite you to join Cognitive Campus and start working on the project with your. Our team is using this powerful tool to collaborate, track progress, and achieve our goals efficiently.</p>
            <p>By joining, you'll have access to:</p>
            <ul>
                <li>Real-time project updates</li>
                <li>Task management tools</li>
                <li>Team communication features</li>
                <li>File sharing capabilities</li>
            </ul>
            <p>To get started, simply click the link below to create your account:</p>
            <p><a href='http://localhost/cognitive.campus/?invite=" . urlencode($toEmail) . "' style='background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Join Now</a></p>
            <p>If you have any questions, please don't hesitate to reach out.</p>
            <p>We look forward to collaborating with you!</p>
            <p>Best regards,<br>{$fromName}</p>
        </body>
        </html>
        ";

        $mail->Body = $body;
        $mail->AltBody = strip_tags(str_replace(['<br>', '</p>'], "\n", $body));

        $mail->send();
        $response['success'] = true;
        $response['message'] = 'Invitation sent successfully';
    } catch (Exception $e) {
        $response['message'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
} else {
    $response['message'] = 'Invalid request';
}

header('Content-Type: application/json');
echo json_encode($response);
?>