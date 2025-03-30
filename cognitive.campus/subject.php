<?php include_once('includes/header.php'); ?>
<?php include_once('includes/utils.php'); ?>
<?php include_once('includes/validation.php'); ?>
<?php include_once('includes/user_session.php'); ?>


<!-- Main layout wrapper -->
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">

        <!-- Include Sidebar -->
        <?php include_once('includes/sidebar-main.php'); ?>

        <div class="layout-page">
            <!-- Include Navbar -->
            <?php include_once('includes/navbar.php'); ?>

            <div class="content-wrapper">
                <?php
                // Include Composer's autoloader
                require_once 'vendor/autoload.php';

                $courses = [];
                $hasGoogleAuth = isset($_SESSION['access_token']);

                if ($hasGoogleAuth) {
                    try {
                       
                        // Fetch list of courses from Google Classroom
                        $courses = $classroomService->courses->listCourses()->getCourses();
                    } catch (Exception $e) {
                        // Log the error and continue with empty courses array
                        error_log('Google Classroom API Error: ' . $e->getMessage());
                    }
                }

                // Fetch course statuses for the current user
                $stmt = $db->prepare("SELECT course_id, status FROM course_status WHERE user_id = ?");
                $stmt->bind_param("s", $userEmail);
                $stmt->execute();
                $result = $stmt->get_result();
                $courseStatuses = [];
                while ($row = $result->fetch_assoc()) {
                    $courseStatuses[$row['course_id']] = $row['status'];
                }
                $stmt->close();
                ?>


                <div class="container-xxl flex-grow-1 container-p-y">
                    <div class="d-flex justify-content-between align-items-center py-3 mb-4">
                        <h4><span class="text-muted fw-light">Subject /</span> All Subjects</h4>
                        <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">+ Add Subject</a>
                    </div>

                    <!-- Modal for adding new course -->
                    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="exampleModalLabel">Add Course</h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form action="" method="post" enctype="multipart/form-data">
                                        <div class="mb-3 form-floating">
                                            <input type="text" name="name" class="form-control" placeholder="Enter Name" required>
                                            <label for="">Enter Course Name</label>
                                        </div>
                                        <div class="mb-3 form-floating">
                                            <input type="file" name="image" class="form-control" title="Upload Image" required>
                                            <label for="">Upload Course Image</label>
                                        </div>
                                        <input type="submit" value="Add" class="btn btn-primary" name="add-course">
                                    </form>
                                    <?php
                                    $userInfo = getUserInfo();
                                    $plan = $userInfo['plan'];
                                    $userEmail = $userInfo['email'];
                                   if(isset($_POST['add-course'])){
                                    $name = $_POST['name'];
                                    $image_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                                    $unique_image_name = uniqid('course_', true) . '.' . $image_extension;
                                    $image_temp = $_FILES['image']['tmp_name'];
                        
                                    $check = $db->query("SELECT * FROM own_course WHERE name = '$name' AND userEmail = '$userEmail'");
                                    $course_count = $db->query("SELECT COUNT(*) as count FROM own_course WHERE userEmail = '$userEmail'")->fetch_assoc()['count'];
                        
                                    $max_courses = ($plan == 'basic') ? 6 : (($plan == 'standard') ? 18 : PHP_INT_MAX);
                        
                                    if($check->num_rows > 0){
                                        echo "<script>
                                            swal({
                                                icon: 'warning',
                                                title: 'Oops...',
                                                text: 'Course Already Exists!'
                                            });
                                        </script>";
                                    } elseif ($course_count >= $max_courses) {
                                        echo "<script>
                                            swal({
                                                icon: 'warning',
                                                title: 'Limit Reached',
                                                text: 'You have reached the maximum number of courses for your plan. Please upgrade to add more courses.'
                                            });
                                        </script>";
                                    } else {
                                        move_uploaded_file($image_temp, "img/$unique_image_name");
                                        $insert = $db->query("INSERT INTO own_course (name, image, userEmail) VALUES ('$name', '$unique_image_name', '$userEmail')");
                                        if($insert){
                                            $notice_message = "A new Subject called : " . $name . " Created";
                                            $type = "Course";
                                            $insert_notification = $db->query("INSERT INTO notice (userEmail, message, type) VALUES ('$userEmail', '$notice_message', '$type') ");
                        
                                            echo "<script>
                                                swal({
                                                    icon: 'success',
                                                    title: 'Success!',
                                                    text: 'Course Added Successfully',
                                                    showConfirmButton: false,
                                                    timer: 1500
                                                }).then(() => {
                                                    window.location.href = 'subject.php';
                                                });
                                            </script>";
                                        } else {
                                            echo "<script>
                                                swal({
                                                    icon: 'error',
                                                    title: 'Error',
                                                    text: '" . $db->error . "'
                                                });
                                            </script>";
                                        }
                                    }
                                }
                                ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Custom styling -->
                    <style>
                        .enhanced-h2 {
                            position: relative;
                            text-align: left;
                            margin: 20px 0;
                        }

                        .enhanced-h2::before {
                            content: '';
                            position: absolute;
                            top: 50%;
                            left: 0;
                            width: 100%;
                            border-top: 0.5px solid silver;
                            transform: translateY(-50%);
                            z-index: -1;
                        }

                        .enhanced-h2 span {
                            background: #f5f5f9 !important;
                            padding: 0 10px;
                            position: relative;
                            z-index: 1;
                        }

                        .enhanced-h2 i {
                            border: 1px solid var(--bs-primary);
                            color: black;
                            transition: all 0.3s ease;
                        }

                        .enhanced-h2:hover i {
                            background: var(--bs-primary);
                            color: white;
                            transition: all 0.3s ease;
                        }
                    </style>

                    <div class="row">
                        <div class="col-md-12">
                            <?php
                            // Fetch user's own courses
                            $own_course = $db->query("SELECT * FROM own_course WHERE userEmail = '$userEmail'");
                            if ($own_course->num_rows) {
                            ?>
                                <h3 class="enhanced-h2 text-uppercase mb-5"><span class="rounded-3 text-muted p-2 px-3">Extra Courses</span></h3>
                                <div class="row">
                                    <?php
                                    while ($row = $own_course->fetch_assoc()) {
                                        $encodedId = encodeId($row['id']); 
                                        $courseName = $row['name'];
                                        $courseImage = $row['image'];
                                    ?>
                                        <div class="col-md-6 mb-4">
                                            <div class="card shadow-sm border-0 h-100">
                                                <div class="position-relative">
                                                    <img src="img/<?php echo $courseImage; ?>" class="card-img-top" alt="Course Image" style="width: 100%; height: 200px; object-fit: cover;">
                                                </div>
                                                <div class="card-body d-flex flex-column">
                                                    <h5 class="card-title fw-bold mb-3"><?php echo $courseName; ?></h5>
                                                    <p class="card-text text-muted mb-4">Extra Course</p>
                                                    <div class="mt-auto d-flex justify-content-between align-items-center">
                                                        <a href="subject-details-own.php?id=<?php echo $encodedId ?>" class="btn btn-primary btn-sm">View Details</a>
                                                        <button type="button" class="btn btn-outline-primary border-0 p-2 btn-sm" onclick="deleteCourse(<?php echo $row['id']; ?>)"><i class="bx bx-trash"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php
                                    }
                                    ?>
                                </div>
                            <?php
                            } else {
                                echo "<div class='alert alert-primary' role='alert'>
                                        <i class='bx bx-info-circle me-2'></i>No Extra Courses found.
                                    </div>";
                            }
                            ?>

                            <script>
                                function deleteCourse(id) {
                                    swal({
                                            title: "Are you sure?",
                                            text: "Once deleted, you will not be able to recover this course!",
                                            icon: "warning",
                                            buttons: true,
                                            dangerMode: true,
                                        })
                                        .then((willDelete) => {
                                            if (willDelete) {
                                                $.ajax({
                                                    url: 'subject-delete-course.php',
                                                    method: 'POST',
                                                    data: { id: id },
                                                    success: function(response) {
                                                        var result = JSON.parse(response);
                                                        if (result.status === 'success') {
                                                            swal("Poof! Your course has been deleted!", {
                                                                icon: "success",
                                                            }).then(() => {
                                                                location.reload();
                                                            });
                                                        } else {
                                                            swal("Oops! Something went wrong", {
                                                                icon: "error",
                                                            });
                                                        }
                                                    },
                                                    error: function(xhr, status, error) {
                                                        console.error(error);
                                                        swal("Oops! Something went wrong", {
                                                            icon: "error",
                                                        });
                                                    }
                                                });
                                            } else {
                                                swal("Your course is safe!");
                                            }
                                        });
                                }
                            </script>

