<?php
include_once('includes/header.php');
include_once('includes/db-connect.php');
require_once 'admin-auth.php'; 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$recipient_email = isset($_GET['email']) ? $_GET['email'] : '';
$recipient_name = isset($_GET['name']) ? $_GET['name'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = $_POST['subject'];
    $message = $_POST['message'];

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

        // Recipients
        $mail->setFrom('from@example.com', 'Cognitive Campus');
        $mail->addAddress($recipient_email, $recipient_name);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;

        $mail->send();
        $success_message = "Email sent successfully!";

        // Add notice to the database
        $notice_message = "Mail Received regarding a feedback! Go check out";
        $type = "Feedback";
        $insert_notification = $db->query("INSERT INTO notice (userEmail, message, type) VALUES ('$recipient_email', '$notice_message', '$type')");
        
        if (!$insert_notification) {
            error_log("Failed to insert notification: " . $db->error);
        }

    } catch (Exception $e) {
        $error_message = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
?>

<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        <?php include_once('includes/admin-sidebar.php'); ?>
        <div class="layout-page">
            <?php include_once('includes/navbar.php'); ?>
            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">
                    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Admin /</span> Compose Email</h4>

                    <?php if (isset($success_message)): ?>
                        <div class="alert alert-primary alert-dismissible" role="alert">
                            <?php echo $success_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger alert-dismissible" role="alert">
                            <?php echo $error_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <div class="card">
                        <h5 class="card-header">Compose Email</h5>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="recipient" class="form-label">To:</label>
                                    <input type="text" class="form-control" id="recipient" value="<?php echo htmlspecialchars($recipient_name . ' <' . $recipient_email . '>'); ?>" readonly>
                                </div>
                                <div class="mb-3">
                                    <label for="subject" class="form-label">Subject:</label>
                                    <input type="text" class="form-control" id="subject" name="subject" required>
                                </div>
                                <div class="mb-3">
                                    <label for="message" class="form-label">Message:</label>
                                    <textarea class="form-control" id="message" name="message" rows="10" required></textarea>
                                </div>
                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary me-2 btn-sm">
                                        <i class="bx bx-send me-1"></i> Send Email
                                    </button>
                                    <a href="feedback-management.php" class="btn btn-outline-primary btn-sm">
                                        <i class="bx bx-x me-1"></i> Cancel
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <?php include_once('includes/footer.php'); ?>
                <div class="content-backdrop fade"></div>
            </div>
        </div>
    </div>
    <div class="layout-overlay layout-menu-toggle"></div>
</div>

<?php include_once('includes/footer-links.php'); ?>

<script>
document.addEventListener('DOMContentLoaded', (event) => {
    // Initialize CKEditor
    ClassicEditor
        .create(document.querySelector('#message'))
        .catch(error => {
            console.error(error);
        });
});
</script>