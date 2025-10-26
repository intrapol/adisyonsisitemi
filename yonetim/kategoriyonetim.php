<?php

include_once '../db/db.php';
include_once '../inc/security.php';

// Kategori ekleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kategori_adi']) && !isset($_POST['guncelle_id'])) {
    // CSRF token kontrolü
    if (isset($_POST['csrf_token']) && !verify_csrf_token($_POST['csrf_token'])) {
        die('Güvenlik hatası: Geçersiz token');
    }
    $kategori_adi = trim($_POST['kategori_adi']);
    if ($kategori_adi !== '') {
        $stmt = $conn->prepare("INSERT INTO kategori (kategori_adi) VALUES (?)");
        $stmt->bind_param("s", $kategori_adi);
        $stmt->execute();
        $stmt->close();
        // Sayfanın ortasında "Yükleniyor" ibaresi göster
        echo '<div style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(255,255,255,0.7); z-index: 9999; display: flex; align-items: center; justify-content: center;">';
        echo '<div style="background: #fff; padding: 2rem 3rem; border-radius: 1rem; box-shadow: 0 0 20px rgba(0,0,0,0.1); font-size: 2rem; color: #5f72bd; font-weight: bold;">';
        echo 'Yükleniyor...';
        echo '</div>';
        echo '</div>';
        echo '<meta http-equiv="refresh" content="0.5;url=index.php?page=masalar">';
        exit;
    }
}

// Kategori adı güncelleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kategori_adi']) && isset($_POST['guncelle_id'])) {
    // CSRF token kontrolü
    if (isset($_POST['csrf_token']) && !verify_csrf_token($_POST['csrf_token'])) {
        die('Güvenlik hatası: Geçersiz token');
    }
    $guncelle_id = intval($_POST['guncelle_id']);
    $kategori_adi = trim($_POST['kategori_adi']);
    if ($kategori_adi !== '' && $guncelle_id > 0) {
        $stmt = $conn->prepare("UPDATE kategori SET kategori_adi = ? WHERE id = ?");
        $stmt->bind_param("si", $kategori_adi, $guncelle_id);
        $stmt->execute();
        $stmt->close();
       // Sayfanın ortasında "Yükleniyor" ibaresi göster
       echo '<div style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(255,255,255,0.7); z-index: 9999; display: flex; align-items: center; justify-content: center;">';
       echo '<div style="background: #fff; padding: 2rem 3rem; border-radius: 1rem; box-shadow: 0 0 20px rgba(0,0,0,0.1); font-size: 2rem; color: #5f72bd; font-weight: bold;">';
       echo 'Yükleniyor...';
       echo '</div>';
       echo '</div>';
       echo '<meta http-equiv="refresh" content="0.5;url=index.php?page=masalar">';
        exit;
    }
}

// Kategori silme işlemi (POST ile)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sil_id']) && is_numeric($_POST['sil_id'])) {
    // CSRF token kontrolü
    if (isset($_POST['csrf_token']) && !verify_csrf_token($_POST['csrf_token'])) {
        die('Güvenlik hatası: Geçersiz token');
    }
    $sil_id = intval($_POST['sil_id']);
    $stmt = $conn->prepare("DELETE FROM kategori WHERE id = ?");
    $stmt->bind_param("i", $sil_id);
    $stmt->execute();
    $stmt->close();
    // Kategori silindiğinde, o kategoriye ait ürünleri de sil
    $stmt_urun = $conn->prepare("DELETE FROM urunler WHERE kategori_id = ?");
    $stmt_urun->bind_param("i", $sil_id);
    $stmt_urun->execute();
    $stmt_urun->close();
    // Silindikten sonra sayfayı yenile
    echo "<script>window.location.href='index.php?page=kategori';</script>";
    exit;
}

// Güncellenecek kategori varsa, bilgilerini çek
$guncellenecek_kategori = null;
if (isset($_GET['guncelle']) && is_numeric($_GET['guncelle'])) {
    $guncelle_id = intval($_GET['guncelle']);
    $stmt = $conn->prepare("SELECT id, kategori_adi FROM kategori WHERE id = ?");
    $stmt->bind_param("i", $guncelle_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $guncellenecek_kategori = $result->fetch_assoc();
    }
    $stmt->close();
}
?>

<div class="container mt-5">
    <div class="row mb-4">
        <div class="col-md-6 mx-auto">
            <?php if ($guncellenecek_kategori): ?>
                <form method="POST" class="d-flex">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token();?>">
                    <input type="hidden" name="guncelle_id" value="<?php echo $guncellenecek_kategori['id']; ?>">
                    <input type="text" name="kategori_adi" class="form-control me-2" value="<?php echo htmlspecialchars($guncellenecek_kategori['kategori_adi']); ?>" required>
                    <button type="submit" class="btn btn-warning">Güncelle</button>
                    <a href="index.php?page=kategori" class="btn btn-secondary ms-2">İptal</a>
                </form>
            <?php else: ?>
                <form method="POST" class="d-flex">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <input type="text" name="kategori_adi" class="form-control me-2" placeholder="Yeni kategori adı girin..." required>
                    <button type="submit" class="btn btn-success">Kategori Ekle</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card shadow-sm">
            <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #5f72bd 0%, #9b23ea 100%); color: #fff;">
                <strong>Mevcut Kategoriler</strong>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Kategori Adı</th>
                            <th>İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $stmt = $conn->prepare("SELECT id, kategori_adi FROM kategori ORDER BY id ASC");
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($result && $result->num_rows > 0) {
                        $sira = 1;
                        while($row = $result->fetch_assoc()) {
                            echo '<tr>';
                            echo '<td>' . $sira++ . '</td>';
                            echo '<td>' . htmlspecialchars($row['kategori_adi']) . '</td>';
                            echo '<td>
                                <a href="index.php?page=kategori&guncelle=' . $row['id'] . '" class="btn btn-warning btn-sm me-1">
                                    Düzenle
                                </a>
                                <form method="POST" style="display:inline;" onsubmit="return confirm(\'Bu kategoriyi silmek istediğinize emin misiniz?\');">
                                    <input type="hidden" name="csrf_token" value="' . generate_csrf_token() . '">
                                    <input type="hidden" name="sil_id" value="' . $row['id'] . '">
                                    <button type="submit" class="btn btn-danger btn-sm">Sil</button>
                                </form>
                            </td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="3" class="text-center text-muted">Hiç kategori bulunamadı.</td></tr>';
                    }
                    $stmt->close();
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
