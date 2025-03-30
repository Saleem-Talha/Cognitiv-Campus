<?php
// deadlines.php - The main component file
require_once 'includes/validation.php';

if (!isAuthenticated()) {
    header('Location: index.php');
    exit();
}
?>
<div class="col-lg-4 col-md-6 col-sm-12 mb-3">
<div class="card h-100">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Upcoming Uni Deadlines</h5>
    </div>
    
    <div class="card-body">
        <ul class="nav nav-pills mb-4" role="tablist">
            <li class="nav-item">
                <button 
                    class="nav-link active d-flex align-items-center justify-content-center" 
                    data-bs-toggle="tab" 
                    data-bs-target="#assignments" 
                    role="tab"
                    aria-selected="true">
                    <i class="bx bx-book-content me-1"></i>
                    Assignments
                    <span class="badge rounded-pill bg-primary ms-2" id="assignments-count">-</span>
                </button>
            </li>
            <li class="nav-item">
                <button 
                    class="nav-link d-flex align-items-center justify-content-center" 
                    data-bs-toggle="tab" 
                    data-bs-target="#quizzes" 
                    role="tab"
                    aria-selected="false">
                    <i class="bx bx-task me-1"></i>
                    Quizzes
                    <span class="badge rounded-pill bg-primary ms-2" id="quizzes-count">-</span>
                </button>
            </li>
        </ul>

        <div class="tab-content p-0">
            <div class="tab-pane fade show active" id="assignments" role="tabpanel">
                <div class="text-center text-muted py-4" id="assignments-loading">
                    <div class="spinner-border spinner-border-sm me-2" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    Loading assignments...
                </div>
            </div>
            
            <div class="tab-pane fade" id="quizzes" role="tabpanel">
                <div class="text-center text-muted py-4" id="quizzes-loading">
                    <div class="spinner-border spinner-border-sm me-2" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    Loading quizzes...
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadDeadlines();
});

function loadDeadlines() {
    fetch('dashboard-get-deadlines.php')
        .then(response => response.json())
        .then(data => {
            updateDeadlinesUI(data);
        })
        .catch(error => {
            console.error('Error loading deadlines:', error);
            document.querySelectorAll('[id$="-loading"]').forEach(el => {
                el.innerHTML = `
                    <div class="alert alert-danger mb-0" role="alert">
                        <i class="bx bx-error-circle me-1"></i>
                        Error loading deadlines. Please try again later.
                    </div>
                `;
            });
        });
}

function updateDeadlinesUI(data) {
    // Update counts
    document.getElementById('assignments-count').textContent = data.assignments.length;
    document.getElementById('quizzes-count').textContent = data.quizzes.length;

    // Update assignments list
    const assignmentsTab = document.getElementById('assignments');
    assignmentsTab.innerHTML = renderDeadlinesList(data.assignments, false);

    // Update quizzes list
    const quizzesTab = document.getElementById('quizzes');
    quizzesTab.innerHTML = renderDeadlinesList(data.quizzes, true);
}

function renderDeadlinesList(items, isQuiz) {
    if (items.length === 0) {
        return `
            <div class="text-center py-2">
                <div class="text-muted">
                    <i class="bx bx-calendar-x mb-2 fs-3"></i>
                    <p>No ${isQuiz ? 'quizzes' : 'assignments'} due at this time.</p>
                </div>
            </div>
        `;
    }

    return items.map(item => `
        <div class="card border-${isQuiz ? 'info' : 'primary'} shadow-none mb-3">
            <div class="card-body">
                <div class="d-flex flex-column">
                    <h6 class="mb-1">${escapeHtml(item.title)}</h6>
                    <span class="text-muted mb-2 small">${escapeHtml(item.course)}</span>
                    <span class="text-muted small">
                        <i class="bx bx-time-five me-1"></i>
                        Due: ${item.dueDateFormatted}
                    </span>
                </div>
            </div>
        </div>
    `).join('');
}

function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}
</script>