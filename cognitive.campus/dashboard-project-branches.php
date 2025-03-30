<?php 
// Your existing PHP query code remains the same
$sql_chart = "SELECT p.id, p.name, p.start_date, p.end_date, p.status, 
COUNT(pb.id) as branch_count
FROM projects p
LEFT JOIN project_branch pb ON p.id = pb.project_id
WHERE p.ownerEmail = '$userEmail'
GROUP BY p.id
ORDER BY p.start_date";

$result_chart = $db->query($sql_chart);

$projects = [];
$chartData = [];
$projectNames = [];

if ($result_chart->num_rows > 0) {
    while($row_abc = $result_chart->fetch_assoc()) {
        $projects[] = $row_abc;
        $chartData[] = (int)$row_abc['branch_count']; // Cast to integer
        $projectNames[] = $row_abc['name'];
    }
}

// Convert PHP arrays to JavaScript arrays
$chartDataJSON = json_encode($chartData);
$projectNamesJSON = json_encode($projectNames);
?>



<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between flex-sm-row flex-column gap-3">
            <div class="d-flex flex-sm-column flex-row align-items-start justify-content-between">
                <div class="card-title">
                    <h5 class="text-nowrap mb-2">Project Timeline</h5>
                    <span class="badge bg-label-warning rounded-pill">All Projects</span>
                </div>
                <div class="mt-sm-auto">
                    <small class="text-success text-nowrap fw-medium">
                        <i class="bx bx-chevron-up"></i> <?php echo count($projects); ?> Projects
                    </small>
                    <h3 class="mb-0"><?php echo array_sum($chartData); ?> Branches</h3>
                </div>
            </div>
            <div id="projectBranchChart"></div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Make sure we have the element
    const projectBranchChartEl = document.querySelector('#projectBranchChart');
    if (!projectBranchChartEl) {
        console.error('Chart element not found');
        return;
    }

    // Define colors (since config might not be available)
    const chartColors = {
        warning: '#ffab00'
    };

    const projectBranchChartConfig = {
        chart: {
            height: 80,
            type: 'line',
            toolbar: {
                show: false
            },
            dropShadow: {
                enabled: true,
                top: 10,
                left: 5,
                blur: 3,
                color: chartColors.warning,
                opacity: 0.15
            },
            sparkline: {
                enabled: true
            }
        },
        grid: {
            show: false,
            padding: {
                right: 8
            }
        },
        colors: [chartColors.warning],
        dataLabels: {
            enabled: false
        },
        stroke: {
            width: 5,
            curve: 'smooth'
        },
        series: [{
            name: 'Branches',
            data: <?php echo $chartDataJSON; ?>
        }],
        xaxis: {
            categories: <?php echo $projectNamesJSON; ?>,
            show: false,
            lines: {
                show: false
            },
            labels: {
                show: false
            },
            axisBorder: {
                show: false
            }
        },
        yaxis: {
            show: false
        },
        tooltip: {
            x: {
                show: true
            },
            y: {
                title: {
                    formatter: function() {
                        return 'Branches:';
                    }
                }
            }
        }
    };

    try {
        const projectBranchChart = new ApexCharts(projectBranchChartEl, projectBranchChartConfig);
        projectBranchChart.render();
    } catch (error) {
        console.error('Error rendering chart:', error);
    }
});
</script>