<div class="col-md-12 mt-3">
    <div class="card">
        <div class="card-header">
            <!-- Button to trigger the task assignment modal -->
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#taskmodal" <?php echo ($project_status !== 'Active') ? 'disabled' : ''; ?>>
                <i class='bx bx-task me-2'></i> Assign Task
            </button>

            <!-- Modal for assigning tasks -->
            <div class="modal fade" id="taskmodal" tabindex="-1" aria-labelledby="taskmodalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="taskmodalLabel">Assign Task</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Task assignment form -->
                            <form action="" method="post">
                                <!-- Dropdown to select team members -->
                                <div class="mb-3 form-floating">
                                    <select name="userEmail" class="form-select" required>
                                        <?php
                                        // Query to fetch team members who have accepted the project
                                        $select_teammembers = $db->query("SELECT email FROM project_requests WHERE project_id = '$project_id' AND status = 'Accepted'");
                                        if ($select_teammembers->num_rows) {
                                            while ($row_teammember = $select_teammembers->fetch_assoc()) {
                                                $getted_email = $row_teammember['email'];
                                        ?>
                                                <option><?php echo htmlspecialchars($getted_email); ?></option>
                                        <?php
                                            }
                                        }
                                        ?>
                                    </select>
                                    <label for="userEmail">Select Team Members</label>
                                </div>
                                <!-- Input for task title -->
                                <div class="mb-3 form-floating">
                                    <input type="text" name="task" class="form-control" placeholder="Task" required>
                                    <label for="task">Task Title</label>
                                </div>
                                <!-- Input for task description -->
                                <div class="mb-3">
                                    <textarea name="task_description" class="form-control" placeholder="Task Description"></textarea>
                                </div>
                                <!-- Input for number of task steps -->
                                <div class="mb-3 form-floating">
                                    <input type="number" name="task_steps" class="form-control" placeholder="Task Steps" required>
                                    <label for="task_steps">Task Steps</label>
                                </div>
                                <!-- Input for task deadline -->
                                <div class="mb-3 form-floating">
                                    <input type="date" name="deadline" class="form-control" placeholder="Deadline" required min="<?php echo date('Y-m-d'); ?>">
                                    <label for="deadline">Deadline</label>
                                </div>
                                <!-- Submit button -->
                                <div class="mb-0">
                                    <input type="submit" value="Assign Task" class="btn btn-primary" name="assign_task">
                                </div>
                            </form>
                            <?php
                            // Handle task assignment form submission
                            if (isset($_POST['assign_task'])) {
                                // Sanitize and prepare task data for insertion
                                $taskuserEmail = $db->real_escape_string($_POST['userEmail']);
                                $task = $db->real_escape_string($_POST['task']);
                                $task_description = $db->real_escape_string($_POST['task_description']);
                                $task_steps = $db->real_escape_string($_POST['task_steps']);
                                $deadline = $db->real_escape_string($_POST['deadline']);

                                // Insert task into the database
                                $insert_task = $db->query("INSERT INTO project_tasks (project_id, userEmail, task, task_description, task_steps, deadline, status) VALUES ('$project_id', '$taskuserEmail', '$task', '$task_description', '$task_steps', '$deadline', 'Active')");

                                // Display success or error message
                                if ($insert_task) {
                                    $encoded_id = $_GET['project-id'];
                                    echo "<script>swal('Success', 'Task Assigned', 'success')).then(() => window.location.href = 'project-reload.php?project-id=" . $encoded_id . "');</script>";
                                    // Send notification to the assigned team member
                                    $notice_message = "You have been assigned a new task: $task";
                                    $type = "Project";
                                    
                                    $insert_notification = $db->query("INSERT INTO notice (userEmail, message, type) VALUES ('$taskuserEmail', '$notice_message', '$type')");
                                    
                                    if (!$insert_notification) {
                                        echo "<script>console.log('Error sending notification: " . $db->error . "');</script>";
                                    }
                                } else {
                                    echo "<script>swal('Error', '" . $db->error . "', 'error');</script>";
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- Table to display tasks -->
            <div class="table-responsive">
                <table class="table table-borderless table-striped table-hover border mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Task</th>
                            <th>Assigned To</th>
                            <th>Task Steps</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Query to fetch all tasks for the project
                        $select_all_tasks = $db->query("SELECT * FROM project_tasks WHERE project_id = '$project_id'");
                        if ($select_all_tasks->num_rows) {
                            $count = 0;
                            while ($row_task = $select_all_tasks->fetch_assoc()) {
                                $task_id = $row_task['id'];
                                $task_userEmail = $row_task['userEmail'];
                                $task_task = $row_task['task'];
                                $task_steps = $row_task['task_steps'];
                                $completed_tasks = isset($row_task['completed_tasks']) ? $row_task['completed_tasks'] : 0;
                                $deadline = strtotime($row_task['deadline']);
                                $current_date = strtotime(date('Y-m-d'));

                                // Update task status based on completion and deadline
                                $status = 'Active';
                                if ($completed_tasks < $task_steps && $current_date > $deadline) {
                                    $status = 'Not Completed';
                                    // Update status in database
                                    $db->query("UPDATE project_tasks SET status = 'Not Completed' WHERE id = '$task_id'");
                                }

                                $count++;
                        ?>
                                <tr>
                                    <td><?php echo $count; ?></td>
                                    <td><?php echo htmlspecialchars($task_task); ?></td>
                                    <td><?php echo htmlspecialchars($task_userEmail); ?></td>
                                    <td width="400">
                                        <div class="progress" style="height: 20px;">
                                            <?php
                                            // Calculate progress bar widths
                                            $progress_percentage = ($completed_tasks !== 0 && $task_steps !== 0) ? (intval($completed_tasks) / intval($task_steps)) * 100 : 0;
                                            $step_percentage = (1 / $task_steps) * 100;
                                            $gap_percentage = 0.5;

                                            for ($i = 1; $i <= $task_steps; $i++) {
                                                $adjusted_width = $step_percentage - $gap_percentage;

                                                // Fetch completed task description
                                                $completed_task_desc = '';
                                                if ($i <= $completed_tasks) {
                                                    $completed_task_query = $db->query("SELECT description FROM task_complete WHERE task_id = '$task_id' AND step_no = '$i'");
                                                    if ($completed_task_query->num_rows > 0) {
                                                        $completed_task_desc = $completed_task_query->fetch_assoc()['description'];
                                                    }
                                                }

                                                $tooltip_text = $i <= $completed_tasks ? $completed_task_desc : "Step $i";

                                                if ($i <= $completed_tasks) {
                                                    echo '<div class="progress-bar bg-primary" role="progressbar" style="width: ' . $adjusted_width . '%" aria-valuenow="' . $adjusted_width . '" aria-valuemin="0" aria-valuemax="100" data-bs-toggle="tooltip" data-bs-placement="top" title="' . htmlspecialchars($tooltip_text) . '"></div>';
                                                } else {
                                                    echo '<div class="progress-bar bg-secondary" role="progressbar" style="width: ' . $adjusted_width . '%" aria-valuenow="' . $adjusted_width . '" aria-valuemin="0" aria-valuemax="100" data-bs-toggle="tooltip" data-bs-placement="top" title="' . htmlspecialchars($tooltip_text) . '"></div>';
                                                }

                                                if ($i < $task_steps) {
                                                    echo '<div class="progress-bar bg-light" role="progressbar" style="width: ' . $gap_percentage . '%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>';
                                                }
                                            }
                                            ?>
                                        </div>
                                        <small><?php echo $completed_tasks . ' / ' . $task_steps; ?> steps completed</small>
                                    </td>
                                    <td>
                                        <!-- Buttons to view task details and complete task step -->
                                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#taskModal<?php echo $task_id; ?>">
                                            <i class="bx bx-show"></i>
                                        </button>
                                        <?php if ($completed_tasks < $task_steps && $status === 'Active' && ($userEmail == $task_userEmail || $userEmail == $ownerEmail)) : ?>
                                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#completeTaskModal<?php echo $task_id; ?>">
                                                <i class="bx bx-check"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>

                                <!-- Modal for task details -->
                                <div class="modal fade" id="taskModal<?php echo $task_id; ?>" tabindex="-1" aria-labelledby="taskModalLabel<?php echo $task_id; ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header shadow pb-3">
                                                <h5 class="modal-title" id="taskModalLabel<?php echo $task_id; ?>">
                                                    <i class="bx bx-task me-2"></i> Task Details
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label text-dark">Task</label>
                                                    <p class="lead"><?php echo htmlspecialchars($task_task); ?></p>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label text-dark">Assigned To</label>
                                                    <p><i class="bx bx-user me-2"></i><?php echo htmlspecialchars($task_userEmail); ?></p>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label text-dark">Description</label>
                                                    <p><?php echo htmlspecialchars($row_task['task_description']); ?></p>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label text-dark">Deadline</label>
                                                    <p><i class="bx bx-calendar me-2"></i><?php echo date('d M Y', strtotime($row_task['deadline'])); ?></p>
                                                </div>
                                                <div class="mb-0">
                                                    <label class="form-label text-dark">Status</label>
                                                    <p><span class="badge bg-<?php echo $status == 'Active' ? 'success' : 'danger'; ?>"><?php echo $status; ?></span></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal for completing task steps -->
<div class="modal fade" id="completeTaskModal<?php echo $task_id; ?>" tabindex="-1" aria-labelledby="completeTaskModalLabel<?php echo $task_id; ?>" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header shadow pb-3">
                <h5 class="modal-title" id="completeTaskModalLabel<?php echo $task_id; ?>">
                    <i class="bx bx-check-circle me-2"></i> Complete Task Step
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="completeTaskForm<?php echo $task_id; ?>">
                    <input type="hidden" name="task_id" value="<?php echo $task_id; ?>">
                    <input type="hidden" name="project_id" value="<?php echo $project_id; ?>">
                    
                    <div class="mb-3">
                        <label for="taskDescription<?php echo $task_id; ?>" class="form-label">Step Description</label>
                        <textarea class="form-control" id="taskDescription<?php echo $task_id; ?>" name="description" rows="3" required></textarea>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="addBranch<?php echo $task_id; ?>" name="add_branch">
                            <label class="form-check-label" for="addBranch<?php echo $task_id; ?>">
                                Add Branch for this completion
                            </label>
                        </div>
                    </div>

                    <div id="branchFields<?php echo $task_id; ?>" style="display: none;">
                        <div class="mb-3">
                            <label for="branchFile<?php echo $task_id; ?>" class="form-label">Branch File</label>
                            <input type="file" class="form-control" id="branchFile<?php echo $task_id; ?>" name="branchFile">
                        </div>
                        <div class="mb-3">
                            <label for="branchDescription<?php echo $task_id; ?>" class="form-label">Branch Description</label>
                            <textarea class="form-control" id="branchDescription<?php echo $task_id; ?>" name="branch_description" rows="2"></textarea>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Complete Step</button>
                </form>
            </div>
        </div>
    </div>
</div>
                            <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="5" class="text-center">No Task Found</td>
                            </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle checkbox toggle for branch fields
    const checkboxes = document.querySelectorAll('[id^="addBranch"]');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const taskId = this.id.replace('addBranch', '');
            const branchFields = document.getElementById('branchFields' + taskId);
            branchFields.style.display = this.checked ? 'block' : 'none';
        });
    });

    // Handle form submission
    const completeForms = document.querySelectorAll('[id^="completeTaskForm"]');
    completeForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            // Send the form data
            fetch('project-complete-task.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Task step completed successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        });
    });
});
</script>