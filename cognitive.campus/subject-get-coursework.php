
<?php
// get-coursework.php
require_once 'includes/validation.php';

if (!isAuthenticated()) {
    http_response_code(401);
    exit('Not authenticated');
}

$courseId = $_GET['id'];
$startDate = isset($_GET['coursework_start_date']) ? $_GET['coursework_start_date'] : '';
$endDate = isset($_GET['coursework_end_date']) ? $_GET['coursework_end_date'] : '';

// Fetch coursework
$allCoursework = $classroomService->courses_courseWork->listCoursesCourseWork($courseId)->getCourseWork();

// Filter coursework
$coursework = [];
foreach ($allCoursework as $work) {
    $workDate = new DateTime($work->getCreationTime());
    if ((!$startDate || $workDate >= new DateTime($startDate)) &&
        (!$endDate || $workDate <= new DateTime($endDate))) {
        $coursework[] = $work;
    }
}

// Fetch student submissions
$studentSubmissions = [];
if (!empty($coursework)) {
    foreach ($coursework as $work) {
        $submissions = $classroomService->courses_courseWork_studentSubmissions->listCoursesCourseWorkStudentSubmissions(
            $courseId,
            $work->getId()
        )->getStudentSubmissions();

        if (!empty($submissions)) {
            $studentSubmissions[$work->getId()] = $submissions;
        }
    }
}
?>

<div id="courseworkSection mt-3">
                                <h2 class="mb-3">Coursework and Submissions</h2>
                                <?php if (!empty($coursework)) : ?>
                                    <?php foreach ($coursework as $work) : ?>
                                        <div class="card mb-3">
                                            <div class="card-body">
                                                <h5 class="card-title">
                                                    <i class='bx bx-book-content me-2'></i>
                                                    <?php echo $work->getTitle(); ?>
                                                </h5>
                                                <p class="card-text"><?php echo $work->getDescription(); ?></p>
                                                <?php if ($work->getDueDate()) : ?>
                                                    <p class="card-text">
                                                        <small class="text-muted">
                                                            Due: <?php echo $work->getDueDate()->getYear() . '-' . $work->getDueDate()->getMonth() . '-' . $work->getDueDate()->getDay(); ?>
                                                        </small>
                                                    </p>
                                                <?php endif; ?>
                                                <p class="card-text">
                                                    <small class="text-muted">
                                                        Type: <?php echo $work->getWorkType(); ?>
                                                    </small>
                                                </p>
                                               
                                                <!-- Student Submissions -->
                                                <?php if (!empty($studentSubmissions[$work->getId()])) : ?>
                                                    <h6 class="mt-3">Student Submissions:</h6>
                                                    <ul class="list-group">
                                                        <?php foreach ($studentSubmissions[$work->getId()] as $submission) : ?>
                                                            <li class="list-group-item">
                                                                <?php
                                                                $attachments = $submission->getAssignmentSubmission()->getAttachments();
                                                                if (!empty($attachments)) {
                                                                    foreach ($attachments as $attachment) {
                                                                        $driveFile = $attachment->getDriveFile();
                                                                        if ($driveFile) {
                                                                            echo '<a href="' . $driveFile->getAlternateLink() . '" target="_blank">';
                                                                            echo '<i class="bx bx-file"></i> ' . $driveFile->getTitle();
                                                                            echo '</a><br>';
                                                                        }
                                                                    }
                                                                } else {
                                                                    echo 'No attachments';
                                                                }
                                                                ?>
                                                            </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php endif; ?>
                                                <div class="text-end mt-2">
                                                    <a href="subject-details-coursework.php?course_id=<?php echo $courseId; ?>&work_id=<?php echo $work->getId(); ?>" class="btn btn-primary btn-sm">View Details</a>
                                                </div>

                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <div class="alert alert-primary" role="alert">
                                    <i class="bx bx-info-circle me-2"></i>
                                    No course found
                                </div>
                                <?php endif; ?>
                            </div>
