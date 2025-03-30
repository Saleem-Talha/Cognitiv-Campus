<?php
// Turn off error reporting for production
error_reporting(0);
ini_set('display_errors', 0);

session_start();
require_once 'vendor/autoload.php';
require_once 'includes/db-connect.php';
require_once 'includes/validation.php'; // Make sure this file includes the getUserInfo function

// Ensure we only output JSON, nothing else
header('Content-Type: application/json');

// Check if user is authenticated
if (!isAuthenticated()) {
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

// Get user info
$userInfo = getUserInfo();
$userEmail = $userInfo['email'];
$classroomService = $userInfo['classroomService'];

// Check if user has Google Classroom access
if ($userInfo['auth_type'] != 'google' || !$classroomService) {
    echo json_encode(['success' => false, 'message' => 'Google Classroom integration is only available for users logged in with Google']);
    exit;
}

// Parse JSON request - use try-catch to handle malformed JSON
try {
    $requestBody = file_get_contents('php://input');
    $request = json_decode($requestBody, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON: ' . json_last_error_msg());
    }
    
    $action = isset($request['action']) ? $request['action'] : '';
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error parsing request: ' . $e->getMessage()]);
    exit;
}

// Handle different actions
try {
    switch ($action) {
        case 'fetch_all_grades':
            fetchAllGrades($userInfo, $classroomService, $db);
            break;
        
        case 'get_course_grades':
            $courseId = isset($request['course_id']) ? $request['course_id'] : '';
            getCourseGrades($userInfo, $courseId, $db);
            break;
        
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            exit;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    exit;
}

/**
 * Function to fetch grades from all enrolled courses
 */
function fetchAllGrades($userInfo, $classroomService, $db) {
    try {
        // Get enrolled courses from database
        $stmt = $db->prepare("SELECT course_id, course_name FROM course_status WHERE user_id = ?");
        $stmt->bind_param("s", $userInfo['email']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $courses = [];
        while ($row = $result->fetch_assoc()) {
            $courses[] = $row;
        }
        
        if (empty($courses)) {
            echo json_encode(['success' => false, 'message' => 'No enrolled courses found']);
            exit;
        }
        
        $totalAssignments = 0;
        
        // Process each course
        foreach ($courses as $course) {
            $courseId = $course['course_id'];
            $courseName = $course['course_name'];
            
            // Fetch course work from Google Classroom
            $courseWorkList = $classroomService->courses_courseWork->listCoursesCourseWork($courseId);
            $courseWorks = $courseWorkList->getCourseWork();
            
            if (empty($courseWorks)) {
                continue;
            }
            
            // Process each assignment
            foreach ($courseWorks as $courseWork) {
                $assignmentId = $courseWork->getId();
                $assignmentTitle = $courseWork->getTitle();
                $maxPoints = $courseWork->getMaxPoints() ?? 0;
                
                // Determine assignment type
                $workType = $courseWork->getWorkType();
                $assignmentType = 'assignment'; // Default
                
                if (stripos($workType, 'quiz') !== false || stripos($assignmentTitle, 'quiz') !== false) {
                    $assignmentType = 'quiz';
                } elseif (stripos($workType, 'exam') !== false || stripos($assignmentTitle, 'exam') !== false) {
                    $assignmentType = 'exam';
                } elseif (stripos($workType, 'lab') !== false || stripos($assignmentTitle, 'lab') !== false) {
                    $assignmentType = 'lab';
                } elseif (stripos($workType, 'task') !== false || stripos($assignmentTitle, 'task') !== false) {
                    $assignmentType = 'task';
                }
                
                // Get student submission
                try {
                    $submissionsList = $classroomService->courses_courseWork_studentSubmissions->listCoursesCourseWorkStudentSubmissions(
                        $courseId, 
                        $assignmentId, 
                        ['userId' => 'me']
                    );
                    
                    $submissions = $submissionsList->getStudentSubmissions();
                    
                    if (!empty($submissions)) {
                        foreach ($submissions as $submission) {
                            $submissionState = $submission->getState();
                            $grade = null;
                            $submittedAt = null;
                            $gradedAt = null;
                            
                            // Get grade if available
                            if ($submission->getAssignedGrade()) {
                                $grade = $submission->getAssignedGrade();
                            }
                            
                            // Get submission timestamp
                            if ($submission->getSubmissionHistory()) {
                                $history = $submission->getSubmissionHistory();
                                foreach ($history as $historyItem) {
                                    if ($historyItem->getStateHistory()) {
                                        $stateHistory = $historyItem->getStateHistory();
                                        if ($stateHistory->getState() === 'TURNED_IN') {
                                            $submittedTimestamp = $stateHistory->getStateTimestamp();
                                            if ($submittedTimestamp) {
                                                $submittedAt = date('Y-m-d H:i:s', strtotime($submittedTimestamp));
                                            }
                                        }
                                    }
                                    
                                    if ($historyItem->getGradeHistory()) {
                                        $gradeHistory = $historyItem->getGradeHistory();
                                        if ($gradeHistory->getGradeTimestamp()) {
                                            $gradedTimestamp = $gradeHistory->getGradeTimestamp();
                                            $gradedAt = date('Y-m-d H:i:s', strtotime($gradedTimestamp));
                                        }
                                    }
                                }
                            }
                            
                            // Save to database
                            $stmt = $db->prepare("INSERT INTO student_grades 
                                (user_id, course_id, course_name, assignment_id, assignment_title, assignment_type, grade, max_points, submission_state, submitted_at, graded_at) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                                ON DUPLICATE KEY UPDATE 
                                course_name = VALUES(course_name),
                                assignment_title = VALUES(assignment_title),
                                assignment_type = VALUES(assignment_type),
                                grade = VALUES(grade),
                                max_points = VALUES(max_points),
                                submission_state = VALUES(submission_state),
                                submitted_at = VALUES(submitted_at),
                                graded_at = VALUES(graded_at)");
                                
                            $stmt->bind_param("ssssssddsss", 
                                $userInfo['email'], 
                                $courseId, 
                                $courseName, 
                                $assignmentId, 
                                $assignmentTitle, 
                                $assignmentType, 
                                $grade, 
                                $maxPoints, 
                                $submissionState, 
                                $submittedAt, 
                                $gradedAt
                            );
                            $stmt->execute();
                            $totalAssignments++;
                        }
                    }
                } catch (Exception $e) {
                    error_log('Error getting submissions: ' . $e->getMessage());
                    // Continue to next assignment
                    continue;
                }
            }
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'Grades fetched successfully', 
            'count' => $totalAssignments
        ]);
        exit;
        
    } catch (Exception $e) {
        error_log('Error fetching grades: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error fetching grades: ' . $e->getMessage()]);
        exit;
    }
}

/**
 * Function to get grades for a specific course
 */
function getCourseGrades($userInfo, $courseId, $db) {
    try {
        // Validate course ID
        if (empty($courseId)) {
            echo json_encode(['success' => false, 'message' => 'Course ID is required']);
            exit;
        }
        
        // Get course name
        $stmt = $db->prepare("SELECT course_name FROM course_status WHERE course_id = ? AND user_id = ? LIMIT 1");
        $stmt->bind_param("ss", $courseId, $userInfo['email']);
        $stmt->execute();
        $result = $stmt->get_result();
        $course = $result->fetch_assoc();
        
        if (!$course) {
            echo json_encode(['success' => false, 'message' => 'Course not found']);
            exit;
        }
        
        // Get grades for this course
        $stmt = $db->prepare("SELECT * FROM student_grades WHERE course_id = ? AND user_id = ? ORDER BY assignment_title");
        $stmt->bind_param("ss", $courseId, $userInfo['email']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $grades = [];
        while ($row = $result->fetch_assoc()) {
            $grades[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'course_name' => $course['course_name'],
            'grades' => $grades
        ]);
        exit;
        
    } catch (Exception $e) {
        error_log('Error getting course grades: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error getting course grades: ' . $e->getMessage()]);
        exit;
    }
}