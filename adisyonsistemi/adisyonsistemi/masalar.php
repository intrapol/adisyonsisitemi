<?php include 'inc/header.php'; ?>

<div class="container mt-5">
 
    <div class="row">
        <?php
        $stmt = $conn->prepare("SELECT id, masa_adi, durum FROM masalar");
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $masa_id = $row['id'];
                $masa_adi = $row['masa_adi'];
                $durum = $row['durum'];

                // Renk seçimi
                if ($durum == 1) {
                    $btnClass = "btn-danger";
                } else {
                    $btnClass = "btn-success";
                }
        ?>
            <div class="col-md-3 mb-4">
                <div class="card shadow-sm border-0 h-100 text-center">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center">
                        <a href="masadetay.php?masa_id=<?php echo urlencode($masa_id); ?>" class="btn <?php echo $btnClass; ?> btn-lg w-100 fw-bold py-4 mb-2" style="font-size: 2.5rem; border-width: 4px; border-style: solid; border-color: <?php echo ($btnClass == 'btn-danger') ? '#dc3545' : '#198754'; ?>; box-shadow: 0 0 10px <?php echo ($btnClass == 'btn-danger') ? '#dc3545' : '#198754'; ?>;">
                            <?php echo htmlspecialchars($masa_adi); ?>
                        </a>
                    </div>
                </div>
            </div>
        <?php
            }
        } else {
            echo '<div class="col-12"><div class="alert alert-warning">Hiç masa bulunamadı.</div></div>';
        }
        ?>
    </div>
</div>











<?php include 'inc/footer.php'; ?>