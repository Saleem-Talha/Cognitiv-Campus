<?php
// grades.php - The main component file
require_once 'includes/validation.php';

if (!isAuthenticated()) {
    header('Location: index.php');
    exit();
}
?>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<div class="col-12 pb-2">
    <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">My Grades</h5>
        </div>
        
        <div class="card-body">
            <div id="grades-loading" class="text-center text-muted py-4">
                <div class="spinner-border spinner-border-sm me-2" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                Loading grades...
            </div>

            <div id="grades-content" class="d-none">
                <div class="row">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white mb-3">
                            <div class="card-body">
                                <h6 class="card-title text-white">Overall Average</h6>
                                <h3 class="card-text text-white" id="overall-average">-</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8 position-relative">
                        <div class="course-slider-container">
                            <div id="course-grades-container" class="course-slider"></div>
                            <div class="slider-navigation">
                                <button id="prev-course" class="btn btn-outline-primary btn-sm slider-nav-btn slider-nav-prev" style="display: none;">
                                    <i class="bx bx-chevron-left"></i>
                                </button>
                                <button id="next-course" class="btn btn-outline-primary btn-sm slider-nav-btn slider-nav-next">
                                    <i class="bx bx-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="heatmap-chart" class="mt-4" style="height: 400px;"></div>
        </div>
    </div>
    

</div>

<style>
#heatmap-chart .apexcharts-tooltip {
    background-color: #f8f9fa; /* Matches Sneat's light theme */
    border-color: #dee2e6;
    color: #212529;
}


.course-slider-container {
    position: relative;
}

.course-slider {
    display: flex;
    transition: transform 0.3s ease;
    gap: 15px;
    overflow: hidden;
}

.course-slider-item {
    flex: 0 0 calc(50% - 7.5px);
    max-width: calc(50% - 7.5px);
}

.slider-navigation {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 100%;
    display: flex;
    justify-content: space-between;
    pointer-events: none;
}

.slider-nav-btn {
    pointer-events: auto;
    z-index: 10;
}

.apexcharts-tooltip-course {
    background-color: #f8f9fa; /* Matches Sneat's light theme */
    border: 1px solid #dee2e6;
    color: #212529;
    padding: 10px;
    border-radius: 5px;
}

</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadGrades();
});

function renderHeatmap(data) {
    const courses = data.courses.map(course => course.courseName);
    const assignments = data.courses.map(course => course.assignments.length);
    const averages = data.courses.map(course => course.courseAverage || 0);

    // Group assignments by type for each course
    const assignmentTypeData = [];
    data.courses.forEach(course => {
        // Count assignments by type
        const typeCount = {};
        course.assignments.forEach(assignment => {
            const type = assignment.type || 'unknown';
            typeCount[type] = (typeCount[type] || 0) + 1;
        });
        
        // Add to data series
        Object.keys(typeCount).forEach(type => {
            assignmentTypeData.push({
                courseName: course.courseName,
                type: type,
                count: typeCount[type]
            });
        });
    });

    const options = {
        chart: {
            type: 'heatmap',
            height: 400,
        },
        series: [
            {
                name: 'Assignments',
                data: assignments.map((count, index) => ({ x: index + 1, y: count })),
            },
            {
                name: 'Grades',
                data: averages.map((avg, index) => ({ x: index + 1, y: avg })),
            },
        ],
        plotOptions: {
            heatmap: {
                colorScale: {
                    ranges: [
                        {
                            from: 0,
                            to: 50,
                            color: '#696cff', // Light blue for assignments with 0-10
                        },
                    ],
                },
            },
        },
        xaxis: {
            type: 'category',
            labels: {
                show: false, // Hide x-axis labels
            },
            tooltip: {
                enabled: false, // Disable default tooltip
            },
        },
        yaxis: {
            title: {
                text: 'Values',
            },
        },
        colors: ['#696cff', '#e7e7ff'], // Default fallback for heatmap colors
        title: {
            text: 'Student Progress Analytics Heatmap',
            align: 'center',
        },
        tooltip: {
            custom: function({ series, seriesIndex, dataPointIndex, w }) {
                const courseName = courses[dataPointIndex];
                const value = series[seriesIndex][dataPointIndex];
                const metric = seriesIndex === 0 ? 'Assignments' : 'Average Grade';
                return `<div class="apexcharts-tooltip-course">
                            <strong>${courseName}</strong><br>
                            ${metric}: ${value}
                        </div>`;
            },
        },
    };

    const chart = new ApexCharts(document.querySelector('#heatmap-chart'), options);
    chart.render();
}




