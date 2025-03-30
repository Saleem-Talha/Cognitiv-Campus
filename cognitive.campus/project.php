<?php include_once('includes/header.php'); ?>
<div class="layout-wrapper layout-content-navbar">
<div class="layout-container">
<?php include_once('includes/sidebar-main.php'); ?>
<div class="layout-page">
<?php include_once('includes/navbar.php'); ?>
<div class="content-wrapper">



<div class="container-xxl flex-grow-1 container-p-y">
<div class="d-flex justify-content-between align-items-center py-3 mb-4">
    <h4><span class="text-muted fw-light">Project /</span> All Projects</h4>
    <div>
        <a href="project.php" class="btn btn-outline-primary">Reset Filters</a>
        <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">+ Add Project</a>
    </div>
</div>






<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="exampleModalLabel">Add Project</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        

        <form action="" method="post" enctype="multipart/form-data">
            <div class="mb-3 form-floating">
                <input type="text" name="name" class="form-control" placeholder="Enter Name" required accept="application/zip,application/x-rar-compressed">
                <label for="">Enter Project Name *</label>
            </div>
            <div class="mb-3 form-floating">
                <input type="file" name="file" class="form-control" title="Choose Rar/Zip file" required accept=".zip,.rar">
                <label for="">Choose Rar/Zip file *</label>
            </div>
            <div class="mb-3 form-floating">
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="courseType" id="uniCourse" value="uniCourse" checked>
                    <label class="form-check-label" for="uniCourse">University Course</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="courseType" id="extraCourse" value="extraCourse">
                    <label class="form-check-label" for="extraCourse">Extra Course</label>
                </div>
            </div>
            <div class="mb-3 form-floating" id="uniCourseSelect">
                <select name="courseId" class="form-select">
                    <option value="none">None</option>
                    <?php 
                    
                    $main_sql = $db->query("SELECT * FROM course_status WHERE user_id = '$userEmail'");
                    while($main_row = $main_sql->fetch_assoc()){
                        echo '<option value="'.$main_row['course_id'].'">'.$main_row['course_name'].'</option>';
                    }
                    
                    ?>
                </select>
                <label for="">University Courses</label>
            </div>
            <div class="mb-3 form-floating" id="extraCourseSelect" style="display: none;">
                <select name="extra_courseId" class="form-select">
                    <option value="none">None</option>
                    <?php 
                    
                    $extra_sql = $db->query("SELECT * FROM own_course WHERE userEmail = '$userEmail'");
                    while($extra_row = $extra_sql->fetch_assoc()){
                        echo '<option value="'.$extra_row['id'].'">'.$extra_row['name'].'</option>';
                    }
                    
                    ?>
                </select>
                <label for="">Extra Courses</label>
            </div>
           
            <div class="mb-3 form-floating">
                <input type="date" name="start_date" class="form-control" title="Start Date" required>
                <label for="">Choose Start Date *</label>
            </div>
            <div class="mb-3 form-floating">
                <input type="date" name="end_date" class="form-control" title="End Date">
                <label for="">Choose End Date</label>
            </div>
            <input type="submit" value="Add" class="btn btn-primary" name="add-project">
        </form>
        



      </div>
    </div>
  </div>
</div>

<?php 
        if(isset($_POST['add-project'])){
            $userInfo = getUserInfo();
            $plan = $userInfo['plan'];
            $userEmail = $userInfo['email'];

            $name       = $_POST['name'];
            $start_date = $_POST['start_date'];
            $end_date   = $_POST['end_date'];
            $status     = date('Y-m-d') < $start_date ? 'Scheduled' : 'Active';
            $courseType = $_POST['courseType'];
            $courseId   = $courseType == 'uniCourse' ? $_POST['courseId'] : $_POST['extra_courseId'];

            $project_file = $_FILES['file']['name'];
            $project_file_tmp = $_FILES['file']['tmp_name'];

            // Get the current number of projects for this user
            $project_count_query = $db->query("SELECT COUNT(*) as count FROM projects WHERE ownerEmail = '$userEmail'");
            $project_count = $project_count_query->fetch_assoc()['count'];

            // Define project limits based on plan
            $project_limit = ($plan == 'basic') ? 5 : (($plan == 'standard') ? 15 : PHP_INT_MAX);

            if ($project_count < $project_limit) {
                $insert = $db->query("INSERT INTO projects (name, start_date, end_date, status, course_id, ownerEmail, courseType, project_file) VALUES ('$name', '$start_date', '$end_date', '$status', '$courseId', '$userEmail', '$courseType', '$project_file')");

                if ($insert) {
                    echo '<script>window.location.href = "project.php";</script>';
                    move_uploaded_file($project_file_tmp, 'projects/'.$project_file);

                    $notice_message = "A new project has been Created : " . $name;
                    $type = "Project";
                    $insert_notification = $db->query("INSERT INTO notice (userEmail, message, type) VALUES ('$userEmail', '$notice_message', '$type')");
                } else {
                    echo '<script>alert("Error: '.$db->error.'");</script>';
                }
            } else {
                echo '<script>
                    swal({
                        title: "Project Limit Reached",
                        text: "You have reached the maximum number of projects allowed for your plan. Please upgrade to add more projects.",
                        icon: "warning",
                        button: "OK",
                    });
                </script>';
            }


        }
        ?>
    
