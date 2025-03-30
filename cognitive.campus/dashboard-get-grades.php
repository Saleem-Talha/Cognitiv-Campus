<?php
// dashboard-get-grades.php
header('Content-Type: application/json');

require_once 'includes/validation.php';
require_once 'includes/db-connect.php'; // Make sure this is the correct path to your db-connect file

if (!isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit();
}

$userInfo = getUserInfo();
$userEmail = $userInfo['email']; // Assuming getUserInfo() returns user email

function getUserGradesFromDB($db, $userEmail) {
    try {
        // Sanitize input to prevent SQL injection
        $userEmail = $db->real_escape_string($userEmail);
        
        // Get all courses for this user
        $courseQuery = "SELECT DISTINCT course_id, course_name 
                        FROM student_grades 
                        WHERE user_id = '$userEmail'
                        ORDER BY course_name";
        
        $courseResult = $db->query($courseQuery);
        
        if (!$courseResult) {
            throw new Exception("Database error: " . $db->error);
        }
        
        $courseGrades = [];
        $totalAverage = 0;
        $courseCount = 0;
        
        while ($course = $courseResult->fetch_assoc()) {
            $courseId = $course['course_id'];
            $courseName = $course['course_name'];
            
            // Get assignments for this course
            $assignmentQuery = "SELECT assignment_id, assignment_title, assignment_type, 
                                grade, max_points, submission_state, 
                                graded_at, submitted_at
                                FROM student_grades 
                                WHERE user_id = '$userEmail' 
                                AND course_id = '$courseId'
                                AND submission_state = 'RETURNED'
                                ORDER BY assignment_title";
            
            $assignmentResult = $db->query($assignmentQuery);
            
            if (!$assignmentResult) {
                throw new Exception("Database error: " . $db->error);
            }
            
            $assignments = [];
            $courseTotal = 0;
            $assignmentCount = 0;
            
            while ($assignment = $assignmentResult->fetch_assoc()) {
                if ($assignment['grade'] !== null) {
                    $percentageGrade = round(($assignment['grade'] / $assignment['max_points']) * 100, 2);
                    
                    $assignments[] = [
                        'title' => $assignment['assignment_title'],
                        'obtainedGrade' => $assignment['grade'],
                        'maxPoints' => $assignment['max_points'],
                        'grade' => $percentageGrade,
                        'type' => $assignment['assignment_type']
                    ];
                    
                    $courseTotal += $percentageGrade;
                    $assignmentCount++;
                }
            }
            
            $courseAverage = $assignmentCount > 0 ? round($courseTotal / $assignmentCount, 2) : null;
            
            $courseGrades[] = [
                'courseName' => $courseName,
                'courseId' => $courseId,
                'assignments' => $assignments,
                'courseAverage' => $courseAverage
            ];
            
            if ($courseAverage !== null) {
                $totalAverage += $courseAverage;
                $courseCount++;
            }
        }
        
        $overallAverage = $courseCount > 0 ? round($totalAverage / $courseCount, 2) : null;
        
        return [
            'courses' => $courseGrades,
            'totalGrades' => [
                'overallAverage' => $overallAverage,
                'courseCount' => $courseCount
            ],
            'message' => count($courseGrades) > 0 ? null : 'No grades available.'
        ];
        
    } catch (Exception $e) {
        error_log('Error fetching grades: ' . $e->getMessage());
        return [
            'courses' => [], 
            'totalGrades' => [
                'overallAverage' => null,
                'courseCount' => 0
            ],
            'message' => 'Error fetching grades from database. Please try again later.'
        ];
    }
}

echo json_encode(getUserGradesFromDB($db, $userEmail));