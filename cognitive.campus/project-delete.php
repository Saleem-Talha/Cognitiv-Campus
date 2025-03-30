<?php
include_once('includes/db-connect.php'); // Your database connection file
include_once('includes/functions.php');   // Include any utility functions

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ensure user is authenticated and authorized
    session_start();
    if (!isset($_SESSION['email'])) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
        exit;
    }

    $project_id = $db->real_escape_string($_POST['project_id']);

    // Check if the current user is the project owner
    $check_owner = $db->query("SELECT ownerEmail FROM projects WHERE id = '$project_id'")->fetch_assoc();
    if ($_SESSION['email'] !== $check_owner['ownerEmail']) {
        echo json_encode(['status' => 'error', 'message' => 'Only project owner can delete the project']);
        exit;
    }

    // Start transaction to ensure all deletions succeed
    $db->begin_transaction();

    try {
        // Delete project and all related data
        $delete_project = $db->query("DELETE FROM projects WHERE id = '$project_id'");
        $delete_requests = $db->query("DELETE FROM project_requests WHERE project_id = '$project_id'");
        $delete_tasks = $db->query("DELETE FROM project_tasks WHERE project_id = '$project_id'");
        $delete_comments = $db->query("DELETE FROM task_complete WHERE project_id = '$project_id'");
        $delete_notice = $db->query("DELETE FROM project_notice WHERE project_id = '$project_id'");
        $delete_branch = $db->query("DELETE FROM project_branch WHERE project_id = '$project_id'");

        // Check if all deletions were successful
        if ($delete_project && $delete_requests && $delete_tasks && 
            $delete_comments && $delete_notice && $delete_branch) {
            $db->commit();
            echo json_encode(['status' => 'success']);
        } else {
            $db->rollback();
            echo json_encode([
                'status' => 'error', 
                'message' => 'Failed to delete project: ' . $db->error
            ]);
        }
    } catch (Exception $e) {
        $db->rollback();
        echo json_encode([
            'status' => 'error', 
            'message' => 'An unexpected error occurred: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}