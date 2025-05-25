<?php include_once('includes/header.php'); ?>
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        <?php include_once('includes/sidebar-main.php'); ?>
        <div class="layout-page">
            <?php include_once('includes/navbar.php'); ?>
            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">
                    <h4 class="py-3 mb-4"><span class="text-muted fw-light">Student / </span> Productivity Dashboard</h4>

                    <?php
                    // Include required files
                    require_once 'includes/db-connect.php';
                    require_once 'includes/validation.php';
                    require_once 'productivity_metrics.php';

                    // Get user info
                    $userInfo = getUserInfo();
                    $userEmail = $userInfo['email'];

                    // Create productivity metrics instance
                    $productivitySystem = new ProductivityMetricsSystem($db, $userEmail);

                    // Calculate and store latest productivity metrics
                    $productivitySystem->calculateAndStoreProductivity();

                    // Get weekly comparison data
                    $weeklyComparison = $productivitySystem->getWeeklyComparison();

                    // Get note edit details
                    $noteEditDetails = $productivitySystem->getNoteEditDetails();

                    // Format the data for display
                    $currentWeekData = $weeklyComparison['current_week'];
                    $previousWeekData = $weeklyComparison['previous_week'];
                    $changes = $weeklyComparison['changes'];

                    // Calculate percentage changes (avoid division by zero)
                    $completionRateChangePercent = ($previousWeekData['completion_rate'] > 0) 
                        ? ($changes['completion_rate'] / $previousWeekData['completion_rate']) * 100 
                        : 0;
                        
                    $noteProductivityChangePercent = ($previousWeekData['note_productivity'] > 0) 
                        ? ($changes['note_productivity'] / $previousWeekData['note_productivity']) * 100 
                        : 0;
                        
                    $overallScoreChangePercent = ($previousWeekData['overall_score'] > 0) 
                        ? ($changes['overall_score'] / $previousWeekData['overall_score']) * 100 
                        : 0;

                    // Format edit time data
                    $currentWeekEditTime = $noteEditDetails['current_week']['total_time'];
                    $previousWeekEditTime = $noteEditDetails['previous_week']['total_time'];
                    $editTimeChangePercent = ($previousWeekEditTime > 0) 
                        ? (($currentWeekEditTime - $previousWeekEditTime) / $previousWeekEditTime) * 100 
                        : 0;

                    // Determine current week number and dates
                    $date = new DateTime();
                    $currentWeekNumber = (int)$date->format('W');
                    $currentYear = (int)$date->format('Y');

                    // Calculate previous week
                    $prevDate = clone $date;
                    $prevDate->modify('-1 week');
                    $previousWeekNumber = (int)$prevDate->format('W');
                    $previousYear = (int)$prevDate->format('Y');

                    // Get week date ranges
                    function getWeekDates($weekNumber, $year) {
                        $dateTime = new DateTime();
                        $dateTime->setISODate($year, $weekNumber);
                        $startDate = $dateTime->format('M j');
                        
                        $dateTime->modify('+6 days');
                        $endDate = $dateTime->format('M j');
                        
                        return "$startDate - $endDate";
                    }

                    $currentWeekDates = getWeekDates($currentWeekNumber, $currentYear);
                    $previousWeekDates = getWeekDates($previousWeekNumber, $previousYear);

                    // Helper function to format time in human-readable format
                    function formatTime($seconds) {
                        $hours = floor($seconds / 3600);
                        $minutes = floor(($seconds % 3600) / 60);
                        
                        if ($hours > 0) {
                            return "$hours hr " . ($minutes > 0 ? "$minutes min" : "");
                        } else {
                            return "$minutes min";
                        }
                    }

                    // Helper function to format change indicators
                    function formatChange($change, $isPercent = false) {
                        if (abs($change) < 0.01) {
                            return '<span class="text-muted">No change</span>';
                        }
                        
                        $prefix = $change > 0 ? '+' : '';
                        $class = $change > 0 ? 'text-success' : 'text-danger';
                        $arrow = $change > 0 ? '↑' : '↓';
                        
                        $formattedValue = $isPercent 
                            ? number_format(abs($change), 1) . '%'
                            : number_format(abs($change), 1);
                            
                        return "<span class=\"$class\">$arrow $prefix$formattedValue</span>";
                    }

                    // Get productivity score class
                    function getScoreClass($score) {
                        if ($score >= 75) return 'success';
                        if ($score >= 50) return 'warning';
                        return 'danger';
                    }
                    
                    $overallScoreClass = getScoreClass($currentWeekData['overall_score']);
                    $completionScoreClass = getScoreClass($currentWeekData['completion_rate']);
                    $noteScoreClass = getScoreClass($currentWeekData['note_productivity']);
                    ?>

                    <!-- Summary Stats Cards -->
                    <div class="row g-4 mb-4">
                        <!-- Overall Score Card -->
                        <div class="col-sm-6 col-lg-3">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-body pb-0">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="bg-<?php echo $overallScoreClass; ?> bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width:48px;height:48px;">
                                            <i class="bx bx-trophy text-<?php echo $overallScoreClass; ?> fs-3"></i>
                                        </div>
                                        <span class="fw-semibold text-primary">Overall Score</span>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between">
                                        <div>
                                            <h2 class="mb-0 fw-bold text-<?php echo $overallScoreClass; ?>"><?php echo round($currentWeekData['overall_score']); ?></h2>
                                            <span class="text-muted small">points</span>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-<?php echo $overallScoreClass; ?> bg-opacity-25 text-<?php echo $overallScoreClass; ?> mb-1 px-2 py-1 fs-6">
                                                <?php echo formatChange($changes['overall_score']); ?>
                                            </span>
                                            <div class="text-muted small">from last week</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Task Completion Card -->
                        <div class="col-sm-6 col-lg-3">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-body pb-0">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="bg-<?php echo $completionScoreClass; ?> bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width:48px;height:48px;">
                                            <i class="bx bx-task text-<?php echo $completionScoreClass; ?> fs-3"></i>
                                        </div>
                                        <span class="fw-semibold text-primary">Task Completion</span>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between">
                                        <div>
                                            <h2 class="mb-0 fw-bold text-<?php echo $completionScoreClass; ?>"><?php echo round($currentWeekData['completion_rate'], 1); ?><span class="fs-5">%</span></h2>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-<?php echo $completionScoreClass; ?> bg-opacity-25 text-<?php echo $completionScoreClass; ?> mb-1 px-2 py-1 fs-6">
                                                <?php echo formatChange($changes['completion_rate']); ?>
                                            </span>
                                            <div class="text-muted small">from last week</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Note Productivity Card -->
                        <div class="col-sm-6 col-lg-3">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-body pb-0">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="bg-<?php echo $noteScoreClass; ?> bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width:48px;height:48px;">
                                            <i class="bx bx-notepad text-<?php echo $noteScoreClass; ?> fs-3"></i>
                                        </div>
                                        <span class="fw-semibold text-primary">Note Productivity</span>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between">
                                        <div>
                                            <h2 class="mb-0 fw-bold text-<?php echo $noteScoreClass; ?>"><?php echo round($currentWeekData['note_productivity'], 1); ?><span class="fs-5">%</span></h2>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-<?php echo $noteScoreClass; ?> bg-opacity-25 text-<?php echo $noteScoreClass; ?> mb-1 px-2 py-1 fs-6">
                                                <?php echo formatChange($changes['note_productivity']); ?>
                                            </span>
                                            <div class="text-muted small">from last week</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Total Edit Time Card -->
                        <div class="col-sm-6 col-lg-3">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-body pb-0">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="bg-info bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width:48px;height:48px;">
                                            <i class="bx bx-time text-info fs-3"></i>
                                        </div>
                                        <span class="fw-semibold text-primary">Total Edit Time</span>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between">
                                        <div>
                                            <h2 class="mb-0 fw-bold text-info"><?php echo formatTime($currentWeekEditTime); ?></h2>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-info bg-opacity-25 text-info mb-1 px-2 py-1 fs-6">
                                                <?php 
                                                    $timeChangeText = $editTimeChangePercent > 0 ? 'more' : 'less';
                                                    echo formatChange($editTimeChangePercent, true);
                                                ?>
                                            </span>
                                            <div class="text-muted small"><?php echo $timeChangeText; ?> than last week</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Main Dashboard Content -->
                    <div class="row">
                        <!-- Productivity Comparison Card -->
                        <div class="col-lg-8 mb-4">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">Productivity Comparison</h5>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            Week <?php echo $currentWeekNumber; ?> (<?php echo $currentWeekDates; ?>)
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#">Week <?php echo $currentWeekNumber; ?></a></li>
                                            <li><a class="dropdown-item" href="#">Week <?php echo $previousWeekNumber; ?></a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div style="height: 350px;">
                                        <canvas id="productivityChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Detailed Stats Card -->
                        <div class="col-lg-4 mb-4">
                            <div class="card h-100">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">Weekly Metrics</h5>
                                    <div class="badge bg-primary">Week <?php echo $currentWeekNumber; ?></div>
                                </div>
                                <div class="card-body">
                                    <div class="mb-4">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <small class="text-muted">Overall Score</small>
                                            <div class="badge bg-<?php echo $overallScoreClass; ?>"><?php echo round($currentWeekData['overall_score']); ?> / 100</div>
                                        </div>
                                        <div class="progress mb-3" style="height: 8px;">
                                            <div class="progress-bar bg-<?php echo $overallScoreClass; ?>" style="width: <?php echo min(100, $currentWeekData['overall_score']); ?>%"></div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <small class="text-muted">Task Completion</small>
                                            <div class="badge bg-<?php echo $completionScoreClass; ?>"><?php echo round($currentWeekData['completion_rate']); ?>%</div>
                                        </div>
                                        <div class="progress mb-3" style="height: 8px;">
                                            <div class="progress-bar bg-<?php echo $completionScoreClass; ?>" style="width: <?php echo min(100, $currentWeekData['completion_rate']); ?>%"></div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <small class="text-muted">Note Productivity</small>
                                            <div class="badge bg-<?php echo $noteScoreClass; ?>"><?php echo round($currentWeekData['note_productivity']); ?>%</div>
                                        </div>
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar bg-<?php echo $noteScoreClass; ?>" style="width: <?php echo min(100, $currentWeekData['note_productivity']); ?>%"></div>
                                        </div>
                                    </div>
                                    
                                    <hr>
                                    
                                    <h6 class="mt-4 mb-3">Notes Activity</h6>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="d-flex align-items-center">
                                            <i class="bx bx-note me-2 text-primary"></i>
                                            <span>Notes Created</span>
                                        </div>
                                        <span><?php echo count($noteEditDetails['current_week']['sessions']); ?></span>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="d-flex align-items-center">
                                            <i class="bx bx-time me-2 text-info"></i>
                                            <span>Avg. Edit Time</span>
                                        </div>
                                        <span>
                                            <?php
                                            $noteCount = count($noteEditDetails['current_week']['sessions']);
                                            $avgTime = ($noteCount > 0) ? $currentWeekEditTime / $noteCount : 0;
                                            echo formatTime($avgTime);
                                            ?>
                                        </span>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <i class="bx bx-trending-up me-2 text-success"></i>
                                            <span>Progress</span>
                                        </div>
                                        <span>
                                            <?php 
                                            $progressIndicator = $currentWeekData['overall_score'] > $previousWeekData['overall_score'] ? 'Improving' : 'Needs Focus';
                                            $progressClass = $currentWeekData['overall_score'] > $previousWeekData['overall_score'] ? 'text-success' : 'text-warning';
                                            echo '<span class="' . $progressClass . '">' . $progressIndicator . '</span>';
                                            ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed Week Comparison -->
                    <div class="row">
                        <div class="col-md-12 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Week-to-Week Performance</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Metric</th>
                                                    <th class="text-center">Current Week<br><small class="text-muted"><?php echo $currentWeekDates; ?></small></th>
                                                    <th class="text-center">Previous Week<br><small class="text-muted"><?php echo $previousWeekDates; ?></small></th>
                                                    <th class="text-center">Change</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Overall Score</td>
                                                    <td class="text-center">
                                                        <span class="badge bg-label-<?php echo $overallScoreClass; ?> rounded-pill">
                                                            <?php echo round($currentWeekData['overall_score'], 1); ?>
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php echo round($previousWeekData['overall_score'], 1); ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php echo formatChange($changes['overall_score']); ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Task Completion Rate</td>
                                                    <td class="text-center">
                                                        <span class="badge bg-label-<?php echo $completionScoreClass; ?> rounded-pill">
                                                            <?php echo round($currentWeekData['completion_rate'], 1); ?>%
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php echo round($previousWeekData['completion_rate'], 1); ?>%
                                                    </td>
                                                    <td class="text-center">
                                                        <?php echo formatChange($changes['completion_rate']); ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Note Productivity</td>
                                                    <td class="text-center">
                                                        <span class="badge bg-label-<?php echo $noteScoreClass; ?> rounded-pill">
                                                            <?php echo round($currentWeekData['note_productivity'], 1); ?>%
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php echo round($previousWeekData['note_productivity'], 1); ?>%
                                                    </td>
                                                    <td class="text-center">
                                                        <?php echo formatChange($changes['note_productivity']); ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Total Edit Time</td>
                                                    <td class="text-center">
                                                        <?php echo formatTime($currentWeekEditTime); ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php echo formatTime($previousWeekEditTime); ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php
                                                        $timeChange = $currentWeekEditTime - $previousWeekEditTime;
                                                        $timeChangeFormatted = formatTime(abs($timeChange));
                                                        $indicator = $timeChange > 0 ? 'more' : 'less';
                                                        echo formatChange($editTimeChangePercent, true) . " ($timeChangeFormatted $indicator)";
                                                        ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Note Count</td>
                                                    <td class="text-center">
                                                        <?php echo count($noteEditDetails['current_week']['sessions']); ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php echo count($noteEditDetails['previous_week']['sessions']); ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php
                                                        $noteCountChange = count($noteEditDetails['current_week']['sessions']) - count($noteEditDetails['previous_week']['sessions']);
                                                        $noteCountPercent = (count($noteEditDetails['previous_week']['sessions']) > 0) 
                                                            ? ($noteCountChange / count($noteEditDetails['previous_week']['sessions'])) * 100 
                                                            : 0;
                                                        echo formatChange($noteCountPercent, true);
                                                        ?>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
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

