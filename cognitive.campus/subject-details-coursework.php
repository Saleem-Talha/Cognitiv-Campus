<?php include_once('includes/header.php'); ?>

<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">

        <!-- Include Sidebar -->
        <?php include_once('includes/sidebar-main.php'); ?>

        <div class="layout-page">
            <!-- Include Navbar -->
            <?php include_once('includes/navbar.php'); ?>

            <div class="content-wrapper">

                <?php
                // Retrieve course ID and coursework ID from URL parameters
                $courseId = $_GET['course_id'];
                $workId = $_GET['work_id'];

                // Fetch course details
                $course = $classroomService->courses->get($courseId);

                // Fetch specific coursework details
                $coursework = $classroomService->courses_courseWork->get($courseId, $workId);

                // Fetch student submissions for the coursework
                $submissions = $classroomService->courses_courseWork_studentSubmissions->listCoursesCourseWorkStudentSubmissions($courseId, $workId);
                ?>

                <div class="container-xxl flex-grow-1 container-p-y">
                    <h4 class="py-3 mb-4"><span class="text-muted fw-light">Subject / Subject Details / </span> CourseWork</h4>

                    <div class="row">
                        <div class="col-md-12">
                            <!-- Display success or error messages for assignment upload -->
                            <?php
                            if (isset($_GET['upload_success'])) {
                                echo '<div class="alert alert-success" role="alert">Assignment uploaded successfully!</div>';
                            }
                            if (isset($_GET['upload_error'])) {
                                echo '<div class="alert alert-danger" role="alert">Error uploading assignment: ' . htmlspecialchars($_GET['upload_error']) . '</div>';
                            }
                            ?>

                            <!-- Display coursework details -->
                            <h1 class="mb-4"><?php echo htmlspecialchars($coursework->getTitle()); ?></h1>
                            <h4 class="mb-3"><?php echo htmlspecialchars($course->getName()); ?></h4>

                            <div class="card mb-4">
                                <div class="card-body">
                                    <h5 class="card-title">Description</h5>
                                    <p class="card-text"><?php echo htmlspecialchars($coursework->getDescription()); ?></p>
                                    <?php if ($coursework->getDueDate()) : ?>
                                        <p class="card-text">
                                            <strong>Due: </strong>
                                            <?php
                                            echo htmlspecialchars($coursework->getDueDate()->getYear()) . '-' . htmlspecialchars($coursework->getDueDate()->getMonth()) . '-' . htmlspecialchars($coursework->getDueDate()->getDay());
                                            if ($coursework->getDueTime()) {
                                                echo ' at ' . htmlspecialchars($coursework->getDueTime()->getHours()) . ':' . htmlspecialchars($coursework->getDueTime()->getMinutes());
                                            }
                                            ?>
                                        </p>
                                    <?php endif; ?>
                                    <p class="card-text">
                                        <strong>Max Points: </strong>
                                        <?php echo htmlspecialchars($coursework->getMaxPoints()); ?>
                                    </p>
                                </div>
                            </div>

                            <!-- Display coursework materials -->
                            <h3 class="mb-3">Materials</h3>
                            <?php if ($coursework->getMaterials()) : ?>
                                <div class="row mb-4">
                                    <?php foreach ($coursework->getMaterials() as $material) : ?>
                                        <div class="col-12 mb-3">
                                            <div class="card h-100">
                                                <div class="card-body">
                                                    <?php
                                                    if ($material->getDriveFile()) {
                                                        $file = $material->getDriveFile()->getDriveFile();
                                                        $fileId = $file->getId();
                                                        $fileType = strtolower(pathinfo($file->getTitle(), PATHINFO_EXTENSION));
                                                        echo '<div class="text-center mb-3">';
                                                        if (in_array($fileType, ['jpg', 'jpeg', 'png', 'gif'])) {
                                                            echo '<img src="https://drive.google.com/uc?export=view&id=' . htmlspecialchars($fileId) . '" class="img-fluid" style="max-height: 250px;" alt="' . htmlspecialchars($file->getTitle()) . '">';
                                                        } elseif ($fileType === ['pdf','docx','csv']) {
                                                            echo '<iframe src="https://drive.google.com/file/d/' . htmlspecialchars($fileId) . '/preview" width="100%" height="250" frameborder="0"></iframe>';
                                                        } else {
                                                            echo '<i class="bx bx-file display-1"></i>';
                                                        }
                                                        echo '</div>';
                                                        echo '<h5 class="card-title"><i class="bx bx-file me-2"></i>' . htmlspecialchars($file->getTitle()) . '</h5>';
                                                        echo '<a href="' . htmlspecialchars($file->getAlternateLink()) . '" class="btn btn-primary btn-sm mt-2" target="_blank">Open File</a>';
                                                    } elseif ($material->getLink()) {
                                                        $link = $material->getLink();
                                                        echo '<div class="text-center mb-3">';
                                                        echo '<i class="bx bx-link display-1"></i>';
                                                        echo '</div>';
                                                        echo '<h5 class="card-title">' . htmlspecialchars($link->getTitle()) . '</h5>';
                                                        echo '<a href="' . htmlspecialchars($link->getUrl()) . '" class="btn btn-primary btn-sm mt-2" target="_blank">Visit Link</a>';
                                                    } elseif ($material->getYoutubeVideo()) {
                                                        $video = $material->getYoutubeVideo();
                                                        echo '<div class="embed-responsive embed-responsive-16by9 mb-3" style="height: 250px;">';
                                                        echo '<iframe class="embed-responsive-item" src="https://www.youtube.com/embed/' . htmlspecialchars($video->getId()) . '" allowfullscreen style="width: 100%; height: 100%;"></iframe>';
                                                        echo '</div>';
                                                        echo '<h5 class="card-title">YouTube Video</h5>';
                                                        echo '<a href="https://www.youtube.com/watch?v=' . htmlspecialchars($video->getId()) . '" class="btn btn-primary btn-sm mt-2" target="_blank">Watch on YouTube</a>';
                                                    } elseif ($material->getForm()) {
                                                        $form = $material->getForm();
                                                        echo '<div class="text-center mb-3">';
                                                        echo '<i class="bx bx-table display-1"></i>';
                                                        echo '</div>';
                                                        echo '<h5 class="card-title">' . htmlspecialchars($form->getTitle()) . ' (Google Form)</h5>';
                                                        echo '<a href="' . htmlspecialchars($form->getFormUrl()) . '" class="btn btn-primary btn-sm mt-2" target="_blank">Open Form</a>';
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else : ?>
                                <div class="alert alert-primary" role="alert">
                                    <i class="bx bx-info-circle me-2"></i>
                                    No materials attached to this coursework.
                                </div>
                            <?php endif; ?>

                            <!-- Form to submit an assignment -->
                            <h3 class="mb-3">Submit Assignment</h3>
                            <div class="card mb-4">
                                <div class="card-body">
                                    <form onsubmit="showAlert(event)" action="subject-upload-assignment.php" method="post" enctype="multipart/form-data">
                                        <input type="hidden" name="course_id" value="<?php echo htmlspecialchars($courseId); ?>">
                                        <input type="hidden" name="coursework_id" value="<?php echo htmlspecialchars($workId); ?>">
                                        <div class="mb-3">
                                            <label for="assignment_file" class="form-label">Choose file to upload</label>
                                            <input type="file" class="form-control" id="assignment_file" name="assignment_file" required>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Upload Assignment</button>
                                    </form>
                                </div>
                            </div>

                            <script>
                                function showAlert(event) {
                                    event.preventDefault(); // Prevent the form from submitting

                                    swal("Upload Restricted", "The Google Classroom API does not allow upload functionality while the project is in testing mode.", "warning");
                                }
                            </script>


                            <!-- Display student submissions for the coursework -->
                            <h3 class="mb-3">Submissions</h3>
                            <?php if ($submissions && count($submissions->getStudentSubmissions()) > 0) : ?>
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th>State</th>
                                                        <th>Grade</th>
                                                        <th>Attachments</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($submissions->getStudentSubmissions() as $submission) : ?>
                                                        <tr>
                                                            <td>
                                                                <span class="badge bg-<?php echo $submission->getState() === 'TURNED_IN' ? 'success' : 'warning'; ?>">
                                                                    <?php echo htmlspecialchars($submission->getState()); ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <?php if ($submission->getAssignedGrade()) : ?>
                                                                    <span class="text-primary fw-bold">
                                                                        <?php echo htmlspecialchars($submission->getAssignedGrade()); ?> / <?php echo htmlspecialchars($coursework->getMaxPoints()); ?>
                                                                    </span>
                                                                <?php else : ?>
                                                                    <span class="text-muted">Not graded</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <?php if ($submission->getAssignmentSubmission() && $submission->getAssignmentSubmission()->getAttachments()) : ?>
                                                                    <?php foreach ($submission->getAssignmentSubmission()->getAttachments() as $attachment) : ?>
                                                                        <?php if ($attachment->getDriveFile()) : ?>
                                                                            <a href="<?php echo htmlspecialchars($attachment->getDriveFile()->getAlternateLink()); ?>" target="_blank" class="btn btn-sm btn-outline-primary me-2 mb-2">
                                                                                <i class="bx bx-file me-1"></i>
                                                                                <?php echo htmlspecialchars($attachment->getDriveFile()->getTitle()); ?>
                                                                            </a>
                                                                        <?php endif; ?>
                                                                    <?php endforeach; ?>
                                                                <?php else : ?>
                                                                    <span class="text-muted">No attachments</span>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            <?php else : ?>
                                <div class="alert alert-primary" role="alert">
                                    <i class="bx bx-info-circle me-2"></i>
                                    No submissions found for this coursework.
                                </div>
                            <?php endif; ?>
                        </div>
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
