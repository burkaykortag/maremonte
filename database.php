<?php
/**
 * Veritabanı Bağlantısı ve Otomatik Tablo / Örnek Veri Kurulumu
 */

require_once __DIR__ . '/config.php';

try {
    $pdo = null;
    if (DB_DRIVER === 'sqlite') {
        $dbDir = dirname(DB_SQLITE_PATH);
        if (!is_dir($dbDir)) {
            @mkdir($dbDir, 0777, true);
        }
        $pdo = new PDO('sqlite:' . DB_SQLITE_PATH);
    } else {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET,
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_TIMEOUT => 3
                ]
            );
        } catch (PDOException $mySqlErr) {
            // MySQL bağlantısı kurulamadıysa SQLite yedeğine güvenli geçiş yap (Sistem Asla Çökmez / 500 Vermez)
            if (file_exists(DB_SQLITE_PATH)) {
                $pdo = new PDO('sqlite:' . DB_SQLITE_PATH);
            } else {
                $dbDir = dirname(DB_SQLITE_PATH);
                if (!is_dir($dbDir)) {
                    @mkdir($dbDir, 0777, true);
                }
                $pdo = new PDO('sqlite:' . DB_SQLITE_PATH);
            }
        }
    }

    if ($pdo) {
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

} catch (Exception $e) {
    try {
        $pdo = new PDO('sqlite:' . DB_SQLITE_PATH);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    } catch (Exception $eFinal) {
        if (basename($_SERVER['PHP_SELF'] ?? '') !== 'install.php') {
            die("Veritabanı başlatılamadı: " . $eFinal->getMessage());
        }
    }
}

/**
 * Tabloları Oluştur
 */
function initDatabase($pdo) {
    $isSqlite = (DB_DRIVER === 'sqlite');
    $autoInc = $isSqlite ? 'INTEGER PRIMARY KEY AUTOINCREMENT' : 'INT AUTO_INCREMENT PRIMARY KEY';
    $textType = 'TEXT';
    $dateTimeDefault = 'DATETIME DEFAULT CURRENT_TIMESTAMP';

    // 1. Ayarlar Tablosu
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        id $autoInc,
        setting_key VARCHAR(100) UNIQUE,
        setting_value $textType,
        updated_at $dateTimeDefault
    )");

    // 2. Yöneticiler Tablosu
    $pdo->exec("CREATE TABLE IF NOT EXISTS admins (
        id $autoInc,
        username VARCHAR(100) UNIQUE,
        password_hash VARCHAR(255),
        name VARCHAR(150),
        role VARCHAR(50) DEFAULT 'admin',
        created_at $dateTimeDefault
    )");

    // 3. Kategoriler Tablosu
    $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
        id $autoInc,
        name VARCHAR(150) NOT NULL,
        slug VARCHAR(150),
        icon VARCHAR(100) DEFAULT 'utensils',
        image VARCHAR(255) DEFAULT '',
        name_en VARCHAR(150) DEFAULT '',
        name_ar VARCHAR(150) DEFAULT '',
        name_ru VARCHAR(150) DEFAULT '',
        name_de VARCHAR(150) DEFAULT '',
        sort_order INT DEFAULT 0,
        is_active INT DEFAULT 1,
        created_at $dateTimeDefault
    )");

    // 4. Ürünler Tablosu
    $pdo->exec("CREATE TABLE IF NOT EXISTS products (
        id $autoInc,
        category_id INT NOT NULL,
        name VARCHAR(200) NOT NULL,
        description $textType,
        name_en VARCHAR(200) DEFAULT '',
        desc_en $textType,
        name_ar VARCHAR(200) DEFAULT '',
        desc_ar $textType,
        name_ru VARCHAR(200) DEFAULT '',
        desc_ru $textType,
        name_de VARCHAR(200) DEFAULT '',
        desc_de $textType,
        price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        old_price DECIMAL(10,2) DEFAULT NULL,
        image VARCHAR(255) DEFAULT '',
        badge VARCHAR(50) DEFAULT '',
        calories INT DEFAULT 0,
        prep_time INT DEFAULT 15,
        allergens VARCHAR(255) DEFAULT '',
        is_available INT DEFAULT 1,
        is_featured INT DEFAULT 0,
        sort_order INT DEFAULT 0,
        view_count INT DEFAULT 0,
        created_at $dateTimeDefault
    )");

    // 5. Masalar Tablosu
    $pdo->exec("CREATE TABLE IF NOT EXISTS tables (
        id $autoInc,
        table_number VARCHAR(50) NOT NULL UNIQUE,
        table_name VARCHAR(100) NOT NULL,
        token VARCHAR(100) UNIQUE,
        created_at $dateTimeDefault
    )");

    // 6. Garson & Hesap Çağrıları Tablosu
    $pdo->exec("CREATE TABLE IF NOT EXISTS waiter_calls (
        id $autoInc,
        table_number VARCHAR(50) NOT NULL,
        call_type VARCHAR(50) DEFAULT 'waiter',
        note $textType,
        status VARCHAR(30) DEFAULT 'pending',
        created_at $dateTimeDefault
    )");

    // 7. Siparişler Tablosu (Orders)
    $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
        id $autoInc,
        table_number VARCHAR(50) NOT NULL,
        total_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        status VARCHAR(30) DEFAULT 'pending',
        customer_note $textType,
        created_at $dateTimeDefault
    )");

    // 8. Sipariş Kalemleri Tablosu (Order Items)
    $pdo->exec("CREATE TABLE IF NOT EXISTS order_items (
        id $autoInc,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        product_name VARCHAR(200) NOT NULL,
        quantity INT DEFAULT 1,
        price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        options_json $textType,
        created_at $dateTimeDefault
    )");

    // 9. Ürün Seçenekleri & Varyasyonları (Product Add-ons / Modifiers)
    $pdo->exec("CREATE TABLE IF NOT EXISTS product_options (
        id $autoInc,
        product_id INT NOT NULL,
        group_name VARCHAR(100) NOT NULL,
        option_name VARCHAR(100) NOT NULL,
        extra_price DECIMAL(10,2) DEFAULT 0.00,
        is_required INT DEFAULT 0
    )");

    // 10. Hikayeler & Kampanyalar Tablosu (Stories)
    $pdo->exec("CREATE TABLE IF NOT EXISTS stories (
        id $autoInc,
        title VARCHAR(150) NOT NULL,
        image VARCHAR(255) NOT NULL,
        link VARCHAR(255) DEFAULT '',
        sort_order INT DEFAULT 0,
        is_active INT DEFAULT 1,
        created_at $dateTimeDefault
    )");

    // 11. Müşteri Değerlendirmeleri & Yorumları (Feedback)
    $pdo->exec("CREATE TABLE IF NOT EXISTS feedback (
        id $autoInc,
        table_number VARCHAR(50) DEFAULT '',
        rating INT NOT NULL DEFAULT 5,
        name VARCHAR(100) DEFAULT '',
        comment $textType,
        is_read INT DEFAULT 0,
        created_at $dateTimeDefault
    )");

    // 12. Canlı Müzik & Etkinlikler Tablosu (Events)
    $pdo->exec("CREATE TABLE IF NOT EXISTS events (
        id $autoInc,
        title VARCHAR(200) NOT NULL,
        performer VARCHAR(150) DEFAULT '',
        event_date DATE,
        event_time VARCHAR(20) DEFAULT '20:30',
        description $textType,
        image VARCHAR(255) DEFAULT '',
        is_active INT DEFAULT 1,
        sort_order INT DEFAULT 0,
        created_at $dateTimeDefault
    )");

    // Migration: Ürünlere pairing_suggestion kolonu ekle
    try {
        $pdo->exec("ALTER TABLE products ADD COLUMN pairing_suggestion $textType DEFAULT ''");
    } catch (Exception $e) {
        // Kolon zaten mevcut
    }

    // Modül ayarları ve demo verileri kontrol et
    seedInitialData($pdo);
}

/**
 * İlk Kurulumda Zengin Demo Verilerini Ekle
 */
