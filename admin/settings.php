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

        // Yeni Modüller
        $enableCurrencyConverter = !empty($_POST['enable_currency_converter']) ? '1' : '0';
        $currencyEurRate = clean($_POST['currency_eur_rate'] ?? '38.50');
        $currencyUsdRate = clean($_POST['currency_usd_rate'] ?? '35.00');
        $currencyGbpRate = clean($_POST['currency_gbp_rate'] ?? '46.00');

        $enablePairings = !empty($_POST['enable_pairings']) ? '1' : '0';

        $enableHappyHour = !empty($_POST['enable_happy_hour']) ? '1' : '0';
        $happyHourTitle = clean($_POST['happy_hour_title'] ?? '🌅 Gün Batımı Happy Hour (Tüm Kokteyllerde %15 İndirim)');
        $happyHourStart = clean($_POST['happy_hour_start'] ?? '17:00');
        $happyHourEnd = clean($_POST['happy_hour_end'] ?? '19:30');
        $happyHourDiscount = clean($_POST['happy_hour_discount'] ?? '15');

        $enableResortService = !empty($_POST['enable_resort_service']) ? '1' : '0';
        $enableEvents = !empty($_POST['enable_events']) ? '1' : '0';
        $enableConcierge = !empty($_POST['enable_concierge']) ? '1' : '0';

        $enableTelegramNotify = !empty($_POST['enable_telegram_notify']) ? '1' : '0';
        $telegramBotToken = clean($_POST['telegram_bot_token'] ?? '');
        $telegramChatId = clean($_POST['telegram_chat_id'] ?? '');

        $enableWhatsappNotify = !empty($_POST['enable_whatsapp_notify']) ? '1' : '0';
        $whatsappPhone = clean($_POST['whatsapp_phone'] ?? '');

        $enableLuckyWheel = !empty($_POST['enable_lucky_wheel']) ? '1' : '0';
        $wheelRewards = clean($_POST['wheel_rewards'] ?? 'Günün Tatlısı İkramı,%10 Hesap İndirimi,Türk Kahvesi İkramı,Şefin Özel Kokteyli,%15 İndirim,Teşekkürler');

        // Pop-up Ayarları
        $popupTitle = clean($_POST['popup_title'] ?? '');
        $popupDesc = clean($_POST['popup_desc'] ?? '');
        $popupImage = clean($_POST['popup_image'] ?? '');
        $popupBtnText = clean($_POST['popup_btn_text'] ?? '');
        $popupBtnLink = clean($_POST['popup_btn_link'] ?? '');

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

            // Yeni Modüller
            updateSetting('enable_currency_converter', $enableCurrencyConverter);
            updateSetting('currency_eur_rate', $currencyEurRate);
            updateSetting('currency_usd_rate', $currencyUsdRate);
            updateSetting('currency_gbp_rate', $currencyGbpRate);

            updateSetting('enable_pairings', $enablePairings);

            updateSetting('enable_happy_hour', $enableHappyHour);
            updateSetting('happy_hour_title', $happyHourTitle);
            updateSetting('happy_hour_start', $happyHourStart);
            updateSetting('happy_hour_end', $happyHourEnd);
            updateSetting('happy_hour_discount', $happyHourDiscount);

            updateSetting('enable_resort_service', $enableResortService);
            updateSetting('enable_events', $enableEvents);
            updateSetting('enable_concierge', $enableConcierge);

            updateSetting('enable_telegram_notify', $enableTelegramNotify);
            updateSetting('telegram_bot_token', $telegramBotToken);
            updateSetting('telegram_chat_id', $telegramChatId);

            updateSetting('enable_whatsapp_notify', $enableWhatsappNotify);
            updateSetting('whatsapp_phone', $whatsappPhone);

            updateSetting('enable_lucky_wheel', $enableLuckyWheel);
            updateSetting('wheel_rewards', $wheelRewards);

            if (!empty($popupTitle)) updateSetting('popup_title', $popupTitle);
            if (!empty($popupDesc)) updateSetting('popup_desc', $popupDesc);
            if (!empty($popupImage)) updateSetting('popup_image', $popupImage);
            if (!empty($popupBtnText)) updateSetting('popup_btn_text', $popupBtnText);
            if (!empty($popupBtnLink)) updateSetting('popup_btn_link', $popupBtnLink);

            $successMsg = 'Tüm restoran ve modül ayarları başarıyla kaydedildi!';
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
$restaurantName = getSetting('restaurant_name', 'HOTEL MARE & MONTE BISTRO');
$restaurantSlogan = getSetting('restaurant_slogan', 'Altınoluk (Est. 1985)');
$currency = getSetting('currency', '₺');
$themeColor = getSetting('theme_color', '#C5A059');
$themeMode = getSetting('theme_mode', 'light');
$logoDarkUrl = getSetting('logo_dark_url', getSetting('logo_url', 'assets/images/maremonte_logo.svg'));
$logoLightUrl = getSetting('logo_light_url', 'assets/images/maremonte_logo.svg');
$bannerUrl = getSetting('banner_url', 'https://images.unsplash.com/photo-1544161515-4ab6ce6db874?w=1200&q=80');
$wifiName = getSetting('wifi_name', 'MareMonte_Guest');
$wifiPass = getSetting('wifi_pass', 'MareMonte1985');
$phone = getSetting('phone', '+90 (266) 396 00 00');
$instagram = getSetting('instagram', 'hotelmaremonte');
$address = getSetting('address', 'İskele Mah. Sahil Cad. No:14, Altınoluk / Balıkesir');
$googleMapsUrl = getSetting('google_maps_url', 'https://maps.google.com/?q=Hotel+Mare+Monte+Altinoluk');

