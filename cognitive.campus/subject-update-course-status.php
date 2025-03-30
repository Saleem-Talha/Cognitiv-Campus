<?php
require_once 'vendor/autoload.php';
require_once 'includes/db-connect.php';

if (!isset($_SESSION['access_token'])) {
    http_response_code(401);
    exit('Unauthorized');
}

if (!isset($_POST['course_id']) || !isset($_POST['status'])) {
    http_response_code(400);
    exit('Bad Request');
}

$client = new Google_Client();
$client->setAccessToken($_SESSION['access_token']);

$oauth2 = new Google_Service_Oauth2($client);
$userInfo = $oauth2->userinfo->get();
$userEmail = $userInfo->getEmail();

$courseId = $_POST['course_id'];
$courseName = $_POST['course_name'];
$status = $_POST['status'];

// Get the user's plan
$stmt = $db->prepare("SELECT plan FROM users WHERE email = ?");
$stmt->bind_param("s", $userEmail);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$plan = $user['plan'];

// Get the current number of active courses for the user
$stmt = $db->prepare("SELECT COUNT(*) as count FROM course_status WHERE user_id = ? AND status = 1");
$stmt->bind_param("s", $userEmail);
$stmt->execute();
$result = $stmt->get_result();
$activeCoursesCount = $result->fetch_assoc()['count'];

// Set the course limit based on the plan
$courseLimit = ($plan == 'basic') ? 6 : (($plan == 'standard') ? 18 : PHP_INT_MAX);

if ($status) {
    if ($activeCoursesCount < $courseLimit) {
        // Insert or update the course status
        $stmt = $db->prepare("INSERT INTO course_status (user_id, course_id, course_name, status) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE status = ?");
        $stmt->bind_param("ssssi", $userEmail, $courseId, $courseName, $status, $status);
    } else {
        http_response_code(403);
        exit('Course limit reached for your plan');
    }
} else {
    // Remove the course status
    $stmt = $db->prepare("DELETE FROM course_status WHERE user_id = ? AND course_id = ?");
    $stmt->bind_param("ss", $userEmail, $courseId);

    if ($stmt->execute()) {
        // Delete related notes when a course is deactivated
        $stmtNotes = $db->prepare("DELETE FROM notes_course WHERE courseId = ?");
        $stmtNotes->bind_param("s", $courseId);
        if (!$stmtNotes->execute()) {
            http_response_code(500);
            echo "Error deleting course notes";
            $stmtNotes->close();
            exit;
        }
        $stmtNotes->close();
    }
}

if ($stmt->execute()) {
    echo "Course status updated successfully";
} else {
    http_response_code(500);
    echo "Error updating course status";
}

$stmt->close();
