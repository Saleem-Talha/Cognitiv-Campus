<?php include_once('includes/header.php'); ?>
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        <?php include_once('includes/sidebar-main.php'); ?>
        <div class="layout-page">
            <?php include_once('includes/navbar.php'); ?>
            <div class="content-wrapper">



                <div class="container-xxl flex-grow-1 container-p-y">
                    <h4 class="py-3 mb-4"><span class="text-muted fw-light">Notes /</span> Course Notes</h4>


                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <form action="" method="get"
                                        class="flex-grow-1 d-flex flex-column justify-content-between">
                                        <div class="mb-3 form-floating">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="courseType"
                                                    id="uniCourse" value="uniCourse" checked>
                                                <label class="form-check-label" for="uniCourse">University
                                                    Course</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="courseType"
                                                    id="extraCourse" value="extraCourse">
                                                <label class="form-check-label" for="extraCourse">Extra Course</label>
                                            </div>
                                        </div>
                                        <div class="mb-3 form-floating" id="uniCourseSelect">
                                            <select name="courseId" class="form-select">
                                                <?php

                                                $main_sql = $db->query("SELECT * FROM course_status WHERE user_id = '$userEmail'");
                                                while ($main_row = $main_sql->fetch_assoc()) {
                                                    echo '<option value="' . $main_row['course_id'] . '">' . $main_row['course_name'] . '</option>';
                                                }

                                                ?>
                                            </select>
                                            <label for="">University Courses</label>
                                        </div>
                                        <div class="mb-3 form-floating" id="extraCourseSelect" style="display: none;">
                                            <select name="extra_courseId" class="form-select">
                                                <?php

                                                $extra_sql = $db->query("SELECT * FROM own_course WHERE userEmail = '$userEmail'");
                                                while ($extra_row = $extra_sql->fetch_assoc()) {
                                                    echo '<option value="' . $extra_row['id'] . '">' . $extra_row['name'] . '</option>';
                                                }

                                                ?>
                                            </select>
                                            <label for="">Extra Courses</label>
                                        </div>
                                        <div class="mt-auto">
                                            <input type="submit" value="Choose" class="btn btn-primary" name="choose">
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    </div>


                    <?php if (isset($_GET['choose'])) { ?>

                        <div class="row mt-3">
                            <?php

                            if (isset($_GET['choose'])) {

                                $courseType = $_GET['courseType'];
                                $courseId = $courseType == 'uniCourse' ? $_GET['courseId'] : $_GET['extra_courseId'];

                                if ($courseType == 'uniCourse') {
                                    $course_sql = $db->query("SELECT * FROM course_status WHERE course_id = '$courseId'");
                                    if ($course_sql->num_rows) {
                                        while ($course_row = $course_sql->fetch_assoc()) {
                                            $courseName = $course_row['course_name'];
                                        }
                                    }
                                } else {
                                    $extra_sql = $db->query("SELECT * FROM own_course WHERE id = '$courseId'");
                                    if ($extra_sql->num_rows) {
                                        while ($extra_row = $extra_sql->fetch_assoc()) {
                                            $courseName = $extra_row['name'];
                                        }
                                    }
                                }


                                ?>
                                <div class="col-md-3 mb-3">
                                    <div class="card h-100">
                                        <div class="card-body d-flex flex-column">
                                            <h5 class="card-title" style="font-size: 1.5rem;"><?php echo $courseName; ?></h5>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <?php

                            }

                            ?>
                            <div class="col-md-9">

                                <div class="card h-100">
                                    <div class="card-header">
                                        <!-- Button trigger modal -->
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                            data-bs-target="#exampleModal">
                                            <i class="bx bx-plus"></i> Add Note Page
                                        </button>

                                        <!-- Modal -->
                                        <div class="modal fade" id="exampleModal" tabindex="-1"
                                            aria-labelledby="exampleModalLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h1 class="modal-title fs-5" id="exampleModalLabel">Add Note Page
                                                        </h1>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">


                                                        <form action="" method="post">
                                                            <div class="mb-3 form-floating">
                                                                <input type="text" id="pageTitle" name="page_title"
                                                                    class="form-control" placeholder="Enter page title"
                                                                    required>
                                                                <label for="pageTitle">Page Title</label>
                                                            </div>
                                                            <div class="mb-3 form-floating">
                                                                <select name="type" class="form-control" required>
                                                                    <option>Assignment</option>
                                                                    <option>Quiz</option>
                                                                    <option>Notes</option>
                                                                </select>
                                                                <label for="">Type</label>
                                                            </div>
                                                            <div class="mb-0">
                                                                <input type="hidden" name="courseType"
                                                                    value="<?php echo $courseType; ?>">
                                                                <input type="submit" value="Add Page" name="add_page"
                                                                    class="btn btn-primary">
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">

                                        <?php

                                        if (isset($_POST['add_page'])) {
                                            $page_title = $_POST['page_title'];
                                            $type = $_POST['type'];
                                            $courseType = $_POST['courseType'];

                                            $insert_page = $db->query("INSERT INTO notes_course (courseId, type, userEmail, page_title, datetime, courseType) VALUES ('$courseId', '$type', '$userEmail', '$page_title', now(), '$courseType') ");
                                            if ($insert_page) {
                                                $notice_message = "A new Note called : " . $page_title . " Created";
                                                $type = "Notes";
                                                $insert_notification = $db->query("INSERT INTO notice (userEmail, message, type) VALUES ('$userEmail', '$notice_message', '$type') ");
                            
                                                echo "<script>
                                                swal({
                                                    title: 'Success!',
                                                    text: 'Page added successfully',
                                                    icon: 'success',
                                                    button: 'OK',
                                                }).then(() => {
                                                    window.location = 'notes-course.php?choose&courseId=$courseId&courseType=$courseType&extra_courseId=$courseId';
                                                });
                                            </script>";
                                                                                } else {
                                                                                    echo "<script>
                                                swal({
                                                    title: 'Error!',
                                                    text: 'Failed to add page',
                                                    icon: 'error',
                                                    button: 'OK',
                                                });
                                            </script>";
                                            }
                                        }

                                        ?>


                                        <?php

                                        $select_pages = $db->query("SELECT * FROM notes_course WHERE courseId = '$courseId' AND userEmail = '$userEmail' ");
                                        if ($select_pages->num_rows) {
                                            echo "<ul class='list-group'>";
                                            $count = 0;
                                            while ($row = $select_pages->fetch_assoc()) {
                                                $page_id = $row['id'];
                                                $page_title = $row['page_title'];
                                                $page_type = $row['type'];
                                                $page_datetime = $row['datetime'];
                                                $count++;
                                                $page_datetime = date('d M Y', strtotime($page_datetime));
                                                echo "<li class='list-group-item d-flex justify-content-between align-items-center'>
                                                    <div class='flex-grow-1 d-flex justify-content-between align-items-center'>
                                                        <div>
                                                            <i class='bx bx-file me-2'></i>
                                                            <span class='text-muted'>Note $count : </span>
                                                            <span>$page_title</span>
                                                            <span class='text-muted'> : $page_type : </span>
                                                            <small class='text-muted ms-2'>$page_datetime</small>
                                                        </div>
                                                        <div class='d-flex align-items-center'>
                                                            <a href='javascript:void(0);' onclick='confirmDelete($page_id, \"$courseId\", \"$courseType\")' class='btn btn-link text-danger me-2'>
                                                                <i class='bx bx-trash'></i>
                                                            </a>
                                                            <a href='notes-page-course.php?courseId=" . ($courseType == 'uniCourse' ? $courseId : $courseId) . "&page_id=$page_id&count=$count&courseType=$courseType' class='btn border'>
                                                                <i class='bx bx-book-open me-2'></i><span>Open</span>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </li>";
                                            }
                                            echo "</ul>";
                                        } else {
                                            echo "<p><i class='bx bx-info-circle me-2'></i>No pages found</p>";
                                        }

                                        ?>
                                        <script src="js/delete-course-note.js"></script>
                                        <?php

                                        if (isset($_GET['remove'])) {
                                            $remove_page_id = $_GET['remove'];
                                            $delete_page = $db->query("DELETE FROM notes_course WHERE id = '$remove_page_id' ");
                                            if ($delete_page) {
                                                $courseType = $_GET['courseType'];
                                                $courseId = $courseType == 'uniCourse' ? $_GET['courseId'] : $_GET['extra_courseId'];
                                                $redirectUrl = "notes-course.php?choose=123&courseType=$courseType&" .
                                                    ($courseType == 'uniCourse' ? "courseId=$courseId" : "extra_courseId=$courseId");
                                                echo "<script>
                                                    swal({
                                                        title: 'Success!',
                                                        text: 'Page deleted successfully',
                                                        icon: 'success',
                                                        button: 'OK',
                                                    }).then(() => {
                                                        window.location = '$redirectUrl';
                                                    });
                                                </script>";
                                                                        } else {
                                                                            echo "<script>
                                                    swal({
                                                        title: 'Error!',
                                                        text: 'Failed to delete page',
                                                        icon: 'error',
                                                        button: 'OK',
                                                    });
                                                </script>";
                                            }
                                        }

                                        ?>

                                    </div>
                                </div>

                            </div>
                        </div>

                    <?php } ?>

                </div>






                <?php include_once('includes/footer.php'); ?>
                <div class="content-backdrop fade"></div>
            </div>
        </div>
    </div>

    <div class="layout-overlay layout-menu-toggle"></div>
</div>

<?php include_once('includes/footer-links.php'); ?>
<script>
    document.getElementById('pageTitle').addEventListener('input', function () {
        if (this.value.length > 25) {
            this.value = this.value.slice(0, 25);
        }
    });
</script>
<script>
    $(document).ready(function () {
        $('input[name="courseType"]').change(function () {
            if ($(this).val() == 'uniCourse') {
                $('#uniCourseSelect').show();
                $('#extraCourseSelect').hide();
            } else {
                $('#uniCourseSelect').hide();
                $('#extraCourseSelect').show();
            }
        });
    });
</script>