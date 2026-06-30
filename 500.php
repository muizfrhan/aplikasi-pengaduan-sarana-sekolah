<?php
// ============================================================
// 500 Internal Server Error
// ============================================================
http_response_code(500);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Internal Server Error</title>
    <link href="assets/vendor/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/fontawesome-all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #F8FAFC; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .error-page { text-align: center; padding: 40px 20px; }
        .error-code { font-size: 150px; font-weight: 800; background: linear-gradient(135deg, #EF4444, #DC2626); -webkit-background-clip: text; -webkit-text-fill-color: transparent; line-height: 1; margin-bottom: 10px; }
        .error-title { font-size: 24px; font-weight: 700; color: #0F172A; margin-bottom: 10px; }
        .error-text { color: #64748B; font-size: 16px; margin-bottom: 30px; }
        .btn-back { background: linear-gradient(135deg, #2563EB, #38BDF8); border: none; border-radius: 12px; padding: 12px 30px; font-weight: 600; color: white; text-decoration: none; transition: all 0.3s; display: inline-block; }
        .btn-back:hover { transform: translateY(-2px); box-shadow: 0 10px 25px -5px rgba(37,99,235,0.4); color: white; }
        .illustration { font-size: 80px; color: rgba(239,68,68,0.1); margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="error-page">
        <div class="illustration"><i class="fas fa-exclamation-triangle"></i></div>
        <div class="error-code">500</div>
        <h1 class="error-title">Internal Server Error</h1>
        <p class="error-text">Maaf, terjadi kesalahan pada server. Silakan coba beberapa saat lagi.</p>
        <a href="index.php" class="btn-back"><i class="fas fa-home me-2"></i>Kembali ke Beranda</a>
    </div>
    <script src="assets/vendor/bootstrap.bundle.min.js"></script>
</body>
</html>
