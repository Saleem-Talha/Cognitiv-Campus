<?php include_once('includes/header.php'); ?>
<?php include_once('includes/utils.php'); ?>

<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        
        <!-- Include Sidebar -->
        <?php include_once('includes/sidebar-main.php'); ?>

        <div class="layout-page">
            <!-- Include Navbar -->
            <?php include_once('includes/navbar.php'); ?>

            <div class="content-wrapper">

                <?php
                
                // Get course ID from the URL parameter
                $course_id = $_GET['id'];
                $decodedId = decodeId($course_id);
                $courseType = 'extraCourse'; // Define course type

                // Fetch course data from the database
                $get_course_data = $db->query("SELECT * FROM own_course WHERE id = '$decodedId' AND userEmail = '$userEmail'");
                if ($get_course_data->num_rows) {
                    $courseData = $get_course_data->fetch_assoc();
                    $courseName = $courseData['name'];
                    $courseImage = $courseData['image'];
                } else {
                    // Redirect if course not found
                    header('location: subject.php');
                    exit();
                }


                // Fetch course recommendations
                $recommendations = [];
                $recommendation_query = $db->query("SELECT * FROM course_recommendations 
                    WHERE source_course_name = '$courseName' 
                    ORDER BY recommendation_rank ASC");

                if ($recommendation_query->num_rows > 0) {
                    while ($recommendation = $recommendation_query->fetch_assoc()) {
                        $recommendations[] = $recommendation;
                    }
                }
                 ?>

                <div class="container-xxl flex-grow-1 container-p-y">
                    <h4 class="py-3 mb-4">
                        <span class="text-muted fw-light">Subject / Extra Course /</span> <?php echo htmlspecialchars($courseName); ?>
                    </h4>

                                    
                <!-- Add this button after the existing course content -->
               <div class="my-3">
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#courseRecommendationsModal">
                        View Course Recommendations
                    </button>
                </div>
                



                <!-- Course Recommendations Modal -->
                <div class="modal fade" id="courseRecommendationsModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Recommended Courses for <?php echo htmlspecialchars($courseName); ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <?php if (!empty($recommendations)): ?>
                                    <div class="row row-cols-1 row-cols-md-2 g-4">
                                        <?php foreach ($recommendations as $index => $recommendation): ?>
                                            <div class="col">
                                                <div class="card h-100 shadow-sm">
                                                    <div class="card-header text-white">
                                                        <h5 class="card-title mb-0">
                                                            Recommendation #<?php echo $index + 1 ?> 
                                                            <span class="badge bg-primary text-white float-end">
                                                                Rank: <?php echo htmlspecialchars($recommendation['recommendation_rank']); ?>
                                                            </span>
                                                        </h5>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col-md-8">
                                                                <h6 class="card-subtitle mb-2 text-muted">
                                                                    <?php echo htmlspecialchars($recommendation['recommended_course_name']); ?>
                                                                </h6>
                                                                
                                                            </div>
                                                            <div class="col-md-4 text-end">
                                                                <div class="mb-2">
                                                                    <small class="text-muted">Difficulty:</small>
                                                                    <span class="badge bg-label-primary">
                                                                        <?php echo htmlspecialchars($recommendation['difficulty_level']); ?>
                                                                    </span>
                                                                </div>
                                                                <div class="mb-2">
                                                                    <small class="text-muted">Rating:</small>
                                                                    <span class="badge bg-label-warning">
                                                                        <?php echo htmlspecialchars($recommendation['course_rating']); ?>/5
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card-footer">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <small class="text-muted">
                                                                    <i class="bx bx-map-pin me-1"></i>
                                                                    <?php echo htmlspecialchars($recommendation['university']); ?>
                                                                </small>
                                                            </div>
                                                            <div class="col-md-6 text-end">
                                                                <a href="<?php echo htmlspecialchars($recommendation['course_url']); ?>" 
                                                                class="btn btn-sm btn-outline-primary" 
                                                                target="_blank">
                                                                    <i class="bx bx-link-external me-1"></i>View Course
                                                                </a>
                                                            </div>
                                                        </div>
                                                        
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center p-5">
                                            <i class="bx bx-info-circle text-primary mb-3" style="font-size: 3rem;"></i>
                                            <h5 class="mb-3">No Similar Courses Found</h5>
                                            <p class="text-muted">We couldn't find any courses similar to your current selection.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-dismiss="modal">
                                    Close
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                    <!-- Display course content by type -->
                    <div class="row">
                        <?php
                        // Define page types to display
                        $page_types = ['Assignment', 'Quiz', 'Notes'];
                        foreach ($page_types as $type) {
                            echo "<div class='col-md-6 mb-4'>";
                            echo "<div class='card'>";
                            echo "<div class='card-header'>";
                            echo "<h5>$type</h5>";
                            echo "</div>";
                            echo "<div class='card-body'>";
                            
                            // Fetch pages of the current type
                            $select_pages = $db->query("SELECT * FROM notes_course WHERE courseId = '$decodedId' AND userEmail = '$userEmail' AND type = '$type'");
                            if ($select_pages->num_rows) {
                                echo "<ul class='list-group'>";
                                $count = 0;
                                while ($row = $select_pages->fetch_assoc()) {
                                    $page_id = $row['id'];
                                    $page_title = $row['page_title'];
                                    $page_datetime = $row['datetime'];
                                    $count++;
                                    $page_datetime = date('d M Y', strtotime($page_datetime));

                                    // Display each page item
                                    echo "<li class='list-group-item d-flex justify-content-between align-items-center'>
                                        <div class='flex-grow-1 d-flex justify-content-between align-items-center'>
                                            <div>
                                                <i class='bx bx-file me-2'></i>
                                                <span class='text-muted'>Note $count : </span>
                                                <span>" . htmlspecialchars($page_title) . "</span>
                                                <small class='text-muted ms-2'>$page_datetime</small>
                                            </div>
                                            <div class='d-flex align-items-center'>
                                                <a href='notes-page-course.php?courseId=" . htmlspecialchars($decodedId) . "&page_id=$page_id&count=$count&courseType=$courseType' class='btn border'>
                                                    <i class='bx bx-book-open me-2'></i><span>Open</span>
                                                </a>
                                            </div>
                                        </div>
                                    </li>";
                                }
                                echo "</ul>";
                            } else {
                                // No items found message
                                echo "<div class='alert alert-primary' role='alert'>
                                    <i class='bx bx-info-circle me-2'></i>
                                    No $type found.
                                </div>";
                            }
                            echo "</div>"; // Close card-body
                            echo "</div>"; // Close card
                            echo "</div>"; // Close col-md-6
                        }
                        ?>
                    </div>

                </div>

                <!-- Include Footer -->
                <?php include_once('includes/footer.php'); ?>
                <div class="content-backdrop fade"></div>
            </div>
        </div>
    </div>

    <div class="layout-overlay layout-menu-toggle"></div>
</div>

<?php include_once('includes/footer-links.php'); ?>

