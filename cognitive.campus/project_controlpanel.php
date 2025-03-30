<div class="col-md-6 mt-3">
    <div class="card">
        <div class="card-header">
            <h5>Project Control Panel</h5>
        </div>
        <?php if ($userEmail == $ownerEmail) { ?>
            <div class="card-body">
                <!-- Form for updating project details -->
                <form action="" method="post">
                    <!-- Input for project name -->
                    <div class="mb-3 form-floating">
                        <input type="text" name="project_name" placeholder="Project name" class="form-control" value="<?php echo htmlspecialchars($project_name); ?>" required>
                        <label for="project_name">Project Name</label>
                    </div>
                    <!-- Input for end date -->
                    <div class="mb-3 form-floating">
                        <input type="date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($project_end_date); ?>">
                        <label for="end_date">End Date</label>
                    </div>
                    <!-- Select for project status -->
                    <div class="mb-3 form-floating">
                        <select name="status" class="form-select">
                            <option value="Active" <?php echo $project_status == 'Active' ? 'selected' : ''; ?>>Active</option>
                            <option value="Incomplete" <?php echo $project_status == 'Incomplete' ? 'selected' : ''; ?>>Incomplete</option>
                            <option value="Completed" <?php echo $project_status == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                        </select>
                        <label for="status">Status</label>
                    </div>
                    <!-- Submit button and delete button -->
                    <div class="mb-0">
                        <input type="submit" value="Update Project" class="btn btn-primary" name="update-project">
                        <!-- Replace the <a> tag with a form -->
                        <form action="" method="post" style="display:inline;">
                            <input type="hidden" name="project_id_to_delete" value="<?php echo $project_id; ?>">
                            <button type="submit" name="delete-project-btn" class="btn btn-outline-primary" onclick="return confirmDelete()">
                                <i class="bx bx-trash"></i>
                            </button>
                        </form>
        

                     <script>
                        function confirmDelete() {
                            return confirm("Are you sure? Once deleted, you will not be able to recover this project!");
                        }
                        </script>

                        <?php
                        // Replace the existing delete logic in the $_GET['delete-project'] section
                        if (isset($_POST['delete-project-btn'])) {
                            $project_id = $db->real_escape_string($_POST['project_id_to_delete']);
                            
                            // Delete related records from multiple tables
                            $delete_project = $db->query("DELETE FROM projects WHERE id = '$project_id'");
                            $delete_requests = $db->query("DELETE FROM project_requests WHERE project_id = '$project_id'");
                            $delete_tasks = $db->query("DELETE FROM project_tasks WHERE project_id = '$project_id'");
                            $delete_comments = $db->query("DELETE FROM task_complete WHERE project_id = '$project_id'");
                            $delete_notice = $db->query("DELETE FROM project_notice WHERE project_id = '$project_id'");
                            $delete_branch = $db->query("DELETE FROM project_branch WHERE project_id = '$project_id'");
                            $delete_notes = $db->query("DELETE FROM notes_project WHERE project_id = '$project_id'");

                            if ($delete_project && $delete_requests && $delete_tasks && $delete_comments && $delete_notice && $delete_branch && $delete_notes) {
                                echo "<script>swal('Success', 'Project Deleted', 'success').then(() => { window.location.href = 'project.php'; });</script>";
                                $notice_message = "$project_name has been Deleted";
                                $type = "Project";
                                
                                // Get all project members
                                $get_project_members = $db->query("SELECT email FROM project_requests WHERE project_id = '$project_id' AND status = 'Accepted'");
                                
                                while ($member = $get_project_members->fetch_assoc()) {
                                    $memberEmail = $member['email'];
                                    $insert_notification = $db->query("INSERT INTO notice (userEmail, message, type) VALUES ('$memberEmail', '$notice_message', '$type')");
                                }
                                
                                // Don't forget to notify the project owner as well
                                $get_project_owner = $db->query("SELECT ownerEmail FROM projects WHERE id = '$project_id'");
                                $owner = $get_project_owner->fetch_assoc();
                                $ownerEmail = $owner['ownerEmail'];
                                $insert_notification = $db->query("INSERT INTO notice (userEmail, message, type) VALUES ('$ownerEmail', '$notice_message', '$type')");

                            } else {
                                echo "<script>swal('Error', '" . $db->error . "', 'error');</script>";
                            }
                        }
                        ?>
                    </div>
                </form>
                
                <?php
                // Handle project update
                if (isset($_POST['update-project'])) {
                    $project_name = $db->real_escape_string($_POST['project_name']);
                    $end_date = $db->real_escape_string($_POST['end_date']);
                    $status = $db->real_escape_string($_POST['status']);

                    // Update project details in the database
                    $update_project = $db->query("UPDATE projects SET name = '$project_name', end_date = '$end_date', status = '$status' WHERE id = '$project_id'");
                    
                    if ($update_project) {
                        echo "<script>swal('Success', 'Project Updated', 'success').then(() => { window.location.href = 'project-details.php?project-id=" . $project_id . "'; });</script>";
                        $notice_message = "$project_name Status Changed to $status";
                        $type = "Project";
                        
                        // Get all project members
                        $get_project_members = $db->query("SELECT email FROM project_requests WHERE project_id = '$project_id' AND status = 'Accepted'");
                        
                        while ($member = $get_project_members->fetch_assoc()) {
                            $memberEmail = $member['email'];
                            $insert_notification = $db->query("INSERT INTO notice (userEmail, message, type) VALUES ('$memberEmail', '$notice_message', '$type')");
                        }
                        
                        // Don't forget to notify the project owner as well
                        $get_project_owner = $db->query("SELECT ownerEmail FROM projects WHERE id = '$project_id'");
                        $owner = $get_project_owner->fetch_assoc();
                        $ownerEmail = $owner['ownerEmail'];
                        $insert_notification = $db->query("INSERT INTO notice (userEmail, message, type) VALUES ('$ownerEmail', '$notice_message', '$type')");
                
                    } else {
                        echo "<script>swal('Error', '" . $db->error . "', 'error');</script>";
                    }
                }

                // Handle project deletion
                
                ?>
            </div>
        <?php } else { ?>
            <div class="alert alert-primary mx-3" role="alert">
                                    <i class="bx bx-info-circle me-2"></i>This panel is only accessible to the project owner.
                                </div>
        <?php } ?>
    </div>
</div>
