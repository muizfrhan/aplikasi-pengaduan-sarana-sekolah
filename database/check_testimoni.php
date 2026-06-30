<?php
$conn = new mysqli("localhost", "root", "", "spk");
if ($conn->connect_error) { die("Connection failed"); }
$result = $conn->query("SHOW TABLES LIKE \"testimoni\"");
if ($result->num_rows > 0) {
    echo "Table testimoni exists\n";
    $desc = $conn->query("DESCRIBE testimoni");
    while ($row = $desc->fetch_assoc()) {
        echo $row["Field"] . " - " . $row["Type"] . " - " . ($row["Null"] == "YES" ? "NULL" : "NOT NULL") . " - " . ($row["Default"] ?? "") . "\n";
    }
} else {
    echo "Table testimoni does NOT exist\n";
}
$conn->close();
