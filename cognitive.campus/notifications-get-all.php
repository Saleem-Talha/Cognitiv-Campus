<?php
include_once('includes/db-connect.php');
include_once('includes/header.php');





// Fetch all notifications for the user
$notifications = $db->query("SELECT * FROM notice WHERE userEmail = '$userEmail' ORDER BY datetime DESC");

if ($notifications->num_rows > 0) {
    while ($row = $notifications->fetch_assoc()) {
        $message = htmlspecialchars($row['message']);
        $datetime = date('M d, Y h:i A', strtotime($row['datetime']));
        $type = htmlspecialchars($row['type']);
        
        echo "<div class='notification-item mb-3 border-bottom pb-2'>";
        echo "<p class='mb-1'><strong>{$type}:</strong> {$message}</p>";
        echo "<small class='text-muted'>{$datetime}</small>";
        echo "</div>";
    }
} else {
    echo "<p class='text-muted'>No notifications found.</p>";
}

$db->close();
?>
