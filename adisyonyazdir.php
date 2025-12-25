
<?php
include_once 'db/db.php';
include_once 'inc/security.php';
?>
<?php
// POS makinesi kağıdı yaklaşık 58mm genişliğinde olur. Piksel karşılığı yaklaşık 220px'dir.
// Tablolar ve başlıklar buna uygun şekilde daraltılacak şekilde ayarlanacak.
// Yazı fontu da POS çıktısı gibi monospace/ufak fontla gösterilecek.

$pos_kagit_genislik_px = 220; // 58mm POS kağıdı için yaklaşık

$masa_id = isset($_GET['masa_id']) ? intval($_GET['masa_id']) : 0;
if ($masa_id <= 0) {
    echo '<div class="alert alert-danger text-center" style="max-width:'.$pos_kagit_genislik_px.'px;margin:0 auto;">Geçersiz masa seçimi.</div>';
} else {
    // Masa adı çek
    $masa_stmt = $conn->prepare("SELECT masa_adi FROM masalar WHERE id = ?");
    $masa_stmt->bind_param("i", $masa_id);
    $masa_stmt->execute();
    $masa_result = $masa_stmt->get_result();
    $masa_adi = '';
    if ($masa_result && $masa_result->num_rows > 0) {
        $row = $masa_result->fetch_assoc();
        $masa_adi = $row['masa_adi'];
    }
    $masa_stmt->close();

    // Siparişleri getir (anliksiparis ve urunler join)
    $stmt = $conn->prepare("SELECT u.urun_adi, u.urun_fiyat, a.adet FROM anliksiparis AS a INNER JOIN urunler AS u ON a.urun_id = u.id WHERE a.masa_id = ?");
    $stmt->bind_param("i", $masa_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Stiller: POS çıktısı gibi çok sade minimal stil
    echo '<div style="
        width:'.$pos_kagit_genislik_px.'px;
        margin:0 auto;
        padding:6px 0 6px 0;
        font-family:monospace;
        font-size:12px;
        background:#fff;
        color:#000;
        border:1px solid #aaa;"
    >';

    // Başlık
    echo '<div style="text-align:center;font-size:14px; font-weight:bold; margin-bottom:2px; letter-spacing:1px;">Yeşilova Alabalık Tesisileri</div>';
    if (!empty($masa_adi)) {
        echo '<div style="text-align:center;font-size:13px; font-weight:600; margin-bottom:7px;">' . htmlspecialchars($masa_adi) . ' MASASI</div>';
    }

    // Tablo başlıkları - POS fontunda
    echo '<table style="
            width:100%;
            border-collapse:collapse;
            font-size:12px;
            margin-bottom:4px;
        ">
        <thead>
            <tr>
                <th style="text-align:left; padding:3px 0; border-bottom:1px dashed #000; width:60%;">Ürün</th>
                <th style="text-align:center; padding:3px 0; border-bottom:1px dashed #000; width:13%;">Ad</th>
                <th style="text-align:right; padding:3px 0; border-bottom:1px dashed #000; width:27%;">Tutar</th>
            </tr>
        </thead>
        <tbody>';
    $toplam_tutar = 0.0;
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $urun_adi = htmlspecialchars($row['urun_adi']);
            $adet = intval($row['adet']);
            $urun_fiyat = floatval($row['urun_fiyat']);
            $tutar = $adet * $urun_fiyat;
            $toplam_tutar += $tutar;

            // POS makinesinde ürün adı çok uzunsa kırpılır
            $kisa_urun_adi = mb_strimwidth($urun_adi, 0, 16, "...");

            echo '<tr>
                <td style="padding:2px 0;vertical-align:top;word-break:break-all;">' . $kisa_urun_adi . '</td>
                <td style="text-align:center; padding:2px 0;">' . $adet . '</td>
                <td style="text-align:right; padding:2px 0;white-space:nowrap;">' . number_format($tutar, 2, ',', '.') . ' ₺</td>
            </tr>';
        }
    } else {
        echo '<tr>
            <td colspan="3" style="text-align:center; padding:9px 0;">Sipariş yok.</td>
        </tr>';
    }
    echo '</tbody></table>';

    // Toplam çizgi & toplam satırı
    echo '<div style="border-top:1px dashed #000; margin:7px 0 5px 0;"></div>';
    echo '<table style="width:100%; font-size:13px; font-weight:bold; margin-bottom:7px;">
        <tr>
            <td style="text-align:left;">TOPLAM</td>
            <td style="text-align:right;" colspan="2">'.number_format($toplam_tutar, 2, ',', '.') . ' ₺</td>
        </tr>
    </table>';

    // Afiyet olsun mesajı ve tarih
    echo '<div style="margin:12px 0 7px 0; text-align:center;font-size:12px;font-weight:bold;">Afiyet olsun</div>';
    echo '<div style="text-align:center;font-size:10px;">'.date("d.m.Y H:i").'</div>';
    echo '</div>';

    // Otomatik yazdırma
    echo '<script>window.print();</script>';
}
?>
