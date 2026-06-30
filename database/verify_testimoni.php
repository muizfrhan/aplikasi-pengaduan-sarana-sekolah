<?php
$conn = new mysqli("localhost", "root", "", "spk");
if ($conn->connect_error) { die("Connection failed"); }
$result = $conn->query("DESCRIBE testimoni");
if ($result) {
    echo "Table testimoni exists:\n";
    while ($row = $result->fetch_assoc()) {
        echo $row["Field"] . " - " . $row["Type"] . " - " . ($row["Null"] == "YES" ? "NULL" : "NOT NULL") . " - Default: " . ($row["Default"] ?? "NULL") . "\n";
    }
} else {
    echo "Error: " . $conn->error . "\n";
}
$conn->close();