<h3 class="enhanced-h2 text-uppercase mb-5"><span class="rounded-3 text-muted p-2 px-3">Active Courses</span></h3>
                        <div id="active-courses" class="row">
                            <?php
                            $activeCourses = 0;
                            if ($hasGoogleAuth && !empty($courses)) {
                                foreach ($courses as $course) :
                                    if (isset($courseStatuses[$course->getId()]) && $courseStatuses[$course->getId()]) :
                                        echo generateCourseCard($course, true, $userEmail);
                                        $activeCourses++;
                                    endif;
                                endforeach;
                            }
                            if ($activeCourses == 0) :
                            ?>
                                <div class="col-12">
                                    <div class="alert alert-primary" role="alert">
                                        <i class="bx bx-info-circle me-2"></i>No active courses found.
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <h3 class="enhanced-h2 mt-4 mb-5 text-uppercase d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#inactive-courses" role="button" aria-expanded="false" aria-controls="inactive-courses"><span class="rounded-3 text-muted p-2 px-3">Inactive Courses <i class="bx bx-chevron-down border rounded-3 p-2 mb-1"></i></span></h3>
                        <div id="inactive-courses" class="row collapse">
                            <?php
                            $inactiveCourses = 0;
                            if ($hasGoogleAuth && !empty($courses)) {
                                foreach ($courses as $course) :
                                    if (!isset($courseStatuses[$course->getId()]) || !$courseStatuses[$course->getId()]) :
                                        echo generateCourseCard($course, false, $userEmail);
                                        $inactiveCourses++;
                                    endif;
                                endforeach;
                            }
                            if ($inactiveCourses == 0) :
                            ?>
                                <div class="col-12">
                                    <div class="alert alert-primary" role="alert">
                                        <i class="bx bx-info-circle me-2"></i>No inactive courses found.
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div class="content-backdrop fade"></div>
            </div>
        </div>
    </div>

    <div class="layout-overlay layout-menu-toggle"></div>
