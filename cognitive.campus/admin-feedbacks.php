<?php
include_once('includes/header.php');
include_once('includes/db-connect.php');

// Function to toggle feedback visibility
function toggleFeedbackVisibility($id) {
    global $db;
    $stmt = $db->prepare("UPDATE feedback SET on_landing = 1 - on_landing WHERE id = ?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

// Handle POST request for toggling visibility
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_id'])) {
    $success = toggleFeedbackVisibility($_POST['toggle_id']);
    header("Location: " . $_SERVER['PHP_SELF'] . ($success ? "?success=1" : "?error=1"));
    exit;
}

// Fetch all feedbacks
$all_feedbacks = $db->query("SELECT * FROM feedback ORDER BY datetime DESC");

// Fetch feedbacks on landing page
$landing_feedbacks = $db->query("SELECT * FROM feedback WHERE on_landing = 1 ORDER BY datetime DESC");
?>

<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        <?php include_once('includes/admin-sidebar.php'); ?>
        <div class="layout-page">
            <?php include_once('includes/navbar.php'); ?>
            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">
                    <h4 class="py-3 mb-4"><span class="text-muted fw-light">Admin /</span> Feedback Management</h4>

                    <script>
                    <?php if (isset($_GET['success'])): ?>
                        swal({
                            title: "Success!",
                            text: "Feedback visibility updated successfully.",
                            icon: "success",
                            button: "OK",
                        });
                    <?php elseif (isset($_GET['error'])): ?>
                        swal({
                            title: "Error!",
                            text: "An error occurred while updating feedback visibility.",
                            icon: "error",
                            button: "OK",
                        });
                    <?php endif; ?>
                    </script>

                    <!-- All Feedbacks Section -->
                    <div class="card mb-4 shadow-sm">
                        <h5 class="card-header  text-primary">All Feedbacks</h5>
                        <div class="card-body">
                            <div class="row">
                                <?php while ($feedback = $all_feedbacks->fetch_assoc()): ?>
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card h-100 border-0 shadow-sm">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center mb-3">
                                                    <img src="<?= htmlspecialchars($feedback['userPicture']) ?>" alt="User" class="rounded-circle me-3" width="48" height="48">
                                                    <div>
                                                        <h5 class="card-title mb-0"><?= htmlspecialchars($feedback['userName']) ?></h5>
                                                        <small class="text-muted"><?= htmlspecialchars($feedback['userEmail']) ?></small>
                                                    </div>
                                                </div>
                                                <p class="card-text"><?= htmlspecialchars($feedback['message']) ?></p>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div class="text-warning">
                                                        <?= str_repeat('<i class="fas fa-star"></i>', $feedback['rating']) . str_repeat('<i class="far fa-star"></i>', 5 - $feedback['rating']) ?>
                                                    </div>
                                                    <small class="text-muted"><?= date('d M Y', strtotime($feedback['datetime'])) ?></small>
                                                </div>
                                                <div class="mt-3">
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="toggle_id" value="<?= $feedback['id'] ?>">
                                                        <button type="submit" class="btn btn-sm <?= $feedback['on_landing'] ? 'btn-outline-warning' : 'btn-outline-success' ?>">
                                                            <?= $feedback['on_landing'] ? '<i class="fas fa-eye-slash me-1"></i>Remove from Landing' : '<i class="fas fa-eye me-1"></i>Show on Landing' ?>
                                                        </button>
                                                    </form>
                                                    <button class="btn btn-outline-primary btn-sm ms-2" onclick="sendEmail('<?= htmlspecialchars($feedback['userEmail']) ?>', '<?= htmlspecialchars($feedback['userName']) ?>')">
                                                        <i class="fas fa-envelope me-1"></i>Mail
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Feedbacks on Landing Page Section -->
                    <div class="card shadow-sm">
                        <h5 class="card-header text-primary">Feedbacks on Landing Page</h5>
                        <div class="card-body">
                            <div class="row">
                                <?php while ($feedback = $landing_feedbacks->fetch_assoc()): ?>
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card h-100 border-0 shadow-sm">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center mb-3">
                                                    <img src="<?= htmlspecialchars($feedback['userPicture']) ?>" alt="User" class="rounded-circle me-3" width="48" height="48">
                                                    <div>
                                                        <h5 class="card-title mb-0"><?= htmlspecialchars($feedback['userName']) ?></h5>
                                                        <small class="text-muted"><?= htmlspecialchars($feedback['userEmail']) ?></small>
                                                    </div>
                                                </div>
                                                <p class="card-text"><?= htmlspecialchars($feedback['message']) ?></p>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div class="text-warning">
                                                        <?= str_repeat('<i class="fas fa-star"></i>', $feedback['rating']) . str_repeat('<i class="far fa-star"></i>', 5 - $feedback['rating']) ?>
                                                    </div>
                                                    <small class="text-muted"><?= date('d M Y', strtotime($feedback['datetime'])) ?></small>
                                                </div>
                                                <div class="mt-3">
                                                    <form method="POST">
                                                        <input type="hidden" name="toggle_id" value="<?= $feedback['id'] ?>">
                                                        <button type="submit" class="btn btn-outline-warning btn-sm">
                                                            <i class="fas fa-eye-slash me-1"></i>Remove from Landing
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
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

<script>
function sendEmail(email, userName) {
    window.location.href = `admin-compose-email.php?email=${encodeURIComponent(email)}&name=${encodeURIComponent(userName)}`;
}
</script>