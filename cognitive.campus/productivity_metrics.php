<?php
/**
 * Productivity Metrics System
 * 
 * Calculates and manages student productivity metrics based on tasks, schedules, and note edit sessions
 */

require_once 'includes/db-connect.php';
require_once 'includes/validation.php';
require_once 'includes/avg_times.php';

class ProductivityMetricsSystem {
    private $db;
    private $userEmail;
    private $currentWeekNumber;
    private $currentYear;
    private $previousWeekNumber;
    private $previousYear;

    /**
     * Constructor - initialize with database connection and user email
     */
    public function __construct($db, $userEmail) {
        $this->db = $db;
        $this->userEmail = $userEmail;
        
        // Determine current week number and year
        $date = new DateTime();
        $this->currentWeekNumber = (int)$date->format('W');
        $this->currentYear = (int)$date->format('Y');
        
        // Calculate previous week and handle year boundary
        $prevDate = clone $date;
        $prevDate->modify('-1 week');
        $this->previousWeekNumber = (int)$prevDate->format('W');
        $this->previousYear = (int)$prevDate->format('Y');
    }
    
    /**
     * Calculate productivity metrics and store in database
     */
    public function calculateAndStoreProductivity() {
        // Get completion rate (schedules + tasks)
        $completionRate = $this->calculateCompletionRate();
        
        // Get note productivity metrics
        $noteProductivity = $this->calculateNoteProductivity();
        
        // Calculate overall score (50% completion rate, 50% note productivity)
        $overallScore = ($completionRate + $noteProductivity) / 2;
        
        // Check if entry already exists for this week
        $stmt = $this->db->prepare("SELECT id FROM user_productivity 
                                   WHERE user_email = ? AND week_number = ? AND year = ?");
        $stmt->bind_param("sii", $this->userEmail, $this->currentWeekNumber, $this->currentYear);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Update existing record
            $row = $result->fetch_assoc();
            $stmt = $this->db->prepare("UPDATE user_productivity 
                                       SET completion_rate = ?, note_productivity = ?, overall_score = ? 
                                       WHERE id = ?");
            $stmt->bind_param("dddi", $completionRate, $noteProductivity, $overallScore, $row['id']);
        } else {
            // Insert new record
            $stmt = $this->db->prepare("INSERT INTO user_productivity 
                                       (user_email, week_number, year, completion_rate, note_productivity, overall_score) 
                                       VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("siiddd", $this->userEmail, $this->currentWeekNumber, $this->currentYear, 
                              $completionRate, $noteProductivity, $overallScore);
        }
        
        $stmt->execute();
        return [
            'completion_rate' => $completionRate,
            'note_productivity' => $noteProductivity,
            'overall_score' => $overallScore
        ];
    }
    
    /**
     * Calculate completion rate based on tasks and schedules
     */
    private function calculateCompletionRate() {
        // Get schedule data from schedule_report
        $scheduleData = $this->getScheduleData();
        
        // Get task completion data
        $taskData = $this->getTaskData();
        
        // Calculate total completion rate
        $totalCompleted = $scheduleData['completed'] + $taskData['completed'];
        $totalItems = $scheduleData['total'] + $taskData['total'];
        
        // Avoid division by zero
        return ($totalItems > 0) ? ($totalCompleted / $totalItems) * 100 : 0;
    }
    
    /**
     * Get schedule data from schedule_report table or directly from schedule table
     */
    private function getScheduleData() {
        // Convert week number and year to date range
        $dates = $this->getWeekDates($this->currentWeekNumber, $this->currentYear);
        $startDate = $dates['start'];
        $endDate = $dates['end'];
        
        // Query for schedule data using schedule_report
        $stmt = $this->db->prepare("SELECT completed_count, incomplete_count 
                                    FROM schedule_reports
                                    WHERE user_email = ? 
                                    AND week_start_date BETWEEN ? AND ?");
        $stmt->bind_param("sss", $this->userEmail, $startDate, $endDate);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $completed = (int)$row['completed_count'];
            $incomplete = (int)$row['incomplete_count'];
            return [
                'completed' => $completed,
                'total' => $completed + $incomplete
            ];
        } else {
            // If no report exists, query directly from schedule table
            return $this->getScheduleDataDirectly();
        }
    }
    
    /**
     * Get schedule data directly from the schedule table
     */
    private function getScheduleDataDirectly() {
        // Convert week number and year to date range
        $dates = $this->getWeekDates($this->currentWeekNumber, $this->currentYear);
        $startDate = $dates['start'];
        $endDate = $dates['end'];
        
        // Get user ID from email
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $this->userEmail);
        $stmt->execute();
        $userResult = $stmt->get_result();
        
        if ($userResult->num_rows === 0) {
            return ['completed' => 0, 'total' => 0];
        }
        
        $userRow = $userResult->fetch_assoc();
        $userId = $userRow['id'];
        
        // Query for completed schedules
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM schedules
                                   WHERE user_id = ? 
                                   AND day BETWEEN ? AND ?
                                   AND is_completed = 'completed'");
        $stmt->bind_param("iss", $userId, $startDate, $endDate);
        $stmt->execute();
        $completedResult = $stmt->get_result();
        $completedRow = $completedResult->fetch_assoc();
        $completed = (int)$completedRow['count'];
        
        // Query for total schedules
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM schedules 
                                   WHERE user_id = ? 
                                   AND day BETWEEN ? AND ?");
        $stmt->bind_param("iss", $userId, $startDate, $endDate);
        $stmt->execute();
        $totalResult = $stmt->get_result();
        $totalRow = $totalResult->fetch_assoc();
        $total = (int)$totalRow['count'];
        
        return [
            'completed' => $completed,
            'total' => $total
        ];
    }
    
    /**
     * Get task data for the current week
     */
    private function getTaskData() {
        // Convert week number and year to date range
        $dates = $this->getWeekDates($this->currentWeekNumber, $this->currentYear);
        $startDate = $dates['start'];
        $endDate = $dates['end'];
        
        // Query for completed tasks
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM tasks 
                                   WHERE userEmail = ? 
                                   AND due_date BETWEEN ? AND ?
                                   AND is_completed = 'completed'");
        $stmt->bind_param("sss", $this->userEmail, $startDate, $endDate);
        $stmt->execute();
        $completedResult = $stmt->get_result();
        $completedRow = $completedResult->fetch_assoc();
        $completed = (int)$completedRow['count'];
        
        // Query for total tasks
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM tasks 
                                   WHERE userEmail = ? 
                                   AND due_date BETWEEN ? AND ?");
        $stmt->bind_param("sss", $this->userEmail, $startDate, $endDate);
        $stmt->execute();
        $totalResult = $stmt->get_result();
        $totalRow = $totalResult->fetch_assoc();
        $total = (int)$totalRow['count'];
        
        return [
            'completed' => $completed,
            'total' => $total
        ];
    }
    
    /**
     * Calculate note productivity metrics
     */
    private function calculateNoteProductivity() {
        global $averageTimes;
        
        // Get total edit session time for the current week
        $currentWeekTime = $this->getTotalEditSessionTime($this->currentWeekNumber, $this->currentYear);
        
        // Get total edit session time for the previous week
        $previousWeekTime = $this->getTotalEditSessionTime($this->previousWeekNumber, $this->previousYear);
        
        // Get expected study time based on averages
        $expectedNoteTime = $this->getExpectedStudyTime();
        
        // Calculate productivity factors
        
        // 1. Time utilization factor (total time compared to expected time)
        $timeUtilizationFactor = ($expectedNoteTime > 0) ? $currentWeekTime / $expectedNoteTime : 0;
        // Cap at reasonable values
        $timeUtilizationFactor = min(1.5, max(0, $timeUtilizationFactor));
        
        // 2. Progress factor (improvement from previous week)
        $progressFactor = 1.0; // Default is neutral
        if ($previousWeekTime > 0) {
            $progressFactor = $currentWeekTime / $previousWeekTime;
            // Cap at reasonable range
            $progressFactor = min(1.5, max(0.5, $progressFactor));
        }
        
        // Calculate final note productivity score (normalized to 0-100)
        $noteProductivity = (($timeUtilizationFactor * 0.6) + ($progressFactor * 0.4)) * 100;
        $noteProductivity = min(100, max(0, $noteProductivity));
        
        return $noteProductivity;
    }
    
    /**
     * Get total edit session time for a specific week
     */
    private function getTotalEditSessionTime($weekNumber, $year) {
        $stmt = $this->db->prepare("SELECT SUM(duration_seconds) as total_time 
                                   FROM edit_sessions 
                                   WHERE userEmail = ? 
                                   AND week_number = ? 
                                   AND year = ?");
        $stmt->bind_param("sii", $this->userEmail, $weekNumber, $year);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return ($row['total_time'] !== null) ? (float)$row['total_time'] : 0;
        }
        
        return 0;
    }
    
    /**
     * Get expected study time based on average times
     */
    private function getExpectedStudyTime() {
        global $averageTimes;
        
        // Get active notes count for the current week
        $stmt = $this->db->prepare("SELECT COUNT(DISTINCT note_id) as note_count 
                                   FROM edit_sessions 
                                   WHERE userEmail = ? 
                                   AND week_number = ? 
                                   AND year = ?");
        $stmt->bind_param("sii", $this->userEmail, $this->currentWeekNumber, $this->currentYear);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $noteCount = 0;
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $noteCount = (int)$row['note_count'];
        }
        
        // Expected time per note: post-class review + weekly review
        $expectedTimePerNote = $averageTimes['note_post_class'] + $averageTimes['note_weekly_review'];
        
        // Convert from minutes to seconds
        return $noteCount * $expectedTimePerNote * 60;
    }
    
    /**
     * Get weekly comparison for dashboard display
     */
    public function getWeeklyComparison() {
        // Get current week productivity data
        $currentWeekData = $this->getWeekProductivityData($this->currentWeekNumber, $this->currentYear);
        
        // Get previous week productivity data
        $previousWeekData = $this->getWeekProductivityData($this->previousWeekNumber, $this->previousYear);
        
        // Calculate changes
        $completionRateChange = $currentWeekData['completion_rate'] - $previousWeekData['completion_rate'];
        $noteProductivityChange = $currentWeekData['note_productivity'] - $previousWeekData['note_productivity'];
        $overallScoreChange = $currentWeekData['overall_score'] - $previousWeekData['overall_score'];
        
        return [
            'current_week' => $currentWeekData,
            'previous_week' => $previousWeekData,
            'changes' => [
                'completion_rate' => $completionRateChange,
                'note_productivity' => $noteProductivityChange,
                'overall_score' => $overallScoreChange
            ]
        ];
    }
    
    /**
     * Get productivity data for a specific week
     */
    private function getWeekProductivityData($weekNumber, $year) {
        $stmt = $this->db->prepare("SELECT completion_rate, note_productivity, overall_score 
                                   FROM user_productivity 
                                   WHERE user_email = ? 
                                   AND week_number = ? 
                                   AND year = ?");
        $stmt->bind_param("sii", $this->userEmail, $weekNumber, $year);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return [
                'completion_rate' => (float)$row['completion_rate'],
                'note_productivity' => (float)$row['note_productivity'],
                'overall_score' => (float)$row['overall_score']
            ];
        }
        
        // Default values if no data exists
        return [
            'completion_rate' => 0,
            'note_productivity' => 0,
            'overall_score' => 0
        ];
    }
    
    /**
     * Get note edit time details
     */
    public function getNoteEditDetails() {
        // Get current week edit sessions by note
        $currentWeekSessions = $this->getEditSessionsByNote($this->currentWeekNumber, $this->currentYear);
        
        // Get previous week edit sessions by note
        $previousWeekSessions = $this->getEditSessionsByNote($this->previousWeekNumber, $this->previousYear);
        
        // Calculate total times
        $currentWeekTotal = array_sum(array_column($currentWeekSessions, 'total_time'));
        $previousWeekTotal = array_sum(array_column($previousWeekSessions, 'total_time'));
        
        return [
            'current_week' => [
                'sessions' => $currentWeekSessions,
                'total_time' => $currentWeekTotal
            ],
            'previous_week' => [
                'sessions' => $previousWeekSessions,
                'total_time' => $previousWeekTotal
            ]
        ];
    }
    
    /**
     * Get edit sessions grouped by note for a specific week
     */
    private function getEditSessionsByNote($weekNumber, $year) {
        $stmt = $this->db->prepare("SELECT note_id, SUM(duration_seconds) as total_time 
                                   FROM edit_sessions 
                                   WHERE userEmail = ? 
                                   AND week_number = ? 
                                   AND year = ? 
                                   GROUP BY note_id");
        $stmt->bind_param("sii", $this->userEmail, $weekNumber, $year);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $sessions = [];
        while ($row = $result->fetch_assoc()) {
            $sessions[] = [
                'note_id' => $row['note_id'],
                'total_time' => (float)$row['total_time']
            ];
        }
        
        return $sessions;
    }
    
    /**
     * Get start and end dates for a specific week and year
     */
    private function getWeekDates($weekNumber, $year) {
        $dateTime = new DateTime();
        $dateTime->setISODate($year, $weekNumber);
        $startDate = $dateTime->format('Y-m-d');
        
        $dateTime->modify('+6 days');
        $endDate = $dateTime->format('Y-m-d');
        
        return [
            'start' => $startDate,
            'end' => $endDate
        ];
    }
}
?>