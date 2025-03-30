<?php
$current_page = basename($_SERVER['PHP_SELF']);
function is_settings_active($page) {
    global $current_page;
    return $current_page === $page ? 'active' : '';
}

// Check if user is authenticated through Google
$is_google_user = isset($_SESSION['access_token']);
?>

<div class="container">
    <!-- Settings Navigation -->
    <div class="row mt-3">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-body">
                    <ul class="nav nav-pills flex-column flex-sm-row mb-0">
                        <?php if (!$is_google_user): ?>
                        <li class="nav-item ms-2">
                            <a class="nav-link <?php echo is_settings_active('settings-account.php'); ?> d-flex align-items-center" href="settings-account.php">
                                <i class="menu-icon tf-icons bx bx-user me-2"></i>
                                <span>Account</span>
                            </a>
                        </li>
                        <li class="nav-item ms-2">
                            <a class="nav-link <?php echo is_settings_active('settings-security.php'); ?> d-flex align-items-center" href="settings-security.php">
                                <i class="menu-icon tf-icons bx bx-lock-alt me-2"></i>
                                <span>Security</span>
                            </a>
                        </li>
                        <?php endif; ?>
                        <li class="nav-item ms-2">
                            <a class="nav-link <?php echo is_settings_active('billing.php'); ?> d-flex align-items-center" href="billing.php">
                                <i class="menu-icon tf-icons bx bx-detail me-2"></i>
                                <span>Billing & Plans</span>
                            </a>
                        </li>
                        <li class="nav-item ms-2">
                            <a class="nav-link <?php echo is_settings_active('logout.php'); ?> d-flex align-items-center" href="logout.php">
                                <i class="bx bx-power-off me-2"></i>
                                <span>Log out</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Custom CSS for the settings navigation */
.nav-pills .nav-link {
    color: #697a8d;
    padding: 0.625rem 1.125rem;
    font-weight: 400;
    background: transparent;
    transition: all 0.2s ease-in-out;
}

.nav-pills .nav-link:hover {
    color: #696cff;
    background: #f6f7fe;
}

.nav-pills .nav-link.active {
    color: #696cff !important;
    background: #f6f7fe !important;
}

.nav-pills .nav-link i {
    font-size: 1.125rem;
}

.menu-icon {
    color: #697a8d;
}

.nav-link.active .menu-icon {
    color: #696cff;
}

/* Responsive adjustments */
@media (max-width: 767.98px) {
    .nav-pills .nav-link {
        padding: 0.625rem 0.75rem;
    }
    
    .nav-pills .nav-link span {
        display: none;
    }
    
    .nav-pills .nav-link i {
        margin-right: 0 !important;
    }
}
</style>