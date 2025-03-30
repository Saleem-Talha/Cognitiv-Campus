
<?php
// get-search-results.php
header('Content-Type: application/json');

require_once 'includes/validation.php';
require_once 'includes/db-connect.php';

if (!isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$searchQuery = $data['search'] ?? '';
$userInfo = getUserInfo();
$userEmail = $userInfo['email'];

$results = [];

if (!empty($searchQuery)) {
    $searchTerm = '%' . $searchQuery . '%';
    
    $query = "
    SELECT 'notes_course' AS source, id, page_title AS title, datetime, userEmail, content 
    FROM notes_course 
    WHERE userEmail = ? AND (page_title LIKE ? OR courseType LIKE ?)

    UNION ALL

    SELECT 'notes_project' AS source, id, page_title AS title, datetime, userEmail, content 
    FROM notes_project 
    WHERE userEmail = ? AND page_title LIKE ?

    UNION ALL

    SELECT 'own_course' AS source, id, name AS title, NULL AS datetime, userEmail, image AS content 
    FROM own_course 
    WHERE userEmail = ? AND name LIKE ?

    UNION ALL

    SELECT 'project' AS source, id, name AS title, start_date AS datetime, ownerEmail AS userEmail, status AS content 
    FROM projects 
    WHERE ownerEmail = ? AND name LIKE ?

    UNION ALL

    SELECT 'course_status' AS source, course_id AS id, course_name AS title, created_at AS datetime, user_id AS userEmail, status AS content 
    FROM course_status 
    WHERE user_id = ? AND course_name LIKE ?
    ";

    $stmt = $db->prepare($query);
    $stmt->bind_param(
        'sssssssssss',
        $userEmail, $searchTerm, $searchTerm,
        $userEmail, $searchTerm,
        $userEmail, $searchTerm,
        $userEmail, $searchTerm,
        $userEmail, $searchTerm
    );

    $stmt->execute();
    $results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Add URLs to results
    foreach ($results as &$result) {
        $result['url'] = getDetailsLink($result['source'], $result['id']);
    }
}

echo json_encode(['results' => $results]);

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