// Modül Durumları
$enableOrder = getSetting('enable_order', '0') === '1';
$enableMultiLang = getSetting('enable_multi_lang', '1') === '1';
$enableKitchen = getSetting('enable_kitchen', '1') === '1';
$enableStories = getSetting('enable_stories', '1') === '1';
$enablePopup = getSetting('enable_popup', '1') === '1';
$enableFeedback = getSetting('enable_feedback', '1') === '1';
$enableAllergensFilter = getSetting('enable_allergens_filter', '1') === '1';
$enableWaiterCall = getSetting('enable_waiter_call', '1') === '1';

// Yeni Modül Durumları
$enableCurrencyConverter = getSetting('enable_currency_converter', '1') === '1';
$currencyEurRate = getSetting('currency_eur_rate', '38.50');
$currencyUsdRate = getSetting('currency_usd_rate', '35.00');
$currencyGbpRate = getSetting('currency_gbp_rate', '46.00');

$enablePairings = getSetting('enable_pairings', '1') === '1';

$enableHappyHour = getSetting('enable_happy_hour', '1') === '1';
$happyHourTitle = getSetting('happy_hour_title', '🌅 Gün Batımı Happy Hour (Tüm Kokteyllerde %15 İndirim)');
$happyHourStart = getSetting('happy_hour_start', '17:00');
$happyHourEnd = getSetting('happy_hour_end', '19:30');
$happyHourDiscount = getSetting('happy_hour_discount', '15');

$enableResortService = getSetting('enable_resort_service', '1') === '1';
$enableEvents = getSetting('enable_events', '1') === '1';
$enableConcierge = getSetting('enable_concierge', '1') === '1';

$enableTelegramNotify = getSetting('enable_telegram_notify', '0') === '1';
$telegramBotToken = getSetting('telegram_bot_token', '');
$telegramChatId = getSetting('telegram_chat_id', '');

$enableWhatsappNotify = getSetting('enable_whatsapp_notify', '0') === '1';
$whatsappPhone = getSetting('whatsapp_phone', '+902663960000');

$enableLuckyWheel = getSetting('enable_lucky_wheel', '1') === '1';
$wheelRewards = getSetting('wheel_rewards', 'Günün Tatlısı İkramı,%10 Hesap İndirimi,Türk Kahvesi İkramı,Şefin Özel Kokteyli,%15 İndirim,Teşekkürler');

$popupTitle = getSetting('popup_title', '🌊 Hotel Mare & Monte Bistro Hoş Geldiniz!');
$popupDesc = getSetting('popup_desc', '1985\'ten beri Altınoluk sahilinde eşsiz lezzetler. Günlük taze deniz ürünlerimiz ve şefin spesiyallerini keşfedin!');
$popupImage = getSetting('popup_image', 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?w=800&q=80');
$popupBtnText = getSetting('popup_btn_text', 'Deniz Ürünlerini İncele');
$popupBtnLink = getSetting('popup_btn_link', '#cat-8');
?>

<style>
.settings-layout-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 24px;
    width: 100%;
}

.modules-toggle-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 14px;
}

.theme-mode-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}

.logo-upload-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.form-2col-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}

.form-3col-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 14px;
}

.form-1-2col-grid {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 14px;
}

.module-toggle-item {
    background: var(--bg-input);
    padding: 12px 14px;
    border-radius: var(--radius-sm);
    border: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    min-width: 0;
}

.module-toggle-item > div:first-child {
    min-width: 0;
    flex: 1;
}

.settings-subcard {
    background: rgba(0,0,0,0.2);
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    padding: 14px;
    margin-top: 14px;
}

