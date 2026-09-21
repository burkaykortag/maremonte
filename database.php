<?php
/**
 * Veritabanı Bağlantısı ve Otomatik Tablo / Örnek Veri Kurulumu
 */

require_once __DIR__ . '/config.php';

try {
    if (DB_DRIVER === 'sqlite') {
        $dbDir = dirname(DB_SQLITE_PATH);
        if (!is_dir($dbDir)) {
            mkdir($dbDir, 0777, true);
        }
        $pdo = new PDO('sqlite:' . DB_SQLITE_PATH);
    } else {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET,
            DB_USER,
            DB_PASS
        );
    }

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
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
        'theme_color' => '#d97706',
        'theme_mode' => 'dark',
        'logo_dark_url' => '',
        'logo_light_url' => '',
        'banner_url' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=1200&q=80',
        'wifi_name' => 'MareMonte_Guest',
        'wifi_pass' => 'MareMonte1985',
        'phone' => '+90 (266) 396 00 00',
        'instagram' => 'hotelmaremonte',
        'address' => 'İskele Mah. Sahil Cad. No:14, Altınoluk / Balıkesir',
        
        // MODÜL AÇ/KAPA (AÇIK = 1, KAPALI = 0)
        'enable_order' => '0',            // Masadan Canlı Sipariş Pasif (Garson Çağrısı Aktif)
        'enable_multi_lang' => '1',       // Çoklu Dil Desteği (TR, EN, AR, RU, DE)
        'enable_kitchen' => '1',          // Canlı Mutfak & Bar Ekranı (KDS)
        'enable_stories' => '1',          // Instagram Tarzı Kampanya Hikayeleri
        'enable_popup' => '1',            // Açılış Kampanya Pop-Up
        'enable_feedback' => '1',         // Google Yorumları & Puanlama
        'enable_allergens_filter' => '1', // Gelişmiş Diyet & Alerjen Filtresi
        'enable_waiter_call' => '1',      // Garson Çağırma & Hesap İsteme
        
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
        ];
        $stmtS = $pdo->prepare("INSERT INTO stories (title, image, link, sort_order, is_active) VALUES (?, ?, ?, ?, 1)");
        foreach ($demoStories as $s) {
            $stmtS->execute([$s['title'], $s['image'], $s['link'], $s['sort_order']]);
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
                'image' => 'https://images.unsplash.com/photo-1621996346565-e3adc644d946?w=600&q=80',
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

// Veritabanını otomatik başlat
initDatabase($pdo);
