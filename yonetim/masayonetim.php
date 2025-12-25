<?php
;
include_once '../db/db.php';
include_once '../inc/security.php';

// Masa ekleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['masa_adi']) && !isset($_POST['guncelle_id'])) {
    // CSRF token kontrolü
    if (isset($_POST['csrf_token']) && !verify_csrf_token($_POST['csrf_token'])) {  
        die('Güvenlik hatası: Geçersiz token');
    }
    $masa_adi = trim($_POST['masa_adi']);
    if ($masa_adi !== '') {
        $stmt = $conn->prepare("INSERT INTO masalar (masa_adi, durum) VALUES (?, 0)");
        $stmt->bind_param("s", $masa_adi);
        $stmt->execute();
        $stmt->close();
        // Başarıyla eklendiyse sayfayı yenile
        echo '<meta http-equiv="refresh" content="1;url=index.php?page=masalar">';
        exit;
    }
}

// Masa adı güncelleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['masa_adi']) && isset($_POST['guncelle_id'])) {
    // CSRF token kontrolü
    if (isset($_POST['csrf_token']) && !verify_csrf_token($_POST['csrf_token'])) {  
        die('Güvenlik hatası: Geçersiz token');
    }
    $guncelle_id = intval($_POST['guncelle_id']);
    $masa_adi = trim($_POST['masa_adi']);
    if ($masa_adi !== '' && $guncelle_id > 0) {
        $stmt = $conn->prepare("UPDATE masalar SET masa_adi = ? WHERE id = ?");
        $stmt->bind_param("si", $masa_adi, $guncelle_id);
        $stmt->execute();
        $stmt->close();
        // Başarıyla güncellendiyse sayfayı yenile
        // INSERT_YOUR_CODE
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

// Masa silme işlemi
if (isset($_GET['sil']) && is_numeric($_GET['sil'])) {
    $sil_id = intval($_GET['sil']);
    $stmt = $conn->prepare("DELETE FROM masalar WHERE id = ?");
    $stmt->bind_param("i", $sil_id);
    $stmt->execute();
    $stmt->close();
    // Silindikten sonra sayfayı yenile
    // Sayfanın ortasında "Yükleniyor" ibaresi göster
    echo '<div style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(255,255,255,0.7); z-index: 9999; display: flex; align-items: center; justify-content: center;">';
    echo '<div style="background: #fff; padding: 2rem 3rem; border-radius: 1rem; box-shadow: 0 0 20px rgba(0,0,0,0.1); font-size: 2rem; color: #5f72bd; font-weight: bold;">';
    echo 'Yükleniyor...';
    echo '</div>';
    echo '</div>';
    echo '<meta http-equiv="refresh" content="0.5;url=index.php?page=masalar">';
    exit;
}

// Güncellenecek masa varsa, bilgilerini çek
$guncellenecek_masa = null;
if (isset($_GET['guncelle']) && is_numeric($_GET['guncelle'])) {
    $guncelle_id = intval($_GET['guncelle']);
    $stmt = $conn->prepare("SELECT id, masa_adi FROM masalar WHERE id = ?");
    $stmt->bind_param("i", $guncelle_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $guncellenecek_masa = $result->fetch_assoc();
    }
    $stmt->close();
}
?>

<div class="container mt-5">
    <div class="row mb-4">
        <div class="col-md-6 mx-auto">
            <?php if ($guncellenecek_masa): ?>
                <form method="POST" class="d-flex">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token();?>">
                    <input type="hidden" name="guncelle_id" value="<?php echo $guncellenecek_masa['id']; ?>">
                    <input type="text" name="masa_adi" class="form-control me-2" value="<?php echo htmlspecialchars($guncellenecek_masa['masa_adi']); ?>" required>
                    <button type="submit" class="btn btn-warning">Güncelle</button>
                    <a href="index.php?page=masalar" class="btn btn-secondary ms-2">İptal</a>
                </form>
            <?php else: ?>
                <form method="POST" class="d-flex">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token();?>">
                    <input type="text" name="masa_adi" class="form-control me-2" placeholder="Yeni masa adı girin..." required>
                    <button type="submit" class="btn btn-primary">Ekle</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #5f72bd 0%, #9b23ea 100%); color: #fff;">
                    <strong>Mevcut Masalar</strong>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Masa Adı</th>
                                <th>İşlem</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $stmt = $conn->prepare("SELECT id, masa_adi FROM masalar ORDER BY id ASC");
                        $stmt->execute();
                        $result = $stmt->get_result();
                        if ($result && $result->num_rows > 0) {
                            $sira = 1;
                            while($row = $result->fetch_assoc()) {
                                echo '<tr>';
                                echo '<td>' . $sira++ . '</td>';
                                echo '<td>' . htmlspecialchars($row['masa_adi']) . '</td>';
                                echo '<td>
                                    <a href="index.php?page=masalar&guncelle=' . $row['id'] . '" class="btn btn-warning btn-sm me-1">
                                        Düzenle
                                    </a>
                                    <a href="index.php?page=masalar&sil=' . $row['id'] . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Bu masayı silmek istediğinize emin misiniz?\');">
                                        Sil
                                    </a>
                                </td>';
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr><td colspan="3" class="text-center text-muted">Hiç masa bulunamadı.</td></tr>';
                        }
                        $stmt->close();
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
