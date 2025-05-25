<div class="col-md-6">
    <div class="card">
        <div class="card-header">

            <?php
            include('includes/header.php');
            // Check if the current user is the owner of the project
            if ($ownerEmail == $userEmail) {
            ?>
                <!-- Button to trigger modal for adding a new user -->
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal" <?php echo ($project_status !== 'Active') ? 'disabled' : ''; ?>>
                    <i class="bx bx-plus"></i> Add User
                </button>

            <?php
            }
            ?>

            <!-- Button to open conversation view -->
            <a href="project-chat.php?project_id=<?php echo $project_id; ?>" class="btn btn-outline-primary"><i class="bx bx-chat me-2"></i> Conversation</a>


            <!-- Modal for Adding User -->
            <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="exampleModalLabel">Add User</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Form to Add a New User -->
                            <form action="" method="post" id="emailForm">
                                <div class="mb-3 form-floating">
                                    <input type="text" name="email" id="email" class="form-control" placeholder="Enter Email">
                                    <label for="email">Email</label>
                                </div>
                                <div id="emailFeedback"></div>
                                <div class="mb-0 d-flex justify-content-between">
                                    <input type="submit" value="Send Request" class="btn btn-primary me-2" name="send-request-btn" id="sendRequestBtn">
                                    <button type="button" class="btn btn-primary" id="inviteFriendBtn">Invite a Friend</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <?php
            // Handling the form submission to send a user request
            if(isset($_POST['send-request-btn'])){
                $userInfo = getUserInfo();
                $plan = $userInfo['plan'];
                $userEmail = $userInfo['email'];

                // Get the email from POST and sanitize it
                $email = trim($_POST['email']);
                
                // Check if user is trying to add themselves
                if ($email === $userEmail) {
                    echo "<script>swal('Error', 'You cannot add yourself to the project', 'error');</script>";
                } else {
                    // Check if request already exists
                    $checkEmail = $db->query("SELECT * FROM project_requests WHERE project_id = '$project_id' AND email = '$email' ");
                    
                    if($checkEmail->num_rows){
                        echo "<script>swal('Error', 'Request Already Sent', 'error');</script>";
                    } else {
                        // Get user's plan
                        $get_user_plan = $db->query("SELECT plan FROM users WHERE email = '$userEmail'");
                        $user_plan = $get_user_plan->fetch_assoc()['plan'];
                    
                        // Set user limit based on plan
                        $user_limit = ($user_plan == 'basic') ? 5 : (($user_plan == 'standard') ? 10 : PHP_INT_MAX);
                    
                        // Get current number of users in the project
                        $get_project_users = $db->query("SELECT COUNT(*) as count FROM project_requests WHERE project_id = '$project_id' AND status != 'Rejected'");
                        $project_users_count = $get_project_users->fetch_assoc()['count'];
                        
                        if ($project_users_count >= $user_limit) {
                            echo "<script>swal('Error', 'User limit reached for your plan. Please upgrade to add more users.', 'error');</script>";
                        } else {
                            $insert = $db->query("INSERT INTO project_requests (project_id, email, status, ownerEmail) VALUES ('$project_id', '$email', 'Pending', '$userEmail') ");
                            if($insert){
                                echo "<script>swal('Success', 'Request Sent', 'success');</script>";
                    
                                $notice_message = "$userName sent you an invite to join the $project_name";
                                $type = "Project";
                                $insert_notification = $db->query("INSERT INTO notice (userEmail, message, type) VALUES ('$email', '$notice_message', '$type') ");
                            } else {
                                echo "<script>swal('Error', '" . $db->error . "', 'error');</script>";
                            }
                        }
                    }
                }
            }
            ?>

        </div>
        <div class="card-body">
            <!-- Table to Display Project User Requests -->
            <div class="table-responsive">
                <table class="table table-borderless table-striped table-hover border mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Status</th>
                            <?php
                            // Display action column only if the current user is the project owner
                            if ($ownerEmail == $userEmail) {
                            ?>
                                <th>Action</th>
                            <?php } ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Fetch all project requests
                        $select_all = $db->query("SELECT * FROM project_requests WHERE project_id = '$project_id'");
                        if ($select_all->num_rows) {
                            $count = 0;
                            while ($row = $select_all->fetch_assoc()) {
                                $request_email = $row['email'];
                                $request_status = $row['status'];
                                $request_id = $row['id'];

                               // Fetch user details for the email in the request
                                $get_email_data = $db->query("SELECT * FROM users WHERE email = '$request_email'");
                                if ($get_email_data->num_rows) {
                                    $email_data = $get_email_data->fetch_assoc();
                                    $email_name = $email_data['name'];
                                    $email_image = 'img/pfps/' . $email_data['picture']; // Added the directory path
                                } else {
                                    $email_name = "N/A";
                                    $email_image = "img/pfps/default.jpg"; // Default image with correct path
                                }

                                $count++;
                        ?>
                                <tr>
                                    <td><?php echo $count; ?></td>
                                    <td>
                                        <img src="<?php echo htmlspecialchars($email_image); ?>" 
                                            alt="Profile Picture" 
                                            style="width: 30px; height: 30px; border-radius: 50%;"
                                            onerror="this.src='img/pfps/default.jpg'">
                                    </td> <td><?php echo $email_name; ?></td>
                                    <td><span class="badge bg-label-<?php echo $request_status == 'Pending' ? 'warning' : 'success' ?> me-1"><?php echo $request_status; ?></span></td>
                                    <?php
                                    // Action column for removing a request, only if the current user is the project owner
                                    if ($ownerEmail == $userEmail) {
                                    ?>
                                        <td>
                                            <a href="project-details.php?request-id=<?php echo $request_id; ?>&project-id=<?php echo $project_id; ?>&remove" class="btn text-primary border-0 btn-xs"><i class="bx bx-trash"></i></a>
                                        </td>
                                    <?php } ?>
                                </tr>
                        <?php
                            }
                        } else {
                        ?>
                            <tr>
                                <td colspan="6" class="text-center">No User Found</td>
                            </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
                
            </div>

            <?php
            // Handle the removal of a user request
            if (isset($_GET['remove'])) {
                $request_id = $_GET['request-id'];
                $project_id = $_GET['project-id'];

                // Delete the request from the database
                $delete = $db->query("DELETE FROM project_requests WHERE id = '$request_id'");
                if ($delete) {
                    header('Location: project-details.php?project-id=' . $project_id);
                } else {
                    echo "<script>swal('Error', '" . $db->error . "', 'error');</script>";
                }
            }
            ?>
        </div>
    </div>
</div>

<script>
    document.getElementById('inviteFriendBtn').addEventListener('click', function() {
    var email = document.getElementById('email').value;
    if (email) {
        fetch('project-send-invite.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'email=' + encodeURIComponent(email)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                swal('Success', 'Invitation sent successfully!', 'success');
            } else {
                swal('Error', 'Failed to send invitation: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            swal('Error', 'An error occurred while sending the invitation.', 'error');
        });
    } else {
        swal('Error', 'Please enter an email address.', 'error');
    }
});

document.getElementById('inviteFriendBtn').addEventListener('click', function() {
    var email = document.getElementById('email').value;
    if (email) {
        fetch('project-send-invite.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'email=' + encodeURIComponent(email)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Invitation Sent',
                    text: 'Your friend has been invited to join the project!',
                    confirmButtonColor: '#3085d6'
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Failed to send invitation: ' + data.message,
                    confirmButtonColor: '#d33'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'An error occurred while sending the invitation.',
                confirmButtonColor: '#d33'
            });
        });
    } else {
        Swal.fire({
            icon: 'warning',
            title: 'Email Required',
            text: 'Please enter an email address.',
            confirmButtonColor: '#3085d6'
        });
    }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const emailInput = document.getElementById('email');
    const sendRequestBtn = document.getElementById('sendRequestBtn');
    const inviteFriendBtn = document.getElementById('inviteFriendBtn');
    const emailForm = document.getElementById('emailForm');
    
    // Ensure the Send Request button is never disabled
    if (sendRequestBtn) {
        // Remove any disabled attribute that might be set
        sendRequestBtn.removeAttribute('disabled');
        
        // Override any styles that might make it appear disabled
        sendRequestBtn.style.opacity = "1";
        sendRequestBtn.style.cursor = "pointer";
    }
    
    // Only validate on form submission, not on input
    if (emailForm) {
        emailForm.addEventListener('submit', function(event) {
            const email = emailInput.value.trim();
            if (!email) {
                event.preventDefault();
                document.getElementById('emailFeedback').innerHTML = '<div class="alert alert-danger">Please enter an email address</div>';
            }
        });
    }
    
    // Invite friend function
    if (inviteFriendBtn) {
        inviteFriendBtn.addEventListener('click', function() {
            const email = emailInput.value.trim();
            
            if (!email) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Email Required',
                    text: 'Please enter an email address.',
                    confirmButtonColor: '#3085d6'
                });
                return;
            }
            
            // Send the invitation
            fetch('project-send-invite.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'email=' + encodeURIComponent(email)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Invitation Sent',
                        text: 'Your friend has been invited to join the project!',
                        confirmButtonColor: '#3085d6'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Failed to send invitation: ' + data.message,
                        confirmButtonColor: '#d33'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'An error occurred while sending the invitation.',
                    confirmButtonColor: '#d33'
                });
            });
        });
    }
});
</script>