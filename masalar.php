<?php include 'inc/header.php'; ?>

<style>
/* Masalar sayfası özel karanlık mod stilleri */
.dark-card {
    background: #1e2330 !important;
    color: #e4e6eb !important;
    border: 1px solid #2d3441 !important;
}
.btn-dark-success {
    background-color: #48bb78 !important;
    color: #fff !important;
    border-color: #48bb78 !important;
    transition: all 0.3s ease;
}
.btn-dark-success:hover {
    background-color: #38a169 !important;
    border-color: #38a169 !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(72, 187, 120, 0.3);
}
.btn-dark-danger {
    background-color: #f56565 !important;
    color: #fff !important;
    border-color: #f56565 !important;
    transition: all 0.3s ease;
}
.btn-dark-danger:hover {
    background-color: #e53e3e !important;
    border-color: #e53e3e !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(245, 101, 101, 0.3);
}
.kategori-baslik {
    font-size: 2rem;
    font-weight: bold;
    color: #4a9eff;
    margin-top: 2rem;
    margin-bottom: 0.25rem;
    letter-spacing: 2px;
    text-shadow: 0 2px 4px rgba(74, 158, 255, 0.3);
}
.kategori-hr {
    border-top: 2.5px solid #2d3441;
    margin-bottom: 1.5rem;
    margin-top: 0.5rem;
}
.masa-btn {
    font-size: 2.2rem !important;
    border-width: 3px !important;
    font-weight: bold !important;
    letter-spacing: 1px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3) !important;
    transition: all 0.3s ease !important;
}
.masa-btn:hover {
    transform: translateY(-3px) !important;
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.4) !important;
}
.alert-dark {
    background-color: #1e2330 !important;
    border-color: #2d3441 !important;
    color: #e4e6eb !important;
}
</style>

<?php
// Eğer odeme=ok parametresi gelirse bildirim göstermek için bir flag ayarla
$odeme_ok = (isset($_GET['odeme']) && $_GET['odeme'] === "ok");
?>

<?php if ($odeme_ok): ?>
    <div id="odeme-alert" style="
        position: fixed;
        top: 25px;
        right: 25px;
        z-index: 1055;
        min-width: 260px;
    ">
        <div class="alert alert-success alert-dismissible fade show shadow" style="font-size:1.25rem; font-weight:bold; margin-bottom:0;">
            Ödeme işlemi alındı
        </div>
    </div>
    <script>
        setTimeout(function(){
            var alert = document.getElementById('odeme-alert');
            if(alert) { alert.style.display = 'none'; }
        }, 3000);
    </script>
<?php endif; ?>

<div class="container mt-5">
    <?php
    // Masaları al ve baş harfe göre kategorileştir
    $stmt = $conn->prepare("SELECT id, masa_adi, durum FROM masalar");
    $stmt->execute();
    $result = $stmt->get_result();

    $kategoriler = array();

    if ($result && $result->num_rows > 0) {
        // Masaları harflere göre grupla
        while ($row = $result->fetch_assoc()) {
            $masa_id = $row['id'];
            $masa_adi = $row['masa_adi'];
            $durum = $row['durum'];

            // Kategori harfini çek, baştaki ilk büyük harf olsun
            $katHarf = strtoupper(substr(trim($masa_adi), 0, 1));
            if (!isset($kategoriler[$katHarf])) {
                $kategoriler[$katHarf] = array();
            }
            $kategoriler[$katHarf][] = [
                'id' => $masa_id,
                'masa_adi' => $masa_adi,
                'durum' => $durum
            ];
        }
        // Kategorileri harf sırasına göre sırala
        ksort($kategoriler);
        foreach ($kategoriler as $katHarf => $masalar) {
            // Masa adlarına göre sıralama (ör: M-1, M-2, M-10 düzgün gözüksün)
            usort($masalar, function($a, $b) {
                // M-2, M-10 gibi isimleri düzgün sırala
                $pattern = '/^([A-Za-z]+)-?(\d+)$/u';
                if (preg_match($pattern, $a['masa_adi'], $ma) && preg_match($pattern, $b['masa_adi'], $mb)) {
                    return intval($ma[2]) <=> intval($mb[2]);
                }
                return strcmp($a['masa_adi'], $b['masa_adi']);
            });
    ?>
        <div class="kategori-baslik"><?php echo htmlspecialchars($katHarf) . " Kategorisi"; ?></div>
        <hr class="kategori-hr">
        <div class="row mb-4">
            <?php foreach ($masalar as $masa) {
                $masa_id = $masa['id'];
                $masa_adi = $masa['masa_adi'];
                $durum = $masa['durum'];
                // Duruma göre buton rengi
                $btnClass = ($durum == 1) ? "btn-dark-danger" : "btn-dark-success";
                $borderColor = ($durum == 1) ? '#c62828' : '#388e3c';
            ?>
            <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                <div class="card dark-card shadow-sm border-0 h-100 text-center">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center">
                        <a href="masadetay.php?masa_id=<?php echo urlencode($masa_id); ?>"
                           class="btn <?php echo $btnClass; ?> masa-btn w-100 py-3 mb-1"
                           style="border-color: <?php echo $borderColor; ?>;">
                            <?php echo htmlspecialchars($masa_adi); ?>
                        </a>
                    </div>
                </div>
            </div>
            <?php } // masalar ?>
        </div>
    <?php
        } // foreach kategoriler
    } else {
        echo '<div class="col-12"><div class="alert alert-dark">Hiç masa bulunamadı.</div></div>';
    }
    ?>
</div>

<?php include 'inc/footer.php'; ?>