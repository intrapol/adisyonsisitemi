<?php include 'inc/header.php'; ?>

<?php
    // masa_id'yi GET ile al
    $masa_id = isset($_GET["masa_id"]) ? intval($_GET["masa_id"]) : 0;
    $masa_adi = "";
    if ($masa_id > 0) {
        $stmt_masa = $conn->prepare("SELECT masa_adi FROM masalar WHERE id = ?");
        $stmt_masa->bind_param("i", $masa_id);
        $stmt_masa->execute();
        $stmt_masa->bind_result($masa_adi);
        $stmt_masa->fetch();
        $stmt_masa->close();
    }
    // Yeni: Geri Dön ve Masa Yazısı
    if ($masa_adi != "") {
        echo '<div class="mb-4">';
        echo '<div class="d-flex align-items-center justify-content-center" style="gap: 14px;">';
        echo '<div style="flex-shrink:0;">';
        echo '<a href="masalar.php" class="btn btn-primary fw-bold text-uppercase" style="font-size:1.1rem; letter-spacing:0.5px; padding: 8px 18px; min-width:120px;">&larr; GERİ DÖN</a>';
        echo '</div>';
        echo '<div class="flex-grow-1 text-center">';
        echo '<span style="display:inline-block; background-color:#1e2330; font-size:2.2rem; font-weight:bold; letter-spacing:1px; color:#4a9eff; padding:0.5rem 1.5rem; border-radius:0.5rem; border: 2px solid #2d3441; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);"> Masa : '. htmlspecialchars($masa_adi) . '</span>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
  ?>
<div class="container mt-5">

  <div class="row">
    <div class="col-md-4">
      <!-- Sol taraf (4 birim) -->
      <div class="card h-100">
        <div class="card-body">

        <?php

        // masa_id'yi GET ile al
        $masa_id = isset($_GET["masa_id"]) ? intval($_GET["masa_id"]) : 0;

        // Sipariş arttır/eksilt/kaldır işlemleri
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // CSRF token kontrolü (opsiyonel - sadece token varsa kontrol et)
            if (isset($_POST['csrf_token']) && !verify_csrf_token($_POST['csrf_token'])) {
                die('Güvenlik hatası: Geçersiz token');
            }
            if (isset($_POST["arttir"])) {
                $siparis_id = intval($_POST["siparis_id"]);
                $stmt = $conn->prepare("UPDATE anliksiparis SET adet = adet + 1 WHERE id = ?");
                $stmt->bind_param("i", $siparis_id);
                $stmt->execute();
                $stmt->close();
            }
            if (isset($_POST["eksilt"])) {
                $siparis_id = intval($_POST["siparis_id"]);
                // Adet 1'den büyükse azalt
                $stmt = $conn->prepare("UPDATE anliksiparis SET adet = adet - 1 WHERE id = ? AND adet > 1");
                $stmt->bind_param("i", $siparis_id);
                $stmt->execute();
                $stmt->close();
            }
            if (isset($_POST["kaldir"])) {
                $siparis_id = intval($_POST["siparis_id"]);
                $stmt = $conn->prepare("DELETE FROM anliksiparis WHERE id = ?");
                $stmt->bind_param("i", $siparis_id);
                $stmt->execute();
                $stmt->close();

                // anliksiparis tablosunda bu masa için başka sipariş var mı kontrol et
                if ($masa_id > 0) {
                    $check_stmt = $conn->prepare("SELECT COUNT(*) FROM anliksiparis WHERE masa_id = ?");
                    $check_stmt->bind_param("i", $masa_id);
                    $check_stmt->execute();
                    $check_stmt->bind_result($siparis_sayisi);
                    $check_stmt->fetch();
                    $check_stmt->close();

                    // Eğer hiç sipariş kalmadıysa masayı boş olarak işaretle
                    if ($siparis_sayisi == 0) {
                        $update_masa_stmt = $conn->prepare("UPDATE masalar SET durum = 0 WHERE id = ?");
                        $update_masa_stmt->bind_param("i", $masa_id);
                        $update_masa_stmt->execute();
                        $update_masa_stmt->close();
                    }
                }
            }
        }

        // Siparişleri çek
        $stmt = $conn->prepare("SELECT * FROM anliksiparis WHERE masa_id = ?");
        $stmt->bind_param("i", $masa_id);
        $stmt->execute();
        $result = $stmt->get_result();

        echo '<h5 class="mb-3">Masa Siparişleri</h5>';
        echo '<table class="table table-bordered table-sm">';
        echo '<thead class="thead-light"><tr>
                <th>Ürün Adı</th>
                <th>Adet</th>
                <th>Tutar</th>
                <th>İşlem</th>
              </tr></thead><tbody>';

        $toplam_tutar = 0.0;

        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $urun_id = intval($row["urun_id"]);
                $adet = intval($row["adet"]);
                $siparis_id = intval($row["id"]);

                // Ürün bilgilerini çek
                $urun_stmt = $conn->prepare("SELECT urun_adi, urun_fiyat FROM urunler WHERE id = ?");
                $urun_stmt->bind_param("i", $urun_id);
                $urun_stmt->execute();
                $urun_result = $urun_stmt->get_result();
                $urun_adi = "Bilinmiyor";
                $urun_fiyat = 0.0;
                if ($urun_result && $urun_result->num_rows > 0) {
                    $urun = $urun_result->fetch_assoc();
                    $urun_adi = htmlspecialchars($urun["urun_adi"]);
                    // urun_fiyat artık float, floatval ile kesinleştir
                    $urun_fiyat = floatval($urun["urun_fiyat"]);
                }
                $tutar = $urun_fiyat * $adet;
                $toplam_tutar += $tutar;

                echo '<tr>';
                echo '<td>' . $urun_adi . '</td>';
                echo '<td>' . $adet . '</td>';
                echo '<td>' . number_format($tutar, 2) . ' ₺</td>';
                echo '<td>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="csrf_token" value="' . generate_csrf_token() . '">
                            <input type="hidden" name="siparis_id" value="' . $siparis_id . '">
                            <button type="submit" name="arttir" class="btn btn-success btn-sm">+</button>
                            <button type="submit" name="eksilt" class="btn btn-warning btn-sm">-</button>
                            <button type="submit" name="kaldir" class="btn btn-danger btn-sm">Kaldır</button>
                        </form>
                        
                      </td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="4" class="text-center">Bu masada sipariş yok.</td></tr>';
        }
        echo '</tbody></table>';

        // Toplam tutarı tablo şeklinde, ortada bölme çizgisi olacak şekilde göster
        echo '<table class="table mt-3" style="max-width: 400px; margin-left: 0;">
                <tr style="background: rgba(237, 137, 54, 0.2); border: 2px solid #ed8936;">
                    <td style="font-size: 1.3rem; font-weight: bold; color: #e4e6eb; border: none; border-right: 2px solid #ed8936; width: 50%; text-align: center; vertical-align: middle;">TOPLAM TUTAR:</td>
                    <td style="font-size: 2rem; font-weight: bold; color: #48bb78; text-align: right; border: none; width: 50%;">' . number_format($toplam_tutar, 2) . ' ₺</td>
                </tr>
              </table>';

        // --- MASA BİRLEŞTİR POST İŞLEMİ ---
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['masa_birlestir_sec'])) {
            // CSRF token kontrolü
            if (isset($_POST['csrf_token']) && !verify_csrf_token($_POST['csrf_token'])) {
                die('Güvenlik hatası: Geçersiz token');
            }
            $mevcut_masa_id = isset($_POST['mevcut_masa_id']) ? intval($_POST['mevcut_masa_id']) : 0;
            $hedef_masa_id = isset($_POST['hedef_masa_id']) ? intval($_POST['hedef_masa_id']) : 0;
            if ($mevcut_masa_id > 0 && $hedef_masa_id > 0 && $mevcut_masa_id != $hedef_masa_id) {
                // anliksiparis tablosunda mevcut masanın siparişlerini hedef masaya aktar
                $stmt = $conn->prepare("UPDATE anliksiparis SET masa_id = ? WHERE masa_id = ?");
                $stmt->bind_param("ii", $hedef_masa_id, $mevcut_masa_id);
                $stmt->execute();
                $stmt->close();
                // Mevcut masayı boş yap
                $update_eski_stmt = $conn->prepare("UPDATE masalar SET durum = 0 WHERE id = ?");
                $update_eski_stmt->bind_param("i", $mevcut_masa_id);
                $update_eski_stmt->execute();
                $update_eski_stmt->close();
                // Hedef masa zaten dolu, durumunu değiştirmeye gerek yok
                // Yönlendir

                echo '<script>window.location.href = "masadetay.php?masa_id=' . $hedef_masa_id . '";</script>';
                
               
                exit;
            }
        }

        // Ödeme al işlemi
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['odeme_al'])) {
            // CSRF token kontrolü (opsiyonel - sadece token varsa kontrol et)
            if (isset($_POST['csrf_token']) && !verify_csrf_token($_POST['csrf_token'])) {
                die('Güvenlik hatası: Geçersiz token');
            }
            $masa_id = isset($_GET['masa_id']) ? intval($_GET['masa_id']) : 0;
            if ($masa_id > 0) {
                // Anlık siparişleri çek
                $siparisler = [];
                $stmt = $conn->prepare("SELECT * FROM anliksiparis WHERE masa_id = ?");
                $stmt->bind_param("i", $masa_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $siparisler[] = $row;
                    }
                }
                // Her siparişi rapor tablosuna ekle
                foreach ($siparisler as $siparis) {
                    $urun_id = intval($siparis['urun_id']);
                    $adet = intval($siparis['adet']);
                    // Ürün bilgisi çek
                    $urun_stmt = $conn->prepare("SELECT urun_adi, urun_fiyat FROM urunler WHERE id = ?");
                    $urun_stmt->bind_param("i", $urun_id);
                    $urun_stmt->execute();
                    $urun_result = $urun_stmt->get_result();
                    if ($urun_result && $urun_result->num_rows > 0) {
                        $urun = $urun_result->fetch_assoc();
                        $urun_adi = $urun['urun_adi'];
                        // urun_fiyat artık float, floatval ile kesinleştir
                        $urun_fiyat = floatval($urun['urun_fiyat']);
                        $tarih = date('Y-m-d');
                        $stmt = $conn->prepare("INSERT INTO rapor (urun_adi, urun_fiyat, adet, tarih) VALUES (?, ?, ?, ?)");
                        $stmt->bind_param("sdis", $urun_adi, $urun_fiyat, $adet, $tarih);
                        $stmt->execute();
                        $stmt->close();
                    }
                }
                // Anlık siparişleri sil
                $delete_stmt = $conn->prepare("DELETE FROM anliksiparis WHERE masa_id = ?");
                $delete_stmt->bind_param("i", $masa_id);
                $delete_stmt->execute();
                $delete_stmt->close();

                // Masa durumunu boş olarak güncelle (isteğe bağlı)
                $update_stmt = $conn->prepare("UPDATE masalar SET durum = 0 WHERE id = ?");
                $update_stmt->bind_param("i", $masa_id);
                $update_stmt->execute();
                $update_stmt->close();
                // masalar.php'ye yönlendir
                echo '<script>window.location.href = "masalar.php?odeme=ok";</script>';
                exit;
            }
        }

        // Masa değiştir işlemi
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['masa_degistir_sec'])) {
            // CSRF token kontrolü (opsiyonel - sadece token varsa kontrol et)
            if (isset($_POST['csrf_token']) && !verify_csrf_token($_POST['csrf_token'])) {
                die('Güvenlik hatası: Geçersiz token');
            }
            // Hem mevcut masa_id hem de yeni masa_id'yi POST ile gönderiyoruz
            $masa_id = isset($_POST['mevcut_masa_id']) ? intval($_POST['mevcut_masa_id']) : 0;
            $yeni_masa_id = isset($_POST['yeni_masa_id']) ? intval($_POST['yeni_masa_id']) : 0;
            if ($masa_id > 0 && $yeni_masa_id > 0 && $masa_id != $yeni_masa_id) {
                // anliksiparis tablosunda masa_id'yi güncelle
                $stmt = $conn->prepare("UPDATE anliksiparis SET masa_id = ? WHERE masa_id = ?");
                $stmt->bind_param("ii", $yeni_masa_id, $masa_id);
                $stmt->execute();
                $stmt->close();
                // Eski masayı boş, yeni masayı dolu yap
                $update_eski_stmt = $conn->prepare("UPDATE masalar SET durum = 0 WHERE id = ?");
                $update_eski_stmt->bind_param("i", $masa_id);
                $update_eski_stmt->execute();
                $update_eski_stmt->close();
                
                $update_yeni_stmt = $conn->prepare("UPDATE masalar SET durum = 1 WHERE id = ?");
                $update_yeni_stmt->bind_param("i", $yeni_masa_id);
                $update_yeni_stmt->execute();
                $update_yeni_stmt->close();
                // Yönlendir
                echo '<script>window.location.href = "masadetay.php?masa_id=' . $yeni_masa_id . '";</script>';
                exit;
            }
        }
        ?>

        <style>
        .btn-lg-custom {
            font-size: 1.35rem !important;
            padding: 0.8rem 1.25rem !important;
            font-weight: bold !important;
            min-height: 56px;
            border-radius: 0.5rem !important;
            transition: all 0.13s cubic-bezier(0.374, 1.41, 0.728, 1.03);
        }
        .btn-lg-custom:focus, .btn-lg-custom:active, .btn-lg-custom:hover {
            box-shadow: 0 0 0 0.15rem rgba(33,37,41,0.28), 0 3px 16px #a4bfff59 !important;
            outline: none !important;
            opacity: 0.93;
            transform: scale(1.045);
        }
        .btn-birlestir {
            background: #ff5e00 !important;
            color: #fff !important;
        }
        .btn-birlestir:hover, .btn-birlestir:focus {
            background: #d94a00 !important;
            color: #fff !important;
        }
        </style>

        <div class="mb-4">
            <div class="row g-3">
                <!-- Ödeme Al ve Ayrı Ödeme Al butonları yan yana üstte -->
                <div class="col-6 d-grid">
                    <form method="post" style="display:block;">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        <button type="submit" name="odeme_al"
                                class="btn btn-success btn-lg-custom w-100"
                                onclick="return confirm('Bu masanın siparişleri ödenmiş olarak işaretlenecek ve silinecek. Emin misiniz?');">
                            <span class="align-middle">💸</span> Ödeme Al
                        </button>
                    </form>
                </div>
                <div class="col-6 d-grid">
                    <a href="ayriodeme.php<?php echo isset($_GET['masa_id']) ? '?masa_id=' . intval($_GET['masa_id']) : ''; ?>"
                       class="btn btn-warning btn-lg-custom w-100">
                        <span class="align-middle">👥</span> Ayrı Ödeme Al
                    </a>
                </div>
                <!-- Masa Değiştir ve Masa Birleştir butonları yan yana altta -->
                <div class="col-6 d-grid">
                    <button type="button"
                        class="btn btn-secondary btn-lg-custom w-100"
                        data-bs-toggle="modal"
                        data-bs-target="#masaDegistirModal"
                        id="masaDegistirBtn">
                        <span class="align-middle">🔁</span> Masa Değiştir
                    </button>
                </div>
                <div class="col-6 d-grid">
                    <button type="button"
                        class="btn btn-lg-custom btn-birlestir w-100"
                        data-bs-toggle="modal"
                        data-bs-target="#masaBirlestirModal"
                        id="masaBirlestirBtn">
                        <span class="align-middle">➕</span> Masa Birleştir
                    </button>
                </div>
            </div>
            <!-- Adisyon Yazdır butonu alta geniş -->
            <div class="row mt-3">
                <div class="col-12 d-grid">
                    <a href="adisyonyazdir.php<?php echo isset($_GET['masa_id']) ? '?masa_id=' . intval($_GET['masa_id']) : ''; ?>"
                       class="btn btn-info btn-lg-custom w-100"
                       target="_blank" style="font-weight:bold;">
                        <span class="align-middle">🧾</span> Adisyon Yazdır
                    </a>
                </div>
            </div>
        </div>


        <!-- Masa Değiştir Modal -->
        <div class="modal fade" id="masaDegistirModal" tabindex="-1" aria-labelledby="masaDegistirModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <form method="post">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                <input type="hidden" name="mevcut_masa_id" value="<?php echo isset($_GET['masa_id']) ? intval($_GET['masa_id']) : 0; ?>">
                <div class="modal-header">
                  <h5 class="modal-title" id="masaDegistirModalLabel">Masa Değiştir</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
                </div>
                <div class="modal-body">
                  <div class="mb-3">
                    <label for="yeni_masa_id" class="form-label">Yeni Masa Seçiniz</label>
                    <select class="form-select" name="yeni_masa_id" id="yeni_masa_id" required>
                      <option value="">Masa Seçiniz</option>
                      <?php
                      $mevcut_masa_id = isset($_GET['masa_id']) ? intval($_GET['masa_id']) : 0;
                      $masalar_stmt = $conn->prepare("SELECT id, masa_adi FROM masalar WHERE id != ? AND durum != 1");
                      $masalar_stmt->bind_param("i", $mevcut_masa_id);
                      $masalar_stmt->execute();
                      $masalar_result = $masalar_stmt->get_result();
                      if ($masalar_result && $masalar_result->num_rows > 0) {
                          while ($masa = $masalar_result->fetch_assoc()) {
                              echo '<option value="' . intval($masa['id']) . '">' . htmlspecialchars($masa['masa_adi']) . '</option>';
                          }
                      }
                      ?>
                    </select>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="submit" name="masa_degistir_sec" class="btn btn-success">Değiştir</button>
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                </div>
              </form>
            </div>
          </div>
        </div>

        <!-- Masa Birleştir Modal -->
        <div class="modal fade" id="masaBirlestirModal" tabindex="-1" aria-labelledby="masaBirlestirModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <form method="post">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                <input type="hidden" name="mevcut_masa_id" value="<?php echo isset($_GET['masa_id']) ? intval($_GET['masa_id']) : 0; ?>">
                <div class="modal-header" style="background: #ff5e00; color: #fff;">
                  <h5 class="modal-title" id="masaBirlestirModalLabel">Masa Birleştir</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
                </div>
                <div class="modal-body">
                  <div class="mb-3">
                    <label for="hedef_masa_id" class="form-label">Birleştirilecek (Dolu) Masa Seçiniz</label>
                    <select class="form-select" name="hedef_masa_id" id="hedef_masa_id" required>
                      <option value="">Dolu Masa Seçiniz</option>
                      <?php
                      $mevcut_masa_id = isset($_GET['masa_id']) ? intval($_GET['masa_id']) : 0;
                      $masalar_stmt = $conn->prepare("SELECT id, masa_adi FROM masalar WHERE id != ? AND durum = 1");
                      $masalar_stmt->bind_param("i", $mevcut_masa_id);
                      $masalar_stmt->execute();
                      $masalar_result = $masalar_stmt->get_result();
                      if ($masalar_result && $masalar_result->num_rows > 0) {
                          while ($masa = $masalar_result->fetch_assoc()) {
                              echo '<option value="' . intval($masa['id']) . '">' . htmlspecialchars($masa['masa_adi']) . '</option>';
                          }
                      }
                      ?>
                    </select>
                  </div>
                  <div class="alert alert-warning" style="font-size:0.95rem;">
                    Seçtiğiniz masanın siparişleriyle bu masanın siparişleri birleştirilecektir. Bu masanın siparişleri seçilen masaya aktarılır ve bu masa boş olarak işaretlenir.
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="submit" name="masa_birlestir_sec" class="btn" style="background: #ff5e00; color: #fff;">Birleştir</button>
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                </div>
              </form>
            </div>
          </div>
        </div>

        </div>
      </div>
    </div>

    <div class="col-md-8">
      <!-- Sağ taraf (8 birim) -->
      <div class="card h-100">
        <div class="card-body">
        
        <?php
        // Kategorileri çek
        $kategori_query = "SELECT id, kategori_adi FROM kategori";
        $kategori_result = $conn->query($kategori_query);

        // Ürünleri çek
        $urunler_query = "SELECT id, urun_adi, urun_fiyat, kategori_id FROM urunler";
        $urunler_result = $conn->query($urunler_query);

        // Ürünleri kategori_id'ye göre grupla
        $urunler_by_kategori = array();
        if ($urunler_result && $urunler_result->num_rows > 0) {
            while ($urun = $urunler_result->fetch_assoc()) {
                // urun_fiyat artık float, floatval ile kesinleştir
                $urun['urun_fiyat'] = floatval($urun['urun_fiyat']);
                $urunler_by_kategori[$urun['kategori_id']][] = $urun;
            }
        }

        // Seçili ürün id'si ve adet için post işlemi
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gonder'])) {
            // CSRF token kontrolü (opsiyonel - sadece token varsa kontrol et)
            if (isset($_POST['csrf_token']) && !verify_csrf_token($_POST['csrf_token'])) {
                
                die('Güvenlik hatası: Geçersiz token');
            }
            $masa_id = isset($_GET['masa_id']) ? intval($_GET['masa_id']) : 0;
            $urun_id = isset($_POST['secili_urun']) ? intval($_POST['secili_urun']) : 0;
            $adet = isset($_POST['adet']) ? intval($_POST['adet']) : 1;
            $saat = date('H:i:s');
            $aciklama = ' ';
            $siparis_durumu = 0;
           

            if (!empty($adet) && !empty($urun_id)) {

                
                $stmt = $conn->prepare("INSERT INTO anliksiparis (masa_id, urun_id, adet, saat, aciklama, siparis_durumu) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iiissi", $masa_id, $urun_id, $adet, $saat, $aciklama, $siparis_durumu);
                $stmt->execute();
                $stmt->close();
            
            // Şu andaki masanın durumunu 1 olarak güncelle
            if ($masa_id > 0) {
                $update_masa = $conn->prepare("UPDATE masalar SET durum = 1 WHERE id = ?");
                $update_masa->bind_param("i", $masa_id);
                $update_masa->execute();
                $update_masa->close();
            }
            
            echo '<script>window.location.href = window.location.href;</script>';
            exit;

          
            } else {
                echo '<div class="alert alert-danger">Lütfen ürün ve adet seçiniz.</div>';
            }
       
        }
        ?>

        <form method="post" id="siparisForm">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            <div class="d-flex flex-row mb-3" style="gap:0; overflow-x: auto;">
                <?php
                if ($kategori_result && $kategori_result->num_rows > 0) {
                    while ($kategori = $kategori_result->fetch_assoc()) {
                        $kategori_id = $kategori['id'];
                        $kategori_adi = htmlspecialchars($kategori['kategori_adi']);
                        echo '<div>';
                        echo '<div class="text-center fw-bold mb-2">' . $kategori_adi . '</div>';
                        
                        echo '<div class="d-flex flex-column" style="gap:0;">';
                        if (isset($urunler_by_kategori[$kategori_id])) {
                            foreach ($urunler_by_kategori[$kategori_id] as $urun) {
                                $urun_id = $urun['id'];
                                $urun_adi = htmlspecialchars($urun['urun_adi']);
                                // urun_fiyat artık float, istenirse burada da gösterilebilir
                                echo '
                                <div class="urun-kutu" 
                                    data-urun-id="' . $urun_id . '" 
                                    style="border:1px solid #2d3441; border-radius:8px; padding:10px; cursor:pointer; background:#1e2330; min-width:120px; text-align:center; transition:all 0.3s ease; margin-bottom:0; color:#e4e6eb;">
                                    <div class="fw-bold" style="font-size:1.08rem;">' . $urun_adi . '</div>
                                </div>
                                ';
                            }
                        } else {
                            echo '<div class="text-muted">Ürün yok</div>';
                        }
                        echo '</div>';
                        echo '</div>';
                    }
                }
                ?>
            </div>
            <input type="hidden" name="secili_urun" id="secili_urun" value="">
            <div class="d-flex justify-content-center align-items-start mb-3" style="gap:20px;">
                <!-- Sol taraf: Adet girme ve Gönder butonu -->
                <div class="d-flex flex-column align-items-center justify-content-start" style="flex:1;">
                    <div style="width:100%; text-align:center;">
                        <label for="adet" class="form-label" style="font-size: 0.95rem; border: 1px solid #ed8936; border-radius: 8px; padding: 4px 12px; display: inline-block; color: #e4e6eb;">Adet Giriniz</label>
                    </div>
                    <input type="text" class="form-control text-center mb-3" id="adet" name="adet" value="" readonly style="font-size:1.1rem; max-width:120px; margin:auto; background-color: rgba(237, 137, 54, 0.2); color: #e4e6eb; border: 1px solid #ed8936;">
                    <div class="text-center" style="margin-top: 8px;">
                        <button type="submit" name="gonder" class="btn btn-success btn-sm" style="font-size:1rem; min-width:70px;">Gönder</button>
                    </div>
                </div>
                <!-- Sağ taraf: Rakam tuşları -->
                <div style="flex:1; min-width:180px;">
                    <div>
                        <div class="d-flex flex-row mb-1">
                            <button type="button" class="btn btn-outline-primary rakam-btn mx-1 my-0 p-0" style="width:44px; height:44px; font-size:1.25rem;" data-rakam="7">7</button>
                            <button type="button" class="btn btn-outline-primary rakam-btn mx-1 my-0 p-0" style="width:44px; height:44px; font-size:1.25rem;" data-rakam="8">8</button>
                            <button type="button" class="btn btn-outline-primary rakam-btn mx-1 my-0 p-0" style="width:44px; height:44px; font-size:1.25rem;" data-rakam="9">9</button>
                        </div>
                        <div class="d-flex flex-row mb-1">
                            <button type="button" class="btn btn-outline-primary rakam-btn mx-1 my-0 p-0" style="width:44px; height:44px; font-size:1.25rem;" data-rakam="4">4</button>
                            <button type="button" class="btn btn-outline-primary rakam-btn mx-1 my-0 p-0" style="width:44px; height:44px; font-size:1.25rem;" data-rakam="5">5</button>
                            <button type="button" class="btn btn-outline-primary rakam-btn mx-1 my-0 p-0" style="width:44px; height:44px; font-size:1.25rem;" data-rakam="6">6</button>
                        </div>
                        <div class="d-flex flex-row mb-1">
                            <button type="button" class="btn btn-outline-primary rakam-btn mx-1 my-0 p-0" style="width:44px; height:44px; font-size:1.25rem;" data-rakam="1">1</button>
                            <button type="button" class="btn btn-outline-primary rakam-btn mx-1 my-0 p-0" style="width:44px; height:44px; font-size:1.25rem;" data-rakam="2">2</button>
                            <button type="button" class="btn btn-outline-primary rakam-btn mx-1 my-0 p-0" style="width:44px; height:44px; font-size:1.25rem;" data-rakam="3">3</button>
                        </div>
                        <div class="d-flex flex-row">
                            <button type="button" class="btn btn-outline-primary rakam-btn mx-1 my-0 p-0" style="width:44px; height:44px; font-size:1.25rem;" data-rakam="0">0</button>
                            <button type="button" class="btn btn-outline-warning mx-1 my-0 p-0" style="width:44px; height:44px; font-size:1.05rem;" id="sil-btn">Sil</button>
                            <button type="button" class="btn btn-outline-danger mx-1 my-0 p-0" style="width:44px; height:44px; font-size:1.05rem;" id="tumunu-sil-btn">C</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <script>
        // Modal açma işlevleri (Masa Değiştir ve Masa Birleştir için)
        document.addEventListener('DOMContentLoaded', function() {
            // Masa Değiştir Modal
            const masaDegistirBtn = document.getElementById('masaDegistirBtn');
            const masaDegistirModal = document.getElementById('masaDegistirModal');
            if (masaDegistirBtn && masaDegistirModal) {
                masaDegistirBtn.addEventListener('click', function(e) {
                    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        const bsModal = new bootstrap.Modal(masaDegistirModal);
                        bsModal.show();
                    } else {
                        masaDegistirModal.style.display = 'block';
                        masaDegistirModal.classList.add('show');
                        masaDegistirModal.setAttribute('aria-hidden', 'false');
                        document.body.classList.add('modal-open');
                        const backdrop = document.createElement('div');
                        backdrop.className = 'modal-backdrop fade show';
                        backdrop.id = 'modalBackdropDegistir';
                        document.body.appendChild(backdrop);
                    }
                });
                // Kapatma
                const closeButtons = masaDegistirModal.querySelectorAll('[data-bs-dismiss="modal"], .btn-close');
                closeButtons.forEach(btn => {
                    btn.addEventListener('click', function() {
                        masaDegistirModal.style.display = 'none';
                        masaDegistirModal.classList.remove('show');
                        masaDegistirModal.setAttribute('aria-hidden', 'true');
                        document.body.classList.remove('modal-open');
                        const backdrop = document.getElementById('modalBackdropDegistir');
                        if (backdrop) backdrop.remove();
                    });
                });
            }

            // Masa Birleştir Modal
            const masaBirlestirBtn = document.getElementById('masaBirlestirBtn');
            const masaBirlestirModal = document.getElementById('masaBirlestirModal');
            if (masaBirlestirBtn && masaBirlestirModal) {
                masaBirlestirBtn.addEventListener('click', function(e) {
                    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        const bsModal = new bootstrap.Modal(masaBirlestirModal);
                        bsModal.show();
                    } else {
                        masaBirlestirModal.style.display = 'block';
                        masaBirlestirModal.classList.add('show');
                        masaBirlestirModal.setAttribute('aria-hidden', 'false');
                        document.body.classList.add('modal-open');
                        const backdrop = document.createElement('div');
                        backdrop.className = 'modal-backdrop fade show';
                        backdrop.id = 'modalBackdropBirlestir';
                        document.body.appendChild(backdrop);
                    }
                });
                // Kapatma
                const closeButtons = masaBirlestirModal.querySelectorAll('[data-bs-dismiss="modal"], .btn-close');
                closeButtons.forEach(btn => {
                    btn.addEventListener('click', function() {
                        masaBirlestirModal.style.display = 'none';
                        masaBirlestirModal.classList.remove('show');
                        masaBirlestirModal.setAttribute('aria-hidden', 'true');
                        document.body.classList.remove('modal-open');
                        const backdrop = document.getElementById('modalBackdropBirlestir');
                        if (backdrop) backdrop.remove();
                    });
                });
            }
        });

        // Ürün seçimi
        document.querySelectorAll('.urun-kutu').forEach(function(kutu) {
            kutu.addEventListener('click', function() {
                document.querySelectorAll('.urun-kutu').forEach(function(k) {
                    k.style.background = '#1e2330';
                    k.style.borderColor = '#2d3441';
                    k.style.color = '#e4e6eb';
                });
                kutu.style.background = '#48bb78';
                kutu.style.borderColor = '#38a169';
                kutu.style.color = '#fff';
                document.getElementById('secili_urun').value = kutu.getAttribute('data-urun-id');
            });
        });

        // Rakam butonları
        document.querySelectorAll('.rakam-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var rakam = btn.getAttribute('data-rakam');
                var adetInput = document.getElementById('adet');
                if (adetInput.value.length < 3) { // max 3 hane
                    adetInput.value += rakam;
                }
            });
        });

        // Sil butonu
        document.getElementById('sil-btn').addEventListener('click', function() {
            var adetInput = document.getElementById('adet');
            adetInput.value = adetInput.value.slice(0, -1);
        });

        // Tümünü sil butonu
        document.getElementById('tumunu-sil-btn').addEventListener('click', function() {
            document.getElementById('adet').value = '';
        });

        // Form gönderim kontrolü
        document.getElementById('siparisForm').addEventListener('submit', function(e) {
            var seciliUrun = document.getElementById('secili_urun').value;
            var adet = document.getElementById('adet').value;
            if (!seciliUrun) {
                alert('Lütfen bir ürün seçiniz.');
                e.preventDefault();
            } else if (!adet || parseInt(adet) <= 0) {
                alert('Lütfen adet giriniz.');
                e.preventDefault();
            }
        });

        // Seçili ürünü tekrar seçince rengi sıfırla
        document.querySelectorAll('.urun-kutu').forEach(function(kutu) {
            kutu.addEventListener('mouseleave', function() {
                if (kutu.getAttribute('data-urun-id') !== document.getElementById('secili_urun').value) {
                    kutu.style.color = '';
                }
            });
        });
        </script>






        </div>
      </div>
    </div>
  </div>
</div>




<?php include 'inc/footer.php'; ?>
