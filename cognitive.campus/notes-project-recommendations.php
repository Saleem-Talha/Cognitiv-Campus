<?php
// Function to fetch recommendations based on note ID
function fetchCourseRecommendations($db, $noteId) {
    // Sanitize input to prevent SQL injection
    $safeNoteIdParam = $db->real_escape_string($noteId);

    // Query to fetch recommendations by note_id
    $note_recommendations_query = $db->query("SELECT nr.* 
        FROM notes_recommendations nr
        JOIN notes_project np ON nr.note_id = np.id
        WHERE np.id = '$safeNoteIdParam'
        ORDER BY nr.recommendation_rank ASC");

    $note_recommendations = [];
    // Fetch recommendations if any exist
    if ($note_recommendations_query && $note_recommendations_query->num_rows > 0) {
        while ($recommendation = $note_recommendations_query->fetch_assoc()) {
            $note_recommendations[] = $recommendation;
        }
    }

    return $note_recommendations;
}

// Assuming $note_id is set earlier in the page context
$note_recommendations = fetchCourseRecommendations($db, $note_id);
?>

<!-- Recommendations Button -->
<div class="my-3">
    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#notesProjectRecommendationsModal">
        View Project Recommendations
    </button>
</div>

<!-- Notes Project Recommendations Modal -->
<div class="modal fade" id="notesProjectRecommendationsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Recommended Courses for Project: <?php echo htmlspecialchars($note_title); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php if (!empty($note_recommendations)): ?>
                    <div class="row row-cols-1 row-cols-md-2 g-4">
                        <?php foreach ($note_recommendations as $index => $recommendation): ?>
                            <div class="col">
                                <div class="card h-100 shadow-sm">
                                    <div class="card-header text-white">
                                        <h5 class="card-title mb-0">
                                            Recommendation #<?php echo $index + 1 ?> 
                                            <span class="badge bg-white text-primary float-end">
                                                Rank: <?php echo htmlspecialchars($recommendation['recommendation_rank']); ?>
                                            </span>
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <h6 class="card-subtitle mb-2 text-muted">
                                                    <?php echo htmlspecialchars($recommendation['recommended_course_name']); ?>
                                                </h6>
                                            </div>
                                            <div class="col-md-4 text-end">
                                                <div class="mb-2">
                                                    <small class="text-muted">Difficulty:</small>
                                                    <span class="badge bg-label-primary">
                                                        <?php echo htmlspecialchars($recommendation['difficulty_level']); ?>
                                                    </span>
                                                </div>
                                                <div class="mb-2">
                                                    <small class="text-muted">Similarity:</small>
                                                    <span class="badge bg-label-warning">
                                                        <?php echo number_format($recommendation['similarity_score'], 2); ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <small class="text-muted">
                                                    <i class="bx bx-map-pin me-1"></i>
                                                    <?php echo htmlspecialchars($recommendation['university']); ?>
                                                </small>
                                            </div>
                                            <div class="col-md-6 text-end">
                                                <a href="<?php echo htmlspecialchars($recommendation['course_url']); ?>" 
                                                class="btn btn-sm btn-outline-primary" 
                                                target="_blank">
                                                    <i class="bx bx-link-external me-1"></i>View Course
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center p-5">
                        <i class="bx bx-info-circle text-primary mb-3" style="font-size: 3rem;"></i>
                        <h5 class="mb-3">No Recommended Courses Found</h5>
                        <p class="text-muted">We couldn't find any courses related to this specific project.</p>
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-dismiss="modal">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>