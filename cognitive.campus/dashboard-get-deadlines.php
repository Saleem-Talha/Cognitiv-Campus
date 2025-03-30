<?php
// get-deadlines.php
header('Content-Type: application/json');

require_once 'includes/validation.php';

if (!isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit();
}

$userInfo = getUserInfo();

// If user logged in via session and doesn't have Google Classroom access
if ($userInfo['auth_type'] === 'session' && $userInfo['classroomService'] === null) {
    echo json_encode([
        'assignments' => [],
        'quizzes' => []
    ]);
    exit();
}

$classroomService = $userInfo['classroomService'];

function getUpcomingDeadlines($classroomService) {
    try {
        $assignments = [];
        $quizzes = [];
        
        $courses = $classroomService->courses->listCourses(['courseStates' => 'ACTIVE'])->getCourses();
        
        if ($courses) {
            foreach ($courses as $course) {
                $courseWork = $classroomService->courses_courseWork->listCoursesCourseWork(
                    $course->getId()
                )->getCourseWork();
                
                if ($courseWork) {
                    foreach ($courseWork as $work) {
                        $dueDate = $work->getDueDate();
                        if ($dueDate) {
                            $dueDateTime = new DateTime(
                                $dueDate->getYear() . '-' . 
                                $dueDate->getMonth() . '-' . 
                                $dueDate->getDay()
                            );
                            
                            if ($work->getDueTime()) {
                                $dueDateTime->setTime(
                                    $work->getDueTime()->getHours(),
                                    $work->getDueTime()->getMinutes()
                                );
                            }
                            
                            if ($dueDateTime > new DateTime()) {
                                $item = [
                                    'title' => $work->getTitle(),
                                    'course' => $course->getName(),
                                    'dueDateFormatted' => $dueDateTime->format('M j, g:i A'),
                                    'id' => $work->getId(),
                                    'courseId' => $course->getId()
                                ];
                                
                                if (stripos($work->getTitle(), 'quiz') !== false) {
                                    $quizzes[] = $item;
                                } else {
                                    $assignments[] = $item;
                                }
                            }
                        }
                    }
                }
            }
        }
        
        $sortByDate = function($a, $b) {
            return strtotime($a['dueDateFormatted']) - strtotime($b['dueDateFormatted']);
        };
        
        usort($assignments, $sortByDate);
        usort($quizzes, $sortByDate);
        
        return [
            'assignments' => $assignments,
            'quizzes' => $quizzes
        ];
        
    } catch (Exception $e) {
        error_log('Error fetching deadlines: ' . $e->getMessage());
        return ['assignments' => [], 'quizzes' => []];
    }
}

echo json_encode(getUpcomingDeadlines($classroomService));
?>