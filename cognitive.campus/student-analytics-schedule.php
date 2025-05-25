<?php

$userInfo = getUserInfo();
$userEmail = $userInfo['email'];

$completed_query = "SELECT COUNT(*) as completed_count FROM schedule_reports WHERE completed_count > 0 AND user_email = ?";
$stmt_completed = mysqli_prepare($db, $completed_query);
mysqli_stmt_bind_param($stmt_completed, "s", $userEmail);
mysqli_stmt_execute($stmt_completed);
$completed_result = mysqli_stmt_get_result($stmt_completed);
$completed_row = mysqli_fetch_assoc($completed_result);
$completed_count = $completed_row['completed_count'];
mysqli_stmt_close($stmt_completed);

// Query to count incomplete schedules for the current user
$incomplete_query = "SELECT COUNT(*) as incomplete_count FROM schedule_reports WHERE incomplete_count > 0 AND user_email = ?";
$stmt_incomplete = mysqli_prepare($db, $incomplete_query);
mysqli_stmt_bind_param($stmt_incomplete, "s", $userEmail);
mysqli_stmt_execute($stmt_incomplete);
$incomplete_result = mysqli_stmt_get_result($stmt_incomplete);
$incomplete_row = mysqli_fetch_assoc($incomplete_result);
$incomplete_count = $incomplete_row['incomplete_count'];
mysqli_stmt_close($stmt_incomplete);

// Calculate total schedules
$total_schedules = $completed_count + $incomplete_count;

// Calculate completion percentage
$completion_percentage = ($total_schedules > 0) ? round(($completed_count / $total_schedules) * 100, 1) : 0;
?>

<!-- Student Analytics Schedule Component -->
<div class="student-analytics-schedule-component my-3 mx-4">
    <div class="row g-4 mb-4">
        <!-- Completed Schedules Card -->
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="fw-medium d-block mb-1">Completed Schedules</span>
                            <div class="d-flex align-items-center">
                                <h3 class="card-title mb-0 me-2"><?php echo $completed_count; ?></h3>
                                <small class="text-success fw-medium">
                                    <i class="bx bx-check-circle"></i>
                                </small>
                            </div>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="bx bx-calendar-check bx-md"></i>
                            </span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mt-3">
                        <div class="progress w-100" style="height: 8px;">
                            <div class="progress-bar bg-success" role="progressbar" 
                                style="width: <?php echo $completion_percentage; ?>%" 
                                aria-valuenow="<?php echo $completion_percentage; ?>" 
                                aria-valuemin="0" 
                                aria-valuemax="100">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Incomplete Schedules Card -->
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="fw-medium d-block mb-1">Incomplete Schedules</span>
                            <div class="d-flex align-items-center">
                                <h3 class="card-title mb-0 me-2"><?php echo $incomplete_count; ?></h3>
                                <small class="text-warning fw-medium">
                                    <i class="bx bx-time"></i>
                                </small>
                            </div>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-warning">
                                <i class="bx bx-calendar-x bx-md"></i>
                            </span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mt-3">
                        <div class="progress w-100" style="height: 8px;">
                            <div class="progress-bar bg-warning" role="progressbar" 
                                style="width: <?php echo (100 - $completion_percentage); ?>%" 
                                aria-valuenow="<?php echo (100 - $completion_percentage); ?>" 
                                aria-valuemin="0" 
                                aria-valuemax="100">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Completion Rate Card -->
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="fw-medium d-block mb-1">Completion Rate</span>
                            <div class="d-flex align-items-center">
                                <h3 class="card-title mb-0 me-2"><?php echo $completion_percentage; ?>%</h3>
                                <small class="text-primary fw-medium">
                                    <i class="bx bx-trending-up"></i>
                                </small>
                            </div>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="bx bx-pie-chart-alt bx-md"></i>
                            </span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mt-3">
                        <div class="progress w-100" style="height: 8px;">
                            <div class="progress-bar bg-primary" role="progressbar" 
                                style="width: <?php echo $completion_percentage; ?>%" 
                                aria-valuenow="<?php echo $completion_percentage; ?>" 
                                aria-valuemin="0" 
                                aria-valuemax="100">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Statistics -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Schedule Completion Details</h5>
                    <div class="dropdown">
                        <button class="btn p-0" type="button" id="scheduleStatsDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="scheduleStatsDropdown">
                            <a class="dropdown-item" href="javascript:void(0);">Last 7 Days</a>
                            <a class="dropdown-item" href="javascript:void(0);">Last 30 Days</a>
                            <a class="dropdown-item" href="javascript:void(0);">All Time</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="mb-0">Total Schedules</h6>
                            <small class="text-muted">All schedules in the system</small>
                        </div>
                        <div>
                            <h5 class="mb-0"><?php echo $total_schedules; ?></h5>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3 pt-3 border-top">
                        <div>
                            <h6 class="mb-0">Completed</h6>
                            <small class="text-muted">Schedules marked as completed</small>
                        </div>
                        <div>
                            <h5 class="mb-0 text-success"><?php echo $completed_count; ?></h5>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                        <div>
                            <h6 class="mb-0">Incomplete</h6>
                            <small class="text-muted">Schedules pending completion</small>
                        </div>
                        <div>
                            <h5 class="mb-0 text-warning"><?php echo $incomplete_count; ?></h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Completion Rate Visualization</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="chart-progress position-relative" style="height: 180px; width: 180px; margin: 0 auto;">
                            <canvas id="completionChart" height="180" width="180"></canvas>
                            <div class="position-absolute top-50 start-50 translate-middle text-center">
                                <h3 class="mb-0"><?php echo $completion_percentage; ?>%</h3>
                                <p class="mb-0">Completed</p>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-center gap-3">
                        <div class="d-flex align-items-center">
                            <span class="badge bg-success me-2"></span>
                            <span>Completed</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-warning me-2"></span>
                            <span>Incomplete</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js for visualization (only include if not already loaded in parent page) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.1/chart.min.js"></script>
<script>
// Initialize the doughnut chart for completion rate
document.addEventListener('DOMContentLoaded', function() {
    var chartElem = document.getElementById('completionChart');
    if (!chartElem) return;
    var ctx = chartElem.getContext('2d');
    var completionRate = <?php echo $completion_percentage; ?>;
    var incompleteRate = 100 - completionRate;
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [completionRate, incompleteRate],
                backgroundColor: ['#71dd37', '#ffab00'],
                borderWidth: 0,
                cutout: '75%'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    enabled: true
                }
            },
            animation: {
                animateScale: true,
                animateRotate: true
            }
        }
    });
});
</script>