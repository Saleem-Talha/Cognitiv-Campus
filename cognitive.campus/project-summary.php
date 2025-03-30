<?php include_once('includes/header.php'); ?>
<?php include_once('includes/db-connect.php'); 
include_once('includes/utils.php');



// Get project ID from URL
$encodedProjectId = isset($_GET['project-id']) ? $_GET['project-id'] : '';

$project_id = decodeId($encodedProjectId);




// Fetch project details
$project_query = "SELECT * FROM projects WHERE id = ?";
$stmt = $db->prepare($project_query);
$stmt->bind_param("i", $project_id);
$stmt->execute();
$project_result = $stmt->get_result();
$project = $project_result->fetch_assoc();

// Fetch project branches
$branch_query = "SELECT * FROM project_branch WHERE project_id = ?";
$stmt = $db->prepare($branch_query);
$stmt->bind_param("i", $project_id);
$stmt->execute();
$branch_result = $stmt->get_result();

// Fetch project notices
$notice_query = "SELECT * FROM project_notice WHERE project_id = ?";
$stmt = $db->prepare($notice_query);
$stmt->bind_param("i", $project_id);
$stmt->execute();
$notice_result = $stmt->get_result();

// Fetch project notes
$notes_query = "SELECT * FROM notes_project WHERE project_id= ?";
$stmt = $db->prepare($notes_query);
$stmt->bind_param("i", $project_id);
$stmt->execute();
$notes_result = $stmt->get_result();

?>
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        <?php include_once('includes/sidebar-main.php'); ?>
        <div class="layout-page">
            <?php include_once('includes/navbar.php'); ?>
            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">
                    <h4 class="py-3 mb-4"><span class="text-muted fw-light">Admin / Projects /</span> Project Details</h4>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="card mb-4">
                                <h5 class="card-header bg-primary text-white">Project Details</h5>
                                <div class="card-body">
                                    <h3 class="text-primary my-4"><?php echo htmlspecialchars($project['name']); ?></h3>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><i class="bx bx-calendar me-2"></i><strong>Start Date:</strong> <?php echo htmlspecialchars($project['start_date']); ?></p>
                                            <p><i class="bx bx-calendar-x me-2"></i><strong>End Date:</strong> <?php echo htmlspecialchars($project['end_date']); ?></p>
                                            <p><i class="bx bx-primary-circle me-2"></i><strong>Status:</strong> <span class="badge bg-primary"><?php echo htmlspecialchars($project['status']); ?></span></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><i class="bx bx-book me-2"></i><strong>Course ID:</strong> <?php echo htmlspecialchars($project['course_id']); ?></p>
                                            <p><i class="bx bx-user me-2"></i><strong>Owner Email:</strong> <?php echo htmlspecialchars($project['ownerEmail']); ?></p>
                                            <p><i class="bx bx-category me-2"></i><strong>Course Type:</strong> <?php echo htmlspecialchars($project['courseType']); ?></p>
                                        </div>
                                    </div>
                                    <p class="mt-3"><i class="bx bx-file me-2"></i><strong>Project File:</strong> 
                                        <a class="ms-2" href="<?php echo htmlspecialchars($project['project_file']); ?>" download class="btn btn-sm btn-outline-primary">
                                            <?php echo basename($project['project_file']); ?> <i class="ms-2 bx bx-download"></i>
                                        </a>
                                    </p>
                                    <h6 class="mt-4 mb-3"><i class="bx bx-notepad me-2"></i>README:</h6>
                                    <pre class="bg-light p-3 rounded"><?php echo htmlspecialchars($project['readme']); ?></pre>
                                </div>
                            </div>

                            <div class="card mb-4">
                                <h5 class="card-header bg-primary text-white">Project Branches</h5>
                                <div class="card-body">
                                    <?php if ($branch_result->num_rows > 0): ?>
                                        <div class="row">
                                            <?php while ($branch = $branch_result->fetch_assoc()): ?>
                                                <div class="col-md-6 mb-4">
                                                    <div class="card h-100">
                                                        <div class="card-body">
                                                            <h6 class="card-title"><i class="bx bx-git-branch me-2"></i><?php echo htmlspecialchars($branch['branch_file']); ?></h6>
                                                            <p class="card-text"><?php echo htmlspecialchars($branch['description']); ?></p>
                                                            <a href="projects/<?php echo htmlspecialchars($branch['branch_file']); ?>" download class="btn btn-sm btn-outline-primary">
                                                                Download Branch File <i class="ms-2 bx bx-download"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endwhile; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-primary mt-3" role="alert">
                                            <i class="bx bx-primary-circle me-1"></i>
                                            No branches found for this project.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="card mb-4">
                                <h5 class="card-header bg-primary text-white">Project Notices</h5>
                                <div class="card-body">
                                    <?php if ($notice_result->num_rows > 0): ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Notice</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php while ($notice = $notice_result->fetch_assoc()): ?>
                                                        <tr>
                                                            <td><i class="bx bx-bell me-2"></i><?php echo htmlspecialchars($notice['notice']); ?></td>
                                                        </tr>
                                                    <?php endwhile; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-primary mt-3" role="alert">
                                            <i class="bx bx-primary-circle me-1"></i>
                                            No notices found for this project.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="card mb-4">
                                <h5 class="card-header bg-primary text-white">Project Notes</h5>
                                <div class="card-body">
                                    <?php if ($notes_result->num_rows > 0): ?>
                                        <div class="row">
                                            <?php while ($note = $notes_result->fetch_assoc()): ?>
                                                <div class="col-md-4 mb-3">
                                                    <div class="card h-100">
                                                        <div class="card-body">
                                                            <h6 class="card-title"><i class="bx bx-note me-2"></i><?php echo htmlspecialchars($note['page_title']); ?></h6>
                                                            <p class="card-text"><strong>User:</strong> <?php echo htmlspecialchars($note['userEmail']); ?></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endwhile; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-primary mt-3" role="alert">
                                            <i class="bx bx-primary-circle me-1"></i>
                                            No notes found for this project.
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