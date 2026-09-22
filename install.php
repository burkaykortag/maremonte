<?php
/**
 * Hotel Mare & Monte Bistro - Web Kurulum Sihirbazı (1-Click Installer)
 * Canlı Sunucu (cPanel / Plesk / DirectAdmin / VPS) Kurulum Asistanı
 */

session_start();

$configFile = __DIR__ . '/config.php';
$sqlFile = __DIR__ . '/database.sql';

$statusMsg = '';
$statusType = '';
$isInstalled = false;

// Sistem Gereksinimleri Kontrolü
$checks = [
    'php' => [
        'name' => 'PHP Sürümü (>= 7.4)',
        'pass' => version_compare(PHP_VERSION, '7.4.0', '>='),
        'current' => PHP_VERSION
    ],
    'pdo' => [
        'name' => 'PDO Genişletmesi',
        'pass' => extension_loaded('pdo'),
        'current' => extension_loaded('pdo') ? 'Yüklü' : 'Eksik'
    ],
    'pdo_mysql' => [
        'name' => 'PDO MySQL Sürücüsü',
        'pass' => extension_loaded('pdo_mysql'),
        'current' => extension_loaded('pdo_mysql') ? 'Yüklü' : 'Eksik'
    ],
    'gd' => [
        'name' => 'GD Kütüphanesi (Resim Yükleme)',
        'pass' => extension_loaded('gd'),
        'current' => extension_loaded('gd') ? 'Yüklü' : 'Eksik'
    ],
    'uploads' => [
        'name' => 'uploads/ Yazma İzni',
        'pass' => is_writable(__DIR__ . '/uploads') || (!file_exists(__DIR__ . '/uploads') && is_writable(__DIR__)),
        'current' => is_writable(__DIR__ . '/uploads') ? 'Yazılabilir' : 'İzin Verilmeli (777/755)'
    ],
    'config' => [
        'name' => 'config.php Yazma İzni',
        'pass' => is_writable($configFile) || is_writable(__DIR__),
        'current' => is_writable($configFile) ? 'Yazılabilir' : 'İzin Verilmeli (644/666)'
    ]
];

