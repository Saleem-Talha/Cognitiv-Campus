<?php
require_once 'includes/header.php';
require_once 'includes/db-connect.php';
require_once 'includes/validation.php';

if (!isAuthenticated()) {
    header('Location: index.php');
    exit();
}

$userInfo = getUserInfo();
$msg = '';
$msgType = '';

// Handle password change
if (isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Get user's current password hash
    $stmt = $db->prepare("SELECT password FROM users WHERE email = ?");
    $stmt->bind_param("s", $userInfo['email']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    // Verify current password
    if (password_verify($currentPassword, $user['password'])) {
        // Validate new password
        if (strlen($newPassword) >= 8 && 
            preg_match('/[a-z]/', $newPassword) && 
            preg_match('/[0-9\W]/', $newPassword)) {
            
            if ($newPassword === $confirmPassword) {
                $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
                $updateStmt = $db->prepare("UPDATE users SET password = ? WHERE email = ?");
                $updateStmt->bind_param("ss", $passwordHash, $userInfo['email']);
                
                if ($updateStmt->execute()) {
                    $msg = "Password updated successfully!";
                    $msgType = "success";
                } else {
                    $msg = "Error updating password.";
                    $msgType = "danger";
                }
            } else {
                $msg = "New passwords do not match.";
                $msgType = "danger";
            }
        } else {
            $msg = "New password does not meet requirements.";
            $msgType = "danger";
        }
    } else {
        $msg = "Current password is incorrect.";
        $msgType = "danger";
    }
}

// Handle 2FA toggle
if (isset($_POST['enable_2fa'])) {
    // In a real implementation, you would:
    // 1. Generate a secret key
    // 2. Show QR code
    // 3. Verify first 2FA code
    // 4. Enable 2FA in database
    $msg = "2FA functionality would be enabled here.";
    $msgType = "info";
}
?>

<!-- Layout wrapper -->
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        <!-- Menu -->
        <?php include 'includes/sidebar-main.php'; ?>
        <!-- / Menu -->

        <!-- Layout container -->
        <div class="layout-page">
            <!-- Navbar -->
            <?php include 'includes/navbar.php'; ?>
            <?php include_once('includes/settings-bar.php'); ?>
            <!-- / Navbar -->

            <!-- Content wrapper -->
            <div class="content-wrapper">
                <!-- Content -->
                <div class="container-xxl flex-grow-1 container-p-y">
                    <?php if ($msg): ?>
                    <div class="alert alert-<?php echo $msgType; ?> alert-dismissible fade show">
                        <?php echo $msg; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>

                    <!-- Password Change -->
                    <div class="card mb-4">
                        <h5 class="card-header">Change Password</h5>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3 form-password-toggle">
                                    <label class="form-label">Current Password</label>
                                    <div class="input-group input-group-merge">
                                        <input type="password" class="form-control" name="current_password" 
                                            placeholder="············" required />
                                        <span class="input-group-text cursor-pointer">
                                            <i class="bx bx-hide"></i>
                                        </span>
                                    </div>
                                </div>

                                <div class="mb-3 form-password-toggle">
                                    <label class="form-label">New Password</label>
                                    <div class="input-group input-group-merge">
                                        <input type="password" class="form-control" name="new_password" 
                                            placeholder="············" required />
                                        <span class="input-group-text cursor-pointer">
                                            <i class="bx bx-hide"></i>
                                        </span>
                                    </div>
                                </div>

                                <div class="mb-3 form-password-toggle">
                                    <label class="form-label">Confirm New Password</label>
                                    <div class="input-group input-group-merge">
                                        <input type="password" class="form-control" name="confirm_password" 
                                            placeholder="············" required />
                                        <span class="input-group-text cursor-pointer">
                                            <i class="bx bx-hide"></i>
                                        </span>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <h6 class="mb-2">Password Requirements:</h6>
                                    <ul class="ps-3 mb-0">
                                        <li class="mb-1">Minimum 8 characters long - the more, the better</li>
                                        <li class="mb-1">At least one lowercase character</li>
                                        <li>At least one number, symbol, or whitespace character</li>
                                    </ul>
                                </div>

                                <div class="mt-2">
                                    <button type="submit" name="change_password" class="btn btn-primary me-2">Save changes</button>
                                    <button type="reset" class="btn btn-outline-secondary">Reset</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!-- /Password Change -->

                    <!-- Two Steps Verification -->
                    <?php
require_once 'includes/validation.php';
require_once 'includes/db-connect.php';

// Get user information from the validation system
$userInfo = getUserInfo();

if (!$userInfo) {
    header('Location: index.php');
    exit();
}

// Fetch complete user data including 2FA status
$stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $userInfo['email']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Handle 2FA toggle
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['enable_2fa'])) {
        $stmt = $db->prepare("UPDATE users SET two_factor_enabled = 1 WHERE email = ?");
        $stmt->bind_param("s", $userInfo['email']);
        $stmt->execute();
        // Refresh page to show updated status
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    } elseif (isset($_POST['disable_2fa'])) {
        $stmt = $db->prepare("UPDATE users SET two_factor_enabled = 0 WHERE email = ?");
        $stmt->bind_param("s", $userInfo['email']);
        $stmt->execute();
        // Refresh page to show updated status
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }
}
?>

<!-- Two Steps Verification -->
<div class="card mb-4">
    <h5 class="card-header">Two-steps verification</h5>
    <div class="card-body">
        <?php if ($user['auth_type'] == 'google'): ?>
            <div class="alert alert-info">
                Two-factor authentication is managed through your Google account settings.
            </div>
        <?php else: ?>
            <?php if ($user['two_factor_enabled']): ?>
                <h6 class="mb-3 text-success">✓ Two factor authentication is enabled</h6>
                <p class="mb-3">
                    Two-factor authentication is currently adding an additional layer of security to your account.
                    <a href="javascript:void(0);">Learn more.</a>
                </p>
                <form method="POST">
                    <button type="submit" name="disable_2fa" class="btn btn-danger">Disable Two-Factor Authentication</button>
                </form>
            <?php else: ?>
                <h6 class="mb-3">Two factor authentication is not enabled yet.</h6>
                <p class="mb-3">
                    Two-factor authentication adds an additional layer of security to your account by requiring more than just a password to log in.
                    <a href="javascript:void(0);">Learn more.</a>
                </p>
                <form method="POST">
                    <button type="submit" name="enable_2fa" class="btn btn-primary">Enable Two-Factor Authentication</button>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
<!-- /Two Steps Verification -->
                </div>
                <!-- / Content -->

                <!-- Footer -->
                <?php include 'includes/footer.php'; ?>
                <!-- / Footer -->
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility
    document.querySelectorAll('.form-password-toggle .input-group-text').forEach(function(toggle) {
        toggle.addEventListener('click', function() {
            const input = this.parentNode.querySelector('input');
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bx-hide');
                icon.classList.add('bx-show');
            } else {
                input.type = 'password';
                icon.classList.remove('bx-show');
                icon.classList.add('bx-hide');
            }
        });
    });
});
</script>