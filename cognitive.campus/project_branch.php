<div class="col-md-6">
    <div class="card">
        <!-- Card Header with Modal Trigger Button -->
        <div class="card-header">
            <!-- Button to trigger modal for adding a new project branch -->
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#projectmodal" <?php echo ($project_status !== 'Active') ? 'disabled' : ''; ?>>
                <i class="bx bx-plus"></i> Add Project Branch
            </button>

            <!-- Modal for Adding Project Branch -->
            <div class="modal fade" id="projectmodal" tabindex="-1" aria-labelledby="projectmodalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="projectmodalLabel">Add Project Branch</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Form to Add New Branch -->
                            <form action="" method="post" id="branchForm" enctype="multipart/form-data">
                                <div class="mb-3 form-floating">
                                    <input type="file" name="branchFile" id="branchFile" class="form-control" required>
                                    <label for="branchFile">Branch File</label>
                                </div>
                                <div class="mb-3">
                                    <textarea name="description" class="form-control" placeholder="Enter Description If Any"></textarea>
                                </div>
                                <div class="mb-0">
                                    <input type="submit" value="Add Branch" class="btn btn-primary" name="add-branch-btn">
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <?php 
if(isset($_POST['add-branch-btn'])){
    // Get user's plan
    $userInfo = getUserInfo();
    $plan = $userInfo['plan'];
    $userEmail = $userInfo['email'];
    $get_user_plan = $db->query("SELECT plan FROM users WHERE email = '$userEmail'");
    $user_plan = $get_user_plan->fetch_assoc()['plan'];

    // Get current number of branches for this project
    $get_branch_count = $db->query("SELECT COUNT(*) as count FROM project_branch WHERE project_id = '$project_id'");
    $branch_count = $get_branch_count->fetch_assoc()['count'];

    // Set branch limit based on plan
    $branch_limit = ($user_plan == 'basic') ? 10 : (($user_plan == 'standard') ? 30 : PHP_INT_MAX);

    if($branch_count < $branch_limit){
        $branchFile = $_FILES['branchFile']['name'];
        $branchFile_tmp = $_FILES['branchFile']['tmp_name'];

        $branchFile = $project_id . '_' . $branchFile;

        $description = $_POST['description'];

        $insert = $db->query("INSERT INTO project_branch (project_id, branch_file, branch_image, datetime, description) VALUES ('$project_id', '$branchFile', '$userProfile', now(), '$description') ");
        if($insert){
            $encoded_id = $_GET['project-id'];
            move_uploaded_file($branchFile_tmp, 'projects/' . $branchFile);
            echo "<script>swal('Success', 'Branch Added', 'success').then(() => window.location.href = 'project-reload.php?project-id=" . $encoded_id . "');</script>";

            $notice_message = "A new Branch Added by $userName in $project_name";
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
        }else{
            echo "<script>swal('Error', '" . $db->error . "', 'error');</script>";
        }
    } else {
        echo "<script>swal('Limit Reached', 'You have reached the maximum number of branches for your plan. Please upgrade to add more branches.', 'warning');</script>";
    }
}
// Handle branch deletion
if(isset($_POST['delete_branch_btn'])) {
    $branch_id = $_POST['branch_id'];
    
    // First get the branch file name to delete the actual file
    $get_branch = $db->query("SELECT branch_file FROM project_branch WHERE id = '$branch_id' AND project_id = '$project_id'");
    if($get_branch->num_rows > 0) {
        $branch_data = $get_branch->fetch_assoc();
        $branch_file = $branch_data['branch_file'];
        
        // Delete the record from database
        $delete = $db->query("DELETE FROM project_branch WHERE id = '$branch_id' AND project_id = '$project_id'");
        
        if($delete) {
            // Delete the actual file
            $file_path = 'projects/' . $branch_file;
            if(file_exists($file_path)) {
                unlink($file_path);
            }
            
            $encoded_id = $_GET['project-id'];
            echo "<script>swal('Success', 'Branch deleted successfully', 'success').then(() => window.location.href = 'project-reload.php?project-id=" . $encoded_id . "');</script>";
            
            // Add notification for branch deletion
            $notice_message = "A Branch was deleted by $userName in $project_name";
            $type = "Project";
            
            // Notify all project members
            $get_project_members = $db->query("SELECT email FROM project_requests WHERE project_id = '$project_id' AND status = 'Accepted'");
            while ($member = $get_project_members->fetch_assoc()) {
                $memberEmail = $member['email'];
                $db->query("INSERT INTO notice (userEmail, message, type) VALUES ('$memberEmail', '$notice_message', '$type')");
            }
            
            // Notify project owner
            $db->query("INSERT INTO notice (userEmail, message, type) VALUES ('$ownerEmail', '$notice_message', '$type')");
        } else {
            echo "<script>swal('Error', 'Failed to delete branch: " . $db->error . "', 'error');</script>";
        }
    }
}


