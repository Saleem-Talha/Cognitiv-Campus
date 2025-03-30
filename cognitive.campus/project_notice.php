<div class="col-md-6 mt-3">
    <div class="card">
        <div class="card-header">
            <!-- Button to trigger the notice modal -->
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#noticeboard">
                Send Notice
            </button>

            <!-- Modal for sending notices -->
            <div class="modal fade" id="noticeboard" tabindex="-1" aria-labelledby="noticeboardLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="noticeboardLabel">Send Notice</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Notice form -->
                            <form action="" method="post">
                                <!-- Input for notice content -->
                                <div class="mb-3 form-floating">
                                    <input type="text" name="notice" class="form-control" placeholder="Notice" required>
                                    <label for="notice">Notice</label>
                                </div>
                                <!-- Submit button -->
                                <div class="mb-0">
                                    <input type="submit" value="Send Notice" class="btn btn-primary" name="send-notice">
                                </div>
                            </form>
                            <?php
                            // Handle notice submission
                            if (isset($_POST['send-notice'])) {
                                // Sanitize and prepare the notice data
                                $notice = $db->real_escape_string($_POST['notice']);
                                $encoded_id = $_GET['project-id'];
                                
                                // Insert notice into the database
                                $insert_notice = $db->query("INSERT INTO project_notice (project_id, notice, datetime) VALUES ('$project_id', '$notice', NOW())");

                                // Display success or error message
                                if ($insert_notice) {
                                    echo "<script>swal('Success', 'Notice Sent', 'success').then(() => window.location.href = 'project-reload.php?project-id=" . $encoded_id . "');</script>";
                                    $notice_message = "Check the notice board of Project : $project_name";
                                    $type = "Project";

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
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <?php
            // Handle notice removal
            if (isset($_GET['remove_notice'])) {
                $notice_id = $db->real_escape_string($_GET['remove_notice']);
                $delete_notice = $db->query("DELETE FROM project_notice WHERE id = '$notice_id'");
                
                if ($delete_notice) {
                    header('Location: project-details.php?project-id=' . $project_id);
                    exit();
                } else {
                    echo "<script>swal('Error', '" . $db->error . "', 'error');</script>";
                }
            }
            ?>
            <!-- List of notices -->
            <div class="list-group">
                <?php
                // Fetch all notices for the project
                $select_all_notice = $db->query("SELECT * FROM project_notice WHERE project_id = '$project_id' ORDER BY datetime DESC");
                
                if ($select_all_notice->num_rows) {
                    $count = 0;
                    while ($row_notice = $select_all_notice->fetch_assoc()) {
                        $notice_id = $row_notice['id'];
                        $notice_notice = $row_notice['notice'];
                        $notice_datetime = $row_notice['datetime'];
                        $count++;
                        $formatted_date = date('d M Y', strtotime($notice_datetime));
                ?>
                    <!-- Display notice in a list item -->
                    <div class="list-group-item d-flex justify-content-between align-items-center" data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo htmlspecialchars($formatted_date); ?>">
                        <span><?php echo htmlspecialchars($notice_notice); ?></span>
                        <a href="project-details.php?remove_notice=<?php echo $notice_id; ?>&project-id=<?php echo $project_id; ?>" onclick="return confirm('Are you sure you want to delete this notice?');">
                            <i class="bx bx-trash mx-2 text-primary"></i>
                        </a>
                    </div>
                <?php
                    }
                } else {
                ?>
                    <!-- No notices found -->
                    <div class="list-group-item text-center">No Notice Found</div>
                <?php
                }
                ?>
            </div>
        </div>
    </div>
</div>
