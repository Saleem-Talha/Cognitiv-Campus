
<?php include_once('includes/header.php'); ?>
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        <?php include_once('includes/sidebar-main.php'); ?>
        <div class="layout-page">
            <?php include_once('includes/navbar.php'); ?>
            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">
                    <h4 class="py-3 mb-4"><span class="mb-2 text-muted fw-light">Student Analytics</span></h4>

                    <?php
                    // Check if logged in user has Google Classroom access
                    if ($userInfo['auth_type'] == 'google' && $userInfo['classroomService']) {
                        // Fetch courses for the logged-in user from the course_status table
                        $stmt = $db->prepare("SELECT course_id, course_name FROM course_status WHERE user_id = ?");
                        $stmt->bind_param("s", $userInfo['email']);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $courses = [];
                        while ($row = $result->fetch_assoc()) {
                            $courses[] = $row;
                        }

                        // Check if we have any courses
                        if (!empty($courses)) {
                            
                            // Check if view_grades action is triggered
                            if (isset($_GET['action']) && $_GET['action'] === 'view_grades' && isset($_GET['course_id'])) {
                                $courseId = $_GET['course_id'];
                                $courseName = '';
                                
                                // Find the course name from our array of courses
                                foreach ($courses as $course) {
                                    if ($course['course_id'] === $courseId) {
                                        $courseName = $course['course_name'];
                                        break;
                                    }
                                }
                                
                                // Fetch grades for this course
                                $gradeStmt = $db->prepare("
                                    SELECT assignment_id, assignment_title, assignment_type, grade, max_points, 
                                           submission_state, submitted_at
                                    FROM student_grades 
                                    WHERE user_id = ? AND course_id = ?
                                ");
                                $gradeStmt->bind_param("ss", $userInfo['email'], $courseId);
                                $gradeStmt->execute();
                                $gradesResult = $gradeStmt->get_result();
                                $grades = [];
                                while ($row = $gradesResult->fetch_assoc()) {
                                    $grades[] = $row;
                                }
                                
                                // Prepare modal content
                                $modalContent = '';
                                if (!empty($grades)) {
                                    $modalContent .= '<h6 class="mb-3">' . htmlspecialchars($courseName) . '</h6>';
                                    $modalContent .= '<div class="table-responsive">';
                                    $modalContent .= '<table class="table table-bordered">';
                                    $modalContent .= '<thead><tr><th>Assignment</th><th>Type</th><th>Grade</th><th>Max Points</th><th>Status</th><th>Submission Date</th></tr></thead>';
                                    $modalContent .= '<tbody>';
                                    
                                    foreach ($grades as $grade) {
                                        $percentage = $grade['max_points'] > 0 ? ((floatval($grade['grade']) / floatval($grade['max_points'])) * 100) . '%' : 'N/A';
                                        $submittedAt = $grade['submitted_at'] ? date('Y-m-d H:i:s', strtotime($grade['submitted_at'])) : 'Not submitted';
                                        
                                        $modalContent .= '<tr>';
                                        $modalContent .= '<td>' . htmlspecialchars($grade['assignment_title']) . '</td>';
                                        $modalContent .= '<td>' . ucfirst(htmlspecialchars($grade['assignment_type'])) . '</td>';
                                        $modalContent .= '<td>' . ($grade['grade'] !== null ? htmlspecialchars($grade['grade']) : 'Not graded') . ' (' . $percentage . ')</td>';
                                        $modalContent .= '<td>' . htmlspecialchars($grade['max_points']) . '</td>';
                                        $modalContent .= '<td>' . htmlspecialchars($grade['submission_state']) . '</td>';
                                        $modalContent .= '<td>' . htmlspecialchars($submittedAt) . '</td>';
                                        $modalContent .= '</tr>';
                                    }
                                    
                                    $modalContent .= '</tbody></table></div>';
                                } else {
                                    $modalContent = '<p>No grades found for this course.</p>';
                                }
                                
                                // JavaScript to show the modal automatically when page loads
                                echo '<script>
                                    document.addEventListener("DOMContentLoaded", function() {
                                        const gradeDetailsContent = document.getElementById("gradeDetailsContent");
                                        gradeDetailsContent.innerHTML = `' . str_replace('`', '\`', $modalContent) . '`;
                                        const modal = new bootstrap.Modal(document.getElementById("gradeDetailsModal"));
                                        modal.show();
                                    });
                                </script>';
                            }
                    ?>
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <div class="card">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h5 class="card-title">Your Enrolled Courses</h5>
                                            <button id="fetchGradesBtn" class="btn btn-primary">Fetch Latest Grades</button>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Course Name</th>
                                                            <th>Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($courses as $course) : ?>
                                                            <tr>
                                                                <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                                                                <td>
                                                                    <!-- Changed from button to link with GET parameters -->
                                                                    <a href="?action=view_grades&course_id=<?php echo htmlspecialchars($course['course_id']); ?>" class="btn btn-sm btn-primary">View Grades</a>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title">AI Analytics</h5>
                                        </div>
                                        <div class="card-body">
                                            <button class="btn btn-primary mb-3" id="generateAiBtn">Get AI Analytics</button>
                                            
                                            <?php
                                            // Fetch insights from the database
                                            $insightStmt = $db->prepare("SELECT id, insight_text, created_at FROM student_grade_insights WHERE user_id = ? ORDER BY created_at DESC");
                                            $insightStmt->bind_param("s", $userEmail);
                                            $insightStmt->execute();
                                            $insightResult = $insightStmt->get_result();
                                            
                                            if ($insightResult->num_rows > 0) {
                                                echo '<div class="list-group mb-4">';
                                                echo '<div class="list-group-item active">Available Insights</div>';
                                                
                                                $insights = array();
                                                $counter = 0;
                                                
                                                while ($insight = $insightResult->fetch_assoc()) {
                                                    $insightId = 'insight-' . $counter;
                                                    $insights[] = array(
                                                        'id' => $insightId,
                                                        'text' => $insight['insight_text'],
                                                        'date' => $insight['created_at']
                                                    );
                                                    
                                                    echo '<div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">';
                                                    echo '<div>Generated on: ' . date('Y-m-d H:i', strtotime($insight['created_at'])) . '</div>';
                                                    echo '<button class="btn btn-sm btn-outline-primary view-insight-btn" data-insight-id="' . $insightId . '">View Insight</button>';
                                                    echo '</div>';
                                                    
                                                    $counter++;
                                                }
                                                
                                                echo '</div>';
                                                
                                                // Container for displaying selected insight
                                                echo '<div id="insight-content-container" style="display: none;" class="card mb-4 border-primary">';
                                                echo '<div class="card-header text-primary d-flex justify-content-between align-items-center">';
                                                echo '<h6 class="mb-0" id="insight-date-display"></h6>';
                                                echo '<button class="btn-close" id="close-insight-btn" aria-label="Close"></button>';
                                                echo '</div>';
                                                echo '<div class="card-body" id="insight-content">';
                                                echo '</div>';
                                                echo '</div>';
                                                
                                                // Store insights data in JavaScript
                                                echo '<script>';
                                                echo 'const insightsData = ' . json_encode($insights) . ';';
                                                echo '</script>';
                                            } else {
                                                echo '<div class="alert alert-primary">';
                                                echo '<p class="mb-0">No analytics insights available yet. Click on "Get AI Analytics" to generate insights based on your grades.</p>';
                                                echo '</div>';
                                            }
                                            ?>
                                            
                                            <div id="gradesContainer">
                                                <!-- Container for insights list and details -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Add a hidden loading indicator -->
                            <div id="loadingOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center;">
                                <div class="spinner-border text-light" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>

                    <?php
                        } else {
                            echo '<div class="alert alert-info">You are not enrolled in any courses yet.</div>';
                        }
                    } else {
                        echo '<div class="alert alert-warning">Google Classroom integration is only available for users logged in with Google.</div>';
                    }
                    ?>

                    <!-- Grade details modal -->
                    <div class="modal fade" id="gradeDetailsModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Course Grades</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body" id="gradeDetailsContent">
                                    <!-- Grade details will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="container">
                <?php include_once('dashboard-student-analysis.php'); ?>
                </div>
                <?php include_once('includes/footer.php'); ?>
                <div class="content-backdrop fade"></div>
            </div>
        </div>
    </div>
    <div class="layout-overlay layout-menu-toggle"></div>
</div>

<?php include_once('includes/footer-links.php'); ?>

<!-- Add JavaScript for handling the fetch grades functionality (keeping only the fetch all grades part) -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fetch grades button click handler
    const fetchGradesBtn = document.getElementById('fetchGradesBtn');
    if (fetchGradesBtn) {
        fetchGradesBtn.addEventListener('click', function() {
            document.getElementById('loadingOverlay').style.display = 'flex';
            
            fetch('student_fetch_grades.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'fetch_all_grades'
                })
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('loadingOverlay').style.display = 'none';
                
                if (data.success) {
                    alert('Grades fetched and saved successfully!');
                    // Refresh the page to show updated data
                    window.location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                document.getElementById('loadingOverlay').style.display = 'none';
                alert('An error occurred while fetching grades: ' + error);
            });
        });
    }
    const getAnalyticsBtn = document.getElementById('generateAiBtn');
    if (getAnalyticsBtn) {
        getAnalyticsBtn.addEventListener('click', function() {
            // Show loading state
            const originalText = getAnalyticsBtn.textContent;
            getAnalyticsBtn.innerHTML = '<span class="spinner-border spinner-border-sm mx-2" role="status" aria-hidden="true"></span> Generating...';
            getAnalyticsBtn.disabled = true;
            document.getElementById('loadingOverlay').style.display = 'flex';
            
            fetch('students_get_ai_analytics.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                // Hide loading state
                document.getElementById('loadingOverlay').style.display = 'none';
                getAnalyticsBtn.innerHTML = originalText;
                getAnalyticsBtn.disabled = false;
                
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                // Handle error and restore button state
                document.getElementById('loadingOverlay').style.display = 'none';
                getAnalyticsBtn.innerHTML = originalText;
                getAnalyticsBtn.disabled = false;
                alert('An error occurred while generating analytics: ' + error);
            });
        });
    }
