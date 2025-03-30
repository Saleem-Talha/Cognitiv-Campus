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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($db, $_POST['name']);
    $email = mysqli_real_escape_string($db, $_POST['email']);
    $phone = mysqli_real_escape_string($db, $_POST['phone']);
    
    
        // Update without changing the picture
        $updateQuery = "UPDATE users SET 
            name = ?, 
            email = ?, 
            phone_number = ? 
            WHERE email = ?";
        $stmt = $db->prepare($updateQuery);
        $stmt->bind_param("ssss", $name, $email, $phone, $userInfo['email']);
    
    
    if (isset($stmt) && $stmt->execute()) {
        $msg = "Profile updated successfully!";
        // Refresh user info
        $userInfo = getUserInfo();
    } else {
        $msg = "Error updating profile: " . ($stmt ? $stmt->error : "Unknown error");
    }
}   

// Get current user data
$stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $userInfo['email']);
$stmt->execute();
$userData = $stmt->get_result()->fetch_assoc();
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
            <?php include_once('includes/navbar.php'); ?>
            <?php include_once('includes/settings-bar.php'); ?>
            <!-- / Navbar -->

            <!-- Content wrapper -->
            <div class="content-wrapper">
                <!-- Content -->
                <div class="container-xxl flex-grow-1 container-p-y">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card mb-4">
                                <h5 class="card-header">Profile Details</h5>
                                <?php if ($msg): ?>
                                <div class="alert alert-primary m-2"><?php echo $msg; ?></div>
                                <?php endif; ?>
                                
                                <div class="card-body">
                                    <form method="POST" enctype="multipart/form-data">
                                        <div class="d-flex align-items-start align-items-sm-center gap-4 mb-4">
                                            <img
                                                src="<?php echo htmlspecialchars('img/pfps/' .$userData['picture'] ?? 'assets/img/avatars/1.png'); ?>"
                                                alt="user-avatar"
                                                class="d-block rounded-circle img-fluid"
                                                style="width: 100px; height: 100px; object-fit: cover;"
                                                id="uploadedAvatar"
                                            />
                                            
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">User Name</label>
                                                <input
                                                    class="form-control"
                                                    type="text"
                                                    name="name"
                                                    value="<?php echo htmlspecialchars($userData['name'] ?? ''); ?>"
                                                />
                                            </div>
                                            
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">E-mail</label>
                                                <input
                                                    class="form-control"
                                                    type="email"
                                                    name="email"
                                                    value="<?php echo htmlspecialchars($userData['email'] ?? ''); ?>"
                                                />
                                            </div>
                                            
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Phone Number</label>
                                                <input
                                                    class="form-control"
                                                    type="text"
                                                    name="phone"
                                                    value="<?php echo htmlspecialchars($userData['phone_number'] ?? ''); ?>"
                                                />
                                            </div>
                                            
                                        </div>
                                        <div class="mt-2">
                                            <button type="submit" class="btn btn-primary me-2">Save changes</button>
                                            <button type="reset" class="btn btn-outline-secondary">Cancel</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- / Content -->

                <!-- Footer -->
                <?php include 'includes/footer.php'; ?>
                <!-- / Footer -->
            </div>
            <!-- Content wrapper -->
        </div>
        <!-- / Layout page -->
    </div>
</div>

