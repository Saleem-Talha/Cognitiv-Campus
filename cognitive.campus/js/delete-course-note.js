function confirmDelete(pageId, projectId, courseType) {
    swal({
        title: "Are you sure?",
        text: "Once deleted, you will not be able to recover this note!",
        icon: "warning",
        buttons: true,
        dangerMode: true,
    })
        .then((willDelete) => {
            if (willDelete) {
                let courseIdParam = courseType === 'uniCourse' ? `courseId=${projectId}` : `extra_courseId=${projectId}`;
                window.location.href = `notes-course.php?choose=123&${courseIdParam}&page_id=${pageId}&remove=${pageId}&courseType=${courseType}`;
            }
        });
}