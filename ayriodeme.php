<?php include 'inc/header.php'; ?>

<?php

// Değişkenlerin başına ayriodeme_ eklendi

// masa_id'yi GET ile al
$ayriodeme_masa_id = isset($_GET["masa_id"]) ? intval($_GET["masa_id"]) : 0;
// Masaya Geri Dön butonu - En solda büyük mavi buton
if ($ayriodeme_masa_id > 0) {
    echo '<div class="position-fixed" style="top: 30px; left: 30px; z-index: 1050;">';
    echo '<a href="masadetay.php?masa_id=' . $ayriodeme_masa_id . '" class="btn btn-primary btn-lg" style="min-width: 220px; font-size: 1.3rem; font-weight: bold; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">';
    echo '← Masaya Geri Dön';
    echo '</a>';
    echo '</div>';
}
// Siparişleri çek
$ayriodeme_siparisler = [];
if ($ayriodeme_masa_id > 0) {
    $ayriodeme_stmt = $conn->prepare("SELECT a.id, a.urun_id, a.adet, u.urun_adi, u.urun_fiyat 
                            FROM anliksiparis a 
                            LEFT JOIN urunler u ON a.urun_id = u.id 
                            WHERE a.masa_id = ?");
    $ayriodeme_stmt->bind_param("i", $ayriodeme_masa_id);
    $ayriodeme_stmt->execute();
    $ayriodeme_result = $ayriodeme_stmt->get_result();
    while ($ayriodeme_row = $ayriodeme_result->fetch_assoc()) {
        $ayriodeme_siparisler[] = $ayriodeme_row;
    }
    $ayriodeme_stmt->close();
}

// Sağ tarafa aktarılan siparişler (oturumda tutulacak)
session_start();
if (!isset($_SESSION['ayri_odeme'][$ayriodeme_masa_id])) {
    $_SESSION['ayri_odeme'][$ayriodeme_masa_id] = [];
}

// Sağ tarafa ürün ekleme işlemi (AJAX veya POST ile)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ayriodeme_saga_aktar'])) {
    $ayriodeme_siparis_id = intval($_POST['ayriodeme_siparis_id']);
    $ayriodeme_adet = 1;
    // Maksimum aktarılabilir adet, anlık siparişte kalan adetten fazla olamaz
    foreach ($ayriodeme_siparisler as $ayriodeme_siparis) {
        if ($ayriodeme_siparis['id'] == $ayriodeme_siparis_id) {
            $ayriodeme_max_adet = $ayriodeme_siparis['adet'];
            break;
        }
    }
    if (!isset($ayriodeme_max_adet)) $ayriodeme_max_adet = 0;
    if ($ayriodeme_max_adet > 0) {
        if (!isset($_SESSION['ayri_odeme'][$ayriodeme_masa_id][$ayriodeme_siparis_id])) {
            $_SESSION['ayri_odeme'][$ayriodeme_masa_id][$ayriodeme_siparis_id] = 0;
        }
        if ($_SESSION['ayri_odeme'][$ayriodeme_masa_id][$ayriodeme_siparis_id] < $ayriodeme_max_adet) {
            $_SESSION['ayri_odeme'][$ayriodeme_masa_id][$ayriodeme_siparis_id]++;
        }
    }
    header("Location: ayriodeme.php?masa_id=" . $ayriodeme_masa_id);
    exit;
}

// Sağdan ürün eksiltme işlemi (geri alma)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ayriodeme_sagdan_eksilt'])) {
    $ayriodeme_siparis_id = intval($_POST['ayriodeme_siparis_id']);
    if (isset($_SESSION['ayri_odeme'][$ayriodeme_masa_id][$ayriodeme_siparis_id]) && $_SESSION['ayri_odeme'][$ayriodeme_masa_id][$ayriodeme_siparis_id] > 0) {
        $_SESSION['ayri_odeme'][$ayriodeme_masa_id][$ayriodeme_siparis_id]--;
        if ($_SESSION['ayri_odeme'][$ayriodeme_masa_id][$ayriodeme_siparis_id] <= 0) {
            unset($_SESSION['ayri_odeme'][$ayriodeme_masa_id][$ayriodeme_siparis_id]);
        }
    }
    header("Location: ayriodeme.php?masa_id=" . $ayriodeme_masa_id);
    exit;
}

