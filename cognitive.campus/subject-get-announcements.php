<?php
// get-announcements.php
require_once 'includes/validation.php';

if (!isAuthenticated()) {
    http_response_code(401);
    exit('Not authenticated');
}

$courseId = $_GET['id'];
$startDate = isset($_GET['announcement_start_date']) ? $_GET['announcement_start_date'] : '';
$endDate = isset($_GET['announcement_end_date']) ? $_GET['announcement_end_date'] : '';

// Fetch announcements
$announcements = $classroomService->courses_announcements->listCoursesAnnouncements($courseId)->getAnnouncements();

// Filter announcements
$filteredAnnouncements = [];
foreach ($announcements as $announcement) {
    $announcementDate = new DateTime($announcement->getCreationTime());
    if ((!$startDate || $announcementDate >= new DateTime($startDate)) &&
        (!$endDate || $announcementDate <= new DateTime($endDate))) {
        $filteredAnnouncements[] = $announcement;
    }
}
?>

<div id="announcementsSection">
    <h2 class="mb-3">Announcements</h2>

    <?php if (!empty($filteredAnnouncements)): ?>
        <?php foreach ($filteredAnnouncements as $announcement): ?>
                                        <div class="card mb-3">
                                            <div class="card-header">
                                                <?php
                                                $announcementCreator = $classroomService->userProfiles->get($announcement->getCreatorUserId());
                                                $creatorPhotoUrl = $announcementCreator->getPhotoUrl();
                                                if ($creatorPhotoUrl && strpos($creatorPhotoUrl, '//') === 0) {
                                                    $creatorPhotoUrl = 'https:' . $creatorPhotoUrl;
                                                }
                                                ?>
                                                <div class="d-flex align-items-center">
                                                    <img src="img/Logo/Cognitive Campus Logo.png" alt="Creator Image" class="rounded-circle" style="width: 40px; height: 40px;">
                                                    <h5 class="ms-3"><?php echo $announcementCreator->getName()->getFullName(); ?></h5>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <p class="text-muted">
                                                    <?php echo $announcement->getText(); ?>
                                                </p>
                                                <?php
                                                $materials = $announcement->getMaterials();
                                                if (!empty($materials)) {
                                                    echo '<h6 class="mt-4 mb-3">Materials:</h6>';
                                                    echo '<div class="row">';
                                                    foreach ($materials as $material) {
                                                        echo '<div class="col-md-6 mb-3">';
                                                        echo '<div class="card h-100">';
                                                        if ($material->getDriveFile()) {
                                                            $driveFile = $material->getDriveFile()->getDriveFile();
                                                            $fileType = pathinfo($driveFile->getTitle(), PATHINFO_EXTENSION);
                                                            echo '<div class="card-body">';
                                                            echo '<div class="d-flex align-items-center">';
                                                            echo '<i class="bx bx-file me-2 fs-2 text-primary"></i>';
                                                            echo '<div>';
                                                            echo '<h6 class="card-title mb-0">' . $driveFile->getTitle() . '</h6>';
                                                            echo '<small class="text-muted">' . strtoupper($fileType) . ' File</small>';
                                                            echo '</div>';
                                                            echo '</div>';
                                                            echo '<a href="' . $driveFile->getAlternateLink() . '" target="_blank" class="btn btn-sm btn-outline-primary mt-2">Open File</a>';
                                                            echo '</div>';
                                                        } elseif ($material->getLink()) {
                                                            $link = $material->getLink();
                                                            echo '<div class="card-body">';
                                                            echo '<div class="d-flex align-items-center">';
                                                            echo '<i class="bx bx-link me-2 fs-2 text-info"></i>';
                                                            echo '<div>';
                                                            echo '<h6 class="card-title mb-0">' . $link->getTitle() . '</h6>';
                                                            echo '<small class="text-muted">External Link</small>';
                                                            echo '</div>';
                                                            echo '</div>';
                                                            echo '<a href="' . $link->getUrl() . '" target="_blank" class="btn btn-sm btn-outline-info mt-2">Visit Link</a>';
                                                            echo '</div>';
                                                        }
                                                        echo '</div>';
                                                        echo '</div>';
                                                    }
                                                    echo '</div>';
                                                }
                                                ?>
                                                <p class="card-text">
                                                    <small class="text-muted">
                                                        Posted on: <?php echo date('Y-m-d H:i:s', strtotime($announcement->getCreationTime())); ?>
                                                    </small>
                                                </p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
    <?php else: ?>
        <div class="alert alert-primary" role="alert">
            <i class="bx bx-info-circle me-2"></i>No announcements found.
        </div>
    <?php endif; ?>
</div>
