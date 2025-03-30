<!DOCTYPE html>
<?php 
    // Include database connection script
    include_once('db-connect.php'); 

    // Include validation script
    include_once('validation.php'); 

    // Include encryption script
    include_once('utils.php'); 
    include_once('user_session.php'); 

?>

<html lang="en" class="light-style layout-menu-fixed layout-compact" dir="ltr" data-theme="theme-default" data-assets-path="assets/" data-template="vertical-menu-template-free">

<head>
    <!-- Meta tags for character encoding and responsive design -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <!-- Page title -->
    <title>Cognitive Campus</title>

    <!-- Meta description (can be filled in later) -->
    <meta name="description" content="" />

    <!-- Favicon with custom style -->
    <link rel="icon" type="image/x-icon" href="img/Logo/Cognitive Campus Logo.png" class="favicon-icon" />
    <style>
        .favicon-icon {
            border-radius: 25%;
        }
    </style>

    <!-- Fonts and Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="assets/vendor/fonts/boxicons.css" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="assets/vendor/css/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="assets/vendor/css/theme-default.css" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="assets/css/demo.css" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <link rel="stylesheet" href="assets/vendor/libs/apex-charts/apex-charts.css" />

    <!-- SweetAlert Library for custom alerts -->
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

     <!-- Include your Sneat template CSS here -->
     <link rel="stylesheet" href="assets/vendor/css/core.css">
    <link rel="stylesheet" href="assets/vendor/css/theme-default.css">
    <link rel="stylesheet" href="assets/css/demo.css">
    
    <!-- FullCalendar CSS -->
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />

    <!-- Select2 CSS for enhanced select dropdowns with custom styling -->
    <link href="assets/vendor/libs/select2/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container--default .select2-selection--single {
            height: 40px !important;
            padding: 5px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px !important;
            margin-right: 15px !important;
        }
    </style>

    <!-- JavaScript Helpers -->
    <script src="assets/vendor/js/helpers.js"></script>
    <!-- Theme Configuration -->
    <script src="assets/js/config.js"></script>
     <!-- Core JS -->
     <script src="assets/vendor/libs/jquery/jquery.js"></script>
    <script src="assets/vendor/libs/popper/popper.js"></script>
    <script src="assets/vendor/js/bootstrap.js"></script>
    
    <!-- FullCalendar JS -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>


</head>


<body>
    <!-- Body content goes here -->
</body>
</html>
