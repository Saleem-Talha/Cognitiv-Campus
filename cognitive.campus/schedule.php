<?php 
include_once('includes/header.php');
include_once('includes/db-connect.php'); // Ensure you have this file for database connection

// Initialize variables
$editMode = false;
$editData = null;
$filterStatus = isset($_GET['filter']) ? $_GET['filter'] : 'all';




// Handle Reset Schedules
if(isset($_GET['reset_schedules'])) {
    // First, count and store completed and incomplete tasks
    $countQuery = "SELECT 
                    COUNT(CASE WHEN is_completed = 'completed' THEN 1 END) as completed_count,
                    COUNT(CASE WHEN is_completed = 'incomplete' THEN 1 END) as incomplete_count
                FROM schedules 
                WHERE user_id = ?";
    $stmt = $db->prepare($countQuery);
    $stmt->bind_param("s", $userEmail);
    $stmt->execute();
    $result = $stmt->get_result();
    $counts = $result->fetch_assoc();
    
    // Get the start date of the current week (Monday)
    $weekStart = date('Y-m-d', strtotime('monday this week'));
    
    // Insert or update the report for the current week
    $reportQuery = "INSERT INTO schedule_reports (user_email, week_start_date, completed_count, incomplete_count) 
                    VALUES (?, ?, ?, ?) 
                    ON DUPLICATE KEY UPDATE 
                    completed_count = completed_count + ?, 
                    incomplete_count = incomplete_count + ?";
    $stmt = $db->prepare($reportQuery);
    $stmt->bind_param("ssiiii", $userEmail, $weekStart, $counts['completed_count'], $counts['incomplete_count'], 
                     $counts['completed_count'], $counts['incomplete_count']);
    $stmt->execute();
    
    // Reset all schedules to active
    $resetQuery = "UPDATE schedules SET is_completed = 'active' WHERE user_id = ?";
    $stmt = $db->prepare($resetQuery);
    $stmt->bind_param("s", $userEmail);
    
    if($stmt->execute()) {
        echo "<script>
            swal('Success', 'All schedules have been reset to active and statistics have been saved', 'success').then(() => {
                window.location.href = 'schedule.php" . ($filterStatus != 'all' ? "?filter=$filterStatus" : "") . "';
            });
        </script>";
    } else {
        echo "<script>
            swal('Error', 'Error resetting schedules', 'error');
        </script>";
    }
}

// Handle Status Change
if(isset($_GET['change_status'])) {
    $scheduleId = $_GET['change_status'];
    $newStatus = $_GET['status'];
    
    $updateStatusQuery = "UPDATE schedules SET is_completed = ? WHERE id = ? AND user_id = ?";
    $stmt = $db->prepare($updateStatusQuery);
    $stmt->bind_param("sis", $newStatus, $scheduleId, $userEmail);
    
    if($stmt->execute()) {
        echo "<script>
            swal('Success', 'Schedule status updated successfully', 'success').then(() => {
                window.location.href = 'schedule.php" . ($filterStatus != 'all' ? "?filter=$filterStatus" : "") . "';
            });
        </script>";
    } else {
        echo "<script>
            swal('Error', 'Error updating schedule status', 'error');
        </script>";
    }
}

// Handle Delete
if(isset($_GET['delete'])) {
    $deleteId = $_GET['delete'];
    $deleteQuery = "DELETE FROM schedules WHERE id = ? AND user_id = ?";
    $stmt = $db->prepare($deleteQuery);
    $stmt->bind_param("is", $deleteId, $userEmail);
    
    if($stmt->execute()) {
        echo "<script>
            swal('Success', 'Schedule deleted successfully', 'success').then(() => {
                window.location.href = 'schedule.php" . ($filterStatus != 'all' ? "?filter=$filterStatus" : "") . "';
            });
        </script>";
    } else {
        echo "<script>
            swal('Error', 'Error deleting schedule', 'error').then(() => {
                window.location.href = 'schedule.php" . ($filterStatus != 'all' ? "?filter=$filterStatus" : "") . "';
            });
        </script>";
    }
}