// POST: Kurulumu Başlat
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['run_install'])) {
    $dbDriver = trim($_POST['db_driver'] ?? 'mysql');
    $dbHost = trim($_POST['db_host'] ?? 'localhost');
    $dbName = trim($_POST['db_name'] ?? 'Maremonte');
    $dbUser = trim($_POST['db_user'] ?? 'Maremonte');
    $dbPass = trim($_POST['db_pass'] ?? 'Maremonte1122334455..');
    $adminUser = trim($_POST['admin_user'] ?? 'admin');
    $adminPass = trim($_POST['admin_pass'] ?? 'admin123');

    if ($dbDriver === 'sqlite') {
        try {
            $dataDir = __DIR__ . '/data';
            if (!is_dir($dataDir)) {
                mkdir($dataDir, 0777, true);
            }
            $sqlitePath = $dataDir . '/menu.sqlite';
            $pdo = new PDO('sqlite:' . $sqlitePath);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Admin Şifresini Güncelle
            try {
                $hash = password_hash($adminPass, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE admins SET username = ?, password_hash = ? WHERE id = 1");
                $stmt->execute([$adminUser, $hash]);
            } catch (Exception $eAd) {
                // Tablolar yoksa database.php include edilince oluşur
            }

            updateConfigFile('sqlite', $dbHost, $dbName, $dbUser, $dbPass);

            $statusType = 'success';
            $statusMsg = 'SQLite Veritabanı başarıyla seçildi ve tüm menü sistemi hazırlandı!';
            $isInstalled = true;
        } catch (Exception $e) {
            $statusType = 'danger';
            $statusMsg = 'SQLite Kurulum Hatası: ' . $e->getMessage();
        }
    } else {
        try {
            $pdo = null;
            // 1. Önce direkt veritabanına bağlanmayı dene
            try {
                $dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";
                $pdo = new PDO($dsn, $dbUser, $dbPass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]);
            } catch (PDOException $eDb) {
                // Veritabanı yoksa host seviyesinde bağlanıp oluşturmayı dene
                $dsnNoDb = "mysql:host={$dbHost};charset=utf8mb4";
                $pdo = new PDO($dsnNoDb, $dbUser, $dbPass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]);
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $pdo->exec("USE `{$dbName}`");
            }

            // 2. database.sql dosyasını içe aktar
            if (file_exists($sqlFile)) {
                $sqlContent = file_get_contents($sqlFile);
                $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
                $queries = array_filter(array_map('trim', explode(";\n", $sqlContent)));
                foreach ($queries as $query) {
                    if (!empty($query) && substr($query, 0, 2) !== '--') {
                        try {
                            $pdo->exec($query);
                        } catch (PDOException $qe) {}
                    }
                }
                $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
            }

            // 3. Admin Şifresini Güncelle
            if (!empty($adminUser) && !empty($adminPass)) {
                $hash = password_hash($adminPass, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE admins SET username = ?, password_hash = ? WHERE id = 1");
                $stmt->execute([$adminUser, $hash]);
                if ($stmt->rowCount() === 0) {
                    $stmtIns = $pdo->prepare("INSERT INTO admins (id, username, password_hash, name, role) VALUES (1, ?, ?, 'Mare & Monte Admin', 'superadmin')");
                    $stmtIns->execute([$adminUser, $hash]);
                }
            }

            // 4. config.php'yi güncelle
            updateConfigFile('mysql', $dbHost, $dbName, $dbUser, $dbPass);

            $statusType = 'success';
            $statusMsg = "MySQL Veritabanı (`{$dbName}`) başarıyla bağlandı, tablolar yüklendi!";
            $isInstalled = true;

        } catch (Exception $e) {
            $statusType = 'danger';
            $statusMsg = 'MySQL Bağlantı Hatası: ' . $e->getMessage() . '<br><br><strong>İpuçları:</strong><br>• Eğer hosting cPanel kullanıyorsanız, kullanıcı adı genellikle <code>cpaneladi_Maremonte</code> şeklindedir.<br>• Eğer kendi bilgisayarınızda (XAMPP) deniyorsanız, kullanıcı: <code>root</code> ve şifre: <em>(boş)</em> olmalıdır.<br>• Veya hiçbir ayarla uğraşmamak için sağdaki <strong>SQLite (Sıfır Ayar)</strong> seçeneğiyle 1 saniyede kurabilirsiniz.';
        }
    }
}

