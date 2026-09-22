<?php
/**
 * QR Menü & Yönetim Paneli - Yapılandırma Dosyası
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Zaman Dilimi
date_default_timezone_set('Europe/Istanbul');

// Hata Raporlama (Geliştirme aşamasında açık)
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 0);

// Veritabanı Ayarları
// 'mysql' (Canlı Sunucu) veya 'sqlite' (Sıfır Ayar)
define('DB_DRIVER', 'mysql'); 

// SQLite Veritabanı Dosya Yolu
define('DB_SQLITE_PATH', __DIR__ . '/data/menu.sqlite');

// MySQL Bağlantı Bilgileri
define('DB_HOST', 'localhost');
define('DB_NAME', 'Maremonte');
define('DB_USER', 'Maremonte');
define('DB_PASS', 'Maremonte1122334455..');
define('DB_CHARSET', 'utf8mb4');

// Temel Dizin & URL Tanımları
define('BASE_PATH', __DIR__);
define('UPLOAD_PATH', __DIR__ . '/uploads');

// Otomatik URL Tespiti
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
$baseUrl = rtrim($protocol . $host . $scriptDir, '/\\');
// Eğer admin dizinindeysek base_url bir üst dizin olsun
if (basename($baseUrl) === 'admin') {
    $baseUrl = dirname($baseUrl);
}
define('BASE_URL', $baseUrl);

/**
 * CSRF Token Oluşturma & Doğrulama
 */
function csrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token) {
    return !empty($token) && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Admin Yetkilendirme Yardımcıları
 */
function isAdminLoggedIn() {
    return !empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function requireAdmin() {
    if (!isAdminLoggedIn()) {
        header('Location: ' . BASE_URL . '/admin/login.php');
        exit;
    }
}

/**
 * Güvenli Girdi Temizleme
 */
function clean($data) {
    if (is_array($data)) {
        return array_map('clean', $data);
    }
    return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
}

/**
 * Fiyat Formatlayıcı
 */
function formatPrice($price, $currency = '₺') {
    return number_format((float)$price, 2, ',', '.') . ' ' . $currency;
}

/**
 * Resim Yükleme ve Optimizasyon Fonksiyonu
 */
function uploadImage($file, $subFolder = 'products', $maxWidth = 1200, $maxHeight = 1200) {
    if (empty($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Dosya yüklenemedi veya seçilmedi.'];
    }

    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($fileInfo, $file['tmp_name']);
    finfo_close($fileInfo);

    if (!in_array($mimeType, $allowedTypes)) {
        return ['success' => false, 'error' => 'Sadece JPG, PNG, WEBP ve GIF formatları desteklenmektedir.'];
    }

    $targetDir = UPLOAD_PATH . '/' . trim($subFolder, '/');
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $extension = 'jpg';
    if ($mimeType === 'image/png') $extension = 'png';
    elseif ($mimeType === 'image/webp') $extension = 'webp';
    elseif ($mimeType === 'image/gif') $extension = 'gif';

    $fileName = uniqid('img_', true) . '.' . $extension;
    $targetFile = $targetDir . '/' . $fileName;

    // Resim boyutlandırma / optimizasyon (GD)
    if (function_exists('imagecreatefromstring')) {
        $sourceData = file_get_contents($file['tmp_name']);
        $srcImg = @imagecreatefromstring($sourceData);

        if ($srcImg) {
            $origWidth = imagesx($srcImg);
            $origHeight = imagesy($srcImg);

            // En boy oranını koruyarak yeniden boyutlandırma
            $ratio = min($maxWidth / $origWidth, $maxHeight / $origHeight, 1);
            $newWidth = (int)round($origWidth * $ratio);
            $newHeight = (int)round($origHeight * $ratio);

            $dstImg = imagecreatetruecolor($newWidth, $newHeight);

            // Şeffaflık koruması (PNG & WEBP)
            if ($mimeType === 'image/png' || $mimeType === 'image/webp') {
                imagealphablending($dstImg, false);
                imagesavealpha($dstImg, true);
                $transparent = imagecolorallocatealpha($dstImg, 255, 255, 255, 127);
                imagefilledrectangle($dstImg, 0, 0, $newWidth, $newHeight, $transparent);
            }

            imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

            // Kaydetme
            if ($extension === 'png') {
                imagepng($dstImg, $targetFile, 8);
            } elseif ($extension === 'webp' && function_exists('imagewebp')) {
                imagewebp($dstImg, $targetFile, 85);
            } else {
                imagejpeg($dstImg, $targetFile, 85);
            }

            imagedestroy($srcImg);
            imagedestroy($dstImg);

            return [
                'success' => true,
                'file_name' => $fileName,
                'file_path' => 'uploads/' . trim($subFolder, '/') . '/' . $fileName,
                'full_url' => BASE_URL . '/uploads/' . trim($subFolder, '/') . '/' . $fileName
            ];
        }
    }

    // GD başarısız olursa normal taşıma
    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        return [
            'success' => true,
            'file_name' => $fileName,
            'file_path' => 'uploads/' . trim($subFolder, '/') . '/' . $fileName,
            'full_url' => BASE_URL . '/uploads/' . trim($subFolder, '/') . '/' . $fileName
        ];
    }

    return ['success' => false, 'error' => 'Dosya kaydedilirken hata oluştu.'];
}
