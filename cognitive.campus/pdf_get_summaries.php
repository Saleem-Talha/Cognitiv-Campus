<?php
require_once 'includes/db-connect.php';
include_once('includes/validation.php');
$userInfo = getUserInfo();
$userEmail = $userInfo['email'];

function getSummaries($user_email, $limit = 10, $offset = 0) {
    global $db;
    
    // Prepare SQL statement to fetch summaries
    $query = "SELECT id, user_email, filename, summary, created_at 
              FROM pdf_summaries 
              WHERE user_email = ? 
              ORDER BY created_at DESC 
              LIMIT ? OFFSET ?";
    
    // Prepare statement
    $stmt = $db->prepare($query);
    
    if ($stmt === false) {
        // Handle preparation error
        error_log("Prepare failed: " . $db->error);
        return [];
    }
    
    // Bind parameters
    // 'sii' means: string (email), then two integers (limit and offset)
    $stmt->bind_param("sii", $user_email, $limit, $offset);
    
    // Execute the query
    if (!$stmt->execute()) {
        // Handle execution error
        error_log("Execute failed: " . $stmt->error);
        return [];
    }
    
    // Get the result
    $result = $stmt->get_result();
    
    // Fetch all rows
    $summaries = $result->fetch_all(MYSQLI_ASSOC);
    
    // Close statement
    $stmt->close();
    
    return $summaries;
}

function countTotalSummaries($user_email) {
    global $db;
    
    // Prepare SQL statement to count total summaries for a specific user
    $query = "SELECT COUNT(*) as total FROM pdf_summaries WHERE user_email = ?";
    
    // Prepare statement
    $stmt = $db->prepare($query);
    
    if ($stmt === false) {
        // Handle preparation error
        error_log("Prepare failed: " . $db->error);
        return 0;
    }
    
    // Bind user email parameter
    $stmt->bind_param("s", $user_email);
    
    // Execute query
    if (!$stmt->execute()) {
        // Handle execution error
        error_log("Execute failed: " . $stmt->error);
        return 0;
    }
    
    // Get result
    $result = $stmt->get_result();
    
    if ($result) {
        $row = $result->fetch_assoc();
        
        // Close statement
        $stmt->close();
        
        return $row['total'];
    }
    
    // Close statement if no result
    $stmt->close();
    
    return 0;
}

$summaries = getSummaries($userEmail);
$total_summaries = countTotalSummaries($userEmail);
?>