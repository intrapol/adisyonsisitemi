<?php
// Sistem test dosyası
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Adisyon Sistemi Test</h2>";

// 1. Session test
echo "<h3>1. Session Test</h3>";
session_start();
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "✅ Session başlatıldı<br>";
} else {
    echo "❌ Session başlatılamadı<br>";
}

// 2. Database connection test
echo "<h3>2. Database Connection Test</h3>";
try {
    include 'db/db.php';
    if ($conn && $conn->ping()) {
        echo "✅ Veritabanı bağlantısı başarılı<br>";
    } else {
        echo "❌ Veritabanı bağlantısı başarısız<br>";
    }
} catch (Exception $e) {
    echo "❌ Veritabanı hatası: " . $e->getMessage() . "<br>";
}

// 3. Security functions test
echo "<h3>3. Security Functions Test</h3>";
try {
    include 'inc/security.php';
    $token = generate_csrf_token();
    if ($token && strlen($token) > 0) {
        echo "✅ CSRF token oluşturuldu: " . substr($token, 0, 10) . "...<br>";
        
        if (verify_csrf_token($token)) {
            echo "✅ CSRF token doğrulaması başarılı<br>";
        } else {
            echo "❌ CSRF token doğrulaması başarısız<br>";
        }
    } else {
        echo "❌ CSRF token oluşturulamadı<br>";
    }
} catch (Exception $e) {
    echo "❌ Security functions hatası: " . $e->getMessage() . "<br>";
}

// 4. File includes test
echo "<h3>4. File Includes Test</h3>";
$files_to_test = [
    'inc/header.php',
    'inc/security.php',
    'masadetay.php',
    'mutfak.php',
    'masalar.php',
    'yonetim/kategoriyonetim.php',
    'yonetim/masayonetim.php',
    'yonetim/urunyonetim.php'
];

foreach ($files_to_test as $file) {
    if (file_exists($file)) {
        echo "✅ $file mevcut<br>";
    } else {
        echo "❌ $file bulunamadı<br>";
    }
}

// 5. PHP version and extensions
echo "<h3>5. PHP Environment Test</h3>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Session Support: " . (function_exists('session_start') ? '✅' : '❌') . "<br>";
echo "MySQLi Support: " . (extension_loaded('mysqli') ? '✅' : '❌') . "<br>";
echo "Random Bytes Support: " . (function_exists('random_bytes') ? '✅' : '❌') . "<br>";

echo "<h3>Test Tamamlandı</h3>";
echo "<p>Eğer tüm testler ✅ ise sistem çalışıyor demektir.</p>";
echo "<p><a href='index.php'>Ana Sayfaya Git</a></p>";
?>
