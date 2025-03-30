<?php include_once('includes/header.php'); ?>

<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        <?php include_once('includes/sidebar-main.php'); ?>
        
        <div class="layout-page">
            <?php include_once('includes/navbar.php'); ?>
            
            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">
                    
                    <!-- Header with title and filter reset button -->
                    <div class="d-flex justify-content-between align-items-center py-3 mb-4">
                        <h4><span class="text-muted fw-light">Notes /</span> All Notes</h4>
                        <a href="notes-all.php" class="btn btn-outline-primary">Reset Filter</a>
                    </div>

                    <div class="row">
                        <!-- Project Notes Section -->
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="card-title">Project Notes</h5>
                                    
                                    <!-- Filter Form for Project Notes -->
                                    <form method="GET" action="" class="mb-0">
                                        <div class="row g-2">
                                            <div class="col-auto">
                                                <input type="date" name="start_date" class="form-control form-control-sm" value="<?php echo isset($_GET['start_date']) ? htmlspecialchars($_GET['start_date']) : ''; ?>" placeholder="Start Date">
                                            </div>
                                            <div class="col-auto">
                                                <input type="date" name="end_date" class="form-control form-control-sm" value="<?php echo isset($_GET['end_date']) ? htmlspecialchars($_GET['end_date']) : ''; ?>" placeholder="End Date">
                                            </div>
                                            <div class="col-auto">
                                                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                                            </div>
                                            <div class="col-auto">
                                                <button type="submit" name="today" class="btn btn-outline-primary btn-sm">Today</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                
                                <div class="card-body">
                                    <?php
                                    // Initial query to fetch notes for the current user
                                    $where_clause = "WHERE userEmail = '$userEmail'";

                                    // Apply filters based on user input
                                    if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
                                        $start_date = $db->real_escape_string($_GET['start_date']);
                                        $end_date = $db->real_escape_string($_GET['end_date']);
                                        $where_clause .= " AND DATE(datetime) BETWEEN '$start_date' AND '$end_date'";
                                    } elseif (isset($_GET['today'])) {
                                        $today = date('Y-m-d');
                                        $where_clause .= " AND DATE(datetime) = '$today'";
                                    }

                                    // Fetch and display notes based on the constructed query
                                    $select_pages = $db->query("SELECT * FROM notes_project $where_clause ORDER BY datetime DESC");
                                    if ($select_pages->num_rows) {
                                        echo "<ul class='list-group'>";
                                        $count = 0;
                                        while ($row = $select_pages->fetch_assoc()) {
                                            $page_id = $row['id'];
                                            $page_title = $row['page_title'];
                                            $page_datetime = $row['datetime'];
                                            $project_id = $row['project_id'];
                                            
                                            // Fetch project name for each note
                                            $project_query = $db->query("SELECT * FROM projects WHERE id = '$project_id'");
                                            $project_row = $project_query->fetch_assoc();
                                            $project_name = $project_row['name'];
                                            
                                            $count++;
                                            $formatted_date = date('d M Y', strtotime($page_datetime));
                                            
                                            echo "<li class='list-group-item d-flex justify-content-between align-items-center'>
                                                    <div class='flex-grow-1 d-flex justify-content-between align-items-center'>
                                                        <div>
                                                            <span class='me-2 border px-3 p-2 rounded-2'>$project_name</span>
                                                            <i class='bx bx-file me-2'></i>
                                                            <span class='text-muted'>Note $count : </span>
                                                            <span>$page_title</span>
                                                            <small class='text-muted ms-2'>$formatted_date</small>
                                                        </div>
                                                        <div class='d-flex align-items-center'>
                                                            <a href='notes-page.php?project_id=$project_id&page_id=$page_id&count=$count' class='btn border'>
                                                                <i class='bx bx-book-open me-2'></i><span>Open</span>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </li>";
                                        }
                                        echo "</ul>";
                                    } else {
                                        echo "<div class='alert alert-primary' role='alert'>
                                        <i class='bx bx-info-circle me-2'></i>No pages found.
                                    </div>";
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>

                        <!-- Course Notes Section -->
                        <div class="col-md-12 mt-5">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">Course Notes</h5>
                                    
                                    <!-- Filter Form for Course Notes -->
                                    <div class="d-flex align-items-center">
                                        <form method="GET" action="" class="me-3">
                                            <div class="row g-2">
                                                <div class="col-auto">
                                                    <input type="date" name="course_start_date" class="form-control form-control-sm" value="<?php echo isset($_GET['course_start_date']) ? htmlspecialchars($_GET['course_start_date']) : ''; ?>" placeholder="Start Date">
                                                </div>
                                                <div class="col-auto">
                                                    <input type="date" name="course_end_date" class="form-control form-control-sm" value="<?php echo isset($_GET['course_end_date']) ? htmlspecialchars($_GET['course_end_date']) : ''; ?>" placeholder="End Date">
                                                </div>
                                                <div class="col-auto">
                                                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                                                </div>
                                                <div class="col-auto">
                                                    <button type="submit" name="course_today" class="btn btn-outline-primary btn-sm">Today</button>
                                                </div>
                                            </div>
                                        </form>
                                        
                                        <div>
                                            <label for="typeFilter" class="form-label me-2">Filter by Type:</label>
                                            <select id="typeFilter" class="form-select form-select-sm d-inline-block w-auto">
                                                <option value="all">All</option>
                                                <option value="Assignment">Assignment</option>
                                                <option value="Quiz">Quiz</option>
                                                <option value="Notes">Notes</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card-body">
                                    <?php
                                    // Query to fetch course notes
                                    $course_where_clause = "WHERE userEmail = '$userEmail'";

                                    if (isset($_GET['course_start_date']) && isset($_GET['course_end_date'])) {
                                        $course_start_date = $db->real_escape_string($_GET['course_start_date']);
                                        $course_end_date = $db->real_escape_string($_GET['course_end_date']);
                                        $course_where_clause .= " AND DATE(datetime) BETWEEN '$course_start_date' AND '$course_end_date'";
                                    } elseif (isset($_GET['course_today'])) {
                                        $today = date('Y-m-d');
                                        $course_where_clause .= " AND DATE(datetime) = '$today'";
                                    }

                                    // Fetch and display course notes based on the constructed query
                                    $select_pages = $db->query("SELECT * FROM notes_course $course_where_clause ORDER BY datetime DESC");
                                    if ($select_pages->num_rows) {
                                        echo "<ul class='list-group' id='notesList'>";
                                        $count = 0;
                                        while ($row = $select_pages->fetch_assoc()) {
                                            $page_id = $row['id'];
                                            $page_title = $row['page_title'];
                                            $page_type = $row['type'];
                                            $page_datetime = $row['datetime'];
                                            $courseId = $row['courseId'];
                                            $courseType = $row['courseType'];

                                            // Determine course name based on course type
                                            if ($courseType == 'uniCourse') {
                                                $course_sql = $db->query("SELECT * FROM course_status WHERE course_id = '$courseId'");
                                                if ($course_sql->num_rows) {
                                                    $course_row = $course_sql->fetch_assoc();
                                                    $courseName = $course_row['course_name'];
                                                }
                                            } else {
                                                $extra_sql = $db->query("SELECT * FROM own_course WHERE id = '$courseId'");
                                                if ($extra_sql->num_rows) {
                                                    $extra_row = $extra_sql->fetch_assoc();
                                                    $courseName = $extra_row['name'];
                                                }
                                            }

                                            $count++;
                                            $formatted_date = date('d M Y', strtotime($page_datetime));

                                            echo "<li class='list-group-item d-flex justify-content-between align-items-center note-item' data-type='$page_type'>
                                                    <div class='flex-grow-1 d-flex justify-content-between align-items-center'>
                                                        <div>
                                                            <span class='me-2 border px-3 p-2 rounded-2'>$courseName</span>
                                                            <i class='bx bx-file me-2'></i>
                                                            <span class='text-muted'>Note $count : </span>
                                                            <span>$page_title</span>
                                                            <span class='text-muted filter-based-type'> : $page_type : </span>
                                                            <small class='text-muted ms-2'>$formatted_date</small>
                                                        </div>
                                                        <div class='d-flex align-items-center'>
                                                            <a href='notes-page-course.php?courseId=" . ($courseType == 'uniCourse' ? $courseId : $courseId) . "&page_id=$page_id&count=$count&courseType=$courseType' class='btn border'>
                                                                <i class='bx bx-book-open me-2'></i><span>Open</span>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </li>";
                                        }
                                        echo "</ul>";
                                    } else {
                                        echo "<div class='alert alert-primary' role='alert'>
                                        <i class='bx bx-info-circle me-2'></i>No pages found.
                                    </div>";
                                    }
                                    ?>
                                </div>

                                <!-- JavaScript for filtering notes based on type -->
                                <script src="js/notes-type-filter.js"></script>
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
