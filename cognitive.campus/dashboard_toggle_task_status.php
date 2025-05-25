<?php
require_once 'includes/validation.php';

// Verify user is logged in
$userInfo = getUserInfo();
if (!$userInfo) {
    echo "error|Unauthorized";
    exit();
}

// Make sure we have the required parameters
if (!isset($_POST['task_id']) || !isset($_POST['current_status'])) {
    echo "error|Missing parameters";
    exit();
}

$taskId = $_POST['task_id'];
$currentStatus = $_POST['current_status'];
$userEmail = $userInfo['email'];

// Validate task ownership and existence
$stmt = $db->prepare("SELECT id FROM tasks WHERE id = ? AND userEmail = ?");
$stmt->bind_param("is", $taskId, $userEmail);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "error|Task not found or unauthorized";
    exit();
}

// Determine the next status in the cycle: active -> completed -> incomplete -> active
$newStatus = 'active';  // Default new status
$iconClass = 'bx-circle text-secondary';
$tooltip = 'Mark as completed';

switch ($currentStatus) {
    case 'active':
        $newStatus = 'completed';
        $iconClass = 'bx-check-circle text-success';
        $tooltip = 'Mark as incomplete';
        break;
    
    case 'completed':
        $newStatus = 'incomplete';
        $iconClass = 'bx-x-circle text-danger';
        $tooltip = 'Mark as active';
        break;
        
    case 'incomplete':
        $newStatus = 'active';
        $iconClass = 'bx-circle text-secondary';
        $tooltip = 'Mark as completed';
        break;
}

// Update the task status in the database
$stmt = $db->prepare("UPDATE tasks SET is_completed = ? WHERE id = ? AND userEmail = ?");
$stmt->bind_param("sis", $newStatus, $taskId, $userEmail);

if ($stmt->execute()) {
    // Return the new status, icon class, and tooltip
    echo $newStatus . '|' . $iconClass . '|' . $tooltip;
} else {
    echo "error|Database update failed";
}