<div class="row">
    <div class="col-md-12">
            <div class="card">
                <h5 class="card-header">List of All My Projects</h5>
                <div class="card-body">
                    <form method="GET" action="" class="mb-3">
                        <div class="row">
                            <div class="col-md-4">
                                <input type="date" name="start_date" class="form-control" value="<?php echo isset($_GET['start_date']) ? $_GET['start_date'] : ''; ?>" placeholder="Start Date">
                            </div>
                            <div class="col-md-4">
                                <input type="date" name="end_date" class="form-control" value="<?php echo isset($_GET['end_date']) ? $_GET['end_date'] : ''; ?>" placeholder="End Date">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Filter</button>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" name="today" class="btn btn-outline-primary w-100">Today</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="table-responsive text-nowrap">
                  <table class="table table-striped table-hover">
                    <thead>
                      <tr>
                        <th>Project</th>
                        <th>StartDate</th>
                        <th>EndDate</th>
                        <th>Course</th>
                        <th>Users</th>
                        <th>Status</th>
                        <th>Summary</th>
                      </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                      

                      <?php 
                      
                        $where = "WHERE ownerEmail = '$userEmail'";
                        
                        if (isset($_GET['today'])) {
                            $today = date('Y-m-d');
                            $where .= " AND start_date = '$today'";
                        } elseif (isset($_GET['start_date']) && isset($_GET['end_date'])) {
                            $start_date = $_GET['start_date'];
                            $end_date = $_GET['end_date'];
                            $where .= " AND ((start_date BETWEEN '$start_date' AND '$end_date') 
                                        OR (end_date BETWEEN '$start_date' AND '$end_date') 
                                        OR (start_date <= '$start_date' AND (end_date >= '$end_date' OR end_date = ''))
                                        OR ('$start_date' <= end_date AND '$end_date' >= start_date))";
                        }

                        $sql_data = $db->query("SELECT * FROM projects $where");
                        if($sql_data->num_rows){
                            while($row = $sql_data->fetch_assoc()){
                                $encodedId = encodeId($row['id']);
                                $name       = $row['name'];
                                $start_date = $row['start_date'];
                                $end_date   = $row['end_date'];
                                $status     = $row['status'];

                                ?>
                               <tr onclick="window.location='project-details.php?project-id=<?php echo $encodedId; ?>';" style="cursor: pointer;">
                                    <td>
                                        <i class="bx bxl-bootstrap bx-sm text-primary me-3"></i>
                                        <span class="fw-medium"><?php echo $name; ?></span>
                                    </td>
                                    <td><?php echo date('d M Y', strtotime($start_date)); ?></td>
                                    <td><?php echo $end_date != '' ? date('d M Y', strtotime($end_date)) : 'No-End-Date'; ?></td>
                                    <td>
                                    <?php 
                                    
                                    $courseType = $row['courseType'];
                                    $courseId   = $row['course_id'];

                                    if($courseType == 'uniCourse'){
                                        $course_sql = $db->query("SELECT * FROM course_status WHERE course_id = '$courseId'");
                                        if($course_sql->num_rows){
                                            while($course_row = $course_sql->fetch_assoc()){
                                                echo $course_row['course_name'];
                                            }
                                        }
                                    }else{
                                        $extra_sql = $db->query("SELECT * FROM own_course WHERE id = '$courseId'");
                                        if($extra_sql->num_rows){
                                            while($extra_row = $extra_sql->fetch_assoc()){
                                                echo $extra_row['name'];
                                            }
                                        }
                                    }
                                    
                                    ?>
                                    </td>
                                    <td>
                                    <ul class="list-unstyled users-list m-0 avatar-group d-flex align-items-center">
    <?php 
    // Display owner information
    echo '<li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" class="avatar avatar-xs pull-up" title="' . htmlspecialchars($userName) . '">
            <img src="' . htmlspecialchars($userProfile) . '" alt="Avatar" class="rounded-circle" />
          </li>';
    
    // Get project requests for the current project
    $get_project_requests = $db->query("SELECT * FROM project_requests WHERE project_id = '$row[id]' AND status = 'Accepted'");
    
    if ($get_project_requests->num_rows) {
        while ($project_requests_data = $get_project_requests->fetch_assoc()) {
            $user_email = $project_requests_data['email'];
            
            // Fetch user details from the users table
            $get_user_data = $db->query("SELECT name, picture FROM users WHERE email = '$user_email'");
            if ($get_user_data->num_rows) {
                $user_data = $get_user_data->fetch_assoc();
                $user_name = $user_data['name'];
                $user_profile = 'img/pfps/' . $user_data['picture']; // Construct the full image path
                
                // Display the user's profile picture and tooltip
                echo '<li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" class="avatar avatar-xs pull-up" title="' . htmlspecialchars($user_name) . '">
                        <img src="' . htmlspecialchars($user_profile) . '" alt="Avatar" class="rounded-circle" />
                      </li>';
            }
        }
    }
    ?>
</ul>

                                    </td>
                                    <td>
                                        <?php
                                        $status_color = '';
                                        switch ($status) {
                                            case 'Active':
                                                $status_color = 'bg-label-primary';
                                                break;
                                            case 'Incomplete':
                                                $status_color = 'bg-label-danger';
                                                break;
                                            case 'Completed':
                                                $status_color = 'bg-label-success';
                                                break;
                                            default:
                                                $status_color = 'bg-label-primary';
                                        }
                                        ?>
                                        <span class="badge <?php echo $status_color; ?> me-1"><?php echo $status; ?></span>
                                    </td>
                                    <td>
                                         <a href="project-summary.php?project-id=<?php echo $encodedId; ?>" class="btn btn-sm btn-outline-primary">
                                             <i class="bx bx-file me-1"></i> Summary
                                         </a>
                                    </td>
                                </tr>
                                <?php


                            }
                        }else{
                            echo '<tr><td colspan="7" class="text-center">No Projects Found</td></tr>';
                        }

                      ?>

                    </tbody>
                  </table>
                </div>
              </div>
