<?php
// success.php
require_once 'vendor/autoload.php';
require_once 'includes/db-connect.php';
include("includes/validation.php");
include("includes/header.php");

// Plans configuration array
$plans = [
    'basic' => [
        'name' => 'Basic',
        'price' => 'Free',
        'period' => 'lifetime',
        'description' => 'Essential features to get started.',
        'features' => [
            '5 Custom Projects',
            '3 Teamspace Projects',
            '10 Branches Per Project',
            '5 Friend Invites Per Project',
            'Uni Active Subjects up to 6',
            'Extra Subjects up to 6',
            'Feedback',
            'Course Notes',
            'Project Notes',
            '10 LLM Summarization Requests /Day',
            '50 LLM Chat Requests Per Day',
            '10 LLM Requests in Notes'
        ]
    ],
    'standard' => [
        'name' => 'Standard',
        'price' => 1999,
        'period' => 'month',
        'description' => 'Advanced features for growing businesses.',
        'features' => [
            '15 Custom Projects',
            '12 Teamspace Projects',
            '30 Branches Per Project',
            '10 Friend Invites Per Project',
            'Uni Active Subjects up to 18',
            'Extra Subjects up to 18',
            'Feedback',
            'Course Notes',
            'Project Notes',
            '20 LLM Summarization Requests /Day',
            '100 LLM Chat Requests Per Day',
            '20 LLM Requests in Notes'
        ]
    ],
    'pro' => [
        'name' => 'Pro',
        'price' => 4999,
        'period' => 'month',
        'description' => 'Comprehensive features for enterprises.',
        'features' => [
            'Unlimited Custom Projects',
            'Unlimited Teamspace Projects',
            'Unlimited Branches Per Project',
            'Unlimited Friend Invites Per Project',
            'Unlimited Uni Active Subjects',
            'Unlimited Extra Subjects',
            'Feedback',
            'Course Notes',
            'Project Notes',
            '100 LLM Summarization Requests /Day',
            '500 LLM Chat Requests Per Day',
            '100 LLM Requests in Notes'
        ]
    ]
];

// Get parameters from URL
$new_plan = $_GET['plan'];
$userEmail = urldecode($_GET['email']);

// Update the user's subscription details
function updateUserSubscription($email, $new_plan, $plans) {
    global $db;
    
    try {
        $db->begin_transaction();
        
        // Update users table
        $sql1 = "UPDATE users SET plan = ? WHERE email = ?";
        $stmt1 = $db->prepare($sql1);
        $stmt1->bind_param("ss", $new_plan, $email);
        $stmt1->execute();
        
        // Get current date and calculate end date
        $start_date = date('Y-m-d');
        $end_date = ($plans[$new_plan]['period'] === 'lifetime') 
            ? '2099-12-31' // Far future date for lifetime plans
            : date('Y-m-d', strtotime('+1 month'));
            
        // Convert price from cents to dollars for storage
        $price = ($plans[$new_plan]['price'] === 'Free') 
            ? 0 
            : $plans[$new_plan]['price'] / 100;
        
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

// Validate the plan exists
if (!isset($plans[$new_plan])) {
    error_log("Invalid plan selected: " . $new_plan);
    $success = false;
} else {
    // Update the subscription
    $success = updateUserSubscription($userEmail, $new_plan, $plans);
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
                                        <p class="mb-2">Congratulations! You have successfully updated your plan to <strong><?= htmlspecialchars($plans[$new_plan]['name']) ?></strong>.</p>
                                        <p class="mb-0">Your subscription has been updated with the following details:</p>
                                    </div>
                                    
                                    <div class="card bg-lighter">
                                        <div class="card-body">
                                            <ul class="list-group list-group-flush">
                                                <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                                                    <span>Plan</span>
                                                    <span class="fw-semibold"><?= htmlspecialchars($plans[$new_plan]['name']) ?></span>
                                                </li>
                                                <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                                                    <span>Price</span>
                                                    <span class="fw-semibold"><?= $plans[$new_plan]['price'] === 'Free' ? 'Free' : '$' . number_format($plans[$new_plan]['price'] / 100, 2) ?></span>
                                                </li>
                                                <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                                                    <span>Billing Period</span>
                                                    <span class="fw-semibold"><?= ucfirst($plans[$new_plan]['period']) ?></span>
                                                </li>
                                                <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                                                    <span>Start Date</span>
                                                    <span class="fw-semibold"><?= date('F j, Y') ?></span>
                                                </li>
                                                <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                                                    <span>End Date</span>
                                                    <span class="fw-semibold"><?= $plans[$new_plan]['period'] === 'lifetime' ? 'Lifetime' : date('F j, Y', strtotime('+1 month')) ?></span>
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
</body>
</html>