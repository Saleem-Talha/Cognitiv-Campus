<?php
session_start(); // Start the session to access session variables
require_once 'includes/db-connect.php'; // Include the database connection script

// Check if the request method is POST and required POST parameters are set
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image']) && isset($_POST['course_id'])) {
    $courseId = $_POST['course_id']; // Retrieve course ID from POST data
    $userEmail = $_POST['user_email']; // Retrieve user email from POST data
    $uploadDir = 'img/'; // Directory to store uploaded images

    // Extract the file extension from the uploaded file
    $fileExtension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);

    // Generate a unique file name using course ID and current timestamp
    $newFileName = $courseId . '_' . time() . '.' . $fileExtension;
    $uploadFile = $uploadDir . $newFileName; // Full path to save the uploaded file

    // Check if the upload directory exists, create it if it does not
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true); // Create the directory with full permissions
    }

    // Move the uploaded file to the designated directory
    if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
        // Prepare an SQL statement to insert a new record into the course_images table
        $stmt = $db->prepare("INSERT INTO course_images (course_id, user_email, image_path) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $courseId, $userEmail, $uploadFile); // Bind parameters to the SQL query

        // Execute the prepared statement and check if it was successful
        if ($stmt->execute()) {
            // Output success response with the image path and a success message for swal
            echo json_encode(['success' => true, 'image_path' => $uploadFile, 'message' => 'Image uploaded successfully']);
        } else {
            // Output error response if database insert failed, with an error message for swal
            echo json_encode(['success' => false, 'message' => 'Database insert failed. Please try again.']);
        }
        $stmt->close(); // Close the prepared statement
    } else {
        // Output error response if file upload failed, with an error message for swal
        echo json_encode(['success' => false, 'message' => 'File upload failed. Please try again.']);
    }
} else {
    // Output error response if the request is invalid, with an error message for swal
    echo json_encode(['success' => false, 'message' => 'Invalid request. Please check your input and try again.']);
}
