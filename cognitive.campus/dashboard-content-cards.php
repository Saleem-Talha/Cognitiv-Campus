<?php
include_once("includes/db-connect.php");
include_once("includes/header.php");
include_once("includes/validation.php");

$userInfo = getUserInfo();
$userEmail = $userInfo['email'];



// Get counts from each table for the current user
$course_status_query = "SELECT COUNT(*) as count FROM course_status WHERE user_id = ?";
$own_course_query = "SELECT COUNT(*) as count FROM own_course WHERE userEmail = ?";
$project_query = "SELECT COUNT(*) as count FROM projects WHERE ownerEmail = ?";
$notes_course_query = "SELECT COUNT(*) as count FROM notes_course WHERE userEmail = ?";
$notes_project_query = "SELECT COUNT(*) as count FROM notes_project WHERE userEmail = ?";

// Prepare and execute course_status count
$stmt = $db->prepare($course_status_query);
$stmt->bind_param("s", $userEmail);
$stmt->execute();
$course_status_count = $stmt->get_result()->fetch_assoc()['count'];

// Prepare and execute own_course count
$stmt = $db->prepare($own_course_query);
$stmt->bind_param("s", $userEmail);
$stmt->execute();
$own_course_count = $stmt->get_result()->fetch_assoc()['count'];

// Prepare and execute notes_course count
$stmt = $db->prepare($notes_course_query);
$stmt->bind_param("s", $userEmail);
$stmt->execute();
$notes_course_count = $stmt->get_result()->fetch_assoc()['count'];

// Prepare and execute notes_project count
$stmt = $db->prepare($notes_project_query);
$stmt->bind_param("s", $userEmail);
$stmt->execute();
$notes_project_count = $stmt->get_result()->fetch_assoc()['count'];

// Prepare and execute notes_project count
$stmt = $db->prepare($project_query);
$stmt->bind_param("s", $userEmail);
$stmt->execute();
$project_count = $stmt->get_result()->fetch_assoc()['count'];
?>

