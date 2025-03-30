<?php
// get-teachers.php
require_once 'includes/validation.php';

if (!isAuthenticated()) {
    http_response_code(401);
    exit('Not authenticated');
}

$courseId = $_GET['id'];
$teachers = $classroomService->courses_teachers->listCoursesTeachers($courseId)->getTeachers();
?>

<?php if (!empty($teachers)) : ?>
    <?php foreach ($teachers as $teacher) : ?>
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <?php
                    $teacherProfile = $classroomService->userProfiles->get($teacher->getUserId());
                    $teacherPhotoUrl = $teacherProfile->getPhotoUrl();
                    if ($teacherPhotoUrl && strpos($teacherPhotoUrl, '//') === 0) {
                        $teacherPhotoUrl = 'https:' . $teacherPhotoUrl;
                    }
                    ?>
                    <img src="img/Logo/Cognitive Campus Logo.png" alt="Teacher Image" class="rounded-circle" style="width: 40px; height: 40px;">
                    <h5 class="ms-3"><?php echo $teacherProfile->getName()->getFullName(); ?></h5>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php else : ?>
    <div class="alert alert-primary" role="alert">
        <i class="bx bx-info-circle me-2"></i>
        No teachers found.
    </div>
<?php endif; ?>