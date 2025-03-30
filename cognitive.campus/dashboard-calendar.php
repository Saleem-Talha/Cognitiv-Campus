<?php
require_once 'includes/validation.php';
require_once 'includes/header.php';

if (!isAuthenticated()) {
    header('Location: index.php');
    exit();
}

$userInfo = getUserInfo();
$userEmail = $userInfo['email'];

$events = [];
$errors = [];

if (!$db->ping()) {
    error_log('Database connection failed');
    $errors[] = "Database connection failed";
}

try {
    // Tasks
    $stmt = $db->prepare("SELECT id, title, description, due_date FROM tasks WHERE userEmail = ?");
    if (!$stmt) {
        throw new Exception("Failed to prepare tasks query: " . $db->error);
    }
    $stmt->bind_param("s", $userEmail);
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute tasks query: " . $stmt->error);
    }
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $due_date = new DateTime($row['due_date']);
        $events[] = [
            'id' => 'task_' . $row['id'],
            'title' => htmlspecialchars($row['title']), // Only show title
            'start' => $due_date->format('Y-m-d\TH:i:s'),
            'description' => htmlspecialchars($row['description']),
            'type' => 'task',
            'displayData' => [
                'Due Date' => $due_date->format('Y-m-d'),
                'Description' => htmlspecialchars($row['description'])
            ],
            'backgroundColor' => '#fff',
            'borderColor' => '#696cff',
            'textColor' => '#696cff',
            'allDay' => true
        ];
    }

    // Course status
    $stmt = $db->prepare("SELECT course_id, course_name, created_at, updated_at, status FROM course_status WHERE user_id IN (SELECT id FROM users WHERE email = ?)");
    if (!$stmt) {
        throw new Exception("Failed to prepare course status query: " . $db->error);
    }
    $stmt->bind_param("s", $userEmail);
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute course status query: " . $stmt->error);
    }
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $created_date = new DateTime($row['created_at']);
        $events[] = [
            'id' => 'course_' . $row['course_id'],
            'title' => htmlspecialchars($row['course_name']), // Only show name
            'start' => $created_date->format('Y-m-d\TH:i:s'),
            'type' => 'course',
            'displayData' => [
                'Created Date' => $created_date->format('Y-m-d'),
                'Status' => htmlspecialchars($row['status']),
                'Last Updated' => $row['updated_at'] ? (new DateTime($row['updated_at']))->format('Y-m-d') : 'N/A'
            ],
            'backgroundColor' => '#fff',
            'borderColor' => '#696cff',
            'textColor' => '#696cff',
            'allDay' => true
        ];
    }

    // Projects
    $stmt = $db->prepare("SELECT id, name, start_date, end_date, status, courseType FROM projects WHERE ownerEmail = ?");
    if (!$stmt) {
        throw new Exception("Failed to prepare project query: " . $db->error);
    }
    $stmt->bind_param("s", $userEmail);
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute project query: " . $stmt->error);
    }
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $start_date = new DateTime($row['start_date']);
        $events[] = [
            'id' => 'project_' . $row['id'],
            'title' => htmlspecialchars($row['name']), // Only show name
            'start' => $start_date->format('Y-m-d\TH:i:s'),
            'type' => 'project',
            'displayData' => [
                'Start Date' => $start_date->format('Y-m-d'),
                'End Date' => $row['end_date'] ? (new DateTime($row['end_date']))->format('Y-m-d') : 'N/A',
                'Type' => htmlspecialchars($row['courseType']),
                'Status' => htmlspecialchars($row['status'])
            ],
            'backgroundColor' => '#fff',
            'borderColor' => '#696cff',
            'textColor' => '#696cff',
            'allDay' => true
        ];
    }

    // Notes
    $stmt = $db->prepare("SELECT 'course' as note_type, id, page_title, datetime, content, courseId 
                         FROM notes_course WHERE userEmail = ? 
                         UNION ALL 
                         SELECT 'project' as note_type, id, page_title, datetime, content, project_id 
                         FROM notes_project WHERE userEmail = ?");
    if (!$stmt) {
        throw new Exception("Failed to prepare notes query: " . $db->error);
    }
    $stmt->bind_param("ss", $userEmail, $userEmail);
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute notes query: " . $stmt->error);
    }
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $note_date = new DateTime($row['datetime']);
        $events[] = [
            'id' => 'note_' . $row['note_type'] . '_' . $row['id'],
            'title' => htmlspecialchars($row['page_title']), // Only show title
            'start' => $note_date->format('Y-m-d\TH:i:s'),
            'type' => 'note',
            'displayData' => [
                'Date' => $note_date->format('Y-m-d'),
                'Type' => ucfirst($row['note_type']) . ' Note',
                
            ],
            'backgroundColor' => '#fff',
            'borderColor' => '#696cff',
            'textColor' => '#696cff',
            'allDay' => true
        ];
    }

    error_log('Number of events loaded: ' . count($events));

} catch (Exception $e) {
    error_log('Calendar Data Error: ' . $e->getMessage());
    $errors[] = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Calendar</title>

       
    

    <style>
        .fc-event {
            cursor: pointer;
            margin: 2px 0;
            padding: 6px 10px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
            border: 2px solid #696cff; /* Border color */
            background-color: #fff; /* White background */
            color: #696cff; /* Text color matches the border */
        }

        .fc-event:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }


    .fc-event {
        position: relative;
    }

    .fc-event:hover::after {
        content: attr(title);
        position: absolute;
        background: #000;
        color: #fff;
        padding: 5px 10px;
        border-radius: 5px;
        white-space: nowrap;
        top: -30px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 10;
        font-size: 0.9rem;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }



        .fc-event-icon {
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #696cff; /* Icon matches the border and text */
        }

        .fc-event-title {
            white-space: normal;
            overflow: hidden;
            text-overflow: ellipsis;
            font-weight: 600;
            font-size: 0.9rem;
        }


        .calendar-legend {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }
        .legend-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .legend-color {
            width: 15px;
            height: 15px;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <!-- Content wrapper -->
    <div class="content-wrapper">
        <!-- Content -->
        
            <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <strong>Error loading calendar data:</strong>
                <ul>
                    <?php foreach($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <div class="row mb-3">
                <div class="col-lg-8 col-md-12 col-sm-12 mb-4">
                    <div class="card">
                        <div class="card-header mt-2">
                            <h5 class="mb-0">Activity Calendar</h5>
                            
                            <div class="calendar-legend mt-3">
                                <div class="legend-item">
                                    <i class="fas fa-check-circle" style="color: #696cff;"></i>
                                    <span>Tasks</span>
                                </div>
                                <div class="legend-item">
                                    <i class="fas fa-graduation-cap" style="color: #696cff;"></i>
                                    <span>Courses</span>
                                </div>
                                <div class="legend-item">
                                    <i class="fas fa-project-diagram" style="color: #696cff;"></i>
                                    <span>Projects</span>
                                </div>
                                <div class="legend-item">
                                    <i class="fas fa-sticky-note" style="color: #696cff;"></i>
                                    <span>Notes</span>
                                </div>
                            </div>

                            
                            
                        </div>
                        <div class="card-body">
                            <div id="calendar"></div>
                        </div>
                    </div>
                   
                </div>
                <?php include("dashboard-upload-tables.php");?>
            </div>
        
    </div>

   
    <script>
       document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var events = <?php echo json_encode($events, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
    
    console.log('Calendar events:', events);
    
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth'
        },
        events: events,
        displayEventTime: false,
        eventDidMount: function(info) {
    const { type, displayData } = info.event.extendedProps;

    // Map event types to icons
    const typeIcons = {
        task: '<i class="fas fa-check-circle"></i>', // Task icon
        course: '<i class="fas fa-graduation-cap"></i>', // Course icon
        project: '<i class="fas fa-project-diagram"></i>', // Project icon
        note: '<i class="fas fa-sticky-note"></i>' // Note icon
    };

    // Add icon and type, hide title
    const typeLabel = type.charAt(0).toUpperCase() + type.slice(1); // Capitalize type
    const iconHtml = `<span class="fc-event-icon">${typeIcons[type] || ''}</span> <span>${typeLabel}</span>`;
    info.el.innerHTML = iconHtml;

    // Add hover tooltip for the event title
    const hoverTitle = info.event.title || displayData['Type'] || 'Event';
    info.el.setAttribute('title', hoverTitle);
},



        eventClick: function(info) {
            // Create standardized modal content
            var modalContent = `
                <div class="modal fade" id="eventModal" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content shadow-lg border-0">
                            <div class="modal-header text-primary">
                                <h5 class="modal-title">
                                    <i class="fas ${info.event.extendedProps.type === 'task' ? 'fa-check-circle' : 
                                                   info.event.extendedProps.type === 'course' ? 'fa-graduation-cap' :
                                                   info.event.extendedProps.type === 'project' ? 'fa-project-diagram' : 
                                                   'fa-sticky-note'} me-2"></i>
                                    ${info.event.title}
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body p-4">
                                <div class="card mb-3 bg-light">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-3">
                                            <span class="badge bg-primary me-2">
                                                ${capitalizeFirstLetter(info.event.extendedProps.type)}
                                            </span>
                                        </div>
                                        ${Object.entries(info.event.extendedProps.displayData)
                                            .map(([key, value]) => `
                                                <div class="mb-3">
                                                    <h6 class="text-muted mb-2">${key}</h6>
                                                    ${key.toLowerCase().includes('content') 
                                                        ? `<div class="p-3 bg-white rounded">${value}</div>`
                                                        : `<p class="mb-0">${value}</p>`}
                                                </div>
                                            `).join('')}
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer border-0">
                                <button type="button" class="btn btn-primary px-4" data-bs-dismiss="modal">
                                    <i class="fas fa-times me-2"></i>Close
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Remove existing modal if any
            var existingModal = document.getElementById('eventModal');
            if (existingModal) {
                existingModal.remove();
            }
            
            // Add new modal to body
            document.body.insertAdjacentHTML('beforeend', modalContent);
            
            // Show the modal
            var eventModal = new bootstrap.Modal(document.getElementById('eventModal'));
            eventModal.show();
        },
        loading: function(isLoading) {
            if (!isLoading) {
                console.log('Calendar finished loading');
                if (events.length === 0) {
                    console.log('No events found');
                    const calendar = document.getElementById('calendar');
                    const existingAlert = calendar.parentNode.querySelector('.alert');
                    if (existingAlert) {
                        existingAlert.remove();
                    }
                    const alert = document.createElement('div');
                    alert.className = 'alert alert-info mt-3';
                    alert.textContent = 'No events found for the selected period.';
                    calendar.parentNode.insertBefore(alert, calendar.nextSibling);
                }
            }
        }
    });

    calendar.render();

    // Helper function to capitalize first letter
    function capitalizeFirstLetter(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }

    // Filter functionality
    document.getElementById('showTasks').addEventListener('change', function() {
        toggleEvents('task', this.checked);
    });
    
    document.getElementById('showCourses').addEventListener('change', function() {
        toggleEvents('course', this.checked);
    });
    
    document.getElementById('showProjects').addEventListener('change', function() {
        toggleEvents('project', this.checked);
    });
    
    document.getElementById('showNotes').addEventListener('change', function() {
        toggleEvents('note', this.checked);
    });

    function toggleEvents(type, show) {
        var events = calendar.getEvents();
        events.forEach(function(event) {
            if (event.extendedProps.type === type) {
                if (show) {
                    event.setProp('display', 'auto');
                } else {
                    event.setProp('display', 'none');
                }
            }
        });
    }

    // Error handling
    window.onerror = function(msg, url, lineNo, columnNo, error) {
        console.error('Calendar Error:', {
            message: msg,
            url: url,
            lineNo: lineNo,
            columnNo: columnNo,
            error: error
        });
        
        const calendar = document.getElementById('calendar');
        const errorAlert = document.createElement('div');
        errorAlert.className = 'alert alert-danger mt-3';
        errorAlert.textContent = 'An error occurred while loading the calendar. Please check the console for details.';
        calendar.parentNode.insertBefore(errorAlert, calendar.nextSibling);
        
        return false;
    };

    // Responsive handling
    window.addEventListener('resize', function() {
        calendar.updateSize();
    });

    console.log('Calendar initialized successfully');
});
    </script>
</body>
</html>