<div id="courseworkSection mt-3" style="display: none;">
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