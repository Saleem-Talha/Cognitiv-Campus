<?php
include_once('includes/header.php');
include_once('includes/db-connect.php');
include_once('admin-auth.php'); 

$error_message = "";
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $admin_id = $_SESSION['admin_id'];
    $stmt = $db->prepare("SELECT password FROM admin WHERE id = ?");
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();

    // Logging for debugging
    error_log("Admin ID: " . $admin_id);
    error_log("Stored Password: " . $admin['password']);
    error_log("Current Password Verification: " . (password_verify($current_password, $admin['password']) ? "True" : "False"));

    if ($admin && (password_verify($current_password, $admin['password']) || $current_password === $admin['password'])) {
        if ($new_password === $confirm_password) {
            if (strlen($new_password) < 8) {
                $error_message = "New password must be at least 8 characters long.";
            } else {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_stmt = $db->prepare("UPDATE admin SET password = ? WHERE id = ?");
                $update_stmt->bind_param("si", $hashed_password, $admin_id);
                
                if ($update_stmt->execute()) {
                    $success_message = "Password updated successfully!";
                    error_log("Password updated successfully for admin ID: " . $admin_id);
                } else {
                    $error_message = "Error updating password. Please try again.";
                    error_log("Error updating password for admin ID: " . $admin_id . ". Error: " . $db->error);
                }
                $update_stmt->close();
            }
        } else {
            $error_message = "New passwords do not match.";
        }
    } else {
        $error_message = "Current password is incorrect.";
    }
    $stmt->close();
}
?>

<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        <?php include_once('includes/admin-sidebar.php'); ?>
        <div class="layout-page">
            <?php include_once('includes/navbar.php'); ?>
            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">
                    <h4 class="py-3 mb-4"><span class="text-muted fw-light">Admin /</span> Change Password</h4>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Change Admin Password</h5>
                                    <?php
                                    if (!empty($error_message)) {
                                        echo "<div class='alert alert-danger'>$error_message</div>";
                                    }
                                    if (!empty($success_message)) {
                                        echo "<div class='alert alert-success'>$success_message</div>";
                                    }
                                    ?>
                                    <form method="POST" action="">
                                        <div class="mb-3">
                                            <label for="current_password" class="form-label">Current Password</label>
                                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="new_password" class="form-label">New Password</label>
                                            <input type="password" class="form-control" id="new_password" name="new_password" required minlength="8">
                                        </div>
                                        <div class="mb-3">
                                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="8">
                                        </div>
                                        <button type="submit" class="btn btn-primary">Change Password</button>
                                    </form>
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