function seedInitialData($pdo) {
    // 1. Varsayılan Admin Kullanıcısı
    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM admins");
    if ($stmt->fetch()['cnt'] == 0) {
        $stmtAdmin = $pdo->prepare("INSERT INTO admins (username, password_hash, name, role) VALUES (?, ?, ?, ?)");
        $stmtAdmin->execute(['admin', password_hash('admin123', PASSWORD_DEFAULT), 'Yönetici', 'superadmin']);
    }

    // 2. Varsayılan Modül ve Restoran Ayarları
    $defaultSettings = [
        'restaurant_name' => 'HOTEL MARE & MONTE BISTRO',
        'restaurant_slogan' => 'Altınoluk (Est. 1985)',
        'currency' => '₺',
        'theme_color' => '#C5A059',
        'theme_mode' => 'light',
        'logo_dark_url' => 'assets/images/maremonte_logo.svg',
        'logo_light_url' => 'assets/images/maremonte_logo.svg',
        'banner_url' => 'https://images.unsplash.com/photo-1544161515-4ab6ce6db874?w=1200&q=80',
        'wifi_name' => 'MareMonte_Guest',
        'wifi_pass' => 'MareMonte1985',
        'phone' => '+90 (266) 396 00 00',
        'instagram' => 'hotelmaremonte',
        'address' => 'İskele Mah. Sahil Cad. No:14, Altınoluk / Balıkesir',
        
        // MODÜL AÇ/KAPA (AÇIK = 1, KAPALI = 0)
        'enable_hero_banner' => '1',      // Restoran Tanıtım & Karşılama Kartı (Hero Banner)
        'enable_order' => '0',            // Masadan Canlı Sipariş Pasif (Garson Çağrısı Aktif)
        'enable_multi_lang' => '1',       // Çoklu Dil Desteği (TR, EN, AR, RU, DE)
        'enable_kitchen' => '1',          // Canlı Mutfak & Bar Ekranı (KDS)
        'enable_stories' => '1',          // Instagram Tarzı Kampanya Hikayeleri
        'enable_popup' => '1',            // Açılış Kampanya Pop-Up
        'enable_feedback' => '1',         // Google Yorumları & Puanlama
        'enable_allergens_filter' => '1', // Gelişmiş Diyet & Alerjen Filtresi
        'enable_waiter_call' => '1',      // Garson Çağırma & Hesap İsteme
        
        // YENİ MODÜLLER (HEPSİ AÇIK/KAPALI YÖNETİLEBİLİR)
        'enable_currency_converter' => '1', // Çoklu Para Birimi (EUR / USD / GBP / TRY)
        'currency_eur_rate' => '38.50',
        'currency_usd_rate' => '35.00',
        'currency_gbp_rate' => '46.00',
        
        'enable_pairings' => '1',           // Şefin Akıllı Eşleştirme & Birlikte İyi Gider
        
        'enable_happy_hour' => '1',         // Sunset Happy Hour & Özel İndirim
        'happy_hour_title' => '🌅 Gün Batımı Happy Hour (Tüm Kokteyllerde %15 İndirim)',
        'happy_hour_start' => '17:00',
        'happy_hour_end' => '19:30',
        'happy_hour_discount' => '15',
        
        'enable_resort_service' => '1',     // Plaj, Şezlong, Cabana & Oda Servisi
        'enable_events' => '0',             // Canlı Müzik & Haftalık Etkinlik Takvimi (Kapalı)
        'enable_concierge' => '1',          // Vale, Taksi & Resepsiyon Servisi
        
        'enable_telegram_notify' => '0',    // Telegram Bot Canlı Bildirimi
        'telegram_bot_token' => '',
        'telegram_chat_id' => '',
        
        'enable_whatsapp_notify' => '0',    // WhatsApp Bildirimi
        'whatsapp_phone' => '+902663960000',
        
        'enable_lucky_wheel' => '1',        // Şans Çarkı / İkram Kuponu
        'wheel_rewards' => 'Günün Tatlısı İkramı,%10 Hesap İndirimi,Türk Kahvesi İkramı,Şefin Özel Kokteyli,%15 İndirim,Teşekkürler',

        // POP-UP KAMPANYA BİLGİLERİ
        'popup_title' => '🌊 Hotel Mare & Monte Bistro Hoş Geldiniz!',
        'popup_desc' => '1985\'ten beri Altınoluk sahilinde eşsiz lezzetler. Günlük taze deniz ürünlerimiz ve şefin spesiyallerini keşfedin!',
        'popup_image' => 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?w=800&q=80',
        'popup_btn_text' => 'Deniz Ürünlerini İncele',
        'popup_btn_link' => '#cat-8',
        
        // GOOGLE HARİTA / YORUM LİNKİ
        'google_maps_url' => 'https://maps.google.com/?q=Hotel+Mare+Monte+Altinoluk'
    ];

    $isSqlite = (DB_DRIVER === 'sqlite');
    foreach ($defaultSettings as $k => $v) {
        if ($isSqlite) {
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON CONFLICT(setting_key) DO NOTHING");
        } else {
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = setting_value");
        }
        $stmt->execute([$k, $v]);
    }

    // 3. Masalar
    $stmtTCheck = $pdo->query("SELECT COUNT(*) as cnt FROM tables");
    if ($stmtTCheck->fetch()['cnt'] == 0) {
        $tables = [
            ['1', 'Masa 1'], ['2', 'Masa 2'], ['3', 'Masa 3'], ['4', 'Masa 4'], ['5', 'Masa 5'],
            ['6', 'Masa 6'], ['7', 'Masa 7'], ['8', 'Masa 8'], ['9', 'Masa 9'], ['10', 'Masa 10'],
            ['B1', 'Bahçe 1'], ['B2', 'Bahçe 2'], ['B3', 'Bahçe 3'], ['B4', 'Bahçe 4'],
            ['I1', 'İskele 1'], ['I2', 'İskele 2'], ['I3', 'İskele 3'], ['I4', 'İskele 4'],
            ['T1', 'Teras 1'], ['T2', 'Teras 2']
        ];
        $stmtTable = $pdo->prepare("INSERT INTO tables (table_number, table_name, token) VALUES (?, ?, ?)");
        foreach ($tables as $t) {
            $stmtTable->execute([$t[0], $t[1], bin2hex(random_bytes(8))]);
        }
    }

    // 4. Hikayeler (Stories)
    $stmtStoryCheck = $pdo->query("SELECT COUNT(*) as cnt FROM stories");
    if ($stmtStoryCheck->fetch()['cnt'] == 0) {
        $demoStories = [
            ['title' => 'Taze Balıklar', 'image' => 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?w=600&q=80', 'link' => '#cat-8', 'sort_order' => 1],
            ['title' => 'Bonfile & Lokum', 'image' => 'https://images.unsplash.com/photo-1558030006-450675393462?w=600&q=80', 'link' => '#cat-7', 'sort_order' => 2],
            ['title' => 'Taş Fırın Pizza', 'image' => 'https://images.unsplash.com/photo-1604382355076-af4b0eb60143?w=600&q=80', 'link' => '#cat-4', 'sort_order' => 3],
            ['title' => 'Mare Burger', 'image' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=600&q=80', 'link' => '#cat-5', 'sort_order' => 4],
            ['title' => 'Atıştırmalıklar', 'image' => 'https://images.unsplash.com/photo-1541592106381-b31e9677c0e5?w=600&q=80', 'link' => '#cat-2', 'sort_order' => 5],
            ['title' => 'Kokteyller', 'image' => 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?w=600&q=80', 'link' => '#cat-11', 'sort_order' => 6],
            ['title' => 'Tatlılar & Kahve', 'image' => 'https://images.unsplash.com/photo-1579372786545-d24232daf58c?w=600&q=80', 'link' => '#cat-10', 'sort_order' => 7]
        ];
        $stmtStory = $pdo->prepare("INSERT INTO stories (title, image, link, sort_order) VALUES (?, ?, ?, ?)");
        foreach ($demoStories as $s) {
            $stmtStory->execute([$s['title'], $s['image'], $s['link'], $s['sort_order']]);
        }
    }

    // 5. Canlı Müzik & Etkinlikler (Events)
    $stmtEventCheck = $pdo->query("SELECT COUNT(*) as cnt FROM events");
    if ($stmtEventCheck->fetch()['cnt'] == 0) {
        $demoEvents = [
            [
                'title' => 'Gün Batımı Akustik Caz & Saksafon',
                'performer' => 'Tuna Trio & Zeynep (Saksafon)',
                'event_date' => date('Y-m-d'),
                'event_time' => '20:30',
                'description' => 'Altınoluk Körfezi gün batımında şarap ve özel kokteyller eşliğinde canlı caz ziyafeti.',
                'image' => 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?w=800&q=80',
                'sort_order' => 1,
                'is_active' => 1
            ],
            [
                'title' => 'Ege & Akdeniz Şarap ve Peynir Tadımı',
                'performer' => 'Mare & Monte Sommelier Atölyesi',
                'event_date' => date('Y-m-d', strtotime('+2 days')),
                'event_time' => '19:00',
                'description' => 'Kaz Dağları eteklerinden yerel peynirler ve seçkin şarap eşleştirmeleri.',
                'image' => 'https://images.unsplash.com/photo-1510812431401-41d2bd2722f3?w=800&q=80',
                'sort_order' => 2,
                'is_active' => 1
            ],
            [
                'title' => 'Gitar & Akustik Riviera Melodileri',
                'performer' => 'Caner Arslan (Solo Akustik)',
                'event_date' => date('Y-m-d', strtotime('+4 days')),
                'event_time' => '21:00',
                'description' => 'Deniz kenarında nostaljik Akdeniz şarkıları ve İtalyan ezgileri.',
                'image' => 'https://images.unsplash.com/photo-1465847899084-d164df4dedc6?w=800&q=80',
                'sort_order' => 3,
                'is_active' => 1
            ]
        ];

        $stmtEvent = $pdo->prepare("INSERT INTO events (title, performer, event_date, event_time, description, image, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($demoEvents as $ev) {
            $stmtEvent->execute([$ev['title'], $ev['performer'], $ev['event_date'], $ev['event_time'], $ev['description'], $ev['image'], $ev['sort_order'], $ev['is_active']]);
        }
    }

    // 5. Kategoriler ve Ürünler
    $stmtCatCheck = $pdo->query("SELECT COUNT(*) as cnt FROM categories");
    if ($stmtCatCheck->fetch()['cnt'] == 0) {
        $demoCategories = [
            [
                'name' => 'Başlangıçlar',
                'name_en' => 'Starters & Soups',
                'name_ar' => 'المقبلات والشوربات',
                'name_ru' => 'Закуски и супы',
                'name_de' => 'Vorspeisen & Suppen',
                'slug' => 'baslangiclar',
                'icon' => 'utensils',
                'image' => 'https://images.unsplash.com/photo-1547592166-23ac45744acd?w=600&q=80',
                'sort_order' => 1,
                'products' => [
                    [
                        'name' => 'Günün Çorbası',
                        'name_en' => 'Soup of the Day',
                        'name_ar' => 'شوربة اليوم',
                        'name_ru' => 'Суп дня',
                        'name_de' => 'Tagessuppe',
                        'desc' => 'Şefimizin günlük olarak taze malzemelerle hazırladığı leziz çorba.',
                        'desc_en' => 'Freshly prepared delicious hot soup of the day by our chef.',
                        'price' => 120.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1547592166-23ac45744acd?w=800&q=80',
                        'badge' => 'Şefin Seçimi',
                        'calories' => 180,
                        'prep_time' => 5,
                        'allergens' => 'Gluten',
                        'is_featured' => 1
                    ],
                    [
                        'name' => 'Ezine Peynir Dilimi',
                        'name_en' => 'Ezine Cheese Slice',
                        'name_ar' => 'شريحة جبن إزيني',
                        'name_ru' => 'Сыр Эзине',
                        'name_de' => 'Ezine Käsescheibe',
                        'desc' => 'Meşhur Ezine peyniri, taze domates ve çıtır salatalık ile.',
                        'desc_en' => 'Famous local Ezine cheese served with fresh tomatoes and cucumbers.',
                        'price' => 150.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1589881133595-a3c085cb731d?w=800&q=80',
                        'badge' => 'Yöresel',
                        'calories' => 210,
                        'prep_time' => 5,
                        'allergens' => 'Laktoz',
                        'is_featured' => 0
                    ],
                    [
                        'name' => 'Meze',
                        'name_en' => 'Traditional Meze',
                        'name_ar' => 'مقبلات باردة',
                        'name_ru' => 'Традиционные мезе',
                        'name_de' => 'Traditionelle Meze',
                        'desc' => 'Günün seçkisiyle taze hazırlanan nefis mevsim mezeleri.',
                        'desc_en' => 'Fresh selection of daily traditional cold mezes.',
                        'price' => 200.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1541592106381-b31e9677c0e5?w=800&q=80',
                        'badge' => 'Günlük Taze',
                        'calories' => 190,
                        'prep_time' => 5,
                        'allergens' => '',
                        'is_featured' => 0
                    ],
                    [
                        'name' => 'Yerli Peynir Tabağı',
                        'name_en' => 'Local Cheese Platter',
                        'name_ar' => 'طبق الأجبان المحلية',
                        'name_ru' => 'Тарелка местных сыров',
                        'name_de' => 'Lokale Käseplatte',
                        'desc' => 'Bölgesel seçkin peynir çeşitleri, kuru meyveler ve ceviz ile.',
                        'desc_en' => 'Fine selection of regional Turkish cheeses served with dried fruits and walnuts.',
                        'price' => 600.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1631379578550-7038263db699?w=800&q=80',
                        'badge' => 'Popüler',
                        'calories' => 450,
                        'prep_time' => 8,
                        'allergens' => 'Laktoz, Kuruyemiş',
                        'is_featured' => 1
                    ],
                ]
            ],
            [
                'name' => 'Atıştırmalıklar',
                'name_en' => 'Snacks & Bites',
                'name_ar' => 'الوجبات الخفيفة والمقبلات',
                'name_ru' => 'Снеки и закуски',
                'name_de' => 'Snacks & Fingerfood',
                'slug' => 'atistirmaliklar',
                'icon' => 'burger',
                'image' => 'https://images.unsplash.com/photo-1541592106381-b31e9677c0e5?w=600&q=80',
                'sort_order' => 2,
                'products' => [
                    [
                        'name' => 'Peynirli Tost',
                        'name_en' => 'Cheese Toast',
                        'name_ar' => 'توست الجبن',
                        'name_ru' => 'Тост с сыром',
                        'name_de' => 'Käsetoast',
                        'desc' => 'Eritilmiş kaşar peyniri, domates ve salatalık eşliğinde.',
                        'desc_en' => 'Melted cheese toast served with tomato and cucumber slices.',
                        'price' => 175.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1528735602780-2552fd46c7af?w=800&q=80',
                        'badge' => '',
                        'calories' => 380,
                        'prep_time' => 8,
                        'allergens' => 'Gluten, Laktoz',
                        'is_featured' => 0
                    ],
                    [
                        'name' => 'Sucuklu Tost',
                        'name_en' => 'Sujuk Toast',
                        'name_ar' => 'توست السجق',
                        'name_ru' => 'Тост с суджуком',
                        'name_de' => 'Sujuk Toast',
                        'desc' => 'Kızarmış dana sucuğu, çeri domates ve salatalık eşliğinde.',
                        'desc_en' => 'Spicy beef sujuk toast served with cherry tomatoes and cucumber.',
                        'price' => 200.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1584776296944-ab6fb57b0bdd?w=800&q=80',
                        'badge' => '',
                        'calories' => 430,
                        'prep_time' => 8,
                        'allergens' => 'Gluten',
                        'is_featured' => 0
                    ],
                    [
                        'name' => 'Karışık Tost',
                        'name_en' => 'Mixed Toast',
                        'name_ar' => 'توست مشكل',
                        'name_ru' => 'Смешанный тост',
                        'name_de' => 'Gemischter Toast',
                        'desc' => 'Dana sucuğu, kaşar peyniri, salatalık ve çeri domates eşliğinde.',
                        'desc_en' => 'Beef sujuk and kashar cheese toast served with tomato and cucumber.',
                        'price' => 250.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1528735602780-2552fd46c7af?w=800&q=80',
                        'badge' => 'Klasik',
                        'calories' => 490,
                        'prep_time' => 8,
                        'allergens' => 'Gluten, Laktoz',
                        'is_featured' => 1
                    ],
                    [
                        'name' => 'Gözleme',
                        'name_en' => 'Traditional Turkish Gözleme',
                        'name_ar' => 'فطائر غوزليمة التركية',
                        'name_ru' => 'Гёзлеме',
                        'name_de' => 'Gözleme Fladenbrot',
                        'desc' => 'Dana kıyma, peynirli, kaşarlı veya ıspanaklı seçenekleriyle sacda taze pişirilir.',
                        'desc_en' => 'Handmade Turkish flatbread filled with minced beef, feta, kashar or spinach.',
                        'price' => 300.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1627308595229-7830a5c91f9f?w=800&q=80',
                        'badge' => 'El Açması',
                        'calories' => 450,
                        'prep_time' => 12,
                        'allergens' => 'Gluten, Laktoz',
                        'is_featured' => 1
                    ],
                    [
                        'name' => 'Bira Tabağı',
                        'name_en' => 'Beer Snack Platter',
                        'name_ar' => 'طبق مقبلات مشكل كبير',
                        'name_ru' => 'Пивная тарелка',
                        'name_de' => 'Bierteller Snackplatte',
                        'desc' => 'Patates kızartması, nugget, sigara böreği, sosis ve çıtır soğan halkası.',
                        'desc_en' => 'French fries, chicken nuggets, crispy rolls, sausages and onion rings.',
                        'price' => 550.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1541592106381-b31e9677c0e5?w=800&q=80',
                        'badge' => 'Favori',
                        'calories' => 950,
                        'prep_time' => 15,
                        'allergens' => 'Gluten, Laktoz',
                        'is_featured' => 1
                    ],
                    [
                        'name' => 'Patates Kızartması',
                        'name_en' => 'French Fries',
                        'name_ar' => 'بطاطس مقلية مقرمشة',
                        'name_ru' => 'Картофель фри',
                        'name_de' => 'Pommes Frites',
                        'desc' => 'Altın sarısı çıtır patates kızartması, özel baharat karışımı ile.',
                        'desc_en' => 'Golden crispy french fries seasoned with house spice blend.',
                        'price' => 200.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1576107232684-1279f3908594?w=800&q=80',
                        'badge' => '',
                        'calories' => 420,
                        'prep_time' => 8,
                        'allergens' => '',
                        'is_featured' => 0
                    ],
                    [
                        'name' => 'Karides Cipsi',
                        'name_en' => 'Prawn Crackers',
                        'name_ar' => 'رقائق الجمبري المقرمشة',
                        'name_ru' => 'Креветочные чипсы',
                        'name_de' => 'Krabbenchips',
                        'desc' => 'Özel baharat ve dip sos ile servis edilen çıtır karides cipsi.',
                        'desc_en' => 'Crispy prawn crackers served with seasoning and dipping sauce.',
                        'price' => 250.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1563379091339-03b21ab4a4f8?w=800&q=80',
                        'badge' => '',
                        'calories' => 280,
                        'prep_time' => 5,
                        'allergens' => 'Deniz Ürünleri',
                        'is_featured' => 0
                    ],
                    [
                        'name' => 'Soğan Halkası',
                        'name_en' => 'Crispy Onion Rings',
                        'name_ar' => 'حلقات البصل المقرمشة',
                        'name_ru' => 'Луковые кольца',
                        'name_de' => 'Zwiebelringe',
                        'desc' => 'Çıtır kaplamalı soğan halkaları, patates kızartması ve soslar ile.',
                        'desc_en' => 'Crispy battered onion rings served with golden french fries and dips.',
                        'price' => 350.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1639024471287-032f66e5f039?w=800&q=80',
                        'badge' => '',
                        'calories' => 480,
                        'prep_time' => 10,
                        'allergens' => 'Gluten',
                        'is_featured' => 0
                    ],
                    [
                        'name' => 'Nuggets',
                        'name_en' => 'Chicken Nuggets',
                        'name_ar' => 'قطع الدجاج المقرمشة (ناجتس)',
                        'name_ru' => 'Куриные наггетсы',
                        'name_de' => 'Chicken Nuggets',
                        'desc' => 'Çıtır tavuk nugget dilimleri, patates kızartması ve soslar ile.',
                        'desc_en' => 'Crispy chicken nuggets served with golden fries and dipping sauces.',
                        'price' => 350.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1562967914-608f82629710?w=800&q=80',
                        'badge' => '',
                        'calories' => 520,
                        'prep_time' => 10,
                        'allergens' => 'Gluten',
                        'is_featured' => 0
                    ],
                    [
                        'name' => 'Sosis Tabağı',
                        'name_en' => 'Sausage Platter',
                        'name_ar' => 'طبق النقانق المشوية',
                        'name_ru' => 'Тарелка с колбасками',
                        'name_de' => 'Würstchenplatte',
                        'desc' => 'Izgara sosis dilimleri, patates kızartması ve hardal ile.',
                        'desc_en' => 'Grilled sausage slices served with crispy french fries and mustard.',
                        'price' => 350.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1585325701165-351af916e581?w=800&q=80',
                        'badge' => '',
                        'calories' => 560,
                        'prep_time' => 10,
                        'allergens' => 'Hardal',
                        'is_featured' => 0
                    ]
                ]
            ],
            [
                'name' => 'Makarnalar',
                'name_en' => 'Pastas',
                'name_ar' => 'المعكرونة الإيطالية',
                'name_ru' => 'Паста',
                'name_de' => 'Pasta & Nudeln',
                'slug' => 'makarnalar',
                'icon' => 'bowl-rice',
                'image' => 'https://images.unsplash.com/photo-1551183053-bf91a1d81141?w=600&q=80',
                'sort_order' => 3,
                'products' => [
                    [
                        'name' => 'Spaghetti Bolonez',
                        'name_en' => 'Spaghetti Bolognese',
                        'name_ar' => 'سباغيتي بولونيز',
                        'name_ru' => 'Спагетти Болоньезе',
                        'name_de' => 'Spaghetti Bolognese',
                        'desc' => 'Geleneksel ağır ateşte pişmiş dana kıymalı Bolonez sos ve rendelenmiş parmesan peyniri ile.',
                        'desc_en' => 'Traditional slow-simmered beef Bolognese sauce topped with fresh parmesan.',
                        'price' => 450.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1621996346565-e3adc644d946?w=800&q=80',
                        'badge' => 'İtalyan',
                        'calories' => 680,
                        'prep_time' => 15,
                        'allergens' => 'Gluten, Laktoz',
                        'is_featured' => 1
                    ],
                    [
                        'name' => 'Spaghetti Pesto',
                        'name_en' => 'Spaghetti al Pesto',
                        'name_ar' => 'سباغيتي مع صلصة البيستو',
                        'name_ru' => 'Спагетти с песто',
                        'name_de' => 'Spaghetti Pesto',
                        'desc' => 'Ev yapımı taze fesleğenli pesto sos, çam fıstığı ve parmesan peyniri ile.',
                        'desc_en' => 'Homemade fresh basil pesto sauce, pine nuts and parmesan cheese.',
                        'price' => 450.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1551183053-bf91a1d81141?w=800&q=80',
                        'badge' => '',
                        'calories' => 610,
                        'prep_time' => 14,
                        'allergens' => 'Gluten, Laktoz, Kuruyemiş',
                        'is_featured' => 0
                    ],
                    [
                        'name' => 'Fettuccine Alfredo',
                        'name_en' => 'Fettuccine Alfredo',
                        'name_ar' => 'فيتوتشيني ألفريدو بالدجاج',
                        'name_ru' => 'Феттучини Альфредо',
                        'name_de' => 'Fettuccine Alfredo',
                        'desc' => 'Kremalı Alfredo sos, ızgara tavuk dilimleri, taze kültür mantarı ve parmesan peyniri ile.',
                        'desc_en' => 'Creamy Alfredo sauce, tender grilled chicken, fresh mushrooms and parmesan.',
                        'price' => 450.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1645112411341-6c4fd023714a?w=800&q=80',
                        'badge' => 'Çok Satan',
                        'calories' => 740,
                        'prep_time' => 16,
                        'allergens' => 'Gluten, Laktoz',
                        'is_featured' => 1
                    ],
                    [
                        'name' => 'Penne Arrabbiata',
                        'name_en' => 'Penne all\'Arrabbiata',
                        'name_ar' => 'بيني أرابياتا الحارة',
                        'name_ru' => 'Пенне Арраббиата',
                        'name_de' => 'Penne Arrabbiata',
                        'desc' => 'Acılı domates sosu, sarımsak, acı pul biber, taze fesleğen ve parmesan peyniri ile.',
                        'desc_en' => 'Spicy Italian tomato sauce, fresh garlic, chili flakes, basil and parmesan.',
                        'price' => 450.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1563379091339-03b21ab4a4f8?w=800&q=80',
                        'badge' => 'Acılı 🌶️',
                        'calories' => 580,
                        'prep_time' => 14,
                        'allergens' => 'Gluten, Laktoz',
                        'is_featured' => 0
                    ]
                ]
            ],
            [
                'name' => 'Pizzalar',
                'name_en' => 'Pizzas',
                'name_ar' => 'البيتزا',
                'name_ru' => 'Пицца',
                'name_de' => 'Pizzen',
                'slug' => 'pizzalar',
                'icon' => 'pizza-slice',
                'image' => 'https://images.unsplash.com/photo-1604382355076-af4b0eb60143?w=600&q=80',
                'sort_order' => 4,
                'products' => [
                    [
                        'name' => 'Pizza Margherita',
                        'name_en' => 'Pizza Margherita',
                        'name_ar' => 'بيتزا مارغريتا',
                        'name_ru' => 'Пицца Маргарита',
                        'name_de' => 'Pizza Margherita',
                        'desc' => 'Özel domates sosu, bol mozzarella peyniri ve taze fesleğen yaprakları ile.',
                        'desc_en' => 'Special tomato sauce, melted mozzarella cheese and fresh aromatic basil.',
                        'price' => 500.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1604382355076-af4b0eb60143?w=800&q=80',
                        'badge' => 'Klasik',
                        'calories' => 690,
                        'prep_time' => 15,
                        'allergens' => 'Gluten, Laktoz',
                        'is_featured' => 1
                    ],
                    [
                        'name' => 'Pizza Hawaii',
                        'name_en' => 'Pizza Hawaii',
                        'name_ar' => 'بيتزا هاواي بالأناناس',
                        'name_ru' => 'Пицца Гавайская',
                        'name_de' => 'Pizza Hawaii',
                        'desc' => 'Domates sosu, mozzarella peyniri ve tatlı ananas dilimleri ile.',
                        'desc_en' => 'Tomato sauce, melted mozzarella cheese and juicy pineapple pieces.',
                        'price' => 450.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=800&q=80',
                        'badge' => '',
                        'calories' => 670,
                        'prep_time' => 15,
                        'allergens' => 'Gluten, Laktoz',
                        'is_featured' => 0
                    ],
                    [
                        'name' => 'Pizza 4 Peynirli',
                        'name_en' => 'Four Cheese Pizza',
                        'name_ar' => 'بيتزا أربعة أجبان',
                        'name_ru' => 'Пицца 4 Сыра',
                        'name_de' => 'Pizza Vier Käse',
                        'desc' => 'Mozzarella, parmesan, ezine peyniri ve eritilmiş cheddar peyniri uyumu.',
                        'desc_en' => 'Four cheese blend: Mozzarella, Parmesan, regional Ezine and Cheddar.',
                        'price' => 550.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1513104890138-7c749659a591?w=800&q=80',
                        'badge' => 'Özel Peynirli',
                        'calories' => 780,
                        'prep_time' => 15,
                        'allergens' => 'Gluten, Laktoz',
                        'is_featured' => 1
                    ],
                    [
                        'name' => 'Pizza Karışık',
                        'name_en' => 'Supreme Mixed Pizza',
                        'name_ar' => 'بيتزا سوبريم مشكلة',
                        'name_ru' => 'Пицца Ассорти',
                        'name_de' => 'Pizza Gemischt',
                        'desc' => 'Dana sucuğu, sosis, mantar, yeşil biber, siyah zeytin ve bol mozzarella peyniri.',
                        'desc_en' => 'Beef sujuk, sausages, fresh mushrooms, bell peppers, black olives and mozzarella.',
                        'price' => 600.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1534308983496-4fabb1a015ee?w=800&q=80',
                        'badge' => 'Popüler',
                        'calories' => 840,
                        'prep_time' => 16,
                        'allergens' => 'Gluten, Laktoz',
                        'is_featured' => 1
                    ]
                ]
            ],
            [
                'name' => 'Burgerler',
                'name_en' => 'Burgers',
                'name_ar' => 'البرغر',
                'name_ru' => 'Бургеры',
                'name_de' => 'Burger',
                'slug' => 'burgerler',
                'icon' => 'burger',
                'image' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=600&q=80',
                'sort_order' => 5,
                'products' => [
                    [
                        'name' => 'Mare Burger',
                        'name_en' => 'Mare Special Burger',
                        'name_ar' => 'ماري برغر الخاص',
                        'name_ru' => 'Фирменный Mare Бургер',
                        'name_de' => 'Mare Spezialburger',
                        'desc' => '150 gr ızgara dana köftesi, karamelize soğan, marul, domates, turşu ve ev yapımı burger sosu ile servis edilir. Patates kızartması eşliğinde.',
                        'desc_en' => '150g grilled beef patty, caramelized onions, crisp lettuce, tomato, pickles and house special sauce. Served with fries.',
                        'price' => 350.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=800&q=80',
                        'badge' => 'Şefin İmzası',
                        'calories' => 790,
                        'prep_time' => 16,
                        'allergens' => 'Gluten, Hardal',
                        'is_featured' => 1
                    ],
                    [
                        'name' => 'Cheeseburger',
                        'name_en' => 'Classic Cheeseburger',
                        'name_ar' => 'تشيز برغر كلاسيك',
                        'name_ru' => 'Чизбургер',
                        'name_de' => 'Cheeseburger',
                        'desc' => '150 gr ızgara dana köftesi, eritilmiş cheddar peyniri, marul, domates, turşu ve ev yapımı burger sosu ile servis edilir. Patates kızartması eşliğinde.',
                        'desc_en' => '150g grilled beef patty, melted cheddar cheese, crisp lettuce, tomato, pickles and house burger sauce. Served with fries.',
                        'price' => 400.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1550547660-d9450f859349?w=800&q=80',
                        'badge' => 'Popüler',
                        'calories' => 840,
                        'prep_time' => 16,
                        'allergens' => 'Gluten, Laktoz, Hardal',
                        'is_featured' => 1
                    ]
                ]
            ],
            [
                'name' => 'Salatalar',
                'name_en' => 'Salads',
                'name_ar' => 'السلطات الطازجة',
                'name_ru' => 'Салаты',
                'name_de' => 'Salate',
                'slug' => 'salatalar',
                'icon' => 'leaf',
                'image' => 'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=600&q=80',
                'sort_order' => 6,
                'products' => [
                    [
                        'name' => 'Çoban Salata',
                        'name_en' => 'Shepherd\'s Salad',
                        'name_ar' => 'سلطة الراعي التركية',
                        'name_ru' => 'Пастуший салат',
                        'name_de' => 'Hirtensalat',
                        'desc' => 'Taze domates, çıtır salatalık, yeşil biber, mor soğan, maydanoz ve sızma zeytinyağı ile.',
                        'desc_en' => 'Diced tomatoes, crisp cucumbers, green peppers, red onion, parsley and extra virgin olive oil.',
                        'price' => 200.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=800&q=80',
                        'badge' => '',
                        'calories' => 160,
                        'prep_time' => 8,
                        'allergens' => '',
                        'is_featured' => 0
                    ],
                    [
                        'name' => 'Mevsim Salata',
                        'name_en' => 'Fresh Garden Salad',
                        'name_ar' => 'سلطة الموسم الخضراء',
                        'name_ru' => 'Сезонный салат',
                        'name_de' => 'Gemischter Gartensalat',
                        'desc' => 'Taze mevsim yeşillikleri, domates, salatalık, rendelenmiş havuç ve limon zeytinyağı sosu ile.',
                        'desc_en' => 'Seasonal fresh garden greens, tomatoes, cucumbers, carrots and lemon olive oil dressing.',
                        'price' => 200.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=800&q=80',
                        'badge' => '',
                        'calories' => 140,
                        'prep_time' => 8,
                        'allergens' => '',
                        'is_featured' => 0
                    ],
                    [
                        'name' => 'Kaşık Salata',
                        'name_en' => 'Finely Chopped Spoon Salad',
                        'name_ar' => 'سلطة الملعقة المفرومة ناعما',
                        'name_ru' => 'Салат Кашик',
                        'name_de' => 'Löffelsalat',
                        'desc' => 'İncecik kıyılmış domates, salatalık, biber, soğan, ceviz ve ekşi nar ekşisi sosu ile.',
                        'desc_en' => 'Finely diced tomatoes, cucumbers, peppers, onion, walnuts with rich pomegranate molasses.',
                        'price' => 250.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1505253716362-afaea1d3d1af?w=800&q=80',
                        'badge' => '',
                        'calories' => 210,
                        'prep_time' => 10,
                        'allergens' => 'Kuruyemiş',
                        'is_featured' => 0
                    ],
                    [
                        'name' => 'Caesar Salata',
                        'name_en' => 'Chicken Caesar Salad',
                        'name_ar' => 'سلطة سيزر بالدجاج',
                        'name_ru' => 'Салат Цезарь с курицей',
                        'name_de' => 'Caesar Salat mit Hähnchen',
                        'desc' => 'Çıtır göbek marul, ızgara tavuk göğsü dilimleri, kruton ekmeği, parmesan peyniri ve özel Caesar sos ile.',
                        'desc_en' => 'Crisp romaine lettuce, grilled chicken slices, croutons, parmesan flakes and Caesar dressing.',
                        'price' => 600.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1550304943-4f24f54ddde9?w=800&q=80',
                        'badge' => 'Özel',
                        'calories' => 460,
                        'prep_time' => 12,
                        'allergens' => 'Gluten, Laktoz, Yumurta',
                        'is_featured' => 1
                    ]
                ]
            ],
            [
                'name' => 'Ana Yemekler',
                'name_en' => 'Main Courses',
                'name_ar' => 'الأطباق الرئيسية والمشاوي',
                'name_ru' => 'Основные блюда',
                'name_de' => 'Hauptgerichte',
                'slug' => 'ana-yemekler',
                'icon' => 'utensils',
                'image' => 'https://images.unsplash.com/photo-1544025162-d76694265947?w=600&q=80',
                'sort_order' => 7,
                'products' => [
                    [
                        'name' => 'Köfte',
                        'name_en' => 'Grilled Turkish Meatballs',
                        'name_ar' => 'كفتة مشوية تركية',
                        'name_ru' => 'Кёфте на гриле',
                        'name_de' => 'Gegrillte Frikadellen (Köfte)',
                        'desc' => 'Özel baharatlarla harmanlanmış ızgara dana köfte, patates kızartması ve soğan piyazı ile.',
                        'desc_en' => 'Traditional grilled beef meatballs served with french fries and seasoned onion salad.',
                        'price' => 450.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1529042410759-befb1204b468?w=800&q=80',
                        'badge' => 'Geleneksel',
                        'calories' => 680,
                        'prep_time' => 18,
                        'allergens' => 'Gluten',
                        'is_featured' => 1
                    ],
                    [
                        'name' => 'Fajita',
                        'name_en' => 'Sizzling Beef Fajita',
                        'name_ar' => 'فاهيتا اللحم البقري',
                        'name_ru' => 'Фахита с говядиной',
                        'name_de' => 'Rindfleisch Fajita',
                        'desc' => 'Cızırdayan döküm tavada sotelenmiş dana eti dilimleri, renkli biberler, patates kızartması ve salsa sos eşliğinde.',
                        'desc_en' => 'Sizzling cast-iron beef strips sautéed with colorful peppers, served with fries and salsa.',
                        'price' => 700.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1534422298391-e4f8c172dddb?w=800&q=80',
                        'badge' => 'Şefin Spesiyali',
                        'calories' => 740,
                        'prep_time' => 20,
                        'allergens' => '',
                        'is_featured' => 1
                    ],
                    [
                        'name' => 'Bonfile Lokum',
                        'name_en' => 'Beef Tenderloin Lokum',
                        'name_ar' => 'ستيك لحم بقر لوكوم تندرلوين',
                        'name_ru' => 'Локум из говяжьей вырезки',
                        'name_de' => 'Rinderfilet Lokum Medaillons',
                        'desc' => 'Tereyağında mühürlenmiş pamuk gibi yumuşak dana bonfile lokum dilimleri, patates kızartması eşliğinde.',
                        'desc_en' => 'Ultra tender seared beef tenderloin medallions served with crispy french fries.',
                        'price' => 800.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1558030006-450675393462?w=800&q=80',
                        'badge' => 'Premium ⭐',
                        'calories' => 710,
                        'prep_time' => 20,
                        'allergens' => 'Laktoz',
                        'is_featured' => 1
                    ],
                    [
                        'name' => 'Bonfile',
                        'name_en' => 'Grilled Tenderloin Steak',
                        'name_ar' => 'ستيك فيليه اللحم مع الفطر',
                        'name_ru' => 'Стейк из говяжьей вырезки',
                        'name_de' => 'Gegrilltes Rinderfilet Steak',
                        'desc' => 'Izgara dana bonfile biftek, kremalı taze mantar sosu ve patates kızartması ile.',
                        'desc_en' => 'Prime grilled tenderloin steak served with creamy wild mushroom sauce and french fries.',
                        'price' => 900.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1544025162-d76694265947?w=800&q=80',
                        'badge' => 'Premium ⭐',
                        'calories' => 760,
                        'prep_time' => 22,
                        'allergens' => 'Laktoz',
                        'is_featured' => 1
                    ],
                    [
                        'name' => 'Sac Kavurma',
                        'name_en' => 'Traditional Sac Kavurma',
                        'name_ar' => 'صاج كاورما لحم تركي',
                        'name_ru' => 'Сач кавурма',
                        'name_de' => 'Traditionelles Sac Kavurma',
                        'desc' => 'Özel sac tavada sotelenmiş leziz dana eti, domates, biber ve patates kızartması ile.',
                        'desc_en' => 'Traditional wok-sautéed tender beef with tomatoes, peppers and french fries.',
                        'price' => 800.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1603048588665-791ca8aea617?w=800&q=80',
                        'badge' => 'Klasik',
                        'calories' => 720,
                        'prep_time' => 20,
                        'allergens' => '',
                        'is_featured' => 1
                    ],
                    [
                        'name' => 'Mantı',
                        'name_en' => 'Handmade Turkish Manti',
                        'name_ar' => 'مانتي تركي يدوي بالزبادي',
                        'name_ru' => 'Турецкие манты',
                        'name_de' => 'Handgemachte Manti Teigtaschen',
                        'desc' => 'El yapımı Kayseri mantısı, sarımsaklı süzme yoğurt ve kızgın tereyağlı biber sosu ile servis edilir.',
                        'desc_en' => 'Handmade Turkish meat dumplings with garlic yogurt and sizzling pepper butter.',
                        'price' => 350.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1625944525533-473f1a3d54e7?w=800&q=80',
                        'badge' => 'El Yapımı',
                        'calories' => 620,
                        'prep_time' => 15,
                        'allergens' => 'Gluten, Laktoz, Yumurta',
                        'is_featured' => 1
                    ],
                    [
                        'name' => 'Çin Böreği',
                        'name_en' => 'Crispy Chicken Spring Rolls',
                        'name_ar' => 'سبرينغ رول الدجاج المقرمش',
                        'name_ru' => 'Спринг-роллы с курицей',
                        'name_de' => 'Knusprige Frühlingsrollen',
                        'desc' => 'Tavuk eti, havuç, kabak, soya sosu ve susam ile sarılmış çıtır börekler.',
                        'desc_en' => 'Crispy fried spring rolls stuffed with chicken, julienned vegetables, soy sauce and sesame.',
                        'price' => 300.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1544025162-d76694265947?w=800&q=80',
                        'badge' => 'Sıcak Başlangıç',
                        'calories' => 390,
                        'prep_time' => 12,
                        'allergens' => 'Gluten, Soya, Susam',
                        'is_featured' => 0
                    ],
                    [
                        'name' => 'Paçanga Böreği',
                        'name_en' => 'Traditional Pacanga Pastry',
                        'name_ar' => 'فطائر باشانغا بالبسطرمة والجبن',
                        'name_ru' => 'Пачанга бёрек',
                        'name_de' => 'Pacanga Teigtaschen mit Pastirma',
                        'desc' => 'Kayseri pastırması, domates, biber ve eritilmiş kaşar peyniri ile çıtır kızarmış börek.',
                        'desc_en' => 'Crispy fried pastry filled with pastrami, peppers, tomatoes and melted kashar cheese.',
                        'price' => 300.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1608897013039-887f21d8c804?w=800&q=80',
                        'badge' => 'Sıcak Lezzet',
                        'calories' => 430,
                        'prep_time' => 12,
                        'allergens' => 'Gluten, Laktoz',
                        'is_featured' => 0
                    ],
                    [
                        'name' => 'Körili Tavuk',
                        'name_en' => 'Savory Curry Chicken',
                        'name_ar' => 'دجاج بالكاري اللذيذ',
                        'name_ru' => 'Курица в соусе карри',
                        'name_de' => 'Curry Hähnchen',
                        'desc' => 'Sotelenmiş tavuk göğsü, renkli biberler, mantar, aromatik krema köri sosu, mevsim yeşillikleri ve patates kızartması ile.',
                        'desc_en' => 'Tender chicken breast sautéed with peppers, mushrooms in creamy curry sauce, with greens and fries.',
                        'price' => 400.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1604908176997-125f25cc6f3d?w=800&q=80',
                        'badge' => 'Popüler',
                        'calories' => 640,
                        'prep_time' => 18,
                        'allergens' => 'Laktoz',
                        'is_featured' => 1
                    ]
                ]
            ],
            [
                'name' => 'Deniz Ürünleri',
                'name_en' => 'Seafood & Fresh Fish',
                'name_ar' => 'المأكولات البحرية والأسماك الطازجة',
                'name_ru' => 'Рыба и морепродукты',
                'name_de' => 'Meeresfrüchte & Frischer Fisch',
                'slug' => 'deniz-urunleri',
                'icon' => 'fish',
                'image' => 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?w=600&q=80',
                'sort_order' => 8,
                'products' => [
                    [
                        'name' => 'Mezgit',
                        'name_en' => 'Pan-Fried Whiting Fish',
                        'name_ar' => 'سمك البياض (ميزغيت) الطازج',
                        'name_ru' => 'Мерланг жареный',
                        'name_de' => 'Gebratener Wittling Fisch',
                        'desc' => 'Taze tava mezgit balığı, kırmızı soğan halkaları, taze roka ve domates ile servis edilir.',
                        'desc_en' => 'Fresh pan-fried whiting fish served with red onion, wild arugula, lemon and tomato.',
                        'price' => 700.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?w=800&q=80',
                        'badge' => 'Günlük Taze 🐟',
                        'calories' => 490,
                        'prep_time' => 18,
                        'allergens' => 'Balık',
                        'is_featured' => 1
                    ],
                    [
                        'name' => 'Sardalya',
                        'name_en' => 'Grilled Gulf Sardines',
                        'name_ar' => 'سردين خليج إدremit المشوي',
                        'name_ru' => 'Сардины на гриле',
                        'name_de' => 'Gegrillte Sardinen',
                        'desc' => 'Körfez taze sardalya, ızgara edilmiş kırmızı soğan, roka ve domates ile servis edilir.',
                        'desc_en' => 'Freshly grilled Gulf sardines served with onion, fresh arugula and lemon.',
                        'price' => 500.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1534604973900-c43ab4c2e0ab?w=800&q=80',
                        'badge' => 'Ege Lezzeti',
                        'calories' => 440,
                        'prep_time' => 15,
                        'allergens' => 'Balık',
                        'is_featured' => 0
                    ],
                    [
                        'name' => 'Çipura',
                        'name_en' => 'Grilled Sea Bream',
                        'name_ar' => 'سمك الدنيس المشوي',
                        'name_ru' => 'Дорадо на гриле',
                        'name_de' => 'Gegrillte Dorade',
                        'desc' => 'Kömür ateşinde ızgara taze çipura, zeytinyağı sosu, soğan, roka ve domates ile servis edilir.',
                        'desc_en' => 'Charcoal grilled fresh whole sea bream served with olive oil sauce, arugula, onion and lemon.',
                        'price' => 650.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1534422298391-e4f8c172dddb?w=800&q=80',
                        'badge' => 'Izgara Balık',
                        'calories' => 510,
                        'prep_time' => 20,
                        'allergens' => 'Balık',
                        'is_featured' => 1
                    ],
                    [
                        'name' => 'Levrek',
                        'name_en' => 'Grilled Sea Bass',
                        'name_ar' => 'سمك القاروص المشوي',
                        'name_ru' => 'Сибас на гриле',
                        'name_de' => 'Gegrillter Wolfsbarsch',
                        'desc' => 'Kömür ateşinde ızgara taze deniz levreği, soğan, roka ve domates ile servis edilir.',
                        'desc_en' => 'Charcoal grilled Mediterranean sea bass served with red onion, fresh arugula and lemon.',
                        'price' => 650.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?w=800&q=80',
                        'badge' => 'Şefin Tavsiyesi',
                        'calories' => 490,
                        'prep_time' => 20,
                        'allergens' => 'Balık',
                        'is_featured' => 1
                    ],
                    [
                        'name' => 'Somon',
                        'name_en' => 'Grilled Norwegian Salmon',
                        'name_ar' => 'فيليه سلمون مشوي مع صلصة التارتار',
                        'name_ru' => 'Лосось на гриле с тар-таром',
                        'name_de' => 'Gegrilltes Lachsfilet mit Remoulade',
                        'desc' => 'Izgara somon fileto, soğan, taze roka, domates ve özel ev yapımı tartar sos ile servis edilir.',
                        'desc_en' => 'Grilled salmon steak served with house-made tartar sauce, fresh arugula, tomato and lemon.',
                        'price' => 700.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1467003909585-2f8a72700288?w=800&q=80',
                        'badge' => 'Şefin Spesiyali',
                        'calories' => 580,
                        'prep_time' => 18,
                        'allergens' => 'Balık, Yumurta',
                        'is_featured' => 1
                    ],
                    [
                        'name' => 'Kalamar',
                        'name_en' => 'Crispy Calamari Rings',
                        'name_ar' => 'حلقات الحبار المقرمشة مع صلصة الطرطور',
                        'name_ru' => 'Кальмары во фритюре с тартаром',
                        'name_de' => 'Knusprige Calamari Ringe',
                        'desc' => 'Altın sarısı çıtır kalamar tava, soğan, taze roka, domates ve nefis cevizli tarator sos ile servis edilir.',
                        'desc_en' => 'Golden fried crispy calamari rings served with traditional walnut tarator sauce and lemon.',
                        'price' => 750.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1599488615731-7e5c2823ff28?w=800&q=80',
                        'badge' => 'Favori Meze',
                        'calories' => 540,
                        'prep_time' => 15,
                        'allergens' => 'Deniz Ürünleri, Gluten, Kuruyemiş',
                        'is_featured' => 1
                    ],
                    [
                        'name' => 'Karides Güveç',
                        'name_en' => 'Baked Shrimp Casserole',
                        'name_ar' => 'طاجن الروبيان بالزبدة والجبن',
                        'name_ru' => 'Креветки в глиняном горшочке',
                        'name_de' => 'Gebackener Garnelenauflauf',
                        'desc' => 'Taze karides, sarımsak, domates, biber, tereyağı ve eritilmiş kaşar peyniri fırınlanarak hazırlanır.',
                        'desc_en' => 'Sizzling hot clay pot baked shrimp with garlic, tomatoes, peppers, butter and melted cheese.',
                        'price' => 700.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1565680018434-b513d5e5fd47?w=800&q=80',
                        'badge' => 'Güveçte Sıcak 🔥',
                        'calories' => 520,
                        'prep_time' => 18,
                        'allergens' => 'Deniz Ürünleri, Laktoz',
                        'is_featured' => 1
                    ]
                ]
            ],
            // 9. MEŞRUBATLAR / İÇECEKLER
            [
                'name' => 'Meşrubatlar & İçecekler',
                'name_en' => 'Soft Drinks & Beverages',
                'name_ar' => 'المشروبات الغازية والباردة',
                'name_ru' => 'Безалкогольные напитки',
                'name_de' => 'Alkoholfreie Getränke',
                'slug' => 'mesrubatlar-icecekler',
                'icon' => 'bottle-water',
                'image' => 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?w=600&q=80',
                'sort_order' => 9,
                'products' => [
                    [
                        'name' => 'Çay',
                        'name_en' => 'Turkish Black Tea',
                        'name_ar' => 'شاي تركي أسود مخدر',
                        'name_ru' => 'Турецкий черный чай',
                        'name_de' => 'Türkischer Schwarztee',
                        'desc' => 'Taze demlenmiş geleneksel Türk çayı ince belli bardakta.',
                        'desc_en' => 'Freshly brewed traditional Turkish black tea.',
                        'price' => 50.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1576092768241-dec231879fc3?w=800&q=80',
                        'badge' => 'Taze Demleme',
                        'calories' => 2,
                        'prep_time' => 2,
                        'allergens' => '',
                        'is_featured' => 0
                    ],
                    [
                        'name' => 'Su',
                        'name_en' => 'Natural Spring Water',
                        'name_ar' => 'مياه معدنية طبيعية',
                        'name_ru' => 'Минеральная вода',
                        'name_de' => 'Stilles Mineralwasser',
                        'desc' => 'Şişe doğal kaynak suyu.',
                        'desc_en' => 'Bottled natural spring water.',
                        'price' => 25.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1548839140-29a749e1bc4e?w=800&q=80',
                        'badge' => '',
                        'calories' => 0,
                        'prep_time' => 1,
                        'allergens' => '',
                        'is_featured' => 0
                    ],
                    [
                        'name' => 'Soda',
                        'name_en' => 'Sparkling Mineral Water',
                        'name_ar' => 'مياه فوارة معدنية',
                        'name_ru' => 'Газированная вода',
                        'name_de' => 'Mineralwasser mit Kohlensäure',
                        'desc' => 'Doğal maden suyu şişe.',
                        'desc_en' => 'Bottled natural sparkling mineral water.',
                        'price' => 50.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1622483767028-3f66f32aef97?w=800&q=80',
                        'badge' => '',
                        'calories' => 0,
                        'prep_time' => 1,
                        'allergens' => '',
                        'is_featured' => 0
                    ],
                    [
                        'name' => 'Coca Cola',
                        'name_en' => 'Coca Cola',
                        'name_ar' => 'كوكاكولا مثلجة',
                        'name_ru' => 'Кока-Кола',
                        'name_de' => 'Coca Cola',
                        'desc' => 'Buz gibi soğuk Coca-Cola (Orijinal / Zero).',
                        'desc_en' => 'Chilled Coca-Cola (Classic / Zero).',
                        'price' => 120.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1622483767028-3f66f32aef97?w=800&q=80',
                        'badge' => '',
                        'calories' => 140,
                        'prep_time' => 2,
                        'allergens' => '',
                        'is_featured' => 0
                    ],
                    [
                        'name' => 'Fanta',
                        'name_en' => 'Fanta Orange',
                        'name_ar' => 'فانتا برتقال',
                        'name_ru' => 'Фанта',
                        'name_de' => 'Fanta Orange',
                        'desc' => 'Buz gibi soğuk portakallı Fanta.',
                        'desc_en' => 'Chilled sparkling orange Fanta.',
                        'price' => 120.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1624517452488-04869289c4ca?w=800&q=80',
                        'badge' => '',
                        'calories' => 150,
                        'prep_time' => 2,
                        'allergens' => '',
                        'is_featured' => 0
                    ],
                    [
                        'name' => 'Sprite',
                        'name_en' => 'Sprite Lemon-Lime',
                        'name_ar' => 'سبرايت ليمون منعش',
                        'name_ru' => 'Спрайт',
                        'name_de' => 'Sprite',
                        'desc' => 'Buz gibi ferahlatıcı limonlu gazoz.',
                        'desc_en' => 'Chilled refreshing lemon-lime soda.',
                        'price' => 120.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1625772299848-391b6a87d7b3?w=800&q=80',
                        'badge' => '',
                        'calories' => 140,
                        'prep_time' => 2,
                        'allergens' => '',
                        'is_featured' => 0
                    ],
                    [
                        'name' => 'Meyve Suyu',
                        'name_en' => 'Fruit Juice Selection',
                        'name_ar' => 'عصائر فواكه مشكلة',
                        'name_ru' => 'Фруктовый сок',
                        'name_de' => 'Fruchtsaft',
                        'desc' => 'Şeftali, Vişne, Portakal veya Karışık meyve suyu seçenekleriyle.',
                        'desc_en' => 'Choice of Peach, Sour Cherry, Orange or Mixed fruit juice.',
                        'price' => 120.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1613478223719-2ab802602423?w=800&q=80',
                        'badge' => '',
                        'calories' => 120,
                        'prep_time' => 2,
                        'allergens' => '',
                        'is_featured' => 0
                    ],
                    [
                        'name' => 'Red Bull',
                        'name_en' => 'Red Bull Energy Drink',
                        'name_ar' => 'مشروب الطاقة ريد بول',
                        'name_ru' => 'Ред Булл',
                        'name_de' => 'Red Bull Energy Drink',
                        'desc' => 'Orijinal enerji içeceği (250 ml kutu).',
                        'desc_en' => 'Original energy drink (250 ml can).',
                        'price' => 150.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1527661591475-527312dd65f5?w=800&q=80',
                        'badge' => 'Enerji',
                        'calories' => 110,
                        'prep_time' => 2,
                        'allergens' => '',
                        'is_featured' => 0
                    ],
                    [
                        'name' => 'Ice tea çeşitleri',
                        'name_en' => 'Iced Tea Selection',
                        'name_ar' => 'شاي مثلج بنكهات مختلفة',
                        'name_ru' => 'Холодный чай в ассортименте',
                        'name_de' => 'Eistee Auswahl',
                        'desc' => 'Şeftali, Limon veya Mango aromalı soğuk çay.',
                        'desc_en' => 'Chilled iced tea with Peach, Lemon or Mango flavor.',
                        'price' => 120.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?w=800&q=80',
                        'badge' => 'Soğuk & Ferah',
                        'calories' => 90,
                        'prep_time' => 2,
                        'allergens' => '',
                        'is_featured' => 0
                    ],
                    [
                        'name' => 'Koruk Suyu',
                        'name_en' => 'Traditional Verjuice (Koruk Suyu)',
                        'name_ar' => 'عصير الحصرم التقليدي (كوروك)',
                        'name_ru' => 'Традиционный виноградный сок Корук',
                        'name_de' => 'Traditioneller Verjus (Koruk Suyu)',
                        'desc' => 'Ege\'nin meşhur geleneksel ekşi ve ferahlatıcı koruk suyu.',
                        'desc_en' => 'Famous traditional Aegean refreshing sour unripe grape juice.',
                        'price' => 150.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?w=800&q=80',
                        'badge' => 'Ege Spesiyali ⭐',
                        'calories' => 85,
                        'prep_time' => 2,
                        'allergens' => '',
                        'is_featured' => 1
                    ],
                    [
                        'name' => 'Karadut Suyu',
                        'name_en' => 'Natural Black Mulberry Juice',
                        'name_ar' => 'عصير التوت الأسود الطبيعي',
                        'name_ru' => 'Сок черной шелковицы',
                        'name_de' => 'Schwarzer Maulbeersaft',
                        'desc' => 'Doğal Ege karadut suyu, buz gibi servis edilir.',
                        'desc_en' => 'Pure natural Aegean black mulberry juice served ice cold.',
                        'price' => 150.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1546173159-315724a31696?w=800&q=80',
                        'badge' => 'Doğal & Taze',
                        'calories' => 110,
                        'prep_time' => 2,
                        'allergens' => '',
                        'is_featured' => 1
                    ],
                    [
                        'name' => 'Limonata',
                        'name_en' => 'Homemade Fresh Lemonade',
                        'name_ar' => 'ليموناضة طبيعية طازجة بالنعناع',
                        'name_ru' => 'Домашний лимонад',
                        'name_de' => 'Hausgemachte Limonade',
                        'desc' => 'Taze sıkılmış limon, nane yaprakları ve buz ile ev yapımı nefis limonata.',
                        'desc_en' => 'Freshly squeezed homemade lemonade with fresh mint and crushed ice.',
                        'price' => 150.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1523371067-2708361719b0?w=800&q=80',
                        'badge' => 'Ev Yapımı',
                        'calories' => 130,
                        'prep_time' => 3,
                        'allergens' => '',
                        'is_featured' => 1
                    ]
                ]
            ],
            // 10. KAHVELER & TATLILAR
            [
                'name' => 'Kahveler & Tatlılar',
                'name_en' => 'Coffees & Desserts',
                'name_ar' => 'القهوة والحلويات',
                'name_ru' => 'Кофе и десерты',
                'name_de' => 'Kaffee & Desserts',
                'slug' => 'kahveler-tatlilar',
                'icon' => 'coffee',
                'image' => 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?w=600&q=80',
                'sort_order' => 10,
                'products' => [
                    [
                        'name' => 'Espresso',
                        'name_en' => 'Single Espresso',
                        'name_ar' => 'إسبريسو سينغل',
                        'name_ru' => 'Эспрессо',
                        'name_de' => 'Espresso',
                        'desc' => 'Yoğun aromalı taze çekilmiş tek shot espresso.',
                        'desc_en' => 'Rich and intense single shot espresso.',
                        'price' => 150.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1510591509098-f4fdc6d0ff04?w=800&q=80',
                        'badge' => '',
                        'calories' => 5,
                        'prep_time' => 3,
                        'allergens' => '',
                        'is_featured' => 0
                    ],
                    [
                        'name' => 'Americano',
                        'name_en' => 'Caffe Americano',
                        'name_ar' => 'أمريكانو ساخن',
                        'name_ru' => 'Американо',
                        'name_de' => 'Americano',
                        'desc' => 'Sıcak su ile dengelenmiş çift shot taze espresso.',
                        'desc_en' => 'Double shot espresso balanced with hot water.',
                        'price' => 150.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?w=800&q=80',
                        'badge' => '',
                        'calories' => 10,
                        'prep_time' => 3,
                        'allergens' => '',
                        'is_featured' => 0
                    ],
                    [
                        'name' => 'Filtre Kahve',
                        'name_en' => 'Brewed Filter Coffee',
                        'name_ar' => 'قهوة مقطرة بالفلتر',
                        'name_ru' => 'Фильтр-кофе',
                        'name_de' => 'Filterkaffee',
                        'desc' => 'Özel çekirdeklerden taze demlenmiş filtre kahve.',
                        'desc_en' => 'Freshly brewed aromatic filter roast coffee.',
                        'price' => 150.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=800&q=80',
                        'badge' => '',
                        'calories' => 5,
                        'prep_time' => 3,
                        'allergens' => '',
                        'is_featured' => 0
                    ],
                    [
                        'name' => 'Türk Kahvesi',
                        'name_en' => 'Traditional Turkish Coffee',
                        'name_ar' => 'قهوة تركية تقليدية مع الحلقوم',
                        'name_ru' => 'Турецкий кофе',
                        'name_de' => 'Türkischer Kaffee',
                        'desc' => 'Lokum ve su eşliğinde közde pişirilmiş geleneksel bol köpüklü Türk kahvesi.',
                        'desc_en' => 'Traditional Turkish coffee served with Turkish delight and water.',
                        'price' => 80.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1578374173705-969cbe6f2d6b?w=800&q=80',
                        'badge' => 'Geleneksel',
                        'calories' => 15,
                        'prep_time' => 5,
                        'allergens' => '',
                        'is_featured' => 1
                    ],
                    [
                        'name' => 'Nescafe',
                        'name_en' => 'Nescafe Coffee',
                        'name_ar' => 'نسكافيه كلاسيك ساخن',
                        'name_ru' => 'Нескафе',
                        'name_de' => 'Nescafe',
                        'desc' => 'Klasik sıcak Nescafe (Sütlü veya Sade).',
                        'desc_en' => 'Classic instant Nescafe coffee (with or without milk).',
                        'price' => 125.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1541167760496-1628856ab772?w=800&q=80',
                        'badge' => '',
                        'calories' => 50,
                        'prep_time' => 2,
                        'allergens' => 'Laktoz',
                        'is_featured' => 0
                    ],
                    [
                        'name' => 'Cappuccino',
                        'name_en' => 'Cappuccino',
                        'name_ar' => 'كابتشينو برغوة كريمية',
                        'name_ru' => 'Капучино',
                        'name_de' => 'Cappuccino',
                        'desc' => 'Espresso, buharda ısıtılmış süt ve kadifemsi süt köpüğü.',
                        'desc_en' => 'Espresso topped with steamed milk and rich velvety foam.',
                        'price' => 200.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1534778101976-62847782c213?w=800&q=80',
                        'badge' => '',
                        'calories' => 130,
                        'prep_time' => 4,
                        'allergens' => 'Laktoz',
                        'is_featured' => 0
                    ],
                    [
                        'name' => 'Latte',
                        'name_en' => 'Caffe Latte',
                        'name_ar' => 'كافيه لاتيه',
                        'name_ru' => 'Латте',
                        'name_de' => 'Caffe Latte',
                        'desc' => 'Espresso ve yumuşak içimli sıcak sütün buluşması.',
                        'desc_en' => 'Smooth espresso combined with steamed milk and light foam.',
                        'price' => 175.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1570968915860-54d5c301fa9f?w=800&q=80',
                        'badge' => '',
                        'calories' => 150,
                        'prep_time' => 4,
                        'allergens' => 'Laktoz',
                        'is_featured' => 0
                    ],
                    [
                        'name' => 'Mocha',
                        'name_en' => 'Caffe Mocha',
                        'name_ar' => 'كافيه موكا بالشوكولاتة',
                        'name_ru' => 'Мокка',
                        'name_de' => 'Caffe Mocha',
                        'desc' => 'Espresso, sıcak çikolata sosu, süt ve süt köpüğü.',
                        'desc_en' => 'Espresso with rich chocolate sauce, steamed milk and foam.',
                        'price' => 200.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1578314670553-33350f601b1d?w=800&q=80',
                        'badge' => '',
                        'calories' => 240,
                        'prep_time' => 4,
                        'allergens' => 'Laktoz',
                        'is_featured' => 0
                    ],
                    [
                        'name' => 'Iced Americano',
                        'name_en' => 'Iced Americano',
                        'name_ar' => 'آيس أمريكانو مثلج',
                        'name_ru' => 'Айс Американо',
                        'name_de' => 'Iced Americano',
                        'desc' => 'Buz dolu bardakta çift shot espresso ve soğuk su.',
                        'desc_en' => 'Chilled double shot espresso over ice and cold water.',
                        'price' => 150.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1517701550927-30cf4ba1dba5?w=800&q=80',
                        'badge' => 'Soğuk Kahve',
                        'calories' => 10,
                        'prep_time' => 3,
                        'allergens' => '',
                        'is_featured' => 0
                    ],
                    [
                        'name' => 'Iced Latte',
                        'name_en' => 'Iced Latte',
                        'name_ar' => 'آيس لاتيه بارد',
                        'name_ru' => 'Айс Латте',
                        'name_de' => 'Iced Latte',
                        'desc' => 'Espresso, soğuk süt ve bol buz ile ferahlatıcı lezzet.',
                        'desc_en' => 'Espresso layered with cold milk and ice cubes.',
                        'price' => 175.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1517701550927-30cf4ba1dba5?w=800&q=80',
                        'badge' => 'Favori Soğuk',
                        'calories' => 130,
                        'prep_time' => 3,
                        'allergens' => 'Laktoz',
                        'is_featured' => 1
                    ],
                    [
                        'name' => 'Iced Mocha',
                        'name_en' => 'Iced Mocha',
                        'name_ar' => 'آيس موكا بالشوكولاتة والثلج',
                        'name_ru' => 'Айс Мокка',
                        'name_de' => 'Iced Mocha',
                        'desc' => 'Çikolata sosu, taze espresso, soğuk süt ve buz.',
                        'desc_en' => 'Rich chocolate sauce, espresso, cold milk over ice.',
                        'price' => 200.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1578314670553-33350f601b1d?w=800&q=80',
                        'badge' => '',
                        'calories' => 230,
                        'prep_time' => 4,
                        'allergens' => 'Laktoz',
                        'is_featured' => 0
                    ],
                    [
                        'name' => 'Frappe Çeşitleri',
                        'name_en' => 'Frappe Selection',
                        'name_ar' => 'فرابيه بارد برغوة كثيفة',
                        'name_ru' => 'Фраппе в ассортименте',
                        'name_de' => 'Frappe Auswahl',
                        'desc' => 'Buz gibi köpüklü soğuk Frappe kahvesi.',
                        'desc_en' => 'Refreshing frothy iced Greek style frappe coffee.',
                        'price' => 200.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1461023058943-07fcbe16d735?w=800&q=80',
                        'badge' => 'Köpüklü & Buzlu',
                        'calories' => 160,
                        'prep_time' => 4,
                        'allergens' => 'Laktoz',
                        'is_featured' => 0
                    ],
                    [
                        'name' => 'Cheesecake',
                        'name_en' => 'Fresh Cheesecake of the Day',
                        'name_ar' => 'تشيز كيك حلوى اليوم الطازجة',
                        'name_ru' => 'Чизкейк (Десерт дня)',
                        'name_de' => 'Käsekuchen (Tagesdessert)',
                        'desc' => 'Günün tatlı seçeneği: San Sebastian, Frambuazlı veya Limonlu taze cheesecake.',
                        'desc_en' => 'Fresh cake of the day: San Sebastian, Raspberry or Lemon cheesecake.',
                        'price' => 250.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1533134242443-d4fd215305ad?w=800&q=80',
                        'badge' => 'Günün Tatlısı 🍰',
                        'calories' => 420,
                        'prep_time' => 3,
                        'allergens' => 'Gluten, Laktoz, Yumurta',
                        'is_featured' => 1
                    ]
                ]
            ],
            // 11. BİRALAR
            [
                'name' => 'Biralar',
                'name_en' => 'Beers',
                'name_ar' => 'البيرة',
                'name_ru' => 'Пиво',
                'name_de' => 'Biere',
                'slug' => 'biralar',
                'icon' => 'beer-mug-empty',
                'image' => 'https://images.unsplash.com/photo-1535958636474-b021ee887b13?w=600&q=80',
                'sort_order' => 11,
                'products' => [
                    [
                        'name' => 'Tuborg Fıçı 33 cl',
                        'name_en' => 'Tuborg Draft Beer 33 cl',
                        'name_ar' => 'توبورغ برميل 33 مل',
                        'name_ru' => 'Туборг разливное 33 сл',
                        'name_de' => 'Tuborg Fassbier 33 cl',
                        'desc' => 'Buz gibi taze fıçı bira (33 cl).',
                        'desc_en' => 'Crisp and cold draft beer (33 cl).',
                        'price' => 150.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1535958636474-b021ee887b13?w=800&q=80',
                        'badge' => 'Fıçı',
                        'calories' => 140,
                        'prep_time' => 2,
                        'allergens' => 'Gluten',
                        'is_featured' => 0
                    ],
                    [
                        'name' => 'Tuborg Fıçı 50 cl',
                        'name_en' => 'Tuborg Draft Beer 50 cl',
                        'name_ar' => 'توبورغ برميل 50 مل',
                        'name_ru' => 'Туборг разливное 50 сл',
                        'name_de' => 'Tuborg Fassbier 50 cl',
                        'desc' => 'Buz gibi taze fıçı bira (50 cl).',
                        'desc_en' => 'Crisp and cold draft beer (50 cl).',
                        'price' => 200.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1535958636474-b021ee887b13?w=800&q=80',
                        'badge' => 'Popüler Fıçı',
                        'calories' => 210,
                        'prep_time' => 2,
                        'allergens' => 'Gluten',
                        'is_featured' => 1
                    ],
                    [
                        'name' => 'Carlsberg Fıçı 50 cl',
                        'name_en' => 'Carlsberg Draft Beer 50 cl',
                        'name_ar' => 'كارلسبيرغ برميل 50 مل',
                        'name_ru' => 'Карлсберг разливное 50 сл',
                        'name_de' => 'Carlsberg Fassbier 50 cl',
                        'desc' => 'Premium Danimarka fıçı birası (50 cl).',
                        'desc_en' => 'Premium Danish draft lager (50 cl).',
                        'price' => 250.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1535958636474-b021ee887b13?w=800&q=80',
                        'badge' => 'Premium Fıçı',
                        'calories' => 215,
                        'prep_time' => 2,
                        'allergens' => 'Gluten',
                        'is_featured' => 1
                    ],
                    [
                        'name' => 'Tuborg Şişe',
                        'name_en' => 'Tuborg Gold Bottle 50 cl',
                        'name_ar' => 'توبورغ زجاجة 50 مل',
                        'name_ru' => 'Туборг Голд бутылочное 50 сл',
                        'name_de' => 'Tuborg Flasche 50 cl',
                        'desc' => 'Tuborg Gold 50 cl şişe bira.',
                        'desc_en' => 'Tuborg Gold 50 cl bottled lager.',
                        'price' => 200.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1608270199043-a616428c0576?w=800&q=80',
                        'badge' => '',
                        'calories' => 210,
                        'prep_time' => 2,
                        'allergens' => 'Gluten',
                        'is_featured' => 0
                    ],
                    [
                        'name' => 'Tuborg Filtresiz',
                        'name_en' => 'Tuborg Unfiltered 50 cl',
                        'name_ar' => 'توبورغ غير مفلتر 50 مل',
                        'name_ru' => 'Туборг нефильтрованное 50 сл',
                        'name_de' => 'Tuborg Ungefiltert 50 cl',
                        'desc' => 'Yoğun aromalı Tuborg Filtresiz 50 cl şişe bira.',
                        'desc_en' => 'Rich cloudy unfiltered lager 50 cl bottle.',
                        'price' => 250.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1608270199043-a616428c0576?w=800&q=80',
                        'badge' => 'Filtresiz',
                        'calories' => 225,
                        'prep_time' => 2,
                        'allergens' => 'Gluten',
                        'is_featured' => 0
                    ],
                    [
                        'name' => 'Carlsberg Şişe',
                        'name_en' => 'Carlsberg Bottle 50 cl',
                        'name_ar' => 'كارلسبيرغ زجاجة 50 مل',
                        'name_ru' => 'Карлсберг бутылочное 50 сл',
                        'name_de' => 'Carlsberg Flasche 50 cl',
                        'desc' => 'Carlsberg 50 cl şişe bira.',
                        'desc_en' => 'Carlsberg 50 cl bottled beer.',
                        'price' => 250.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1608270199043-a616428c0576?w=800&q=80',
                        'badge' => '',
                        'calories' => 210,
                        'prep_time' => 2,
                        'allergens' => 'Gluten',
                        'is_featured' => 0
                    ],
                    [
                        'name' => 'Carlsberg Luna',
                        'name_en' => 'Carlsberg Luna 50 cl',
                        'name_ar' => 'كارلسبيرغ لونا 50 مل',
                        'name_ru' => 'Карлсберг Луна 50 сл',
                        'name_de' => 'Carlsberg Luna 50 cl',
                        'desc' => 'Carlsberg Luna 50 cl şişe bira.',
                        'desc_en' => 'Carlsberg Luna dry hopped lager 50 cl bottle.',
                        'price' => 250.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1608270199043-a616428c0576?w=800&q=80',
                        'badge' => '',
                        'calories' => 210,
                        'prep_time' => 2,
                        'allergens' => 'Gluten',
                        'is_featured' => 0
                    ],
                    [
                        'name' => 'Tuborg Ice',
                        'name_en' => 'Tuborg Ice Bottle',
                        'name_ar' => 'توبورغ آيس زجاجة',
                        'name_ru' => 'Туборг Айс',
                        'name_de' => 'Tuborg Ice',
                        'desc' => 'Tuborg Ice ekstra ferahlatıcı şişe bira.',
                        'desc_en' => 'Tuborg Ice extra refreshing bottled lager.',
                        'price' => 250.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1608270199043-a616428c0576?w=800&q=80',
                        'badge' => 'Buz Gibi',
                        'calories' => 195,
                        'prep_time' => 2,
                        'allergens' => 'Gluten',
                        'is_featured' => 0
                    ],
                    [
                        'name' => 'Sol',
                        'name_en' => 'Sol Mexican Beer 33 cl',
                        'name_ar' => 'بيرة سول المكسيكية',
                        'name_ru' => 'Мексиканское пиво Сол',
                        'name_de' => 'Sol Mexikanisches Bier',
                        'desc' => 'Meksika\'nın ferahlatıcı hafif lager birası (33 cl şişe).',
                        'desc_en' => 'Mexican refreshing crisp lager bottled beer (33 cl).',
                        'price' => 250.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1608270199043-a616428c0576?w=800&q=80',
                        'badge' => 'Meksika',
                        'calories' => 140,
                        'prep_time' => 2,
                        'allergens' => 'Gluten',
                        'is_featured' => 0
                    ],
                    [
                        'name' => 'Heineken',
                        'name_en' => 'Heineken Premium Beer 33 cl',
                        'name_ar' => 'هاينكن بريميوم 33 مل',
                        'name_ru' => 'Хайнекен 33 сл',
                        'name_de' => 'Heineken Premium 33 cl',
                        'desc' => 'Dünyaca ünlü Hollanda lager birası (33 cl şişe).',
                        'desc_en' => 'World famous Dutch premium lager bottled beer (33 cl).',
                        'price' => 300.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1608270199043-a616428c0576?w=800&q=80',
                        'badge' => 'İthal',
                        'calories' => 145,
                        'prep_time' => 2,
                        'allergens' => 'Gluten',
                        'is_featured' => 1
                    ],
                    [
                        'name' => 'Desperados',
                        'name_en' => 'Desperados Tequila Flavored Beer',
                        'name_ar' => 'ديسبيرادوس بنكهة التيكيلا',
                        'name_ru' => 'Десперадос со вкусом текилы',
                        'name_de' => 'Desperados Tequila Bier',
                        'desc' => 'Tekila aromalı benzersiz lezzetli lager bira (33 cl şişe).',
                        'desc_en' => 'Tequila flavored distinctive lager beer (33 cl).',
                        'price' => 300.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1608270199043-a616428c0576?w=800&q=80',
                        'badge' => 'Özel Lezzet',
                        'calories' => 170,
                        'prep_time' => 2,
                        'allergens' => 'Gluten',
                        'is_featured' => 1
                    ],
                    [
                        'name' => 'Blanc',
                        'name_en' => 'Kronenbourg 1664 Blanc 33 cl',
                        'name_ar' => 'كرونينبورغ 1664 بلانك بيرة القمح الفرنسية',
                        'name_ru' => 'Кроненбург 1664 Бланк',
                        'name_de' => '1664 Blanc Weizenbier',
                        'desc' => 'Fransız narenciye ve kişniş aromalı premium buğday birası (33 cl).',
                        'desc_en' => 'French wheat beer with hints of citrus and coriander (33 cl).',
                        'price' => 300.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1608270199043-a616428c0576?w=800&q=80',
                        'badge' => 'Buğday Birası',
                        'calories' => 150,
                        'prep_time' => 2,
                        'allergens' => 'Gluten',
                        'is_featured' => 1
                    ],
                    [
                        'name' => 'Guinness Kutu',
                        'name_en' => 'Guinness Draught Stout Can 44 cl',
                        'name_ar' => 'غينيس بيرة سوداء أيرلندية 44 مل',
                        'name_ru' => 'Гиннесс стаут банка 44 сл',
                        'name_de' => 'Guinness Extra Stout Dose 44 cl',
                        'desc' => 'İrlanda\'nın efsanevi kremamsı köpüklü siyah birası (44 cl kutu).',
                        'desc_en' => 'Legendary rich and creamy Irish dry stout (44 cl can).',
                        'price' => 350.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1567696911980-2eed69a46042?w=800&q=80',
                        'badge' => 'İrlanda Stout ⭐',
                        'calories' => 180,
                        'prep_time' => 2,
                        'allergens' => 'Gluten',
                        'is_featured' => 1
                    ]
                ]
            ],
            // 12. ŞARAPLAR (KADEH)
            [
                'name' => 'Şaraplar (Kadeh)',
                'name_en' => 'Wines by the Glass',
                'name_ar' => 'النبيذ بالكأس',
                'name_ru' => 'Вина по бокалам',
                'name_de' => 'Weine im Glas',
                'slug' => 'saraplar',
                'icon' => 'wine-glass',
                'image' => 'https://images.unsplash.com/photo-1510812431401-41d2bd2722f3?w=600&q=80',
                'sort_order' => 12,
                'products' => [
                    [
                        'name' => 'Kırmızı Şarap (Kadeh)',
                        'name_en' => 'Red Wine (Glass)',
                        'name_ar' => 'كأس نبيذ أحمر فاخر',
                        'name_ru' => 'Красное вино (бокал)',
                        'name_de' => 'Rotwein (Glas)',
                        'desc' => 'Seçkin yerli bağlardan kadeh kırmızı şarap. (Şişe seçenekleri için garsonunuza danışınız).',
                        'desc_en' => 'Selected Turkish regional red wine by the glass.',
                        'price' => 400.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1506377247377-2a5b3b417ebb?w=800&q=80',
                        'badge' => 'Kadeh',
                        'calories' => 125,
                        'prep_time' => 2,
                        'allergens' => 'Sülfit',
                        'is_featured' => 1
                    ],
                    [
                        'name' => 'Beyaz Şarap (Kadeh)',
                        'name_en' => 'White Wine (Glass)',
                        'name_ar' => 'كأس نبيذ أبيض منعش',
                        'name_ru' => 'Белое вино (бокал)',
                        'name_de' => 'Weißwein (Glas)',
                        'desc' => 'Soğuk servis edilen aromatik kadeh beyaz şarap.',
                        'desc_en' => 'Chilled aromatic white wine by the glass.',
                        'price' => 400.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1584916201218-f4242ceb4809?w=800&q=80',
                        'badge' => 'Kadeh',
                        'calories' => 120,
                        'prep_time' => 2,
                        'allergens' => 'Sülfit',
                        'is_featured' => 1
                    ],
                    [
                        'name' => 'Rosé Şarap (Kadeh)',
                        'name_en' => 'Rosé Wine (Glass)',
                        'name_ar' => 'كأس نبيذ روزيه وردي',
                        'name_ru' => 'Розовое вино (бокал)',
                        'name_de' => 'Roséwein (Glas)',
                        'desc' => 'Meyvemsi ve canlı kadeh pembe şarap.',
                        'desc_en' => 'Fruity and fresh rosé wine by the glass.',
                        'price' => 400.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1558001373-7b93ee48ffa0?w=800&q=80',
                        'badge' => 'Kadeh',
                        'calories' => 120,
                        'prep_time' => 2,
                        'allergens' => 'Sülfit',
                        'is_featured' => 0
                    ],
                    [
                        'name' => 'Blush Şarap (Kadeh)',
                        'name_en' => 'Blush Wine (Glass)',
                        'name_ar' => 'كأس نبيذ بلش خفيف',
                        'name_ru' => 'Блаш вино (бокал)',
                        'name_de' => 'Blush Wein (Glas)',
                        'desc' => 'Hafif gövdeli, zarif ve ferahlatıcı kadeh blush şarap.',
                        'desc_en' => 'Delicate, crisp and refreshing blush wine by the glass.',
                        'price' => 400.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1558001373-7b93ee48ffa0?w=800&q=80',
                        'badge' => 'Kadeh',
                        'calories' => 115,
                        'prep_time' => 2,
                        'allergens' => 'Sülfit',
                        'is_featured' => 0
                    ]
                ]
            ],
            // 13. MARE & MONTE CLASSIC COCKTAILS
            [
                'name' => 'Kokteyller',
                'name_en' => 'Classic & Signature Cocktails',
                'name_ar' => 'الكوكتيلات الكلاسيكية والخاصة',
                'name_ru' => 'Классические и авторские коктейли',
                'name_de' => 'Klassische & Signature Cocktails',
                'slug' => 'kokteyller',
                'icon' => 'martini-glass-citrus',
                'image' => 'https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b?w=600&q=80',
                'sort_order' => 13,
                'products' => [
                    [
                        'name' => 'Negroni',
                        'name_en' => 'Negroni',
                        'name_ar' => 'نيغروني إيطالي كلاسيك',
                        'name_ru' => 'Негрони',
                        'name_de' => 'Negroni',
                        'desc' => 'Gin, Campari ve Sweet Vermouth\'un eşit oranlarda birleşiminden doğan İtalyan klasiği. Güçlü, acımsı ve aromatik.',
                        'desc_en' => 'Gin, Campari, Sweet Vermouth. Bold, bitter-sweet and deeply aromatic Italian classic.',
                        'price' => 550.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?w=800&q=80',
                        'badge' => 'Klasik',
                        'calories' => 195,
                        'prep_time' => 4,
                        'allergens' => '',
                        'is_featured' => 1
                    ],
                    [
                        'name' => 'Whiskey Sour',
                        'name_en' => 'Whiskey Sour',
                        'name_ar' => 'ويسكي ساور بالليمون',
                        'name_ru' => 'Виски Сауэр',
                        'name_de' => 'Whiskey Sour',
                        'desc' => 'Bourbon viski, limon ve şeker şurubunun dengesiyle hazırlanan zamansız klasik. Kadifemsi köpüğü ve canlı asiditesiyle öne çıkar. İçerik: Bourbon, Limon Suyu, Şeker Şurubu, Yumurta (opsiyonel).',
                        'desc_en' => 'Bourbon, fresh lemon juice, sugar syrup, optional egg white foam.',
                        'price' => 650.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b?w=800&q=80',
                        'badge' => 'Popüler ⭐',
                        'calories' => 180,
                        'prep_time' => 5,
                        'allergens' => 'Yumurta',
                        'is_featured' => 1
                    ],
                    [
                        'name' => 'Mojito',
                        'name_en' => 'Classic Cuban Mojito',
                        'name_ar' => 'موهيتو كوبي كلاسيك بالنعناع',
                        'name_ru' => 'Мохито',
                        'name_de' => 'Mojito',
                        'desc' => 'Beyaz rom, nane, lime ve soda ile hazırlanan Küba klasiği. Ferahlatıcı ve her zaman favori.',
                        'desc_en' => 'White rum, fresh mint, lime wedges, sugar and sparkling soda.',
                        'price' => 550.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?w=800&q=80',
                        'badge' => 'Çok Satan',
                        'calories' => 170,
                        'prep_time' => 4,
                        'allergens' => '',
                        'is_featured' => 1
                    ],
                    [
                        'name' => 'Margarita',
                        'name_en' => 'Classic Margarita',
                        'name_ar' => 'مارغريتا مكسيكية بالتيكيلا',
                        'name_ru' => 'Маргарита',
                        'name_de' => 'Margarita',
                        'desc' => 'Tekilanın limon ve triple sec ile buluştuğu, dünyanın en çok sipariş edilen klasik kokteyllerinden biri.',
                        'desc_en' => 'Tequila, triple sec, fresh lime juice with salted rim.',
                        'price' => 550.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b?w=800&q=80',
                        'badge' => 'Klasik',
                        'calories' => 165,
                        'prep_time' => 4,
                        'allergens' => '',
                        'is_featured' => 1
                    ],
                    [
                        'name' => 'Espresso Martini',
                        'name_en' => 'Espresso Martini',
                        'name_ar' => 'إسبريسو مارتيني بالقهوة والفودكا',
                        'name_ru' => 'Эспрессо Мартини',
                        'name_de' => 'Espresso Martini',
                        'desc' => 'Vodka, taze espresso ve kahve likörü ile gece sizi uyaracak modern klasik.',
                        'desc_en' => 'Vodka, freshly brewed espresso and coffee liqueur.',
                        'price' => 550.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1545438102-799c3991ffb2?w=800&q=80',
                        'badge' => 'Kahveli Kokteyl',
                        'calories' => 190,
                        'prep_time' => 4,
                        'allergens' => '',
                        'is_featured' => 1
                    ],
                    [
                        'name' => 'Dry Martini',
                        'name_en' => 'Classic Dry Martini',
                        'name_ar' => 'دراي مارتيني بالزيتون',
                        'name_ru' => 'Драй Мартини',
                        'name_de' => 'Dry Martini',
                        'desc' => 'Sadelik ve zarafetin sembolü. Gin ve dry vermouth\'un kusursuz uyumu.',
                        'desc_en' => 'Gin, dry vermouth and green olive garnish.',
                        'price' => 550.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1575023782549-62ca0d244b39?w=800&q=80',
                        'badge' => '',
                        'calories' => 160,
                        'prep_time' => 3,
                        'allergens' => '',
                        'is_featured' => 0
                    ],
                    [
                        'name' => 'Aperol Spritz',
                        'name_en' => 'Aperol Spritz',
                        'name_ar' => 'أبيرول سبريتز الإيطالي الصيفي',
                        'name_ru' => 'Апероль Спритц',
                        'name_de' => 'Aperol Spritz',
                        'desc' => 'Aperol, prosecco ve soda ile hazırlanan hafif, ferahlatıcı ve yaz akşamlarının vazgeçilmezi.',
                        'desc_en' => 'Aperol, prosecco, splash of club soda and fresh orange slice.',
                        'price' => 550.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1560512823-829485b8bf24?w=800&q=80',
                        'badge' => 'Yaz Favorisi ☀️',
                        'calories' => 135,
                        'prep_time' => 3,
                        'allergens' => '',
                        'is_featured' => 1
                    ],
                    [
                        'name' => 'Gin Smash',
                        'name_en' => 'Gin Basil Smash',
                        'name_ar' => 'جين سماش بالريحان والليمون',
                        'name_ru' => 'Джин Смэш',
                        'name_de' => 'Gin Smash',
                        'desc' => 'Taze fesleğen, limon ve gin ile hazırlanan aromatik ve ferahlatıcı bir klasik.',
                        'desc_en' => 'Gin, fresh muddled basil leaves, lemon juice and sugar syrup.',
                        'price' => 550.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b?w=800&q=80',
                        'badge' => 'Aromatik',
                        'calories' => 165,
                        'prep_time' => 4,
                        'allergens' => '',
                        'is_featured' => 0
                    ],
                    [
                        'name' => 'Aegean Bloom',
                        'name_en' => 'Aegean Bloom (Signature)',
                        'name_ar' => 'إيجيان بلوم (كوكتيل بحر إيجة الخاص)',
                        'name_ru' => 'Aegean Bloom (Фирменный)',
                        'name_de' => 'Aegean Bloom (Signature)',
                        'desc' => 'Ege\'nin aromatik ruhundan ilham alan hafif ve zarif bir kokteyl. Otların ve narenciyenin dengeli uyumu ile ferahlatıcı bir imza lezzet. İçerik: Vodka, Triple Sec, Reyhan Şerbeti, Lime, Zeytinyağı.',
                        'desc_en' => 'Signature cocktail: Vodka, Triple Sec, purple basil syrup, lime juice, dash of olive oil.',
                        'price' => 650.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b?w=800&q=80',
                        'badge' => 'İmza Kokteyl ⭐',
                        'calories' => 185,
                        'prep_time' => 5,
                        'allergens' => '',
                        'is_featured' => 1
                    ],
                    [
                        'name' => 'Scarlet Breeze',
                        'name_en' => 'Scarlet Breeze (Signature)',
                        'name_ar' => 'سكارليت بريز بالتوت والزنجبيل',
                        'name_ru' => 'Scarlet Breeze (Фирменный)',
                        'name_de' => 'Scarlet Breeze',
                        'desc' => 'Frambuazın canlı aroması, narenciye ve fesleğenle buluşarak meyvemsi ve ferah bir karakter sunar. İçerik: Gin, Limon Suyu, Frambuaz Likörü, Zencefil Şurubu, Fesleğen.',
                        'desc_en' => 'Gin, lemon juice, raspberry liqueur, ginger syrup, fresh basil.',
                        'price' => 650.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?w=800&q=80',
                        'badge' => 'İmza Kokteyl ⭐',
                        'calories' => 190,
                        'prep_time' => 5,
                        'allergens' => '',
                        'is_featured' => 1
                    ],
                    [
                        'name' => 'Gin Fizz',
                        'name_en' => 'Gin Fizz',
                        'name_ar' => 'جين فيز المنعش',
                        'name_ru' => 'Джин Физз',
                        'name_de' => 'Gin Fizz',
                        'desc' => 'Gin, limon suyu, şeker şurubu ve soda ile hazırlanan narenciye ferahlığı.',
                        'desc_en' => 'Gin, fresh lemon juice, simple syrup topped with sparkling soda.',
                        'price' => 550.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b?w=800&q=80',
                        'badge' => '',
                        'calories' => 155,
                        'prep_time' => 3,
                        'allergens' => '',
                        'is_featured' => 0
                    ],
                    [
                        'name' => 'White Russian',
                        'name_en' => 'White Russian',
                        'name_ar' => 'وايت راشن بالكريمة والقهوة',
                        'name_ru' => 'Белый русский',
                        'name_de' => 'White Russian',
                        'desc' => 'Vodka, kahve likörü ve krema ile hazırlanan yumuşak ve tatlı karakterli klasik.',
                        'desc_en' => 'Vodka, coffee liqueur and rich fresh cream.',
                        'price' => 650.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1545438102-799c3991ffb2?w=800&q=80',
                        'badge' => 'Kremamsı',
                        'calories' => 240,
                        'prep_time' => 4,
                        'allergens' => 'Laktoz',
                        'is_featured' => 0
                    ],
                    [
                        'name' => 'Black Russian',
                        'name_en' => 'Black Russian',
                        'name_ar' => 'بلاك راشن بالفودكا والقهوة',
                        'name_ru' => 'Черный русский',
                        'name_de' => 'Black Russian',
                        'desc' => 'Vodka ve kahve likörünün güçlü ve sade birlikteliği.',
                        'desc_en' => 'Bold combination of vodka and rich coffee liqueur over ice.',
                        'price' => 650.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1545438102-799c3991ffb2?w=800&q=80',
                        'badge' => '',
                        'calories' => 190,
                        'prep_time' => 3,
                        'allergens' => '',
                        'is_featured' => 0
                    ],
                    [
                        'name' => 'Boulevardier',
                        'name_en' => 'Boulevardier',
                        'name_ar' => 'بوليفاردييه بالويسكي والكامباري',
                        'name_ru' => 'Бульвардье',
                        'name_de' => 'Boulevardier',
                        'desc' => 'Negroni\'nin viskiyle güçlendirilmiş, daha gövdeli ve sıcak versiyonu. İçerik: Bourbon, Campari, Sweet Vermouth.',
                        'desc_en' => 'Bourbon, Campari, Sweet Vermouth. Rich, bold whiskey version of Negroni.',
                        'price' => 650.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?w=800&q=80',
                        'badge' => 'Özel',
                        'calories' => 200,
                        'prep_time' => 4,
                        'allergens' => '',
                        'is_featured' => 0
                    ],
                    [
                        'name' => 'Lynchburg Lemonade',
                        'name_en' => 'Lynchburg Lemonade',
                        'name_ar' => 'لينشبورغ ليموناضة بالويسكي',
                        'name_ru' => 'Линчбург Лимонад',
                        'name_de' => 'Lynchburg Lemonade',
                        'desc' => 'Jack Daniel\'s, triple sec, limon ve şurup ile hazırlanan tatlı-ekşi dengesi mükemmel klasik.',
                        'desc_en' => 'Jack Daniel\'s whiskey, triple sec, lemon juice, sugar syrup, sprite.',
                        'price' => 550.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b?w=800&q=80',
                        'badge' => 'Çok Sevilen',
                        'calories' => 195,
                        'prep_time' => 4,
                        'allergens' => '',
                        'is_featured' => 1
                    ],
                    [
                        'name' => 'Long Island Iced Tea',
                        'name_en' => 'Long Island Iced Tea',
                        'name_ar' => 'لونغ آيلاند آيس تي القوي',
                        'name_ru' => 'Лонг Айленд Айс Ти',
                        'name_de' => 'Long Island Iced Tea',
                        'desc' => 'Beş beyaz içkinin, turunçgil ve cola ile birleştiği efsane kokteyl. İçerik: Vodka, Gin, Rom, Tekila, Triple Sec, Limon, Cola.',
                        'desc_en' => 'Vodka, Gin, White Rum, Tequila, Triple Sec, lemon juice, splash of cola.',
                        'price' => 650.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?w=800&q=80',
                        'badge' => 'Efsane Klasik',
                        'calories' => 260,
                        'prep_time' => 5,
                        'allergens' => '',
                        'is_featured' => 1
                    ],
                    [
                        'name' => 'Cuba Libre',
                        'name_en' => 'Cuba Libre',
                        'name_ar' => 'كوبا ليبري بالروم والليمون والكولا',
                        'name_ru' => 'Куба Либре',
                        'name_de' => 'Cuba Libre',
                        'desc' => 'Rom, cola ve lime ile Küba\'nın en tanınan uzun içkisi.',
                        'desc_en' => 'Rum, fresh lime juice and Coca-Cola over ice.',
                        'price' => 550.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?w=800&q=80',
                        'badge' => '',
                        'calories' => 175,
                        'prep_time' => 3,
                        'allergens' => '',
                        'is_featured' => 0
                    ],
                    [
                        'name' => 'Olympus Sour',
                        'name_en' => 'Olympus Sour (Signature)',
                        'name_ar' => 'أوليمبوس ساور (كوكتيل أسطوري)',
                        'name_ru' => 'Olympus Sour (Фирменный)',
                        'name_de' => 'Olympus Sour',
                        'desc' => 'Bitkisel aromalar ve Safari likörünün dengesiyle hazırlanan güçlü, kadifemsi ve canlı bir sour. İçerik: Tekila, Cin, Safari Likörü, Reyhan Şurubu, Lime.',
                        'desc_en' => 'Tequila, Gin, Safari liqueur, purple basil syrup, fresh lime juice.',
                        'price' => 650.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b?w=800&q=80',
                        'badge' => 'İmza Kokteyl ⭐',
                        'calories' => 205,
                        'prep_time' => 5,
                        'allergens' => '',
                        'is_featured' => 1
                    ],
                    [
                        'name' => 'Moonflower',
                        'name_en' => 'Moonflower (Signature)',
                        'name_ar' => 'مون فلاور بزهر الخمان والشمام',
                        'name_ru' => 'Moonflower (Фирменный)',
                        'name_de' => 'Moonflower',
                        'desc' => 'Mürver çiçeği ve kavunun zarif uyumuyla hazırlanan yumuşak içimli, çiçeksi ve meyvemsi premium kokteyl. İçerik: Vodka, St-Germain, Kavun Likörü, Limon.',
                        'desc_en' => 'Vodka, St-Germain elderflower, melon liqueur, fresh lemon juice.',
                        'price' => 650.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b?w=800&q=80',
                        'badge' => 'İmza Kokteyl ⭐',
                        'calories' => 195,
                        'prep_time' => 5,
                        'allergens' => '',
                        'is_featured' => 1
                    ],
                    [
                        'name' => 'Vanilla Noir',
                        'name_en' => 'Vanilla Noir (Signature)',
                        'name_ar' => 'فانيليا نوار بالويسكي والآيس كريم',
                        'name_ru' => 'Vanilla Noir (Фирменный)',
                        'name_de' => 'Vanilla Noir',
                        'desc' => 'Vanilya ve viskinin buluştuğu yoğun aromalı, kadifemsi dokulu tatlı karakterli bir kokteyl. İçerik: Viski, Vanilya Şurubu, Vanilyalı Dondurma, Krema.',
                        'desc_en' => 'Whiskey, vanilla syrup, rich vanilla ice cream, fresh cream.',
                        'price' => 650.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1545438102-799c3991ffb2?w=800&q=80',
                        'badge' => 'İmza Kokteyl ⭐',
                        'calories' => 290,
                        'prep_time' => 5,
                        'allergens' => 'Laktoz',
                        'is_featured' => 1
                    ]
                ]
            ],
            // 14. ALKOLLÜ İÇECEKLER & RAKI
            [
                'name' => 'Alkollü İçecekler & Rakı',
                'name_en' => 'Spirits & Traditional Raki',
                'name_ar' => 'المشروبات الروحية والعرق التركي',
                'name_ru' => 'Крепкий алкоголь и ракы',
                'name_de' => 'Spirituosen & Raki',
                'slug' => 'alkollu-icecekler-raki',
                'icon' => 'wine-bottle',
                'image' => 'https://images.unsplash.com/photo-1527061011665-3652c757a4d4?w=600&q=80',
                'sort_order' => 14,
                'products' => [
                    [
                        'name' => 'Absolute Votka Duble',
                        'name_en' => 'Absolut Vodka Double',
                        'name_ar' => 'فودكا أبسولوت دبل مع صودا أو تونيك',
                        'name_ru' => 'Абсолют Водка (Двойная)',
                        'name_de' => 'Absolut Wodka Doppel',
                        'desc' => 'Duble Absolut votka (Soda veya Tonik ile servis edilir).',
                        'desc_en' => 'Absolut Vodka double measure served with soda or tonic.',
                        'price' => 350.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1527061011665-3652c757a4d4?w=800&q=80',
                        'badge' => 'Vodka',
                        'calories' => 130,
                        'prep_time' => 2,
                        'allergens' => '',
                        'is_featured' => 0
                    ],
                    [
                        'name' => 'Gordon\'s Gin Tek',
                        'name_en' => 'Gordon\'s Gin Single',
                        'name_ar' => 'جين غوردونز سينغل مع تونيك',
                        'name_ru' => 'Гордонс Джин (Одинарный)',
                        'name_de' => 'Gordon\'s Gin Einzel',
                        'desc' => 'Tek porsiyon Gordon\'s cin (Soda veya Tonik ile servis edilir).',
                        'desc_en' => 'Gordon\'s London dry gin single served with soda or tonic.',
                        'price' => 300.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1527061011665-3652c757a4d4?w=800&q=80',
                        'badge' => 'Gin',
                        'calories' => 110,
                        'prep_time' => 2,
                        'allergens' => '',
                        'is_featured' => 0
                    ],
                    [
                        'name' => 'Gordon\'s Gin Duble',
                        'name_en' => 'Gordon\'s Gin Double',
                        'name_ar' => 'جين غوردونز دبل مع تونيك',
                        'name_ru' => 'Гордонс Джин (Двойной)',
                        'name_de' => 'Gordon\'s Gin Doppel',
                        'desc' => 'Duble porsiyon Gordon\'s cin (Soda veya Tonik ile servis edilir).',
                        'desc_en' => 'Gordon\'s London dry gin double served with soda or tonic.',
                        'price' => 350.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1527061011665-3652c757a4d4?w=800&q=80',
                        'badge' => 'Gin',
                        'calories' => 180,
                        'prep_time' => 2,
                        'allergens' => '',
                        'is_featured' => 0
                    ],
                    [
                        'name' => 'Viski Tek',
                        'name_en' => 'Whiskey Single',
                        'name_ar' => 'ويسكي فاخر سينغل',
                        'name_ru' => 'Виски (Одинарный)',
                        'name_de' => 'Whisky Einzel',
                        'desc' => 'Tek porsiyon kaliteli viski (Buz veya su ile).',
                        'desc_en' => 'Premium whiskey single measure served on the rocks.',
                        'price' => 350.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1527061011665-3652c757a4d4?w=800&q=80',
                        'badge' => 'Viski',
                        'calories' => 115,
                        'prep_time' => 2,
                        'allergens' => '',
                        'is_featured' => 0
                    ],
                    [
                        'name' => 'Viski Duble',
                        'name_en' => 'Whiskey Double',
                        'name_ar' => 'ويسكي فاخر دبل',
                        'name_ru' => 'Виски (Двойной)',
                        'name_de' => 'Whisky Doppel',
                        'desc' => 'Duble porsiyon kaliteli viski (Buz veya su ile).',
                        'desc_en' => 'Premium whiskey double measure served on the rocks.',
                        'price' => 600.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1527061011665-3652c757a4d4?w=800&q=80',
                        'badge' => 'Viski Duble',
                        'calories' => 230,
                        'prep_time' => 2,
                        'allergens' => '',
                        'is_featured' => 1
                    ],
                    [
                        'name' => 'Tekila Shot',
                        'name_en' => 'Tequila Shot',
                        'name_ar' => 'شوت تيكيلا مع الملح والليمون',
                        'name_ru' => 'Шот Текилы',
                        'name_de' => 'Tequila Shot',
                        'desc' => 'Tuz ve limon dilimi eşliğinde tekila shot.',
                        'desc_en' => 'Tequila shot served with salt and lemon slice.',
                        'price' => 250.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b?w=800&q=80',
                        'badge' => 'Shot',
                        'calories' => 65,
                        'prep_time' => 1,
                        'allergens' => '',
                        'is_featured' => 0
                    ],
                    [
                        'name' => 'Jägermeister Shot',
                        'name_en' => 'Jägermeister Shot',
                        'name_ar' => 'شوت ياغرميستر المثلج',
                        'name_ru' => 'Шот Егермейстер',
                        'name_de' => 'Jägermeister Shot',
                        'desc' => 'Buz gibi dondurulmuş Jägermeister shot.',
                        'desc_en' => 'Ice cold Jägermeister herbal shot.',
                        'price' => 250.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b?w=800&q=80',
                        'badge' => 'Shot',
                        'calories' => 70,
                        'prep_time' => 1,
                        'allergens' => '',
                        'is_featured' => 0
                    ],
                    [
                        'name' => 'Votka Shot',
                        'name_en' => 'Vodka Shot',
                        'name_ar' => 'شوت فودكا بارد',
                        'name_ru' => 'Шот Водки',
                        'name_de' => 'Wodka Shot',
                        'desc' => 'Buz gibi soğutulmuş votka shot.',
                        'desc_en' => 'Chilled vodka shot.',
                        'price' => 200.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b?w=800&q=80',
                        'badge' => 'Shot',
                        'calories' => 65,
                        'prep_time' => 1,
                        'allergens' => '',
                        'is_featured' => 0
                    ],
                    [
                        'name' => 'Yeni Rakı Kadeh Tek',
                        'name_en' => 'Yeni Raki Single Glass',
                        'name_ar' => 'عرق يني راكي كأس مفرد',
                        'name_ru' => 'Ени Ракы (Одинарный бокал)',
                        'name_de' => 'Yeni Raki Einzelglas',
                        'desc' => 'Tek kadeh Yeni Rakı (Soğuk su ve buz ile servis edilir).',
                        'desc_en' => 'Single glass Yeni Raki served with chilled water and ice.',
                        'price' => 250.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1527061011665-3652c757a4d4?w=800&q=80',
                        'badge' => 'Rakı',
                        'calories' => 130,
                        'prep_time' => 2,
                        'allergens' => '',
                        'is_featured' => 0
                    ],
                    [
                        'name' => 'Yeni Rakı Kadeh Duble',
                        'name_en' => 'Yeni Raki Double Glass',
                        'name_ar' => 'عرق يني راكي كأس مضاعف',
                        'name_ru' => 'Ени Ракы (Двойной бокал)',
                        'name_de' => 'Yeni Raki Doppelglas',
                        'desc' => 'Duble kadeh Yeni Rakı (Soğuk su ve buz ile servis edilir).',
                        'desc_en' => 'Double glass Yeni Raki served with chilled water and ice.',
                        'price' => 400.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1527061011665-3652c757a4d4?w=800&q=80',
                        'badge' => 'Rakı Duble',
                        'calories' => 250,
                        'prep_time' => 2,
                        'allergens' => '',
                        'is_featured' => 1
                    ],
                    [
                        'name' => 'Yeni Rakı 20 cl',
                        'name_en' => 'Yeni Raki 20 cl Bottle',
                        'name_ar' => 'عرق يني راكي زجاجة 20 مل',
                        'name_ru' => 'Ени Ракы 20 сл',
                        'name_de' => 'Yeni Raki 20 cl Flasche',
                        'desc' => '20 cl Yeni Rakı şişe.',
                        'desc_en' => '20 cl bottle Yeni Raki.',
                        'price' => 1000.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1527061011665-3652c757a4d4?w=800&q=80',
                        'badge' => 'Şişe 20 cl',
                        'calories' => 500,
                        'prep_time' => 2,
                        'allergens' => '',
                        'is_featured' => 0
                    ],
                    [
                        'name' => 'Yeni Rakı 35 cl',
                        'name_en' => 'Yeni Raki 35 cl Bottle',
                        'name_ar' => 'عرق يني راكي زجاجة 35 مل',
                        'name_ru' => 'Ени Ракы 35 сл',
                        'name_de' => 'Yeni Raki 35 cl Flasche',
                        'desc' => '35 cl Yeni Rakı şişe.',
                        'desc_en' => '35 cl bottle Yeni Raki (35 cl).',
                        'price' => 1400.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1527061011665-3652c757a4d4?w=800&q=80',
                        'badge' => 'Şişe 35 cl',
                        'calories' => 875,
                        'prep_time' => 2,
                        'allergens' => '',
                        'is_featured' => 1
                    ],
                    [
                        'name' => 'Yeni Rakı 50 cl',
                        'name_en' => 'Yeni Raki 50 cl Bottle',
                        'name_ar' => 'عرق يني راكي زجاجة 50 مل',
                        'name_ru' => 'Ени Ракы 50 сл',
                        'name_de' => 'Yeni Raki 50 cl Flasche',
                        'desc' => '50 cl Yeni Rakı şişe.',
                        'desc_en' => '50 cl bottle Yeni Raki (50 cl).',
                        'price' => 2000.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1527061011665-3652c757a4d4?w=800&q=80',
                        'badge' => 'Şişe 50 cl',
                        'calories' => 1250,
                        'prep_time' => 2,
                        'allergens' => '',
                        'is_featured' => 0
                    ],
                    [
                        'name' => 'Yeni Rakı 70 cl',
                        'name_en' => 'Yeni Raki 70 cl Bottle',
                        'name_ar' => 'عرق يني راكي زجاجة 70 مل',
                        'name_ru' => 'Ени Ракы 70 сл',
                        'name_de' => 'Yeni Raki 70 cl Flasche',
                        'desc' => '70 cl Yeni Rakı şişe (Büyük).',
                        'desc_en' => '70 cl bottle Yeni Raki (70 cl).',
                        'price' => 2400.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1527061011665-3652c757a4d4?w=800&q=80',
                        'badge' => 'Şişe 70 cl',
                        'calories' => 1750,
                        'prep_time' => 2,
                        'allergens' => '',
                        'is_featured' => 1
                    ],
                    [
                        'name' => 'Yeni Rakı 100 cl',
                        'name_en' => 'Yeni Raki 100 cl Bottle (1L)',
                        'name_ar' => 'عرق يني راكي زجاجة 100 مل (1 لتر)',
                        'name_ru' => 'Ени Ракы 100 сл (1 Литр)',
                        'name_de' => 'Yeni Raki 100 cl Flasche (1 Liter)',
                        'desc' => '100 cl Yeni Rakı şişe (1 Litre).',
                        'desc_en' => '100 cl bottle Yeni Raki (1 Liter).',
                        'price' => 3000.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1527061011665-3652c757a4d4?w=800&q=80',
                        'badge' => 'Şişe 1 Litre',
                        'calories' => 2500,
                        'prep_time' => 2,
                        'allergens' => '',
                        'is_featured' => 1
                    ]
                ]
            ]
        ];

        $stmtCat = $pdo->prepare("INSERT INTO categories (name, name_en, name_ar, name_ru, name_de, slug, icon, image, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
        $stmtProd = $pdo->prepare("INSERT INTO products (category_id, name, name_en, name_ar, name_ru, name_de, description, desc_en, price, old_price, image, badge, calories, prep_time, allergens, is_available, is_featured, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?)");
        $stmtOpt = $pdo->prepare("INSERT INTO product_options (product_id, group_name, option_name, extra_price, is_required) VALUES (?, ?, ?, ?, ?)");

        foreach ($demoCategories as $cat) {
            $stmtCat->execute([$cat['name'], $cat['name_en'] ?? '', $cat['name_ar'] ?? '', $cat['name_ru'] ?? '', $cat['name_de'] ?? '', $cat['slug'], $cat['icon'], $cat['image'], $cat['sort_order']]);
            $catId = $pdo->lastInsertId();

            $pOrder = 1;
            foreach ($cat['products'] as $p) {
                $stmtProd->execute([
                    $catId,
                    $p['name'],
                    $p['name_en'] ?? '',
                    $p['name_ar'] ?? '',
                    $p['name_ru'] ?? '',
                    $p['name_de'] ?? '',
                    $p['desc'],
                    $p['desc_en'] ?? '',
                    $p['price'],
                    $p['old_price'],
                    $p['image'],
                    $p['badge'],
                    $p['calories'],
                    $p['prep_time'],
                    $p['allergens'],
                    $p['is_featured'],
                    $pOrder++
                ]);
                $prodId = $pdo->lastInsertId();

                if (!empty($p['options'])) {
                    foreach ($p['options'] as $opt) {
                        $stmtOpt->execute([$prodId, $opt['group_name'], $opt['option_name'], $opt['extra_price'], $opt['is_required']]);
                    }
                }
            }
        }
    }

    // 6. Örnek Müşteri Yorumları (Feedback)
    $stmtFbCheck = $pdo->query("SELECT COUNT(*) as cnt FROM feedback");
    if ($stmtFbCheck->fetch()['cnt'] == 0) {
        $demoFb = [
            ['table_number' => '4', 'rating' => 5, 'name' => 'Burak Yılmaz', 'comment' => 'Smokehouse Burger ve trüflü patates inanılmaz lezzetliydi, servis çok hızlı.'],
            ['table_number' => 'B2', 'rating' => 5, 'name' => 'Elif Demir', 'comment' => 'San Sebastian cheesecake ve kahve muhteşemdi, bahçe ortamı çok keyifli.'],
            ['table_number' => '1', 'rating' => 4, 'name' => 'Caner K.', 'comment' => 'Serpme kahvaltı çok zengin ve tazeydi. Teşekkürler.']
        ];
        $stmtF = $pdo->prepare("INSERT INTO feedback (table_number, rating, name, comment, is_read) VALUES (?, ?, ?, ?, 1)");
        foreach ($demoFb as $fb) {
            $stmtF->execute([$fb['table_number'], $fb['rating'], $fb['name'], $fb['comment']]);
        }
    }
}

/**
 * Ayar Getirme Fonksiyonu
 */
function getSetting($key, $default = '') {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $res = $stmt->fetch();
        return $res ? $res['setting_value'] : $default;
    } catch (Exception $e) {
        return $default;
    }
}

/**
 * Ayar Güncelleme Fonksiyonu
 */
function updateSetting($key, $value) {
    global $pdo;
    $isSqlite = (DB_DRIVER === 'sqlite');
    if ($isSqlite) {
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON CONFLICT(setting_key) DO UPDATE SET setting_value = excluded.setting_value, updated_at = CURRENT_TIMESTAMP");
        return $stmt->execute([$key, $value]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = CURRENT_TIMESTAMP");
        return $stmt->execute([$key, $value]);
    }
}

if (!function_exists('sendTelegramAlert')) {
    function sendTelegramAlert($text) {
        if (getSetting('enable_telegram_notify', '0') !== '1') {
            return false;
        }
        $token = getSetting('telegram_bot_token', '');
        $chatId = getSetting('telegram_chat_id', '');
        if (empty($token) || empty($chatId)) {
            return false;
        }

        try {
            $url = "https://api.telegram.org/bot{$token}/sendMessage";
            $data = [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML'
            ];

            if (function_exists('curl_init')) {
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 3);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_exec($ch);
                curl_close($ch);
            } else {
                $opts = [
                    'http' => [
                        'method'  => 'POST',
                        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                        'content' => http_build_query($data),
                        'timeout' => 3
                    ]
                ];
                $context = stream_context_create($opts);
                @file_get_contents($url, false, $context);
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}

/**
 * AI Sommelier & Şef Eşleştirme Motoru (Gastronomi Kuralları ile Akıllı Öneri Üretici)
 */
function generateAiChefPairing($productName, $categoryName = '', $description = '') {
    $text = mb_strtolower($productName . ' ' . $categoryName . ' ' . $description, 'UTF-8');
    
    // 1. Bira ve Malt İçecekler
    if (preg_match('/(bira|beer|efes|tuborg|bomonti|corona|heineken|miller|carlsberg|craft|lager|ipa|stout|draft|fıçı|pilsen)/u', $text)) {
        $pairings = [
            '🥜 Çıtır Bira Tabağı, Tuzlu Fıstık & Baharatlı Soğan Halkası',
            '🍟 Trüflü Parmesanlı Patates Kızartması & Jalapeno Poppers',
            '🍗 Çıtır Tavuk Sepeti & Ballı Hardal Sos',
            '🧀 Izgara Hellim Peyniri & Nachos Tabağı'
        ];
        return $pairings[array_rand($pairings)];
    }

    // 2. Kırmızı Şaraplar & Ağır Etler
    if (preg_match('/(kırmızı şarap|red wine|cabernet|merlot|öküzgözü|boğazkere|shiraz|syrah|pinot noir|bonfile|antrikot|steak|dana|pirzola|kuzu)/u', $text)) {
        $pairings = [
            '🧀 Gurme İsli Peynir Tabağı, Kuru İncir & Ceviz',
            '🍷 Kazdağları Meşe Fıçı Cabernet Sauvignon & Kuru Meyveler',
            '🧄 Fırınlanmış Sarımsaklı Focaccia & Trüflü Tereyağı',
            '🥩 Biberiyeli Izgara Dana Antrikot & Fırın Patates'
        ];
        return $pairings[array_rand($pairings)];
    }

    // 3. Beyaz / Roze Şaraplar & Hafif Lezzetler
    if (preg_match('/(beyaz şarap|white wine|roze|sauvignon|chardonnay|narince|emir|blush)/u', $text)) {
        $pairings = [
            '🦐 Tereyağlı Sarımsaklı Karides Güveç & Ege Roka Salatası',
            '🧀 Keçi Peynirli & Cevizli İncir Salatası',
            '🐟 Izgara Ege Levreği & Deniz Börülcesi Mezesi',
            '🥖 Çıtır Bruschetta & Taze Fesleğenli Mozzarella'
        ];
        return $pairings[array_rand($pairings)];
    }

    // 4. Rakı & Çilingir Sofrası
    if (preg_match('/(rakı|raki|yeni rakı|tekirdağ|beylerbeyi|kulüp|altınbaş|meze|çilingir)/u', $text)) {
        $pairings = [
            '🐟 Izgara Çipura, Fava, Kavun & Ezine Peyniri Tabağı',
            '🐙 Izgara Ahtapot Bacağı, Şakşuka & Köz Patlıcan',
            '🦐 Güveçte Tereyağlı Karides & Haydari',
            '🥗 Ayvalık Cunda Meze Üçlüsü & Sıcak Ot Kavurması'
        ];
        return $pairings[array_rand($pairings)];
    }

    // 5. Kokteyller & Alkollü İçecekler
    if (preg_match('/(kokteyl|cocktail|margarita|mojito|aperol|gin|cin|vodka|viski|whiskey|tequila|rom|martini)/u', $text)) {
        $pairings = [
            '🍤 Çıtır Kalamar Tava & Ev Yapımı Tarator Sos',
            '🧀 Karışık Akdeniz Tapas Tabağı & Fesleğenli Zeytinler',
            '🌮 Mini Guacamole Nachos & Çıtır Karides',
            '🍢 Mini Izgara Şişler & Füme Peynir'
        ];
        return $pairings[array_rand($pairings)];
    }

    // 6. Burgerler & Sandviçler
    if (preg_match('/(burger|cheeseburger|hamburger|sandviç|wrap|dürüm|tost)/u', $text)) {
        $pairings = [
            '🍟 Çıtır Baharatlı Patates & Buz Gibi Fıçı Bira',
            '🥤 Soğuk Ev Yapımı Fesleğenli Ayran veya Craft Kola',
            '🧅 Çıtır Soğan Halkası & Trüflü Mayonez Sos',
            '🍹 Buzlu Naneli Limonata'
        ];
        return $pairings[array_rand($pairings)];
    }

    // 7. Pizzalar & Pideler
    if (preg_match('/(pizza|pide|calzone|margherita|quattro|lahmacun)/u', $text)) {
        $pairings = [
            '🍹 Taze Fesleğenli Ev Yapımı Limonata & Akdeniz Salatası',
            '🍺 Buz Gibi Soğuk İtalyan Birası veya Draft Lager',
            '🍷 Kadehte Hafif Gövdeli Ege Kırmızı Şarabı',
            '🧄 Sarımsaklı Zeytinyağlı Çıtır Ekmek Dilimleri'
        ];
        return $pairings[array_rand($pairings)];
    }

    // 8. Makarnalar & Risotto
    if (preg_match('/(makarna|pasta|spaghetti|fettuccine|penne|ravioli|risotto|lasagna|lazanya)/u', $text)) {
        $pairings = [
            '🍷 Ege Roze Şarabı & Taze Parmesanlı Focaccia Ekmeği',
            '🥗 Balzamik Soslu Akdeniz Yeşillikleri & Çeri Domates',
            '🧄 Sarımsaklı Fırın Ekmek & Taze Fesleğen Pesto',
            '🥂 Soğuk Kadeh Chardonnay'
        ];
        return $pairings[array_rand($pairings)];
    }

    // 9. Balık & Deniz Ürünleri
    if (preg_match('/(balık|levrek|çipura|somon|kalamar|karides|ahtapot|midye|deniz)/u', $text)) {
        $pairings = [
            '🥂 Soğuk Ege Beyaz Şarabı & Taze Deniz Börülcesi',
            '🥗 Nar Ekşili Roka Salatası & Zeytinyağlı Fava',
            '🍋 Taze Sıkılmış Çilekli & Naneli Limonata',
            '🍶 Tekirdağ Altın Seri Rakı & Kavun Dilimleri'
        ];
        return $pairings[array_rand($pairings)];
    }

    // 10. Tatlılar
    if (preg_match('/(tatlı|sufle|cheesecake|tiramisu|pasta|baklava|dondurma|künefe|magnolia|brownie|waffle|fondü)/u', $text)) {
        $pairings = [
            '☕ Damla Sakızlı Türk Kahvesi veya Double Espresso',
            '🍨 Bir Top Hakiki Maraş Dondurması',
            '🥃 Baileys Likörü veya Sıcak Sütlü Latte',
            '🍵 Bergamot Aromalı Taze Demleme Çay'
        ];
        return $pairings[array_rand($pairings)];
    }

    // 11. Kahvaltı & Yumurta
    if (preg_match('/(kahvaltı|omlet|menemen|serpme|kuymak|pancake|poşe)/u', $text)) {
        $pairings = [
            '🫖 Taze Demleme Rize Çayı & Taze Sıkılmış Portakal Suyu',
            '🥑 Avokado Dilimleri & Köy Tereyağı',
            '🍯 Petek Bal & Kaymak İkilisi'
        ];
        return $pairings[array_rand($pairings)];
    }

    // 12. Kahveler & Sıcak İçecekler
    if (preg_match('/(kahve|coffee|espresso|latte|cappuccino|americano|çay|tea|salep)/u', $text)) {
        $pairings = [
            '🍪 Fındıklı Ev Yapımı Kurabiye & Mini Macaron',
            '🍫 Şefin El Yapımı Belçika Çikolatası',
            '🍰 Dilim Frambuazlı Cheesecake'
        ];
        return $pairings[array_rand($pairings)];
    }

    // Genel Akdeniz Şef Önerisi
    return '🍷 Şefin Önerisi: Şarap menümüz ve serinletici imza içeceklerimiz ile lezzeti taçlandırın.';
}

// Veritabanını otomatik başlat
initDatabase($pdo);

