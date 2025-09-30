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
            background: #f8f9fa;
        }
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #5f72bd 0%, #9b23ea 100%);
            color: #fff;
        }
        .sidebar .nav-link {
            color: #fff;
            font-weight: 500;
        }
        .sidebar .nav-link.active, .sidebar .nav-link:hover {
            background: rgba(255,255,255,0.1);
            color: #ffd700;
        }
        .sidebar .sidebar-title {
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 2rem;
            margin-top: 1rem;
            letter-spacing: 1px;
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
