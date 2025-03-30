<?php
// offer-billing.php
require_once 'vendor/autoload.php';
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

// Fetch active offers
$current_date = date('Y-m-d');
$offers_query = "SELECT * FROM offers 
                WHERE status = 'active' 
                AND start_date <= ? 
                AND end_date >= ?
                ORDER BY plan ASC";
$stmt = $db->prepare($offers_query);
$stmt->bind_param("ss", $current_date, $current_date);
$stmt->execute();
$result = $stmt->get_result();
$active_offers = $result->fetch_all(MYSQLI_ASSOC);

// Plans configuration array (for reference only)
$plans = [
    'basic' => [
        'name' => 'Basic',
        'price' => 'Free',
        'period' => 'lifetime',
        'description' => 'Essential features to get started.'
    ],
    'standard' => [
        'name' => 'Standard',
        'price' => 1999,
        'period' => 'month',
        'description' => 'Advanced features for growing businesses.'
    ],
    'pro' => [
        'name' => 'Pro',
        'price' => 4999,
        'period' => 'month',
        'description' => 'Comprehensive features for enterprises.'
    ]
];

// Handle form submission for plan upgrade
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['plan']) && isset($_POST['offer_id'])) {
    $new_plan = $_POST['plan'];
    $offer_id = $_POST['offer_id'];
    
    if (!array_key_exists($new_plan, $plans)) {
        die('Invalid plan selected');
    }
    
    // Verify offer is still valid
    $offer_query = "SELECT * FROM offers WHERE id = ? AND status = 'active' AND end_date >= CURRENT_DATE()";
    $stmt = $db->prepare($offer_query);
    $stmt->bind_param("i", $offer_id);
    $stmt->execute();
    $offer = $stmt->get_result()->fetch_assoc();
    
    if (!$offer) {
        die('Offer has expired or is no longer valid');
    }
    
    $metadata = [
        'user_email' => $userEmail,
        'offer_id' => $offer_id
    ];

    try {
        $checkout_session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => $plans[$new_plan]['name'] . ' (Special Offer)',
                        'metadata' => $metadata,
                    ],
                    'unit_amount' => intval($offer['discount_price'] * 100),
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'client_reference_id' => $userEmail,
            'customer_email' => $userEmail,
            'metadata' => $metadata,
            'success_url' => 'http://localhost/cognitive.campus/billing-offer-success.php?plan=' . $new_plan . '&email=' . urlencode($userEmail) . '&offer_id=' . $offer_id,
            'cancel_url' => 'http://localhost/cognitive.campus/offer-billing.php?status=cancelled',
        ]);

        header('Location: ' . $checkout_session->url);
        exit;
    } catch (Exception $e) {
        error_log("Error creating checkout session: " . $e->getMessage());
        die('Error processing payment. Please try again later.');
    }
}
?>

<?php include_once('includes/header.php'); ?>
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        <?php include_once('includes/sidebar-main.php'); ?>
        <div class="layout-page">
            <?php include_once('includes/navbar.php'); ?>
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <!-- Breadcrumb -->
            <h4 class="fw-bold py-3 mb-4">
                <span class="text-muted fw-light">Special Offers /</span> Limited Time Deals
            </h4>

            

            <!-- Special Offers Only -->
            <div class="row mb-4">
                <?php foreach ($active_offers as $offer): 
                    $plan_key = $offer['plan'];
                    if (!isset($plans[$plan_key])) continue; // Skip if plan doesn't exist
                    $plan_details = $plans[$plan_key];
                ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100 border-primary">
                            <div class="ribbon ribbon-top-right">
                                <span class="bg-primary">Special Offer</span>
                            </div>
                            
                            <div class="card-header d-flex justify-content-between">
                                <div>
                                    <h5 class="card-title mb-0"><?php echo $plan_details['name']; ?></h5>
                                    <small class="text-muted"><?php echo $plan_details['description']; ?></small>
                                </div>
                            </div>
                            
                            <div class="card-body">
                                <div class="text-center mb-4">
                                    <div class="mb-2">
                                        <h1 class="display-5 mb-0">
                                            <span class="text-decoration-line-through text-muted h4">
                                                $<?php echo number_format($plan_details['price']/100, 2); ?>
                                            </span>
                                            <span class="text-primary">
                                                $<?php echo number_format($offer['discount_price'], 2); ?>
                                            </span>
                                        </h1>
                                        <small class="text-danger">
                                            Offer ends <?php echo date('M d, Y', strtotime($offer['end_date'])); ?>
                                        </small>
                                    </div>
                                    <small class="text-muted">per <?php echo $plan_details['period']; ?></small>
                                </div>

                                <?php if ($plan_key !== $current_plan): ?>
                                    <form method="post" action="">
                                        <input type="hidden" name="plan" value="<?php echo $plan_key; ?>">
                                        <input type="hidden" name="offer_id" value="<?php echo $offer['id']; ?>">
                                        <button type="submit" class="btn btn-primary d-grid w-100">
                                            Get Special Offer
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

<style>
.ribbon {
    width: 150px;
    height: 150px;
    overflow: hidden;
    position: absolute;
}
.ribbon-top-right {
    top: -10px;
    right: -10px;
}
.ribbon-top-right::before,
.ribbon-top-right::after {
    border-top-color: transparent;
    border-right-color: transparent;
}
.ribbon-top-right::before {
    top: 0;
    left: 0;
}
.ribbon-top-right::after {
    bottom: 0;
    right: 0;
}
.ribbon span {
    position: absolute;
    display: block;
    width: 225px;
    padding: 8px 0;
    background-color: #3b71ca;
    box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
    color: #fff;
    text-shadow: 0 1px 1px rgba(0, 0, 0, 0.2);
    text-transform: uppercase;
    text-align: center;
    right: -25px;
    top: 30px;
    transform: rotate(45deg);
}
</style>