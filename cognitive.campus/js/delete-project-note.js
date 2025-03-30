function confirmDelete(pageId, projectId) {
    swal({
            title: "Are you sure?",
            text: "Once deleted, you will not be able to recover this note!",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        })
        .then((willDelete) => {
            if (willDelete) {
                window.location.href = `notes-projects.php?choose=123&project_id=${projectId}&page_id=${pageId}&remove=${pageId}`;
            }
        });
}