let coursesData = null;
let currentSliderIndex = 0;

function loadGrades() {
    fetch('dashboard-get-grades.php')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            coursesData = data;
            updateGradesUI(data);
            renderHeatmap(data);
        })
        .catch(error => {
            console.error('Error loading grades:', error);
            document.getElementById('grades-loading').innerHTML = `
                <div class="alert alert-danger mb-0" role="alert">
                    <i class="bx bx-error-circle me-1"></i>
                    Error loading grades: ${error.message}. Please try again later.
                </div>
            `;
        });
}

function updateGradesUI(data) {
    const gradesLoading = document.getElementById('grades-loading');
    const gradesContent = document.getElementById('grades-content');
    const overallAverage = document.getElementById('overall-average');
    const courseGradesContainer = document.getElementById('course-grades-container');
    const prevBtn = document.getElementById('prev-course');
    const nextBtn = document.getElementById('next-course');
    // Hide loading, show content
    gradesLoading.classList.add('d-none');
    gradesContent.classList.remove('d-none');
    


    // Update overall average
    overallAverage.textContent = data.totalGrades.overallAverage !== null 
        ? `${data.totalGrades.overallAverage}%` 
        : 'N/A';

    // Render course grades
    if (data.courses.length === 0) {
        courseGradesContainer.innerHTML = `
            <div class="alert alert-primary mb-0" role="alert">
                <i class="bx bx-primary-circle me-1"></i>
                No grades available at this time.
            </div>
        `;
        prevBtn.style.display = 'none';
        nextBtn.style.display = 'none';
        return;
    }

    // Render slider
    function renderCourseSlider() {
        // Calculate start and end indices for current view
        const startIndex = currentSliderIndex * 2;
        const endIndex = startIndex + 2;
        const coursesToShow = data.courses.slice(startIndex, endIndex);

        // Render visible courses
        courseGradesContainer.innerHTML = coursesToShow.map((course, index) => `
            <div class="card mb-3 course-slider-item">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">${escapeHtml(course.courseName)}</h6>
                        <span class="badge bg-primary">
                            ${course.courseAverage !== null ? `${course.courseAverage}%` : 'N/A'}
                        </span>
                    </div>
                    <button class="btn btn-sm btn-outline-primary float-end" data-bs-toggle="modal" data-bs-target="#courseModal${startIndex + index}">
                        View Details
                    </button>
                </div>
            </div>
            <!-- Modal - using original index -->
            <div class="modal fade" id="courseModal${startIndex + index}" tabindex="-1" aria-labelledby="courseModalLabel${startIndex + index}" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="courseModalLabel${startIndex + index}">
                                ${escapeHtml(course.courseName)} - Assignments
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="assignments-scroll" style="max-height: 200px; overflow-y: auto;">
                                ${course.assignments.length > 0 ? `
                                    <div class="list-group list-group-flush">
                                        ${course.assignments.map(assignment => `
                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                <div>
                                                    ${escapeHtml(assignment.title)}
                                                    <small class="text-muted d-block">
                                                        ${assignment.obtainedGrade} / ${assignment.maxPoints} points
                                                    </small>
                                                </div>
                                                <span class="badge bg-primary">${assignment.grade}%</span>
                                            </div>
                                        `).join('')}
                                    </div>
                                ` : '<p class="text-muted mb-0 small">No graded assignments</p>'}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');

        // Update navigation buttons
        prevBtn.style.display = currentSliderIndex > 0 ? 'block' : 'none';
        nextBtn.style.display = (currentSliderIndex + 1) * 2 < data.courses.length ? 'block' : 'none';
    }

    // Initial render
    renderCourseSlider();

    // Navigation event listeners
    document.getElementById('prev-course').addEventListener('click', () => {
        if (currentSliderIndex > 0) {
            currentSliderIndex--;
            renderCourseSlider();
        }
    });

    document.getElementById('next-course').addEventListener('click', () => {
        if ((currentSliderIndex + 1) * 2 < data.courses.length) {
            currentSliderIndex++;
            renderCourseSlider();
        }
    });
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