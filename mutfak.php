<?php include 'inc/header.php'; ?>
<!-- Sayfanın her dakika (60 saniye) otomatik yenilenmesi için meta etiketi -->
<meta http-equiv="refresh" content="100">

<?php
// Sipariş gönderildi butonuna basıldıysa işle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gonder_id'])) {
    // CSRF token kontrolü (opsiyonel - sadece token varsa kontrol et)
    if (isset($_POST['csrf_token']) && !verify_csrf_token($_POST['csrf_token'])) {
        die('Güvenlik hatası: Geçersiz token');
    }
    $gonder_id = intval($_POST['gonder_id']);
    $stmt = $conn->prepare("UPDATE anliksiparis SET siparis_durumu = 1 WHERE id = ?");
    $stmt->bind_param("i", $gonder_id);
    $stmt->execute();
    $stmt->close();
    // Sayfayı yenile
    echo '<script>window.location.href = window.location.href;</script>';
    exit;
}

// Anlık siparişleri çek (yeni siparişler önce)
$siparis_stmt = $conn->prepare("SELECT * FROM anliksiparis ORDER BY siparis_durumu ASC, saat DESC");
$siparis_stmt->execute();
$siparis_result = $siparis_stmt->get_result();

// Masaları çek
$masalar = [];
$masa_stmt = $conn->prepare("SELECT id, masa_adi FROM masalar ORDER BY id ASC");
$masa_stmt->execute();
$masa_result = $masa_stmt->get_result();
if ($masa_result && $masa_result->num_rows > 0) {
    while ($row = $masa_result->fetch_assoc()) {
        $masalar[$row['id']] = $row['masa_adi'];
    }
}

// Ürünleri çek
$urunler = [];
$urun_stmt = $conn->prepare("SELECT id, urun_adi FROM urunler ORDER BY urun_adi ASC");
$urun_stmt->execute();
$urun_result = $urun_stmt->get_result();
if ($urun_result && $urun_result->num_rows > 0) {
    while ($row = $urun_result->fetch_assoc()) {
        $urunler[$row['id']] = $row['urun_adi'];
    }
}

// Gönderilmemiş ürünlerin toplamını hesapla
$toplam_siparisler = [];
if ($siparis_result && $siparis_result->num_rows > 0) {
    $siparis_result->data_seek(0); // Result pointer'ı başa al
    while ($row = $siparis_result->fetch_assoc()) {
        if ($row['siparis_durumu'] == 0) { // Sadece gönderilmemiş siparişler
            $urun_id = $row['urun_id'];
            $urun_adi = isset($urunler[$urun_id]) ? $urunler[$urun_id] : "Ürün " . $urun_id;
            if (!isset($toplam_siparisler[$urun_adi])) {
                $toplam_siparisler[$urun_adi] = 0;
            }
            $toplam_siparisler[$urun_adi] += intval($row['adet']);
        }
    }
}

