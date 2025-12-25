<?php

include_once '../db/db.php';
include_once '../inc/security.php';

// Mesajlar için değişkenler
$mesaj = '';
$mesaj_tipi = '';

// Ürün ekleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['urun_adi'], $_POST['urun_fiyat'], $_POST['kategori_id']) && !isset($_POST['duzenle_id'])) {
    // CSRF token kontrolü (opsiyonel - sadece token varsa kontrol et)
    if (isset($_POST['csrf_token']) && !verify_csrf_token($_POST['csrf_token'])) {
        die('Güvenlik hatası: Geçersiz token');
    }
    $urun_adi = trim($_POST['urun_adi']);
    $urun_fiyat = floatval($_POST['urun_fiyat']);
    $kategori_id = intval($_POST['kategori_id']);

    if ($urun_adi !== '' && $urun_fiyat > 0 && $kategori_id > 0) {
        $stmt = $conn->prepare("INSERT INTO urunler (urun_adi, urun_fiyat, kategori_id) VALUES (?, ?, ?)");
        // urun_fiyat artık float olarak işleniyor
        $stmt->bind_param("sdi", $urun_adi, $urun_fiyat, $kategori_id);
        if ($stmt->execute()) {
            $mesaj = "Ürün başarıyla eklendi.";
            $mesaj_tipi = "success";
        } else {
            $mesaj = "Ürün eklenirken bir hata oluştu.";
            $mesaj_tipi = "danger";
        }
        $stmt->close();
    } else {
        $mesaj = "Lütfen tüm alanları doğru doldurun.";
        $mesaj_tipi = "warning";
    }
}

// Ürün güncelleme işlemi (aynı sayfada kal)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['duzenle_id'], $_POST['urun_adi'], $_POST['urun_fiyat'], $_POST['kategori_id'])) {
    // CSRF token kontrolü (opsiyonel - sadece token varsa kontrol et)
    if (isset($_POST['csrf_token']) && !verify_csrf_token($_POST['csrf_token'])) {
        die('Güvenlik hatası: Geçersiz token');
    }
    $duzenle_id = intval($_POST['duzenle_id']);
    $urun_adi = trim($_POST['urun_adi']);
    $urun_fiyat = floatval($_POST['urun_fiyat']);
    $kategori_id = intval($_POST['kategori_id']);

    if ($duzenle_id > 0 && $urun_adi !== '' && $urun_fiyat > 0 && $kategori_id > 0) {
        $stmt = $conn->prepare("UPDATE urunler SET urun_adi = ?, urun_fiyat = ?, kategori_id = ? WHERE id = ?");
        // urun_fiyat artık float olarak işleniyor
        $stmt->bind_param("sdii", $urun_adi, $urun_fiyat, $kategori_id, $duzenle_id);
        if ($stmt->execute()) {
            $mesaj = "Ürün başarıyla güncellendi.";
            $mesaj_tipi = "success";
            // Güncellemeden sonra formu sıfırla
            unset($_GET['duzenle']);
        } else {
            $mesaj = "Ürün güncellenirken bir hata oluştu.";
            $mesaj_tipi = "danger";
        }
        $stmt->close();
    } else {
        $mesaj = "Lütfen tüm alanları doğru doldurun.";
        $mesaj_tipi = "warning";
    }
}

// Ürün silme işlemi
if (isset($_GET['sil']) && is_numeric($_GET['sil'])) {
    $sil_id = intval($_GET['sil']);
    $stmt = $conn->prepare("DELETE FROM urunler WHERE id = ?");
    $stmt->bind_param("i", $sil_id);
    $stmt->execute();
    $stmt->close();
    // Silindikten sonra sayfayı yenile
    echo '<meta http-equiv="refresh" content="0;url=index.php?page=urunler">';
    exit;
}

// Kategorileri çek
$kategoriler = [];
$kategori_stmt = $conn->prepare("SELECT id, kategori_adi FROM kategori ORDER BY kategori_adi ASC");
$kategori_stmt->execute();
$kategori_sorgu = $kategori_stmt->get_result();
if ($kategori_sorgu && $kategori_sorgu->num_rows > 0) {
    while ($row = $kategori_sorgu->fetch_assoc()) {
        $kategoriler[] = $row;
    }
}
$kategori_stmt->close();

// Düzenlenecek ürün var mı?
$duzenle_urun = null;
if (isset($_GET['duzenle']) && is_numeric($_GET['duzenle'])) {
    $duzenle_id = intval($_GET['duzenle']);
    $stmt = $conn->prepare("SELECT * FROM urunler WHERE id = ?");
    $stmt->bind_param("i", $duzenle_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $duzenle_urun = $result->fetch_assoc();
    }
    $stmt->close();
}
?>

