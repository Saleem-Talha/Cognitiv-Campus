<?php
// subject-get-materials.php
require_once 'includes/validation.php';

if (!isAuthenticated()) {
    http_response_code(401);
    exit('Not authenticated');
}

$courseId = $_GET['id'];

// Fetch course materials
$materials = $classroomService->courses_courseWorkMaterials->listCoursesCourseWorkMaterials($courseId)->getCourseWorkMaterial();
?>

<div id="materialsSection">
    <h2 class="mb-3">Course Materials</h2>

    <?php if (!empty($materials)): ?>
        <div class="row">
            <?php foreach ($materials as $material): ?>
                <div class="col-12">
                    <div class="card mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0"><?php echo $material->getTitle(); ?></h5>
                            <?php if ($material->getState() === 'PUBLISHED'): ?>
                                <span class="badge bg-primary">Published</span>
                            <?php else: ?>
                                <span class="badge bg-warning">Draft</span>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <?php if ($material->getDescription()): ?>
                                <p class="card-text"><?php echo $material->getDescription(); ?></p>
                            <?php endif; ?>

                            <?php
                            $attachments = $material->getMaterials();
                            if (!empty($attachments)):
                            ?>
                                <div class="mt-3">
                                    <h6 class="mb-3">Attachments:</h6>
                                    <div class="list-group">
                                        <?php foreach ($attachments as $attachment): ?>
                                            <?php if ($attachment->getDriveFile()): ?>
                                                <?php 
                                                $driveFile = $attachment->getDriveFile()->getDriveFile();
                                                $fileType = pathinfo($driveFile->getTitle(), PATHINFO_EXTENSION);
                                                ?>
                                                <div class="list-group-item list-group-item-action">
                                                    <div class="d-flex w-100 justify-content-between align-items-center">
                                                        <div class="d-flex align-items-center">
                                                            <i class="bx bx-file me-2 text-primary"></i>
                                                            <div>
                                                                <h6 class="mb-0"><?php echo $driveFile->getTitle(); ?></h6>
                                                                <small class="text-muted"><?php echo strtoupper($fileType); ?> File</small>
                                                            </div>
                                                        </div>
                                                        <a href="<?php echo $driveFile->getAlternateLink(); ?>" 
                                                           target="_blank" 
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="bx bx-download me-1"></i>Open
                                                        </a>
                                                    </div>
                                                </div>
                                            <?php elseif ($attachment->getLink()): ?>
                                                <?php $link = $attachment->getLink(); ?>
                                                <div class="list-group-item list-group-item-action">
                                                    <div class="d-flex w-100 justify-content-between align-items-center">
                                                        <div class="d-flex align-items-center">
                                                            <i class="bx bx-link me-2 text-info"></i>
                                                            <div>
                                                                <h6 class="mb-0"><?php echo $link->getTitle(); ?></h6>
                                                                <small class="text-muted">External Link</small>
                                                            </div>
                                                        </div>
                                                        <a href="<?php echo $link->getUrl(); ?>" 
                                                           target="_blank" 
                                                           class="btn btn-sm btn-outline-info">
                                                            <i class="bx bx-link-external me-1"></i>Visit
                                                        </a>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer">
                            <small class="text-muted">
                                Posted: <?php echo date('Y-m-d H:i:s', strtotime($material->getCreationTime())); ?>
                                <?php if ($material->getUpdateTime() !== $material->getCreationTime()): ?>
                                    <br>Updated: <?php echo date('Y-m-d H:i:s', strtotime($material->getUpdateTime())); ?>
                                <?php endif; ?>
                            </small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-primary" role="alert">
            <i class="bx bx-info-circle me-2"></i>No course materials found.
        </div>
    <?php endif; ?>
</div>