
<?php
// Veritabanı bağlantısı
include_once '../db/db.php';

// Stil ayarı

$pos_kagit_genislik_px = 340;

// Tarih filtresi (varsayılan: bugün)
$baslangic_tarih = isset($_GET['baslangic_tarih']) ? $_GET['baslangic_tarih'] : date('Y-m-d');
$bitis_tarih     = isset($_GET['bitis_tarih'])     ? $_GET['bitis_tarih']     : date('Y-m-d');

// Form submit edildiyse ve tarih formatları uygunsa işle
$filtred = false;
if (
    isset($_GET['baslangic_tarih']) && isset($_GET['bitis_tarih']) &&
    preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['baslangic_tarih']) &&
    preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['bitis_tarih'])
) {
    $baslangic_tarih = $_GET['baslangic_tarih'];
    $bitis_tarih     = $_GET['bitis_tarih'];
    $filtred = true;

    // Otomatik yazdırma için JavaScript komutu, filtre uygulandıysa
    echo '<script>window.onload=function(){window.print();}</script>';
}

// Tarih-bitiş saatlerini tam kaplayacak şekilde ayarla
$basTS = $baslangic_tarih . " 00:00:00";
$bitTS = $bitis_tarih    . " 23:59:59";

// Form - Tarih aralığı seçme (her zaman ekranda gözüksün, yazdırılırken gözükmesin)
?>
<div class="container my-4" style="max-width:460px;">
    <form class="row g-2 justify-content-center" method="get" action="" id="filtreForm">
        <div class="col-auto">
            <label for="baslangic_tarih" class="form-label mb-0">Başlangıç:</label>
            <input type="date" name="baslangic_tarih" id="baslangic_tarih" class="form-control" value="<?php echo htmlspecialchars($baslangic_tarih); ?>" required>
        </div>
        <div class="col-auto">
            <label for="bitis_tarih" class="form-label mb-0">Bitiş:</label>
            <input type="date" name="bitis_tarih" id="bitis_tarih" class="form-control" value="<?php echo htmlspecialchars($bitis_tarih); ?>" required>
        </div>
        <div class="col-auto align-self-end">
            <button type="submit" class="btn btn-primary">
                Filtrele
            </button>
        </div>
    </form>
</div>
<style>
@media print {
    #filtreForm, .container.my-4 { display: none !important; }
}
/* Tablo hücrelerinin ve başlıklarının taşmasını engelle */
.kasarapor-table td,
.kasarapor-table th {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 75px !important;
    /* Responsive için elle kontrol */
}

/* Özellikle "Tutar" kolonunda kelime taşarsa küçült */
.kasarapor-table .tutar-td,
.kasarapor-table .tutar-th {
    max-width: 72px !important;
}
</style>

<?php
// Satış verisini rapor tablosundan çekiyoruz (Tarih filtreli)
// rapor tablosu: id, urun_adi, urun_fiyat, adet, tarih
$satislar = [];
$total_satis = 0.0;
$satis_stmt = $conn->prepare(
    "SELECT id, urun_adi, urun_fiyat, adet, tarih FROM rapor
     WHERE tarih BETWEEN ? AND ?
     ORDER BY tarih DESC"
);
$satis_stmt->bind_param('ss', $basTS, $bitTS);
$satis_stmt->execute();
$result = $satis_stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $row['toplam'] = floatval($row['urun_fiyat']) * intval($row['adet']);
    $satislar[] = $row;
    $total_satis += $row['toplam'];
}
$satis_stmt->close();

// Giderler Tablosu Getir (Tarih filtreli)
// gider tablosu: id, aciklama, tutar, tarih
$giderler = [];
$total_gider = 0.0;
$gider_stmt = $conn->prepare(
    "SELECT id, aciklama, tutar, tarih FROM gider
     WHERE tarih BETWEEN ? AND ?
     ORDER BY tarih DESC"
);
$gider_stmt->bind_param('ss', $basTS, $bitTS);
$gider_stmt->execute();
$result = $gider_stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $giderler[] = $row;
    $total_gider += floatval($row['tutar']);
}
$gider_stmt->close();

// POS çıktısı benzeri kutu
echo '<div style="width:'.$pos_kagit_genislik_px.'px; margin:0 auto; font-family:monospace; font-size:14px; background:#fff; color:#000; border:1px solid #aaa; padding:8px;">';

// Başlık
echo '<div style="text-align:center; font-size:16px; font-weight:bold; margin-bottom:10px;">Kasa Raporu</div>';

// Eğer tarih filtresi uygulandıysa yazdır:
if ($filtred) {
    echo '<div style="text-align:center; font-size:13px; color:#555; margin-bottom:10px;">
        <span>Tarih Aralığı:</span> <strong>'
         . date("d.m.Y", strtotime($baslangic_tarih)) . ' - ' . date("d.m.Y", strtotime($bitis_tarih)) .
        '</strong>
    </div>';
} else {
    echo '<div style="text-align:center; font-size:13px; color:#555; margin-bottom:10px;">
        (Bugünün işlemleri gösteriliyor)
    </div>';
}

echo '<div style="text-align:center; font-size:13px; font-weight:600; margin-bottom:8px;">Satışlar</div>';

