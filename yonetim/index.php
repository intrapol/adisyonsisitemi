<?php
session_start();
// Menüdeki hangi sayfanın seçili olduğunu belirle
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Menüdeki sayfa dosyalarını eşleştir
$pages = [
    'dashboard'   => 'dashboard.php',      // Panel Ana Sayfa
    'kullanicilar'=> 'kullaniciyonetim.php',
    'masalar'     => 'masayonetim.php',
    'kategori'    => 'kategoriyonetim.php',
    'urunler'     => 'urunyonetim.php',
    'satislar'    => 'satislar.php',
    'giderekle'    => 'giderekle.php',    
    'ayarlar'     => 'ayarlar.php'
];

// Geçerli dosya var mı kontrol et, yoksa dashboard göster
$content_file = isset($pages[$page]) && file_exists($pages[$page]) ? $pages[$page] : $pages['dashboard'];


?>
<!DOCTYPE html>
<html lang="tr">
<head>
    
    <meta charset="UTF-8">
    <title>Yönetim Paneli</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS -->
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #0f1419 !important;
            color: #e4e6eb !important;
        }
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
            color: #fff;
            border-right: 1px solid #2d3441;
        }
        .sidebar .nav-link {
            color: #e4e6eb !important;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .sidebar .nav-link.active, .sidebar .nav-link:hover {
            background: rgba(74, 158, 255, 0.2) !important;
            color: #ffd700 !important;
            transform: translateX(5px);
        }
        .sidebar .sidebar-title {
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 2rem;
            margin-top: 1rem;
            letter-spacing: 1px;
            color: #fff;
        }
        main {
            background: #0f1419 !important;
            color: #e4e6eb !important;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sol Menü -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar py-4">
                <div class="sidebar-sticky">
                    <div class="sidebar-title text-center mb-4">
                        <span style="font-size:2rem;">&#9881;&#65039;</span>
                        <span class="ms-2">Yönetim Paneli</span>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="../index.php">
                                <span class="me-2">&#8592;</span> Ana Sayfa
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php if($page=='dashboard') echo 'active'; ?>" href="?page=dashboard">
                                <span class="me-2">&#127968;</span> Panel Ana Sayfa
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php if($page=='kullanicilar') echo 'active'; ?>" href="?page=kullanicilar">
                                <span class="me-2">&#128188;</span> Kullanıcılar
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php if($page=='masalar') echo 'active'; ?>" href="?page=masalar">
                                <span class="me-2">&#127860;</span> Masalar
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php if($page=='kategori') echo 'active'; ?>" href="?page=kategori">
                                <span class="me-2">&#128193;</span> Kategoriler
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php if($page=='urunler') echo 'active'; ?>" href="?page=urunler">
                                <span class="me-2">&#127828;</span> Ürünler
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="satislar.php">
                                <span class="me-2">&#128179;</span> Satışlar
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="giderekle.php">
                                <span class="me-2">&#127828;</span> Gider Ekle
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="kasarapor.php">
                                <span class="me-2"><i class="fa fa-file-invoice-dollar"></i>&#128184;</span> Kasa Raporu
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php if($page=='ayarlar') echo 'active'; ?>" href="?page=ayarlar">
                                <span class="me-2">&#9881;&#65039;</span> Ayarlar
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
            <!-- Sağ İçerik Alanı -->
            <main class="col-md-9 col-lg-10 ms-sm-auto px-md-4 py-4">
                <?php
                // Seçilen menüye göre ilgili dosyayı çağır
                if (file_exists($content_file)) {
                    include $content_file;
                } else {
                    echo '<div class="alert alert-warning">İçerik bulunamadı.</div>';
                }
                ?>
            </main>
        </div>
    </div>
    <!-- Bootstrap JS (Opsiyonel) -->
    <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