?>
        </div>

        <!-- Card Body to Display Branch Files -->
        <div class="card-body">
            <style>
                /* Styling for file structure display */
                .file-item {
                    margin-bottom: 10px;
                }
                .main-file {
                    font-weight: bold;
                    font-size: 18px;
                }
                .sub-file {
                    margin-left: 60px;
                    position: relative;
                }
                .sub-file::before {
                    content: '';
                    position: absolute;
                    left: -15px;
                    top: 50%;
                    width: 30px;
                    height: 1px;
                    background-color: silver;
                }
                .sub-file::after {
                    content: '';
                    position: absolute;
                    left: -15px;
                    top: -8px;
                    bottom: 50%;
                    width: 1px;
                    background-color: silver;
                }
                .div-main-content {
                    margin-left: 30px;
                }
                .file-icon {
                    margin-right: 5px;
                }
                .file-structure a {
                    text-decoration: none;
                }
                .file-structure a:hover {
                    text-decoration: underline;
                }
            </style>

            <!-- Display Project Files and Branches -->
            <div class="file-structure">
                <small><code class="text-dark">Click on file name to Download the code</code></small>
                <div class="file-item main-file">
                    <span class="file-icon">
                        <img src="<?php echo $userProfile ?>" class="rounded-circle" style="width: 25px;">
                    </span>
                    <a href="projects/<?php echo $mainProjectFile; ?>" download class="text-dark" target="_blank"><?php echo $mainProjectFile; ?></a>
                </div>
                <?php
                // Fetch and display all branches for the current project
                $branch_sql = $db->query("SELECT * FROM project_branch WHERE project_id = '$project_id' ORDER BY datetime ASC");
                if ($branch_sql->num_rows) {
                    $count = 0;
                    while ($branch_row = $branch_sql->fetch_assoc()) {
                        $branch_file = $branch_row['branch_file'];
                        $branch_image = $branch_row['branch_image'];
                        $branch_datetime = $branch_row['datetime'];
                        $branch_description = $branch_row['description'];
                        $count++;
                ?>
                        <div class="file-item sub-file" id="branch-<?php echo $branch_row['id']; ?>">
                            <div class="div-main-content">
                                <span class="file-icon">
                                    <img src="<?php echo $branch_image ?>" class="rounded-circle" style="width: 25px;">
                                </span>
                                --
                                <small><?php echo date('d M Y', strtotime($branch_datetime)); ?></small>
                                -
                                <span class="fw-bold" data-bs-toggle="tooltip" data-bs-placement="top" title="Branch Number <?php echo $count; ?>">BN.<?php echo $count; ?></span>
                                -
                                <a href="projects/<?php echo $branch_file; ?>" download class="text-muted" target="_blank" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="<?php echo $branch_description != '' ? $branch_description : 'No Description Provided'; ?>"><?php echo $branch_file; ?></a>
                                
                                <!-- Delete Button for Each Branch -->
                                <form action="" method="post" style="display:inline;">
                                    <input type="hidden" name="branch_id" value="<?php echo $branch_row['id']; ?>">
                                    <button type="submit" name="delete_branch_btn" class="btn text-primary border-0 btn-xs"><i class="bx bx-trash"></i></button>
                                </form>
                            </div>
                        </div>
                <?php
                    }
                } else {
                ?>
                    <div class="file-item sub-file">
                        <div class="div-main-content">
                        <div class="alert alert-primary" role="alert">
                                    <i class="bx bx-info-circle me-2"></i>No Branch found.
                                </div>
                        </div>
                    </div>
                <?php
                }
                ?>
            </div>
        </div>
    </div>
</div>
