    <!-- Bootstrap JS -->
    <script src="../assets/vendor/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="../assets/vendor/jquery.min.js"></script>
    
    
    <!-- AOS Animation -->
    <script src="../assets/vendor/aos.js"></script>
    
    <!-- Chart.js -->
    <script src="../assets/vendor/chart.umd.min.js"></script>
    
    <!-- Custom JS -->
    <script src="../assets/js/main.js"></script>
    
    <?php if (isset($_SESSION['role'])): ?>
    <script src="../assets/js/admin.js"></script>
    <?php endif; ?>
    
    <!-- Alert -->
    <?= tampilkan_alert() ?>
    
    <script>
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });
    </script>
</body>
</html>
