<?php
// search-get-results.php
// Note: This file is kept for backward compatibility but we recommend
// using the direct approach in search.php instead

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once 'includes/validation.php';
require_once 'includes/db-connect.php';
require_once 'includes/utils.php'; // Added utils.php inclusion for encodeId function

// Log the input for debugging
error_log("Search API called with input: " . file_get_contents('php://input'));

if (!isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit();
}

// Attempt to parse JSON input
$rawInput = file_get_contents('php://input');
$data = null;

try {
    $data = json_decode($rawInput, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("JSON decode error: " . json_last_error_msg());
    }
} catch (Exception $e) {
    error_log("JSON parsing error: " . $e->getMessage());
    $data = [];
    
    // Fallback to GET/POST parameters
    if (isset($_POST['search'])) {
        $data['search'] = $_POST['search'];
    } elseif (isset($_GET['search'])) {
        $data['search'] = $_GET['search'];
    }
}

$searchQuery = isset($data['search']) ? trim($data['search']) : '';
$userInfo = getUserInfo();
$userEmail = $userInfo['email'];

error_log("Processing search for user: $userEmail, query: $searchQuery");

$results = [];

if (!empty($searchQuery)) {
    $searchTerm = '%' . $searchQuery . '%';
    
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
    
    $results = [];
    
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
                $results[] = $item; // Add to combined results
            }
        }
        
        // Sort results by title
        usort($results, function($a, $b) {
            return strcmp($a['title'], $b['title']);
        });
        
        error_log("Search found " . count($results) . " total results");
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        exit();
    }
}

// Return results even if empty
echo json_encode(['results' => $results]);
exit();

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