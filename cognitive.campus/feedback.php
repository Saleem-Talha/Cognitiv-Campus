<?php include_once('includes/header.php'); ?>
<div class="layout-wrapper layout-content-navbar">
<div class="layout-container">
<?php include_once('includes/sidebar-main.php'); ?>
<div class="layout-page">
<?php include_once('includes/navbar.php'); ?>
<div class="content-wrapper">

<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['delete_feedback'])) {
        // Existing delete feedback code...
    } else {
        $message = $_POST['message'];
        $rating = $_POST['rating'];
        $datetime = date('Y-m-d H:i:s');

        $query = "INSERT INTO feedback (userName, userEmail, userPicture, message, rating, datetime) 
                  VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        $stmt->bind_param("ssssss", $userName, $userEmail, $userProfile, $message, $rating, $datetime);
        
        if ($stmt->execute()) {
            // Send thank you email
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
                $mail->addAddress($userEmail, $userName);

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Thank You for Your Feedback';
                $mail->Body    = "
                    <h2>Dear $userName,</h2>
                    <p>Thank you for taking the time to provide us with your valuable feedback. We greatly appreciate your input as it helps us improve our services and better meet the needs of our users.</p>
                    <p>Your feedback:</p>
                    <blockquote>$message</blockquote>
                    <p>Rating: " . str_repeat('★', $rating) . str_repeat('☆', 5 - $rating) . "</p>
                    <p>We assure you that your feedback will be carefully reviewed and considered as we continue to enhance our services.</p>
                    <p>If you have any further questions or comments, please don't hesitate to reach out to us.</p>
                    <p>Thank you again for your support!</p>
                    <p>Best regards,<br>Cognitive Campus Team</p>
                ";

                $mail->send();
                echo "<script>
                    swal({
                        title: 'Success!',
                        text: 'Feedback submitted successfully! A thank you email has been sent to your email address.',
                        icon: 'success',
                        button: 'OK',
                    });
                </script>";
            } catch (Exception $e) {
                echo "<script>
                    swal({
                        title: 'Partial Success',
                        text: 'Feedback submitted successfully, but we couldn\'t send you a thank you email. Error: " . $mail->ErrorInfo . "',
                        icon: 'warning',
                        button: 'OK',
                    });
                </script>";
            }
        } else {
            echo "<div class='alert alert-danger mt-3'>Error submitting feedback. Please try again.</div>";
        }
    }
}
?>

<div class="container-xxl flex-grow-1 container-p-y">
<h4 class="py-3 mb-4"><span class="text-muted fw-light">Feedback /</span> Give Feedbacks</h4>


<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Give Feedback</h5>
                <form action="" method="POST">
                    <div class="mb-3">
                        <label for="message" class="form-label">Message</label>
                        <textarea class="form-control" id="message" name="message" rows="3" required></textarea>
                    </div>
                    <div class="mb-3 form-floating">
                        <select class="form-select" id="rating" name="rating" required>
                        <option value="" selected disabled>Choose a rating</option>
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?> star<?php echo $i > 1 ? 's' : ''; ?></option>
                            <?php endfor; ?>
                        </select>
                        <label for="rating" class="form-label">Rating</label>
                        </div>
                    <button type="submit" class="btn btn-primary">Submit Feedback</button>
                </form>

                <?php
                if ($_SERVER["REQUEST_METHOD"] == "POST") {
                    if (isset($_POST['delete_feedback'])) {
                        $feedback_id = $_POST['feedback_id'];
                        $delete_query = "DELETE FROM feedback WHERE id = ? AND userEmail = ?";
                        $delete_stmt = $db->prepare($delete_query);
                        $delete_stmt->bind_param("is", $feedback_id, $userEmail);
                        
                        if ($delete_stmt->execute()) {
                            echo "<script>
                                swal({
                                    title: 'Success!',
                                    text: 'Feedback deleted successfully!',
                                    icon: 'success',
                                    button: 'OK',
                                });
                            </script>";
                        } else {
                            echo "<div class='alert alert-danger mt-3'>Error deleting feedback. Please try again.</div>";
                        }
                    } else {
                        $message = $_POST['message'];
                        $rating = $_POST['rating'];
                        $datetime = date('Y-m-d H:i:s');

                        $query = "INSERT INTO feedback (userName, userEmail, userPicture, message, rating, datetime) 
                                  VALUES (?, ?, ?, ?, ?, ?)";
                        $stmt = $db->prepare($query);
                        $stmt->bind_param("ssssss", $userName, $userEmail, $userProfile, $message, $rating, $datetime);
                        
                        if ($stmt->execute()) {
                            echo "<script>
                                swal({
                                    title: 'Success!',
                                    text: 'Feedback submitted successfully!',
                                    icon: 'success',
                                    button: 'OK',
                                });
                            </script>";
                        } else {
                            echo "<div class='alert alert-danger mt-3'>Error submitting feedback. Please try again.</div>";
                        }
                    }
                }
                ?>
            </div>
        </div>
    </div>


    <div class="col-md-12 mt-5">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Previous Feedbacks</h5>
            </div>
            <div class="card-body">

                <?php
                $stmt = $db->prepare("SELECT * FROM feedback WHERE userEmail = ? ORDER BY datetime DESC");
                $stmt->bind_param("s", $userEmail);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0):
                    ?>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Message</th>
                                <th>Rating</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while ($row = $result->fetch_assoc()): 
                            $formatted_date = date('d M Y', strtotime($row['datetime']));
                            $rating      = $row['rating'];
                            $message     = $row['message'];
                            $userName    = $row['userName'];
                            $userEmail   = $row['userEmail'];
                            $userPicture = $row['userPicture'];
                            $feedback_id = $row['id'];
                            ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="<?= $userPicture ?>" alt="<?= $userName ?>" class="rounded-circle me-3" width="32" height="32">
                                        <div>
                                            <div><?= $userName ?></div>
                                            <small class="text-muted"><?= $userEmail ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td width="600">
                                    <details><summary>Your Message</summary><?= $message ?></details>
                                </td>
                                <td><?= str_repeat('★', $rating) . str_repeat('☆', 5 - $rating) ?></td>
                                <td><?= $formatted_date ?></td>
                                <td>
                                    <form action="" method="POST" onsubmit="return confirm('Are you sure you want to delete this feedback?');">
                                        <input type="hidden" name="feedback_id" value="<?= $feedback_id ?>">
                                        <button type="submit" name="delete_feedback" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-center">No feedbacks available.</p>
                <?php endif; ?>

            </div>
        </div>
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