<div class="container mt-5">
    <div class="row mb-4">
        <div class="col-md-8 mx-auto">
            <?php if ($mesaj): ?>
                <div class="alert alert-<?php echo $mesaj_tipi; ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($mesaj); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Kapat"></button>
                </div>
            <?php endif; ?>
            <?php if ($duzenle_urun): ?>
                <form method="POST" class="row g-2 align-items-end">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <input type="hidden" name="duzenle_id" value="<?php echo $duzenle_urun['id']; ?>">
                    <div class="col-md-4">
                        <input type="text" name="urun_adi" class="form-control" placeholder="Ürün adı" required value="<?php echo htmlspecialchars($duzenle_urun['urun_adi']); ?>">
                    </div>
                    <div class="col-md-3">
                        <input type="number" name="urun_fiyat" class="form-control" placeholder="Fiyat (₺)" min="0" step="0.01" required value="<?php echo htmlspecialchars($duzenle_urun['urun_fiyat']); ?>">
                    </div>
                    <div class="col-md-3">
                        <select name="kategori_id" class="form-select" required>
                            <option value="">Kategori Seçiniz</option>
                            <?php
                            foreach ($kategoriler as $kategori) {
                                $selected = ($kategori['id'] == $duzenle_urun['kategori_id']) ? 'selected' : '';
                                echo '<option value="' . $kategori['id'] . '" ' . $selected . '>' . htmlspecialchars($kategori['kategori_adi']) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-1">
                        <button type="submit" class="btn btn-warning w-100">Güncelle</button>
                        <a href="index.php?page=urunler" class="btn btn-secondary w-100">İptal</a>
                    </div>
                </form>
            <?php else: ?>
                <form method="POST" class="row g-2 align-items-end">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <div class="col-md-4">
                        <input type="text" name="urun_adi" class="form-control" placeholder="Ürün adı" required>
                    </div>
                    <div class="col-md-3">
                        <input type="number" name="urun_fiyat" class="form-control" placeholder="Fiyat (₺)" min="0" step="0.01" required>
                    </div>
                    <div class="col-md-3">
                        <select name="kategori_id" class="form-select" required>
                            <option value="">Kategori Seçiniz</option>
                            <?php
                            foreach ($kategoriler as $kategori) {
                                echo '<option value="' . $kategori['id'] . '">' . htmlspecialchars($kategori['kategori_adi']) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Ürün Ekle</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-10 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #5f72bd 0%, #9b23ea 100%); color: #fff;">
                    <strong>Mevcut Ürünler</strong>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Ürün Adı</th>
                                <th>Fiyat (₺)</th>
                                <th>Kategori</th>
                                <th>İşlem</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        // Kategorileri ve ürünleri kategori bazında gruplayarak listele
                        $sira = 1;
                        foreach ($kategoriler as $kategori) {
                            // Kategori başlığı
                            echo '<tr style="background:#f1f1f1;"><td colspan="5" class="fw-bold">'.htmlspecialchars($kategori['kategori_adi']).'</td></tr>';

                            // Bu kategoriye ait ürünleri çek
                            $stmt = $conn->prepare("SELECT u.id, u.urun_adi, u.urun_fiyat, u.kategori_id, k.kategori_adi 
                                                    FROM urunler u 
                                                    LEFT JOIN kategori k ON u.kategori_id = k.id 
                                                    WHERE u.kategori_id = ? 
                                                    ORDER BY u.id ASC");
                            $stmt->bind_param("i", $kategori['id']);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            if ($result && $result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    echo '<tr>';
                                    echo '<td>' . $sira++ . '</td>';
                                    echo '<td>' . htmlspecialchars($row['urun_adi']) . '</td>';
                                    // urun_fiyat float olarak gösteriliyor
                                    echo '<td>' . number_format((float)$row['urun_fiyat'], 2, ',', '.') . '</td>';
                                    echo '<td>' . htmlspecialchars($row['kategori_adi'] ?? 'Kategori Yok') . '</td>';
                                    echo '<td>
                                        <a href="index.php?page=urunler&duzenle=' . $row['id'] . '" class="btn btn-warning btn-sm me-1">Düzenle</a>
                                        <a href="index.php?page=urunler&sil=' . $row['id'] . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Bu ürünü silmek istediğinize emin misiniz?\');">
                                            Sil
                                        </a>
                                    </td>';
                                    echo '</tr>';
                                }
                            } else {
                                echo '<tr><td colspan="5" class="text-center text-muted">Bu kategoride ürün yok.</td></tr>';
                            }
                            $stmt->close();
                        }

                        // Kategorisi olmayan ürünler (kategori_id NULL veya olmayanlar)
                        $stmt_katsiz = $conn->prepare("SELECT u.id, u.urun_adi, u.urun_fiyat, u.kategori_id, k.kategori_adi 
                                       FROM urunler u 
                                       LEFT JOIN kategori k ON u.kategori_id = k.id 
                                       WHERE u.kategori_id IS NULL OR u.kategori_id NOT IN (SELECT id FROM kategori)
                                       ORDER BY u.id ASC");
                        $stmt_katsiz->execute();
                        $result_katsiz = $stmt_katsiz->get_result();
                        if ($result_katsiz && $result_katsiz->num_rows > 0) {
                            echo '<tr style="background:#f1f1f1;"><td colspan="5" class="fw-bold">Kategori Yok</td></tr>';
                            while($row = $result_katsiz->fetch_assoc()) {
                                echo '<tr>';
                                echo '<td>' . $sira++ . '</td>';
                                echo '<td>' . htmlspecialchars($row['urun_adi']) . '</td>';
                                // urun_fiyat float olarak gösteriliyor
                                echo '<td>' . number_format((float)$row['urun_fiyat'], 2, ',', '.') . '</td>';
                                echo '<td>' . htmlspecialchars($row['kategori_adi'] ?? 'Kategori Yok') . '</td>';
                                echo '<td>
                                    <a href="index.php?page=urunler&duzenle=' . $row['id'] . '" class="btn btn-warning btn-sm me-1">Düzenle</a>
                                    <a href="index.php?page=urunler&sil=' . $row['id'] . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Bu ürünü silmek istediğinize emin misiniz?\');">
                                        Sil
                                    </a>
                                </td>';
                                echo '</tr>';
                            }
                        } elseif ($sira === 1) {
                            // Hiç ürün yoksa
                            echo '<tr><td colspan="5" class="text-center text-muted">Hiç ürün bulunamadı.</td></tr>';
                        }
                        $stmt_katsiz->close();
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
