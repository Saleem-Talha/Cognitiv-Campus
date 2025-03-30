<?php include_once('includes/header.php'); ?>

<!-- Main layout wrapper -->
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        
        <!-- Sidebar inclusion -->
        <?php include_once('includes/sidebar-main.php'); ?>
        
        <div class="layout-page">
            
            <!-- Navbar inclusion -->
            <?php include_once('includes/navbar.php'); ?>
            
            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">
                    
                    <!-- Page Title -->
                    <h4 class="py-3 mb-4">
                        <span class="text-muted fw-light">Notes /</span> Projects Notes
                    </h4>
                    
                    <div class="row">
                        <!-- Project Selection Form -->
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <form action="" method="get" class="flex-grow-1 d-flex flex-column justify-content-between">
                                        <div class="mb-3 form-floating">
                                            <select name="project_id" class="form-select" required>
                                                <?php
                                                // Fetch projects for the current user
                                                $select_project = $db->query("
                                                    SELECT p.id, p.name
                                                    FROM projects p
                                                    LEFT JOIN project_requests pr ON p.id = pr.project_id
                                                    WHERE p.ownerEmail = '$userEmail' OR pr.email = '$userEmail'
                                                ");

                                                // Populate project dropdown
                                                if ($select_project->num_rows) {
                                                    while ($row = $select_project->fetch_assoc()) {
                                                        $project_id = $row['id'];
                                                        $project_name = $row['name'];
                                                        echo "<option value='$project_id'>$project_name</option>";
                                                    }
                                                } else {
                                                    echo "<option value disabled selected>No projects found</option>";
                                                }
                                                ?>
                                            </select>
                                            <label for="">Choose Project</label>
                                        </div>
                                        <div class="mt-auto">
                                            <input type="submit" value="Choose" class="btn btn-primary" name="choose">
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Display Project Details and Notes -->
                    <?php if (isset($_GET['choose'])) { ?>
                        <div class="row mt-3">
                            <?php
                            // Retrieve selected project details
                            if (isset($_GET['choose'])) {
                                $project_id = $_GET['project_id'];
                                $project_query = $db->query("SELECT * FROM projects WHERE id = '$project_id'");
                                $project_row = $project_query->fetch_assoc();
                                $project_name = $project_row['name'];
                                $project_start_date = $project_row['start_date'];
                                $project_end_date = $project_row['end_date'];
                                $project_status = $project_row['status'];
                                $project_course_id = $project_row['course_id'];
                                $project_courseType = $project_row['courseType'];
                                $ownerEmail = $project_row['ownerEmail'];
                                $mainProjectFile = $project_row['project_file'];
                                $project_readme = $project_row['readme'];

                                // Fetch course name based on course type
                                if ($project_courseType == 'uniCourse') {
                                    $course_sql = $db->query("SELECT * FROM course_status WHERE course_id = '$project_course_id'");
                                    if ($course_sql->num_rows) {
                                        while ($course_row = $course_sql->fetch_assoc()) {
                                            $courseName = $course_row['course_name'];
                                        }
                                    }
                                } else {
                                    $extra_sql = $db->query("SELECT * FROM own_course WHERE id = '$project_course_id'");
                                    if ($extra_sql->num_rows) {
                                        while ($extra_row = $extra_sql->fetch_assoc()) {
                                            $courseName = $extra_row['name'];
                                        }
                                    }
                                }
                            ?>
                                <div class="col-md-3 mb-3">
                                    <div class="card h-100">
                                        <div class="card-body d-flex flex-column">
                                            <h5 class="card-title" style="font-size: 1.5rem;"><?php echo $project_name; ?></h5>
                                            <small class="<?php 
                                                if ($project_status == 'Active') {
                                                    echo "text-primary";
                                                } elseif ($project_status == 'Incomplete') {
                                                    echo "text-danger";
                                                } else {
                                                    echo 'text-success';
                                                } ?>">
                                                <?php
                                                // Format end date
                                                $end_date = !empty($project_end_date) ? date('d M Y', strtotime($project_end_date)) : 'No End Date';
                                                echo $end_date;
                                                ?>
                                            </small>
                                            <p class="mb-0 mt-auto"><span class="text-dark">Status</span>: <?php echo $project_status; ?></p>
                                            <p><span class="text-dark">Course</span>: <?php echo $courseName; ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php
                            }
                            ?>
                            <div class="col-md-9">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <!-- Button to trigger modal for adding a note page -->
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">
                                            <i class="bx bx-plus"></i> Add Note Page
                                        </button>

                                        <!-- Modal for adding a note page -->
                                        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h1 class="modal-title fs-5" id="exampleModalLabel">Add Note Page</h1>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form action="" method="post">
                                                            <div class="mb-3 form-floating">
                                                                <input type="text" id="pageTitle" name="page_title" class="form-control" placeholder="Enter page title" required>
                                                                <label for="pageTitle">Page Title</label>
                                                            </div>
                                                            <div class="mb-0">
                                                                <input type="submit" value="Add Page" name="add_page" class="btn btn-primary">
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <?php
                                        // Fetch notes for the selected project
                                        $select_pages = $db->query("
                                            SELECT np.*
                                            FROM notes_project np
                                            LEFT JOIN project_requests pr ON np.project_id = pr.project_id AND pr.email = '$userEmail'
                                            WHERE np.project_id = '$project_id' AND (np.userEmail = '$userEmail' OR pr.email = '$userEmail')
                                        ");
                                        if ($select_pages->num_rows) {
                                            echo "<ul class='list-group'>";
                                            $count = 0;
                                            while ($row = $select_pages->fetch_assoc()) {
                                                $page_id = $row['id'];
                                                $page_title = $row['page_title'];
                                                $page_datetime = $row['datetime'];
                                                $count++;
                                                $page_datetime = date('d M Y', strtotime($page_datetime));
                                                echo "<li class='list-group-item d-flex justify-content-between align-items-center'>
                                    <div class='flex-grow-1 d-flex justify-content-between align-items-center'>
                                        <div>
                                            <i class='bx bx-file me-2'></i>
                                            <span class='text-muted'>Note $count : </span>
                                            <span>$page_title</span>
                                            <small class='text-muted ms-2'>$page_datetime</small>
                                        </div>
                                        <div class='d-flex align-items-center'>
                                            <a href='javascript:void(0);' onclick='confirmDelete($page_id, $project_id)' class='btn btn-link text-danger me-2'>
                                                <i class='bx bx-trash'></i>
                                            </a>
                                            <a href='notes-page.php?project_id=$project_id&page_id=$page_id&count=$count' class='btn border'>
                                                <i class='bx bx-book-open me-2'></i><span>Open</span>
                                            </a>
                                        </div>
                                    </div>
                                </li>";
                                            }
                                            echo "</ul>";
                                        } else {
                                            echo "<p><i class='bx bx-info-circle me-2'></i>No pages found</p>";
                                        }
                                        ?>
                                        <!-- JavaScript for delete confirmation -->
                                        <script src="js/delete-project-note.js"></script>
                                        <?php
                                        // Handle note deletion
                                        if (isset($_GET['remove'])) {
                                            $remove_page_id = $_GET['remove'];
                                            $delete_page = $db->query("DELETE FROM notes_project WHERE id = '$remove_page_id' ");
                                            if ($delete_page) {
                                                echo "<script>
                                                    swal({
                                                        title: 'Success!',
                                                        text: 'Page deleted successfully',
                                                        icon: 'success',
                                                        button: 'OK',
                                                    }).then(() => {
                                                        window.location = 'notes-projects.php?choose&project_id=$project_id';
                                                    });
                                                </script>";
                                                            } else {
                                                                echo "<script>
                                                    swal({
                                                        title: 'Error!',
                                                        text: 'Failed to delete page',
                                                        icon: 'error',
                                                        button: 'OK',
                                                    });
                                                </script>";
                                                            }
                                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                
                <?php
                // Handle adding a new note page
                if (isset($_POST['add_page'])) {
                    $page_title = $_POST['page_title'];

                    $insert_page = $db->query("INSERT INTO notes_project (project_id, userEmail, page_title, datetime) VALUES ('$project_id', '$userEmail', '$page_title', now()) ");
                    if ($insert_page) {
                        $notice_message = "A new Note called : " . $page_title . " Created";
                        $type = "Notes";
                        $insert_notification = $db->query("INSERT INTO notice (userEmail, message, type) VALUES ('$userEmail', '$notice_message', '$type') ");
                            
                        echo "<script>
                    swal({
                        title: 'Success!',
                        text: 'Page added successfully',
                        icon: 'success',
                        button: 'OK',
                    }).then(() => {
                        window.location = 'notes-projects.php?choose&project_id=$project_id';
                    });
                </script>";
                    } else {
                        echo "<script>
                    swal({
                        title: 'Error!',
                        text: 'Failed to add page',
                        icon: 'error',
                        button: 'OK',
                    });
                </script>";
                    }
                }
                ?>

                <!-- Footer inclusion -->
                <?php include_once('includes/footer.php'); ?>
                <div class="content-backdrop fade"></div>
            </div>
        </div>
    </div>
    
    <!-- Overlay for layout menu toggle -->
    <div class="layout-overlay layout-menu-toggle"></div>
</div>

<!-- Footer links inclusion -->
<?php include_once('includes/footer-links.php'); ?>

<script>
    // Restrict input length for page title
    document.getElementById('pageTitle').addEventListener('input', function() {
        if (this.value.length > 30) {
            this.value = this.value.slice(0, 30);
        }
    });
</script>
