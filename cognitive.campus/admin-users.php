<?php include_once('includes/header.php'); ?>
<?php include_once('includes/db-connect.php'); 
include_once('admin-auth.php'); 
include_once('includes/validation.php'); 

?>
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        <?php include_once('includes/admin-sidebar.php'); ?>
        <div class="layout-page">
            <?php include_once('includes/navbar.php'); ?>
            <div class="content-wrapper">

                <div class="container-xxl flex-grow-1 container-p-y">
                    <h4 class="py-3 mb-4"><span class="text-muted fw-light">Admin /</span> Users</h4>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">All Users</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Email</th>
                                                    <th>Name</th>
                                                    <th>Plan</th>
                                                    <th>Picture</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                
                                                $query = "SELECT id, email, name, plan, picture FROM users";
                                                $result = mysqli_query($db, $query);

                                                while ($row = mysqli_fetch_assoc($result)) {
                                                    echo "<tr>";
                                                    echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row['plan']) . "</td>";
                                                    // Check if the picture is a URL or a local file
                                                    if (filter_var($row['picture'], FILTER_VALIDATE_URL)) {
                                                        echo "<td><img src='" . htmlspecialchars($row['picture']) . "' alt='User Picture' class='img-thumbnail' width='50'></td>";
                                                    } else {
                                                        echo "<td><img src='img/pfps/" . htmlspecialchars($row['picture']) . "' alt='User Picture' class='img-thumbnail' width='50'></td>";
                                                    }
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