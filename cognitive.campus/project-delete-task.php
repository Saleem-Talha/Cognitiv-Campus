<?php
require_once 'includes/db_connect.php';

// Start session to get user email
session_start();
if (!isset($_SESSION['email'])) {
    echo "ERROR:User not authenticated";
    exit;
}

$userEmail = $_SESSION['email'];

// Check if task_id and project_id are provided
if (isset($_POST['task_id']) && isset($_POST['project_id'])) {
    $task_id = $db->real_escape_string($_POST['task_id']);
    $project_id = $db->real_escape_string($_POST['project_id']);
    
    // Verify the user is the project owner
    $check_owner = $db->query("SELECT ownerEmail FROM projects WHERE id = '$project_id'");
    if ($check_owner->num_rows === 0) {
        echo "ERROR:Project not found";
        exit;
    }
    
    $owner_row = $check_owner->fetch_assoc();
    $ownerEmail = $owner_row['ownerEmail'];
    
    if ($userEmail !== $ownerEmail) {
        echo "ERROR:You do not have permission to delete this task";
        exit;
    }
    
    // Begin transaction
    $db->begin_transaction();
    
    try {
        // Get task information for notification before deletion
        $select_task = $db->query("SELECT userEmail, task FROM project_tasks WHERE id = '$task_id'");
        $task_data = null;
        
        if ($select_task->num_rows > 0) {
            $task_data = $select_task->fetch_assoc();
        }
        
        // First, delete task completion records
        $delete_completions = $db->query("DELETE FROM task_complete WHERE task_id = '$task_id'");
        
        // Then delete the task
        $delete_task = $db->query("DELETE FROM project_tasks WHERE id = '$task_id' AND project_id = '$project_id'");
        
        if ($delete_task) {
            // Send notification if task data was retrieved
            if ($task_data) {
                $assigned_email = $task_data['userEmail'];
                $task_name = $task_data['task'];
                
                // Insert notification
                $notice_message = "Task \"$task_name\" assigned to you has been deleted by the project owner";
                $insert_notification = $db->query("INSERT INTO notice (userEmail, message, type) VALUES ('$assigned_email', '$notice_message', 'Project')");
            }
            
            // Commit the transaction
            $db->commit();
            
            // Return success message
            echo "SUCCESS:Task deleted successfully";
        } else {
            throw new Exception('Failed to delete task');
        }
    } catch (Exception $e) {
        // Roll back the transaction in case of an error
        $db->rollback();
        echo "ERROR:" . $e->getMessage();
    }
} else {
    echo "ERROR:Task ID or Project ID not provided";
}
?>