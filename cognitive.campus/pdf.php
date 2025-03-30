<?php
require_once 'includes/db-connect.php';
require_once 'pdf_get_summaries.php';

function parseStructuredSummary($summaryText) {
    // Predefined sections to extract
    $sections = [
        'Document Type/Genre' => '',
        'Main Topic' => '',
        'Key Points' => [],
        'Purpose' => '',
        'Target Audience' => ''
    ];

    // Regex patterns to extract sections
    $patterns = [
        '/Document Type\/Genre:\s*(.+?)(?=\n|$)/i' => 'Document Type/Genre',
        '/Main Topic:\s*(.+?)(?=\n|$)/i' => 'Main Topic',
        '/Purpose:\s*(.+?)(?=\n|$)/i' => 'Purpose',
        '/Target Audience:\s*(.+?)(?=\n|$)/i' => 'Target Audience',
        '/Key Points:(.+?)(?=(Purpose:|Target Audience:|$))/is' => 'Key Points'
    ];

    // Extract each section
    foreach ($patterns as $pattern => $key) {
        if (preg_match($pattern, $summaryText, $matches)) {
            if ($key === 'Key Points') {
                // Split key points into an array, removing empty entries
                $points = preg_split('/[\n\r]+/', trim($matches[1]));
                $sections[$key] = array_filter(array_map('trim', $points), function($point) {
                    return !empty($point) && !preg_match('/^[\-•\*]?\s*$/', $point);
                });
            } else {
                $sections[$key] = trim($matches[1]);
            }
        }
    }

    return $sections;
}

include_once('includes/validation.php');
$userInfo = getUserInfo();
$userEmail = $userInfo['email'] ?? null;
$previousSummaries = getSummaries($userEmail);

// Get total count of summaries
$total_summaries = countTotalSummaries($userEmail);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PDF Summarizer</title>
    
</head>
<body>
    <?php include_once('includes/header.php'); ?>
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        <?php include_once('includes/sidebar-main.php'); ?>
        <div class="layout-page">
            <?php include_once('includes/navbar.php'); ?>
            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">
                    <h4 class="py-3 mb-4"><span class="text-muted fw-light">PDF /</span> Summarizer</h4>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="col-md-12">
                                <div class="card upload-section mb-4 p-5">
                                    <h2 class="mb-4 text-primary">PDF Summarizer</h2>
                                    <form id="uploadForm" enctype="multipart/form-data">
                                        <div class="mb-3">
                                            <label for="pdfFile" class="form-label">Upload PDF</label>
                                            <input 
                                                type="file" 
                                                class="form-control" 
                                                id="pdfFile" 
                                                name="pdfFile" 
                                                accept=".pdf" 
                                                required
                                            >
                                        </div>
                                        <button type="submit" id="submitBtn" class="btn btn-primary btn-loader">
                                            Summarize PDF
                                        </button>
                                    </form>

                                    <div id="summary-container" class="mt-3 d-none">
                                        <div class="card">
                                            <div class="card-header bg-primary text-white">
                                                Latest Summary
                                            </div>
                                            <div class="card-body" id="summary-content">
                                                <!-- Latest summary will be dynamically inserted here -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                    <span>Previous Summaries</span>
                                    <span class="badge bg-light text-primary"><?php echo $total_summaries; ?></span>
                                </div>
                                <div class="card-body" id="previous-summaries-container">
                                    <?php if (empty($previousSummaries)): ?>
                                        <p class="mt-2 text-muted">No previous summaries found.</p>
                                    <?php else: ?>
                                        <?php foreach ($previousSummaries as $index => $summary): ?>
                                            <?php 
                                            $parsedSummary = parseStructuredSummary($summary['summary']);
                                            $summaryId = 'summary-' . $index;
                                            ?>
                                            <div class="card summary-card mb-2 mt-3">
                                                <div class="card-body position-relative">
                                                    <button class="copy-btn" 
                                                            onclick="copySummary('<?php echo $summaryId; ?>')" 
                                                            data-summary-id="<?php echo $summaryId; ?>">
                                                        <i class='bx bx-copy-alt copy-icon'></i>
                                                        <i class='bx bx-check copy-icon copied-icon'></i>
                                                    </button>
                                                    <div id="<?php echo $summaryId; ?>">
                                                        <?php foreach ($parsedSummary as $section => $content): ?>
                                                            <?php if (!empty($content)): ?>
                                                                <div class="summary-section">
                                                                    <span class="summary-section-title"><?php echo htmlspecialchars($section); ?>: </span>
                                                                    <?php if ($section === 'Key Points'): ?>
                                                                        <ul class="key-points-list">
                                                                            <?php foreach ($content as $point): ?>
                                                                                <li><?php echo htmlspecialchars($point); ?></li>
                                                                            <?php endforeach; ?>
                                                                        </ul>
                                                                    <?php else: ?>
                                                                        <?php echo htmlspecialchars($content); ?>
                                                                    <?php endif; ?>
                                                                </div>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                    </div>
                                                    <small class="text-muted d-block mt-2">
                                                        <?php echo htmlspecialchars($summary['filename']); ?> | 
                                                        <?php echo date('d M Y H:i', strtotime($summary['created_at'])); ?>
                                                    </small>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
