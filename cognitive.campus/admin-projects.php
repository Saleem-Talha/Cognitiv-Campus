<?php include_once('includes/header.php'); ?>
<?php include_once('includes/db-connect.php'); 
include_once('admin-auth.php'); 
?>
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        <?php include_once('includes/admin-sidebar.php'); ?>
        <div class="layout-page">
            <?php include_once('includes/navbar.php'); ?>
            <div class="content-wrapper">

                <div class="container-xxl flex-grow-1 container-p-y">
                    <h4 class="py-3 mb-4"><span class="text-muted fw-light">Admin /</span> Projects</h4>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">All Projects</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Name</th>
                                                    <th>Start Date</th>
                                                    <th>End Date</th>
                                                    <th>Status</th>
                                                    <th>Course ID</th>
                                                    <th>Owner Email</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $query = "SELECT * FROM projects";
                                                $result = mysqli_query($db, $query);

                                                while ($row = mysqli_fetch_assoc($result)) {
                                                    echo "<tr>";
                                                    echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row['start_date']) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row['end_date']) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row['course_id']) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row['ownerEmail']) . "</td>";
                                                    echo "<td><a href='admin-project-details.php?id=" . htmlspecialchars($row['id']) . "' class='btn btn-primary btn-sm'>View Details</a></td>";
                                                    echo "</tr>";
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
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

<?php include_once('includes/footer-links.php'); ?>