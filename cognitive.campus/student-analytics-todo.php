<?php

$userInfo = getUserInfo();
$userEmail = $userInfo['email'];
function getTaskStatistics() {
    global $db;
    global $userEmail;
    
    // Get count of completed tasks
    $completed_query = "SELECT COUNT(*) as completed FROM tasks WHERE is_completed = 'completed' AND userEmail = ?";
    $stmt_completed = mysqli_prepare($db, $completed_query);
    mysqli_stmt_bind_param($stmt_completed, "s", $userEmail);
    mysqli_stmt_execute($stmt_completed);
    $completed_result = mysqli_stmt_get_result($stmt_completed);
    $completed_count = mysqli_fetch_assoc($completed_result)['completed'];
    mysqli_stmt_close($stmt_completed);

    // Get count of incomplete tasks
    $incomplete_query = "SELECT COUNT(*) as incomplete FROM tasks WHERE is_completed = 'incomplete' AND userEmail = ?";
    $stmt_incomplete = mysqli_prepare($db, $incomplete_query);
    mysqli_stmt_bind_param($stmt_incomplete, "s", $userEmail);
    mysqli_stmt_execute($stmt_incomplete);
    $incomplete_result = mysqli_stmt_get_result($stmt_incomplete);
    $incomplete_count = mysqli_fetch_assoc($incomplete_result)['incomplete'];
    mysqli_stmt_close($stmt_incomplete);
    
    // Get count of active tasks
    $active_query = "SELECT COUNT(*) as active FROM tasks WHERE is_completed = 'active' AND userEmail = ?";
    $stmt_active = mysqli_prepare($db, $active_query);
    mysqli_stmt_bind_param($stmt_active, "s", $userEmail);
    mysqli_stmt_execute($stmt_active);
    $active_result = mysqli_stmt_get_result($stmt_active);
    $active_count = mysqli_fetch_assoc($active_result)['active'];
    mysqli_stmt_close($stmt_active);
    
    // Calculate total and completion percentage
    $total = $completed_count + $incomplete_count + $active_count;
    $completion_percentage = ($total > 0) ? round(($completed_count / $total) * 100) : 0;
    
    return [
        'completed' => $completed_count,
        'incomplete' => $incomplete_count,
        'active' => $active_count,
        'total' => $total,
        'percentage' => $completion_percentage
    ];
}

// Get the statistics
$task_stats = getTaskStatistics();
?>

