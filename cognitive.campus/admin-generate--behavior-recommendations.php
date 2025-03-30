<?php
include("includes/db-connect.php");
include("admin-auth.php");

function getRecommendations($page = 1, $perPage = 10) {
    global $db;
    $offset = ($page - 1) * $perPage;

    // Count total recommendations
    $countQuery = "SELECT COUNT(*) as total FROM notes_recommendations";
    $countResult = mysqli_query($db, $countQuery);
    $totalRecommendations = mysqli_fetch_assoc($countResult)['total'];
    $totalPages = ceil($totalRecommendations / $perPage);

    // Fetch paginated recommendations
    $query = "SELECT *
              FROM notes_recommendations nr
              ORDER BY nr.note_id, nr.recommendation_rank
              LIMIT $perPage OFFSET $offset";
    
    $result = mysqli_query($db, $query);
    $recommendations = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $recommendations[] = $row;
    }

    return [
        'recommendations' => $recommendations,
        'totalPages' => $totalPages,
        'currentPage' => $page
    ];
}

function executePythonScript() {
    // the absolute path to Python script and dataset with spaces handled correctly
    $scriptDir = dirname(__FILE__);  // Use dirname(__FILE__) to get the current script's directory
    $pythonScript = $scriptDir . DIRECTORY_SEPARATOR . 'admin-behavior-recommendation-modal.py';
    $datasetDir = $scriptDir . DIRECTORY_SEPARATOR . 'dataset';
    $csvFile = $datasetDir . DIRECTORY_SEPARATOR . 'Coursera.csv';

    // Verify Python script exists
    if (!file_exists($pythonScript)) {
        return json_encode([
            'success' => false,
            'output' => ['Error: Python script not found at: ' . $pythonScript],
            'error' => true
        ]);
    }

    // Verify CSV file exists
    if (!file_exists($csvFile)) {
        return json_encode([
            'success' => false,
            'output' => ['Error: Coursera.csv file not found at: ' . $csvFile],
            'error' => true
        ]);
    }

    $output = [];
    $return_var = 0;

    // Change directory to script location
    chdir($scriptDir);

    // Execute Python script with proper path escaping for spaces
    $command = sprintf('python "%s"', str_replace('\\', '/', $pythonScript));
    exec($command . " 2>&1", $output, $return_var);

    // Parse recommendations from output
    $recommendations = [];
    $currentCourse = null;

    foreach ($output as $line) {
        if (preg_match('/Generating recommendations for:\s*(.+)/', $line, $matches)) {
            $currentCourse = trim($matches[1]);
            $recommendations[$currentCourse] = [];
        } elseif (preg_match('/Recommendation #(\d+):/', $line, $recMatches)) {
            $recNum = $recMatches[1];
            continue;
        } elseif (preg_match('/Course:\s*(.+)/', $line, $courseMatches)) {
            $recommendations[$currentCourse][] = [
                'name' => trim($courseMatches[1])
            ];
        } elseif (preg_match('/Similarity Score:\s*(.+)/', $line, $simMatches)) {
            $lastRec = &$recommendations[$currentCourse][count($recommendations[$currentCourse])-1];
            $lastRec['similarity'] = trim($simMatches[1]);
        }
    }

    // Debug information
    $debug_info = [
        'Script Directory' => $scriptDir,
        'Python Script Path' => $pythonScript,
        'CSV File Path' => $csvFile,
        'Command Executed' => $command
    ];

    return json_encode([
        'success' => ($return_var === 0),
        'output' => $output,
        'debug' => $debug_info,
        'recommendations' => $recommendations,
        'error' => ($return_var !== 0),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

// Check if this is an AJAX request to generate recommendations
if(isset($_POST['action']) && $_POST['action'] === 'generate') {
    header('Content-Type: application/json');
    echo executePythonScript();
    exit;
}

// Pagination handling
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$recommendationsData = getRecommendations($page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notes-Based Course Recommendations</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/img/favicon/favicon.ico" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />

    <!-- Icons -->
    <link rel="stylesheet" href="assets/vendor/fonts/boxicons.css" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="assets/vendor/css/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="assets/vendor/css/theme-default.css" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="assets/css/demo.css" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />

    <!-- Page CSS -->
    <link rel="stylesheet" href="assets/vendor/css/pages/page-auth.css" />

    <!-- Helpers -->
    <script src="assets/vendor/js/helpers.js"></script>
    <script src="assets/js/config.js"></script>
</head>
<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include_once('includes/admin-sidebar.php'); ?>
            
            <div class="layout-page">
                <?php include_once('includes/navbar.php'); ?>
                
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div id="alertContainer" class="mb-3"></div>
                        
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Notes-Based Course Recommendations</h5>
                                <button id="generateBtn" class="btn btn-primary">
                                    <span class="tf-icons bx bx-refresh mx-2"></span> Generate Recommendations
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive text-nowrap">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Source Note Type</th>
                                                <th>Source Note ID</th>
                                                <th>Recommended Course</th>
                                                <th>University</th>
                                                <th>Difficulty</th>
                                                <th>Recommendation Rank</th>
                                                <th>Similarity Score</th>
                                                <th>Course Rating</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recommendationsData['recommendations'] as $rec): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($rec['source_note_type']); ?></td>
                                                <td><?php echo htmlspecialchars($rec['note_id']); ?></td>
                                                <td><?php echo htmlspecialchars($rec['recommended_course_name']); ?></td>
                                                <td><?php echo htmlspecialchars($rec['university']); ?></td>
                                                <td><?php echo htmlspecialchars($rec['difficulty_level']); ?></td>
                                                <td><?php echo htmlspecialchars($rec['recommendation_rank']); ?></td>
                                                <td><?php echo number_format($rec['similarity_score'], 2); ?></td>
                                                <td><?php echo number_format($rec['course_rating'], 1); ?></td>
                                                <td>
                                                    <a href="<?php echo htmlspecialchars($rec['course_url']); ?>" 
                                                       target="_blank" 
                                                       class="btn btn-sm btn-primary">
                                                        View Course
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- Pagination -->
                                <nav aria-label="Page navigation" class="mt-3">
                                    <ul class="pagination justify-content-center">
                                        <?php for ($i = 1; $i <= $recommendationsData['totalPages']; $i++): ?>
                                            <li class="page-item <?php echo $i == $recommendationsData['currentPage'] ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                            </li>
                                        <?php endfor; ?>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/vendor/libs/jquery/jquery.js"></script>
    <script src="assets/vendor/libs/popper/popper.js"></script>
    <script src="assets/vendor/js/bootstrap.js"></script>
    <script src="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="assets/vendor/js/menu.js"></script>
    <script src="assets/js/main.js"></script>

    <script>

$(document).ready(function() {
    $('#generateBtn').click(function() {
        const btn = $(this);
        console.log('Generating recommendations: Python script triggered');
        btn.prop('disabled', true);
        $.ajax({
            url: window.location.href,
            type: 'POST',
            data: {
                action: 'generate'
            },
            complete: function() {
                // Always re-enable the button
                btn.prop('disabled', false);
            }
        });
    });
});
</script>

</body>
</html>