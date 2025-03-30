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