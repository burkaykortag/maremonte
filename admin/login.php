<?php
/**
 * Admin Giriş Ekranı
 */

require_once __DIR__ . '/../database.php';

$error = '';

if (isAdminLoggedIn()) {
    header('Location: index.php');
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $username = clean($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Lütfen kullanıcı adı ve şifrenizi girin.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ? LIMIT 1");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password_hash'])) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_name'] = $admin['name'];
                $_SESSION['admin_role'] = $admin['role'];

                header('Location: index.php');
                exit;
            } else {
                $error = 'Hatalı kullanıcı adı veya şifre!';
            }
        } catch (Exception $e) {
            $error = 'Giriş yapılırken bir hata oluştu: ' . $e->getMessage();
        }
    }
}

$restaurantName = getSetting('restaurant_name', 'Gusto QR Menü');
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yönetici Girişi - <?php echo htmlspecialchars($restaurantName); ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">

    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: radial-gradient(circle at top right, #1e293b 0%, #0c111d 100%);
            padding: 20px;
        }
        .login-box {
            width: 100%;
            max-width: 420px;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 36px 32px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
        }
        .login-header {
            text-align: center;
            margin-bottom: 28px;
        }
        .login-icon {
            width: 56px;
            height: 56px;
            border-radius: var(--radius-md);
            background: rgba(var(--primary-rgb), 0.15);
            color: var(--primary);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin-bottom: 14px;
            border: 1px solid rgba(var(--primary-rgb), 0.3);
        }
    </style>
</head>
<body>

    <div class="login-box">
        <div class="login-header">
            <div class="login-icon">
                <i class="fas fa-lock"></i>
            </div>
            <h2 style="font-size: 1.4rem; font-weight: 800; color: #fff;"><?php echo htmlspecialchars($restaurantName); ?></h2>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 4px;">Yönetim Paneline Giriş Yapın</p>
        </div>

        <?php if (!empty($error)): ?>
            <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid var(--danger); color: #fca5a5; padding: 12px 16px; border-radius: var(--radius-sm); font-size: 0.85rem; margin-bottom: 20px;">
                <i class="fas fa-circle-exclamation" style="margin-right: 6px;"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label class="form-label">Kullanıcı Adı</label>
                <div style="position: relative;">
                    <input type="text" name="username" class="form-control" placeholder="Kullanıcı adınızı girin" required autofocus style="padding-left: 40px;">
                    <i class="fas fa-user" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-dim);"></i>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 24px;">
                <label class="form-label">Şifre</label>
                <div style="position: relative;">
                    <input type="password" name="password" class="form-control" placeholder="Şifrenizi girin" required style="padding-left: 40px;">
                    <i class="fas fa-key" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-dim);"></i>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; height: 46px; font-size: 0.95rem; font-weight: 700;">
                <i class="fas fa-right-to-bracket"></i> Giriş Yap
            </button>
        </form>

        <div style="margin-top: 24px; padding-top: 18px; border-top: 1px solid var(--border); text-align: center;">
            <div style="font-size: 0.78rem; color: var(--text-dim);">
                Varsayılan Giriş Bilgileri:<br>
                <strong style="color: var(--primary);">admin</strong> / <strong style="color: var(--primary);">admin123</strong>
            </div>
        </div>
    </div>

</body>
</html>