// Handle Edit
if(isset($_GET['edit'])) {
    $editId = $_GET['edit'];
    $editQuery = "SELECT * FROM schedules WHERE id = ? AND user_id = ?";
    $stmt = $db->prepare($editQuery);
    $stmt->bind_param("is", $editId, $userEmail);
    $stmt->execute();
    $result = $stmt->get_result();
    $editData = $result->fetch_assoc();
    $editMode = true;
}

// Handle Insert/Update
if(isset($_POST['submitSchedule'])) {
    $courseType = $_POST['courseType'];
    $day = $_POST['day'];
    $time = $_POST['time'];
    $courseId = ($courseType == 'uniCourse') ? $_POST['courseId'] : $_POST['extra_courseId'];
    $isCompleted = isset($_POST['is_completed']) ? $_POST['is_completed'] : 'active';

    if($editMode) {
        $query = "UPDATE schedules SET course_type = ?, course_id = ?, day = ?, time = ?, is_completed = ? WHERE id = ? AND user_id = ?";
        $stmt = $db->prepare($query);
        $stmt->bind_param("sssssis", $courseType, $courseId, $day, $time, $isCompleted, $_POST['id'], $userEmail);
    } else {
        $query = "INSERT INTO schedules (user_id, course_type, course_id, day, time, is_completed) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        $stmt->bind_param("ssssss", $userEmail, $courseType, $courseId, $day, $time, $isCompleted);
    }

    if($stmt->execute()) {
        // Get the course name
        if ($courseType == 'uniCourse') {
            $courseNameQuery = "SELECT course_name FROM course_status WHERE course_id = ?";
        } else {
            $courseNameQuery = "SELECT name AS course_name FROM own_course WHERE id = ?";
        }
        $stmtCourseName = $db->prepare($courseNameQuery);
        $stmtCourseName->bind_param("s", $courseId);
        $stmtCourseName->execute();
        $resultCourseName = $stmtCourseName->get_result();
        $courseName = $resultCourseName->fetch_assoc()['course_name'];

        // Create notification message
        $notice_message = "You have " . $db->real_escape_string($courseName) . " scheduled on " . $db->real_escape_string($day) . " at " . $db->real_escape_string($time);
        $type = "Others";
        
        // Insert notification
        $insert_notification = $db->prepare("INSERT INTO notice (userEmail, message, type) VALUES (?, ?, ?)");
        $insert_notification->bind_param("sss", $userEmail, $notice_message, $type);
        $insert_notification->execute();

        echo "<script>
            swal('Success', 'Schedule " . ($editMode ? "updated" : "added") . " successfully', 'success').then(() => {
                window.location.href = 'schedule.php";
        echo ($filterStatus != 'all' ? "?filter=$filterStatus" : "") . "';
            });
        </script>";
        $editMode = false;
        $editData = null;
    } else {
        echo "<script>
            swal('Error', 'Error " . ($editMode ? "updating" : "adding") . " schedule', 'error').then(() => {
                window.location.href = 'schedule.php";
        echo ($filterStatus != 'all' ? "?filter=$filterStatus" : "") . "';
            });
        </script>";
    }
}

// Get current week's schedule report
$weekStart = date('Y-m-d', strtotime('monday this week'));
$reportQuery = "SELECT completed_count, incomplete_count FROM schedule_reports 
                WHERE user_email = ? AND week_start_date = ?";
$stmt = $db->prepare($reportQuery);
$stmt->bind_param("ss", $userEmail, $weekStart);
$stmt->execute();
$result = $stmt->get_result();
$weekReport = $result->fetch_assoc();

// Get current schedule counts (live data)
$currentCountQuery = "SELECT 
                COUNT(CASE WHEN is_completed = 'completed' THEN 1 END) as completed_count,
                COUNT(CASE WHEN is_completed = 'incomplete' THEN 1 END) as incomplete_count,
                COUNT(CASE WHEN is_completed = 'active' THEN 1 END) as active_count
            FROM schedules 
            WHERE user_id = ?";
$stmt = $db->prepare($currentCountQuery);
$stmt->bind_param("s", $userEmail);
$stmt->execute();
$result = $stmt->get_result();
$currentCounts = $result->fetch_assoc();

