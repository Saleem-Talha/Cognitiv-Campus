<?php
// success.php
require_once 'vendor/autoload.php';
require_once 'includes/db-connect.php';
include("includes/validation.php");
include("includes/header.php");

// Get parameters from URL
$new_plan = $_GET['plan'];
$userEmail = urldecode($_GET['email']);

// Get current price from offers table
function getPlanPrice($plan) {
    global $db;
    
    $sql = "SELECT regular_price, discount_price FROM offers 
            WHERE plan = ? 
            AND status = 'active' 
            AND CURRENT_DATE BETWEEN start_date AND end_date
            LIMIT 1";
    
    $stmt = $db->prepare($sql);
    $stmt->bind_param("s", $plan);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Use discount price if available, otherwise use regular price
        return $row['discount_price'] ?? $row['regular_price'];
    }
    return null;
}

// Update the user's plan in both tables
function updateUserSubscription($email, $new_plan, $price) {
    global $db;
    
    try {
        $db->begin_transaction();
        
        // Update users table
        $sql1 = "UPDATE users SET plan = ? WHERE email = ?";
        $stmt1 = $db->prepare($sql1);
        $stmt1->bind_param("ss", $new_plan, $email);
        $stmt1->execute();
        
        // Get current date and calculate end date (assume monthly subscription)
        $start_date = date('Y-m-d');
        $end_date = date('Y-m-d', strtotime('+1 month'));
        
        // Check if there's an existing subscription
        $sql2 = "SELECT id FROM subscription_plan WHERE userEmail = ? AND end_date >= CURRENT_DATE";
        $stmt2 = $db->prepare($sql2);
        $stmt2->bind_param("s", $email);
        $stmt2->execute();
        $result = $stmt2->get_result();
        
        if ($result->num_rows > 0) {
            // Update existing subscription
            $sql3 = "UPDATE subscription_plan 
                    SET plan = ?, price = ?, updated_on = NOW(), 
                        start_date = ?, end_date = ?
                    WHERE userEmail = ? AND end_date >= CURRENT_DATE";
            $stmt3 = $db->prepare($sql3);
            $stmt3->bind_param("sdsss", $new_plan, $price, $start_date, $end_date, $email);
        } else {
            // Insert new subscription
            $sql3 = "INSERT INTO subscription_plan 
                    (userEmail, start_date, end_date, plan, price, updated_on)
                    VALUES (?, ?, ?, ?, ?, NOW())";
            $stmt3 = $db->prepare($sql3);
            $stmt3->bind_param("ssssd", $email, $start_date, $end_date, $new_plan, $price);
        }
        
        $stmt3->execute();
        $db->commit();
        return true;
        
    } catch (Exception $e) {
        $db->rollback();
        error_log("Failed to update subscription for user: " . $email . ". Error: " . $e->getMessage());
        return false;
    }
}

// Verify user is authenticated
$userInfo = getUserInfo();
if (!$userInfo || $userInfo['email'] !== $userEmail) {
    header('Location: index.php');
    exit();
}

// Get the current price for the plan
$price = getPlanPrice($new_plan);
if ($price === null) {
    error_log("Failed to get price for plan: " . $new_plan);
    $success = false;
} else {
    // Update the subscription
    $success = updateUserSubscription($userEmail, $new_plan, $price);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Billing - Success</title>
</head>
<body>
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        <?php include_once('includes/sidebar-main.php'); ?>
        <div class="layout-page">
            <?php include_once('includes/navbar.php'); ?>
            <div class="content-wrapper">
            <div class="container-xxl flex-grow-1 container-p-y">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-center mb-4">
                                <h3 class="mb-2">Billing <?php echo $success ? 'Successful' : 'Error'; ?></h3>
                                <?php if ($success): ?>
                                    <div class="d-flex flex-column gap-3">
                                        <div class="alert alert-primary bg-primary text-white">
                                            <p class="mb-2">Congratulations! You have successfully updated your plan to <strong><?= htmlspecialchars($new_plan) ?></strong>.</p>
                                            <p class="mb-0">Your subscription has been updated with the following details:</p>
                                        </div>
                                        
                                        <div class="card bg-lighter">
                                            <div class="card-body">
                                                <ul class="list-group list-group-flush">
                                                    <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                                                        <span>Plan</span>
                                                        <span class="fw-semibold"><?= htmlspecialchars($new_plan) ?></span>
                                                    </li>
                                                    <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                                                        <span>Price</span>
                                                        <span class="fw-semibold">$<?= number_format($price, 2) ?></span>
                                                    </li>
                                                    <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                                                        <span>Start Date</span>
                                                        <span class="fw-semibold"><?= date('F j, Y') ?></span>
                                                    </li>
                                                    <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                                                        <span>End Date</span>
                                                        <span class="fw-semibold"><?= date('F j, Y', strtotime('+1 month')) ?></span>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-danger">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bx bx-error-circle fs-3 me-2"></i>
                                            <p class="mb-0">There was an error updating your plan. Please contact support.</p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="mt-4">
                                    <a href="billing.php" class="btn btn-primary">
                                        <span class="tf-icons bx bx-home me-2"></span>Go to Billing
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>