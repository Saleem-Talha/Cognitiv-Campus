<?php
include_once('includes/db-connect.php');

$stmt = $db->prepare("SELECT id, password FROM admin");
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    if (!password_verify($row['password'], $row['password'])) {
        $hashed_password = password_hash($row['password'], PASSWORD_DEFAULT);
        $update_stmt = $db->prepare("UPDATE admin SET password = ? WHERE id = ?");
        $update_stmt->bind_param("si", $hashed_password, $row['id']);
        $update_stmt->execute();
        $update_stmt->close();
        echo "Updated password for admin ID: " . $row['id'] . "<br>";
    } else {
        echo "Password already hashed for admin ID: " . $row['id'] . "<br>";
    }
}

$stmt->close();
echo "All passwords have been checked and updated if necessary.";
?>