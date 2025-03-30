<?php
require_once 'includes/validation.php';
require_once 'includes/header.php';
require_once 'includes/db-connect.php';



$userInfo = getUserInfo();
$userEmail = $userInfo['email'];
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Create directory if it doesn't exist
    $upload_dir = __DIR__ . '/img/tables/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Function to handle file upload
    function handleFileUpload($file, $field_name) {
        global $upload_dir, $error;
        
        if ($file['error'] === 0) {
            $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (!in_array($file_extension, $allowed_extensions)) {
                $error = "Only JPG, JPEG, PNG & GIF files are allowed for $field_name.";
                return false;
            }
            
            // Create unique filename
            $new_filename = uniqid($field_name . '_') . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                return $new_filename;
            } else {
                $error = "Failed to upload $field_name.";
                return false;
            }
        }
        return null;
    }

    // Process calendar upload
    $calendar_filename = isset($_FILES['annual_calendar']) ? 
        handleFileUpload($_FILES['annual_calendar'], 'calendar') : null;

    // Process timetable upload
    $timetable_filename = isset($_FILES['timetable']) ? 
        handleFileUpload($_FILES['timetable'], 'timetable') : null;

    // Update database if at least one file was uploaded successfully
    if ($calendar_filename || $timetable_filename) {
        try {
            $sql_parts = [];
            $params = [];
            $types = "";
            
            if ($calendar_filename) {
                $sql_parts[] = "annual_calendar = ?";
                $params[] = $calendar_filename;
                $types .= "s";
            }
            if ($timetable_filename) {
                $sql_parts[] = "timetable = ?";
                $params[] = $timetable_filename;
                $types .= "s";
            }
            
            if (!empty($sql_parts)) {
                $sql = "UPDATE users SET " . implode(", ", $sql_parts) . " WHERE email = ?";
                $params[] = $userEmail;
                $types .= "s";
                
                $stmt = $db->prepare($sql);
                $stmt->bind_param($types, ...$params);
                
                if ($stmt->execute()) {
                    $message = "Files uploaded successfully!";
                } else {
                    $error = "Database update failed.";
                }
            }
        } catch (Exception $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

// Fetch current user data
try {
    $stmt = $db->prepare("SELECT annual_calendar, timetable FROM users WHERE email = ?");
    $stmt->bind_param("s", $userEmail);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_data = $result->fetch_assoc();
} catch (Exception $e) {
    $error = "Error fetching user data: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en" class="light-style layout-navbar-fixed layout-menu-fixed" dir="ltr" data-theme="theme-default">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Calendar & Timetable</title>
    
    <!-- Include Sneat Template CSS -->
    <link rel="stylesheet" href="assets/vendor/fonts/boxicons.css" />
    <link rel="stylesheet" href="assets/vendor/css/core.css" />
    <link rel="stylesheet" href="assets/vendor/css/theme-default.css" />
    <link rel="stylesheet" href="assets/css/demo.css" />
</head>
<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
    <?php include_once('includes/sidebar-main.php'); ?>
        <div class="layout-container">
            

            <!-- Layout container -->
            <div class="layout-page">
                <!-- Navbar -->
                <?php include('includes/navbar.php'); ?>
                <!-- / Navbar -->

                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <!-- Content -->
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <h4 class="fw-bold py-3 mb-4">Upload Calendar & Timetable</h4>

                        

                        <div class="row">
                            <div class="col-md-12">
                                <div class="card mb-4">
                                    <h5 class="card-header">Upload Images</h5>
                                    <div class="card-body">
                                        <form method="POST" enctype="multipart/form-data">
                                            <!-- Annual Calendar Upload -->
                                            <div class="mb-3">
                                                <label for="annual_calendar" class="form-label">Annual Calendar</label>
                                                <input type="file" class="form-control" id="annual_calendar" name="annual_calendar" accept="image/*">
                                                <?php if (!empty($user_data['annual_calendar'])): ?>
                                                    <div class="mt-2">
                                                        <small class="text-muted">Current file: <?php echo htmlspecialchars($user_data['annual_calendar']); ?></small>
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                            <!-- Timetable Upload -->
                                            <div class="mb-3">
                                                <label for="timetable" class="form-label">Timetable</label>
                                                <input type="file" class="form-control" id="timetable" name="timetable" accept="image/*">
                                                <?php if (!empty($user_data['timetable'])): ?>
                                                    <div class="mt-2">
                                                        <small class="text-muted">Current file: <?php echo htmlspecialchars($user_data['timetable']); ?></small>
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                            <button type="submit" class="btn btn-primary">Upload Files</button>
                                        </form>

                                        <!-- Preview Section -->
                                        <?php if (!empty($user_data['annual_calendar']) || !empty($user_data['timetable'])): ?>
                                            <div class="mt-4">
                                                <h6>Current Images</h6>
                                                <div class="row">
                                                    <?php if (!empty($user_data['annual_calendar'])): ?>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Annual Calendar</label>
                                                            <img src="img/tables/<?php echo htmlspecialchars($user_data['annual_calendar']); ?>" 
                                                                 class="img-fluid img-thumbnail" 
                                                                 alt="Annual Calendar">
                                                        </div>
                                                    <?php endif; ?>

                                                    <?php if (!empty($user_data['timetable'])): ?>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Timetable</label>
                                                            <img src="img/tables/<?php echo htmlspecialchars($user_data['timetable']); ?>" 
                                                                 class="img-fluid img-thumbnail" 
                                                                 alt="Timetable">
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- / Content -->
                    
                    <!-- Footer -->
                    <?php include('includes/footer.php'); ?>
                    <!-- / Footer -->
                </div>
            </div>
        </div>
    </div>

    <!-- Core JS -->
    <script src="assets/vendor/libs/jquery/jquery.js"></script>
    <script src="assets/vendor/libs/popper/popper.js"></script>
    <script src="assets/vendor/js/bootstrap.js"></script>
    <script src="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="assets/vendor/js/menu.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>