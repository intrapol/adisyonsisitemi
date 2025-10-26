<?php
// Float sistem test dosyası
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Float Sistem Test</h2>";

// 1. Float değer testleri
echo "<h3>1. Float Değer Testleri</h3>";

$test_values = [12.4, 15.99, 0.50, 100.00, 25.75];

foreach ($test_values as $value) {
    $float_val = floatval($value);
    echo "Değer: $value → Float: $float_val<br>";
}

// 2. Form input testi
echo "<h3>2. Form Input Testi</h3>";
echo '<form method="post">';
echo '<input type="number" name="test_fiyat" step="0.01" min="0" placeholder="Fiyat girin (örn: 12.4)" required>';
echo '<button type="submit" name="test_submit">Test Et</button>';
echo '</form>';

if (isset($_POST['test_submit'])) {
    $test_fiyat = floatval($_POST['test_fiyat']);
    echo "<strong>Girilen değer:</strong> " . $_POST['test_fiyat'] . "<br>";
    echo "<strong>Float değer:</strong> " . $test_fiyat . "<br>";
    echo "<strong>Formatlanmış:</strong> " . number_format($test_fiyat, 2) . " ₺<br>";
}

// 3. Hesaplama testi
echo "<h3>3. Hesaplama Testi</h3>";
$fiyat = 12.4;
$adet = 3;
$tutar = $fiyat * $adet;
echo "Fiyat: $fiyat ₺<br>";
echo "Adet: $adet<br>";
echo "Tutar: " . number_format($tutar, 2) . " ₺<br>";

// 4. Database test (eğer bağlantı varsa)
echo "<h3>4. Database Test</h3>";
try {
    include 'db/db.php';
    if ($conn && $conn->ping()) {
        echo "✅ Veritabanı bağlantısı başarılı<br>";
        
        // Test verisi ekleme
        $test_urun_adi = "Test Ürün " . time();
        $test_urun_fiyat = 12.4;
        $test_kategori_id = 1;
        
        $stmt = $conn->prepare("INSERT INTO urunler (urun_adi, urun_fiyat, kategori_id) VALUES (?, ?, ?)");
        $stmt->bind_param("sdi", $test_urun_adi, $test_urun_fiyat, $test_kategori_id);
        
        if ($stmt->execute()) {
            echo "✅ Float değer veritabanına başarıyla eklendi<br>";
            
            // Test verisini sil
            $delete_stmt = $conn->prepare("DELETE FROM urunler WHERE urun_adi = ?");
            $delete_stmt->bind_param("s", $test_urun_adi);
            $delete_stmt->execute();
            $delete_stmt->close();
            echo "✅ Test verisi temizlendi<br>";
        } else {
            echo "❌ Float değer eklenemedi<br>";
        }
        $stmt->close();
    } else {
        echo "❌ Veritabanı bağlantısı başarısız<br>";
    }
} catch (Exception $e) {
    echo "❌ Veritabanı hatası: " . $e->getMessage() . "<br>";
}

echo "<h3>Test Tamamlandı</h3>";
echo "<p>Float sistem test edildi. Artık 12.4 gibi küsüratlı değerler kullanabilirsiniz.</p>";
echo "<p><a href='yonetim/urunyonetim.php'>Ürün Yönetimi</a> | <a href='index.php'>Ana Sayfa</a></p>";
?>
