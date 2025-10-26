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
include_once '../db/db.php';

// Periyotlar
$periods = [
    'gun' => 1,
    'hafta' => 7,
    'ay' => 30,
    '3ay' => 90,
    '6ay' => 180,
    'yil' => 365
];

// Seçili periyodu al, yoksa 'ay'
$selected_period = isset($_GET['periyot']) && array_key_exists($_GET['periyot'], $periods) ? $_GET['periyot'] : 'ay';

// Tarih aralığı formundan gelen değerleri al
$custom_start = isset($_GET['baslangic']) ? $_GET['baslangic'] : '';
$custom_end = isset($_GET['bitis']) ? $_GET['bitis'] : '';

// Tarih aralığını hesapla
$today = date('Y-m-d');
$start_date = date('Y-m-d', strtotime("-" . $periods[$selected_period] . " days"));
$end_date = $today;

// Eğer özel tarih aralığı seçildiyse, onu kullan
$use_custom_range = false;
if ($custom_start && $custom_end) {
    $start_date = $custom_start;
    $end_date = $custom_end;
    $use_custom_range = true;
}

$date_condition = "WHERE tarih >= ? AND tarih <= ?";

// Gider ekleme işlemi
$gider_ekle_hata = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gider_ekle'])) {
    $aciklama = trim($_POST['aciklama']);
    $tutar = str_replace(',', '.', $_POST['tutar']);
    $tarih = $_POST['tarih'];

    if ($aciklama === '' || $tutar === '' || $tarih === '') {
        $gider_ekle_hata = "Lütfen tüm alanları doldurun.";
    } elseif (!is_numeric($tutar)) {
        $gider_ekle_hata = "Tutar sayısal olmalıdır.";
    } else {
        $stmt = $conn->prepare("INSERT INTO gider (aciklama, tutar, tarih) VALUES (?, ?, ?)");
        $stmt->bind_param("sds", $aciklama, $tutar, $tarih);
        $stmt->execute();
        $stmt->close();
        // Yönlendirme: özel tarih aralığı varsa parametreleri koru
        if ($use_custom_range) {
            header("Location: giderekle.php?baslangic=$custom_start&bitis=$custom_end");
        } else {
            header("Location: giderekle.php?periyot=$selected_period");
        }
        exit;
    }
}

// Gider silme işlemi
if (isset($_GET['sil']) && is_numeric($_GET['sil'])) {
    $gider_id = intval($_GET['sil']);
    $redirect_url = "giderekle.php";
    if ($use_custom_range) {
        $redirect_url .= "?baslangic=$custom_start&bitis=$custom_end";
    } else {
        $redirect_url .= "?periyot=$selected_period";
    }
    $stmt = $conn->prepare("DELETE FROM gider WHERE id = ?");
    $stmt->bind_param("i", $gider_id);
    $stmt->execute();
    $stmt->close();
    header("Location: $redirect_url");
    exit;
}

// Gider düzenleme işlemi
$duzenle_id = isset($_GET['duzenle']) && is_numeric($_GET['duzenle']) ? intval($_GET['duzenle']) : null;
$duzenle_gider = null;
if ($duzenle_id) {
    $stmt = $conn->prepare("SELECT * FROM gider WHERE id = ?");
    $stmt->bind_param("i", $duzenle_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $duzenle_gider = $result->fetch_assoc();
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gider_duzenle'])) {
    $gider_id = intval($_POST['gider_id']);
    $aciklama = trim($_POST['aciklama']);
    $tutar = str_replace(',', '.', $_POST['tutar']);
    $tarih = $_POST['tarih'];

    if ($aciklama === '' || $tutar === '' || $tarih === '') {
        $gider_ekle_hata = "Lütfen tüm alanları doldurun.";
    } elseif (!is_numeric($tutar)) {
        $gider_ekle_hata = "Tutar sayısal olmalıdır.";
    } else {
        $stmt = $conn->prepare("UPDATE gider SET aciklama = ?, tutar = ?, tarih = ? WHERE id = ?");
        $stmt->bind_param("sdsi", $aciklama, $tutar, $tarih, $gider_id);
        $stmt->execute();
        $stmt->close();
        // Yönlendirme: özel tarih aralığı varsa parametreleri koru
        if ($use_custom_range) {
            header("Location: giderekle.php?baslangic=$custom_start&bitis=$custom_end");
        } else {
            header("Location: giderekle.php?periyot=$selected_period");
        }
        exit;
    }
}

// Giderleri çek
$stmt = $conn->prepare("SELECT * FROM gider $date_condition ORDER BY tarih DESC, id DESC");
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$result = $stmt->get_result();

$giderler = [];
$toplam_gider = 0.0;
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $row['tutar'] = floatval($row['tutar']);
        $giderler[] = $row;
        $toplam_gider += $row['tutar'];
    }
}
$stmt->close();
?>