// Ayrı ödemeyi tamamla işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ayriodeme_tamamla'])) {
    $ayriodeme_odenenler = $_SESSION['ayri_odeme'][$ayriodeme_masa_id];
    if (!empty($ayriodeme_odenenler)) {
        foreach ($ayriodeme_odenenler as $ayriodeme_siparis_id => $ayriodeme_adet_cek) {
            if ($ayriodeme_adet_cek <= 0) continue;
            // Siparişten ürünü çek
            $ayriodeme_stmt = $conn->prepare("SELECT a.urun_id, a.adet, u.urun_adi, u.urun_fiyat FROM anliksiparis a LEFT JOIN urunler u ON a.urun_id = u.id WHERE a.id = ?");
            $ayriodeme_stmt->bind_param("i", $ayriodeme_siparis_id);
            $ayriodeme_stmt->execute();
            $ayriodeme_result = $ayriodeme_stmt->get_result();
            if ($ayriodeme_row = $ayriodeme_result->fetch_assoc()) {
                $ayriodeme_urun_id = $ayriodeme_row['urun_id'];
                $ayriodeme_urun_adi = $ayriodeme_row['urun_adi'];
                $ayriodeme_urun_fiyat = $ayriodeme_row['urun_fiyat'];
                $ayriodeme_kalan_adet = $ayriodeme_row['adet'];
                $ayriodeme_tarih = date('Y-m-d');
                // Rapor tablosuna ekle
                $ayriodeme_stmt2 = $conn->prepare("INSERT INTO rapor (urun_adi, urun_fiyat, adet, tarih) VALUES (?, ?, ?, ?)");
                $ayriodeme_stmt2->bind_param("sdis", $ayriodeme_urun_adi, $ayriodeme_urun_fiyat, $ayriodeme_adet_cek, $ayriodeme_tarih);
                $ayriodeme_stmt2->execute();
                $ayriodeme_stmt2->close();
                // anliksiparis'ten eksilt
                if ($ayriodeme_kalan_adet > $ayriodeme_adet_cek) {
                    $ayriodeme_stmt3 = $conn->prepare("UPDATE anliksiparis SET adet = adet - ? WHERE id = ?");
                    $ayriodeme_stmt3->bind_param("ii", $ayriodeme_adet_cek, $ayriodeme_siparis_id);
                    $ayriodeme_stmt3->execute();
                    $ayriodeme_stmt3->close();
                } else {
                    // Tamamı ödendiyse satırı sil
                    $ayriodeme_stmt3 = $conn->prepare("DELETE FROM anliksiparis WHERE id = ?");
                    $ayriodeme_stmt3->bind_param("i", $ayriodeme_siparis_id);
                    $ayriodeme_stmt3->execute();
                    $ayriodeme_stmt3->close();
                }
            }
            $ayriodeme_stmt->close();
        }
        // Sağ tarafı temizle
        $_SESSION['ayri_odeme'][$ayriodeme_masa_id] = [];
    }
    header("Location: ayriodeme.php?masa_id=" . $ayriodeme_masa_id . "&odeme=ok");
    exit;
}

// Sol ve sağ siparişleri hazırla
$ayriodeme_sag_siparisler = [];
foreach ($_SESSION['ayri_odeme'][$ayriodeme_masa_id] as $ayriodeme_siparis_id => $ayriodeme_adet) {
    if ($ayriodeme_adet <= 0) continue;
    foreach ($ayriodeme_siparisler as $ayriodeme_siparis) {
        if ($ayriodeme_siparis['id'] == $ayriodeme_siparis_id) {
            $ayriodeme_sag_siparisler[] = [
                'id' => $ayriodeme_siparis_id,
                'urun_adi' => $ayriodeme_siparis['urun_adi'],
                'urun_fiyat' => $ayriodeme_siparis['urun_fiyat'],
                'adet' => $ayriodeme_adet
            ];
            break;
        }
    }
}