// Satışlar Tablosu
echo '<table class="kasarapor-table" style="width:100%; table-layout:fixed; border-collapse:collapse; font-size:13px; margin-bottom:10px;">
        <colgroup>
          <col style="width:65px;">
          <col style="width:80px;">
          <col style="width:45px;">
          <col style="width:35px;">
          <col style="width:65px;">
        </colgroup>
        <thead>
            <tr>
                <th style="text-align:left; border-bottom:1px dashed #000; padding-bottom:2px;">Tarih</th>
                <th style="text-align:left; border-bottom:1px dashed #000;">Ürün</th>
                <th style="text-align:right; border-bottom:1px dashed #000;">Fiyat</th>
                <th style="text-align:center; border-bottom:1px dashed #000;">Adet</th>
                <th class="tutar-th" style="text-align:right; border-bottom:1px dashed #000;">Tutar</th>
            </tr>
        </thead>
        <tbody>';
if (count($satislar) > 0) {
    foreach ($satislar as $sat) {
        $tarih = date("d.m.Y H:i", strtotime($sat['tarih']));
        $urun = htmlspecialchars($sat['urun_adi']);
        $adet = intval($sat['adet']);
        $urun_fiyat = floatval($sat['urun_fiyat']);
        $tutar = $sat['toplam'];
        $kisa_urun = mb_strimwidth($urun, 0, 12, "..."); // kısalttık
        echo "<tr>
                <td style='padding:2px 2px 2px 0;vertical-align:top;overflow:hidden;text-overflow:ellipsis;max-width:60px;white-space:nowrap;'>$tarih</td>
                <td style='padding:2px 2px 2px 0;vertical-align:top;overflow:hidden;text-overflow:ellipsis;max-width:70px;white-space:nowrap;'>$kisa_urun</td>
                <td style='text-align:right;white-space:nowrap;overflow:hidden;max-width:40px;'>".number_format($urun_fiyat, 2, ',', '.')." ₺</td>
                <td style='text-align:center;white-space:nowrap;max-width:28px;'>$adet</td>
                <td class='tutar-td' style='text-align:right;white-space:nowrap;overflow:hidden;max-width:60px;'>".number_format($tutar, 2, ',', '.')." ₺</td>
              </tr>";
    }
} else {
    echo '<tr><td colspan="5" style="text-align:center; padding:8px 0;">Kayıt yok.</td></tr>';
}
echo '</tbody></table>';

// Toplam Satış Alt Satırı
echo '<div style="text-align:right; font-weight:bold; margin-bottom:8px;">Toplam Satış: '.number_format($total_satis, 2, ',', '.').' ₺</div>';

// Giderler Tablo Başlığı
echo '<div style="text-align:center; font-size:13px; font-weight:600; margin-bottom:7px;">Giderler</div>';
// Giderler Tablosu
echo '<table class="kasarapor-table" style="width:100%; table-layout:fixed; border-collapse:collapse; font-size:13px; margin-bottom:10px;">
        <colgroup>
          <col style="width:70px;">
          <col style="width:105px;">
          <col style="width:70px;">
        </colgroup>
        <thead>
            <tr>
                <th style="text-align:left; border-bottom:1px dashed #000;">Tarih</th>
                <th style="text-align:left; border-bottom:1px dashed #000;">Açıklama</th>
                <th class="tutar-th" style="text-align:right; border-bottom:1px dashed #000;">Tutar</th>
            </tr>
        </thead>
        <tbody>';
if (count($giderler) > 0) {
    foreach ($giderler as $gid) {
        $tarih = date("d.m.Y H:i", strtotime($gid['tarih']));
        $aciklama = htmlspecialchars($gid['aciklama']);
        $tutar = number_format($gid['tutar'], 2, ',', '.') . ' ₺';
        $kisa_aciklama = mb_strimwidth($aciklama, 0, 15, "..."); // kısalttık
        echo "<tr>
                <td style='padding:2px 2px 2px 0;vertical-align:top;overflow:hidden;text-overflow:ellipsis;max-width:64px;white-space:nowrap;'>$tarih</td>
                <td style='padding:2px 2px 2px 0;vertical-align:top;overflow:hidden;text-overflow:ellipsis;max-width:92px;white-space:nowrap;'>$kisa_aciklama</td>
                <td class='tutar-td' style='text-align:right;white-space:nowrap;overflow:hidden;max-width:63px;'>$tutar</td>
              </tr>";
    }
} else {
    echo '<tr><td colspan="3" style="text-align:center; padding:8px 0;">Gider kaydı yok.</td></tr>';
}
echo '</tbody></table>';

// Toplam Gider Alt Satırı
echo '<div style="text-align:right; font-weight:bold; margin-bottom:8px;">Toplam Gider: '.number_format($total_gider, 2, ',', '.').' ₺</div>';

// ALTTA Net Hesap
$net_tutar = $total_satis - $total_gider;
$net_color = $net_tutar >= 0 ? "#15803d" : "#dc2626";
echo '<div style="border-top:1px dashed #000; margin:13px 0 7px 0;"></div>';
echo '<div style="text-align:center; font-size:15px; font-weight:bold; color:'.$net_color.'; margin-bottom:10px;">';
echo 'Net Kasa: '.number_format($net_tutar, 2, ',', '.') . ' ₺';
echo '</div>';

// Raporun hazırlandığı an
echo '<div style="text-align:center;font-size:11px; color:#555;">'.date("d.m.Y H:i").'</div>';

echo '</div>';
?>
<?php
// Eğer hem başlangıç hem de bitiş tarihi get ile verilmişse butonu gösterme
if (empty($_GET['tarih1']) && empty($_GET['tarih2'])):
?>
<?php endif; ?>

