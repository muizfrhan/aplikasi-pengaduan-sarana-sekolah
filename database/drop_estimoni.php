<?php
$conn = new mysqli("localhost", "root", "", "spk");
$conn->query("DROP TABLE IF EXISTS estimoni");
echo "Done\n";
$conn->close();