</div>

<?php include_once('includes/footer-links.php'); ?>




<?php
// Function to generate HTML for course card
function generateCourseCard($course, $isActive, $userEmail)
{
    global $db;

    // Fetch course image from the database
    $stmt = $db->prepare("SELECT image_path FROM course_images WHERE course_id = ? AND user_email = ? ORDER BY created_at DESC LIMIT 1");
    $courseId = $course->getId();
    $stmt->bind_param("ss", $courseId, $userEmail);
    $stmt->execute();
    $result = $stmt->get_result();
    $imageData = $result->fetch_assoc();
    $stmt->close();

    // Default image if none exists
    $defaultImage = "img/insert.png";
    $imagePath = $imageData ? $imageData['image_path'] : $defaultImage;

    // Generate HTML for the course card
    $html = '<div class="col-md-6 mb-4">';
    $html .= '<div class="card shadow-sm border-0 h-100">';
    $html .= '<div class="position-relative">';
    $html .= '<img src="' . $imagePath . '" class="card-img-top" alt="Course Image" style="width: 100%; height: 200px; object-fit: cover;" id="course-image-' . $course->getId() . '">';
    $html .= '<div class="position-absolute top-0 end-0 m-2">';
    $html .= '<label for="image-upload-' . $course->getId() . '" class="btn btn-light btn-sm rounded-3 p-3 shadow">';
    $html .= '<i class="bx bx-camera"></i>';
    $html .= '</label>';
    $html .= '<input type="file" id="image-upload-' . $course->getId() . '" class="d-none image-upload" data-course-id="' . $course->getId() . '" accept="image/*">';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '<div class="card-body d-flex flex-column">';
    $html .= '<h5 class="card-title fw-bold mb-3">' . $course->getName() . '</h5>';
    $html .= '<p class="card-text text-muted mb-4">' . $course->getSection() . '</p>';
    $html .= '<div class="mt-auto d-flex justify-content-between align-items-center">';
    $html .= '<a href="subject-details.php?id=' . $course->getId() . '" class="btn btn-outline-primary btn-sm">View Details</a>';
    $html .= '<div class="form-check form-switch">';
    $html .= '<input type="checkbox" class="form-check-input course-status" id="course-' . $course->getId() . '" data-course-id="' . $course->getId() . '"' . ($isActive ? ' checked' : '') . '>';
    $html .= '<label class="form-check-label" for="course-' . $course->getId() . '">Active</label>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}
?>

<script>
    $(document).ready(function() {
        $(document).on('change', '.course-status', function() {
            var courseId = $(this).data('course-id');
            var status = $(this).prop('checked') ? 1 : 0;
            var card = $(this).closest('.col-md-6');
            var courseName = card.find('.card-title').text();
            console.log(courseName);

            $.ajax({
                url: 'subject-update-course-status.php',
                method: 'POST',
                data: {
                    course_id: courseId,
                    course_name: courseName,
                    status: status
                },
                success: function(response) {
                    console.log(response);
                    if (status) {
                        $('#active-courses').append(card);
                        $('#no-active-courses').addClass('d-none');
                        swal({
                            icon: 'success',
                            title: 'Course Activated',
                            text: 'The course has been successfully activated.',
                            timer: 2000,
                            buttons: false
                        });
                    } else {
                        $('#inactive-courses').append(card);
                        if ($('#active-courses').children().length === 0) {
                            $('#no-active-courses').removeClass('d-none');
                        }
                        swal({
                            icon: 'info',
                            title: 'Course Deactivated',
                            text: 'The course has been deactivated.',
                            timer: 2000,
                            buttons: false
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error(error);
                    // Revert the switch if there's an error
                    $(this).prop('checked', !status);
                    if (xhr.status === 403) {
                        swal({
                            icon: 'error',
                            title: 'Course Limit Reached',
                            text: 'You have reached the maximum number of courses for your plan. Please upgrade to add more courses.',
                        });
                    } else {
                        swal({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred. Please try again.',
                        });
                    }
                }
            });
        });

        $(document).on('change', '.image-upload', function(e) {
            var courseId = $(this).data('course-id');
            var file = e.target.files[0];
            var reader = new FileReader();
            var imgElement = $('#course-image-' + courseId);

            reader.onload = function(e) {
                var formData = new FormData();
                formData.append('image', file);
                formData.append('course_id', courseId);
                formData.append('user_email', '<?php echo $userEmail; ?>');

                $.ajax({
                    url: 'subject-update-course-image.php',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        var result = JSON.parse(response);
                        if (result.success) {
                            imgElement.attr('src', result.image_path);
                        } else {
                            alert('Failed to update image. Please try again.');
                        }
                    },
                    error: function() {
                        alert('An error occurred. Please try again.');
                    }
                });
            }

            if (file) {
                reader.readAsDataURL(file);
            }
        });
    });
</script>