function updateConfigFile($driver, $host, $name, $user, $pass) {
    $configFile = __DIR__ . '/config.php';
    if (!is_writable($configFile) && file_exists($configFile)) {
        @chmod($configFile, 0666);
    }

    $template = "<?php\n";
    $template .= "/**\n * QR Menü & Yönetim Paneli - Yapılandırma Dosyası\n */\n\n";
    $template .= "if (session_status() === PHP_SESSION_NONE) {\n    session_start();\n}\n\n";
    $template .= "date_default_timezone_set('Europe/Istanbul');\n\n";
    $template .= "error_reporting(E_ALL & ~E_NOTICE);\nini_set('display_errors', 0);\n\n";
    $template .= "// Veritabanı Ayarları\n";
    $template .= "define('DB_DRIVER', '" . addslashes($driver) . "');\n\n";
    $template .= "// SQLite Veritabanı Dosya Yolu\n";
    $template .= "define('DB_SQLITE_PATH', __DIR__ . '/data/menu.sqlite');\n\n";
    $template .= "// MySQL Bağlantı Bilgileri\n";
    $template .= "define('DB_HOST', '" . addslashes($host) . "');\n";
    $template .= "define('DB_NAME', '" . addslashes($name) . "');\n";
    $template .= "define('DB_USER', '" . addslashes($user) . "');\n";
    $template .= "define('DB_PASS', '" . addslashes($pass) . "');\n";
    $template .= "define('DB_CHARSET', 'utf8mb4');\n\n";
    $template .= "// Temel Dizin & URL Tanımları\n";
    $template .= "define('BASE_PATH', __DIR__);\n";
    $template .= "define('UPLOAD_PATH', __DIR__ . '/uploads');\n\n";
    $template .= "\$protocol = (!empty(\$_SERVER['HTTPS']) && \$_SERVER['HTTPS'] !== 'off' || (isset(\$_SERVER['SERVER_PORT']) && \$_SERVER['SERVER_PORT'] == 443)) ? \"https://\" : \"http://\";\n";
    $template .= "\$host = \$_SERVER['HTTP_HOST'] ?? 'localhost';\n";
    $template .= "\$scriptDir = dirname(\$_SERVER['SCRIPT_NAME'] ?? '');\n";
    $template .= "\$baseUrl = rtrim(\$protocol . \$host . \$scriptDir, '/\\\\');\n";
    $template .= "if (basename(\$baseUrl) === 'admin') {\n    \$baseUrl = dirname(\$baseUrl);\n}\n";
    $template .= "define('BASE_URL', \$baseUrl);\n\n";
    $template .= "function csrfToken() {\n    if (empty(\$_SESSION['csrf_token'])) {\n        \$_SESSION['csrf_token'] = bin2hex(random_bytes(32));\n    }\n    return \$_SESSION['csrf_token'];\n}\n\n";
    $template .= "function verifyCsrfToken(\$token) {\n    return !empty(\$token) && !empty(\$_SESSION['csrf_token']) && hash_equals(\$_SESSION['csrf_token'], \$token);\n}\n\n";
    $template .= "function isAdminLoggedIn() {\n    return !empty(\$_SESSION['admin_logged_in']) && \$_SESSION['admin_logged_in'] === true;\n}\n\n";
    $template .= "function requireAdmin() {\n    if (!isAdminLoggedIn()) {\n        header('Location: ' . BASE_URL . '/admin/login.php');\n        exit;\n    }\n}\n\n";
    $template .= "function clean(\$data) {\n    if (is_array(\$data)) return array_map('clean', \$data);\n    return htmlspecialchars(trim((string)\$data), ENT_QUOTES, 'UTF-8');\n}\n\n";
    $template .= "function formatPrice(\$price, \$currency = '₺') {\n    return number_format((float)\$price, 2, ',', '.') . ' ' . \$currency;\n}\n\n";
    $template .= "function uploadImage(\$file, \$subFolder = 'products', \$maxWidth = 1200, \$maxHeight = 1200) {\n";
    $template .= "    if (empty(\$file) || \$file['error'] !== UPLOAD_ERR_OK) return ['success' => false, 'error' => 'Dosya yüklenemedi.'];\n";
    $template .= "    \$allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];\n";
    $template .= "    \$finfo = finfo_open(FILEINFO_MIME_TYPE);\n";
    $template .= "    \$mime = finfo_file(\$finfo, \$file['tmp_name']);\n";
    $template .= "    finfo_close(\$finfo);\n";
    $template .= "    if (!in_array(\$mime, \$allowed)) return ['success' => false, 'error' => 'Geçersiz resim formatı.'];\n";
    $template .= "    \$targetDir = UPLOAD_PATH . '/' . trim(\$subFolder, '/');\n";
    $template .= "    if (!is_dir(\$targetDir)) mkdir(\$targetDir, 0777, true);\n";
    $template .= "    \$ext = (\$mime === 'image/png') ? 'png' : ((\$mime === 'image/webp') ? 'webp' : ((\$mime === 'image/gif') ? 'gif' : 'jpg'));\n";
    $template .= "    \$fileName = uniqid('img_', true) . '.' . \$ext;\n";
    $template .= "    \$target = \$targetDir . '/' . \$fileName;\n";
    $template .= "    if (move_uploaded_file(\$file['tmp_name'], \$target)) {\n";
    $template .= "        return ['success' => true, 'file_name' => \$fileName, 'file_path' => 'uploads/' . trim(\$subFolder, '/') . '/' . \$fileName, 'full_url' => BASE_URL . '/uploads/' . trim(\$subFolder, '/') . '/' . \$fileName];\n";
    $template .= "    }\n";
    $template .= "    return ['success' => false, 'error' => 'Resim kaydedilemedi.'];\n";
    $template .= "}\n";

    @file_put_contents($configFile, $template);
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Mare &amp; Monte - Canlı Sunucu Kurulum Sihirbazı</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #C5A059;
            --primary-hover: #B38E46;
            --bg-dark: #090D16;
            --bg-card: #161F30;
            --bg-input: #1E293B;
            --text-main: #F8FAFC;
            --text-muted: #94A3B8;
            --text-dim: #64748B;
            --border: rgba(255, 255, 255, 0.08);
            --success: #10B981;
            --danger: #EF4444;
            --radius-sm: 10px;
            --radius-md: 16px;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--bg-dark);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 16px;
        }

        .installer-container {
            max-width: 680px;
            width: 100%;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            box-shadow: 0 12px 40px rgba(0,0,0,0.6);
            overflow: hidden;
        }

        .installer-header {
            background: linear-gradient(135deg, rgba(197, 160, 89, 0.15) 0%, rgba(22, 31, 48, 0.95) 100%);
            border-bottom: 1px solid var(--border);
            padding: 28px 24px;
            text-align: center;
        }

        .brand-logo {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: var(--primary);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            margin-bottom: 12px;
            box-shadow: 0 4px 15px rgba(197, 160, 89, 0.4);
        }

        .installer-header h1 {
            font-size: 1.35rem;
            font-weight: 800;
            color: #fff;
            margin-bottom: 4px;
        }

        .installer-header p {
            font-size: 0.82rem;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 700;
        }

        .installer-body {
            padding: 24px;
        }

        .alert {
            padding: 14px 18px;
            border-radius: var(--radius-sm);
            font-size: 0.88rem;
            margin-bottom: 22px;
            line-height: 1.4;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid var(--success);
            color: #6ee7b7;
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid var(--danger);
            color: #fca5a5;
        }

        .section-title {
            font-size: 0.92rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .checks-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 24px;
        }

        @media (max-width: 540px) {
            .checks-grid { grid-template-columns: 1fr; }
        }

        .check-item {
            background: var(--bg-input);
            border: 1px solid var(--border);
            padding: 10px 14px;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.80rem;
        }

        .badge-pass {
            background: rgba(16, 185, 129, 0.2);
            color: var(--success);
            padding: 2px 8px;
            border-radius: 999px;
            font-weight: 700;
            font-size: 0.72rem;
        }

        .badge-fail {
            background: rgba(239, 68, 68, 0.2);
            color: var(--danger);
            padding: 2px 8px;
            border-radius: 999px;
            font-weight: 700;
            font-size: 0.72rem;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-label {
            display: block;
            font-size: 0.80rem;
            font-weight: 700;
            color: var(--text-muted);
            margin-bottom: 6px;
        }

        .form-control {
            width: 100%;
            height: 44px;
            padding: 0 14px;
            background: var(--bg-input);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            color: #fff;
            font-size: 0.90rem;
            font-family: inherit;
            transition: border-color 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
        }

        .form-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        @media (max-width: 540px) {
            .form-grid-2 { grid-template-columns: 1fr; }
        }

        .driver-select {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 16px;
        }

        .driver-card {
            border: 2px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 14px;
            cursor: pointer;
            text-align: center;
            transition: all 0.2s;
            background: var(--bg-input);
        }

        .driver-card.active, .driver-card:hover {
            border-color: var(--primary);
            background: rgba(197, 160, 89, 0.1);
        }

        .driver-card i {
            font-size: 1.4rem;
            color: var(--primary);
            margin-bottom: 6px;
            display: block;
        }

        .driver-card strong {
            font-size: 0.88rem;
            display: block;
            color: #fff;
        }

        .driver-card small {
            font-size: 0.72rem;
            color: var(--text-dim);
        }

        .btn-install {
            width: 100%;
            height: 48px;
            background: var(--primary);
            color: #000;
            border: none;
            border-radius: var(--radius-sm);
            font-size: 0.95rem;
            font-weight: 800;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background 0.2s;
            box-shadow: 0 4px 15px rgba(197, 160, 89, 0.3);
            margin-top: 10px;
        }

        .btn-install:hover {
            background: var(--primary-hover);
            color: #fff;
        }

        .success-links-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-top: 20px;
        }

        @media (max-width: 540px) {
            .success-links-grid { grid-template-columns: 1fr; }
        }

        .success-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px;
            background: var(--bg-input);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            color: #fff;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.88rem;
            transition: all 0.2s;
        }

        .success-btn:hover {
            border-color: var(--primary);
            background: rgba(197, 160, 89, 0.15);
            color: var(--primary);
        }

        .success-btn i {
            font-size: 1.2rem;
            color: var(--primary);
        }
    </style>
