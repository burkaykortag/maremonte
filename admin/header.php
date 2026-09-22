<?php
/**
 * Admin Paneli - Üst Şablon ve Yan Menü
 */

require_once __DIR__ . '/../database.php';
requireAdmin();

$currentPage = basename($_SERVER['PHP_SELF']);
$adminName = $_SESSION['admin_name'] ?? 'Yönetici';
$restaurantName = getSetting('restaurant_name', 'HOTEL MARE & MONTE BISTRO');

// Bekleyen çağrı ve sipariş sayıları
try {
    $stmtCalls = $pdo->query("SELECT COUNT(*) FROM waiter_calls WHERE status = 'pending'");
    $pendingCallsCount = $stmtCalls->fetchColumn();

    $stmtOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status IN ('pending', 'preparing')");
    $pendingOrdersCount = $stmtOrders->fetchColumn();
} catch (Exception $e) {
    $pendingCallsCount = 0;
    $pendingOrdersCount = 0;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Yönetim Paneli - <?php echo htmlspecialchars($restaurantName); ?></title>
    
    <!-- Google Fonts & FontAwesome -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Admin CSS (Cache-Busting) -->
    <link rel="stylesheet" href="../assets/css/admin.css?v=<?php echo time(); ?>">
</head>
<body>

    <!-- SİDEBAR MOBİL KARARTMA (BACKDROP) -->
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <!-- YAN MENÜ (SIDEBAR) -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo-icon">
                <img src="../assets/images/maremonte_logo.svg" alt="Mare & Monte">
            </div>
            <div class="sidebar-brand-text">
                <h2>Mare &amp; Monte</h2>
                <span>Yönetim Paneli</span>
            </div>
            <button type="button" class="sidebar-close-btn" id="sidebarCloseBtn" aria-label="Menüyü Kapat">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section-label">GENEL</div>
            <a href="index.php" class="nav-link <?php echo $currentPage === 'index.php' ? 'active' : ''; ?>">
                <i class="fas fa-chart-pie"></i>
                <span>Kontrol Paneli</span>
            </a>

            <div class="nav-section-label">MUTFAK & SİPARİŞ</div>
            <a href="pos.php" class="nav-link <?php echo $currentPage === 'pos.php' ? 'active' : ''; ?>" style="color: #34d399;">
                <i class="fas fa-cash-register"></i>
                <span>Garson Terminali (POS)</span>
            </a>
            <a href="kitchen.php" class="nav-link <?php echo $currentPage === 'kitchen.php' ? 'active' : ''; ?>" style="color: #60a5fa;">
                <i class="fas fa-kitchen-set"></i>
                <span>Mutfak & Bar Ekranı</span>
                <?php if ($pendingOrdersCount > 0): ?>
                    <span class="nav-badge" style="background:#3b82f6;"><?php echo $pendingOrdersCount; ?></span>
                <?php endif; ?>
            </a>
            <a href="waiter-calls.php" class="nav-link <?php echo $currentPage === 'waiter-calls.php' ? 'active' : ''; ?>">
                <i class="fas fa-bell"></i>
                <span>Garson Çağrıları</span>
                <?php if ($pendingCallsCount > 0): ?>
                    <span class="nav-badge"><?php echo $pendingCallsCount; ?></span>
                <?php endif; ?>
            </a>

            <div class="nav-section-label">MENÜ YÖNETİMİ</div>
            <a href="products.php" class="nav-link <?php echo $currentPage === 'products.php' ? 'active' : ''; ?>">
                <i class="fas fa-burger"></i>
                <span>Ürün Yönetimi</span>
            </a>
            <a href="ai_assistant.php" class="nav-link <?php echo $currentPage === 'ai_assistant.php' ? 'active' : ''; ?>" style="color:#fbbf24;">
                <i class="fas fa-wand-magic-sparkles"></i>
                <span>AI Şef &amp; Eşleştirme</span>
            </a>
            <a href="quick-price.php" class="nav-link <?php echo $currentPage === 'quick-price.php' ? 'active' : ''; ?>">
                <i class="fas fa-tags"></i>
                <span>Hızlı Fiyat Düzenle</span>
            </a>
            <a href="categories.php" class="nav-link <?php echo $currentPage === 'categories.php' ? 'active' : ''; ?>">
                <i class="fas fa-layer-group"></i>
                <span>Kategoriler</span>
            </a>

            <div class="nav-section-label">PAZARLAMA & MASA</div>
            <a href="events.php" class="nav-link <?php echo $currentPage === 'events.php' ? 'active' : ''; ?>" style="color:#c084fc;">
                <i class="fas fa-music"></i>
                <span>Canlı Müzik & Etkinlik</span>
            </a>
            <a href="stories.php" class="nav-link <?php echo $currentPage === 'stories.php' ? 'active' : ''; ?>">
                <i class="fas fa-circle-play"></i>
                <span>Hikayeler & Pop-up</span>
            </a>
            <a href="feedback.php" class="nav-link <?php echo $currentPage === 'feedback.php' ? 'active' : ''; ?>">
                <i class="fas fa-star"></i>
                <span>Müşteri Yorumları</span>
            </a>
            <a href="tables.php" class="nav-link <?php echo $currentPage === 'tables.php' ? 'active' : ''; ?>">
                <i class="fas fa-qrcode"></i>
                <span>Masa QR Kodları</span>
            </a>

            <div class="nav-section-label">RAPOR & AYARLAR</div>
            <a href="reports.php" class="nav-link <?php echo $currentPage === 'reports.php' ? 'active' : ''; ?>">
                <i class="fas fa-chart-line"></i>
                <span>Satış Raporları</span>
            </a>
            <a href="settings.php" class="nav-link <?php echo $currentPage === 'settings.php' ? 'active' : ''; ?>">
                <i class="fas fa-sliders"></i>
                <span>Modül & Tema Ayarları</span>
            </a>
            <a href="logout.php" class="nav-link" style="color: #ef4444;">
                <i class="fas fa-right-from-bracket"></i>
                <span>Çıkış Yap</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="admin-profile">
                <div class="admin-avatar">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div>
                    <div class="admin-name"><?php echo htmlspecialchars($adminName); ?></div>
                    <div class="admin-role">Yönetici</div>
                </div>
            </div>
            <a href="logout.php" title="Çıkış" style="color: var(--text-dim); text-decoration:none; font-size:1.1rem;">
                <i class="fas fa-power-off"></i>
            </a>
        </div>
    </aside>

    <!-- ANA İÇERİK SARMALAYICI -->
    <div class="admin-main">
        <!-- ÜST ÇUBUK (TOPBAR) -->
        <header class="admin-topbar">
            <div style="display:flex; align-items:center; gap:12px;">
                <button type="button" class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Menüyü Aç">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="topbar-brand" style="display:flex; align-items:center; gap:8px;">
                    <img src="../assets/images/maremonte_logo.svg" style="width:30px; height:30px; border-radius:50%;" alt="Logo">
                    <span style="font-size:0.92rem; font-weight:800; color:#fff;">Mare &amp; Monte</span>
                </div>
            </div>

            <div class="topbar-actions" style="display:flex; align-items:center; gap:8px;">
                <a href="pos.php" class="btn-preview" style="background:rgba(16,185,129,0.15); border-color:rgba(16,185,129,0.4); color:#34d399;" title="Garson Terminali">
                    <i class="fas fa-cash-register"></i>
                    <span>Garson POS</span>
                </a>
                <a href="kitchen.php" class="btn-preview" style="background:rgba(59,130,246,0.15); border-color:rgba(59,130,246,0.4); color:#60a5fa;" title="Mutfak & Bar Ekranı">
                    <i class="fas fa-kitchen-set"></i>
                    <span>Mutfak</span>
                </a>
                <a href="../index.php" target="_blank" class="btn-preview" title="Müşteri Görünümü">
                    <i class="fas fa-arrow-up-right-from-square"></i>
                    <span>Menüyü Gör</span>
                </a>
            </div>
        </header>

        <main class="admin-content">
