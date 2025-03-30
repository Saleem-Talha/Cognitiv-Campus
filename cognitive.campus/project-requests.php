<?php include_once('includes/header.php'); ?>
<?php include_once('includes/validation.php'); ?>
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        <?php include_once('includes/sidebar-main.php'); ?>
        <div class="layout-page">
            <?php include_once('includes/navbar.php'); ?>
            <div class="content-wrapper">

                <div class="container-xxl flex-grow-1 container-p-y">
                    <h4 class="py-3 mb-4"><span class="text-muted fw-light">Requests /</span> All Requests</h4>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Project Requests</h5>
                                </div>
                                <div class="card-body">

                                    <table class="table table-borderless table-striped table-hover border mb-0">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Project</th>
                                                <th>Owner</th>
                                                <th>O.Name</th>
                                                <th>O.Email</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php

                                            $select_all = $db->query("SELECT * FROM project_requests WHERE email = '$userEmail' AND status = 'Pending' ");
                                            if ($select_all->num_rows) {

                                                $count = 0;
                                                while ($row = $select_all->fetch_assoc()) {

                                                    $request_project_id  = $row['project_id'];
                                                    $request_email  = $row['email'];
                                                    $request_status = $row['status'];
                                                    $request_id     = $row['id'];

                                                    $get_project_data =  $db->query("SELECT * FROM  projects WHERE id = '$request_project_id' ");
                                                    if ($get_project_data->num_rows) {

                                                        $project_data = $get_project_data->fetch_assoc();
                                                        $project_name = $project_data['name'];
                                                        $owner_email = $project_data['ownerEmail'];

                                                        $get_owner_data = $db->query("SELECT * FROM users WHERE email = '$owner_email' ");
                                                        if ($get_owner_data->num_rows) {
                                                            $owner_data = $get_owner_data->fetch_assoc();
                                                            $email_name = $owner_data['name'];
                                                            $email_image = $owner_data['picture'];
                                                        } else {
                                                            $email_name = "N/A";
                                                            $email_image = "N/A";
                                                        }
                                                    }

                                                    $count++;

                                            ?>
                                            <?php
// Query the database to get the picture name using the owner_email
$stmt = $db->prepare("SELECT picture FROM users WHERE email = ?");
$stmt->bind_param("s", $owner_email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user_data = $result->fetch_assoc();
    // Construct the full path to the image
    $email_image = 'img/pfps/' . $user_data['picture'];
} else {
    // Use a default profile picture if no match is found
    $email_image = 'img/pfps/default.jpg';
}
?>
                                                    <tr>
                                                        <td><?php echo $count; ?></td>
                                                        <td><?php echo $project_name; ?></td>
                                                        <td>
    <img src="<?php echo htmlspecialchars($email_image); ?>" alt="Profile Picture" style="width: 50px; height: 50px; border-radius: 50%;">
</td><td><?php echo $email_name; ?></td>
                                                        <td><?php echo $owner_email; ?></td>
                                                        <td><?php echo $request_status; ?></td>
                                                        <td>
                                                            <a href="project-requests.php?accept=<?php echo $request_id; ?>&ownerEmail=<?php echo $owner_email ?>&projectName=<?php echo $project_name; ?>" class="btn btn-success btn-sm">Accept</a>
                                                            <a href="project-requests.php?reject=<?php echo $request_id; ?>&ownerEmail=<?php echo $owner_email ?>&projectName=<?php echo $project_name; ?>" class="btn btn-danger btn-sm">Reject</a>
                                                        </td>
                                                    </tr>
                                                <?php

                                                }
                                            } else {
                                                ?>
                                                <tr>
                                                    <td colspan="16" class="text-center">No User Found</td>
                                                </tr>
                                            <?php
                                            }

                                            ?>
                                        </tbody>
                                    </table>

                                    <?php

if(isset($_GET['accept'])){
    $request_id  = $_GET['accept'];
    $ownerEmail  = $_GET['ownerEmail'];
    $projectName = $_GET['projectName'];

    // Get user's plan
    $get_user_plan = $db->query("SELECT plan FROM users WHERE email = '$userEmail'");
    $user_plan = $get_user_plan->fetch_assoc()['plan'];

    // Get current number of team projects
    $get_team_projects = $db->query("SELECT COUNT(*) as count FROM project_requests WHERE status = 'Accepted' AND email = '$userEmail'");
    $team_projects_count = $get_team_projects->fetch_assoc()['count'];

    // Set project limit based on plan
    $project_limit = ($user_plan == 'basic') ? 3 : (($user_plan == 'standard') ? 12 : PHP_INT_MAX);

    if($team_projects_count < $project_limit){
        $update_request = $db->query("UPDATE project_requests SET status = 'Accepted' WHERE id = '$request_id' ");
        if($update_request){
            $notice_message = $userName . " Accepted Your Project Request : " . $projectName;
            $type = "Project";
            $insert_notification = $db->query("INSERT INTO notice (userEmail, message, type) VALUES ('$ownerEmail', '$notice_message', '$type') ");
            
            if($insert_notification){
                echo "<script>
                    swal('Request Accepted', 'Your project request has been accepted.', 'success').then((value) => {
                        window.location.href = 'project-requests.php';
                    });
                </script>";
            }
        }
    } else {
        echo "<script>
            swal('Limit Reached', 'You have reached the maximum number of team projects for your plan. Please upgrade to accept more projects.', 'warning');
        </script>";
    }
}

                                    if (isset($_GET['reject'])) {
                                        $request_id  = $_GET['reject'];
                                        $ownerEmail  = $_GET['ownerEmail'];
                                        $projectName = $_GET['projectName'];
                                        $update_request = $db->query("UPDATE project_requests SET status = 'Rejected' WHERE id = '$request_id' ");
                                        if ($update_request) {

                                            $notice_message = $userName . " Rejected Your Project Request : " . $projectName;
                                            $type = "Project";
                                            $insert_notification = $db->query("INSERT INTO notice (userEmail, message, type) VALUES ('$ownerEmail', '$notice_message', '$type') ");
                                            if ($insert_notification) {
                                                echo "<script>
                            swal('Request Rejected', 'Your project request has been rejected.', 'success').then((value) => {
                                window.location.href = 'project-requests.php';
                            });
                        </script>";
                                            }
                                        }
                                    }

                                    ?>

                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <?php include_once('includes/footer.php'); ?>
                <div class="content-backdrop fade"></div>
            </div>
        </div>
    </div>

    <div class="layout-overlay layout-menu-toggle"></div>
</div>

<?php include_once('includes/footer-links.php'); ?>