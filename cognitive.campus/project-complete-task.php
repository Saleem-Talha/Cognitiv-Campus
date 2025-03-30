<?php
// Prevent PHP from outputting errors in the response
ini_set('display_errors', 0);
error_reporting(0);

// Ensure proper content type for JSON response
header('Content-Type: application/json');

// Include your database connection file here
include 'includes/db-connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate required fields
        if (!isset($_POST['task_id']) || !isset($_POST['project_id']) || !isset($_POST['description'])) {
            throw new Exception('Missing required fields');
        }

        $task_id = $db->real_escape_string($_POST['task_id']);
        $project_id = $db->real_escape_string($_POST['project_id']);
        $description = $db->real_escape_string($_POST['description']);
        
        // Start transaction
        $db->begin_transaction();

        // Get the current completed_tasks count
        $current_count_query = $db->query("SELECT completed_tasks, task_steps FROM project_tasks WHERE id = '$task_id'");
        if (!$current_count_query) {
            throw new Exception('Failed to retrieve task information');
        }

        $task_info = $current_count_query->fetch_assoc();
        $current_count = (int)$task_info['completed_tasks'];
        $total_steps = (int)$task_info['task_steps'];
        $new_count = $current_count + 1;

        // Validate step count
        if ($new_count > $total_steps) {
            throw new Exception('All steps are already completed');
        }

        // Insert into task_complete table
        $insert_query = $db->prepare("INSERT INTO task_complete (project_id, task_id, step_no, description) VALUES (?, ?, ?, ?)");
        if (!$insert_query) {
            throw new Exception('Failed to prepare task completion query');
        }

        $insert_query->bind_param("iiis", $project_id, $task_id, $new_count, $description);
        if (!$insert_query->execute()) {
            throw new Exception('Failed to record task completion');
        }

        // Update completed_tasks in project_tasks table
        $update_query = $db->prepare("UPDATE project_tasks SET completed_tasks = ? WHERE id = ?");
        if (!$update_query) {
            throw new Exception('Failed to prepare task update query');
        }

        $update_query->bind_param("ii", $new_count, $task_id);
        if (!$update_query->execute()) {
            throw new Exception('Failed to update task progress');
        }

        // Handle branch creation if checkbox was checked
        if (isset($_POST['add_branch']) && isset($_FILES['branchFile'])) {
            $branchFile = $_FILES['branchFile']['name'];
            $branchFile_tmp = $_FILES['branchFile']['tmp_name'];
            $branch_description = isset($_POST['branch_description']) ? 
                                $db->real_escape_string($_POST['branch_description']) : '';

            // Validate file upload
            if (empty($branchFile)) {
                throw new Exception('No file was uploaded');
            }

            // Create unique filename using project_id and timestamp
            $branchFile = $project_id . '_' . time() . '_' . $branchFile;

            // Get user profile image
            $userProfile = isset($userProfile) ? $userProfile : 'default-profile-image.jpg';

            // Insert branch record
            $insert_branch = $db->query("INSERT INTO project_branch 
                (project_id, branch_file, branch_image, datetime, description) 
                VALUES 
                ('$project_id', '$branchFile', '$userProfile', NOW(), '$branch_description')");

            if (!$insert_branch) {
                throw new Exception('Failed to create branch record');
            }

            // Move uploaded file
            if (!move_uploaded_file($branchFile_tmp, 'projects/' . $branchFile)) {
                throw new Exception('Failed to save branch file');
            }
        }

        // If we get here, everything worked
        $db->commit();
        echo json_encode([
            'success' => true,
            'message' => 'Task step completed successfully'
        ]);

    } catch (Exception $e) {
        // Rollback transaction on error
        if ($db->mysqli->trans_status !== null) {
            $db->rollback();
        }
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}