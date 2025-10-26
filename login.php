<?php
session_start();
include_once 'db/db.php';
include_once 'inc/security.php';
include_once 'inc/auth.php';

$error_message = '';

// Giriş işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    // Rate limiting kontrolü
    if (!check_rate_limit('login', 5, 300)) {
        $error_message = 'Çok fazla giriş denemesi. Lütfen 5 dakika sonra tekrar deneyin.';
    } elseif (empty($username) || empty($password)) {
        $error_message = 'Kullanıcı adı ve şifre gereklidir.';
    } elseif (login_user($username, $password)) {
        header("Location: index.php");
        exit;
    } else {
        $error_message = 'Geçersiz kullanıcı adı veya şifre.';
    }
}

// Zaten giriş yapmışsa ana sayfaya yönlendir
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş - Adisyon Sistemi</title>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(120deg, #e0e7ef 0%, #f5f7fa 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-card {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card login-card">
                    <div class="card-header text-center bg-primary text-white">
                        <h4>🍽️ Adisyon Sistemi</h4>
                        <p class="mb-0">Giriş Yapın</p>
                    </div>
                    <div class="card-body">
                        <?php if ($error_message): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Kullanıcı Adı</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Şifre</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" name="login" class="btn btn-primary">Giriş Yap</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
