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
                        <?php
                        // Veritabanı bağlantısı                       

                        // Gönderilmeyen siparişleri çek
                        $stmt = $conn->prepare("SELECT COUNT(*) as sayi FROM anliksiparis WHERE siparis_durumu = 0");
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $row = $result->fetch_assoc();
                        $gonderilmeyen_siparis = $row ? intval($row['sayi']) : 0;
                        $stmt->close();

                        // Saat, tarih, gün bilgisi
                        $saat = date('H:i');
                        $tarih = date('d.m.Y');
                        $gunler = [
                            'Pazar', 'Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi'
                        ];
                        $gun = $gunler[date('w')];
                        ?>
                        <div class="text-center">
                            <div class="mb-2">
                                <span class="fw-bold">Tarih:</span> <?php echo $tarih; ?><br>
                                <span class="fw-bold">Gün:</span> <?php echo $gun; ?><br>
                                <span class="fw-bold">Saat:</span> <?php echo $saat; ?>
                            </div>
                            <div class="alert alert-warning mb-0 py-2" style="font-size:1.1rem;">
                                <span class="fw-bold">Gönderilmeyen Sipariş:</span>
                                <span class="text-danger" style="font-size:1.3rem;"><?php echo $gonderilmeyen_siparis; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

  
<?php include 'inc/footer.php'; ?>