<div class="container mt-4">
    <h2 class="mb-4">Gider Ekle</h2>
    <?php if ($gider_ekle_hata): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($gider_ekle_hata); ?></div>
    <?php endif; ?>

    <?php if ($duzenle_gider): ?>
        <form method="post" class="row g-3 mb-4">
            <input type="hidden" name="gider_id" value="<?php echo $duzenle_gider['id']; ?>">
            <div class="col-md-5">
                <input type="text" name="aciklama" class="form-control" placeholder="Açıklama" value="<?php echo htmlspecialchars($duzenle_gider['aciklama']); ?>" required>
            </div>
            <div class="col-md-3">
                <input type="number" step="0.01" name="tutar" class="form-control" placeholder="Tutar" value="<?php echo htmlspecialchars($duzenle_gider['tutar']); ?>" required>
            </div>
            <div class="col-md-3">
                <input type="date" name="tarih" class="form-control" value="<?php echo htmlspecialchars($duzenle_gider['tarih']); ?>" required>
            </div>
            <div class="col-md-1 d-grid">
                <button type="submit" name="gider_duzenle" class="btn btn-warning">Güncelle</button>
            </div>
        </form>
    <?php else: ?>
        <form method="post" class="row g-3 mb-4">
            <div class="col-md-5">
                <input type="text" name="aciklama" class="form-control" placeholder="Açıklama" required>
            </div>
            <div class="col-md-3">
                <input type="number" step="0.01" name="tutar" class="form-control" placeholder="Tutar" required>
            </div>
            <div class="col-md-3">
                <input type="date" name="tarih" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <div class="col-md-1 d-grid">
                <button type="submit" name="gider_ekle" class="btn btn-primary">Ekle</button>
            </div>
        </form>
    <?php endif; ?>

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
                }
                ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Tarih aralığı seçimi -->
    <div class="mb-3">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-auto">
                <label for="baslangic" class="form-label mb-0">Başlangıç Tarihi</label>
                <input type="date" id="baslangic" name="baslangic" class="form-control" value="<?php echo htmlspecialchars($custom_start ?: $start_date); ?>">
            </div>
            <div class="col-auto">
                <label for="bitis" class="form-label mb-0">Bitiş Tarihi</label>
                <input type="date" id="bitis" name="bitis" class="form-control" value="<?php echo htmlspecialchars($custom_end ?: $end_date); ?>">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-success">Tarihe Göre Filtrele</button>
            </div>
            <?php if ($use_custom_range): ?>
                <div class="col-auto">
                    <a href="giderekle.php?periyot=<?php echo $selected_period; ?>" class="btn btn-outline-secondary">Filtreyi Temizle</a>
                </div>
            <?php endif; ?>
        </form>
    </div>

    <div class="mb-3">
        <div class="alert alert-info">
            <strong>Toplam Gider:</strong> <?php echo number_format($toplam_gider, 2, ',', '.'); ?> ₺
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-light">
                <tr>
                    <th>Tarih</th>
                    <th>Açıklama</th>
                    <th>Tutar (₺)</th>
                    <th style="width:120px;">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($giderler) > 0): ?>
                    <?php foreach ($giderler as $gider): ?>
                        <tr>
                            <td><?php echo date('d.m.Y', strtotime($gider['tarih'])); ?></td>
                            <td><?php echo htmlspecialchars($gider['aciklama']); ?></td>
                            <td><?php echo number_format($gider['tutar'], 2, ',', '.'); ?></td>
                            <td>
                                <?php
                                // İşlem linklerinde mevcut filtreleri koru
                                $query_params = [];
                                if ($use_custom_range) {
                                    $query_params['baslangic'] = $custom_start;
                                    $query_params['bitis'] = $custom_end;
                                } else {
                                    $query_params['periyot'] = $selected_period;
                                }
                                $duzenle_url = 'giderekle.php?duzenle=' . $gider['id'] . '&' . http_build_query($query_params);
                                $sil_url = 'giderekle.php?sil=' . $gider['id'] . '&' . http_build_query($query_params);
                                ?>
                                <a href="<?php echo $duzenle_url; ?>" class="btn btn-sm btn-warning">Düzenle</a>
                                <a href="<?php echo $sil_url; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bu gideri silmek istediğinize emin misiniz?');">Sil</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center">Seçili dönemde gider bulunamadı.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
