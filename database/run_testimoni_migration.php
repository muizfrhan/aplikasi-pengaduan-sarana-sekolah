<?php
$conn = new mysqli("localhost", "root", "", "spk");
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }
$sql = file_get_contents("C:\\xampp\\htdocs\\PSK\\database\\testimoni_migration.sql");
if ($conn->multi_query($sql)) {
    do { if ($result = $conn->store_result()) { $result->free(); } } while ($conn->next_result());
    echo "Table 'testimoni' created successfully.\n";
} else {
    echo "Error: " . $conn->error . "\n";
}
$conn->close();