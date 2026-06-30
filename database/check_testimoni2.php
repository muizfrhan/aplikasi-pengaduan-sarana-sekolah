<?php
$conn = new mysqli("localhost", "root", "", "spk");
if ($conn->connect_error) { die("Connection failed"); }
echo "Database: " . $conn->query("SELECT DATABASE()")->fetch_row()[0] . "\n";
// Check all tables
$tables = $conn->query("SHOW TABLES");
echo "All tables:\n";
while ($row = $tables->fetch_row()) {
    echo "  - " . $row[0] . "\n";
}
$conn->close();
