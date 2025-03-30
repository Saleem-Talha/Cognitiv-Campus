
<!-- 2. Update project-reload.php -->
<?php
session_start();

// Get the encoded project ID from POST or GET
$project_id = isset($_POST['project-id']) ? $_POST['project-id'] : (isset($_GET['project-id']) ? $_GET['project-id'] : '');

// Set any success messages in session if needed
if (isset($_POST['success_message'])) {
    $_SESSION['success_message'] = $_POST['success_message'];
}

// Perform the redirect after a brief delay
header("Refresh: 0.1; URL=project-details.php?project-id=" . $project_id);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Redirecting...</title>
</head>
<body>
    <!-- Optional loading indicator -->
    <div style="display: flex; justify-content: center; align-items: center; height: 100vh;">
        <p>Redirecting...</p>
    </div>
</body>
</html>