@media (max-width: 1024px) {
    .settings-layout-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
}

@media (max-width: 768px) {
    .modules-toggle-grid,
    .form-3col-grid {
        grid-template-columns: 1fr;
        gap: 10px;
    }
    .theme-mode-grid {
        grid-template-columns: 1fr;
        gap: 12px;
    }
    .logo-upload-grid {
        grid-template-columns: 1fr;
        gap: 14px;
    }
    .form-2col-grid,
    .form-1-2col-grid {
        grid-template-columns: 1fr;
        gap: 12px;
    }
}
</style>

<div class="page-header">
    <div class="page-title">
        <h1>⚙️ Modül, Restoran & Görünüm Ayarları</h1>
        <p>Tüm gelişmiş otel & bistro özelliklerini tek tıkla açıp kapatın, kurları ve bildirimleri yönetin</p>
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

<div class="settings-layout-grid">

    <!-- GENEL AYARLAR VE MODÜLLER FORMU -->
    <form method="POST" action="settings.php" enctype="multipart/form-data" style="width: 100%;">
        <input type="hidden" name="setting_type" value="general">

        <!-- 1. MODÜL VE ÖZELLİK YÖNETİMİ (AÇ / KAPA) -->
        <div class="card" style="border: 2px solid var(--primary); background: linear-gradient(135deg, rgba(197, 160, 89, 0.08) 0%, rgba(22, 31, 48, 0.95) 100%);">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-toggle-on" style="color:var(--primary);"></i> Modül & Özellik Yönetimi (Aç / Kapa)</h3>
                <span style="font-size: 0.75rem; background: var(--primary); color: #000; padding: 3px 10px; border-radius: 999px; font-weight: 800;">14 Modül Aktif / Pasif</span>
            </div>
            <div class="card-body">
                <p style="font-size: 0.84rem; color: var(--text-muted); margin-bottom: 18px;">
                    İstediğiniz özellikleri tek tıkla menüden gizleyebilir veya anında aktif edebilirsiniz:
                </p>

                <div class="modules-toggle-grid">
                    
                    <!-- 1. Sipariş & Sepet -->
                    <div class="module-toggle-item">
                        <div>
                            <div style="font-weight: 700; color: #fff; font-size: 0.88rem;"><i class="fas fa-cart-shopping" style="color:var(--primary);margin-right:6px;"></i> Masadan Sipariş</div>
                            <div style="font-size: 0.72rem; color: var(--text-dim);">Sepet & Masadan sipariş verme</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="enable_order" value="1" <?php echo $enableOrder ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <!-- 2. Çoklu Dil -->
                    <div class="module-toggle-item">
                        <div>
                            <div style="font-weight: 700; color: #fff; font-size: 0.88rem;"><i class="fas fa-language" style="color:#60a5fa;margin-right:6px;"></i> Çoklu Dil Desteği</div>
                            <div style="font-size: 0.72rem; color: var(--text-dim);">TR, EN, AR, RU, DE dilleri</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="enable_multi_lang" value="1" <?php echo $enableMultiLang ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <!-- 3. Çoklu Para Birimi -->
                    <div class="module-toggle-item">
                        <div>
                            <div style="font-weight: 700; color: #fff; font-size: 0.88rem;"><i class="fas fa-coins" style="color:#fbbf24;margin-right:6px;"></i> Döviz / Para Birimi Çevirici</div>
                            <div style="font-size: 0.72rem; color: var(--text-dim);">EUR, USD, GBP, TRY anlık çevirici</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="enable_currency_converter" value="1" <?php echo $enableCurrencyConverter ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <!-- 4. Şefin Eşleştirmesi (Birlikte İyi Gider) -->
                    <div class="module-toggle-item">
                        <div>
                            <div style="font-weight: 700; color: #fff; font-size: 0.88rem;"><i class="fas fa-wine-glass" style="color:#f43f5e;margin-right:6px;"></i> "Birlikte İyi Gider" (Eşleştirme)</div>
                            <div style="font-size: 0.72rem; color: var(--text-dim);">Ürün detayında akıllı şarap/içecek önerisi</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="enable_pairings" value="1" <?php echo $enablePairings ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <!-- 5. Sunset Happy Hour -->
                    <div class="module-toggle-item">
                        <div>
                            <div style="font-weight: 700; color: #fff; font-size: 0.88rem;"><i class="fas fa-sun" style="color:#f59e0b;margin-right:6px;"></i> Sunset Happy Hour</div>
                            <div style="font-size: 0.72rem; color: var(--text-dim);">Belirli saatlerde otomatik indirim & şerit</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="enable_happy_hour" value="1" <?php echo $enableHappyHour ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <!-- 6. Resort & Şezlong / Oda Servisi -->
                    <div class="module-toggle-item">
                        <div>
                            <div style="font-weight: 700; color: #fff; font-size: 0.88rem;"><i class="fas fa-umbrella-beach" style="color:#38bdf8;margin-right:6px;"></i> Resort & Şezlong / Oda Modu</div>
                            <div style="font-size: 0.72rem; color: var(--text-dim);">Masa, Şezlong, Cabana, Oda, İskele</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="enable_resort_service" value="1" <?php echo $enableResortService ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <!-- 7. Canlı Müzik & Etkinlik Takvimi -->
                    <div class="module-toggle-item">
                        <div>
                            <div style="font-weight: 700; color: #fff; font-size: 0.88rem;"><i class="fas fa-music" style="color:#a855f7;margin-right:6px;"></i> Canlı Müzik & Etkinlikler</div>
                            <div style="font-size: 0.72rem; color: var(--text-dim);">Haftalık konser & sanatçı programı</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="enable_events" value="1" <?php echo $enableEvents ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <!-- 8. Vale, Taksi & Concierge -->
                    <div class="module-toggle-item">
                        <div>
                            <div style="font-weight: 700; color: #fff; font-size: 0.88rem;"><i class="fas fa-car" style="color:#34d399;margin-right:6px;"></i> Vale, Taksi & Concierge</div>
                            <div style="font-size: 0.72rem; color: var(--text-dim);">Garson çağrısına Vale / Taksi butonları</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="enable_concierge" value="1" <?php echo $enableConcierge ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <!-- 9. Şans Çarkı / İkram Kuponu -->
                    <div class="module-toggle-item">
                        <div>
                            <div style="font-weight: 700; color: #fff; font-size: 0.88rem;"><i class="fas fa-gift" style="color:#ec4899;margin-right:6px;"></i> Şans Çarkı (Gamification)</div>
                            <div style="font-size: 0.72rem; color: var(--text-dim);">Günde 1 kez çark çevirme & ikram kodu</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="enable_lucky_wheel" value="1" <?php echo $enableLuckyWheel ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <!-- 10. Mutfak Ekranı (KDS) -->
                    <div class="module-toggle-item">
                        <div>
                            <div style="font-weight: 700; color: #fff; font-size: 0.88rem;"><i class="fas fa-kitchen-set" style="color:#34d399;margin-right:6px;"></i> Mutfak Ekranı (KDS)</div>
                            <div style="font-size: 0.72rem; color: var(--text-dim);">Canlı sipariş takibi & termal fiş</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="enable_kitchen" value="1" <?php echo $enableKitchen ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <!-- 11. Hikayeler -->
                    <div class="module-toggle-item">
                        <div>
                            <div style="font-weight: 700; color: #fff; font-size: 0.88rem;"><i class="fas fa-circle-play" style="color:#f43f5e;margin-right:6px;"></i> Kampanya Hikayeleri</div>
                            <div style="font-size: 0.72rem; color: var(--text-dim);">Instagram tarzı hikaye çubuğu</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="enable_stories" value="1" <?php echo $enableStories ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <!-- 12. Giriş Pop-up -->
                    <div class="module-toggle-item">
                        <div>
                            <div style="font-weight: 700; color: #fff; font-size: 0.88rem;"><i class="fas fa-bullhorn" style="color:#fbbf24;margin-right:6px;"></i> Açılış Pop-Up Duyuru</div>
                            <div style="font-size: 0.72rem; color: var(--text-dim);">Girişte kampanya penceresi</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="enable_popup" value="1" <?php echo $enablePopup ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <!-- 13. Google Yorumları & Puanlama -->
                    <div class="module-toggle-item">
                        <div>
                            <div style="font-weight: 700; color: #fff; font-size: 0.88rem;"><i class="fas fa-star" style="color:#fbbf24;margin-right:6px;"></i> Google Yorum & Puan</div>
                            <div style="font-size: 0.72rem; color: var(--text-dim);">1-5 yıldız ve Harita yönlendirme</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="enable_feedback" value="1" <?php echo $enableFeedback ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <!-- 14. Alerjen & Diyet Filtresi -->
                    <div class="module-toggle-item">
                        <div>
                            <div style="font-weight: 700; color: #fff; font-size: 0.88rem;"><i class="fas fa-shield-halved" style="color:#10b981;margin-right:6px;"></i> Alerjen & Diyet Filtresi</div>
                            <div style="font-size: 0.72rem; color: var(--text-dim);">Glutensiz, Vegan, Kalori filtreleri</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="enable_allergens_filter" value="1" <?php echo $enableAllergensFilter ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <!-- 15. Garson & Hesap Çağrı -->
                    <div class="module-toggle-item">
                        <div>
                            <div style="font-weight: 700; color: #fff; font-size: 0.88rem;"><i class="fas fa-bell" style="color:var(--primary);margin-right:6px;"></i> Garson & Hesap Çağrı</div>
                            <div style="font-size: 0.72rem; color: var(--text-dim);">Masadan garson çağırma modülü</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="enable_waiter_call" value="1" <?php echo $enableWaiterCall ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <!-- 16. Telegram & WhatsApp Canlı Bildirim -->
                    <div class="module-toggle-item">
                        <div>
                            <div style="font-weight: 700; color: #fff; font-size: 0.88rem;"><i class="fab fa-telegram" style="color:#229ed9;margin-right:6px;"></i> Telegram / WhatsApp Bot</div>
                            <div style="font-size: 0.72rem; color: var(--text-dim);">Sipariş ve çağrılarda anlık bildirim</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="enable_telegram_notify" value="1" <?php echo $enableTelegramNotify ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                    </div>

                </div>
            </div>
        </div>

        <!-- 2. DÖVİZ KURLARI & PARA BİRİMİ AYARLARI -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-money-bill-transfer" style="color:var(--primary);"></i> Döviz Kurları (Para Birimi Çevirici)</h3>
                <span style="font-size:0.75rem; color:var(--text-muted);">1 TL Karşılığı Kur Değerleri</span>
            </div>
            <div class="card-body">
                <div class="form-3col-grid">
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-euro-sign" style="color:#60a5fa;"></i> Euro (EUR) Kuru (TL Karşılığı)</label>
                        <input type="number" step="0.01" name="currency_eur_rate" value="<?php echo htmlspecialchars($currencyEurRate); ?>" class="form-control" placeholder="38.50">
                    </div>
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-dollar-sign" style="color:#34d399;"></i> Dolar (USD) Kuru (TL Karşılığı)</label>
                        <input type="number" step="0.01" name="currency_usd_rate" value="<?php echo htmlspecialchars($currencyUsdRate); ?>" class="form-control" placeholder="35.00">
                    </div>
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-sterling-sign" style="color:#f43f5e;"></i> Sterlin (GBP) Kuru (TL Karşılığı)</label>
                        <input type="number" step="0.01" name="currency_gbp_rate" value="<?php echo htmlspecialchars($currencyGbpRate); ?>" class="form-control" placeholder="46.00">
                    </div>
                </div>
                <small style="color:var(--text-dim); display:block; margin-top:4px;">
                    * Müşteri menüden EUR / USD / GBP seçtiğinde tüm ürün ve seçenek fiyatları bu kurlara bölünerek anlık olarak döviz cinsinden gösterilir.
                </small>
            </div>
        </div>

        <!-- 3. SUNSET HAPPY HOUR AYARLARI -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-sun" style="color:#f59e0b;"></i> Sunset Happy Hour & Özel İndirim Ayarları</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Happy Hour Başlığı / Kampanya Duyurusu</label>
                    <input type="text" name="happy_hour_title" value="<?php echo htmlspecialchars($happyHourTitle); ?>" class="form-control">
                </div>
                <div class="form-3col-grid">
                    <div class="form-group">
                        <label class="form-label">Başlangıç Saati</label>
                        <input type="time" name="happy_hour_start" value="<?php echo htmlspecialchars($happyHourStart); ?>" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Bitiş Saati</label>
                        <input type="time" name="happy_hour_end" value="<?php echo htmlspecialchars($happyHourEnd); ?>" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">İndirim Oranı (%)</label>
                        <input type="number" name="happy_hour_discount" value="<?php echo htmlspecialchars($happyHourDiscount); ?>" class="form-control" placeholder="15">
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. TELEGRAM VE WHATSAPP BİLDİRİM BOTU AYARLARI -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fab fa-telegram" style="color:#229ed9;"></i> Telegram & WhatsApp Canlı Bildirim Entegrasyonu</h3>
            </div>
            <div class="card-body">
                <div class="form-2col-grid">
                    <div class="form-group">
                        <label class="form-label">Telegram Bot Token</label>
                        <input type="text" name="telegram_bot_token" value="<?php echo htmlspecialchars($telegramBotToken); ?>" placeholder="123456789:ABCdefGhI..." class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Telegram Chat / Grup ID</label>
                        <input type="text" name="telegram_chat_id" value="<?php echo htmlspecialchars($telegramChatId); ?>" placeholder="-100123456789 veya chat_id" class="form-control">
                    </div>
                </div>
                <div class="form-2col-grid">
                    <div class="form-group">
                        <label class="form-label"><i class="fab fa-whatsapp" style="color:#25d366;"></i> WhatsApp Bildirim Telefonu</label>
                        <input type="text" name="whatsapp_phone" value="<?php echo htmlspecialchars($whatsappPhone); ?>" placeholder="+905xxxxxxxxx" class="form-control">
                    </div>
                    <div class="form-group" style="display:flex; align-items:flex-end;">
                        <label class="switch-container" style="display:flex; align-items:center; gap:10px; cursor:pointer; margin-bottom:10px;">
                            <input type="checkbox" name="enable_whatsapp_notify" value="1" <?php echo $enableWhatsappNotify ? 'checked' : ''; ?>>
                            <span style="font-size:0.85rem; color:#fff; font-weight:700;">WhatsApp Butonunu Aktif Et</span>
                        </label>
                    </div>
                </div>
                <small style="color:var(--text-dim); display:block; margin-top:2px;">
                    * Telegram Bot token ve Chat ID tanımlandığında masadan gelen her yeni garson çağrısı ve sipariş anında Telegram grubunuza bildirim olarak düşer.
                </small>
            </div>
        </div>

        <!-- 5. ŞANS ÇARKI / İKRAM KUPONU SEÇENEKLERİ -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-gift" style="color:#ec4899;"></i> Şans Çarkı İkram Seçenekleri</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Çark Dilimleri & Ödüller (Virgülle ayırarak yazın)</label>
                    <textarea name="wheel_rewards" rows="2" class="form-control" placeholder="Günün Tatlısı İkramı,%10 İndirim,Türk Kahvesi İkramı..."><?php echo htmlspecialchars($wheelRewards); ?></textarea>
                    <small style="color:var(--text-dim); display:block; margin-top:4px;">
                        * Çarkta görüntülenecek hediye ve ikramları virgülle ayırarak giriniz.
                    </small>
                </div>
            </div>
        </div>

        <!-- 6. TEMA MODU SEÇİMİ (DARK / LIGHT) -->
        <div class="card" style="border: 2px solid var(--border-focus);">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-circle-half-stroke" style="color:var(--primary);"></i> Menü Tema Modu</h3>
                <span style="font-size: 0.75rem; background: var(--primary); color: #000; padding: 3px 8px; border-radius: 4px; font-weight: 700;">Admin Belirler</span>
            </div>
            <div class="card-body">
                <div class="theme-mode-grid">
                    <!-- Light Mode -->
                    <label style="cursor: pointer; display: block;">
                        <input type="radio" name="theme_mode" value="light" <?php echo $themeMode === 'light' ? 'checked' : ''; ?> style="display: none;" onchange="updateThemeCards()">
                        <div id="cardThemeLight" style="border: 2px solid <?php echo $themeMode === 'light' ? 'var(--primary)' : 'var(--border)'; ?>; background: #ffffff; border-radius: var(--radius-md); padding: 16px; text-align: center; transition: all 0.2s ease;">
                            <div style="font-size: 2rem; margin-bottom: 8px;">☀️</div>
                            <div style="font-weight: 800; color: #0f172a; font-size: 0.95rem;">Ferah Açık Tema (Light)</div>
                            <div style="font-size: 0.75rem; color: #64748b; margin-top: 4px;">Akdeniz & Ege Rivierası parşömen görünüm</div>
                        </div>
                    </label>

                    <!-- Dark Mode -->
                    <label style="cursor: pointer; display: block;">
                        <input type="radio" name="theme_mode" value="dark" <?php echo $themeMode === 'dark' ? 'checked' : ''; ?> style="display: none;" onchange="updateThemeCards()">
                        <div id="cardThemeDark" style="border: 2px solid <?php echo $themeMode === 'dark' ? 'var(--primary)' : 'var(--border)'; ?>; background: #0c0f14; border-radius: var(--radius-md); padding: 16px; text-align: center; transition: all 0.2s ease;">
                            <div style="font-size: 2rem; margin-bottom: 8px;">🌙</div>
                            <div style="font-weight: 800; color: #fff; font-size: 0.95rem;">Lüks Koyu Tema (Dark)</div>
                            <div style="font-size: 0.75rem; color: #94a3b8; margin-top: 4px;">Gece mavisi & koyu zemin üzerinde lüks altın</div>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <!-- 7. DARK VE LIGHT LOGOLAR -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-images" style="color:var(--primary);"></i> Tema Logoları (Dark & Light)</h3>
            </div>
            <div class="card-body">
                <div class="logo-upload-grid">
                    
                    <!-- Light Logo -->
                    <div style="background: var(--bg-input); padding: 16px; border-radius: var(--radius-md); border: 1px solid var(--border);">
                        <label class="form-label" style="color: #38bdf8;"><i class="fas fa-sun"></i> Açık (Light) Tema Logosu</label>
                        <input type="file" name="logo_light_file" class="form-control image-upload-input" data-preview="logoLightPreview" accept="image/*" style="margin-bottom: 8px;">
                        <input type="text" name="logo_light_url" value="<?php echo htmlspecialchars($logoLightUrl); ?>" placeholder="veya Logo Dosya Yolu / URL" class="form-control" style="margin-bottom: 12px;">

                        <div style="background: #ffffff; border: 2px dashed #cbd5e1; border-radius: var(--radius-sm); padding: 14px; text-align: center; min-height: 85px; display: flex; align-items: center; justify-content: center;">
                            <?php if (!empty($logoLightUrl)): ?>
                                <img id="logoLightPreview" src="../<?php echo htmlspecialchars($logoLightUrl); ?>" alt="Light Logo" style="max-height: 65px; max-width: 100%; object-fit: contain;" onerror="this.src='../assets/images/maremonte_logo.svg'">
                            <?php else: ?>
                                <img id="logoLightPreview" src="../assets/images/maremonte_logo.svg" alt="Light Logo" style="max-height: 65px; max-width: 100%; object-fit: contain;">
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Dark Logo -->
                    <div style="background: var(--bg-input); padding: 16px; border-radius: var(--radius-md); border: 1px solid var(--border);">
                        <label class="form-label" style="color: #fbbf24;"><i class="fas fa-moon"></i> Koyu (Dark) Tema Logosu</label>
                        <input type="file" name="logo_dark_file" class="form-control image-upload-input" data-preview="logoDarkPreview" accept="image/*" style="margin-bottom: 8px;">
                        <input type="text" name="logo_dark_url" value="<?php echo htmlspecialchars($logoDarkUrl); ?>" placeholder="veya Dark Logo Dosya Yolu / URL" class="form-control" style="margin-bottom: 12px;">

                        <div style="background: #0c0f14; border: 2px dashed rgba(255,255,255,0.15); border-radius: var(--radius-sm); padding: 14px; text-align: center; min-height: 85px; display: flex; align-items: center; justify-content: center;">
                            <?php if (!empty($logoDarkUrl)): ?>
                                <img id="logoDarkPreview" src="../<?php echo htmlspecialchars($logoDarkUrl); ?>" alt="Dark Logo" style="max-height: 65px; max-width: 100%; object-fit: contain;" onerror="this.src='../assets/images/maremonte_logo.svg'">
                            <?php else: ?>
                                <img id="logoDarkPreview" src="../assets/images/maremonte_logo.svg" alt="Dark Logo" style="max-height: 65px; max-width: 100%; object-fit: contain;">
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- 8. MENÜ KAPAK GÖRSELİ (BANNER) -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-panorama" style="color:var(--primary);"></i> Menü Üst Kapak Görseli (Banner)</h3>
            </div>
            <div class="card-body">
                <div class="form-2col-grid" style="align-items: center;">
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

        <!-- 9. RESTORAN BİLGİLERİ VE GOOGLE HARİTA -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-store" style="color:var(--primary);"></i> Restoran Bilgileri & Google Harita Linki</h3>
            </div>
            <div class="card-body">
                <div class="form-2col-grid">
                    <div class="form-group">
                        <label class="form-label">Restoran / İşletme Adı *</label>
                        <input type="text" name="restaurant_name" value="<?php echo htmlspecialchars($restaurantName); ?>" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Slogan / Açıklama</label>
                        <input type="text" name="restaurant_slogan" value="<?php echo htmlspecialchars($restaurantSlogan); ?>" class="form-control">
                    </div>
                </div>

                <div class="form-1-2col-grid">
                    <div class="form-group">
                        <label class="form-label">Varsayılan Para Birimi</label>
                        <input type="text" name="currency" value="<?php echo htmlspecialchars($currency); ?>" class="form-control">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Vurgu / Buton Rengi</label>
                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                            <input type="color" name="theme_color" id="themeColorPicker" value="<?php echo htmlspecialchars($themeColor); ?>" style="width: 44px; height: 40px; padding: 2px; border-radius: var(--radius-sm); border: 1px solid var(--border); background: var(--bg-input); cursor: pointer;">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="setThemeColor('#C5A059')">Riviera Gold</button>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="setThemeColor('#1A4B4B')">Kazdağları Yeşil</button>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="setThemeColor('#d97706')">Sıcak Amber</button>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="setThemeColor('#2563eb')">Gece Mavisi</button>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label"><i class="fab fa-google" style="color:#ea4335;margin-right:4px;"></i> Google Haritalar / Yorum Linki (5 Yıldız Yönlendirmesi)</label>
                    <input type="url" name="google_maps_url" value="<?php echo htmlspecialchars($googleMapsUrl); ?>" placeholder="https://maps.google.com/?q=..." class="form-control">
                </div>
            </div>
        </div>

        <!-- 10. WI-FI & İLETİŞİM -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-wifi" style="color:var(--primary);"></i> Wi-Fi & İletişim</h3>
            </div>
            <div class="card-body">
                <div class="form-2col-grid">
                    <div class="form-group">
                        <label class="form-label">Wi-Fi Ağ Adı (SSID)</label>
                        <input type="text" name="wifi_name" value="<?php echo htmlspecialchars($wifiName); ?>" class="form-control">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Wi-Fi Şifresi</label>
                        <input type="text" name="wifi_pass" value="<?php echo htmlspecialchars($wifiPass); ?>" class="form-control">
                    </div>
                </div>

                <div class="form-2col-grid">
                    <div class="form-group">
                        <label class="form-label">Telefon</label>
                        <input type="text" name="phone" value="<?php echo htmlspecialchars($phone); ?>" class="form-control">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Instagram Kullanıcı Adı</label>
                        <input type="text" name="instagram" value="<?php echo htmlspecialchars($instagram); ?>" placeholder="Örn: hotelmaremonte" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Restoran Adresi</label>
                    <textarea name="address" rows="2" class="form-control"><?php echo htmlspecialchars($address); ?></textarea>
                </div>
            </div>
            <div class="card-header" style="justify-content: flex-end;">
                <button type="submit" class="btn btn-primary" style="padding: 14px 32px; font-size: 1.05rem; font-weight: 800; width: 100%; max-width: 320px; justify-content: center;">
                    <i class="fas fa-save"></i> Tüm Ayarları Kaydet
                </button>
            </div>
        </div>
    </form>

    <!-- ŞİFRE DEĞİŞTİRME & SİSTEM ÖZETİ -->
    <div style="width: 100%;">
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

                    <button type="submit" class="btn btn-secondary" style="width: 100%; justify-content: center;">
                        <i class="fas fa-lock"></i> Şifreyi Güncelle
                    </button>
                </div>
            </form>
        </div>

        <div class="card" style="background: rgba(197, 160, 89, 0.08); border-color: rgba(197, 160, 89, 0.35);">
            <div class="card-body">
                <h4 style="font-size: 0.95rem; font-weight: 800; color: var(--primary-light); margin-bottom: 10px;">
                    <i class="fas fa-circle-check"></i> Aktif Modül Durumları
                </h4>
                <p style="font-size: 0.82rem; color: var(--text-muted); line-height: 1.9;">
                    • Masadan Sipariş: <strong style="color:#fff;"><?php echo $enableOrder ? 'Aktif ✓' : 'Kapalı ✗'; ?></strong><br>
                    • Çoklu Para Birimi: <strong style="color:#fff;"><?php echo $enableCurrencyConverter ? 'Aktif ✓' : 'Kapalı ✗'; ?></strong><br>
                    • Sunset Happy Hour: <strong style="color:#fff;"><?php echo $enableHappyHour ? 'Aktif ✓' : 'Kapalı ✗'; ?></strong><br>
                    • Şefin Eşleştirmesi: <strong style="color:#fff;"><?php echo $enablePairings ? 'Aktif ✓' : 'Kapalı ✗'; ?></strong><br>
                    • Resort & Şezlong Servisi: <strong style="color:#fff;"><?php echo $enableResortService ? 'Aktif ✓' : 'Kapalı ✗'; ?></strong><br>
                    • Canlı Müzik & Etkinlikler: <strong style="color:#fff;"><?php echo $enableEvents ? 'Aktif ✓' : 'Kapalı ✗'; ?></strong><br>
                    • Vale, Taksi & Concierge: <strong style="color:#fff;"><?php echo $enableConcierge ? 'Aktif ✓' : 'Kapalı ✗'; ?></strong><br>
                    • Şans Çarkı / İkram: <strong style="color:#fff;"><?php echo $enableLuckyWheel ? 'Aktif ✓' : 'Kapalı ✗'; ?></strong><br>
                    • Telegram Canlı Botu: <strong style="color:#fff;"><?php echo $enableTelegramNotify ? 'Aktif ✓' : 'Kapalı ✗'; ?></strong><br>
                    • Mutfak Ekranı (KDS): <strong style="color:#fff;"><?php echo $enableKitchen ? 'Aktif ✓' : 'Kapalı ✗'; ?></strong><br>
                    • Aktif Menü Teması: <strong style="color:var(--primary-light); text-transform:uppercase;"><?php echo $themeMode; ?></strong>
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
