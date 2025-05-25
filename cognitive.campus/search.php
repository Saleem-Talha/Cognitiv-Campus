<?php
// search.php - Main page
require_once 'includes/validation.php';
require_once 'includes/db-connect.php';
require_once 'includes/utils.php'; // Added utils.php inclusion for encodeId function

// Fetch logged-in user email
$userInfo = getUserInfo();
$userEmail = $userInfo['email'] ?? null;

// Redirect if the user is not logged in
if (!$userEmail) {
    header('Location: index.php');
    exit();
}

// Get search query from either GET or POST (to support navbar form)
$searchQuery = isset($_POST['search']) ? trim($_POST['search']) : (isset($_GET['search']) ? trim($_GET['search']) : '');

// Process search directly in this file to avoid AJAX issues
$searchResults = [];

if (!empty($searchQuery)) {
    $searchTerm = '%' . $searchQuery . '%';
    
    // Debug - Log search query
    error_log("Search Term: " . $searchTerm . " for user: " . $userEmail);
    
    // Individual queries to help with debugging
    $queries = [
        "notes_course" => "SELECT 'notes_course' AS source, id, page_title AS title, datetime, userEmail, content 
                         FROM notes_course 
                         WHERE userEmail = ? AND (page_title LIKE ? OR courseType LIKE ?)",
                         
        "notes_project" => "SELECT 'notes_project' AS source, id, page_title AS title, datetime, userEmail, content 
                          FROM notes_project 
                          WHERE userEmail = ? AND page_title LIKE ?",
                          
        "own_course" => "SELECT 'own_course' AS source, id, name AS title, NULL AS datetime, userEmail, image AS content 
                        FROM own_course 
                        WHERE userEmail = ? AND name LIKE ?",
                        
        "project" => "SELECT 'project' AS source, id, name AS title, start_date AS datetime, ownerEmail AS userEmail, status AS content 
                     FROM projects 
                     WHERE ownerEmail = ? AND name LIKE ?",
                     
        "course_status" => "SELECT 'course_status' AS source, course_id AS id, course_name AS title, created_at AS datetime, user_id AS userEmail, status AS content 
                          FROM course_status 
                          WHERE user_id = ? AND course_name LIKE ?"
    ];
    
    $searchResults = [];
    
    try {
        // Execute each query separately for debugging
        foreach ($queries as $source => $query) {
            $stmt = $db->prepare($query);
            if (!$stmt) {
                error_log("Failed to prepare query for $source: " . $db->error);
                continue;
            }
            
            // Bind parameters based on the query type
            if ($source == 'notes_course') {
                $stmt->bind_param('sss', $userEmail, $searchTerm, $searchTerm);
            } else {
                $stmt->bind_param('ss', $userEmail, $searchTerm);
            }
            
            if (!$stmt->execute()) {
                error_log("Failed to execute query for $source: " . $stmt->error);
                continue;
            }
            
            $result = $stmt->get_result();
            if (!$result) {
                error_log("Failed to get result for $source: " . $stmt->error);
                continue;
            }
            
            $rows = $result->fetch_all(MYSQLI_ASSOC);
            error_log("$source search found " . count($rows) . " results");
            
            // Add URLs to results
            foreach ($rows as &$item) {
                $item['url'] = getDetailsLink($item['source'], $item['id']);
                $searchResults[] = $item; // Add to combined results
            }
        }
        
        // Sort results by title
        usort($searchResults, function($a, $b) {
            return strcmp($a['title'], $b['title']);
        });
        
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
    }
}

function getDetailsLink($source, $id) {
    switch ($source) {
        case 'notes_course':
            return "notes-page-course.php?id=" . $id;
        case 'notes_project':
            return "notes-page.php?id=" . $id;
        case 'own_course':
            return "subject-details-own.php?id=" . encodeId($id);
        case 'project':
            return "project-details.php?id=" . encodeId($id);
        case 'course_status':
            return "subject-details.php?id=" . $id;
        default:
            return "#";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/header.php'; ?>
    <title>Search Results</title>
    <style>
        .search-item {
            transition: background-color 0.2s;
            text-decoration: none;
            color: inherit;
        }
        .search-item:hover {
            background-color: rgba(0, 0, 0, 0.05);
            text-decoration: none;
            color: inherit;
        }
        .result-type {
            font-size: 0.8rem;
            padding: 0.2rem 0.6rem;
            border-radius: 1rem;
            text-transform: capitalize;
        }
        .type-notes { background-color: #e3f2fd; color: #1565c0; }
        .type-course { background-color: #f3e5f5; color: #7b1fa2; }
        .type-project { background-color: #e8f5e9; color: #2e7d32; }
    </style>
</head>
<body>
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        <?php include_once('includes/sidebar-main.php'); ?>
        <div class="layout-page">
            <?php include_once('includes/navbar.php'); ?>

            <div class="container-xxl flex-grow-1 container-p-y">
                <h4 class="fw-bold mb-4">
                    <span class="text-muted fw-light">Search /</span> 
                    Results for "<?php echo htmlspecialchars($searchQuery); ?>"
                </h4>

                <div id="search-results">
                    <?php if (empty($searchQuery)): ?>
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="bx bx-search bx-lg text-muted mb-3"></i>
                            <h5>Enter a search term</h5>
                            <p class="text-muted">Use the search bar in the navigation to search your data</p>
                        </div>
                    </div>
                    <?php elseif (empty($searchResults)): ?>
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="bx bx-search-alt bx-lg text-muted mb-3"></i>
                            <h5>No Results Found</h5>
                            <p class="text-muted">Try adjusting your search terms or browse all items</p>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="card">
                        <div class="table-responsive text-nowrap">
                            <div class="list-group list-group-flush">
                                <?php foreach ($searchResults as $result): ?>
                                <?php 
                                    $type = str_replace(['notes_', 'own_'], '', $result['source']); 
                                    $typeClass = 'type-' . explode('_', $type)[0];
                                ?>
                                <a href="<?php echo htmlspecialchars($result['url']); ?>" class="list-group-item list-group-item-action search-item">
                                    <div class="d-flex justify-content-between align-items-center py-2">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($result['title']); ?></h6>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="result-type <?php echo $typeClass; ?>">
                                                    <?php echo ucfirst($type); ?>
                                                </span>
                                                <?php if (!empty($result['datetime'])): ?>
                                                <small class="text-muted">
                                                    <?php echo date('M j, Y', strtotime($result['datetime'])); ?>
                                                </small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <i class="bx bx-chevron-right text-muted"></i>
                                    </div>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>