<?php
$servername = "localhost";
$username = "serc_adisyon";
$password = "qHN-A!xP3kcwkC@O";
$dbname = "serc_adisyon";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Veritabanı bağlantısı başarısız: " . $conn->connect_error);
}
?>