<?php
require_once 'includes/validation.php';

$userInfo = getUserInfo();
if (!$userInfo) {
    header('Location: index.php');
    exit();
}

// Handle task operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $title = $_POST['title'];
                $description = $_POST['description'];
                $due_date = $_POST['due_date'];
                $userEmail = $userInfo['email'];
                
                $stmt = $db->prepare("INSERT INTO tasks (title, description, due_date, userEmail) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $title, $description, $due_date, $userEmail);
                $stmt->execute();
                break;
                
            case 'delete':
                $taskId = $_POST['task_id'];
                $userEmail = $userInfo['email'];
                
                $stmt = $db->prepare("DELETE FROM tasks WHERE id = ? AND userEmail = ?");
                $stmt->bind_param("is", $taskId, $userEmail);
                $stmt->execute();
                break;
                
            case 'update':
                $taskId = $_POST['task_id'];
                $title = $_POST['title'];
                $description = $_POST['description'];
                $due_date = $_POST['due_date'];
                $userEmail = $userInfo['email'];
                
                $stmt = $db->prepare("UPDATE tasks SET title = ?, description = ?, due_date = ? WHERE id = ? AND userEmail = ?");
                $stmt->bind_param("sssis", $title, $description, $due_date, $taskId, $userEmail);
                $stmt->execute();
                break;
        }
        
        // Redirect to prevent form resubmission
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Fetch user's tasks
$stmt = $db->prepare("SELECT * FROM tasks WHERE userEmail = ? ORDER BY due_date ASC");
$stmt->bind_param("s", $userInfo['email']);
$stmt->execute();
$result = $stmt->get_result();
$tasks = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Todo List</title>
    <!-- Include Sneat Template CSS -->
    <link rel="stylesheet" href="assets/vendor/css/core.css">
    <link rel="stylesheet" href="assets/vendor/css/theme-default.css">
    <link rel="stylesheet" href="assets/css/demo.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<div class="row">
  <div class="col-lg-8 col-md-6 col-sm-12 mb-3">
    <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center py-2">
            <h5 class="mb-0">Todo List</h5>
            <button type="button" class="btn btn-primary btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#addTaskModal">
                <i class="bx bx-plus me-1"></i>Add Task
            </button>
        </div>
        
        <div class="card-body p-0">
            <div class="list-group list-group-flush">
                <?php if (empty($tasks)): ?>
                <div class="text-center py-5">
                    <i class="bx bx-task bx-lg mb-2 text-primary"></i>
                    <p class="mb-0 text-muted">No tasks yet</p>
                </div>
                <?php else: ?>
                    <?php foreach ($tasks as $task): ?>
                        <div class="list-group-item">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="me-2">
                        <h6 class="mb-1 text-truncate" style="max-width: 200px;" title="<?= htmlspecialchars($task['title']) ?>">
                            <?= htmlspecialchars($task['title']) ?>
                        </h6>
                        <p class="mb-1 text-muted small text-truncate" style="max-width: 200px;" title="<?= htmlspecialchars($task['description']) ?>">
                            <?= htmlspecialchars($task['description']) ?>
                        </p>
                        <div class="d-flex align-items-center text-muted small">
                            <i class="bx bx-calendar me-1"></i>
                            <?= date('M d, Y', strtotime($task['due_date'])) ?>
                        </div>
                    </div>
                    <div class="d-flex align-items-end">
                        <!-- Edit Icon -->
                        <a href="javascript:void(0);" class="me-2 edit-task" 
                        data-task-id="<?= $task['id'] ?>" 
                        data-title="<?= htmlspecialchars($task['title']) ?>" 
                        data-description="<?= htmlspecialchars($task['description']) ?>" 
                        data-due-date="<?= htmlspecialchars($task['due_date']) ?>" 
                        title="Edit">
                            <i class="bx bx-edit bx-sm text-primary"></i>
                        </a>
                        
                        <!-- Delete Icon -->
                        <a href="javascript:void(0);" class="delete-task" 
                        data-task-id="<?= $task['id'] ?>" 
                        title="Delete">
                            <i class="bx bx-trash bx-sm text-danger"></i>
                        </a>
                    </div>
                </div>
            </div>

                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
     
   </div>
   <?php include("dashboard-deadlines.php");?>
     <?php include("dashboard-student-analysis.php");?>
</div>

<!-- Add Task Modal -->
<div class="modal fade" id="addTaskModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-4">
                <h5 class="modal-title">Add New Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label small">Title</label>
                        <input type="text" class="form-control form-control-sm" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Description</label>
                        <textarea class="form-control form-control-sm" name="description" rows="2" required></textarea>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small">Due Date</label>
                        <input type="date" class="form-control form-control-sm" name="due_date" required>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Add Task</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Task Modal -->
<div class="modal fade" id="editTaskModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-4">
                <h5 class="modal-title">Edit Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="task_id" id="edit-task-id">
                    <div class="mb-3">
                        <label class="form-label small">Title</label>
                        <input type="text" class="form-control form-control-sm" name="title" id="edit-title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Description</label>
                        <textarea class="form-control form-control-sm" name="description" id="edit-description" rows="2" required></textarea>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small">Due Date</label>
                        <input type="date" class="form-control form-control-sm" name="due_date" id="edit-due-date" required>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Update Task</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Task Form (Hidden) -->
<form id="deleteTaskForm" action="" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="task_id" id="delete-task-id">
</form>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle Edit Task
    const editButtons = document.querySelectorAll('.edit-task');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const taskId = this.dataset.taskId;
            const title = this.dataset.title;
            const description = this.dataset.description;
            const dueDate = this.dataset.dueDate;

            document.getElementById('edit-task-id').value = taskId;
            document.getElementById('edit-title').value = title;
            document.getElementById('edit-description').value = description;
            document.getElementById('edit-due-date').value = dueDate;

            new bootstrap.Modal(document.getElementById('editTaskModal')).show();
        });
    });

    // Handle Delete Task
    const deleteButtons = document.querySelectorAll('.delete-task');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Are you sure you want to delete this task?')) {
                document.getElementById('delete-task-id').value = this.dataset.taskId;
                document.getElementById('deleteTaskForm').submit();
            }
        });
    });

    // Initialize all dropdowns
    const dropdownElementList = document.querySelectorAll('[data-bs-toggle="dropdown"]');
    const dropdownList = [...dropdownElementList].map(dropdownToggleEl => new bootstrap.Dropdown(dropdownToggleEl));
});
</script>