<!-- Task Statistics Component -->
<div class="row m-3">
    <!-- Column 1: Task Completion Status Card (Percentage) -->
    <div class="col-md-6 col-12 d-flex">
        <div class="card flex-fill h-100">
            <div class="card-body text-center d-flex flex-column justify-content-between h-100">
                <div>
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="card-title mb-0">Task Completion Status</h5>
                            <small class="text-muted">Overall completion metrics</small>
                        </div>
                        <div class="avatar flex-shrink-0 p-2 bg-label-primary">
                            <i class="bx bx-pie-chart-alt fs-3"></i>
                        </div>
                    </div>
                    <div class="mt-4 mb-4">
                        <h1 class="display-4 mb-0"><?php echo $task_stats['percentage']; ?>%</h1>
                        <div class="text-muted">Completion Rate</div>
                    </div>
                    <div class="d-flex justify-content-center mb-4">
                        <div class="text-center">
                            <div class="chart-circle mt-2 mb-3" style="width: 150px; height: 150px;">
                                <span class="text-primary">
                                    <i class="bx bx-task fs-1 me-2"></i><?php echo $task_stats['total']; ?>
                                </span>
                                <small class="d-block text-muted">Total Tasks</small>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Progress bars for visualization -->
                <div class="mt-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <i class="bx bx-check-circle text-success me-1"></i>
                            <span>Completed</span>
                        </div>
                        <div><?php echo $task_stats['completed']; ?></div>
                    </div>
                    <div class="progress mb-3" style="height: 8px">
                        <div class="progress-bar bg-success" style="width: <?php echo ($task_stats['total'] > 0) ? ($task_stats['completed'] / $task_stats['total'] * 100) : 0; ?>%" role="progressbar" aria-valuenow="<?php echo ($task_stats['total'] > 0) ? ($task_stats['completed'] / $task_stats['total'] * 100) : 0; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <i class="bx bx-time text-warning me-1"></i>
                            <span>Incomplete</span>
                        </div>
                        <div><?php echo $task_stats['incomplete']; ?></div>
                    </div>
                    <div class="progress mb-3" style="height: 8px">
                        <div class="progress-bar bg-warning" style="width: <?php echo ($task_stats['total'] > 0) ? ($task_stats['incomplete'] / $task_stats['total'] * 100) : 0; ?>%" role="progressbar" aria-valuenow="<?php echo ($task_stats['total'] > 0) ? ($task_stats['incomplete'] / $task_stats['total'] * 100) : 0; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <i class="bx bx-loader text-info me-1"></i>
                            <span>Active</span>
                        </div>
                        <div><?php echo $task_stats['active']; ?></div>
                    </div>
                    <div class="progress" style="height: 8px">
                        <div class="progress-bar bg-info" style="width: <?php echo ($task_stats['total'] > 0) ? ($task_stats['active'] / $task_stats['total'] * 100) : 0; ?>%" role="progressbar" aria-valuenow="<?php echo ($task_stats['total'] > 0) ? ($task_stats['active'] / $task_stats['total'] * 100) : 0; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Column 2: Completed and Incomplete Tasks Cards -->
    <div class="col-md-6 col-12 d-flex flex-column">
        <div class="card flex-fill mb-4 h-50">
            <div class="card-body d-flex flex-column justify-content-between h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h5 class="card-title mb-0">Completed Tasks</h5>
                        <small class="text-muted">Total tasks marked as completed</small>
                    </div>
                    <div class="avatar flex-shrink-0 p-2 bg-label-success">
                        <i class="bx bx-check-circle fs-3"></i>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="card-info">
                        <h3 class="mb-0"><?php echo $task_stats['completed']; ?></h3>
                        <small><?php echo ($task_stats['total'] > 0) ? round(($task_stats['completed'] / $task_stats['total'] * 100)) : 0; ?>% of total tasks</small>
                    </div>
                    <div class="progress w-50 mt-3" style="height: 8px;">
                        <div class="progress-bar bg-success" role="progressbar" 
                            style="width: <?php echo ($task_stats['total'] > 0) ? ($task_stats['completed'] / $task_stats['total'] * 100) : 0; ?>%" 
                            aria-valuenow="<?php echo ($task_stats['total'] > 0) ? ($task_stats['completed'] / $task_stats['total'] * 100) : 0; ?>" 
                            aria-valuemin="0" 
                            aria-valuemax="100">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card flex-fill h-50">
            <div class="card-body d-flex flex-column justify-content-between h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h5 class="card-title mb-0">Pending Tasks</h5>
                        <small class="text-muted">Tasks still waiting to be completed</small>
                    </div>
                    <div class="avatar flex-shrink-0 p-2 bg-label-warning">
                        <i class="bx bx-time fs-3"></i>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="card-info">
                        <h3 class="mb-0"><?php echo $task_stats['incomplete'] + $task_stats['active']; ?></h3>
                        <small><?php echo ($task_stats['total'] > 0) ? round((($task_stats['incomplete'] + $task_stats['active']) / $task_stats['total'] * 100)) : 0; ?>% of total tasks</small>
                    </div>
                    <div class="progress w-50 mt-3" style="height: 8px;">
                        <div class="progress-bar bg-warning" role="progressbar" 
                            style="width: <?php echo ($task_stats['total'] > 0) ? (($task_stats['incomplete'] + $task_stats['active']) / $task_stats['total'] * 100) : 0; ?>%" 
                            aria-valuenow="<?php echo ($task_stats['total'] > 0) ? (($task_stats['incomplete'] + $task_stats['active']) / $task_stats['total'] * 100) : 0; ?>" 
                            aria-valuemin="0" 
                            aria-valuemax="100">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>