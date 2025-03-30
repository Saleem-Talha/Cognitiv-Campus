<?php

// Include the database connection file
include_once("includes/db-connect.php");

// Check if the 'id' parameter is set in the POST request
if (isset($_POST['id'])) {
    // Start transaction to ensure both operations succeed or fail together
    $db->begin_transaction();

    try {
        // Prepare and execute query to delete notes associated with the course
        $delete_notes = $db->prepare("DELETE FROM notes_course WHERE courseId = ?");
        $delete_notes->bind_param("s", $_POST['id']);
        $delete_notes->execute();
        $delete_notes->close();

        // Prepare and execute query to delete the course
        $delete_course = $db->prepare("DELETE FROM own_course WHERE id = ?");
        $delete_course->bind_param("s", $_POST['id']);
        $delete_course->execute();
        $delete_course->close();

        // If both operations are successful, commit the transaction
        $db->commit();
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Course and associated notes deleted successfully'
        ]);

    } catch (Exception $e) {
        // If any error occurs, rollback the transaction
        $db->rollback();
        
        echo json_encode([
            'status' => 'error',
            'message' => 'Error: ' . $e->getMessage()
        ]);
    } finally {
        // Close the database connection
        $db->close();
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Course ID not provided'
    ]);
}