</head>
<body>

<div class="installer-container">
    <div class="installer-header">
        <div class="brand-logo">
            <i class="fas fa-utensils"></i>
        </div>
        <h1>Hotel Mare &amp; Monte Bistro</h1>
        <p>1-Click Canlı Sunucu Kurulum Sihirbazı</p>
    </div>

    <div class="installer-body">
        
        <?php if (!empty($statusMsg)): ?>
            <div class="alert alert-<?php echo $statusType; ?>">
                <i class="fas fa-<?php echo $statusType === 'success' ? 'circle-check' : 'circle-exclamation'; ?>" style="font-size: 1.3rem; margin-top: 2px;"></i>
                <div><?php echo $statusMsg; ?></div>
            </div>
        <?php endif; ?>

        <?php if ($isInstalled): ?>
            <div style="background: rgba(197, 160, 89, 0.1); border: 1px solid var(--primary); border-radius: var(--radius-sm); padding: 18px; margin-bottom: 20px; text-align: center;">
                <h3 style="color: #fff; margin-bottom: 6px; font-size: 1.1rem;"><i class="fas fa-crown" style="color:var(--primary);"></i> Kurulum Tamamlandı!</h3>
                <p style="font-size: 0.84rem; color: var(--text-muted); margin-bottom: 8px;">
                    Yönetici Kullanıcı Adı: <strong style="color:#fff;"><?php echo htmlspecialchars($_POST['admin_user'] ?? 'admin'); ?></strong> | Şifre: <strong style="color:#fff;">(Girdiğiniz Şifre)</strong>
                </p>
                <div style="font-size: 0.75rem; color: #fbbf24;">
                    <i class="fas fa-shield-halved"></i> Güvenliğiniz için kurulum tamamlandıktan sonra <code>install.php</code> dosyasını sunucudan silebilirsiniz.
                </div>
            </div>

            <div class="section-title"><i class="fas fa-compass" style="color:var(--primary);"></i> Hızlı Erişim Linkleri</div>
            <div class="success-links-grid">
                <a href="index.php" class="success-btn" target="_blank">
                    <i class="fas fa-mobile-screen"></i>
                    <div>
                        <div>Müşteri Menüsü</div>
                        <small style="color:var(--text-dim); font-size:0.72rem;">QR Menü Ana Sayfası</small>
                    </div>
                </a>
                <a href="admin/index.php" class="success-btn" target="_blank">
                    <i class="fas fa-gauge-high"></i>
                    <div>
                        <div>Yönetim Paneli</div>
                        <small style="color:var(--text-dim); font-size:0.72rem;">Ürün &amp; Kategori Yönetimi</small>
                    </div>
                </a>
                <a href="admin/pos.php" class="success-btn" target="_blank">
                    <i class="fas fa-cash-register"></i>
                    <div>
                        <div>Garson Terminali (POS)</div>
                        <small style="color:var(--text-dim); font-size:0.72rem;">Masa Sipariş Alma</small>
                    </div>
                </a>
                <a href="admin/kitchen.php" class="success-btn" target="_blank">
                    <i class="fas fa-kitchen-set"></i>
                    <div>
                        <div>Mutfak &amp; Bar Ekranı</div>
                        <small style="color:var(--text-dim); font-size:0.72rem;">Canlı Sipariş Ekranı</small>
                    </div>
                </a>
            </div>

        <?php else: ?>

            <!-- SİSTEM GEREKSİNİMLERİ -->
            <div class="section-title"><i class="fas fa-server" style="color:var(--primary);"></i> 1. Sistem Gereksinimleri Kontrolü</div>
            <div class="checks-grid">
                <?php foreach ($checks as $c): ?>
                    <div class="check-item">
                        <span><?php echo $c['name']; ?></span>
                        <span class="<?php echo $c['pass'] ? 'badge-pass' : 'badge-fail'; ?>">
                            <?php echo $c['pass'] ? '<i class="fas fa-check"></i> ' . $c['current'] : '<i class="fas fa-times"></i> ' . $c['current']; ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- KURULUM FORMU -->
            <form method="POST" action="install.php">
                <input type="hidden" name="run_install" value="1">

                <div class="section-title"><i class="fas fa-database" style="color:var(--primary);"></i> 2. Veritabanı Türü Seçimi</div>
                
                <div class="driver-select">
                    <label class="driver-card active" id="cardMysql" onclick="selectDriver('mysql')">
                        <input type="radio" name="db_driver" value="mysql" id="radioMysql" checked style="display:none;">
                        <i class="fas fa-database"></i>
                        <strong>MySQL / MariaDB</strong>
                        <small>cPanel / Plesk (Önerilen)</small>
                    </label>
                    <label class="driver-card" id="cardSqlite" onclick="selectDriver('sqlite')">
                        <input type="radio" name="db_driver" value="sqlite" id="radioSqlite" style="display:none;">
                        <i class="fas fa-file-code"></i>
                        <strong>SQLite (Sıfır Ayar)</strong>
                        <small>Veritabanı Açmadan Çalışır</small>
                    </label>
                </div>

                <!-- MYSQL AYARLARI -->
                <div id="mysqlFields">
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Veritabanı Sunucusu (Host)</label>
                            <input type="text" name="db_host" class="form-control" value="localhost" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Veritabanı Adı (DB Name)</label>
                            <input type="text" name="db_name" class="form-control" value="Maremonte" required>
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Veritabanı Kullanıcısı (DB User)</label>
                            <input type="text" name="db_user" class="form-control" value="Maremonte" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Veritabanı Şifresi (DB Password)</label>
                            <input type="text" name="db_pass" class="form-control" value="Maremonte1122334455..">
                        </div>
                    </div>
                </div>

                <div class="section-title" style="margin-top: 10px;"><i class="fas fa-user-shield" style="color:var(--primary);"></i> 3. Yönetici (Admin) Hesabı</div>
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Admin Kullanıcı Adı</label>
                        <input type="text" name="admin_user" class="form-control" value="admin" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Admin Şifresi</label>
                        <input type="text" name="admin_pass" class="form-control" value="admin123" required>
                    </div>
                </div>

                <button type="submit" class="btn-install">
                    <i class="fas fa-bolt"></i> Kurulumu Başlat &amp; Veritabanını Yükle
                </button>
            </form>
        <?php endif; ?>

    </div>
</div>

<script>
function selectDriver(driver) {
    const cardMysql = document.getElementById('cardMysql');
    const cardSqlite = document.getElementById('cardSqlite');
    const radioMysql = document.getElementById('radioMysql');
    const radioSqlite = document.getElementById('radioSqlite');
    const mysqlFields = document.getElementById('mysqlFields');

    if (driver === 'mysql') {
        cardMysql.classList.add('active');
        cardSqlite.classList.remove('active');
        radioMysql.checked = true;
        mysqlFields.style.display = 'block';
    } else {
        cardSqlite.classList.add('active');
        cardMysql.classList.remove('active');
        radioSqlite.checked = true;
        mysqlFields.style.display = 'none';
    }
}
</script>

</body>
</html>