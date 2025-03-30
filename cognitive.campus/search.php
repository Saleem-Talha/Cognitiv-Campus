<?php
// search.php - Main page
require_once 'includes/validation.php';
require_once 'includes/db-connect.php';

// Fetch logged-in user email
$userInfo = getUserInfo();
$userEmail = $userInfo['email'] ?? null;

// Redirect if the user is not logged in
if (!$userEmail) {
    header('Location: index.php');
    exit();
}

// Get search query
$searchQuery = isset($_POST['search']) ? trim($_POST['search']) : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/header.php'; ?>
    <title>Search Results</title>
    <style>
        .search-item {
            transition: background-color 0.2s;
            text-decoration: none;
            color: inherit;
        }
        .search-item:hover {
            background-color: rgba(0, 0, 0, 0.05);
            text-decoration: none;
            color: inherit;
        }
        .result-type {
            font-size: 0.8rem;
            padding: 0.2rem 0.6rem;
            border-radius: 1rem;
            text-transform: capitalize;
        }
        .type-notes { background-color: #e3f2fd; color: #1565c0; }
        .type-course { background-color: #f3e5f5; color: #7b1fa2; }
        .type-project { background-color: #e8f5e9; color: #2e7d32; }
    </style>
</head>
<body>
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        <?php include_once('includes/sidebar-main.php'); ?>
        <div class="layout-page">
            <?php include_once('includes/navbar.php'); ?>

            <div class="container-xxl flex-grow-1 container-p-y">
                <h4 class="fw-bold mb-4">
                    <span class="text-muted fw-light">Search /</span> 
                    Results for "<?php echo htmlspecialchars($searchQuery); ?>"
                </h4>

                <div id="search-results">
                    <div class="card">
                        <div class="card-body text-center py-5" id="loading-state">
                            <div class="spinner-border text-primary mb-3" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <h5>Searching...</h5>
                            <p class="text-muted">Please wait while we fetch your results</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadSearchResults();
});

function loadSearchResults() {
    const searchQuery = <?php echo json_encode($searchQuery); ?>;
    
    fetch('search-get-results.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ search: searchQuery })
    })
    .then(response => response.json())
    .then(data => {
        updateSearchResults(data);
    })
    .catch(error => {
        console.error('Error loading search results:', error);
        document.getElementById('search-results').innerHTML = `
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bx bx-error-circle bx-lg text-danger mb-3"></i>
                    <h5>Error Loading Results</h5>
                    <p class="text-muted">Please try refreshing the page</p>
                </div>
            </div>
        `;
    });
}

function updateSearchResults(data) {
    if (!data.results || data.results.length === 0) {
        document.getElementById('search-results').innerHTML = `
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bx bx-search-alt bx-lg text-muted mb-3"></i>
                    <h5>No Results Found</h5>
                    <p class="text-muted">Try adjusting your search terms or browse all items</p>
                </div>
            </div>
        `;
        return;
    }

    const resultsHtml = `
        <div class="card">
            <div class="table-responsive text-nowrap">
                <div class="list-group list-group-flush">
                    ${data.results.map(result => {
                        const type = result.source.replace('notes_', '').replace('own_', '');
                        const typeClass = 'type-' + type.split('_')[0];
                        return `
                            <a href="${result.url}" class="list-group-item list-group-item-action search-item">
                                <div class="d-flex justify-content-between align-items-center py-2">
                                    <div>
                                        <h6 class="mb-1">${escapeHtml(result.title)}</h6>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="result-type ${typeClass}">
                                                ${capitalizeFirst(type)}
                                            </span>
                                            ${result.datetime ? `
                                                <small class="text-muted">
                                                    ${formatDate(result.datetime)}
                                                </small>
                                            ` : ''}
                                        </div>
                                    </div>
                                    <i class="bx bx-chevron-right text-muted"></i>
                                </div>
                            </a>
                        `;
                    }).join('')}
                </div>
            </div>
        </div>
    `;

    document.getElementById('search-results').innerHTML = resultsHtml;
}

function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function capitalizeFirst(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    });
}
</script>
</body>
</html>
