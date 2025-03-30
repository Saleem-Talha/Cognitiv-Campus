<?php
require_once 'includes/validation.php';
require_once 'includes/header.php';
require_once 'includes/db-connect.php';



$userInfo = getUserInfo();
$userEmail = $userInfo['email'];

// Fetch user's images
$stmt = $db->prepare("SELECT annual_calendar, timetable FROM users WHERE email = ?");
$stmt->bind_param("s", $userEmail);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
?>

    
    <div class="col-md-12 col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Schedules & Calendar</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <?php if (!empty($user_data['annual_calendar'])): ?>
                    <div class="col-12">
                        <div class="card shadow-none bg-light">
                            <div class="card-body p-3">
                                <h6 class="card-title mb-2">Annual Calendar</h6>
                                <img src="img/tables/<?php echo htmlspecialchars($user_data['annual_calendar']); ?>" 
                                     class="img-fluid rounded cursor-pointer view-image" 
                                     data-bs-toggle="modal" 
                                     data-bs-target="#imageModal"
                                     data-img-src="img/tables/<?php echo htmlspecialchars($user_data['annual_calendar']); ?>"
                                     data-img-title="Annual Calendar"
                                     alt="Annual Calendar">
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($user_data['timetable'])): ?>
                    <div class="col-12">
                        <div class="card shadow-none bg-light">
                            <div class="card-body p-3">
                                <h6 class="card-title mb-2">Timetable</h6>
                                <img src="img/tables/<?php echo htmlspecialchars($user_data['timetable']); ?>" 
                                     class="img-fluid rounded cursor-pointer view-image" 
                                     data-bs-toggle="modal" 
                                     data-bs-target="#imageModal"
                                     data-img-src="img/tables/<?php echo htmlspecialchars($user_data['timetable']); ?>"
                                     data-img-title="Timetable"
                                     alt="Timetable">
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (empty($user_data['annual_calendar']) && empty($user_data['timetable'])): ?>
                    <div class="col-12 text-center">
                        <p class="text-muted mb-0">No schedules uploaded yet</p>
                        <a href="dashboard-upload-tables-page.php" class="btn btn-primary btn-sm mt-2">
                            <i class="bx bx-upload me-1"></i> Upload Schedules
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="col-12">
                        <a href="dashboard-upload-tables-page.php" class="btn btn-outline-primary btn-sm w-100">
                            <i class="bx bx-edit me-1"></i> Update Schedules
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>


<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalLabel">Image Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img src="" id="modalImage" class="img-fluid" alt="Preview">
            </div>
        </div>
    </div>
</div>

<!-- Add this script before closing body tag -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle image click to show in modal
    const viewButtons = document.querySelectorAll('.view-image');
    const modalImage = document.getElementById('modalImage');
    const modalTitle = document.getElementById('imageModalLabel');

    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const imgSrc = this.getAttribute('data-img-src');
            const imgTitle = this.getAttribute('data-img-title');
            modalImage.src = imgSrc;
            modalTitle.textContent = imgTitle;
        });
    });

    // Add hover effect for images
    viewButtons.forEach(img => {
        img.style.cursor = 'pointer';
        img.addEventListener('mouseover', function() {
            this.style.opacity = '0.8';
            this.style.transition = 'opacity 0.3s ease';
        });
        img.addEventListener('mouseout', function() {
            this.style.opacity = '1';
        });
    });
});
</script>

<style>
.cursor-pointer {
    cursor: pointer;
}
.view-image:hover {
    transform: scale(1.02);
    transition: transform 0.2s ease;
}
#modalImage {
    max-height: 80vh;
    object-fit: contain;
}
.card-body img {
    width: 100%;
    height: auto;
    object-fit: cover;
    max-height: 200px;
}
</style>