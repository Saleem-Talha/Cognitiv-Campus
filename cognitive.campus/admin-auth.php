<?php

include_once('includes/db-connect.php');
include_once('includes/header.php');
include_once('includes/validation.php');
include_once('includes/user_session.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.php");
    exit();
}

// Check if the logged-in user's email matches the required email
$admin_id = $_SESSION['admin_id'];
$stmt = $db->prepare("SELECT email FROM users WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
//$userEmail = $userInfo->getEmail();


if ($userEmail !== 'saleemtalha967@gmail.com') {
    header("Location: dashboard.php");
    exit();
}

function getPlanAmount($plan) {
    // Defined plan prices (in cents)
    $prices = [
        'standard' => 2000,
        'pro' => 4000
    ];
    return $prices[$plan] ?? 0;
}
?>