</div>

<style>
.copy-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    background: none;
    border: none;
    cursor: pointer;
    transition: transform 0.2s ease;
}

.copy-btn .copy-icon {
    font-size: 1.5rem;
    color: #6c757d;
    transition: color 0.3s ease;
}

.copy-btn .copied-icon {
    display: none;
    color: #28a745;
}

.copy-btn.copied .bx-copy-alt {
    display: none;
}

.copy-btn.copied .copied-icon {
    display: inline-block;
    animation: copyAnimation 0.5s ease;
}

@keyframes copyAnimation {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.2); }
}

.copy-btn:hover .copy-icon {
    color: #495057;
}
</style>

<script>
function copySummary(elementId) {
    const summaryElement = document.getElementById(elementId);
    const summaryText = summaryElement.innerText;
    const copyButton = document.querySelector(`button[data-summary-id="${elementId}"]`);

    // Create a temporary textarea to copy the text
    const tempTextArea = document.createElement('textarea');
    tempTextArea.value = summaryText;
    document.body.appendChild(tempTextArea);
    
    // Select and copy the text
    tempTextArea.select();
    document.execCommand('copy');
    
    // Remove the temporary textarea
    document.body.removeChild(tempTextArea);

    // Add copied state
    copyButton.classList.add('copied');

    // Remove copied state after 2 seconds
    setTimeout(() => {
        copyButton.classList.remove('copied');
    }, 2000);
}
</script>
                        </div>
                    </div>
                </div>
                <?php include_once('includes/footer.php'); ?>
                <div class="content-backdrop fade"></div>
            </div>
        </div>
    </div>

    <div class="layout-overlay layout-menu-toggle"></div>
</div>

<?php include_once('includes/footer-links.php'); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function displayStructuredSummary(data) {
        const submitBtn = document.getElementById('submitBtn');
        const summaryContainer = document.getElementById('summary-container');
        const summaryContent = document.getElementById('summary-content');
        const previousSummariesContainer = document.getElementById('previous-summaries-container');

        // Remove loader
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Summarize PDF';

        if (data.summary) {
            // Clear previous content
            summaryContent.innerHTML = '';

            // Parse summary sections
            const sections = [
                'Document Type/Genre',
                'Main Topic',
                'Key Points',
                'Purpose',
                'Target Audience'
            ];

            sections.forEach(section => {
                const sectionRegex = new RegExp(`${section}:\\s*(.+?)(?=\\n|$)`, 'is');
                const match = data.summary.match(sectionRegex);

                if (match) {
                    const sectionDiv = document.createElement('div');
                    sectionDiv.className = 'summary-section';

                    const sectionTitle = document.createElement('span');
                    sectionTitle.className = 'summary-section-title';
                    sectionTitle.textContent = `${section}: `;

                    const sectionContent = document.createElement('span');

                    if (section === 'Key Points') {
                        const pointsList = document.createElement('ul');
                        pointsList.className = 'key-points-list';

                        // Split key points and create list items
                        const points = match[1].trim().split(/[\n\r]+/).filter(point => point.trim());
                        points.forEach(point => {
                            const listItem = document.createElement('li');
                            listItem.textContent = point.trim();
                            pointsList.appendChild(listItem);
                        });

                        sectionDiv.appendChild(sectionTitle);
                        sectionDiv.appendChild(pointsList);
                    } else {
                        sectionContent.textContent = match[1].trim();
                        sectionDiv.appendChild(sectionTitle);
                        sectionDiv.appendChild(sectionContent);
                    }

                    summaryContent.appendChild(sectionDiv);
                }
            });

            summaryContainer.classList.remove('d-none');

            // Prepend new summary to previous summaries
            const newSummaryCard = document.createElement('div');
            newSummaryCard.className = 'card summary-card mb-2';
            newSummaryCard.innerHTML = `
                <div class="card-body">
                    ${summaryContent.innerHTML}
                    <small class="text-muted d-block mt-2">
                        ${data.filename} | Just now
                    </small>
                </div>
            `;

            // Insert new summary at the top
            if (previousSummariesContainer.firstChild) {
                previousSummariesContainer.insertBefore(
                    newSummaryCard, 
                    previousSummariesContainer.firstChild
                );
            } else {
                previousSummariesContainer.appendChild(newSummaryCard);
            }
        } else {
            summaryContent.textContent = 'Error generating summary';
            summaryContainer.classList.remove('d-none');
        }
    }

    document.getElementById('uploadForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const fileInput = document.getElementById('pdfFile');
        const submitBtn = document.getElementById('submitBtn');
        const file = fileInput.files[0];

        if (file && file.type === 'application/pdf') {
            // Disable button and add loader
            submitBtn.disabled = true;
            submitBtn.innerHTML = `
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                Summarizing...
            `;

            const formData = new FormData();
            formData.append('pdfFile', file);

            fetch('pdf_upload.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(displayStructuredSummary)
            .catch(error => {
                console.error('Error:', error);
                
                // Remove loader and re-enable button
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Summarize PDF';
                
                alert('An error occurred while uploading the PDF');
            });
        } else {
            alert('Please upload a PDF file');
        }
    });
    </script>
</body>
</html>