// Sayfa otomatik yenileme (1 dakika)
echo '<meta http-equiv="refresh" content="60">';
?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-center align-items-center mb-4">
        <h4 class="card-title mb-0 me-3">Anlık Siparişler</h4>
        <form method="get" style="display:inline;">
            <button type="submit" class="btn btn-secondary">
                <i class="bi bi-arrow-clockwise"></i> Sayfayı Yenile
            </button>
        </form>
    </div>
    </div>
    <?php
    if ($siparis_result && $siparis_result->num_rows > 0) {
        // Masalara göre grupla
        $masa_siparisleri = [];
        $siparis_result->data_seek(0); // Result pointer'ı başa al
        while ($row = $siparis_result->fetch_assoc()) {
            $masa_siparisleri[$row['masa_id']][] = $row;
        }
        
        // Masaları yan yana sırala (her satırda 5 masa)
        $masa_sayisi = count($masa_siparisleri);
        $satir_basina_masa = 5;
        $satir_sayisi = ceil($masa_sayisi / $satir_basina_masa);
        
        for ($satir = 0; $satir < $satir_sayisi; $satir++) {
            echo '<div class="row mb-4">';
            
            for ($kolon = 0; $kolon < $satir_basina_masa; $kolon++) {
                $masa_index = $satir * $satir_basina_masa + $kolon;
                $masa_id = array_keys($masa_siparisleri)[$masa_index] ?? null;
                
                if ($masa_id && isset($masa_siparisleri[$masa_id])) {
                    $siparisler = $masa_siparisleri[$masa_id];
                    $masa_adi = isset($masalar[$masa_id]) ? htmlspecialchars($masalar[$masa_id]) : "Masa $masa_id";
                    
                    // Masa kartı
                    echo '<div class="col-lg-2 col-md-3 col-sm-4 col-6 mb-3">';
                    echo '<div class="card h-100 border-0 shadow-sm">';
                    
                    // Masa başlığı
                    echo '<div class="card-header bg-primary text-white text-center py-2" style="border-radius: 0.375rem 0.375rem 0 0;">';
                    echo '<h6 class="mb-0 fw-bold" style="font-size: 0.9rem;">' . $masa_adi . '</h6>';
                    echo '</div>';
                    
                    // Masa içeriği
                    echo '<div class="card-body p-2">';
                    
                    foreach ($siparisler as $siparis) {
                        $urun_adi = isset($urunler[$siparis['urun_id']]) ? htmlspecialchars($urunler[$siparis['urun_id']]) : "Ürün " . $siparis['urun_id'];
                        $adet = intval($siparis['adet']);
                        $saat = htmlspecialchars($siparis['saat']);
                        $aciklama = htmlspecialchars($siparis['aciklama']);
                        $siparis_durumu = intval($siparis['siparis_durumu']);
                        
                        // Satır rengi: gönderilmediyse sarı, gönderildiyse yeşil
                        $row_class = $siparis_durumu == 1 ? 'table-success' : 'table-warning';
                        
                        echo '<div class="border-bottom mb-2 pb-2" style="border-color: #2d3441 !important;">';
                        
                        // Ürün adı ve adet
                        echo '<div class="d-flex justify-content-between align-items-center mb-1">';
                        echo '<span class="fw-bold text-uppercase" style="font-size: 0.8rem; color: #e4e6eb;">' . $urun_adi . '</span>';
                        echo '<span class="badge bg-dark" style="font-size: 0.75rem;">' . $adet . '  ADET </span>';
                        echo '</div>';
                        
                        // Sipariş zamanı bilgisi
                        $siparis_zamani = strtotime($saat);
                        $simdi = time();
                        $dakika_farki = floor(($simdi - $siparis_zamani) / 60);
                        
                        if ($dakika_farki < 1) {
                            $gecen_sure = "Az önce";
                            $sure_class = "text-success";
                        } elseif ($dakika_farki < 5) {
                            $gecen_sure = $dakika_farki . " dk";
                            $sure_class = "text-warning";
                        } elseif ($dakika_farki < 15) {
                            $gecen_sure = $dakika_farki . " dk";
                            $sure_class = "text-danger";
                        } else {
                            $gecen_sure = $dakika_farki . " dk";
                            $sure_class = "text-danger fw-bold";
                        }
                        
                        echo '<div class="d-flex justify-content-between align-items-center mb-1">';
                        echo '<span class="small text-muted" style="font-size: 0.65rem;">Saat: ' . date('H:i', $siparis_zamani) . '</span>';
                        echo '<span class="small ' . $sure_class . '" style="font-size: 0.65rem; font-weight: 600;">' . $gecen_sure . '</span>';
                        echo '</div>';
                        
                        // Açıklama
                        if ($aciklama && trim($aciklama) != ' ') {
                            echo '<div class="small text-muted mb-1" style="font-size: 0.7rem;">' . $aciklama . '</div>';
                        }
                        
                        // Durum butonu
                        if ($siparis_durumu == 0) {
                            echo '<form method="post" class="mb-0">';
                            echo '<input type="hidden" name="csrf_token" value="' . generate_csrf_token() . '">';
                            echo '<input type="hidden" name="gonder_id" value="' . $siparis['id'] . '">';
                            echo '<button type="submit" class="btn btn-outline-success btn-sm w-100" style="font-size: 0.7rem; padding: 0.25rem;">Gönder</button>';
                            echo '</form>';
                        } else {
                            echo '<span class="badge bg-success w-100" style="font-size: 0.7rem;">Gönderildi</span>';
                        }
                        
                        echo '</div>';
                    }
                    
                    echo '</div>'; // card-body
                    echo '</div>'; // card
                    echo '</div>'; // col
                }
            }
            
            echo '</div>'; // row
        }
    } else {
        echo '<div class="alert alert-info text-center">Şu anda hiç sipariş yok.</div>';
    }
    ?>
    
    <!-- Gönderilmemiş Ürünlerin Toplamı -->
    <?php if (!empty($toplam_siparisler)) { ?>
    <div class="row mt-5">
        <div class="col-12">
            <div class="card border-0 shadow">
                <div class="card-header text-center py-3" style="background-color: rgba(237, 137, 54, 0.2) !important; border-color: #ed8936 !important; color: #ed8936 !important;">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-utensils me-2"></i>
                        Hazırlanacak Ürünler
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php
                        $urun_basina_kolon = 4; // Her satırda 4 ürün
                        $urun_sayisi = count($toplam_siparisler);
                        $satir_sayisi = ceil($urun_sayisi / $urun_basina_kolon);
                        
                        for ($satir = 0; $satir < $satir_sayisi; $satir++) {
                            echo '<div class="row mb-3">';
                            
                            for ($kolon = 0; $kolon < $urun_basina_kolon; $kolon++) {
                                $urun_index = $satir * $urun_basina_kolon + $kolon;
                                $urun_adi = array_keys($toplam_siparisler)[$urun_index] ?? null;
                                
                                if ($urun_adi) {
                                    $adet = $toplam_siparisler[$urun_adi];
                                    echo '<div class="col-lg-3 col-md-4 col-sm-6 col-12 mb-2">';
                                    echo '<div class="d-flex justify-content-between align-items-center p-3 rounded border" style="background-color: #1e2330 !important; border-color: #2d3441 !important;">';
                                    echo '<span class="fw-bold text-uppercase" style="font-size: 0.9rem; color: #e4e6eb;">' . $urun_adi . '</span>'; 
                                    echo '<span class="badge" style="background-color: rgba(237, 137, 54, 0.3) !important; color: #ed8936 !important; font-size: 1rem; padding: 0.5rem 0.75rem; border: 1px solid #ed8936;">' . $adet . '</span>';
                                    echo '</div>';
                                    echo '</div>';
                                }
                            }
                            
                            echo '</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php } ?>
</div>

<style>
/* Responsive tasarım için */
@media (max-width: 1200px) {
    .col-lg-2 {
        flex: 0 0 20%;
        max-width: 20%;
    }
}

@media (max-width: 992px) {
    .col-lg-2 {
        flex: 0 0 25%;
        max-width: 25%;
    }
    .col-md-3 {
        flex: 0 0 33.333333%;
        max-width: 33.333333%;
    }
}

@media (max-width: 768px) {
    .col-lg-2, .col-md-3 {
        flex: 0 0 50%;
        max-width: 50%;
    }
    .col-sm-4 {
        flex: 0 0 50%;
        max-width: 50%;
    }
}

@media (max-width: 576px) {
    .col-lg-2, .col-md-3, .col-sm-4 {
        flex: 0 0 100%;
        max-width: 100%;
    }
    .col-6 {
        flex: 0 0 50%;
        max-width: 50%;
    }
}

/* Kart stilleri */
.card {
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
}

.card-header {
    border: none;
}

.card-body {
    max-height: 300px;
    overflow-y: auto;
}

/* Scrollbar stilleri */
.card-body::-webkit-scrollbar {
    width: 4px;
}

.card-body::-webkit-scrollbar-track {
    background: #0f1419;
    border-radius: 2px;
}

.card-body::-webkit-scrollbar-thumb {
    background: #2d3441;
    border-radius: 2px;
}

.card-body::-webkit-scrollbar-thumb:hover {
    background: #4a5568;
}

/* Badge stilleri */
.badge {
    font-weight: 600;
    letter-spacing: 0.5px;
}

/* Buton stilleri */
.btn-outline-success:hover {
    transform: scale(1.05);
    transition: transform 0.2s ease;
}

/* Zaman bilgisi stilleri */
.text-success {
    color: #48bb78 !important;
}

.text-warning {
    color: #ed8936 !important;
}

.text-danger {
    color: #f56565 !important;
}

.text-muted {
    color: #b0b3b8 !important;
}

/* Toplam tablosu stilleri */
.bg-warning {
    background: rgba(237, 137, 54, 0.2) !important;
    border-color: #ed8936 !important;
}

.bg-light {
    background: #1e2330 !important;
    border-color: #2d3441 !important;
}
</style>

<?php include 'inc/footer.php'; ?>
