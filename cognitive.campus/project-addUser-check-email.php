<?php
// Include necessary files
require_once("includes/db-connect.php");  // Database connection
require_once("includes/validation.php");  // Validation functions

$userInfo = getUserInfo();
$userEmail = $userInfo["email"];

// Initialize response array
$response = ['message' => '', 'canSend' => false];

// Check if email is submitted via POST
if (isset($_POST['email'])) {
    // Sanitize and validate email
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    // Additional email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = "<div class='alert alert-danger' role='alert'>
                                    <i class='bx bx-error-circle me-2'></i>Invalid email format.
                                </div>";
    } else {
        // Prepare the statement to check if the email exists
        $stmt = $db->prepare("SELECT COUNT(*) AS count FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row['count'] > 0) {
            // Email found, now check if it's not the current user's email
            if ($email !== $userEmail) {
                $response['message'] = "<div class='alert alert-success' role='alert'>
                                            <i class='bx bx-info-circle me-2'></i>You can send request.
                                        </div>";
                $response['canSend'] = true;
            } else {
                $response['message'] = "<div class='alert alert-warning' role='alert'>
                                            <i class='bx bx-error-circle me-2'></i>You cannot send a request to your own email.
                                        </div>";
            }
        } else {
            // Email not found in the database
            $response['message'] = "<div class='alert alert-primary' role='alert'>
                                        <i class='bx bx-info-circle me-2'></i>No Email Found.
                                    </div>";
        }

        $stmt->close();
    }
}

// Close the database connection
$db->close();

// Set the response header to JSON
header('Content-Type: application/json');

// Output the JSON-encoded response
echo json_encode($response);
?>