<?php
/**
 * Authentication ve Authorization sistemi
 */

// Kullanıcı girişi kontrolü
function check_auth() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
        header("Location: login.php");
        exit;
    }
}

// Admin yetkisi kontrolü
function check_admin() {
    check_auth();
    if ($_SESSION['user_role'] !== 'admin') {
        header("Location: unauthorized.php");
        exit;
    }
}

// Kullanıcı girişi
function login_user($username, $password) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ? AND active = 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['login_time'] = time();
            
            // Session hijacking koruması
            session_regenerate_id(true);
            
            return true;
        }
    }
    return false;
}

// Kullanıcı çıkışı
function logout_user() {
    session_destroy();
    header("Location: login.php");
    exit;
}

// Session timeout kontrolü (30 dakika)
function check_session_timeout() {
    if (isset($_SESSION['login_time'])) {
        $timeout = 30 * 60; // 30 dakika
        if (time() - $_SESSION['login_time'] > $timeout) {
            logout_user();
        }
    }
}

// Güvenli redirect
function safe_redirect($url) {
    $parsed_url = parse_url($url);
    if ($parsed_url === false || 
        (isset($parsed_url['scheme']) && !in_array($parsed_url['scheme'], ['http', 'https'])) ||
        (isset($parsed_url['host']) && $parsed_url['host'] !== $_SERVER['HTTP_HOST'])) {
        $url = 'index.php';
    }
    
    header("Location: " . $url);
    exit;
}

// Rate limiting
function check_rate_limit($action, $max_attempts = 5, $time_window = 300) {
    $key = $action . '_' . $_SERVER['REMOTE_ADDR'];
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
?>
