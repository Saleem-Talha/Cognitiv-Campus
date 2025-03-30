<?php

/**
 * Navigation Menu Component
 * 
 * This file contains the sidebar navigation menu for the application.
 * It includes functions to determine the active page and menu items.
 */

// Get the current page filename
$current_page = basename($_SERVER['PHP_SELF']);

/**
 * Determines if a given page is the active page
 *
 * @param string $page The page to check
 * @return string Returns 'active' if it's the current page, otherwise an empty string
 */
function is_active($page)
{
    global $current_page;
    return $current_page === $page ? 'active' : '';
}

/**
 * Determines if any page in a given array is the active page
 *
 * @param array $pages An array of pages to check
 * @return string Returns 'active open' if any page in the array is current, otherwise an empty string
 */
function is_open($pages)
{
    global $current_page;
    return in_array($current_page, $pages) ? 'active open' : '';
}
?>


<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <!-- Application Brand -->
    <div class="app-brand demo">
        <a href="index.php" class="app-brand-link">
            
            <span class="app-brand-text demo menu-text fw-bold text-uppercase">Admin</span>
        </a>

        <!-- Mobile Toggle Button -->
        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <!-- Navigation Menu Items -->
    <ul class="menu-inner py-1">
        <!-- Dashboard -->
        <li class="menu-item <?php echo is_active('admin-dashboard.php'); ?>">
            <a href="admin-dashboard.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-user"></i>
                <div data-i18n="Dashboard">Dashboard</div>
            </a>
        </li>

        <!-- Links Section -->
        <li class="menu-header small text-uppercase"><span class="menu-header-text">Links</span></li>

        <!-- Subjects -->
        <li class="menu-item <?php echo is_active('admin-users.php'); ?>">
            <a href="admin-users.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-user"></i>
                <div data-i18n="Subjects">Users</div>
            </a>
        </li>

        <!-- Projects -->
        <li class="menu-item <?php echo is_active('admin-projects.php'); ?>">
            <a href="admin-projects.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-briefcase"></i>
                <div data-i18n="Projects">Projects</div>
            </a>
        </li>

        <!-- All Notes -->
        <li class="menu-item <?php echo is_active(''); ?>">
            <a href="admin-feedbacks.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-comment"></i>
                <div data-i18n="All Notes">Feedbacks</div>
            </a>
        </li>
        <!-- All Notes -->
        <li class="menu-item <?php echo is_active(''); ?>">
            <a href="admin-offers.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-gift"></i>
                <div data-i18n="All Notes">Offers</div>
            </a>
        </li>

        <!-- Other Section -->
        <li class="menu-header small text-uppercase"><span class="menu-header-text">Other</span></li>

        <!-- Project Requests -->
        <li class="menu-item <?php echo is_active('admin-change-password.php'); ?>">
            <a href="admin-change-password.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-receipt"></i>
                <div data-i18n="Requests">Change Password</div>
            </a>
        </li>
        <li class="menu-item <?php echo is_active('admin-generate-recommendations.php'); ?>">
            <a href="admin-generate-recommendations.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-cloud-upload"></i>
                <div data-i18n="Requests">Course Recommendations</div>
            </a>
        </li>
        <li class="menu-item <?php echo is_active('admin-generate--behavior-recommendations.php'); ?>">
            <a href="admin-generate--behavior-recommendations.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-cloud-upload"></i>
                <div data-i18n="Requests">Notes based Recommendations</div>
            </a>
        </li>

        

        <!-- Extra Section -->
        <li class="menu-header small text-uppercase"><span class="menu-header-text">Extra</span></li>

        <!-- Logout -->
        <li class="menu-item <?php echo is_active('logout.php'); ?>">
            <a href="logout.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-log-out"></i>
                <div data-i18n="Basic">Logout</div>
            </a>
        </li>
    </ul>
</aside>