</div>

<div class="col-md-12 my-5">
    <div class="card">
        <div class="card-header">
            <h5>Teamspace Projects</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="" class="mb-3">
                <div class="row">
                    <div class="col-md-4">
                        <input type="date" name="new_start_date" class="form-control" value="<?php echo isset($_GET['new_start_date']) ? $_GET['new_start_date'] : ''; ?>" placeholder="Start Date">
                    </div>
                    <div class="col-md-4">
                        <input type="date" name="new_end_date" class="form-control" value="<?php echo isset($_GET['new_end_date']) ? $_GET['new_end_date'] : ''; ?>" placeholder="End Date">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" name="new_today" class="btn btn-outline-primary w-100">Today</button>
                    </div>
                </div>
            </form>

            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Project</th>
                        <th>StartDate</th>
                        <th>EndDate</th>
                        <th>Course</th>
                        <th>Users</th>
                        <th>Status</th>
                        <th>Summary</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    
                    $new_start_date = isset($_GET['new_start_date']) ? $_GET['new_start_date'] : '';
                    $new_end_date = isset($_GET['new_end_date']) ? $_GET['new_end_date'] : '';
                    
                    // Check if "Today" button is clicked
                    if (isset($_GET['new_today'])) {
                        $new_start_date = $new_end_date = date('Y-m-d');
                    }
                    
                    $date_condition = "";
                    if ($new_start_date && $new_end_date) {
                        $date_condition = " AND (projects.start_date BETWEEN '$new_start_date' AND '$new_end_date' OR projects.end_date BETWEEN '$new_start_date' AND '$new_end_date')";
                    } elseif ($new_start_date) {
                        $date_condition = " AND projects.start_date >= '$new_start_date'";
                    } elseif ($new_end_date) {
                        $date_condition = " AND projects.end_date <= '$new_end_date'";
                    }
                    
                    $accepted_requests = $db->query("SELECT * FROM project_requests 
                                                     JOIN projects ON project_requests.project_id = projects.id
                                                     WHERE project_requests.email = '$userEmail' 
                                                     AND project_requests.status = 'Accepted'
                                                     $date_condition");
                    
                    $accepted_requests = $db->query("SELECT * FROM project_requests WHERE email = '$userEmail' AND status = 'Accepted' ");
                                            if ($accepted_requests->num_rows) {
                                                while ($requests_data = $accepted_requests->fetch_assoc()) {
                                                    
                                                    $get_project_id = $requests_data['project_id'];
                                                    $get_project_data = $db->query("SELECT * FROM projects WHERE id = '$get_project_id' ");
                                                    if ($get_project_data->num_rows) {
                                                        $project_data = $get_project_data->fetch_assoc();
                                                        $team_id = $project_data['id'];
                                                        $encoded_team_id = encodeId($team_id);
                                                        $project_name = $project_data['name'];
                                                        $project_start_date = $project_data['start_date'];
                                                        $project_end_date = $project_data['end_date'];
                                                        $project_status = $project_data['status'];
                                                        $project_course_id = $project_data['course_id'];
                                                        $project_courseType = $project_data['courseType'];
                                                        

                                                        // Fetch course name
                                                        if ($project_courseType == 'uniCourse') {
                                                            $course_sql = $db->query("SELECT * FROM course_status WHERE course_id = '$project_course_id'");
                                                            if ($course_sql->num_rows) {
                                                                while ($course_row = $course_sql->fetch_assoc()) {
                                                                    $course_name = $course_row['course_name'];
                                                                }
                                                            }
                                                        } else {
                                                            $extra_sql = $db->query("SELECT * FROM own_course WHERE id = '$project_course_id'");
                                                            if ($extra_sql->num_rows) {
                                                                while ($extra_row = $extra_sql->fetch_assoc()) {
                                                                    $course_name = $extra_row['name'];
                                                                }
                                                            }
                                                        }

                                                        // Fetch users
                                                        $get_project_requests = $db->query("SELECT * FROM project_requests WHERE project_id = '$get_project_id' AND status = 'Accepted' ");
if ($get_project_requests->num_rows) {
    $users = '';
    
    // Loop through each project request
    while ($project_requests_data = $get_project_requests->fetch_assoc()) {
        $user_email = $project_requests_data['email'];
        
        // Fetch user details from the users table
        $get_user_data = $db->query("SELECT name, picture FROM users WHERE email = '$user_email' ");
        if ($get_user_data->num_rows) {
            $user_data = $get_user_data->fetch_assoc();
            $user_name = $user_data['name'];
            $user_profile = 'img/pfps/' . $user_data['picture']; // Construct full image path
            
            // Append user to the list
            $users .= '<li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" class="avatar avatar-xs pull-up" title="' . htmlspecialchars($user_name) . '">
                          <img src="' . htmlspecialchars($user_profile) . '" alt="Avatar" class="rounded-circle" />
                       </li>';
        }
    }

    // Add project owner to the user list
    $owner_email = $project_data['ownerEmail'];
    $get_owner_data = $db->query("SELECT name, picture FROM users WHERE email = '$owner_email' ");
    if ($get_owner_data->num_rows) {
        $owner_data = $get_owner_data->fetch_assoc();
        $owner_name = $owner_data['name'];
        $owner_profile = 'img/pfps/' . $owner_data['picture']; // Construct full image path
        
        // Append owner to the list
        $users .= '<li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" class="avatar avatar-xs pull-up" title="' . htmlspecialchars($owner_name) . '">
                      <img src="' . htmlspecialchars($owner_profile) . '" alt="Avatar" class="rounded-circle" />
                   </li>';
    }
}



                            ?>
                           <tr onclick="window.location='project-details.php?project-id=<?php echo $encoded_team_id; ?>';" style="cursor: pointer;">
                                <td>
                                    <i class="bx bxl-bootstrap bx-sm text-primary me-3"></i>
                                    <span class="fw-medium"><?php echo $project_name; ?></span>
                                </td>
                                <td><?php echo date('d M Y', strtotime($project_start_date)); ?></td>
                                <td><?php echo $project_end_date != '' ? date('d M Y', strtotime($project_end_date)) : 'No-End-Date'; ?></td>
                                <td><?php echo $course_name; ?></td>
                                <td>
                                    <ul class="list-unstyled users-list m-0 avatar-group d-flex align-items-center">
                                        <?php echo $users; ?>
                                    </ul>
                                </td>
                                <td>
                                    <?php
                                    $status_color = '';
                                    switch ($project_status) {
                                        case 'Active':
                                            $status_color = 'bg-label-success';
                                            break;
                                        case 'Incopmlete':
                                            $status_color = 'bg-label-danger';
                                            break;
                                        case 'Completed':
                                            $status_color = 'bg-label-info';
                                            break;
                                        default:
                                            $status_color = 'bg-label-primary';
                                    }
                                    ?>
                                    <span class="badge <?php echo $status_color; ?> me-1"><?php echo $project_status; ?></span>
                                </td>
                                <td>
                                    <a href="project-summary.php?project-id=<?php echo $encoded_team_id; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bx bx-file me-1"></i> Summary
                                    </a>
                                </td>
                            </tr>
                            <?php
                            }
                        }                        
                    } else {
                        echo '<tr><td colspan="6" class="text-center">No Projects Found</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
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
<script>
$(document).ready(function(){
    $('input[name="courseType"]').change(function(){
        if($(this).val() == 'uniCourse'){
            $('#uniCourseSelect').show();
            $('#extraCourseSelect').hide();
            } else {
                $('#uniCourseSelect').hide();
                $('#extraCourseSelect').show();
             }
    });
});
</script>