// Combine stored and current counts
$totalCompleted = ($weekReport ? $weekReport['completed_count'] : 0) + $currentCounts['completed_count'];
$totalIncomplete = ($weekReport ? $weekReport['incomplete_count'] : 0) + $currentCounts['incomplete_count'];

?>

<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        <?php include_once('includes/sidebar-main.php'); ?>
        <div class="layout-page">
            <?php include_once('includes/navbar.php'); ?>
            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">
                    <h4 class="py-3 mb-4"><span class="text-muted fw-light">Schedule /</span> My Schedule</h4>
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header ">
                                    <h5 class="card-title mb-0">Optimize Your Schedule</h5>
                                </div>
                                <div class="card-body">
                                    <form id="scheduleForm">
                                        <button type="submit" class="btn btn-primary mt-3">
                                            Get Schedule Optimization
                                        </button>
                                    </form>
                                </div>
                            </div>
                            
                            <div id="results" class="mt-4"></div>
                        </div>
                    </div>
                    
                    <!-- Weekly Report Card -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">Weekly Schedule Report</h5>
                                    <a href="?reset_schedules=true" class="btn btn-sm btn-outline-primary" onclick="return confirm('This will reset all schedules to active. Weekly statistics will be saved. Continue?')">
                                        <i class='bx bx-reset'></i> Reset All Schedules
                                    </a>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="card bg-primary text-white mb-3">
                                                <div class="card-body d-flex align-items-center">
                                                    <i class='bx bx-check-circle bx-lg me-3'></i>
                                                    <div>
                                                        <h3 class="mb-0 text-white"><?php echo $totalCompleted; ?></h3>
                                                        <p class="mb-0">Completed Tasks</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card bg-primary text-white mb-3">
                                                <div class="card-body d-flex align-items-center">
                                                    <i class='bx bx-x-circle bx-lg me-3'></i>
                                                    <div>
                                                        <h3 class="mb-0 text-white"><?php echo $totalIncomplete; ?></h3>
                                                        <p class="mb-0">Incomplete Tasks</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card bg-primary text-white mb-3">
                                                <div class="card-body d-flex align-items-center">
                                                    <i class='bx bx-calendar-check bx-lg me-3'></i>
                                                    <div>
                                                        <h3 class="mb-0 text-white"><?php echo $currentCounts['active_count']; ?></h3>
                                                        <p class="mb-0">Active Tasks</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="alert alert-primary mt-3">
                                        <i class='bx bx-primary-circle me-2'></i>
                                        <small>This report shows your schedule performance for the current week (starting <?php echo date('M d, Y', strtotime('monday this week')); ?>).
                                        The "Reset All Schedules" button will save your current statistics and set all schedules to active.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
    <div class="col-12">
    <?php 
ob_start();

$query = "SELECT response FROM ai_schedules WHERE userEmail = ?";
$stmt = $db->prepare($query);
$stmt->bind_param("s", $userEmail);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$schedules = $row ? $row['response'] : '';

// Process the content if there's any response
if($schedules) {
    // Process the content
    $formattedContent = preg_replace_callback(
        '/(\d+\.) (.*?)(?=\d+\.|$)/s',
        function ($matches) {
            $sectionContent = $matches[2];
            
            // Convert **text** to colored text while keeping the rest of the line normal
            $sectionContent = preg_replace(
                '/\*\*(.*?)\*\*/',
                '<h5 class="text-primary fw-bold d-inline">$1</h5>',
                $sectionContent
            );
            
            return sprintf(
                '<div class="mb-4">
                    <h5 class="mb-3">%s %s</h5>
                    <div class="section-content">%s</div>
                </div>',
                $matches[1],
                strip_tags(explode("\n", $matches[2])[0]),
                str_replace("\n", " ", $sectionContent) // Replace newlines with spaces
            );
        },
        $schedules
    );
}

ob_end_clean();

if(isset($formattedContent) && !empty($formattedContent)):
?>

