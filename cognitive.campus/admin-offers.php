<?php
require_once 'includes/db-connect.php';
require_once 'admin-auth.php';
require_once 'includes/validation.php';

// First, add the status column to the offers table if it doesn't exist
$alter_table_query = "ALTER TABLE offers ADD COLUMN IF NOT EXISTS status ENUM('active', 'expired', 'deactive') DEFAULT 'active'";
$db->query($alter_table_query);

// Function to get regular price
function getRegularPrice($plan) {
    return getPlanAmount($plan) / 100;
}

// Function to update expired offers
function updateExpiredOffers($db) {
    $current_date = date('Y-m-d');
    $update_query = "UPDATE offers SET status = 'expired' WHERE end_date < ? AND status = 'active'";
    $stmt = $db->prepare($update_query);
    $stmt->bind_param("s", $current_date);
    $stmt->execute();
}

// Update expired offers on page load
updateExpiredOffers($db);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    $id = isset($_POST['id']) ? (int)$_POST['id'] : null;

    // Handle delete action separately
    if ($action === 'delete' && $id) {
        $stmt = $db->prepare("DELETE FROM offers WHERE id = ?");
        $stmt->bind_param("i", $id);
        if($stmt->execute()) {
            echo '<div class="alert alert-success">Offer deleted successfully!</div>';
        } else {
            echo '<div class="alert alert-danger">Error deleting offer.</div>';
        }
    }
    // Handle create and update actions
    else if ($action === 'create' || $action === 'update') {
        // Validate required fields
        $required_fields = ['plan', 'start_date', 'end_date', 'discount_price'];
        $missing_fields = false;
        
        foreach ($required_fields as $field) {
            if (!isset($_POST[$field]) || empty($_POST[$field])) {
                $missing_fields = true;
                break;
            }
        }

        if ($missing_fields) {
            echo '<div class="alert alert-danger">All fields are required.</div>';
        } else {
            $plan = $_POST['plan'];
            $start_date = $_POST['start_date'];
            $end_date = $_POST['end_date'];
            $regular_price = getRegularPrice($plan);
            $discount_price = (float)$_POST['discount_price'];
            $status = isset($_POST['status']) ? $_POST['status'] : 'active';

            // Validate dates
            $current_date = date('Y-m-d');
            if ($start_date < $current_date && $action == 'create') {
                echo '<div class="alert alert-danger">Start date cannot be in the past</div>';
            } elseif ($end_date <= $start_date) {
                echo '<div class="alert alert-danger">End date must be after start date</div>';
            } else {
                // If end date is past, automatically set status to expired
                if ($end_date < $current_date) {
                    $status = 'expired';
                }

                if ($action == 'create') {
                    $stmt = $db->prepare("INSERT INTO offers (plan, start_date, end_date, regular_price, discount_price, status) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("sssdds", $plan, $start_date, $end_date, $regular_price, $discount_price, $status);
                    if($stmt->execute()) {
                        echo '<div class="alert alert-success">Offer created successfully!</div>';
                    } else {
                        echo '<div class="alert alert-danger">Error creating offer.</div>';
                    }
                } elseif ($action == 'update' && $id) {
                    $stmt = $db->prepare("UPDATE offers SET plan = ?, start_date = ?, end_date = ?, regular_price = ?, discount_price = ?, status = ? WHERE id = ?");
                    $stmt->bind_param("sssddsi", $plan, $start_date, $end_date, $regular_price, $discount_price, $status, $id);
                    if($stmt->execute()) {
                        echo '<div class="alert alert-success">Offer updated successfully!</div>';
                    } else {
                        echo '<div class="alert alert-danger">Error updating offer.</div>';
                    }
                }
            }
        }
    }
}

// Fetch all offers
$result = $db->query("SELECT * FROM offers ORDER BY start_date DESC");
$offers = $result->fetch_all(MYSQLI_ASSOC);

