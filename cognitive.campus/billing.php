<?php
// billing.php
require_once 'vendor/autoload.php';
include("includes/db-connect.php");
include("includes/validation.php");
include("includes/header.php");

// Initialize Stripe
$stripeKey = STRIPE_API_KEY;
\Stripe\Stripe::setApiKey($stripeKey);

// Get current user info
$userInfo = getUserInfo();
if (!$userInfo) {
    header('Location: index.php');
    exit();
}

$userEmail = $userInfo['email'];
$current_plan = $userInfo['plan'];




// Function to update user's plan
function updateUserPlan($userEmail, $plan, $price) {
    global $db;
    
    // Start transaction
    $db->begin_transaction();
    
    try {
        // Update users table
        $updateUserQuery = "UPDATE users SET plan = ? WHERE email = ?";
        $stmt = $db->prepare($updateUserQuery);
        $stmt->bind_param("ss", $plan, $userEmail);
        $stmt->execute();

        // Calculate dates
        $startDate = date('Y-m-d H:i:s');
        $endDate = date('Y-m-d H:i:s', strtotime('+30 days'));

        // Insert into subscription_plan table
        $insertSubQuery = "INSERT INTO subscription_plan 
                          (userEmail, plan, start_date, end_date, updated_on, price) 
                          VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($insertSubQuery);
        $stmt->bind_param("sssssd", 
            $userEmail, 
            $plan, 
            $startDate, 
            $endDate, 
            $startDate, 
            $price
        );
        $stmt->execute();

        // Commit transaction
        $db->commit();
        return true;
    } catch (Exception $e) {
        // Rollback transaction on error
        $db->rollback();
        error_log("Error updating subscription: " . $e->getMessage());
        return false;
    }
}



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

// Handle form submission for plan upgrade
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['plan'])) {
    $new_plan = $_POST['plan'];
    
    if (!array_key_exists($new_plan, $plans)) {
        die('Invalid plan selected');
    }
    
    $metadata = [
        'user_email' => $userEmail
    ];

    try {
        $checkout_session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => $plans[$new_plan]['name'],
                        'metadata' => $metadata,
                    ],
                    'unit_amount' => $plans[$new_plan]['price'],
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'client_reference_id' => $userEmail,
            'customer_email' => $userEmail,
            'metadata' => $metadata,
            'success_url' => 'http://localhost/cognitive.campus/billing-success.php?plan=' . $new_plan . '&email=' . urlencode($userEmail),
            'cancel_url' => 'http://localhost/cognitive.campus/billing.php?status=cancelled',
        ]);

        header('Location: ' . $checkout_session->url);
        exit;
    } catch (Exception $e) {
        error_log("Error creating checkout session: " . $e->getMessage());
        die('Error processing payment. Please try again later.');
    }
}

// Function to check and update expired subscriptions
function checkAndUpdateExpiredSubscription($userEmail) {
    global $db;
    
    // Get current subscription details
    $query = "SELECT sp.* 
              FROM subscription_plan sp
              WHERE sp.userEmail = ?
              ORDER BY sp.updated_on DESC 
              LIMIT 1";
              
    $stmt = $db->prepare($query);
    $stmt->bind_param("s", $userEmail);
    $stmt->execute();
    $result = $stmt->get_result();
    $subscription = $result->fetch_assoc();
    
    if (!$subscription) {
        return null;
    }
    
    $endDate = strtotime($subscription['end_date']);
    $currentDate = time();
    
    // If subscription has expired
    if ($currentDate > $endDate) {
        $db->begin_transaction();
        
        try {
            // Update user's plan to basic
            $updateUserQuery = "UPDATE users SET plan = 'basic' WHERE email = ?";
            $stmt = $db->prepare($updateUserQuery);
            $stmt->bind_param("s", $userEmail);
            $stmt->execute();
            
            // Insert new basic plan subscription
            $startDate = date('Y-m-d H:i:s');
            $newEndDate = date('Y-m-d H:i:s', strtotime('+30 days'));
            
            $insertSubQuery = "INSERT INTO subscription_plan 
                              (userEmail, plan, start_date, end_date, updated_on, price) 
                              VALUES (?, 'basic', ?, ?, ?, 0)";
            $stmt = $db->prepare($insertSubQuery);
            $stmt->bind_param("ssss", 
                $userEmail,
                $startDate,
                $newEndDate,
                $startDate
            );
            $stmt->execute();
            
            $db->commit();
            return ['status' => 'expired', 'new_plan' => 'basic'];
        } catch (Exception $e) {
            $db->rollback();
            error_log("Error updating expired subscription: " . $e->getMessage());
            return null;
        }
    }
    
    // Calculate remaining days
    $daysRemaining = ceil(($endDate - $currentDate) / (60 * 60 * 24));
    return [
        'status' => 'active',
        'days_remaining' => $daysRemaining,
        'end_date' => $subscription['end_date'],
        'plan' => $subscription['plan']
    ];
}

