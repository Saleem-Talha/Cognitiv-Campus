<?php include_once('includes/header.php'); ?>
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        <?php include_once('includes/sidebar-main.php'); ?>
        <div class="layout-page">
            <?php include_once('includes/navbar.php'); ?>
            <div class="content-wrapper">
                <?php
                $courseType = isset($_GET['courseType']) ? $_GET['courseType'] : '';
                $note_id    = isset($_GET['page_id']) ? $_GET['page_id'] : '';
                $count      = isset($_GET['count']) ? $_GET['count'] : '';

                if (empty($courseType) || empty($note_id) || empty($count)) {
                    header('location: dashboard.php');
                    exit;
                }

                $courseId = $courseType == 'uniCourse' ? $_GET['courseId'] : $_GET['courseId'];

                if ($courseType == 'uniCourse') {
                    $course_sql = $db->query("SELECT * FROM course_status WHERE course_id = '$courseId'");
                    if ($course_sql->num_rows) {
                        $courseName = $course_sql->fetch_assoc()['course_name'];
                    }
                } else {
                    $extra_sql = $db->query("SELECT * FROM own_course WHERE id = '$courseId'");
                    if ($extra_sql->num_rows) {
                        $courseName = $extra_sql->fetch_assoc()['name'];
                    }
                }

                $note_query  = $db->query("SELECT * FROM notes_course WHERE id = '$note_id'");
                $note_row    = $note_query->fetch_assoc();
                $note_title  = $note_row['page_title'];
                $note_type   = $note_row['type'];
                $note_datetime = $note_row['datetime'];
                $content     = $note_row['content'];
                ?>

                <div class="container-xxl flex-grow-1 container-p-y">
                    <div class="d-flex justify-content-between align-items-center py-3 mb-4">
                        <h4><span class="text-muted fw-light">Notes / <?php echo $courseName; ?> /</span> Editor</h4>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="editSwitch">
                            <label class="form-check-label" for="editSwitch">Edit Mode</label>
                        </div>
                    </div>
                    <?php include_once('notes-course-recommendations.php');?>
                    <div class="row">
                        <div class="col-md-12 readonly">
                            <div class="card">
                                <div class="card-header">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class='bx bx-file me-2'></i>
                                            <span class="fw-bold"><?php echo $note_title; ?></span>
                                            <span class='text-muted'> : Note <?php echo $count; ?></span>
                                            <i class='text-muted ms-2'><?php echo date('d M Y', strtotime($note_datetime)); ?></i>
                                        </div>
                                        <button type="button" id="readonlyTtsButton" class="btn btn-link p-0 text-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="Read Aloud">
                                            <i class='bx bx-volume-full'></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="readonlyContent" style="user-select: text;">
                                        <?php echo $content; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12 editor d-none">
                            <div class="card">
                                <div class="card-header">
                                    <div>
                                        <i class='bx bx-file me-2'></i>
                                        <span class="fw-bold"><?php echo $note_title; ?></span>
                                        <span class='text-muted'> : <?php echo $note_type; ?></span>
                                        <span class='text-muted'> : Note <?php echo $count; ?></span>
                                        <i class='text-muted ms-2'><?php echo date('d M Y', strtotime($note_datetime)); ?></i>
                                    </div>
                                </div>
                                <div class="card-body">
                                <div class="mb-3">
                                    <button type="button" id="aiNotesBtn" class="btn btn-primary btn-sm mb-3">AI Notes</button>
                                    <div id="aiNotesContainer" class="d-none">
                                        <textarea id="aiNotesPrompt" class="form-control mb-2" rows="3" placeholder="Enter your AI note prompt"></textarea>
                                        <button type="button" id="generateAiNotes" onclick="sendMessage()" class="btn btn-outline-primary btn-sm mb-2">Generate</button>
                                        <button type="button" id="generateAiImage" onclick="" class="btn btn-outline-primary btn-sm mb-2">Generate Image</button>
                                    </div>
                                    <button type="button" id="summarizeNotesBtn" class="btn btn-primary btn-sm mb-3">Summarize Notes</button>
                                    <div id="summarizeNotesContainer" class="d-none mt-3">
                                        <div id="summarizedContent" class="alert alert-primary"></div>
                                    </div>
                                    <div id="summaryLoadingSpinner" class="text-center d-none">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </div>
                                </div>
                                    <form action="" method="post">
                                        <div class="d-flex justify-content-end mb-3">
                                            <button type="button" id="ttsButton" class="btn btn-link p-0 text-primary me-3" data-bs-toggle="tooltip" data-bs-placement="top" title="Read Aloud">
                                                <i class='bx bx-volume-full'></i>
                                            </button>
                                            <button type="button" id="sttButton" class="btn btn-link p-0 text-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="Speech to Text">
                                                <i class='bx bx-microphone'></i>
                                            </button>
                                        </div>
                                        <div class="mb-3">
                                            <textarea name="note_content" id="blog-editor" rows="30"><?php echo htmlspecialchars($content); ?></textarea>
                                        </div>

                                        <?php include_once('notes-infinity-canvas.php'); ?>
                                    </form>
                                </div>
                            </div>
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

<?php include_once('notes-tiny-editor.php'); ?>
<?php include_once('notes-text-to-speech.php'); ?>
<?php include_once('notes-speech-to-text.php'); ?>
<?php include_once('notes-swicth-to-edit.php'); ?>
<?php include_once('includes/footer-links.php'); ?>


<script>
    function saveContent(content) {
  $.ajax({
    url: 'notes-save-course-note.php',
    type: 'POST',
    data: {
      note_id: <?php echo $note_id; ?>,
      content: content
    },
    success: function(response) {
      console.log('Content saved successfully');
    },
    error: function(xhr, status, error) {
      console.error('Error saving content:', error);
    }
  });
}
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const aiNotesBtn = document.getElementById('aiNotesBtn');
    const aiNotesContainer = document.getElementById('aiNotesContainer');
    const generateAiNotes = document.getElementById('generateAiNotes');
    const aiNotesPrompt = document.getElementById('aiNotesPrompt');

    aiNotesBtn.addEventListener('click', function() {
        aiNotesContainer.classList.toggle('d-none');
        if (!aiNotesContainer.classList.contains('d-none')) {
            aiNotesPrompt.focus();
        }
    });

    generateAiNotes.addEventListener('click', function() {
        const prompt = aiNotesPrompt.value.trim();
        if (prompt) {
            // Here you can add the API call to your AI service
            console.log('Generating AI notes for prompt:', prompt);
            // After getting response, you can insert it into TinyMCE
            // tinymce.activeEditor.setContent(aiResponse);
        }
    });
});
</script>
<script src="js/ai-generate-image.js"></script>
<script src="js/ai-send-notes-prompt.js"></script>
<script src="js/ai-summarize-notes.js"></script>