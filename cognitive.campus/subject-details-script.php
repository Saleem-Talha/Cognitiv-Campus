
<script>
// Convert PHP array to JavaScript
const courseMaterials = <?php echo json_encode($courseMaterials); ?>;
const itemsPerPage = 5; // Number of items per page
let currentPage = 1; // Track the current page

/**
 * Display course materials for a specific page
 * @param {number} page - Page number to display
 */
function displayMaterials(page) {
    const startIndex = (page - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const paginatedMaterials = courseMaterials.slice(startIndex, endIndex);

    // Generate HTML for course materials
    const materialsHtml = paginatedMaterials.map((material, index) => `
        <div class="mb-4">
            <h5 class="mb-2">${startIndex + index + 1}. ${material.title}</h5>
            ${renderAttachments(material.materials)}
        </div>
    `).join('');

    // Insert HTML into the DOM
    document.getElementById('courseMaterialsList').innerHTML = materialsHtml;
    updatePagination(); // Update pagination controls
}

/**
 * Render attachments for a material
 * @param {Array} attachments - List of attachments
 * @returns {string} - HTML for attachments
 */
function renderAttachments(attachments) {
    if (!attachments || attachments.length === 0) return '';

    return `
        <div class="d-flex flex-wrap gap-2">
            ${attachments.map(attachment => {
                if (attachment.driveFile) {
                    const file = attachment.driveFile.driveFile;
                    const fileType = file.title.split('.').pop().toUpperCase();
                    return `
                        <div class="d-flex align-items-center p-2 bg-light rounded border">
                            <i class="bx bx-file me-2 text-primary"></i>
                            <span class="me-2">${file.title}</span>
                            <small class="text-muted me-2">(${fileType})</small>
                            <a href="${file.alternateLink}" target="_blank" class="btn btn-sm btn-outline-primary">Open</a>
                        </div>
                    `;
                } else if (attachment.link) {
                    return `
                        <div class="d-flex align-items-center p-2 bg-light rounded border">
                            <i class="bx bx-link me-2 text-info"></i>
                            <span class="me-2">${attachment.link.title}</span>
                            <a href="${attachment.link.url}" target="_blank" class="btn btn-sm btn-outline-info">Visit</a>
                        </div>
                    `;
                }
            }).join('')}
        </div>
    `;
}

/**
 * Update pagination controls
 */
function updatePagination() {
    const totalPages = Math.ceil(courseMaterials.length / itemsPerPage);
    let paginationHtml = '';

    for (let i = 1; i <= totalPages; i++) {
        paginationHtml += `
            <li class="page-item ${currentPage === i ? 'active' : ''}">
                <a class="page-link" href="#" onclick="changePage(${i}); return false;">${i}</a>
            </li>
        `;
    }

    // Insert pagination HTML into the DOM
    document.getElementById('pagination').innerHTML = paginationHtml;
}

/**
 * Change the current page and display materials
 * @param {number} page - New page number
 */
function changePage(page) {
    currentPage = page;
    displayMaterials(currentPage);
}

// Initial display of materials
displayMaterials(currentPage);
</script>

<script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const announcementsBtn = document.getElementById('announcementsBtn');
                                    const courseworkBtn = document.getElementById('courseworkBtn');
                                    const announcementsSection = document.getElementById('announcementsSection');
                                    const courseworkSection = document.getElementById('courseworkSection');

                                    announcementsBtn.addEventListener('click', function() {
                                        announcementsSection.style.display = 'block';
                                        courseworkSection.style.display = 'none';
                                        announcementsBtn.classList.add('active');
                                        courseworkBtn.classList.remove('active');
                                    });

                                    courseworkBtn.addEventListener('click', function() {
                                        announcementsSection.style.display = 'none';
                                        courseworkSection.style.display = 'block';
                                        courseworkBtn.classList.add('active');
                                        announcementsBtn.classList.remove('active');
                                    });

                                    document.querySelectorAll('.view-more').forEach(button => {
                                        button.addEventListener('click', function() {
                                            const textElement = this.closest('.announcement-text');
                                            const fullText = textElement.dataset.fullText;
                                            textElement.innerHTML = fullText;
                                        });
                                    });
                                });
                            </script>