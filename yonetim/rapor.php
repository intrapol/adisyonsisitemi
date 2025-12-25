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
        .table-responsive {
            max-height: 400px;
            overflow-y: auto;
        }
        @media (max-width: 991.98px) {
            .row-table-split {
                flex-direction: column;
            }
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

// Periyotlar
$periods = [
    'gun' => 1,
    'hafta' => 7,
    'ay' => 30,
    '3ay' => 90,
    '6ay' => 180,
    'yil' => 365,
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

// Satışları çek (tablo için)
$satislar = [];
if ($use_custom_range || $selected_period !== 'tum') {
    $stmt = $conn->prepare("SELECT urun_adi, urun_fiyat, adet, tarih FROM rapor $date_condition ORDER BY tarih DESC, id DESC");
    $stmt->bind_param("ss", $start_date, $end_date);
} else {
    $stmt = $conn->prepare("SELECT urun_adi, urun_fiyat, adet, tarih FROM rapor ORDER BY tarih DESC, id DESC");
}
$stmt->execute();
$result = $stmt->get_result();

$toplam_satis = 0.0;
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $row['tutar'] = floatval($row['urun_fiyat']) * intval($row['adet']);
        $toplam_satis += $row['tutar'];
        $satislar[] = $row;
    }
}
$stmt->close();

// Giderleri çek (tablo için)
$giderler = [];
if ($use_custom_range || $selected_period !== 'tum') {
    $stmt = $conn->prepare("SELECT aciklama, tutar, tarih FROM gider $date_condition ORDER BY tarih DESC, id DESC");
    $stmt->bind_param("ss", $start_date, $end_date);
} else {
    $stmt = $conn->prepare("SELECT aciklama, tutar, tarih FROM gider ORDER BY tarih DESC, id DESC");
}
$stmt->execute();
$result = $stmt->get_result();

$toplam_gider = 0.0;
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $toplam_gider += floatval($row['tutar']);
        $giderler[] = $row;
    }
}
$stmt->close();

$net_kazanc = $toplam_satis - $toplam_gider;
?>

<div class="container mt-4">
    <h2 class="mb-4">Net Kazanç Raporu</h2>
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
                    case 'yil': echo "1 Yıl"; break;
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
                    <a href="rapor.php" class="btn btn-secondary">Filtreyi Temizle</a>
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
            <strong>Toplam Gider:</strong> <?php echo number_format($toplam_gider, 2, ',', '.'); ?> ₺
            <br>
            <strong>Net Kazanç:</strong> <span class="<?php echo $net_kazanc >= 0 ? 'text-success' : 'text-danger'; ?>"><?php echo number_format($net_kazanc, 2, ',', '.'); ?> ₺</span>
        </div>
    </div>

    <!-- Tablo Alanı -->
    <div class="row row-table-split g-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    Satışlar (Rapor)
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Ürün Adı</th>
                                <th>Tutar (₺)</th>
                                <th>Adet</th>
                                <th>Tarih</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($satislar) > 0): ?>
                                <?php foreach ($satislar as $satis): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($satis['urun_adi']); ?></td>
                                        <td><?php echo number_format($satis['tutar'], 2, ',', '.'); ?></td>
                                        <td><?php echo intval($satis['adet']); ?></td>
                                        <td><?php echo date('d.m.Y', strtotime($satis['tarih'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Kayıt bulunamadı.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    Giderler
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Açıklama</th>
                                <th>Tutar (₺)</th>
                                <th>Tarih</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($giderler) > 0): ?>
                                <?php foreach ($giderler as $gider): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($gider['aciklama']); ?></td>
                                        <td><?php echo number_format($gider['tutar'], 2, ',', '.'); ?></td>
                                        <td><?php echo date('d.m.Y', strtotime($gider['tarih'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted">Kayıt bulunamadı.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /container -->

<!-- Bootstrap JS (isteğe bağlı) -->
<script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
