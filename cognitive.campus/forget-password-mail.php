<?php
// forget-password-mail.php
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function sendPasswordResetEmail($data) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';              // Specify SMTP server (e.g., smtp.gmail.com)
        $mail->SMTPAuth   = true;                            // Enable SMTP authentication
        $mail->Username   = 'saleemtalha967@gmail.com';        // SMTP username
        $mail->Password   = 'vwjz biua zfog fqfa';            // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // Recipients
        $mail->setFrom('your-email@gmail.com', 'Cognitive Campus');
        $mail->addAddress($data['email'], $data['name']);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset OTP - Cognitive Campus';
        
        // Email template
        $emailContent = getPasswordResetTemplate($data);
        $mail->Body = $emailContent['html'];
        $mail->AltBody = $emailContent['text'];
        
        return $mail->send();
    } catch (Exception $e) {
        error_log("Mail Error: " . $mail->ErrorInfo);
        return false;
    }
}

function getPasswordResetTemplate($data) {
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { text-align: center; margin-bottom: 30px; }
            .otp { font-size: 24px; text-align: center; padding: 20px; 
                  background-color: #f5f5f5; margin: 20px 0; letter-spacing: 3px; }
            .footer { text-align: center; font-size: 12px; color: #666; margin-top: 30px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h2>Password Reset Request</h2>
            </div>
            <p>Hello ' . htmlspecialchars($data['name']) . ',</p>
            <p>We received a request to reset your password for your Cognitive Campus account. 
               Please use the following OTP to complete your password reset:</p>
            <div class="otp">' . $data['otp'] . '</div>
            <p>This OTP will expire in 15 minutes for security reasons.</p>
            <p>If you did not request this password reset, please ignore this email or contact support 
               if you have concerns.</p>
            <div class="footer">
                <p>This is an automated message, please do not reply to this email.</p>
                <p>&copy; ' . date('Y') . ' Cognitive Campus. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>';
    
    $text = "Password Reset Request\n\n" .
            "Hello " . $data['name'] . ",\n\n" .
            "We received a request to reset your password for your Cognitive Campus account.\n" .
            "Please use the following OTP to complete your password reset:\n\n" .
            $data['otp'] . "\n\n" .
            "This OTP will expire in 15 minutes for security reasons.\n\n" .
            "If you did not request this password reset, please ignore this email or contact support if you have concerns.";
    
    return [
        'html' => $html,
        'text' => $text
    ];
}