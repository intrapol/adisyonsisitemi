<?php
session_start();
include_once '../db/db.php';
include_once '../inc/security.php';
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Satış Raporu - Adisyon Sistemi</title>
    
    <!-- Bootstrap CSS -->
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        body {
            background: linear-gradient(120deg, #e0e7ef 0%, #f5f7fa 100%);
        }
        .header {
            background: linear-gradient(135deg, #5f72bd 0%, #9b23ea 100%);
            color: white;
            padding: 2rem 0;
        }
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border: none;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="header py-2">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-auto">
                    <span style="font-size:1.5rem;">🍽️</span>
                    <span class="h4 align-middle ms-2">Adisyon Sistemi</span>
                </div>
                <div class="col d-flex justify-content-center">
                    <nav>
                        <ul class="nav">
                            <li class="nav-item">
                                <a class="nav-link text-white" href="../index.php"><strong>Anasayfa</strong></a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-white" href="../masalar.php"><strong>Masalar</strong></a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-white" href="../mutfak.php"><strong>Mutfak</strong></a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-white" href="index.php"><strong>Yönetim</strong></a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

<div class="container mt-3">
    <a href="index.php" class="btn btn-secondary mb-3">
        <span style="font-size:1.2rem;">&#8592;</span> Geri Dön
    </a>
</div>
<?php

// Tarih aralığı butonları ve gün sayıları
$periods = [
    'gun' => 1,
    'hafta' => 7,
    'ay' => 30,
    '3ay' => 90,
    '6ay' => 180,
    '9ay' => 270,
    '12ay' => 365,
    'tum' => 0
];

// Seçili periyodu al, yoksa 'hafta'
$selected_period = isset($_GET['periyot']) && array_key_exists($_GET['periyot'], $periods) ? $_GET['periyot'] : 'hafta';

// Tarih aralığı formundan gelen özel tarih aralığı var mı?
$use_custom_range = false;
$custom_start = '';
$custom_end = '';
if (isset($_GET['baslangic']) && isset($_GET['bitis']) && $_GET['baslangic'] !== '' && $_GET['bitis'] !== '') {
    $custom_start = $_GET['baslangic'];
    $custom_end = $_GET['bitis'];
    $use_custom_range = true;
}

// Tarih aralığını hesapla
$today = date('Y-m-d');
if ($use_custom_range) {
    $start_date = $custom_start;
    $end_date = $custom_end;
    $date_condition = "WHERE tarih >= ? AND tarih <= ?";
} elseif ($selected_period !== 'tum') {
    $start_date = date('Y-m-d', strtotime("-" . $periods[$selected_period] . " days"));
    $end_date = $today;
    $date_condition = "WHERE tarih >= ? AND tarih <= ?";
} else {
    $start_date = null;
    $end_date = null;
    $date_condition = "";
}

// Rapor verilerini çek
if ($use_custom_range || $selected_period !== 'tum') {
    $stmt = $conn->prepare("SELECT urun_adi, urun_fiyat, SUM(adet) as toplam_adet FROM rapor $date_condition GROUP BY urun_adi, urun_fiyat ORDER BY toplam_adet DESC");
    $stmt->bind_param("ss", $start_date, $end_date);
} else {
    $stmt = $conn->prepare("SELECT urun_adi, urun_fiyat, SUM(adet) as toplam_adet FROM rapor GROUP BY urun_adi, urun_fiyat ORDER BY toplam_adet DESC");
}
$stmt->execute();
$result = $stmt->get_result();

$raporlar = [];
$toplam_satis = 0.0;
$toplam_urun = 0;
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // urun_fiyat'ı float olarak işle
        $row['urun_fiyat'] = floatval($row['urun_fiyat']);
        $row['toplam_adet'] = intval($row['toplam_adet']);
        $raporlar[] = $row;
        $toplam_satis += $row['urun_fiyat'] * $row['toplam_adet'];
        $toplam_urun += $row['toplam_adet'];
    }
}
$stmt->close();
?>

<div class="container mt-4">
    <h2 class="mb-4">Satış Raporu</h2>
    <div class="mb-3">
        <?php foreach ($periods as $key => $days): ?>
            <a href="?periyot=<?php echo $key; ?>" class="btn btn-<?php echo ($selected_period == $key && !$use_custom_range) ? 'primary' : 'outline-primary'; ?> btn-sm me-1 mb-1">
                <?php
                switch ($key) {
                    case 'gun': echo "Bugün"; break;
                    case 'hafta': echo "Hafta"; break;
                    case 'ay': echo "Ay"; break;
                    case '3ay': echo "3 Ay"; break;
                    case '6ay': echo "6 Ay"; break;
                    case '9ay': echo "9 Ay"; break;
                    case '12ay': echo "12 Ay"; break;
                    case 'tum': echo "Tüm Zamanlar"; break;
                }
                ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Tarih aralığı seçimi -->
    <div class="mb-3">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-auto">
                <label for="baslangic" class="form-label mb-0">Başlangıç:</label>
                <input type="date" id="baslangic" name="baslangic" class="form-control" value="<?php echo htmlspecialchars($custom_start ?: ($selected_period !== 'tum' ? $start_date : '')); ?>">
            </div>
            <div class="col-auto">
                <label for="bitis" class="form-label mb-0">Bitiş:</label>
                <input type="date" id="bitis" name="bitis" class="form-control" value="<?php echo htmlspecialchars($custom_end ?: ($selected_period !== 'tum' ? $end_date : '')); ?>">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-info">Tarihe Göre Filtrele</button>
            </div>
            <?php if ($use_custom_range): ?>
                <div class="col-auto">
                    <a href="satislar.php" class="btn btn-secondary">Filtreyi Temizle</a>
                </div>
            <?php endif; ?>
        </form>
    </div>

    <div class="mb-3">
        <div class="alert alert-info">
            <strong>Seçili Dönem:</strong>
            <?php
            if ($use_custom_range) {
                echo date('d.m.Y', strtotime($custom_start)) . " - " . date('d.m.Y', strtotime($custom_end));
            } elseif ($selected_period === 'tum') {
                echo "Tüm Zamanlar";
            } else {
                echo date('d.m.Y', strtotime($start_date)) . " - " . date('d.m.Y', strtotime($end_date));
            }
            ?>
            <br>
            <strong>Toplam Satış:</strong> <?php echo number_format($toplam_satis, 2, ',', '.'); ?> ₺
            <br>
            <strong>Toplam Satılan Ürün:</strong> <?php echo $toplam_urun; ?> adet
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-light">
                <tr>
                    <th>Ürün Adı</th>
                    <th>Ürün Fiyatı (₺)</th>
                    <th>Toplam Adet</th>
                    <th>Toplam Tutar (₺)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($raporlar) > 0): ?>
                    <?php foreach ($raporlar as $rapor): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($rapor['urun_adi']); ?></td>
                            <td><?php echo number_format((float)$rapor['urun_fiyat'], 2, ',', '.'); ?></td>
                            <td><?php echo $rapor['toplam_adet']; ?></td>
                            <td><?php echo number_format($rapor['urun_fiyat'] * $rapor['toplam_adet'], 2, ',', '.'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted">Seçili dönemde satış bulunamadı.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Footer -->
<footer class="bg-light text-center text-muted py-3 mt-5">
    <div class="container">
        <p class="mb-0">&copy; 2024 Adisyon Sistemi. Tüm hakları saklıdır.</p>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="../bootstrap/js/bootstrap.min.js"></script>

</body>
</html>
