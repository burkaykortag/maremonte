<?php
/**
 * Modern Lüks QR Menü - Müşteri Arayüzü (Tüm Gelişmiş Modüller Entegre)
 */

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/languages.php';

// Aktif Dil Seçimi (tr, en, ar, ru, de)
$currentLang = clean($_GET['lang'] ?? $_SESSION['current_lang'] ?? 'tr');
if (!in_array($currentLang, ['tr', 'en', 'ar', 'ru', 'de'])) {
    $currentLang = 'tr';
}
$_SESSION['current_lang'] = $currentLang;
$isRtl = ($currentLang === 'ar');

// Masa Numarası Tespiti (?table=5 veya ?t=token)
$tableNumber = clean($_GET['table'] ?? $_GET['t'] ?? $_SESSION['current_table'] ?? '');
if (!empty($tableNumber)) {
    $stmtT = $pdo->prepare("SELECT table_number, table_name FROM tables WHERE token = ? OR table_number = ? LIMIT 1");
    $stmtT->execute([$tableNumber, $tableNumber]);
    $tRow = $stmtT->fetch();
    if ($tRow) {
        $tableNumber = $tRow['table_number'];
        $tableName = $tRow['table_name'];
    } else {
        $tableName = __t('table', $currentLang) . ' ' . $tableNumber;
    }
    $_SESSION['current_table'] = $tableNumber;
} else {
    $tableName = '';
}

// Genel Ayarlar & Modül Durumları
$restaurantName = getSetting('restaurant_name', 'Gusto Gourmet & Lounge');
$restaurantSlogan = getSetting('restaurant_slogan', 'Eşsiz Lezzetler & Keyifli Anlar');
$currency = getSetting('currency', '₺');
$themeColor = getSetting('theme_color', '#d97706');
$themeMode = getSetting('theme_mode', 'dark');

// Dark & Light Logo
$logoDarkUrl = getSetting('logo_dark_url', '');
$logoLightUrl = getSetting('logo_light_url', '');
$legacyLogo = getSetting('logo_url', '');

if ($themeMode === 'light') {
    $activeLogo = !empty($logoLightUrl) ? $logoLightUrl : (!empty($logoDarkUrl) ? $logoDarkUrl : $legacyLogo);
} else {
    $activeLogo = !empty($logoDarkUrl) ? $logoDarkUrl : (!empty($logoLightUrl) ? $logoLightUrl : $legacyLogo);
}

