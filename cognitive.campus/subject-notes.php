<h2 class="mb-3">Notes</h2>
        
        <div class="card mb-3">
        <div class="card-body">
        <?php 

        $notes_start_date = isset($_GET['notes_start_date']) ? $_GET['notes_start_date'] : '';
        $notes_end_date = isset($_GET['notes_end_date']) ? $_GET['notes_end_date'] : '';

        // Check if "Today" button is clicked
        if (isset($_GET['notes_today'])) {
            $notes_start_date = $notes_end_date = date('Y-m-d');
        }

        $query = "SELECT * FROM notes_course WHERE courseId = '$courseId' AND userEmail = '$userEmail'";
        if ($notes_start_date && $notes_end_date) {
            $query .= " AND DATE(datetime) BETWEEN '$notes_start_date' AND '$notes_end_date'";
        }

        $select_pages = $db->query($query);
        if($select_pages->num_rows){
            echo "<ul class='list-group'>";
            $count = 0;
            while($row = $select_pages->fetch_assoc()){
                $page_id       = $row['id'];
                $page_title    = $row['page_title'];
                $page_type     = $row['type'];
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
                            <a href='notes-page-course.php?courseId=" . ($courseType == 'uniCourse' ? $courseId : 'extra_' . $courseId) . "&page_id=$page_id&count=$count&courseType=$courseType' class='btn border'>
                                <i class='bx bx-book-open me-2'></i><span>Open</span>
                            </a>
                        </div>
                    </div>
                </li>";
            }
            echo "</ul>";
        }else{
            echo " <div class='alert alert-primary' role='alert'>
                         <i class='bx bx-info-circle me-2'></i>No pages found.
                    </div>";
        }

        ?>

        </div>
        </div>