<?php
// ============================================================
// Konfigurasi Database
// Aplikasi Pengaduan Sarana Sekolah
// ============================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'spk');

$koneksi = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

mysqli_set_charset($koneksi, 'utf8mb4');

// Auto-create required tables if missing
mysqli_query($koneksi, "CREATE TABLE IF NOT EXISTS `kontak_messages` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `nama` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `no_hp` VARCHAR(20) DEFAULT NULL,
    `subjek` VARCHAR(200) NOT NULL,
    `kategori` VARCHAR(100) DEFAULT NULL,
    `pesan` TEXT NOT NULL,
    `lampiran` VARCHAR(255) DEFAULT NULL,
    `status` ENUM('pending','dibaca','dibalas') NOT NULL DEFAULT 'pending',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `status` (`status`),
    KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

function query(string $sql, array $params = []): mysqli_result|bool
{
    global $koneksi;
    $stmt = mysqli_prepare($koneksi, $sql);
    if (!$stmt) {
        die("Error prepared statement: " . mysqli_error($koneksi));
    }
    if (!empty($params)) {
        $types = '';
        $bindParams = [];
        $bindParams[] = &$types;
        foreach ($params as $key => $value) {
            if (is_int($value)) {
                $types .= 'i';
            } elseif (is_float($value) || is_double($value)) {
                $types .= 'd';
            } elseif (is_string($value)) {
                $types .= 's';
            } else {
                $types .= 'b';
            }
            $bindParams[] = &$params[$key];
        }
        call_user_func_array([$stmt, 'bind_param'], $bindParams);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
    return $result;
}

function fetch(mysqli_result $result): ?array
{
    return mysqli_fetch_assoc($result);
}

function fetchAll(mysqli_result $result): array
{
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    return $data;
}

function insert(string $sql, array $params = []): int
{
    global $koneksi;
    $stmt = mysqli_prepare($koneksi, $sql);
    if (!$stmt) {
        die("Error prepared statement: " . mysqli_error($koneksi));
    }
    if (!empty($params)) {
        $types = '';
        $bindParams = [];
        $bindParams[] = &$types;
        foreach ($params as $key => $value) {
            if (is_int($value)) {
                $types .= 'i';
            } elseif (is_float($value) || is_double($value)) {
                $types .= 'd';
            } elseif (is_string($value)) {
                $types .= 's';
            } else {
                $types .= 'b';
            }
            $bindParams[] = &$params[$key];
        }
        call_user_func_array([$stmt, 'bind_param'], $bindParams);
    }
    mysqli_stmt_execute($stmt);
    $id = mysqli_stmt_insert_id($stmt);
    mysqli_stmt_close($stmt);
    return $id;
}

function execute(string $sql, array $params = []): int
{
    global $koneksi;
    $stmt = mysqli_prepare($koneksi, $sql);
    if (!$stmt) {
        die("Error prepared statement: " . mysqli_error($koneksi));
    }
    if (!empty($params)) {
        $types = '';
        $bindParams = [];
        $bindParams[] = &$types;
        foreach ($params as $key => $value) {
            if (is_int($value)) {
                $types .= 'i';
            } elseif (is_float($value) || is_double($value)) {
                $types .= 'd';
            } elseif (is_string($value)) {
                $types .= 's';
            } else {
                $types .= 'b';
            }
            $bindParams[] = &$params[$key];
        }
        call_user_func_array([$stmt, 'bind_param'], $bindParams);
    }
    $result = mysqli_stmt_execute($stmt);
    $affected = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);
    return $affected;
}

function bersihkan(string $data): string
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function generate_csrf(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validasi_csrf(string $token): bool
{
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        return false;
    }
    return true;
}

function upload_foto(array $file, string $folder = '../assets/upload/'): array
{
    $targetDir = $folder;
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $allowedTypes = ['jpg', 'jpeg', 'png', 'webp'];
    $maxSize = 2 * 1024 * 1024;

    $fileName = $file['name'];
    $fileTmp = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];

    if ($fileError !== UPLOAD_ERR_OK) {
        return ['status' => false, 'message' => 'Error upload file'];
    }

    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    if (!in_array($fileExt, $allowedTypes)) {
        return ['status' => false, 'message' => 'Tipe file tidak diizinkan. Hanya JPG, JPEG, PNG, WEBP'];
    }

    if ($fileSize > $maxSize) {
        return ['status' => false, 'message' => 'Ukuran file maksimal 2 MB'];
    }

    $newFileName = date('YmdHis') . '_' . bin2hex(random_bytes(8)) . '.' . $fileExt;
    $targetFile = $targetDir . $newFileName;

    if (move_uploaded_file($fileTmp, $targetFile)) {
        return ['status' => true, 'file' => $newFileName];
    } else {
        return ['status' => false, 'message' => 'Gagal mengupload file'];
    }
}

