<?php
/**
 * Restoran Bilgileri, Modül Yönetimi (Aç/Kapa), Logo ve Tema Ayarları
 */

require_once __DIR__ . '/header.php';

$successMsg = '';
$errorMsg = '';

// POST: Ayarları Kaydet
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $actionType = $_POST['setting_type'] ?? 'general';

    if ($actionType === 'general') {
        $restaurantName = clean($_POST['restaurant_name'] ?? '');
        $restaurantSlogan = clean($_POST['restaurant_slogan'] ?? '');
        $currency = clean($_POST['currency'] ?? '₺');
        $themeColor = clean($_POST['theme_color'] ?? '#C5A059');
        $themeMode = clean($_POST['theme_mode'] ?? 'light');
        $wifiName = clean($_POST['wifi_name'] ?? '');
        $wifiPass = clean($_POST['wifi_pass'] ?? '');
        $phone = clean($_POST['phone'] ?? '');
        $instagram = clean($_POST['instagram'] ?? '');
        $address = clean($_POST['address'] ?? '');
        $googleMapsUrl = clean($_POST['google_maps_url'] ?? '');

        // Modül Aç/Kapa Değerleri (Checkbox)
        $enableOrder = !empty($_POST['enable_order']) ? '1' : '0';
        $enableMultiLang = !empty($_POST['enable_multi_lang']) ? '1' : '0';
        $enableKitchen = !empty($_POST['enable_kitchen']) ? '1' : '0';
        $enableStories = !empty($_POST['enable_stories']) ? '1' : '0';
        $enablePopup = !empty($_POST['enable_popup']) ? '1' : '0';
        $enableFeedback = !empty($_POST['enable_feedback']) ? '1' : '0';
        $enableAllergensFilter = !empty($_POST['enable_allergens_filter']) ? '1' : '0';
        $enableWaiterCall = !empty($_POST['enable_waiter_call']) ? '1' : '0';

        $logoDarkUrl = getSetting('logo_dark_url', '');
        $logoLightUrl = getSetting('logo_light_url', '');
        $bannerUrl = getSetting('banner_url', '');

        // 1. Dark Logo Dosyası Yüklendi mi?
        if (!empty($_FILES['logo_dark_file']['name'])) {
            $darkRes = uploadImage($_FILES['logo_dark_file'], 'branding', 500, 500);
            if ($darkRes['success']) {
                $logoDarkUrl = $darkRes['full_url'];
            } else {
                $errorMsg = 'Dark Logo Yükleme Hatası: ' . $darkRes['error'];
            }
        } elseif (isset($_POST['logo_dark_url'])) {
            $logoDarkUrl = clean($_POST['logo_dark_url']);
        }

        // 2. Light Logo Dosyası Yüklendi mi?
        if (!empty($_FILES['logo_light_file']['name'])) {
            $lightRes = uploadImage($_FILES['logo_light_file'], 'branding', 500, 500);
            if ($lightRes['success']) {
                $logoLightUrl = $lightRes['full_url'];
            } else {
                $errorMsg = 'Light Logo Yükleme Hatası: ' . $lightRes['error'];
            }
        } elseif (isset($_POST['logo_light_url'])) {
            $logoLightUrl = clean($_POST['logo_light_url']);
        }

        // 3. Banner Dosyası Yüklendi mi?
        if (!empty($_FILES['banner_file']['name'])) {
            $bannerRes = uploadImage($_FILES['banner_file'], 'branding', 1600, 800);
            if ($bannerRes['success']) {
                $bannerUrl = $bannerRes['full_url'];
            } else {
                $errorMsg = 'Banner Yükleme Hatası: ' . $bannerRes['error'];
            }
        } elseif (isset($_POST['banner_url'])) {
            $bannerUrl = clean($_POST['banner_url']);
        }

        if (empty($errorMsg)) {
            updateSetting('restaurant_name', $restaurantName);
            updateSetting('restaurant_slogan', $restaurantSlogan);
            updateSetting('currency', $currency);
            updateSetting('theme_color', $themeColor);
            updateSetting('theme_mode', $themeMode);
            updateSetting('logo_dark_url', $logoDarkUrl);
            updateSetting('logo_light_url', $logoLightUrl);
            updateSetting('banner_url', $bannerUrl);
            updateSetting('wifi_name', $wifiName);
            updateSetting('wifi_pass', $wifiPass);
            updateSetting('phone', $phone);
            updateSetting('instagram', $instagram);
            updateSetting('address', $address);
            updateSetting('google_maps_url', $googleMapsUrl);

            // Modül Ayarlarını Güncelle
            updateSetting('enable_order', $enableOrder);
            updateSetting('enable_multi_lang', $enableMultiLang);
            updateSetting('enable_kitchen', $enableKitchen);
            updateSetting('enable_stories', $enableStories);
            updateSetting('enable_popup', $enablePopup);
            updateSetting('enable_feedback', $enableFeedback);
            updateSetting('enable_allergens_filter', $enableAllergensFilter);
            updateSetting('enable_waiter_call', $enableWaiterCall);

            $successMsg = 'Restoran ve modül ayarları başarıyla kaydedildi!';
        }
    } elseif ($actionType === 'password') {
        $currentPass = $_POST['current_password'] ?? '';
        $newPass = $_POST['new_password'] ?? '';
        $newPassConfirm = $_POST['new_password_confirm'] ?? '';

        if (empty($currentPass) || empty($newPass)) {
            $errorMsg = 'Lütfen tüm şifre alanlarını doldurun.';
        } elseif ($newPass !== $newPassConfirm) {
            $errorMsg = 'Yeni şifreler birbiriyle eşleşmiyor.';
        } elseif (strlen($newPass) < 6) {
            $errorMsg = 'Yeni şifre en az 6 karakter olmalıdır.';
        } else {
            $adminId = $_SESSION['admin_id'];
            $stmt = $pdo->prepare("SELECT password_hash FROM admins WHERE id = ?");
            $stmt->execute([$adminId]);
            $hash = $stmt->fetchColumn();

            if ($hash && password_verify($currentPass, $hash)) {
                $newHash = password_hash($newPass, PASSWORD_DEFAULT);
                $pdo->prepare("UPDATE admins SET password_hash = ? WHERE id = ?")->execute([$newHash, $adminId]);
                $successMsg = 'Yönetici şifreniz başarıyla değiştirildi!';
            } else {
                $errorMsg = 'Mevcut şifrenizi hatalı girdiniz.';
            }
        }
    }
}