// Add event listeners for the insight view buttons
const viewButtons = document.querySelectorAll('.view-insight-btn');
    const insightContainer = document.getElementById('insight-content-container');
    const insightContent = document.getElementById('insight-content');
    const insightDateDisplay = document.getElementById('insight-date-display');
    const closeInsightBtn = document.getElementById('close-insight-btn');
    
    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const insightId = this.getAttribute('data-insight-id');
            const insightData = insightsData.find(insight => insight.id === insightId);
            
            if (insightData) {
                // Format the insight text
                let formattedText = insightData.text;
                
                // Replace headers
                formattedText = formattedText.replace(/###\s+(.*?)(\n|$)/g, '<h3 class="text-primary mb-3">$1</h3>');
                formattedText = formattedText.replace(/####\s+(.*?)(\n|$)/g, '<h4 class="text-secondary mb-2">$1</h4>');
                formattedText = formattedText.replace(/##\s+(.*?)(\n|$)/g, '<h3 class="text-primary mb-3">$1</h3>');
                formattedText = formattedText.replace(/#\s+(.*?)(\n|$)/g, '<h4 class="text-secondary mb-2">$1</h4>');
                
                // Replace bold text
                formattedText = formattedText.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
                
                // Process bullet points
                formattedText = formattedText.replace(/- (.*?)(\n|$)/g, '<li>$1</li>');
                
                // Wrap consecutive list items in ul tags
                let parts = formattedText.split('<li>');
                formattedText = parts[0];
                
                for (let i = 1; i < parts.length; i++) {
                    if (i === 1) {
                        formattedText += '<ul class="mb-3"><li>' + parts[i];
                    } else if (parts[i-1].endsWith('</li>') && !parts[i-1].endsWith('</ul></li>')) {
                        formattedText += '<li>' + parts[i];
                    } else {
                        formattedText += '</ul><li>' + parts[i];
                    }
                }
                
                // Close the last ul if needed
                if (formattedText.lastIndexOf('<li>') > formattedText.lastIndexOf('</ul>')) {
                    formattedText += '</ul>';
                }
                
                // Process numbered lists
                formattedText = formattedText.replace(/(\d+)\.\s+(.*?)(\n|$)/g, '<li>$2</li>');
                
                // Wrap consecutive numbered list items in ol tags
                parts = formattedText.split('<li>');
                formattedText = parts[0];
                
                let inOrderedList = false;
                for (let i = 1; i < parts.length; i++) {
                    if (parts[i-1].match(/\d+\.\s+/) && !inOrderedList) {
                        formattedText += '<ol class="mb-3"><li>' + parts[i];
                        inOrderedList = true;
                    } else if (inOrderedList && parts[i-1].match(/\d+\.\s+/)) {
                        formattedText += '<li>' + parts[i];
                    } else {
                        if (inOrderedList) {
                            formattedText += '</ol>';
                            inOrderedList = false;
                        }
                        formattedText += '<li>' + parts[i];
                    }
                }
                
                if (inOrderedList) {
                    formattedText += '</ol>';
                }
                
                // Convert newlines to breaks for remaining text
                formattedText = formattedText.replace(/\n/g, '<br>');
                
                // Display the insight
                insightDateDisplay.textContent = 'Generated on: ' + new Date(insightData.date).toLocaleString();
                insightContent.innerHTML = formattedText;
                insightContainer.style.display = 'block';
                
                // Scroll to the insight
                insightContainer.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });
    
    // Add event listener for close button
    if (closeInsightBtn) {
        closeInsightBtn.addEventListener('click', function() {
            insightContainer.style.display = 'none';
        });
    }
});
</script>