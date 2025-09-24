<?php

/**
 * Güvenlik fonksiyonları
 * SQL enjeksiyon ve diğer güvenlik açıklarını önlemek için
 */

/**
 * Kullanıcı girdilerini temizle ve doğrula
 * @param string $input Kullanıcı girdisi
 * @param string $type Girdi tipi (string, int, float, email)
 * @return mixed Temizlenmiş girdi
 */
function sanitize_input($input, $type = 'string') {
    if ($input === null || $input === '') {
        return null;
    }
    
    switch ($type) {
        case 'int':
            return filter_var($input, FILTER_VALIDATE_INT);
        case 'float':
            return filter_var($input, FILTER_VALIDATE_FLOAT);
        case 'email':
            return filter_var($input, FILTER_VALIDATE_EMAIL);
        case 'string':
        default:
            return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}

/**
 * SQL sorgusu için güvenli parametre hazırlama
 * @param mixed $value Değer
 * @param string $type Veri tipi (i=integer, s=string, d=double, b=blob)
 * @return array Hazırlanmış parametre
 */
function prepare_sql_param($value, $type = 's') {
    switch ($type) {
        case 'i':
            return [intval($value), 'i'];
        case 'd':
            return [floatval($value), 'd'];
        case 's':
        default:
            return [strval($value), 's'];
    }
}

/**
 * CSRF token oluştur
 * @return string CSRF token
 */
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * CSRF token doğrula
 * @param string $token Doğrulanacak token
 * @return bool Token geçerli mi
 */
function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Güvenli redirect
 * @param string $url Yönlendirilecek URL
 */
function safe_redirect($url) {
    // URL'nin güvenli olduğunu kontrol et
    $parsed_url = parse_url($url);
    if ($parsed_url === false || 
        (isset($parsed_url['scheme']) && !in_array($parsed_url['scheme'], ['http', 'https'])) ||
        (isset($parsed_url['host']) && $parsed_url['host'] !== $_SERVER['HTTP_HOST'])) {
        $url = 'index.php';
    }
    
    header("Location: " . $url);
    exit;
}

/**
 * Dosya yükleme güvenliği
 * @param array $file $_FILES dizisi
 * @param array $allowed_types İzin verilen dosya tipleri
 * @param int $max_size Maksimum dosya boyutu (byte)
 * @return array Sonuç dizisi
 */
function secure_file_upload($file, $allowed_types = ['jpg', 'jpeg', 'png', 'gif'], $max_size = 2097152) {
    $result = ['success' => false, 'message' => '', 'filename' => ''];
    
    if (!isset($file['error']) || is_array($file['error'])) {
        $result['message'] = 'Geçersiz dosya yükleme.';
        return $result;
    }
    
    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            $result['message'] = 'Dosya seçilmedi.';
            return $result;
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            $result['message'] = 'Dosya boyutu çok büyük.';
            return $result;
        default:
            $result['message'] = 'Bilinmeyen hata.';
            return $result;
    }
    
    if ($file['size'] > $max_size) {
        $result['message'] = 'Dosya boyutu çok büyük.';
        return $result;
    }
    
    $file_info = pathinfo($file['name']);
    $extension = strtolower($file_info['extension']);
    
    if (!in_array($extension, $allowed_types)) {
        $result['message'] = 'Geçersiz dosya tipi.';
        return $result;
    }
    
    $filename = uniqid() . '.' . $extension;
    $result['success'] = true;
    $result['filename'] = $filename;
    
    return $result;
}

/**
 * Rate limiting kontrolü
 * @param string $key Benzersiz anahtar
 * @param int $max_attempts Maksimum deneme sayısı
 * @param int $time_window Zaman penceresi (saniye)
 * @return bool İzin veriliyor mu
 */
function check_rate_limit($key, $max_attempts = 10, $time_window = 300) {
    $cache_file = sys_get_temp_dir() . '/rate_limit_' . md5($key) . '.txt';
    $now = time();
    
    if (file_exists($cache_file)) {
        $data = json_decode(file_get_contents($cache_file), true);
        if ($data && $now - $data['first_attempt'] < $time_window) {
            if ($data['attempts'] >= $max_attempts) {
                return false;
            }
            $data['attempts']++;
        } else {
            $data = ['first_attempt' => $now, 'attempts' => 1];
        }
    } else {
        $data = ['first_attempt' => $now, 'attempts' => 1];
    }
    
    file_put_contents($cache_file, json_encode($data));
    return true;
}

/**
 * Güvenli şifre hash'leme
 * @param string $password Şifre
 * @return string Hash'lenmiş şifre
 */
function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Şifre doğrulama
 * @param string $password Girilen şifre
 * @param string $hash Hash'lenmiş şifre
 * @return bool Şifre doğru mu
 */
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * XSS koruması için çıktı temizleme
 * @param string $output Çıktı
 * @return string Temizlenmiş çıktı
 */
function clean_output($output) {
    return htmlspecialchars($output, ENT_QUOTES, 'UTF-8');
}

/**
 * SQL injection koruması için string escape
 * @param mysqli $conn Veritabanı bağlantısı
 * @param string $string Temizlenecek string
 * @return string Temizlenmiş string
 */
function escape_string($conn, $string) {
    return mysqli_real_escape_string($conn, $string);
}
?>