$bannerUrl = getSetting('banner_url', 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=1200&q=80');
$wifiName = getSetting('wifi_name', 'Gusto_Guest_5G');
$wifiPass = getSetting('wifi_pass', 'Gusto2026!');
$googleMapsUrl = getSetting('google_maps_url', 'https://maps.google.com/?q=Gusto+Gourmet');

// Modül Aç/Kapa
$enableOrder = getSetting('enable_order', '1') === '1';
$enableMultiLang = getSetting('enable_multi_lang', '1') === '1';
$enableKitchen = getSetting('enable_kitchen', '1') === '1';
$enableStories = getSetting('enable_stories', '1') === '1';
$enablePopup = getSetting('enable_popup', '1') === '1';
$enableFeedback = getSetting('enable_feedback', '1') === '1';
$enableAllergensFilter = getSetting('enable_allergens_filter', '1') === '1';
$enableWaiterCall = getSetting('enable_waiter_call', '1') === '1';

// Pop-up Kampanya
$popupTitle = getSetting('popup_title', '🎉 Haftanın Özel Spesiyali!');
$popupDesc = getSetting('popup_desc', 'Gusto Smokehouse Burger yanında çıtır patates ile şimdi %15 indirimli!');
$popupImage = getSetting('popup_image', 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=800&q=80');
$popupBtnText = getSetting('popup_btn_text', 'Hemen İncele');
$popupBtnLink = getSetting('popup_btn_link', '#cat-2');

// Hikayeleri Çek
$stories = [];
if ($enableStories) {
    $stories = $pdo->query("SELECT * FROM stories WHERE is_active = 1 ORDER BY sort_order ASC, id ASC")->fetchAll();
}

// Kategorileri ve Ürünleri Çek
$stmtCats = $pdo->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order ASC, id ASC");
$categories = $stmtCats->fetchAll();

$stmtProds = $pdo->query("SELECT * FROM products WHERE is_available = 1 ORDER BY sort_order ASC, id ASC");
$allProducts = $stmtProds->fetchAll();

// Ürün Opsiyonlarını Çek
$stmtOptions = $pdo->query("SELECT * FROM product_options ORDER BY id ASC");
$allOptions = $stmtOptions->fetchAll();
$optionsByProduct = [];
foreach ($allOptions as $opt) {
    $optionsByProduct[$opt['product_id']][] = $opt;
}

// Menü Hiyerarşisini Oluştur
$menuData = [];
foreach ($categories as $cat) {
    $catProducts = array_filter($allProducts, function($p) use ($cat) {
        return (int)$p['category_id'] === (int)$cat['id'];
    });
    if (!empty($catProducts)) {
        $cat['products'] = array_values($catProducts);
        $menuData[] = $cat;
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($currentLang); ?>" data-theme="<?php echo htmlspecialchars($themeMode); ?>" <?php echo $isRtl ? 'dir="rtl"' : ''; ?>>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo htmlspecialchars($restaurantName); ?> - <?php echo __t('menu', $currentLang); ?></title>
    
    <!-- Google Fonts & FontAwesome -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Özel Menü CSS -->
    <link rel="stylesheet" href="assets/css/menu.css">

    <style>
        :root {
            --primary: <?php echo htmlspecialchars($themeColor); ?>;
        }
    </style>
</head>
<body>

    <!-- ÜST HEADER -->
    <header class="app-header">
        <a href="index.php?lang=<?php echo $currentLang; ?>" class="header-brand">
            <?php if (!empty($activeLogo)): ?>
                <img src="<?php echo htmlspecialchars($activeLogo); ?>" alt="<?php echo htmlspecialchars($restaurantName); ?>" class="brand-logo">
            <?php else: ?>
                <div class="brand-logo" style="display:flex;align-items:center;justify-content:center;background:var(--primary);color:#fff;font-weight:800;font-size:1.2rem;border-radius:10px;">
                    <?php echo mb_substr($restaurantName, 0, 1); ?>
                </div>
            <?php endif; ?>
            <div class="brand-info">
                <h1><?php echo htmlspecialchars($restaurantName); ?></h1>
                <p><?php echo htmlspecialchars($restaurantSlogan); ?></p>
            </div>
        </a>

        <div class="header-actions">
            <?php if (!empty($tableNumber)): ?>
                <div class="table-pill" title="Masa Numarası">
                    <i class="fas fa-utensils"></i>
                    <span><?php echo htmlspecialchars($tableName); ?></span>
                </div>
            <?php endif; ?>

            <!-- Çoklu Dil Seçici -->
            <?php if ($enableMultiLang): ?>
                <div style="position: relative;">
                    <button type="button" class="lang-selector-btn" onclick="document.getElementById('langDropdown').classList.toggle('active');">
                        <span><?php echo $translations[$currentLang]['flag']; ?></span>
                        <span style="text-transform:uppercase;"><?php echo $currentLang; ?></span>
                        <i class="fas fa-chevron-down" style="font-size:0.65rem;"></i>
                    </button>
                    <div id="langDropdown" style="display:none; position:absolute; top:42px; right:0; background:var(--bg-card); border:1px solid var(--border-color); border-radius:var(--radius-md); box-shadow:var(--shadow-md); z-index:300; min-width:140px; overflow:hidden;">
                        <?php foreach ($translations as $code => $tInfo): ?>
                            <a href="javascript:void(0)" onclick="changeLanguage('<?php echo $code; ?>')" style="display:flex; align-items:center; gap:10px; padding:10px 14px; text-decoration:none; color:var(--text-main); font-size:0.85rem; font-weight:600; border-bottom:1px solid var(--border-color);">
                                <span><?php echo $tInfo['flag']; ?></span>
                                <span><?php echo $tInfo['name']; ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <script>
                    document.addEventListener('click', (e) => {
                        const dd = document.getElementById('langDropdown');
                        if (dd && !e.target.closest('.lang-selector-btn') && !e.target.closest('#langDropdown')) {
                            dd.style.display = 'none';
                        }
                    });
                    document.querySelector('.lang-selector-btn')?.addEventListener('click', (e) => {
                        e.stopPropagation();
                        const dd = document.getElementById('langDropdown');
                        dd.style.display = dd.style.display === 'none' ? 'block' : 'none';
                    });
                </script>
            <?php endif; ?>

            <button type="button" class="icon-btn" id="openWifiBtn" title="<?php echo __t('wifi', $currentLang); ?>">
                <i class="fas fa-wifi"></i>
            </button>
        </div>
    </header>

    <!-- INSTAGRAM TARZI HİKAYELER (STORIES) -->
    <?php if ($enableStories && !empty($stories)): ?>
        <div class="stories-container">
            <?php foreach ($stories as $story): ?>
                <div class="story-item" data-title="<?php echo htmlspecialchars($story['title']); ?>" data-image="<?php echo htmlspecialchars($story['image']); ?>" data-link="<?php echo htmlspecialchars($story['link']); ?>">
                    <div class="story-ring">
                        <img src="<?php echo htmlspecialchars($story['image']); ?>" alt="" class="story-avatar">
                    </div>
                    <span class="story-title"><?php echo htmlspecialchars($story['title']); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- HERO SECTION & BANNER -->
    <section class="hero-section">
        <div class="hero-banner">
            <img src="<?php echo htmlspecialchars($bannerUrl); ?>" alt="<?php echo htmlspecialchars($restaurantName); ?>">
            <div class="hero-overlay">
                <h2 class="hero-title"><?php echo htmlspecialchars($restaurantName); ?></h2>
                <p class="hero-subtitle"><?php echo htmlspecialchars($restaurantSlogan); ?></p>
            </div>
        </div>

        <!-- Canlı Arama Çubuğu -->
        <div class="search-container">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="menuSearch" class="search-input" placeholder="<?php echo __t('search_placeholder', $currentLang); ?>" autocomplete="off">
            <button type="button" id="searchClear" class="search-clear">
                <i class="fas fa-times-circle"></i>
            </button>
        </div>

        <!-- Hızlı Filtre Rozetleri -->
        <div class="quick-filters">
            <button type="button" class="filter-badge active" data-filter="all">
                <i class="fas fa-border-all"></i> <?php echo __t('all', $currentLang); ?>
            </button>
            <button type="button" class="filter-badge" data-filter="chef">
                <i class="fas fa-hat-chef"></i> <?php echo __t('chef_choice', $currentLang); ?>
            </button>
            <button type="button" class="filter-badge" data-filter="popular">
                <i class="fas fa-fire"></i> <?php echo __t('popular', $currentLang); ?>
            </button>
            <button type="button" class="filter-badge" data-filter="discount">
                <i class="fas fa-tag"></i> <?php echo __t('discounted', $currentLang); ?>
            </button>
            <button type="button" class="filter-badge" data-filter="vegan">
                <i class="fas fa-leaf"></i> <?php echo __t('vegan', $currentLang); ?>
            </button>
            <?php if ($enableAllergensFilter): ?>
                <button type="button" class="filter-badge" data-filter="gluten_free">
                    <i class="fas fa-wheat-awn-circle-exclamation"></i> <?php echo __t('gluten_free', $currentLang); ?>
                </button>
                <button type="button" class="filter-badge" data-filter="low_cal">
                    <i class="fas fa-bolt"></i> <?php echo __t('low_cal', $currentLang); ?>
                </button>
            <?php endif; ?>
        </div>
    </section>

    <!-- KATEGORİ CAROUSEL (STICKY) -->
    <nav class="categories-bar">
        <div class="categories-carousel">
            <?php foreach ($menuData as $index => $cat): 
                $catName = getLocalizedText($cat, 'name', $currentLang);
            ?>
                <a href="#cat-<?php echo $cat['id']; ?>" class="category-pill <?php echo $index === 0 ? 'active' : ''; ?>">
                    <?php if (!empty($cat['image'])): ?>
                        <img src="<?php echo htmlspecialchars($cat['image']); ?>" alt="<?php echo htmlspecialchars($catName); ?>">
                    <?php else: ?>
                        <i class="fas fa-<?php echo htmlspecialchars($cat['icon'] ?: 'utensils'); ?>"></i>
                    <?php endif; ?>
                    <span><?php echo htmlspecialchars($catName); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </nav>

    <!-- MENÜ İÇERİK ALANI -->
    <main class="menu-container">
        
        <div id="noResultsMessage" style="display: none; text-align: center; padding: 40px 20px;">
            <i class="fas fa-search" style="font-size: 3rem; color: var(--text-dim); margin-bottom: 12px; display: block;"></i>
            <h3 style="font-weight: 700; color: var(--text-main); margin-bottom: 6px;"><?php echo __t('no_results', $currentLang); ?></h3>
            <p style="color: var(--text-muted); font-size: 0.88rem;"><?php echo __t('no_results_desc', $currentLang); ?></p>
        </div>

        <?php foreach ($menuData as $cat): 
            $catName = getLocalizedText($cat, 'name', $currentLang);
        ?>
            <section class="category-section" id="cat-<?php echo $cat['id']; ?>">
                <div class="section-header">
                    <h3 class="section-title">
                        <i class="fas fa-<?php echo htmlspecialchars($cat['icon'] ?: 'utensils'); ?>" style="color:var(--primary);"></i>
                        <?php echo htmlspecialchars($catName); ?>
                    </h3>
                    <span class="section-badge"><?php echo count($cat['products']); ?> <?php echo __t('items', $currentLang); ?></span>
                </div>

                <div class="products-grid">
                    <?php foreach ($cat['products'] as $prod): 
                        $prodName = getLocalizedText($prod, 'name', $currentLang);
                        $prodDesc = getLocalizedText($prod, 'desc', $currentLang);
                        $hasDiscount = !empty($prod['old_price']) && (float)$prod['old_price'] > (float)$prod['price'];
                        $badge = $prod['badge'];
                        $badgeClass = '';
                        if (stripos($badge, 'şef') !== false || stripos($badge, 'chef') !== false) $badgeClass = 'badge-chef';
                        elseif (stripos($badge, 'popüler') !== false || stripos($badge, 'satan') !== false || stripos($badge, 'popular') !== false) $badgeClass = 'badge-popular';
                        elseif (stripos($badge, 'yeni') !== false || stripos($badge, 'new') !== false) $badgeClass = 'badge-new';
                        elseif (stripos($badge, 'acı') !== false || stripos($badge, 'spicy') !== false) $badgeClass = 'badge-spicy';
                        
                        $prodOptions = $optionsByProduct[$prod['id']] ?? [];
                    ?>
                        <div class="product-card <?php echo !$prod['is_available'] ? 'unavailable' : ''; ?>"
                             data-id="<?php echo $prod['id']; ?>"
                             data-name="<?php echo htmlspecialchars($prodName); ?>"
                             data-desc="<?php echo htmlspecialchars($prodDesc); ?>"
                             data-price="<?php echo $prod['price']; ?>"
                             data-price-formatted="<?php echo formatPrice($prod['price'], $currency); ?>"
                             data-old-price-formatted="<?php echo $hasDiscount ? formatPrice($prod['old_price'], $currency) : ''; ?>"
                             data-image="<?php echo htmlspecialchars($prod['image']); ?>"
                             data-badge="<?php echo htmlspecialchars($badge); ?>"
                             data-calories="<?php echo (int)$prod['calories']; ?>"
                             data-prep-time="<?php echo (int)$prod['prep_time']; ?>"
                             data-allergens="<?php echo htmlspecialchars($prod['allergens']); ?>"
                             data-featured="<?php echo $prod['is_featured']; ?>"
                             data-discount="<?php echo $hasDiscount ? '1' : '0'; ?>"
                             data-available="<?php echo $prod['is_available']; ?>"
                             data-options='<?php echo json_encode($prodOptions, JSON_UNESCAPED_UNICODE); ?>'>

                            <div class="product-image-wrap">
                                <?php if (!empty($prod['image'])): ?>
                                    <img src="<?php echo htmlspecialchars($prod['image']); ?>" alt="<?php echo htmlspecialchars($prodName); ?>" class="product-img" loading="lazy">
                                <?php else: ?>
                                    <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:var(--text-dim);font-size:1.8rem;background:var(--bg-surface);">
                                        <i class="fas fa-utensils"></i>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($badge)): ?>
                                    <span class="product-badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($badge); ?></span>
                                <?php endif; ?>

                                <?php if (!$prod['is_available']): ?>
                                    <div class="sold-out-tag"><?php echo __t('sold_out', $currentLang); ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="product-content">
                                <div>
                                    <h4 class="product-name"><?php echo htmlspecialchars($prodName); ?></h4>
                                    <p class="product-desc"><?php echo htmlspecialchars($prodDesc); ?></p>
                                </div>

                                <div class="product-footer">
                                    <div class="product-price-box">
                                        <span class="product-price"><?php echo formatPrice($prod['price'], $currency); ?></span>
                                        <?php if ($hasDiscount): ?>
                                            <span class="product-old-price"><?php echo formatPrice($prod['old_price'], $currency); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <span class="product-action-btn" title="Detay">
                                        <i class="fas fa-arrow-right"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>

        <!-- MÜŞTERİ DEĞERLENDİRME & GOOGLE YORUM MODÜLÜ -->
        <?php if ($enableFeedback): ?>
            <section class="feedback-section">
                <h3 style="font-size: 1.2rem; font-weight: 800; color: var(--text-main); margin-bottom: 4px;">
                    <i class="fas fa-star" style="color:var(--primary);"></i> <?php echo __t('rate_us', $currentLang); ?>
                </h3>
                <p style="font-size: 0.85rem; color: var(--text-muted);"><?php echo __t('rate_us_desc', $currentLang); ?></p>

                <div class="star-rating-box">
                    <button type="button" class="star-btn active" data-star="1"><i class="fas fa-star"></i></button>
                    <button type="button" class="star-btn active" data-star="2"><i class="fas fa-star"></i></button>
                    <button type="button" class="star-btn active" data-star="3"><i class="fas fa-star"></i></button>
                    <button type="button" class="star-btn active" data-star="4"><i class="fas fa-star"></i></button>
                    <button type="button" class="star-btn active" data-star="5"><i class="fas fa-star"></i></button>
                </div>

                <div style="max-width: 440px; margin: 0 auto; display: flex; flex-direction: column; gap: 10px;">
                    <input type="hidden" id="feedbackTableNumber" value="<?php echo htmlspecialchars($tableNumber); ?>">
                    <input type="text" id="feedbackName" class="search-input" placeholder="<?php echo __t('your_name', $currentLang); ?>" style="height: 42px; border-radius: var(--radius-sm);">
                    <textarea id="feedbackComment" rows="2" class="search-input" placeholder="<?php echo __t('your_comment', $currentLang); ?>" style="height: auto; padding: 10px 14px; border-radius: var(--radius-sm);"></textarea>
                    
                    <button type="button" id="sendFeedbackBtn" class="bottom-cta-btn" style="width: 100%; justify-content: center; padding: 12px;">
                        <?php echo __t('send_feedback', $currentLang); ?>
                    </button>
                </div>

                <div id="googleReviewCtaBox" style="display:none; margin-top: 18px; padding-top: 16px; border-top: 1px solid var(--border-color);">
                    <p style="font-size: 0.85rem; color: #34d399; font-weight: 700; margin-bottom: 10px;">
                        🎉 Bizi beğendiğinize çok sevindik!
                    </p>
                    <a href="<?php echo htmlspecialchars($googleMapsUrl); ?>" target="_blank" class="btn btn-primary" style="padding: 10px 20px; border-radius: var(--radius-full); font-size: 0.85rem;">
                        <i class="fab fa-google"></i> <?php echo __t('google_maps_cta', $currentLang); ?>
                    </a>
                </div>
            </section>
        <?php endif; ?>

    </main>

    <!-- ÜRÜN DETAY & VARYASYON MODALI (DRAWER / BOTTOM SHEET) -->
    <div class="drawer-backdrop" id="productDrawer">
        <div class="drawer-modal">
            <div class="drawer-handle"></div>
            <div class="drawer-image-wrap">
                <img src="" alt="" id="drawerImg">
                <button type="button" class="drawer-close-btn" id="drawerClose">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="drawer-body">
                <div class="drawer-meta-tags">
                    <span class="product-badge badge-chef" id="drawerBadge" style="position:static;display:none;"></span>
                    <span class="meta-pill"><i class="fas fa-fire-flame-curved" style="color:#ef4444;"></i> <span id="drawerCalories">450 kcal</span></span>
                    <span class="meta-pill"><i class="fas fa-clock" style="color:#3b82f6;"></i> <span id="drawerPrepTime">15 dk</span></span>
                </div>

                <h3 class="drawer-title" id="drawerTitle">Ürün Adı</h3>
                <p class="drawer-description" id="drawerDesc">Ürün açıklaması burada yer alacak.</p>

                <!-- Opsiyonlar / Ekstra Seçenekler -->
                <div id="drawerOptionsContainer" style="display:none;"></div>

                <div class="drawer-allergens-box" id="drawerAllergensBox" style="display:none;">
                    <div class="drawer-allergens-title"><i class="fas fa-shield-halved"></i> <?php echo __t('allergens', $currentLang); ?></div>
                    <div class="drawer-allergens-text" id="drawerAllergensText">Gluten, Laktoz</div>
                </div>

                <div class="drawer-footer">
                    <div class="drawer-price-box">
                        <span style="font-size:0.75rem;color:var(--text-muted);font-weight:600;text-transform:uppercase;"><?php echo __t('total', $currentLang); ?></span>
                        <div>
                            <span class="drawer-price" id="drawerPrice">0,00 ₺</span>
                            <span class="product-old-price" id="drawerOldPrice" style="font-size:0.9rem;margin-left:6px;display:none;"></span>
                        </div>
                    </div>

                    <?php if ($enableOrder): ?>
                        <button type="button" class="bottom-cta-btn" id="drawerAddToCartBtn">
                            <i class="fas fa-cart-plus"></i> <?php echo __t('add_to_cart', $currentLang); ?>
                        </button>
                    <?php else: ?>
                        <button type="button" class="bottom-cta-btn" onclick="document.getElementById('productDrawer').classList.remove('active'); document.getElementById('waiterModal').classList.add('active');">
                            <i class="fas fa-bell"></i> <?php echo __t('call_waiter', $currentLang); ?>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- SEPET ÇEKMECESİ (CART DRAWER) -->
    <?php if ($enableOrder): ?>
        <div class="drawer-backdrop" id="cartDrawer">
            <div class="drawer-modal" style="max-height:85vh;">
                <div class="drawer-handle"></div>
                <div style="padding:16px 20px; border-bottom:1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center;">
                    <h3 style="font-size:1.15rem; font-weight:800; color:var(--text-main);"><i class="fas fa-cart-shopping" style="color:var(--primary);margin-right:8px;"></i> <?php echo __t('cart', $currentLang); ?></h3>
                    <button type="button" class="icon-btn" id="cartCloseBtn"><i class="fas fa-times"></i></button>
                </div>

                <div class="drawer-body" style="padding-top:10px;">
                    <div id="cartItemsContainer"></div>

                    <div style="margin-top:18px; padding-top:14px; border-top:1px solid var(--border-color);">
                        <div style="margin-bottom:12px;">
                            <label style="font-size:0.75rem; font-weight:700; color:var(--text-muted); display:block; margin-bottom:4px;"><?php echo __t('table', $currentLang); ?> *</label>
                            <input type="text" id="orderTableNumber" value="<?php echo htmlspecialchars($tableNumber); ?>" class="search-input" placeholder="Masa No (Örn: 4)" style="height:42px; border-radius:var(--radius-sm);">
                        </div>

                        <div style="margin-bottom:16px;">
                            <label style="font-size:0.75rem; font-weight:700; color:var(--text-muted); display:block; margin-bottom:4px;"><?php echo __t('order_note', $currentLang); ?></label>
                            <input type="text" id="orderCustomerNote" class="search-input" placeholder="<?php echo __t('order_note_placeholder', $currentLang); ?>" style="height:42px; border-radius:var(--radius-sm);">
                        </div>

                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                            <span style="font-weight:700; color:var(--text-muted);"><?php echo __t('total', $currentLang); ?>:</span>
                            <strong style="font-size:1.35rem; color:var(--primary-light);" id="cartTotalEl">0,00 ₺</strong>
                        </div>

                        <button type="button" id="submitOrderBtn" class="bottom-cta-btn" style="width:100%; justify-content:center; padding:14px; font-size:0.95rem;">
                            <i class="fas fa-paper-plane"></i> <?php echo __t('submit_order', $currentLang); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- YÜZEN SEPET BUTONU (FLOATING CART) -->
        <button type="button" class="cart-floating-btn" id="cartFloatingBtn" style="display:none;">
            <i class="fas fa-cart-shopping"></i>
            <span class="cart-badge" id="cartFloatingCount">0</span>
            <span style="font-weight:800;" id="cartFloatingTotal">0,00 ₺</span>
        </button>
    <?php endif; ?>

    <!-- INSTAGRAM HİKAYE OYNATICI MODALI -->
    <div class="story-viewer-modal" id="storyViewerModal">
        <div class="story-viewer-content">
            <div class="story-progress-bar">
                <div class="story-progress-fill" id="storyProgressFill"></div>
            </div>
            <div class="story-viewer-header">
                <span class="story-viewer-title" id="storyViewerTitle">Hikaye</span>
                <button type="button" class="story-viewer-close" id="storyViewerClose"><i class="fas fa-times"></i></button>
            </div>
            <img src="" alt="" class="story-viewer-image" id="storyViewerImg">
            <div class="story-viewer-cta">
                <a href="#" id="storyViewerLink" class="bottom-cta-btn" style="width:100%; justify-content:center;">
                    Detayı Gör <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- AÇILIŞ KAMPANYA POP-UP'I -->
    <?php if ($enablePopup): ?>
        <div class="action-modal" id="popupModal">
            <div class="modal-content" style="padding:0; overflow:hidden; max-width:400px; text-align:center;">
                <div style="position:relative; width:100%; height:200px;">
                    <img src="<?php echo htmlspecialchars($popupImage); ?>" alt="" style="width:100%; height:100%; object-fit:cover;">
                    <button type="button" id="closePopupBtn" style="position:absolute; top:12px; right:12px; width:32px; height:32px; border-radius:50%; background:rgba(0,0,0,0.6); color:#fff; border:none; cursor:pointer;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div style="padding:20px;">
                    <h3 style="font-size:1.25rem; font-weight:800; color:var(--text-main); margin-bottom:6px;"><?php echo htmlspecialchars($popupTitle); ?></h3>
                    <p style="font-size:0.85rem; color:var(--text-muted); line-height:1.5; margin-bottom:18px;"><?php echo htmlspecialchars($popupDesc); ?></p>
                    <a href="<?php echo htmlspecialchars($popupBtnLink); ?>" onclick="document.getElementById('popupModal').classList.remove('active');" class="bottom-cta-btn" style="width:100%; justify-content:center; padding:12px;">
                        <?php echo htmlspecialchars($popupBtnText); ?>
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- ALT SABİT BUTONLAR (FLOATING BAR) -->
    <div class="bottom-bar">
        <button type="button" class="bottom-action-btn" onclick="window.scrollTo({top:0, behavior:'smooth'});">
            <i class="fas fa-compass"></i>
            <span><?php echo __t('menu', $currentLang); ?></span>
        </button>

        <?php if ($enableWaiterCall): ?>
            <button type="button" class="bottom-cta-btn" id="openWaiterBtn">
                <i class="fas fa-bell"></i>
                <span><?php echo __t('call_waiter', $currentLang); ?></span>
            </button>
        <?php endif; ?>

        <button type="button" class="bottom-action-btn" onclick="document.getElementById('wifiModal').classList.add('active');">
            <i class="fas fa-wifi"></i>
            <span><?php echo __t('wifi', $currentLang); ?></span>
        </button>
    </div>

    <!-- GARSON & HESAP ÇAĞIRMA MODALI -->
    <?php if ($enableWaiterCall): ?>
        <div class="action-modal" id="waiterModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title"><i class="fas fa-bell" style="color:var(--primary);margin-right:8px;"></i> <?php echo __t('call_waiter', $currentLang); ?></h3>
                    <button type="button" class="icon-btn" id="closeWaiterBtn">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div style="margin-bottom: 14px;">
                    <label style="font-size:0.8rem;font-weight:700;color:var(--text-muted);display:block;margin-bottom:6px;"><?php echo __t('table', $currentLang); ?> *</label>
                    <input type="text" id="waiterTableNumber" value="<?php echo htmlspecialchars($tableNumber); ?>" placeholder="Örn: 4 veya B2" class="search-input" style="padding-left:16px;">
                </div>

                <div class="call-options-grid">
                    <button type="button" class="call-option-btn selected" data-type="waiter">
                        <i class="fas fa-user-tie"></i>
                        <span><?php echo __t('call_waiter', $currentLang); ?></span>
                    </button>
                    <button type="button" class="call-option-btn" data-type="card_bill">
                        <i class="fas fa-credit-card"></i>
                        <span>Kartla Hesap</span>
                    </button>
                    <button type="button" class="call-option-btn" data-type="cash_bill">
                        <i class="fas fa-money-bill-wave"></i>
                        <span>Nakit Hesap</span>
                    </button>
                    <button type="button" class="call-option-btn" data-type="custom">
                        <i class="fas fa-comment-dots"></i>
                        <span>Özel İstek</span>
                    </button>
                </div>

                <div style="margin-bottom: 16px;">
                    <textarea id="waiterNote" rows="2" placeholder="Örn: Su alabilir miyiz, kül tablası rica ediyoruz..." class="search-input" style="height:auto;padding:10px 14px;border-radius:var(--radius-md);"></textarea>
                </div>

                <button type="button" id="sendWaiterCallBtn" class="bottom-cta-btn" style="width:100%;justify-content:center;padding:14px;">
                    Çağrıyı Gönder
                </button>
            </div>
        </div>
    <?php endif; ?>

    <!-- WI-FI BİLGİLERİ MODALI -->
    <div class="action-modal" id="wifiModal">
        <div class="modal-content" style="text-align:center;">
            <div class="modal-header">
                <h3 class="modal-title"><i class="fas fa-wifi" style="color:var(--primary);margin-right:8px;"></i> <?php echo __t('free_wifi', $currentLang); ?></h3>
                <button type="button" class="icon-btn" id="closeWifiBtn">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div style="padding: 10px 0 20px;">
                <div style="width:64px;height:64px;border-radius:50%;background:rgba(var(--primary-rgb),0.15);color:var(--primary-light);display:inline-flex;align-items:center;justify-content:center;font-size:1.8rem;margin-bottom:12px;">
                    <i class="fas fa-wifi"></i>
                </div>
                <div style="background:var(--bg-surface);border:1px solid var(--border-color);border-radius:var(--radius-md);padding:14px;text-align:left;margin-bottom:16px;">
                    <div style="font-size:0.75rem;color:var(--text-dim);font-weight:600;margin-bottom:2px;">AĞ ADI (SSID)</div>
                    <div style="font-weight:800;color:var(--text-main);font-size:1rem;margin-bottom:10px;"><?php echo htmlspecialchars($wifiName); ?></div>

                    <div style="font-size:0.75rem;color:var(--text-dim);font-weight:600;margin-bottom:2px;">ŞİFRE</div>
                    <div style="font-weight:800;color:var(--primary-light);font-size:1.1rem;letter-spacing:1px;" id="wifiPasswordText"><?php echo htmlspecialchars($wifiPass); ?></div>
                </div>

                <button type="button" id="copyWifiBtn" class="bottom-cta-btn" style="width:100%;justify-content:center;padding:12px;">
                    <i class="fas fa-copy"></i> <?php echo __t('copy_password', $currentLang); ?>
                </button>
            </div>
        </div>
    </div>

    <!-- JavaScript Motoru -->
    <script src="assets/js/menu.js"></script>
</body>
</html>