// Sol siparişlerden sağa aktarılanları düş
$ayriodeme_sol_siparisler = [];
foreach ($ayriodeme_siparisler as $ayriodeme_siparis) {
    $ayriodeme_kalan_adet = $ayriodeme_siparis['adet'];
    if (isset($_SESSION['ayri_odeme'][$ayriodeme_masa_id][$ayriodeme_siparis['id']])) {
        $ayriodeme_kalan_adet -= $_SESSION['ayri_odeme'][$ayriodeme_masa_id][$ayriodeme_siparis['id']];
    }
    if ($ayriodeme_kalan_adet > 0) {
        $ayriodeme_sol_siparisler[] = [
            'id' => $ayriodeme_siparis['id'],
            'urun_adi' => $ayriodeme_siparis['urun_adi'],
            'urun_fiyat' => $ayriodeme_siparis['urun_fiyat'],
            'adet' => $ayriodeme_kalan_adet
        ];
    }
}

// Ekran ikiye böl
?>
<div class="container mt-4">
    <div class="row">
        <!-- Sol taraf: Siparişler -->
        <div class="col-md-6">
            <h5>Masa Siparişleri</h5>
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>Ürün Adı</th>
                        <th>Adet</th>
                        <th>Tutar</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php if (count($ayriodeme_sol_siparisler) > 0): ?>
                    <?php foreach ($ayriodeme_sol_siparisler as $ayriodeme_siparis): ?>
                        <tr>
                            <td><?= htmlspecialchars($ayriodeme_siparis['urun_adi']) ?></td>
                            <td><?= $ayriodeme_siparis['adet'] ?></td>
                            <td><?= number_format($ayriodeme_siparis['urun_fiyat'] * $ayriodeme_siparis['adet'], 2) ?> ₺</td>
                            <td>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="ayriodeme_siparis_id" value="<?= $ayriodeme_siparis['id'] ?>">
                                    <button type="submit" name="ayriodeme_saga_aktar" class="btn btn-primary btn-sm" title="Sağa Aktar">
                                        <span style="font-size:1.2rem;">&#8594;</span>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center text-muted">Tüm siparişler sağa aktarıldı veya sipariş yok.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <!-- Sağ taraf: Ayrı ödeme için seçilenler -->
        <div class="col-md-6">
            <h5>Ödenecek Ürünler</h5>
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>Ürün Adı</th>
                        <th>Adet</th>
                        <th>Tutar</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $ayriodeme_toplam_odeme = 0;
                if (count($ayriodeme_sag_siparisler) > 0):
                    foreach ($ayriodeme_sag_siparisler as $ayriodeme_siparis):
                        $ayriodeme_tutar = $ayriodeme_siparis['urun_fiyat'] * $ayriodeme_siparis['adet'];
                        $ayriodeme_toplam_odeme += $ayriodeme_tutar;
                ?>
                    <tr>
                        <td><?= htmlspecialchars($ayriodeme_siparis['urun_adi']) ?></td>
                        <td><?= $ayriodeme_siparis['adet'] ?></td>
                        <td><?= number_format($ayriodeme_tutar, 2) ?> ₺</td>
                        <td>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="ayriodeme_siparis_id" value="<?= $ayriodeme_siparis['id'] ?>">
                                <button type="submit" name="ayriodeme_sagdan_eksilt" class="btn btn-warning btn-sm" title="Bir adet geri al">
                                    <span style="font-size:1.2rem;">&#8592;</span>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php
                    endforeach;
                else:
                ?>
                    <tr><td colspan="4" class="text-center text-muted">Henüz ürün seçilmedi.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
            <div class="mt-3">
                <div class="alert alert-info text-center" style="font-size:1.2rem;">
                    <strong>Toplam Ödeme:</strong> <?= number_format($ayriodeme_toplam_odeme, 2) ?> ₺
                </div>
                <?php if ($ayriodeme_toplam_odeme > 0): ?>
                <form method="post" class="text-center">
                    <button type="submit" name="ayriodeme_tamamla" class="btn btn-success btn-lg">Ayrı Ödemeyi Tamamla</button>
                </form>
                <?php elseif (isset($_GET['odeme']) && $_GET['odeme'] == 'ok'): ?>
                    <div class="alert alert-success mt-2">Ayrı ödeme başarıyla tamamlandı!</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'inc/footer.php'; ?>