<div class="row">
    <!-- Course Status Card -->
    <div class="col-12 col-sm-6 col-lg-3 mb-4">
        <a href="subject.php" class="text-decoration-none">
            <div class="card h-100 hover-shadow-primary">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-auto">
                        <div class="card-info">
                            <span class="d-block text-primary fw-semibold mb-2">Uni Courses</span>
                            <div class="d-flex align-items-center mb-1">
                                <h4 class="card-title mb-0 me-2"><?php echo number_format($course_status_count); ?></h4>
                                <small class="text-success fw-semibold">
                                    <i class="bx bx-up-arrow-alt"></i> Active
                                </small>
                            </div>
                            <small class="text-muted">Enrolled University Courses</small>
                        </div>
                        <div class="avatar avatar-sm">
                            <span class="avatar-initial rounded bg-primary">
                            <i class='bx bx-book-content bx-sm'></i>
                            </span>
                        </div>
                    </div>
                    <div class="progress mt-4" style="height: 3px;">
                        <div class="progress-bar progress-bar-striped bg-primary opacity-25" style="width: 100%" role="progressbar"></div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Own Course Card -->
    <div class="col-12 col-sm-6 col-lg-3 mb-4">
        <a href="subject.php" class="text-decoration-none">
            <div class="card h-100 hover-shadow-primary">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-auto">
                        <div class="card-info">
                            <span class="d-block text-primary fw-semibold mb-2">Own Courses</span>
                            <div class="d-flex align-items-center mb-1">
                                <h4 class="card-title mb-0 me-2"><?php echo number_format($own_course_count); ?></h4>
                                <small class="text-success fw-semibold">
                                    <i class="bx bx-book-bookmark"></i> Created
                                </small>
                            </div>
                            <small class="text-muted">Created Personal Courses</small>
                        </div>
                        <div class="avatar avatar-sm">
                            <span class="avatar-initial rounded bg-primary">
                                <i class="bx bx-book-alt bx-sm"></i>
                            </span>
                        </div>
                    </div>
                    <div class="progress mt-4" style="height: 3px;">
                        <div class="progress-bar progress-bar-striped bg-primary opacity-25" style="width: 100%" role="progressbar"></div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    
    <!-- Projects Card -->
    <div class="col-12 col-sm-6 col-lg-3 mb-4">
        <a href="project.php" class="text-decoration-none">
            <div class="card h-100 hover-shadow-primary">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-auto">
                        <div class="card-info">
                            <span class="d-block text-primary fw-semibold mb-2">Projects</span>
                            <div class="d-flex align-items-center mb-1">
                                <h4 class="card-title mb-0 me-2"><?php echo number_format($project_count); ?></h4>
                                <small class="text-success fw-semibold">
                                    <i class="bx bx-code-block"></i> Active
                                </small>
                            </div>
                            <small class="text-muted">Total Active Projects</small>
                        </div>
                        <div class="avatar avatar-sm">
                            <span class="avatar-initial rounded bg-primary">
                                <i class="bx bx-code-alt bx-sm"></i>
                            </span>
                        </div>
                    </div>
                    <div class="progress mt-4" style="height: 3px;">
                        <div class="progress-bar progress-bar-striped bg-primary opacity-25" style="width: 100%" role="progressbar"></div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Course Notes Card -->
    <div class="col-12 col-sm-6 col-lg-3 mb-4">
        <a href="notes-course.php" class="text-decoration-none">
            <div class="card h-100 hover-shadow-primary">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-auto">
                        <div class="card-info">
                            <span class="d-block text-primary fw-semibold mb-2">Course Notes</span>
                            <div class="d-flex align-items-center mb-1">
                                <h4 class="card-title mb-0 me-2"><?php echo number_format($notes_course_count); ?></h4>
                                <small class="text-success fw-semibold">
                                    <i class="bx bx-notepad"></i> Saved
                                </small>
                            </div>
                            <small class="text-muted">Total Course Notes Taken</small>
                        </div>
                        <div class="avatar avatar-sm">
                            <span class="avatar-initial rounded bg-primary">
                                <i class="bx bx-note bx-sm"></i>
                            </span>
                        </div>
                    </div>
                    <div class="progress mt-4" style="height: 3px;">
                        <div class="progress-bar progress-bar-striped bg-primary opacity-25" style="width: 100%" role="progressbar"></div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Project Notes Card -->
    <div class="col-12 col-sm-6 col-lg-3 mb-4">
        <a href="notes-projects.php" class="text-decoration-none">
            <div class="card h-100 hover-shadow-primary">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-auto">
                        <div class="card-info">
                            <span class="d-block text-primary fw-semibold mb-2">Project Notes</span>
                            <div class="d-flex align-items-center mb-1">
                                <h4 class="card-title mb-0 me-2"><?php echo number_format($notes_project_count); ?></h4>
                                <small class="text-success fw-semibold">
                                    <i class="bx bx-folder-plus"></i> Documented
                                </small>
                            </div>
                            <small class="text-muted">Total Project Documentation</small>
                        </div>
                        <div class="avatar avatar-sm">
                            <span class="avatar-initial rounded bg-primary">
                                <i class="bx bx-folder bx-sm"></i>
                            </span>
                        </div>
                    </div>
                    <div class="progress mt-4" style="height: 3px;">
                        <div class="progress-bar progress-bar-striped bg-primary opacity-25" style="width: 100%" role="progressbar"></div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-lg-9 col-md-12 col-sm-12">
    <?php include_once("dashboard-project-branches.php"); ?>
    </div>

</div>

<style>
.hover-shadow-primary {
    transition: all 0.3s ease;
}

.hover-shadow-primary:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 25px 0 rgba(67, 89, 113, 0.1);
}

.avatar-sm {
    width: 2.5rem;
    height: 2.5rem;
}

.avatar-initial {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: var(--bs-primary) !important;
    color: #fff;
}

/* Make cards fill height of their container */
.card {
    height: 100%;
    min-height: 135px;
}

/* Ensure consistent spacing in card body */
.card-body {
    padding: 1.5rem;
}

/* Responsive text adjustments */
@media (max-width: 767.98px) {
    .card-title {
        font-size: 1.25rem;
    }
    
    .card-text {
        font-size: 0.875rem;
    }
}

/* Handle very small screens */
@media (max-width: 575.98px) {
    .avatar-sm {
        width: 2rem;
        height: 2rem;
    }
    
    .bx-sm {
        font-size: 1.15rem !important;
    }
}
</style>