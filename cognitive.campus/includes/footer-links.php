<!-- Core JS -->
<!-- build:js assets/vendor/js/core.js -->

<script src="assets/vendor/libs/jquery/jquery.js"></script> <!-- jQuery library -->
<script src="assets/vendor/libs/popper/popper.js"></script> <!-- Popper.js for tooltips and popovers -->
<script src="assets/vendor/js/bootstrap.js"></script> <!-- Bootstrap JavaScript components -->
<script src="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script> <!-- Custom scrollbar library -->
<script src="assets/vendor/js/menu.js"></script> <!-- Menu related JavaScript -->

<!-- endbuild -->

<!-- Vendors JS -->
<script src="assets/vendor/libs/apex-charts/apexcharts.js"></script> <!-- ApexCharts library for charts -->

<!-- Main JS -->
<script src="assets/js/main.js"></script> <!-- Main JavaScript file for custom scripts -->

<!-- Page JS -->
<script src="assets/js/dashboards-analytics.js"></script> <!-- JavaScript for dashboard analytics -->

<!-- GitHub Buttons -->
<script async defer src="https://buttons.github.io/buttons.js"></script> <!-- GitHub buttons script -->

<!-- Select2 -->
<script src="assets/vendor/libs/select2/js/select2.min.js"></script> <!-- Select2 library for enhanced select boxes -->

<!-- Datatables -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css"> <!-- Datatables CSS -->
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script> <!-- Datatables core JS -->
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script> <!-- Datatables Bootstrap integration -->

<!-- TinyMCE -->
<script src="https://cdn.tiny.cloud/1/p0w0012foe7hsylpq76c2uabfw7zulc7tw3oud8j37601rxe/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script> <!-- TinyMCE editor -->

<script>
  // Initialize Bootstrap tooltips
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
  var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
  })
</script>

</body>
</html>
