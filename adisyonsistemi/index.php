<?php include 'inc/header.php'; ?>

    <!-- Main Content -->
    <div class="container mt-5">
        <div class="row">
            <!-- Masalar (Sol Kısım) -->
            <div class="col-md-4 mb-4 d-flex align-items-center justify-content-center">
                <a href="masalar.php" class="btn btn-lg btn-primary w-100 py-5" style="font-size:2rem;">
                    <span style="font-size:2.5rem;">🪑</span><br>
                    Masalar
                </a>
            </div>
            <!-- Ayarlar (Orta Kısım) -->
            <div class="col-md-4 mb-4 d-flex align-items-center justify-content-center">
                <a href="yonetim/index.php" class="btn btn-lg btn-success w-100 py-5" style="font-size:2rem;">
                    <span style="font-size:2.5rem;">⚙️</span><br>
                    Ayarlar
                </a>
            </div>
            <!-- Sistem Bilgileri (Sağ Kısım) -->
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0">📈 Sistem Bilgileri</h5>
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-center" style="min-height:200px;">
                        <span class="text-muted">Sistemle ilgili bilgiler burada yer alacak.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

  
<?php include 'inc/footer.php'; ?>