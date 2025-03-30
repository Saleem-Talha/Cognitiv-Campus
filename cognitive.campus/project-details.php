<?php include_once('includes/header.php'); ?>
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        <?php include_once('includes/sidebar-main.php'); ?>
        <div class="layout-page">
            <?php include_once('includes/navbar.php'); ?>
            <div class="content-wrapper">

                <?php

                
                $id = $_GET['project-id'];
                $project_id = decodeId($id);
                if (!isset($project_id)) {
                    header('Location: project.php');
                }

                $project_query = $db->query("SELECT * FROM projects WHERE id = '$project_id'");
                if ($project_query->num_rows) {

                    $project_data = $project_query->fetch_assoc();
                    $project_name = $project_data['name'];
                    $project_start_date = $project_data['start_date'];
                    $project_end_date = $project_data['end_date'];
                    $project_status = $project_data['status'];
                    $project_course_id = $project_data['course_id'];
                    $project_courseType = $project_data['courseType'];
                    $ownerEmail = $project_data['ownerEmail'];
                    $mainProjectFile = $project_data['project_file'];
                    $project_readme = $project_data['readme'];

                    if ($project_courseType == 'uniCourse') {
                        $course_sql = $db->query("SELECT * FROM course_status WHERE course_id = '$project_course_id'");
                        if ($course_sql->num_rows) {
                            while ($course_row = $course_sql->fetch_assoc()) {
                                $courseName = $course_row['course_name'];
                            }
                        }
                    } else {
                        $extra_sql = $db->query("SELECT * FROM own_course WHERE id = '$project_course_id'");
                        if ($extra_sql->num_rows) {
                            while ($extra_row = $extra_sql->fetch_assoc()) {
                                $courseName = $extra_row['name'];
                            }
                        }
                    }
                } else {
                    header('location: project.php');
                }

                ?>

                <div class="container-xxl flex-grow-1 container-p-y">
                    <h4 class="py-3 mb-4 d-flex justify-content-between align-items-center">
                        <span><span class="text-muted fw-light">Project / All Projects /</span> <?php echo $project_name; ?></span>
                        <small class="<?php if ($project_status == 'Active') {
                                            echo "text-primary";
                                        } elseif ($project_status == 'Incomplete') {
                                            echo "text-danger";
                                        } else {
                                            echo 'text-success';
                                        } ?>">
                            <?php

                            if (!empty($project_end_date) && isset($project_end_date)) {
                                $end_date = date('d M Y', strtotime($project_end_date));
                                $current_date = date('Y-m-d');
                                if ($project_end_date < $current_date && $project_status == 'Active') {
                                    $update_status = $db->query("UPDATE projects SET status = 'Incomplete' WHERE id = '$project_id'");
                                    if ($update_status) {
                                        echo "<script>swal('Project Status Updated', 'The project status has been updated to Incomplete as the end date has passed.', 'info');</script>";
                                    } else {
                                        echo "<script>swal('Error', 'Failed to update project status: " . $db->error . "', 'error');</script>";
                                    }
                                }
                            } else {
                                $end_date = 'No End Date';
                            }

                            echo $end_date;

                            ?>
                        </small>
                    </h4>

                    <div class="row">
                        <?php include_once('project_branch.php'); ?>
                        <?php include_once('project_addUser.php'); ?>
                        <?php include_once('project_assignTask.php'); ?>
                        
                        <div class="col-md-6 mt-3">
                            <div class="card" id="view-card">
                                <div class="card-header">
                                    <button id="edit-button" class="btn btn-primary" <?php echo ($project_status !== 'Active') ? 'disabled' : ''; ?>><i class='bx bx-edit-alt me-2'></i> Edit Readme</button>
                                </div>
                                <div class="card-body">
                                    <?php echo $project_readme; ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mt-3" id="edit-card-container" style="display: none;">
                            <div class="card" id="edit-card">
                                <div class="card-body">
                                    <textarea id="blog-editor" rows="30"><?php echo htmlspecialchars($project_readme); ?></textarea>
                                    <div class="mt-3">
                                        <button id="update-readme" class="btn btn-primary">Save Readme</button>
                                        <button id="cancel-edit" class="btn btn-outline-primary">Close</button>
                                    </div>
                                    <?php include 'notes-infinity-canvas.php'; ?>
                                </div>
                            </div>
                        </div>

                        <?php include_once('project_notice.php'); ?>
                        <?php include_once('project_controlpanel.php'); ?>
                        <?php include_once('project_notes.php'); ?>
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

<!-- jQuery Script -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- TinyMCE Script -->
<script src="https://cdn.tiny.cloud/1/p0w0012foe7hsylpq76c2uabfw7zulc7tw3oud8j37601rxe/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    tinymce.init({
        selector: '#blog-editor',
        plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount linkchecker',
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table mergetags | addcomment showcomments | spellcheckdialog a11ycheck typography | align lineheight | checklist numlist bullist indent outdent | emoticons charmap | removeformat | insertfile',
        tinycomments_mode: 'embedded',
        tinycomments_author: 'Author name',
        mergetags_list: [
            { value: 'First.Name', title: 'First Name' },
            { value: 'Email', title: 'Email' }
        ],
        ai_request: (request, respondWith) => respondWith.string(() => Promise.reject("See docs to implement AI Assistant")),
        content_style: "",
        font_formats: 'Bootstrap Sans-serif=var(--bs-font-sans-serif); Andale Mono=andale mono,times; Arial=arial,helvetica,sans-serif; Arial Black=arial black,avant garde; Book Antiqua=book antiqua,palatino; Comic Sans MS=comic sans ms,sans-serif; Courier New=courier new,courier; Georgia=georgia,palatino; Helvetica=helvetica; Impact=impact,chicago; Symbol=symbol; Tahoma=tahoma,arial,helvetica,sans-serif; Terminal=terminal,monaco; Times New Roman=times new roman,times; Trebuchet MS=trebuchet ms,geneva; Verdana=verdana,geneva; Webdings=webdings; Wingdings=wingdings,zapf dingbats',
        setup: function(editor) {
            editor.on('change', function() {
                editor.save();
                saveContent(editor.getContent());
            });
        }
    });

    $(document).ready(function() {
        $('#edit-button').click(function() {
            $('#view-card').parent().hide();
            $('#edit-card-container').show();
        });

        $('#cancel-edit').click(function() {
            $('#edit-card-container').hide();
            $('#view-card').parent().show();
        });

        $('#update-readme').click(function() {
            var content = tinymce.get('blog-editor').getContent();
            $.ajax({
                url: 'project-update-readme.php',
                method: 'POST',
                data: {
                    project_id: <?php echo json_encode($project_id); ?>,
                    readme: content
                },
                success: function(response) {
                    alert('Readme updated successfully!');
                    $('#view-card .card-body').html(content);
                    $('#view-card').parent().show();
                    $('#edit-card-container').hide();
                },
                error: function() {
                    alert('Error updating readme. Please try again.');
                }
            });
        });
    });
</script>

<script>
$(document).ready(function() {
    $('#email').on('input', function() {
        var email = $(this).val();
        if (email.length > 3) {  // Start checking after 3 characters
            $.ajax({
                url: 'project-addUser-check-email.php',
                method: 'POST',
                data: { email: email },
                success: function(response) {
                    $('#emailFeedback').html(response.message);
                    $('#sendRequestBtn').prop('disabled', !response.canSend);
                }
            });
        } else {
            $('#emailFeedback').html('');
            $('#sendRequestBtn').prop('disabled', true);
        }
    });
});
</script>

