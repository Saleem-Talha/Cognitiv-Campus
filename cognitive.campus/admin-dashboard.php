<?php
include_once("includes/db-connect.php");
include_once('admin-auth.php'); 
include_once('includes/header.php'); 


$admin_username = $_SESSION['admin_username'];

// Fetch counts for dashboard
$user_count = $db->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$project_count = $db->query("SELECT COUNT(*) as count FROM projects")->fetch_assoc()['count'];
$offer_count = $db->query("SELECT COUNT(*) as count FROM offers WHERE end_date >= CURDATE()")->fetch_assoc()['count'];
$feedback_count = $db->query("SELECT COUNT(*) as count FROM feedback")->fetch_assoc()['count'];

// Fetch recent activities with usernames
$recent_activities = $db->query("
    (SELECT 'New user registered' as activity, email as username, created_at as activity_date FROM users ORDER BY created_at DESC LIMIT 3)
    UNION ALL
    (SELECT 'New project created' as activity, ownerEmail as username, start_date as activity_date FROM projects ORDER BY start_date DESC LIMIT 3)
    UNION ALL
    (SELECT 'Feedback submitted' as activity, userEmail as username, datetime as activity_date FROM feedback ORDER BY datetime DESC LIMIT 3)
    ORDER BY activity_date DESC
    LIMIT 5
");

// Fetch data for user growth chart
$user_growth = $db->query("SELECT DATE(created_at) as date, COUNT(*) as count FROM users GROUP BY DATE(created_at) ORDER BY date DESC LIMIT 7");
$user_growth_data = array_reverse($user_growth->fetch_all(MYSQLI_ASSOC));

// Fetch data for feedback chart
$feedback_data = $db->query("SELECT rating, COUNT(*) as count FROM feedback GROUP BY rating")->fetch_all(MYSQLI_ASSOC);

// Fetch data for recent activities chart
$activity_data = $db->query("
    SELECT 'User Registrations' as activity_type, COUNT(*) as count FROM users WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    UNION ALL
    SELECT 'New Projects' as activity_type, COUNT(*) as count FROM projects WHERE start_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    UNION ALL
    SELECT 'Feedbacks' as activity_type, COUNT(*) as count FROM feedback WHERE datetime >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
")->fetch_all(MYSQLI_ASSOC);

include_once('includes/header.php');
?>
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        <?php include_once('includes/admin-sidebar.php'); ?>
        <div class="layout-page">
            <?php include_once('includes/navbar.php'); ?>
            <div class="content-wrapper">

                <div class="container-xxl flex-grow-1 container-p-y">
                    <h4 class="py-3 mb-4"><span class="text-muted fw-light">Admin /</span> Dashboard</h4>

                    <div class="row">
                        <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div class="card-primary">
                                            <p class="card-text">Total Users</p>
                                            <div class="d-flex align-items-end mb-2">
                                                <h4 class="card-title mb-0 me-2"><?php echo $user_count; ?></h4>
                                            </div>
                                        </div>
                                        <div class="card-icon">
                                            <span class="badge bg-label-primary rounded p-2">
                                                <i class="bx bx-user bx-sm"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <a href="admin-users.php" class="btn btn-sm btn-outline-primary">View Users</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div class="card-primary">
                                            <p class="card-text">Total Projects</p>
                                            <div class="d-flex align-items-end mb-2">
                                                <h4 class="card-title mb-0 me-2"><?php echo $project_count; ?></h4>
                                            </div>
                                        </div>
                                        <div class="card-icon">
                                            <span class="badge bg-label-primary rounded p-2">
                                                <i class="bx bx-folder bx-sm"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <a href="admin-projects.php" class="btn btn-sm btn-outline-primary">View Projects</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div class="card-primary">
                                            <p class="card-text">Active Offers</p>
                                            <div class="d-flex align-items-end mb-2">
                                                <h4 class="card-title mb-0 me-2"><?php echo $offer_count; ?></h4>
                                            </div>
                                        </div>
                                        <div class="card-icon">
                                            <span class="badge bg-label-primary rounded p-2">
                                                <i class="bx bx-gift bx-sm"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <a href="admin-offers.php" class="btn btn-sm btn-outline-primary">Manage Offers</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div class="card-primary">
                                            <p class="card-text">Total Feedbacks</p>
                                            <div class="d-flex align-items-end mb-2">
                                                <h4 class="card-title mb-0 me-2"><?php echo $feedback_count; ?></h4>
                                            </div>
                                        </div>
                                        <div class="card-icon">
                                            <span class="badge bg-label-primary rounded p-2">
                                                <i class="bx bx-message-square-dots bx-sm"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <a href="admin-feedbacks.php" class="btn btn-sm btn-outline-primary">View Feedbacks</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">User Growth</h5>
                                </div>
                                <div class="card-body">
                                    <div id="userGrowthChart"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <h5 class="card-title">Quick Actions</h5>
                                    <div class="d-grid gap-2">
                                        <a href="admin-offers.php" class="btn btn-outline-primary">Create New Offer</a>
                                        <a href="dashboard.php" class="btn btn-outline-primary">Go to User Dashboard</a>
                                        <a href="admin-feedbacks.php" class="btn btn-outline-primary">Manage Landing Page Feedbacks</a>
                                        <a href="admin-change-password.php" class="btn btn-outline-primary">Change Admin Password</a>
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Feedback Distribution</h5>
                                </div>
                                <div class="card-body">
                                    <div id="feedbackChart"></div>
                                </div>
                            </div>
                        </div>                
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Recent Activities Chart</h5>
                                </div>
                                <div class="card-body">
                                    <div id="recentActivitiesChart"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Recent Activities</h5>
                                    <ul class="list-group list-group-flush">
                                        <?php
                                        while ($activity = $recent_activities->fetch_assoc()) {
                                            $icon = '';
                                            $color = '';
                                            switch ($activity['activity']) {
                                                case 'New user registered':
                                                    $icon = 'bx-user-plus';
                                                    $color = 'text-primary';
                                                    break;
                                                case 'New project created':
                                                    $icon = 'bx-folder-plus';
                                                    $color = 'text-primary';
                                                    break;
                                                case 'Feedback submitted':
                                                    $icon = 'bx-message-square-dots';
                                                    $color = 'text-primary';
                                                    break;
                                            }
                                            echo "<li class='list-group-item'>
                                                    <i class='bx {$icon} {$color} me-2'></i>
                                                    <strong>{$activity['activity']}</strong> by <span class='text-primary'>{$activity['username']}</span>
                                                    <br><small class='text-muted'>" . date('M d, Y H:i', strtotime($activity['activity_date'])) . "</small>
                                                  </li>";
                                        }
                                        ?>
                                    </ul>
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

<script>
    // User Growth Chart
    var userGrowthOptions = {
        series: [{
            name: 'New Users',
            data: <?php echo json_encode(array_column($user_growth_data, 'count')); ?>
        }],
        chart: {
            height: 550,
            type: 'area'
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: 'smooth'
        },
        xaxis: {
            type: 'datetime',
            categories: <?php echo json_encode(array_column($user_growth_data, 'date')); ?>
        },
        tooltip: {
            x: {
                format: 'dd/MM/yy'
            },
        },
        colors: ['#5f61e6'] // Primary color
    };

    var userGrowthChart = new ApexCharts(document.querySelector("#userGrowthChart"), userGrowthOptions);
    userGrowthChart.render();

    // Feedback Distribution Chart
    var feedbackOptions = {
        series: <?php echo json_encode(array_column($feedback_data, 'count')); ?>,
        chart: {
            width: 380,
            type: 'pie',
        },
        labels: <?php echo json_encode(array_column($feedback_data, 'rating')); ?>,
        responsive: [{
            breakpoint: 480,
            options: {
                chart: {
                    width: 200
                },
                legend: {
                    position: 'bottom'
                }
            }
        }]
    };

    var feedbackChart = new ApexCharts(document.querySelector("#feedbackChart"), feedbackOptions);
    feedbackChart.render();

    // Recent Activities Chart
    var recentActivitiesOptions = {
        series: [{
            name: 'Activities',
            data: <?php echo json_encode(array_column($activity_data, 'count')); ?>
        }],
        chart: {
            height: 350,
            type: 'radar'
        },
        xaxis: {
            categories: <?php echo json_encode(array_column($activity_data, 'activity_type')); ?>
        },
        yaxis: {
            labels: {
                formatter: function(val) {
                    return Math.round(val);
                }
            }
        },
        dataLabels: {
            enabled: true,
            background: {
                enabled: true,
                borderRadius: 2,
            }
        },
        title: {
            text: 'Recent Activities (Last 7 Days)',
            align: 'center',
            style: {
                color: '#444'
            }
        },
        markers: {
            size: 4,
            colors: ['#fff'],
            strokeColor: '#FF4560',
            strokeWidth: 2,
        },
        tooltip: {
            y: {
                formatter: function(value) {
                    return value;
                }
            }
        },
        theme: {
            palette: 'palette2'
        }
    };

    var recentActivitiesChart = new ApexCharts(document.querySelector("#recentActivitiesChart"), recentActivitiesOptions);
    recentActivitiesChart.render();
</script>