<div class="card mb-3">
    <h5 class="card-header mb-3">Cognitive AI's Suggestions</h5>
    <div class="container">
        <div class="schedule-content">
            <?php echo $formattedContent; ?>
        </div>
    </div>
</div>

<style>
.schedule-content {
    line-height: 1.6;
}
.section-content {
    text-align: justify;
}
.section-content h5 {
    display: inline;
    border: none;
    padding: 0;
    margin: 0;
}
</style>
<?php endif; ?>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="card mb-4">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0"><?php echo $editMode ? 'Edit' : 'Add'; ?> Schedule</h5>
                                </div>
                                <div class="card-body">
                                    <form action="" method="post">
                                        <?php if($editMode): ?>
                                            <input type="hidden" name="id" value="<?php echo $editData['id']; ?>">
                                        <?php endif; ?>
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="courseType" id="uniCourse" value="uniCourse" <?php echo (!$editMode || ($editMode && $editData['course_type'] == 'uniCourse')) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="uniCourse">University Course</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="courseType" id="extraCourse" value="extraCourse" <?php echo ($editMode && $editData['course_type'] == 'extraCourse') ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="extraCourse">Extra Course</label>
                                            </div>
                                        </div>
                                        <div class="mb-3" id="uniCourseSelect">
                                            <label for="courseId" class="form-label">University Courses</label>
                                            <select name="courseId" class="form-select">
                                                <option value="none">None</option>
                                                <?php 
                                                $main_sql = $db->prepare("SELECT * FROM course_status WHERE user_id = ?");
                                                $main_sql->bind_param("s", $userEmail);
                                                $main_sql->execute();
                                                $result = $main_sql->get_result();
                                                while($main_row = $result->fetch_assoc()){
                                                    $selected = ($editMode && $editData['course_id'] == $main_row['course_id']) ? 'selected' : '';
                                                    echo '<option value="'.$main_row['course_id'].'" '.$selected.'>'.$main_row['course_name'].'</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="mb-3" id="extraCourseSelect" style="display: none;">
                                            <label for="extra_courseId" class="form-label">Extra Courses</label>
                                            <select name="extra_courseId" class="form-select">
                                                <option value="none">None</option>
                                                <?php 
                                                $extra_sql = $db->prepare("SELECT * FROM own_course WHERE userEmail = ?");
                                                $extra_sql->bind_param("s", $userEmail);
                                                $extra_sql->execute();
                                                $result = $extra_sql->get_result();
                                                while($extra_row = $result->fetch_assoc()){
                                                    $selected = ($editMode && $editData['course_id'] == $extra_row['id']) ? 'selected' : '';
                                                    echo '<option value="'.$extra_row['id'].'" '.$selected.'>'.$extra_row['name'].'</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="day" class="form-label">Day</label>
                                            <select name="day" class="form-select">
                                                <option value="none">None</option>
                                                <?php
                                                $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                                                foreach($days as $day) {
                                                    $selected = ($editMode && $editData['day'] == $day) ? 'selected' : '';
                                                    echo "<option value='$day' $selected>$day</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="time" class="form-label">Time</label>
                                            <input type="time" name="time" class="form-control" value="<?php echo $editMode ? $editData['time'] : ''; ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label for="is_completed" class="form-label">Status</label>
                                            <select name="is_completed" class="form-select">
                                                <option value="active" <?php echo ($editMode && $editData['is_completed'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                                <option value="completed" <?php echo ($editMode && $editData['is_completed'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                                                <option value="incomplete" <?php echo ($editMode && $editData['is_completed'] == 'incomplete') ? 'selected' : ''; ?>>Incomplete</option>
                                            </select>
                                        </div>
                                        <button type="submit" name="submitSchedule" class="btn btn-primary"><?php echo $editMode ? 'Update' : 'Add'; ?> Schedule</button>
                                        <?php if($editMode): ?>
                                            <a href="schedule.php<?php echo ($filterStatus != 'all' ? "?filter=$filterStatus" : ""); ?>" class="btn btn-outline-primary">Cancel</a>
                                        <?php endif; ?>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <div class="mb-3">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5 class="mb-0">My Schedules</h5>
                                            <div class="btn-group" role="group">
                                                <a href="schedule.php" class="btn btn-sm <?php echo ($filterStatus == 'all' || !isset($_GET['filter'])) ? 'btn-primary' : 'btn-outline-primary'; ?>">All</a>
                                                <a href="schedule.php?filter=active" class="btn btn-sm <?php echo ($filterStatus == 'active') ? 'btn-primary' : 'btn-outline-primary'; ?>">Active</a>
                                                <a href="schedule.php?filter=completed" class="btn btn-sm <?php echo ($filterStatus == 'completed') ? 'btn-primary' : 'btn-outline-primary'; ?>">Completed</a>
                                                <a href="schedule.php?filter=incomplete" class="btn btn-sm <?php echo ($filterStatus == 'incomplete') ? 'btn-primary' : 'btn-outline-primary'; ?>">Incomplete</a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                    <?php
                                    $whereClause = "s.user_id = ?";
                                    $params = array($userEmail);
                                    $types = "s";
                                    
                                    if ($filterStatus != 'all' && isset($_GET['filter'])) {
                                        $whereClause .= " AND s.is_completed = ?";
                                        $params[] = $filterStatus;
                                        $types .= "s";
                                    }
                                    
                                    $scheduleQuery = "SELECT DISTINCT s.*, 
                                                      CASE 
                                                        WHEN s.course_type = 'uniCourse' THEN cs.course_name
                                                        ELSE oc.name
                                                      END AS course_name
                                                      FROM schedules s
                                                      LEFT JOIN course_status cs ON s.course_id = cs.course_id AND s.course_type = 'uniCourse'
                                                      LEFT JOIN own_course oc ON s.course_id = oc.id AND s.course_type = 'extraCourse'
                                                      WHERE $whereClause
                                                      ORDER BY FIELD(s.day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), s.time";
                                    $stmt = $db->prepare($scheduleQuery);
                                    $stmt->bind_param($types, ...$params);
                                    $stmt->execute();
                                    $result = $stmt->get_result();

                                    if($result->num_rows > 0):
                                    ?>
                                        <div class="row">
                                            <?php while($row = $result->fetch_assoc()): 
                                                $statusClass = 'bg-primary';
                                                $statusIcon = 'bx-calendar-check';
                                                
                                                if($row['is_completed'] == 'completed') {
                                                    $statusClass = 'bg-success';
                                                    $statusIcon = 'bx-check-circle';
                                                } else if($row['is_completed'] == 'incomplete') {
                                                    $statusClass = 'bg-danger';
                                                    $statusIcon = 'bx-x-circle';
                                                }
                                            ?>
                                                <div class="col-12 mb-3">
                                                    <div class="card h-100 border-0 shadow-sm">
                                                        <div class="card-body">
                                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                                <h5 class="card-title mb-0">
                                                                    <i class='bx bx-calendar me-2'></i>
                                                                    <?php echo $row['day']; ?>
                                                                </h5>
                                                                <div>
                                                                    <span class="badge <?php echo $statusClass; ?> me-2">
                                                                        <i class='bx <?php echo $statusIcon; ?>'></i>
                                                                        <?php echo ucfirst($row['is_completed']); ?>
                                                                    </span>
                                                                    <span class="badge bg-primary">
                                                                        <i class='bx bx-time'></i>
                                                                        <?php echo date('h:i A', strtotime($row['time'])); ?>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <p class="card-text">
                                                                <i class='bx bx-book me-2'></i>
                                                                <strong><?php echo htmlspecialchars($row['course_name']); ?></strong>
                                                            </p>
                                                            <p class="card-text">
                                                                <i class='bx bx-category me-2'></i>
                                                                <?php echo $row['course_type'] == 'uniCourse' ? 'University Course' : 'Extra Course'; ?>
                                                            </p>
                                                        </div>
                                                        <div class="card-footer bg-transparent border-0">
                                                            <div class="d-flex justify-content-end">
                                                                <?php if($row['is_completed'] != 'completed'): ?>
                                                                    <a href="?change_status=<?php echo $row['id']; ?>&status=completed<?php echo ($filterStatus != 'all' ? "&filter=$filterStatus" : ""); ?>" class="btn btn-outline-success btn-sm me-2">
                                                                        <i class='bx bx-check'></i> Mark Completed
                                                                    </a>
                                                                <?php else: ?>
                                                                    <a href="?change_status=<?php echo $row['id']; ?>&status=active<?php echo ($filterStatus != 'all' ? "&filter=$filterStatus" : ""); ?>" class="btn btn-outline-primary btn-sm me-2">
                                                                        <i class='bx bx-revision'></i> Mark Active
                                                                    </a>
                                                                <?php endif; ?>
                                                                
                                                                <?php if($row['is_completed'] != 'incomplete'): ?>
                                                                    <a href="?change_status=<?php echo $row['id']; ?>&status=incomplete<?php echo ($filterStatus != 'all' ? "&filter=$filterStatus" : ""); ?>" class="btn btn-outline-danger btn-sm me-2">
                                                                        <i class='bx bx-x'></i> Mark Incomplete
                                                                    </a>
                                                                <?php endif; ?>
                                                                
                                                                <a href="?edit=<?php echo $row['id']; ?><?php echo ($filterStatus != 'all' ? "&filter=$filterStatus" : ""); ?>" class="btn btn-outline-primary btn-sm me-2">
                                                                    <i class='bx bx-edit'></i> Edit
                                                                </a>
                                                                <a href="?delete=<?php echo $row['id']; ?><?php echo ($filterStatus != 'all' ? "&filter=$filterStatus" : ""); ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure you want to delete this schedule?')">
                                                                    <i class='bx bx-trash'></i> Delete
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                   
                                                </div>
                                            <?php endwhile; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-5">
                                            <i class='bx bx-calendar-x bx-lg text-muted'></i>
                                            <p class="mt-3">No schedules found<?php echo ($filterStatus != 'all' ? " with status '$filterStatus'" : ""); ?>. 
                                            <?php if($filterStatus != 'all'): ?>
                                                <a href="schedule.php">View all schedules</a> or start by adding a new schedule!
                                            <?php else: ?>
                                                Start by adding a new schedule!
                                            <?php endif; ?>
                                            </p>
                                        </div>
                                    <?php endif; ?>
                                    </div>
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
        document.getElementById('scheduleForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const email = "<?php echo $userEmail; ?>";
    const resultsDiv = document.getElementById('results');
    
    resultsDiv.innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary mb-3" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;

    fetch('schedule_handle_response.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'email=' + encodeURIComponent(email)
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            resultsDiv.innerHTML = `
                <div class="alert alert-danger" role="alert">
                    ${data.error}
                </div>
            `;
        } else {
            // Function to convert **text** to bold headings
            const formatBoldText = (text) => {
                return text.replace(/\*\*(.*?)\*\*/g, '<p class="fw-bold">$1</p>');
            };

            resultsDiv.innerHTML = `
                <div class="card mb-3">
                    <div class="card-header mb-2">
                        Schedule Optimization Results
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Current Schedule</h5>
                        <pre class="bg-light p-3 rounded">${formatBoldText(data.schedules)}</pre>
                        
                        <h5 class="card-title mt-4">Optimization Suggestions</h5>
                        ${data.optimization.split(/\d+\./).filter(point => point.trim()).map((point, index) => `
                            <div class="alert alert-primary" role="alert">
                                <h5 class="mb-2">Suggestion ${index + 1}</h5>
                                ${formatBoldText(point.trim())}
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;
        }
    })
    .catch(error => {
        resultsDiv.innerHTML = `
            <div class="alert alert-danger" role="alert">
                An error occurred: ${error}
            </div>
        `;
    });
});
    </script>

<script>
$(document).ready(function(){
    function toggleCourseSelect() {
        if($('#uniCourse').is(':checked')){
            $('#uniCourseSelect').show();
            $('#extraCourseSelect').hide();
        } else {
            $('#uniCourseSelect').hide();
            $('#extraCourseSelect').show();
        }
    }

    $('input[name="courseType"]').change(toggleCourseSelect);
    toggleCourseSelect(); // Call on page load
});
</script>