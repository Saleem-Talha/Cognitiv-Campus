<?php 
include_once('includes/header.php');
include_once('includes/db-connect.php'); // Ensure you have this file for database connection

// Initialize variables
$editMode = false;
$editData = null;

// Handle Delete
if(isset($_GET['delete'])) {
    $deleteId = $_GET['delete'];
    $deleteQuery = "DELETE FROM schedules WHERE id = ? AND user_id = ?";
    $stmt = $db->prepare($deleteQuery);
    $stmt->bind_param("is", $deleteId, $userEmail);
    
    if($stmt->execute()) {
        echo "<script>
            swal('Success', 'Schedule deleted successfully', 'success').then(() => {
                window.location.href = 'schedule.php';
            });
        </script>";
    } else {
        echo "<script>
            swal('Error', 'Error deleting schedule', 'error').then(() => {
                window.location.href = 'schedule.php';
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

    if($editMode) {
        $query = "UPDATE schedules SET course_type = ?, course_id = ?, day = ?, time = ? WHERE id = ? AND user_id = ?";
        $stmt = $db->prepare($query);
        $stmt->bind_param("sssssi", $courseType, $courseId, $day, $time, $_POST['id'], $userEmail);
    } else {
        $query = "INSERT INTO schedules (user_id, course_type, course_id, day, time) VALUES (?, ?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        $stmt->bind_param("sssss", $userEmail, $courseType, $courseId, $day, $time);
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
                window.location.href = 'schedule.php';
            });
        </script>";
        $editMode = false;
        $editData = null;
    } else {
        echo "<script>
            swal('Error', 'Error " . ($editMode ? "updating" : "adding") . " schedule', 'error').then(() => {
                window.location.href = 'schedule.php';
            });
        </script>";
    }
}

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

$schedules = $row['response'];

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

ob_end_clean();
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
                                        <button type="submit" name="submitSchedule" class="btn btn-primary"><?php echo $editMode ? 'Update' : 'Add'; ?> Schedule</button>
                                        <?php if($editMode): ?>
                                            <a href="schedule.php" class="btn btn-secondary">Cancel</a>
                                        <?php endif; ?>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <div class="">
                                <div class="">
                                    <?php
                                    $scheduleQuery = "SELECT DISTINCT s.*, 
                                                      CASE 
                                                        WHEN s.course_type = 'uniCourse' THEN cs.course_name
                                                        ELSE oc.name
                                                      END AS course_name
                                                      FROM schedules s
                                                      LEFT JOIN course_status cs ON s.course_id = cs.course_id AND s.course_type = 'uniCourse'
                                                      LEFT JOIN own_course oc ON s.course_id = oc.id AND s.course_type = 'extraCourse'
                                                      WHERE s.user_id = ?
                                                      ORDER BY FIELD(s.day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), s.time";
                                    $stmt = $db->prepare($scheduleQuery);
                                    $stmt->bind_param("s", $userEmail);
                                    $stmt->execute();
                                    $result = $stmt->get_result();

                                    if($result->num_rows > 0):
                                    ?>
                                        <div class="row">
                                            <?php while($row = $result->fetch_assoc()): ?>
                                                <div class="col-12 mb-3">
                                                    <div class="card h-100 border-0 shadow-sm">
                                                        <div class="card-body">
                                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                                <h5 class="card-title mb-0">
                                                                    <i class='bx bx-calendar me-2'></i>
                                                                    <?php echo $row['day']; ?>
                                                                </h5>
                                                                <span class="badge bg-primary">
                                                                    <i class='bx bx-time'></i>
                                                                    <?php echo date('h:i A', strtotime($row['time'])); ?>
                                                                </span>
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
                                                                <a href="?edit=<?php echo $row['id']; ?>" class="btn btn-outline-primary btn-sm me-2">
                                                                    <i class='bx bx-edit'></i> Edit
                                                                </a>
                                                                <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure you want to delete this schedule?')">
                                                                    <i class='bx bx-trash'></i> Delete
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endwhile; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-5 card">
                                            <i class='bx bx-calendar-x bx-lg text-muted'></i>
                                            <p class="mt-3">No schedules found. Start by adding a new schedule!</p>
                                        </div>
                                    <?php endif; ?>

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