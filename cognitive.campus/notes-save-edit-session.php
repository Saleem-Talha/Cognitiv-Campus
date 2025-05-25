<?php
/**
 * Save Edit Session - Enhanced Version
 * 
 * Saves user edit session data to the database with removed note type handling
 */

// Include required files
require_once 'includes/db-connect.php';
require_once 'includes/validation.php';

// Get user info
$userInfo = getUserInfo();
$userEmail = $userInfo['email'];

// Set default response
$response = [
    'success' => false,
    'message' => 'An error occurred while saving the edit session'
];

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate required fields
        $requiredFields = ['noteId', 'startTime', 'endTime', 'durationSeconds'];
        foreach ($requiredFields as $field) {
            if (!isset($_POST[$field]) || empty($_POST[$field])) {
                throw new Exception("Missing required field: $field");
            }
        }
        
        // Log received data for debugging
        error_log("Edit session data received: " . json_encode($_POST));
        
        $noteId = (int)$_POST['noteId'];
        if ($noteId <= 0) {
            throw new Exception("Invalid note ID: $noteId");
        }
        
        // Validate timestamps
        $startTime = sanitizeInput($_POST['startTime']);
        $endTime = sanitizeInput($_POST['endTime']);
        
        // Convert ISO format to MySQL datetime format
        $startDateTime = new DateTime($startTime);
        $endDateTime = new DateTime($endTime);
        
        $formattedStartTime = $startDateTime->format('Y-m-d H:i:s');
        $formattedEndTime = $endDateTime->format('Y-m-d H:i:s');
        
        // Calculate duration (server-side validation)
        $durationSeconds = (int)$_POST['durationSeconds'];
        $calculatedDuration = $endDateTime->getTimestamp() - $startDateTime->getTimestamp();
        
        // If the client-side duration is off by more than 10 seconds, use the server calculation
        if (abs($durationSeconds - $calculatedDuration) > 10) {
            $durationSeconds = $calculatedDuration;
        }
        
        // Calculate week number and year from start time
        $weekNumber = (int)$startDateTime->format('W');
        $year = (int)$startDateTime->format('Y');
        
        // Log data before insert
        error_log("Inserting edit session: " . json_encode([
            'note_id' => $noteId,
            'userEmail' => $userEmail,
            'start_time' => $formattedStartTime,
            'end_time' => $formattedEndTime,
            'duration_seconds' => $durationSeconds,
            'week_number' => $weekNumber,
            'year' => $year
        ]));
        
        // Prepare and execute SQL query
        $stmt = $db->prepare("
            INSERT INTO edit_sessions (
                note_id, 
                userEmail, 
                start_time, 
                end_time, 
                duration_seconds, 
                week_number, 
                year
            ) VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $db->error);
        }
        
        $stmt->bind_param("isssiis", 
            $noteId, 
            $userEmail, 
            $formattedStartTime, 
            $formattedEndTime, 
            $durationSeconds, 
            $weekNumber, 
            $year
        );
        
        $result = $stmt->execute();
        
        if ($result) {
            $response = [
                'success' => true,
                'message' => 'Edit session saved successfully',
                'sessionId' => $db->insert_id
            ];
            error_log("Edit session saved successfully with ID: " . $db->insert_id);
        } else {
            throw new Exception("Database error: " . $stmt->error);
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        $response = [
            'success' => false,
            'message' => $e->getMessage()
        ];
        
        // Log the error
        error_log("Edit session error: " . $e->getMessage());
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit;

/**
 * Sanitize input data
 * @param string $data Input data to sanitize
 * @return string Sanitized data
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>