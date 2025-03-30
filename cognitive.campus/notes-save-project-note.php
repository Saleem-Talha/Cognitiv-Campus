<?php
include_once('includes/db-connect.php'); // Make sure this file connects to your database

if (isset($_POST['note_id']) && isset($_POST['content'])) {
    $note_id = $_POST['note_id'];
    $content = $_POST['content'];

    // Sanitize and validate input
    $note_id = intval($note_id);
    $content = $db->real_escape_string($content);

    $query = "UPDATE notes_project SET content = '$content' WHERE id = $note_id";
    $result = $db->query($query);

    if ($result) {
        echo "Success";
    } else {
        echo "Error: " . $db->error;
    }
} else {
    echo "Error: Missing required parameters";
}
