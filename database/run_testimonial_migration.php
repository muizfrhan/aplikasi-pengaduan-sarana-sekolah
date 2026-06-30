<?php
require_once __DIR__ . '/../config/database.php';

// Check if table already exists
$check = mysqli_query($koneksi, "SHOW TABLES LIKE 'testimonials'");
if (mysqli_num_rows($check) > 0) {
    echo "Table 'testimonials' already exists.\n";
} else {
    $sql = "CREATE TABLE testimonials (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        nama VARCHAR(255) NOT NULL,
        kelas VARCHAR(100) DEFAULT NULL,
        foto VARCHAR(255) DEFAULT NULL,
        rating TINYINT(1) NOT NULL DEFAULT 5 COMMENT 'Rating 1-5',
        judul VARCHAR(255) NOT NULL,
        isi TEXT NOT NULL,
        foto_testimoni VARCHAR(255) DEFAULT NULL COMMENT 'Upload foto testimoni',
        status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
        alasan_tolak TEXT DEFAULT NULL,
        approved_by INT DEFAULT NULL,
        approved_at DATETIME DEFAULT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        INDEX idx_status (status),
        INDEX idx_created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

    if (mysqli_query($koneksi, $sql)) {
        echo "Table 'testimonials' created successfully.\n";
    } else {
        echo "Error creating table: " . mysqli_error($koneksi) . "\n";
        exit(1);
    }
}

// Create upload directories if not exist
$dirs = ['../assets/img/testimoni', '../assets/img/testimoni/foto'];
foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
        echo "Created directory: $dir\n";
    }
}

echo "Migration completed.\n";