// Handle success/error messages for email sending
if (isset($_GET['success'])) {
    echo '<div class="alert alert-primary alert-dismissible" role="alert">
            Offer emails sent successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>';
    $notice_message = "An offer mail received! Go and check out or visit the billing page";
    $type = "Offer";
    $insert_notification = $db->query("INSERT INTO notice (userEmail, message, type) VALUES ('$userEmail', '$notice_message', '$type')");
}
if (isset($_GET['error'])) {
    echo '<div class="alert alert-danger alert-dismissible" role="alert">
            Error sending emails: ' . htmlspecialchars($_GET['error']) . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Offers</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- Sneat CSS -->
    <link rel="stylesheet" href="assets/vendor/css/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="assets/vendor/css/theme-default.css" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="assets/css/demo.css" />
    <style>
        .select2-container {
            width: 100% !important;
        }
    </style>
</head>
<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include_once('includes/admin-sidebar.php'); ?>
            <div class="layout-page">
                <?php include_once('includes/navbar.php'); ?>
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <h4 class="py-3 mb-4"><span class="text-muted fw-light">Admin /</span> Offers</h4>

                        <!-- Offer Form -->
                        <div class="card mb-4">
                            <h5 class="card-header">Add/Edit Offer</h5>
                            <div class="card-body">
                                <form method="POST" id="offerForm">
                                    <input type="hidden" name="action" value="create">
                                    <input type="hidden" name="id" value="">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="plan" class="form-label">Plan</label>
                                            <select class="form-select" id="plan" name="plan" required>
                                                <option value="standard">Standard</option>
                                                <option value="pro">Pro</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="status" class="form-label">Status</label>
                                            <select class="form-select" id="status" name="status" required>
                                                <option value="active">Active</option>
                                                <option value="deactive">Deactive</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="start_date" class="form-label">Start Date</label>
                                            <input type="date" class="form-control" id="start_date" name="start_date" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="end_date" class="form-label">End Date</label>
                                            <input type="date" class="form-control" id="end_date" name="end_date" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="discount_price" class="form-label">Discount Price</label>
                                        <input type="number" step="0.01" class="form-control" id="discount_price" name="discount_price" required>
                                    </div>
                                    <div class="mt-3">
                                        <button type="submit" class="btn btn-primary">Submit</button>
                                        <button type="button" class="btn btn-outline-primary" id="resetForm">Reset</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Offers Table -->
                        <div class="card mb-4">
                            <h5 class="card-header">Current Offers</h5>
                            <div class="table-responsive text-nowrap">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Plan</th>
                                            <th>Start Date</th>
                                            <th>End Date</th>
                                            <th>Regular Price</th>
                                            <th>Discount Price</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($offers as $offer): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($offer['id']); ?></td>
                                            <td><?php echo htmlspecialchars($offer['plan']); ?></td>
                                            <td><?php echo htmlspecialchars($offer['start_date']); ?></td>
                                            <td><?php echo htmlspecialchars($offer['end_date']); ?></td>
                                            <td><?php echo htmlspecialchars($offer['regular_price']); ?></td>
                                            <td><?php echo htmlspecialchars($offer['discount_price']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo $offer['status'] == 'active' ? 'success' : 
                                                        ($offer['status'] == 'expired' ? 'warning' : 'danger'); 
                                                ?>">
                                                    <?php echo htmlspecialchars($offer['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary edit-btn" 
                                                        data-offer='<?php echo json_encode($offer); ?>'>
                                                    Edit
                                                </button>
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo $offer['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger" 
                                                            onclick="return confirm('Are you sure you want to delete this offer?')">
                                                        Delete
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Email Offer Section -->
                        <div class="card">
                            <h5 class="card-header">Send Offer Emails</h5>
                            <div class="card-body">
                                <form method="POST" action="admin-send-offer-emails.php">
                                    <div class="mb-3">
                                        <label for="users" class="form-label">Select Users</label>
                                        <select class="form-select" id="users" name="users[]" multiple required>
                                            <?php
                                            $users = $db->query("SELECT id, email, name FROM users ORDER BY name");
                                            while ($user = $users->fetch_assoc()) {
                                                echo "<option value='" . htmlspecialchars($user['id']) . "'>" . 
                                                     htmlspecialchars($user['name']) . " (" . htmlspecialchars($user['email']) . ")</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="offer_select" class="form-label">Select Offer</label>
                                        <select class="form-select" id="offer_select" name="offer" required>
                                            <?php foreach ($offers as $offer): ?>
                                                <?php if ($offer['status'] == 'active'): ?>
                                                <option value="<?php echo htmlspecialchars($offer['id']); ?>">
                                                    <?php echo htmlspecialchars($offer['plan']) . ' - $' . 
                                                              htmlspecialchars($offer['discount_price']) . 
                                                              ' (Valid: ' . htmlspecialchars($offer['start_date']) . 
                                                              ' to ' . htmlspecialchars($offer['end_date']) . ')'; ?>
                                                </option>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Send Offer Emails</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Core JS -->
    <script src="assets/vendor/libs/jquery/jquery.js"></script>
    <script src="assets/vendor/libs/popper/popper.js"></script>
    <script src="assets/vendor/js/bootstrap.js"></script>
    <script src="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="assets/vendor/js/menu.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Main JS -->
    <script src="assets/js/main.js"></script>

    <script>
$(document).ready(function() {
    // Initialize Select2
    $('#users').select2({
        placeholder: 'Select users',
        allowClear: true
    });

    // Set minimum date for start_date and end_date
    var today = new Date().toISOString().split('T')[0];
    $('#start_date').attr('min', today);
    $('#end_date').attr('min', today);

    // Handle edit button click
    $('.edit-btn').click(function() {
        var offerData = $(this).data('offer');
        
        // Populate form with offer data
        $('input[name="action"]').val('update');
        $('input[name="id"]').val(offerData.id);
        $('#plan').val(offerData.plan);
        $('#start_date').val(offerData.start_date);
        $('#end_date').val(offerData.end_date);
        $('#discount_price').val(offerData.discount_price);
        $('#status').val(offerData.status);

        // Scroll to form
        $('html, body').animate({
            scrollTop: $("#offerForm").offset().top - 100
        }, 500);
    });

    // Handle reset button click
    $('#resetForm').click(function() {
        resetForm();
    });

    // Function to reset form
    function resetForm() {
        $('input[name="action"]').val('create');
        $('input[name="id"]').val('');
        $('#offerForm')[0].reset();
        $('#status').val('active');
    }

    // Date validation
    $('#start_date, #end_date').on('change', function() {
        var startDate = new Date($('#start_date').val());
        var endDate = new Date($('#end_date').val());
        
        if ($('#start_date').val() && $('#end_date').val()) {
            if (endDate <= startDate) {
                alert('End date must be after start date');
                $(this).val('');
            }
        }
    });

    // Validate form submission
    $('#offerForm').on('submit', function(e) {
        var startDate = new Date($('#start_date').val());
        var endDate = new Date($('#end_date').val());
        var discountPrice = parseFloat($('#discount_price').val());
        
        if (endDate <= startDate) {
            e.preventDefault();
            alert('End date must be after start date');
            return false;
        }

        if (discountPrice <= 0) {
            e.preventDefault();
            alert('Discount price must be greater than 0');
            return false;
        }
    });

    // Auto-update status based on dates
    function updateStatus() {
        var currentDate = new Date();
        $('.offer-status').each(function() {
            var row = $(this).closest('tr');
            var endDate = new Date(row.find('td:eq(3)').text());
            var status = row.find('td:eq(6)').find('.badge');
            
            if (endDate < currentDate && status.hasClass('bg-success')) {
                status.removeClass('bg-success').addClass('bg-warning');
                status.text('expired');
            }
        });
    }

    // Update status every minute
    updateStatus();
    setInterval(updateStatus, 60000);

    // Format date inputs for better browser compatibility
    function formatDate(date) {
        var d = new Date(date),
            month = '' + (d.getMonth() + 1),
            day = '' + d.getDate(),
            year = d.getFullYear();

        if (month.length < 2) 
            month = '0' + month;
        if (day.length < 2) 
            day = '0' + day;

        return [year, month, day].join('-');
    }

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    // Handle delete confirmation
    $('.delete-btn').on('click', function(e) {
        if (!confirm('Are you sure you want to delete this offer?')) {
            e.preventDefault();
        }
    });

    // Validate discount price input
    $('#discount_price').on('input', function() {
        var value = $(this).val();
        if (value <= 0) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });

    // Handle offer selection in email form
    $('#offer_select').on('change', function() {
        var selectedOffer = $(this).find('option:selected');
        if (selectedOffer.length) {
            var endDate = new Date(selectedOffer.data('end-date'));
            var today = new Date();
            if (endDate < today) {
                alert('Warning: This offer has expired. Please select an active offer.');
                $(this).val('');
            }
        }
    });
});
</script>