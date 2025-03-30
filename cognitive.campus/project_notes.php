<div class="col-md-6 mt-3">
    <div class="card">
        <div class="card-header">
            <h5>Notes</h5>
        </div>
        <div class="card-body">
            <?php
            // Query to fetch notes related to the current project
            $select_pages = $db->query("
                SELECT np.*
                FROM notes_project np
                WHERE np.project_id = '$project_id'
            ");

            // Check if there are any notes to display
            if ($select_pages->num_rows) {
                echo "<ul class='list-group'>"; // Start list of notes
                $count = 0;
                while ($row = $select_pages->fetch_assoc()) {
                    $page_id = $row['id'];
                    $page_title = $row['page_title'];
                    $page_datetime = $row['datetime'];
                    $page_user_email = $row['userEmail'];
                    $count++;
                    $formatted_date = date('d M Y', strtotime($page_datetime)); // Format date

                    // Display each note
                    echo "<li class='list-group-item d-flex justify-content-between align-items-center'>
                        <div class='flex-grow-1 d-flex justify-content-between align-items-center'>
                            <div>
                                <i class='bx bx-file me-2'></i>
                                <span class='text-muted'>Note $count : </span>
                                <span>$page_title</span>
                                <small class='text-muted ms-2'>$formatted_date</small>
                                <span class='text-primary ms-2'>$page_user_email</span>
                            </div>
                            <div class='d-flex align-items-center'>
                                <a href='notes-page.php?project_id=$project_id&page_id=$page_id&count=$count' class='btn border'>
                                    <i class='bx bx-book-open me-2'></i><span>Open</span>
                                </a>
                            </div>
                        </div>
                    </li>";
                }
                echo "</ul>";
            } else {
                // Message if no notes are found
                echo "<div class='alert alert-primary' role='alert'>
                                        <i class='bx bx-info-circle me-2'></i>No notes found.
                                    </div>";
            }
            ?>
        </div>
    </div>
</div>
