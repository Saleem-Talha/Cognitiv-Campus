<?php
$current_page = basename($_SERVER['PHP_SELF']);
function is_active($page) {
  global $current_page;
  return $current_page === $page ? 'active' : '';
}

function is_open($pages) {
  global $current_page;
  return in_array($current_page, $pages) ? 'active open' : '';
}
?>
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
  <div class="app-brand demo">
    <a href="dashboard.php" class="app-brand-link">
      <span class="app-brand-text demo menu-text fw-bold text-uppercase">Campus
      </span>
    </a>

    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
      <i class="bx bx-chevron-left bx-sm align-middle"></i>
    </a>
  </div>

  <div class="menu-inner-shadow"></div>

  <ul class="menu-inner py-1">

  <ul class="menu-inner py-1">
    <li class="menu-item <?php echo is_active('dashboard.php'); ?>">
      <a href="dashboard.php" class="menu-link">
        <i class="menu-icon tf-icons bx bx-user"></i>
        <div data-i18n="Dashboard">Dashboard</div>
      </a>
    </li>
   
    <li class="menu-header small text-uppercase"><span class="menu-header-text">Links</span></li>

    <li class="menu-item <?php echo is_active('subject.php'); ?>">
      <a href="subject.php" class="menu-link">
        <i class="menu-icon tf-icons bx bx-book"></i>
        <div data-i18n="Subjects">Subjects</div>
      </a>
    </li>

    <li class="menu-item <?php echo is_active('project.php'); ?>">
      <a href="project.php" class="menu-link">
        <i class="menu-icon tf-icons bx bx-folder"></i>
        <div data-i18n="Projects">Projects</div>
      </a>
    </li>

    <li class="menu-item <?php echo is_active('notes-all.php'); ?>">
      <a href="notes-all.php" class="menu-link">
        <i class="menu-icon tf-icons bx bx-note"></i>
        <div data-i18n="All Notes">All Notes</div>
      </a>
    </li>

    <li class="menu-item <?php echo is_active('schedule.php'); ?>">
      <a href="schedule.php" class="menu-link">
        <i class="menu-icon tf-icons bx bx-calendar"></i>
        <div data-i18n="Schedule">Schedule</div>
      </a>
    </li>
    <li class="menu-item <?php echo is_active('pdf.php'); ?>">
      <a href="pdf.php" class="menu-link">
        <i class="menu-icon tf-icons bx bx-file-blank"></i>
        <div data-i18n="Schedule">PDF Summarizer</div>
      </a>
    </li>
    <li class="menu-item <?php echo is_active('student_analytics.php'); ?>">
      <a href="student_analytics.php" class="menu-link">
        <i class="menu-icon tf-icons bx bx-brain"></i>
        <div data-i18n="Schedule">Student  Analytics</div>
      </a>
    </li>


    
<li class="menu-header small text-uppercase"><span class="menu-header-text">Other</span></li>



    <li class="menu-item <?php echo is_active('project-requests.php'); ?>">
      <a href="project-requests.php" class="menu-link">
        <i class="menu-icon tf-icons bx bx-receipt"></i>
        <div data-i18n="Requests">Requests</div>
      </a>
    </li>

    <li class="menu-item <?php echo is_active('notifications.php'); ?>">
      <a href="notifications.php" class="menu-link">
        <i class="menu-icon tf-icons bx bx-bell"></i>
        <div data-i18n="Notifications">Notifications</div>
      </a>
    </li>


<li class="menu-header small text-uppercase"><span class="menu-header-text">Extra</span></li>



<li class="menu-item <?php echo is_active('feedback.php'); ?>">
  <a href="feedback.php" class="menu-link">
    <i class="menu-icon tf-icons bx bx-message-square-dots"></i>
    <div data-i18n="Basic">Feedbacks</div>
  </a>
</li>


<li class="menu-item <?php echo is_active('logout.php'); ?>">
  <a href="logout.php" class="menu-link">
    <i class="menu-icon tf-icons bx bx-log-out"></i>
    <div data-i18n="Basic">Logout</div>
  </a>
</li>

    

  </ul>
</aside>