// Add this code right after getting user info
$subscriptionStatus = checkAndUpdateExpiredSubscription($userEmail);
if ($subscriptionStatus && $subscriptionStatus['status'] === 'expired') {
    $current_plan = 'basic'; // Update current plan if expired
}

?>

<?php include_once('includes/header.php'); ?>
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        <?php include_once('includes/sidebar-main.php'); ?>
        <div class="layout-page">
            <?php include_once('includes/navbar.php'); ?>
            <?php include_once('includes/settings-bar.php'); ?>
            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">
                    

                    <!-- Breadcrumb -->
                    <h4 class="fw-bold py-3 mb-4">
                        <span class="text-muted fw-light">Account Settings /</span> Billing & Plans
                    </h4>

                    <!-- Current Plan Info -->
                    <div class="card mb-4">
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h5 class="card-title">Current Plan</h5>
                                    <p class="card-text">Your current plan is <span class="fw-bold text-primary"><?php echo ucfirst($current_plan); ?></span></p>
                                    <?php if ($subscriptionStatus && $subscriptionStatus['status'] === 'active' && $current_plan !== 'basic'): ?>
                                        <div class="alert alert-primary mt-2">
                                            <i class="bx bx-time me-2"></i>
                                            <?php if ($subscriptionStatus['days_remaining'] > 1): ?>
                                                Your subscription will expire in <?php echo $subscriptionStatus['days_remaining']; ?> days
                                            <?php else: ?>
                                                Your subscription will expire tomorrow
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    <p class="text-muted mb-0"><?php echo $plans[$current_plan]['description']; ?></p>
                                </div>
                                <div class="badge bg-label-primary rounded-pill">
                                    <?php echo ($subscriptionStatus && $subscriptionStatus['status'] === 'active') ? 'Active' : 'Basic'; ?>
                                </div>
                            </div>
                            <a href="billing-offer.php" class="btn btn-primary btn-sm mt-3" style="float:right;">View Offers</a>
                        </div>
                    </div>
                    </div>

                    

                    <!-- Available Plans -->
                    <div class="row mb-4">
                        <?php foreach ($plans as $plan_key => $plan_details): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card h-100">
                                    <div class="card-header d-flex justify-content-between">
                                        <div>
                                            <h5 class="card-title mb-0"><?php echo $plan_details['name']; ?></h5>
                                            <small class="text-muted"><?php echo $plan_details['description']; ?></small>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="text-center mb-4">
                                            <div class="mb-2 d-flex align-items-center justify-content-center">
                                                <?php if ($plan_details['price'] === 'Free'): ?>
                                                    <h1 class="display-5 mb-0">Free</h1>
                                                <?php else: ?>
                                                    <h1 class="display-5 mb-0">$<?php echo number_format($plan_details['price']/100, 2); ?></h1>
                                                <?php endif; ?>
                                            </div>
                                            <small class="text-muted">per <?php echo $plan_details['period']; ?></small>
                                        </div>

                                        <ul class="list-unstyled mb-4">
                                            <?php foreach ($plan_details['features'] as $feature): ?>
                                                <li class="mb-2">
                                                    <span class="badge badge-center rounded-pill bg-label-primary me-2">
                                                        <i class="bx bx-check"></i>
                                                    </span>
                                                    <?php echo $feature; ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>

                                        <?php if ($plan_key !== $current_plan && $plan_key !== 'basic'): ?>
                                            <form method="post" action="">
                                                <input type="hidden" name="plan" value="<?php echo $plan_key; ?>">
                                                <button type="submit" class="btn btn-primary d-grid w-100">
                                                    Upgrade
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                </div>
                <?php include_once('includes/footer.php'); ?>
                <div class="content-backdrop fade"></div>
            </div>
        </div>
    </div>
    <div class="layout-overlay layout-menu-toggle"></div>
</div>