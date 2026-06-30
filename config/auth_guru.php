<?php
function cek_guru(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['user_id'])) {
        redirect('../login.php');
        exit;
    }
    if ($_SESSION['role'] !== 'guru') {
        http_response_code(403);
        echo '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>Akses Ditolak</title>';
        echo '<script src="../assets/vendor/sweetalert2.min.js"></script>';
        echo '</head><body>';
        echo '<script>
        Swal.fire({
            icon: "error",
            title: "Akses Ditolak",
            text: "Anda tidak memiliki izin untuk mengakses halaman ini.",
            confirmButtonColor: "#dc3545",
            confirmButtonText: "Kembali"
        }).then(function() {
            window.location.href = "../login.php";
        });
        </script>';
        echo '</body></html>';
        exit;
    }
}
