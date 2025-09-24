<?php 
session_start();
include_once 'db/db.php';
include_once 'security.php';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adisyon Sistemi</title>
    
    <!-- Bootstrap CSS -->
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        body {
            /* Daha göz yormayan, şık bir arka plan için yumuşak bir degrade ve hafif renkler kullanalım */
            background: linear-gradient(120deg, #e0e7ef 0%, #f5f7fa 100%);
            /* Alternatif olarak, aşağıdaki gibi sade bir renk de kullanılabilir:
            background-color: #e9ecef;
            */
        }
        .header {
            background: linear-gradient(135deg, #5f72bd 0%, #9b23ea 100%);
            color: white;
            padding: 2rem 0;
        }
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border: none;
        }
    </style>
</head>
<body>

    <!-- Küçük Header ve Menü -->
    <div class="header py-2">
        <div class="container">
            <div class="row align-items-center">
                <!-- Logo ve Başlık -->
                <div class="col-auto">
                    <span style="font-size:1.5rem;">🍽️</span>
                    <span class="h4 align-middle ms-2">Adisyon Sistemi</span>
                </div>
                <!-- Menü -->
                <div class="col d-flex justify-content-center">
                    <nav>
                        <ul class="nav">
                            <li class="nav-item">
                                <a class="nav-link text-white" href="/index.php"><strong>Anasayfa</strong></a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-white" href="masalar.php"><strong>Masalar</strong></a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-white" href="../mutfak.php"><strong>Mutfak</strong></a>
                            </li>
                        </ul>
                    </nav>
                </div>
                <!-- Yönetim (En sağda) -->
                
            </div>
        </div>
    </div>