<?php include_once('includes/header.php'); ?>
<?php include_once('includes/user_session.php'); ?>

<div class="layout-wrapper layout-content-navbar">
  <div class="layout-container">
    <?php include_once('includes/sidebar-main.php'); ?>
    <div class="layout-page">
      <?php include_once('includes/navbar.php'); ?>


      <!-- / Navbar -->

      <!-- Content wrapper -->
      <div class="content-wrapper">
      <div class="container">
      <div class="col-lg-12 mt-4 mb-4 order-0">
        

      <?php include_once("dashboard-welcome-card.php"); ?>
      <?php include_once("dashboard-content-cards.php"); ?>
      <?php include_once('dashboard-calendar.php'); ?>
      <?php include_once('dashboard-todo.php'); ?>
      
        

     </div>
        <!-- Footer -->
        
        <?php include_once('includes/footer.php'); ?>
        <!-- / Footer -->

        <div class="content-backdrop fade"></div>
      </div>
    </div>
  </div>

  <div class="layout-overlay layout-menu-toggle"></div>
</div>

<?php include_once('includes/footer-links.php'); ?>


