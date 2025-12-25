<?php
$servername = "localhost";
$username = "serdar";
$password = "serdar123";
$dbname = "src_adisyon";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Veritabanı bağlantısı başarısız: " . $conn->connect_error);
}
?>