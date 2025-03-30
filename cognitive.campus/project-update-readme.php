<?php
// Include your database connection file
include 'includes/db-connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $project_id = $_POST['project_id'];
    $readme = $_POST['readme'];

    // Sanitize inputs to prevent SQL injection
    $project_id = $db->real_escape_string($project_id);
    $readme = $db->real_escape_string($readme);

    $update_query = "UPDATE projects SET readme = '$readme' WHERE id = '$project_id'";

    if ($db->query($update_query)) {
        echo "success";
    } else {
        echo "error";
    }
}

$db->close();
