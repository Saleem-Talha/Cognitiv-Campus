<?php
include_once('includes/db-connect.php');
include_once('includes/header.php');

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $db->prepare("SELECT id, username, password FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password']) || $password === $row['password']) {
            $_SESSION['admin_id'] = $row['id'];
            $_SESSION['admin_username'] = $row['username'];
            
            // If the password is not hashed, update it
            if ($password === $row['password']) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $update_stmt = $db->prepare("UPDATE admin SET password = ? WHERE id = ?");
                $update_stmt->bind_param("si", $hashed_password, $row['id']);
                $update_stmt->execute();
                $update_stmt->close();
            }
            
            header("Location: admin-dashboard.php");
            exit();
        } else {
            $error_message = "Invalid username or password";
        }
    } else {
        $error_message = "Invalid username or password";
    }
    $stmt->close();
}
?>



<div class="content-wrapper">
    <div class=>

        <div class=>
     
            <div class="">
                <div class="container-xxl flex-grow-1 container-p-y">
              

                    <div class="row">
                        <div class="col-md-6 mx-auto">
                            <div class="card">
                                <div class="card-body">
                                    <h2 class="text-center mb-4">Admin Login</h2>
                                    <?php
                                    if (!empty($error_message)) {
                                        echo "<div class='alert alert-danger' role='alert'>$error_message</div>";
                                    }
                                    ?>
                                    <form method="POST" action="">
                                        <div class="mb-3">
                                            <label for="username" class="form-label">Username:</label>
                                            <input type="text" class="form-control" id="username" name="username" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="password" class="form-label">Password:</label>
                                            <input type="password" class="form-control" id="password" name="password" required>
                                        </div>
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-primary">Login</button>
                                        </div>
                                    </form>
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