<?php include_once('includes/header.php'); ?>
<div class="layout-wrapper layout-content-navbar">
<div class="layout-container">
<?php include_once('includes/sidebar-main.php'); ?>
<div class="layout-page">
<?php include_once('includes/navbar.php'); ?>
<div class="content-wrapper">

<?php 
$update_is_read = $db->query("UPDATE notice SET is_read = 'yes' WHERE userEmail = '$userEmail' AND is_read = 'no' ");
$one_week_ago = date('Y-m-d H:i:s', strtotime('-1 week'));
?>

<div class="container-xxl flex-grow-1 container-p-y">
<h4 class="py-3 mb-4"><span class="text-muted fw-light">Notifications /</span> Last Week's Notifications</h4>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card h-100 shadow-sm transition-hover">
            <div class="card-header">
                <h5 class="card-title mb-0">Course Notifications</h5>
            </div>
            <div class="card-body">
                <div class="notification-list">
                    <?php 
                    $notifications = $db->query("SELECT * FROM notice WHERE userEmail = '$userEmail' AND type = 'Course' AND datetime >= '$one_week_ago' ORDER BY id DESC LIMIT 5");
                    if($notifications->num_rows){
                        while($row = $notifications->fetch_assoc()){
                            $message = $row['message'];
                            $datetime = $row['datetime'];
                            ?>
                            <div class="notification-item mb-3 border-bottom pb-2">
                                <p class="mb-1"><?php echo $message; ?></p>
                                <small class="text-muted"><?php echo date('M d, Y h:i A', strtotime($datetime)); ?></small>
                            </div>
                            <?php
                        }
                    } else {
                        echo "<p class='text-muted'>No course notifications found in the last week.</p>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100 shadow-sm transition-hover">
            <div class="card-header">
                <h5 class="card-title mb-0">Project Notifications</h5>
            </div>
            <div class="card-body">
                <div class="notification-list">
                    <?php 
                    $notifications = $db->query("SELECT * FROM notice WHERE userEmail = '$userEmail' AND type = 'Project' AND datetime >= '$one_week_ago' ORDER BY id DESC LIMIT 5");
                    if($notifications->num_rows){
                        while($row = $notifications->fetch_assoc()){
                            $message = $row['message'];
                            $datetime = $row['datetime'];
                            ?>
                            <div class="notification-item mb-3 border-bottom pb-2">
                                <p class="mb-1"><?php echo $message; ?></p>
                                <small class="text-muted"><?php echo date('M d, Y h:i A', strtotime($datetime)); ?></small>
                            </div>
                            <?php
                        }
                    } else {
                        echo "<p class='text-muted'>No project notifications found in the last week.</p>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card h-100 shadow-sm transition-hover">
            <div class="card-header">
                <h5 class="card-title mb-0">Notes</h5>
            </div>
            <div class="card-body">
                <div class="notification-list">
                    <?php 
                    $notifications = $db->query("SELECT * FROM notice WHERE userEmail = '$userEmail' AND type = 'Notes' AND datetime >= '$one_week_ago' ORDER BY id DESC LIMIT 5");
                    if($notifications->num_rows){
                        while($row = $notifications->fetch_assoc()){
                            $message = $row['message'];
                            $datetime = $row['datetime'];
                            ?>
                            <div class="notification-item mb-3 border-bottom pb-2">
                                <p class="mb-1"><?php echo $message; ?></p>
                                <small class="text-muted"><?php echo date('M d, Y h:i A', strtotime($datetime)); ?></small>
                            </div>
                            <?php
                        }
                    } else {
                        echo "<p class='text-muted'>No notes found in the last week.</p>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100 shadow-sm transition-hover">
            <div class="card-header">
                <h5 class="card-title mb-0">Other Notifications</h5>
            </div>
            <div class="card-body">
                <div class="notification-list">
                    <?php 
                    $notifications = $db->query("SELECT * FROM notice WHERE userEmail = '$userEmail' AND type NOT IN ('Project', 'Course', 'Notes') AND datetime >= '$one_week_ago' ORDER BY id DESC LIMIT 5");
                    if($notifications->num_rows){
                        while($row = $notifications->fetch_assoc()){
                            $message = $row['message'];
                            $datetime = $row['datetime'];
                            ?>
                            <div class="notification-item mb-3 border-bottom pb-2">
                                <p class="mb-1"><?php echo $message; ?></p>
                                <small class="text-muted"><?php echo date('M d, Y h:i A', strtotime($datetime)); ?></small>
                            </div>
                            <?php
                        }
                    } else {
                        echo "<p class='text-muted'>No other notifications found in the last week.</p>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12 text-center">
        <button id="showAllNotifications" class="btn btn-primary">Show All Notifications</button>
    </div>
</div>

<div id="allNotificationsModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">All Notifications</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="allNotificationsList"></div>
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

<style>
.card.transition-hover {
    transition: all 0.3s ease;
}
.card.transition-hover:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}
.notification-item {
    transition: background-color 0.3s ease;
}
.notification-item:hover {
    background-color: #f8f9fa;
}
</style>

<script>
$(document).ready(function() {
    $('#showAllNotifications').click(function() {
        $.ajax({
            url: 'notifications-get-all.php',
            type: 'GET',
            success: function(response) {
                $('#allNotificationsList').html(response);
                $('#allNotificationsModal').modal('show');
            },
            error: function() {
                alert('Error fetching notifications');
            }
        });
    });
});
</script>