<!-- Chart.js Script -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get productivity data for the chart
        const ctx = document.getElementById('productivityChart').getContext('2d');
        
        // Prepare data for the chart
        const labels = ['Completion Rate', 'Note Productivity', 'Overall Score'];
        const currentWeekData = [
            <?php echo round($currentWeekData['completion_rate']); ?>,
            <?php echo round($currentWeekData['note_productivity']); ?>,
            <?php echo round($currentWeekData['overall_score']); ?>
        ];
        const previousWeekData = [
            <?php echo round($previousWeekData['completion_rate']); ?>,
            <?php echo round($previousWeekData['note_productivity']); ?>,
            <?php echo round($previousWeekData['overall_score']); ?>
        ];

        // Chart colors
        const currentWeekColor = '#696cff'; // Primary color
        const previousWeekColor = '#8592a3'; // Secondary/gray color

        // Create the chart
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Current Week',
                        data: currentWeekData,
                        backgroundColor: currentWeekColor + 'CC', // With opacity
                        borderColor: currentWeekColor,
                        borderWidth: 1,
                        borderRadius: 6
                    },
                    {
                        label: 'Previous Week',
                        data: previousWeekData,
                        backgroundColor: previousWeekColor + 'CC', // With opacity
                        borderColor: previousWeekColor,
                        borderWidth: 1,
                        borderRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        top: 10,
                        right: 10,
                        bottom: 10,
                        left: 10
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: {
                            drawBorder: false,
                            color: '#f0f0f0'
                        },
                        ticks: {
                            stepSize: 20
                        },
                        title: {
                            display: true,
                            text: 'Score (%)',
                            color: '#566a7f',
                            font: {
                                size: 13
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            boxWidth: 12,
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.7)',
                        padding: 10,
                        cornerRadius: 4,
                        titleFont: {
                            size: 14
                        },
                        bodyFont: {
                            size: 13
                        }
                    }
                },
                animation: {
                    duration: 1000
                }
            }
        });
    });
</script>