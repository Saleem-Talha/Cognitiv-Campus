<?php
require_once 'includes/db-connect.php';
require_once 'admin-auth.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_ids = $_POST['users'];
    $offer_id = $_POST['offer'];

    // Fetch offer details
    $stmt = $db->prepare("SELECT * FROM offers WHERE id = ?");
    $stmt->bind_param("i", $offer_id);
    $stmt->execute();
    $offer = $stmt->get_result()->fetch_assoc();

    // Fetch user details
    $user_ids_str = implode(',', array_map('intval', $user_ids));
    $users = $db->query("SELECT email, name FROM users WHERE id IN ($user_ids_str)");

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';              
        $mail->SMTPAuth   = true;
        $mail->Username   = 'saleemtalha967@gmail.com';       
        $mail->Password   = 'vwjz biua zfog fqfa';            
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Sender
        $mail->setFrom('from@example.com', 'Cognitive Campus');

        // Content
        $mail->isHTML(true);
        $mail->Subject = "Exclusive Offer: Upgrade to {$offer['plan']} Plan";

        while ($user = $users->fetch_assoc()) {
            $mail->clearAddresses();
            $mail->addAddress($user['email'], $user['name']);

            $savings = $offer['regular_price'] - $offer['discount_price'];
            $savingsPercentage = round(($savings / $offer['regular_price']) * 100);

            $mailContent = "
            <html>
            <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                <h2>Hello {$user['name']},</h2>
                <p>We're excited to bring you an exclusive offer on our {$offer['plan']} plan!</p>
                <h3 style='color: #4CAF50;'>Limited Time Offer:</h3>
                <ul>
                    <li>Plan: <strong>{$offer['plan']}</strong></li>
                    <li>You save: <strong>${$savings} ({$savingsPercentage}% off)</strong></li>
                    <li>Offer valid: {$offer['start_date']} to {$offer['end_date']}</li>
                </ul>
                <p>This is your chance to unlock premium features and take your experience to the next level!</p>
                
                <p>Don't miss out on this amazing opportunity to enhance your learning journey!</p>
                <p style='text-align: center;'>
                    <a href='http://localhost:/cognitive.campus/' style='background-color: #4CAF50; color: white; padding: 12px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Upgrade Now</a>
                </p>
                <p>If you have any questions about this offer or need assistance, please don't hesitate to contact our support team.</p>
                <p>Best regards,<br>The Cognitive Campus Team</p>
            </body>
            </html>
            ";

            $mail->Body = $mailContent;
            $mail->AltBody = strip_tags(str_replace(['<br>', '</p>'], "\n", $mailContent));

            $mail->send();
        }

        header("Location: admin-offers.php?success=1");
        exit();
    } catch (Exception $e) {
        header("Location: admin-offers.php?error=" . urlencode($mail->ErrorInfo));
        exit();
    }
}