// Güncel Ayarları Oku
$restaurantName = getSetting('restaurant_name', 'Gusto Gourmet & Lounge');
$restaurantSlogan = getSetting('restaurant_slogan', 'Eşsiz Lezzetler & Keyifli Anlar');
$currency = getSetting('currency', '₺');
$themeColor = getSetting('theme_color', '#d97706');
$themeMode = getSetting('theme_mode', 'dark');
$logoDarkUrl = getSetting('logo_dark_url', getSetting('logo_url', ''));
$logoLightUrl = getSetting('logo_light_url', '');
$bannerUrl = getSetting('banner_url', 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=1200&q=80');
$wifiName = getSetting('wifi_name', 'Gusto_Guest_5G');
$wifiPass = getSetting('wifi_pass', 'Gusto2026!');
$phone = getSetting('phone', '');
$instagram = getSetting('instagram', '');
$address = getSetting('address', '');
$googleMapsUrl = getSetting('google_maps_url', 'https://maps.google.com/?q=Gusto+Gourmet');

// Modül Durumları
$enableOrder = getSetting('enable_order', '1') === '1';
$enableMultiLang = getSetting('enable_multi_lang', '1') === '1';
$enableKitchen = getSetting('enable_kitchen', '1') === '1';
$enableStories = getSetting('enable_stories', '1') === '1';
$enablePopup = getSetting('enable_popup', '1') === '1';
$enableFeedback = getSetting('enable_feedback', '1') === '1';
$enableAllergensFilter = getSetting('enable_allergens_filter', '1') === '1';
$enableWaiterCall = getSetting('enable_waiter_call', '1') === '1';
?>

<div class="page-header">
    <div class="page-title">
        <h1>⚙️ Modül, Restoran & Görünüm Ayarları</h1>
        <p>Menü modüllerini tek tıkla açıp kapatın, Dark/Light tema ve marka bilgilerinizi yönetin</p>
    </div>
</div>

<?php if (!empty($successMsg)): ?>
    <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid var(--success); color: #6ee7b7; padding: 12px 16px; border-radius: var(--radius-sm); font-size: 0.88rem; margin-bottom: 20px;">
        <i class="fas fa-check-circle" style="margin-right: 6px;"></i> <?php echo htmlspecialchars($successMsg); ?>
    </div>
<?php endif; ?>

<?php if (!empty($errorMsg)): ?>
    <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid var(--danger); color: #fca5a5; padding: 12px 16px; border-radius: var(--radius-sm); font-size: 0.88rem; margin-bottom: 20px;">
        <i class="fas fa-circle-exclamation" style="margin-right: 6px;"></i> <?php echo htmlspecialchars($errorMsg); ?>
    </div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">

    <!-- GENEL AYARLAR VE MODÜLLER FORMU -->
    <form method="POST" action="settings.php" enctype="multipart/form-data">
        <input type="hidden" name="setting_type" value="general">

        <!-- 1. MODÜL VE ÖZELLİK YÖNETİMİ (AÇ / KAPA) -->
        <div class="card" style="border: 2px solid #3b82f6; background: linear-gradient(135deg, rgba(59, 130, 246, 0.08) 0%, rgba(22, 31, 48, 0.95) 100%);">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-toggle-on" style="color:#60a5fa;"></i> Modül & Özellik Yönetimi (Aç / Kapa)</h3>
                <span style="font-size: 0.75rem; background: #3b82f6; color: #fff; padding: 3px 8px; border-radius: 4px; font-weight: 700;">8 Aktif Modül</span>
            </div>
            <div class="card-body">
                <p style="font-size: 0.84rem; color: var(--text-muted); margin-bottom: 18px;">
                    İstediğiniz özellikleri tek tıkla menüden gizleyebilir veya aktif edebilirsiniz:
                </p>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    
                    <!-- 1. Sipariş & Sepet -->
                    <div style="background: var(--bg-input); padding: 12px 16px; border-radius: var(--radius-sm); border: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <div style="font-weight: 700; color: #fff; font-size: 0.88rem;"><i class="fas fa-cart-shopping" style="color:var(--primary);margin-right:6px;"></i> Masadan Canlı Sipariş</div>
                            <div style="font-size: 0.72rem; color: var(--text-dim);">Sepet & Masadan sipariş verme</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="enable_order" value="1" <?php echo $enableOrder ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <!-- 2. Çoklu Dil -->
                    <div style="background: var(--bg-input); padding: 12px 16px; border-radius: var(--radius-sm); border: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <div style="font-weight: 700; color: #fff; font-size: 0.88rem;"><i class="fas fa-language" style="color:#60a5fa;margin-right:6px;"></i> Çoklu Dil Desteği</div>
                            <div style="font-size: 0.72rem; color: var(--text-dim);">TR, EN, AR, RU, DE dilleri</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="enable_multi_lang" value="1" <?php echo $enableMultiLang ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <!-- 3. Mutfak Ekranı -->
                    <div style="background: var(--bg-input); padding: 12px 16px; border-radius: var(--radius-sm); border: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <div style="font-weight: 700; color: #fff; font-size: 0.88rem;"><i class="fas fa-kitchen-set" style="color:#34d399;margin-right:6px;"></i> Mutfak Ekranı (KDS)</div>
                            <div style="font-size: 0.72rem; color: var(--text-dim);">Canlı sipariş takibi & termal fiş</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="enable_kitchen" value="1" <?php echo $enableKitchen ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <!-- 4. Hikayeler -->
                    <div style="background: var(--bg-input); padding: 12px 16px; border-radius: var(--radius-sm); border: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <div style="font-weight: 700; color: #fff; font-size: 0.88rem;"><i class="fas fa-circle-play" style="color:#f43f5e;margin-right:6px;"></i> Kampanya Hikayeleri</div>
                            <div style="font-size: 0.72rem; color: var(--text-dim);">Instagram tarzı hikaye çubuğu</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="enable_stories" value="1" <?php echo $enableStories ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <!-- 5. Giriş Pop-up -->
                    <div style="background: var(--bg-input); padding: 12px 16px; border-radius: var(--radius-sm); border: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <div style="font-weight: 700; color: #fff; font-size: 0.88rem;"><i class="fas fa-bullhorn" style="color:#fbbf24;margin-right:6px;"></i> Açılış Pop-Up Duyuru</div>
                            <div style="font-size: 0.72rem; color: var(--text-dim);">Girişte kampanya penceresi</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="enable_popup" value="1" <?php echo $enablePopup ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <!-- 6. Google Yorumları & Puanlama -->
                    <div style="background: var(--bg-input); padding: 12px 16px; border-radius: var(--radius-sm); border: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <div style="font-weight: 700; color: #fff; font-size: 0.88rem;"><i class="fas fa-star" style="color:#fbbf24;margin-right:6px;"></i> Google Yorum & Puan</div>
                            <div style="font-size: 0.72rem; color: var(--text-dim);">1-5 yıldız ve Harita yönlendirme</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="enable_feedback" value="1" <?php echo $enableFeedback ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <!-- 7. Alerjen & Diyet Filtresi -->
                    <div style="background: var(--bg-input); padding: 12px 16px; border-radius: var(--radius-sm); border: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <div style="font-weight: 700; color: #fff; font-size: 0.88rem;"><i class="fas fa-shield-halved" style="color:#10b981;margin-right:6px;"></i> Alerjen & Diyet Filtresi</div>
                            <div style="font-size: 0.72rem; color: var(--text-dim);">Glutensiz, Vegan, Kalori filtreleri</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="enable_allergens_filter" value="1" <?php echo $enableAllergensFilter ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <!-- 8. Garson & Hesap Çağrı -->
                    <div style="background: var(--bg-input); padding: 12px 16px; border-radius: var(--radius-sm); border: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <div style="font-weight: 700; color: #fff; font-size: 0.88rem;"><i class="fas fa-bell" style="color:var(--primary);margin-right:6px;"></i> Garson & Hesap Çağrı</div>
                            <div style="font-size: 0.72rem; color: var(--text-dim);">Masadan garson çağırma modülü</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="enable_waiter_call" value="1" <?php echo $enableWaiterCall ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                    </div>

                </div>
            </div>
        </div>

        <!-- 2. TEMA MODU SEÇİMİ (DARK / LIGHT) -->
        <div class="card" style="border: 2px solid var(--border-focus);">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-circle-half-stroke" style="color:var(--primary);"></i> Menü Tema Modu</h3>
                <span style="font-size: 0.75rem; background: var(--primary); color: #fff; padding: 3px 8px; border-radius: 4px; font-weight: 700;">Admin Belirler</span>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <!-- Dark Mode -->
                    <label style="cursor: pointer; display: block;">
                        <input type="radio" name="theme_mode" value="dark" <?php echo $themeMode === 'dark' ? 'checked' : ''; ?> style="display: none;" onchange="updateThemeCards()">
                        <div id="cardThemeDark" style="border: 2px solid <?php echo $themeMode === 'dark' ? 'var(--primary)' : 'var(--border)'; ?>; background: #0c0f14; border-radius: var(--radius-md); padding: 16px; text-align: center; transition: all 0.2s ease;">
                            <div style="font-size: 2rem; margin-bottom: 8px;">🌙</div>
                            <div style="font-weight: 800; color: #fff; font-size: 0.95rem;">Lüks Koyu Tema (Dark)</div>
                            <div style="font-size: 0.75rem; color: #94a3b8; margin-top: 4px;">Siyah & Koyu zemin üzerinde lüks altın görünüm</div>
                        </div>
                    </label>

                    <!-- Light Mode -->
                    <label style="cursor: pointer; display: block;">
                        <input type="radio" name="theme_mode" value="light" <?php echo $themeMode === 'light' ? 'checked' : ''; ?> style="display: none;" onchange="updateThemeCards()">
                        <div id="cardThemeLight" style="border: 2px solid <?php echo $themeMode === 'light' ? 'var(--primary)' : 'var(--border)'; ?>; background: #ffffff; border-radius: var(--radius-md); padding: 16px; text-align: center; transition: all 0.2s ease;">
                            <div style="font-size: 2rem; margin-bottom: 8px;">☀️</div>
                            <div style="font-weight: 800; color: #0f172a; font-size: 0.95rem;">Ferah Açık Tema (Light)</div>
                            <div style="font-size: 0.75rem; color: #64748b; margin-top: 4px;">Beyaz & Aydınlık zemin üzerinde modern görünüm</div>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <!-- 3. DARK VE LIGHT LOGOLAR -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-images" style="color:var(--primary);"></i> Tema Logoları (Dark & Light)</h3>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    
                    <!-- Dark Logo -->
                    <div style="background: var(--bg-input); padding: 16px; border-radius: var(--radius-md); border: 1px solid var(--border);">
                        <label class="form-label" style="color: #fbbf24;"><i class="fas fa-moon"></i> Koyu (Dark) Tema Logosu</label>
                        <input type="file" name="logo_dark_file" class="form-control image-upload-input" data-preview="logoDarkPreview" accept="image/*" style="margin-bottom: 8px;">
                        <input type="url" name="logo_dark_url" value="<?php echo htmlspecialchars($logoDarkUrl); ?>" placeholder="veya Dark Logo URL" class="form-control" style="margin-bottom: 12px;">

                        <div style="background: #0c0f14; border: 2px dashed rgba(255,255,255,0.15); border-radius: var(--radius-sm); padding: 14px; text-align: center; min-height: 85px; display: flex; align-items: center; justify-content: center;">
                            <?php if (!empty($logoDarkUrl)): ?>
                                <img id="logoDarkPreview" src="<?php echo htmlspecialchars($logoDarkUrl); ?>" alt="Dark Logo" style="max-height: 65px; max-width: 100%; object-fit: contain;">
                            <?php else: ?>
                                <img id="logoDarkPreview" src="" alt="Dark Logo" style="max-height: 65px; max-width: 100%; object-fit: contain; display: none;">
                                <span style="font-size: 0.75rem; color: var(--text-dim);"><i class="fas fa-image"></i> Dark Logo Yüklenmedi</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Light Logo -->
                    <div style="background: var(--bg-input); padding: 16px; border-radius: var(--radius-md); border: 1px solid var(--border);">
                        <label class="form-label" style="color: #38bdf8;"><i class="fas fa-sun"></i> Açık (Light) Tema Logosu</label>
                        <input type="file" name="logo_light_file" class="form-control image-upload-input" data-preview="logoLightPreview" accept="image/*" style="margin-bottom: 8px;">
                        <input type="url" name="logo_light_url" value="<?php echo htmlspecialchars($logoLightUrl); ?>" placeholder="veya Light Logo URL" class="form-control" style="margin-bottom: 12px;">

                        <div style="background: #ffffff; border: 2px dashed #cbd5e1; border-radius: var(--radius-sm); padding: 14px; text-align: center; min-height: 85px; display: flex; align-items: center; justify-content: center;">
                            <?php if (!empty($logoLightUrl)): ?>
                                <img id="logoLightPreview" src="<?php echo htmlspecialchars($logoLightUrl); ?>" alt="Light Logo" style="max-height: 65px; max-width: 100%; object-fit: contain;">
                            <?php else: ?>
                                <img id="logoLightPreview" src="" alt="Light Logo" style="max-height: 65px; max-width: 100%; object-fit: contain; display: none;">
                                <span style="font-size: 0.75rem; color: #64748b;"><i class="fas fa-image"></i> Light Logo Yüklenmedi</span>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- 4. MENÜ KAPAK GÖRSELİ (BANNER) -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-panorama" style="color:var(--primary);"></i> Menü Üst Kapak Görseli (Banner)</h3>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; align-items: center;">
                    <div>
                        <label class="form-label">Kapak Görseli Yükle</label>
                        <input type="file" name="banner_file" class="form-control image-upload-input" data-preview="bannerPreview" accept="image/*" style="margin-bottom: 8px;">
                        <input type="url" name="banner_url" value="<?php echo htmlspecialchars($bannerUrl); ?>" placeholder="veya Banner URL" class="form-control">
                    </div>
                    <div style="text-align: center;">
                        <img id="bannerPreview" src="<?php echo htmlspecialchars($bannerUrl); ?>" alt="Banner" style="max-height: 80px; width: 100%; object-fit: cover; border-radius: var(--radius-sm); border: 1px solid var(--border);">
                    </div>
                </div>
            </div>
        </div>

        <!-- 5. RESTORAN BİLGİLERİ VE GOOGLE HARİTA -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-store" style="color:var(--primary);"></i> Restoran Bilgileri & Google Harita Linki</h3>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Restoran / İşletme Adı *</label>
                        <input type="text" name="restaurant_name" value="<?php echo htmlspecialchars($restaurantName); ?>" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Slogan / Açıklama</label>
                        <input type="text" name="restaurant_slogan" value="<?php echo htmlspecialchars($restaurantSlogan); ?>" class="form-control">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Para Birimi</label>
                        <input type="text" name="currency" value="<?php echo htmlspecialchars($currency); ?>" class="form-control">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Vurgu / Buton Rengi</label>
                        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                            <input type="color" name="theme_color" id="themeColorPicker" value="<?php echo htmlspecialchars($themeColor); ?>" style="width: 44px; height: 42px; padding: 2px; border-radius: var(--radius-sm); border: 1px solid var(--border); background: var(--bg-input); cursor: pointer;">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="setThemeColor('#d97706')">Sıcak Amber</button>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="setThemeColor('#059669')">Zümrüt Yeşili</button>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="setThemeColor('#2563eb')">Gece Mavisi</button>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="setThemeColor('#dc2626')">Kırmızı</button>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="setThemeColor('#7c3aed')">Mor</button>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label"><i class="fab fa-google" style="color:#ea4335;margin-right:4px;"></i> Google Haritalar / Yorum Linki (5 Yıldız Yönlendirmesi)</label>
                    <input type="url" name="google_maps_url" value="<?php echo htmlspecialchars($googleMapsUrl); ?>" placeholder="https://maps.google.com/?q=..." class="form-control">
                </div>
            </div>
        </div>

        <!-- 6. WI-FI & İLETİŞİM -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-wifi" style="color:var(--primary);"></i> Wi-Fi & İletişim</h3>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Wi-Fi Ağ Adı (SSID)</label>
                        <input type="text" name="wifi_name" value="<?php echo htmlspecialchars($wifiName); ?>" class="form-control">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Wi-Fi Şifresi</label>
                        <input type="text" name="wifi_pass" value="<?php echo htmlspecialchars($wifiPass); ?>" class="form-control">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Telefon</label>
                        <input type="text" name="phone" value="<?php echo htmlspecialchars($phone); ?>" class="form-control">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Instagram Kullanıcı Adı</label>
                        <input type="text" name="instagram" value="<?php echo htmlspecialchars($instagram); ?>" placeholder="Örn: gustogourmet" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Restoran Adresi</label>
                    <textarea name="address" rows="2" class="form-control"><?php echo htmlspecialchars($address); ?></textarea>
                </div>
            </div>
            <div class="card-header" style="justify-content: flex-end;">
                <button type="submit" class="btn btn-primary" style="padding: 12px 28px; font-size: 1rem; font-weight: 800;">
                    <i class="fas fa-save"></i> Tüm Ayarları Kaydet
                </button>
            </div>
        </div>
    </form>

    <!-- ŞİFRE DEĞİŞTİRME & SİSTEM ÖZETİ -->
    <div>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-key" style="color:var(--warning);"></i> Yönetici Şifresi</h3>
            </div>
            <form method="POST" action="settings.php">
                <input type="hidden" name="setting_type" value="password">
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Mevcut Şifre *</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Yeni Şifre (En az 6 karakter) *</label>
                        <input type="password" name="new_password" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Yeni Şifre Tekrar *</label>
                        <input type="password" name="new_password_confirm" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-secondary" style="width: 100%;">
                        <i class="fas fa-lock"></i> Şifreyi Güncelle
                    </button>
                </div>
            </form>
        </div>

        <div class="card" style="background: rgba(16, 185, 129, 0.08); border-color: rgba(16, 185, 129, 0.3);">
            <div class="card-body">
                <h4 style="font-size: 0.95rem; font-weight: 700; color: #34d399; margin-bottom: 8px;">
                    <i class="fas fa-circle-check"></i> Sistem & Modül Durumu
                </h4>
                <p style="font-size: 0.8rem; color: var(--text-muted); line-height: 1.7;">
                    • Masadan Sipariş: <strong><?php echo $enableOrder ? 'Aktif ✓' : 'Kapalı ✗'; ?></strong><br>
                    • Çoklu Dil Desteği: <strong><?php echo $enableMultiLang ? 'Aktif ✓' : 'Kapalı ✗'; ?></strong><br>
                    • Mutfak Ekranı (KDS): <strong><?php echo $enableKitchen ? 'Aktif ✓' : 'Kapalı ✗'; ?></strong><br>
                    • Hikayeler & Pop-Up: <strong><?php echo $enableStories ? 'Aktif ✓' : 'Kapalı ✗'; ?></strong><br>
                    • Google Yorumları: <strong><?php echo $enableFeedback ? 'Aktif ✓' : 'Kapalı ✗'; ?></strong><br>
                    • Aktif Menü Teması: <strong style="color:#fff; text-transform:uppercase;"><?php echo $themeMode; ?></strong>
                </p>
            </div>
        </div>
    </div>

</div>

<script>
function setThemeColor(hex) {
    document.getElementById('themeColorPicker').value = hex;
}

function updateThemeCards() {
    const isDark = document.querySelector('input[name="theme_mode"][value="dark"]').checked;
    const cardDark = document.getElementById('cardThemeDark');
    const cardLight = document.getElementById('cardThemeLight');

    if (isDark) {
        cardDark.style.borderColor = 'var(--primary)';
        cardLight.style.borderColor = 'var(--border)';
    } else {
        cardDark.style.borderColor = 'var(--border)';
        cardLight.style.borderColor = 'var(--primary)';
    }
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