function tgl_indonesia(string $date, bool $withTime = false): string
{
    $bulan = [
        1 => 'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember'
    ];
    $hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

    $t = strtotime($date);
    $h = $hari[date('w', $t)];
    $d = date('j', $t);
    $m = $bulan[(int)date('m', $t)];
    $y = date('Y', $t);

    if ($withTime) {
        $time = date('H:i:s', $t);
        return "$h, $d $m $y $time";
    }
    return "$d $m $y";
}

function generate_kode(): string
{
    global $koneksi;
    $prefix = 'ADU';
    $date = date('Ymd');
    $query = mysqli_query($koneksi, "SELECT MAX(id) as max_id FROM pengaduan");
    $row = mysqli_fetch_assoc($query);
    $maxId = $row['max_id'] ?? 0;
    $number = str_pad($maxId + 1, 4, '0', STR_PAD_LEFT);
    return $prefix . $date . $number;
}

function get_setting(?string $key = null): mixed
{
    global $koneksi;
    $result = mysqli_query($koneksi, "SELECT * FROM setting WHERE id = 1");
    $setting = mysqli_fetch_assoc($result);
    if ($key && isset($setting[$key])) {
        return $setting[$key];
    }
    return $setting;
}

function catat_aktivitas(int $user_id, string $aksi, ?string $keterangan = null): void
{
    $sql = "INSERT INTO aktivitas (user_id, aksi, keterangan) VALUES (?, ?, ?)";
    execute($sql, [$user_id, $aksi, $keterangan]);
}

function hitung(string $table, string $where = ''): int
{
    global $koneksi;
    if ($where) {
        $result = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM $table WHERE $where");
    } else {
        $result = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM $table");
    }
    if (!$result) return 0;
    $row = mysqli_fetch_assoc($result);
    return $row ? (int)$row['total'] : 0;
}

function alert(string $type, string $message): void
{
    $_SESSION['alert'] = ['type' => $type, 'message' => $message];
}

function tampilkan_alert(): string
{
    if (isset($_SESSION['alert'])) {
        $type = $_SESSION['alert']['type'];
        $message = $_SESSION['alert']['message'];
        unset($_SESSION['alert']);
        $title = match ($type) {
            'success' => 'Berhasil',
            'error' => 'Gagal',
            'warning' => 'Peringatan',
            default => 'Informasi'
        };
        return "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: '$type',
                    title: '$title',
                    text: '$message',
                    allowOutsideClick: false
                });
            });
        </script>";
    }
    return '';
}

function buat_notifikasi(int $userId, string $judul, string $pesan, string $link = '', string $jenis = ''): int
{
    $sql = "INSERT INTO notifications (user_id, judul, pesan, link, jenis) VALUES (?, ?, ?, ?, ?)";
    return insert($sql, [$userId, $judul, $pesan, $link, $jenis]);
}

function waktu_relatif(string $datetime): string
{
    $now = time();
    $t = strtotime($datetime);
    $diff = $now - $t;

    if ($diff < 60) return 'Baru saja';
    if ($diff < 3600) return floor($diff / 60) . ' menit lalu';
    if ($diff < 86400) return floor($diff / 3600) . ' jam lalu';
    if ($diff < 259200) return floor($diff / 86400) . ' hari lalu';
    return tgl_indonesia($datetime);
}

function redirect(string $url): never
{
    header("Location: $url");
    exit;
}
