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
        'restaurant_name' => 'Gusto Gourmet & Lounge',
        'restaurant_slogan' => 'Eşsiz Lezzetler & Keyifli Anlar',
        'currency' => '₺',
        'theme_color' => '#d97706',
        'theme_mode' => 'dark',
        'logo_dark_url' => '',
        'logo_light_url' => '',
        'banner_url' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=1200&q=80',
        'wifi_name' => 'Gusto_Guest_5G',
        'wifi_pass' => 'Gusto2026!',
        'phone' => '+90 (212) 555 0199',
        'instagram' => 'gustogourmet',
        'address' => 'Bağdat Caddesi No: 142, Kadıköy / İstanbul',
        
        // MODÜL AÇ/KAPA (AÇIK = 1, KAPALI = 0)
        'enable_order' => '0',            // Masadan Canlı Sipariş & Sepet (Varsayılan Pasif - Garson Çağrısı Aktif)
        'enable_multi_lang' => '1',       // Çoklu Dil Desteği (TR, EN, AR, RU, DE)
        'enable_kitchen' => '1',          // Canlı Mutfak & Bar Ekranı (KDS)
        'enable_stories' => '1',          // Instagram Tarzı Kampanya Hikayeleri
        'enable_popup' => '1',            // Açılış Kampanya Pop-Up
        'enable_feedback' => '1',         // Google Yorumları & Puanlama
        'enable_allergens_filter' => '1', // Gelişmiş Diyet & Alerjen Filtresi
        'enable_waiter_call' => '1',      // Garson Çağırma & Hesap İsteme
        
        // POP-UP KAMPANYA BİLGİLERİ
        'popup_title' => '🎉 Haftanın Özel Spesiyali!',
        'popup_desc' => 'Gusto Smokehouse Burger yanında ev yapımı çıtır patates ve özel trüf mayonez ile şimdi %15 indirimli!',
        'popup_image' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=800&q=80',
        'popup_btn_text' => 'Hemen İncele',
        'popup_btn_link' => '#cat-2',
        
        // GOOGLE HARİTA / YORUM LİNKİ
        'google_maps_url' => 'https://maps.google.com/?q=Gusto+Gourmet'
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
            ['1', 'Masa 1'], ['2', 'Masa 2'], ['3', 'Masa 3'], ['4', 'Masa 4'],
            ['5', 'Masa 5'], ['B1', 'Bahçe 1'], ['B2', 'Bahçe 2'], ['VIP-1', 'Teras VIP 1']
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
            ['title' => 'Günün Menüsü', 'image' => 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=600&q=80', 'link' => '#cat-4', 'sort_order' => 1],
            ['title' => 'Gurme Burgerler', 'image' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=600&q=80', 'link' => '#cat-2', 'sort_order' => 2],
            ['title' => 'Happy Hour %20', 'image' => 'https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b?w=600&q=80', 'link' => '#cat-6', 'sort_order' => 3],
            ['title' => 'Şefin Tatlıları', 'image' => 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?w=600&q=80', 'link' => '#cat-5', 'sort_order' => 4],
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
                'name' => 'Kahvaltı & Başlangıçlar',
                'name_en' => 'Breakfast & Starters',
                'name_ar' => 'الإفطار والمقبلات',
                'name_ru' => 'Завтраки и закуски',
                'name_de' => 'Frühstück & Vorspeisen',
                'slug' => 'kahvalti-baslangiclar',
                'icon' => 'egg',
                'image' => 'https://images.unsplash.com/photo-1533089860892-a7c6f0a88666?w=600&q=80',
                'sort_order' => 1,
                'products' => [
                    [
                        'name' => 'Serpme Ege Kahvaltısı (2 Kişilik)',
                        'name_en' => 'Aegean Spread Breakfast (For 2)',
                        'name_ar' => 'إفطار بحر إيجة لشخصين',
                        'name_ru' => 'Эгейский завтрак (на 2 персоны)',
                        'name_de' => 'Ägäisches Frühstück (für 2)',
                        'desc' => 'Ezine peyniri, Bergama tulumu, Çeçil peyniri, ev yapımı reçeller, tereyağı, petek bal, kaymak, ızgara zeytinler, sahanda sucuklu yumurta, pişi ve sınırsız çay.',
                        'desc_en' => 'Ezine cheese, aged tulum, cecil cheese, homemade jams, butter, honeycomb, clotted cream, grilled olives, fried eggs with sucuk, fried dough and unlimited tea.',
                        'price' => 540.00,
                        'old_price' => 600.00,
                        'image' => 'https://images.unsplash.com/photo-1533089860892-a7c6f0a88666?w=800&q=80',
                        'badge' => 'Popüler',
                        'calories' => 1250,
                        'prep_time' => 15,
                        'allergens' => 'Gluten, Laktoz, Yumurta',
                        'is_featured' => 1
                    ],
                    [
                        'name' => 'Avokado Poşe Yumurta & Ekşi Maya',
                        'name_en' => 'Avocado Poached Egg on Sourdough',
                        'name_ar' => 'أفوكادو مع بيض مسلوق على خبز العجين المخمر',
                        'name_ru' => 'Яйцо пашот с авокадо на закваске',
                        'name_de' => 'Avocado-Pochiertes Ei auf Sauerteig',
                        'desc' => 'Kızarmış ekşi mayalı ekmek üzerine taze avokado püresi, poşe köy yumurtası, labne, çeri domates ve çörek otu.',
                        'desc_en' => 'Fresh avocado mash on toasted sourdough bread, poached farm egg, labneh, cherry tomatoes and black sesame seeds.',
                        'price' => 240.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1525351484163-7529414344d8?w=800&q=80',
                        'badge' => 'Şefin Seçimi',
                        'calories' => 460,
                        'prep_time' => 10,
                        'allergens' => 'Gluten, Laktoz, Yumurta',
                        'is_featured' => 1
                    ]
                ]
            ],
            [
                'name' => 'Gurme Burgerler',
                'name_en' => 'Gourmet Burgers',
                'name_ar' => 'برغر الذواقة',
                'name_ru' => 'Гурме бургеры',
                'name_de' => 'Gourmet-Burger',
                'slug' => 'gurme-burgerler',
                'icon' => 'burger',
                'image' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=600&q=80',
                'sort_order' => 2,
                'products' => [
                    [
                        'name' => 'Gusto Smokehouse Burger',
                        'name_en' => 'Gusto Smokehouse Burger',
                        'name_ar' => 'غوستو سموك هاوس برغر',
                        'name_ru' => 'Gusto Смоукхаус бургер',
                        'name_de' => 'Gusto Smokehouse Burger',
                        'desc' => '180 gr dinlendirilmiş dana köftesi, füme antrikot dilimleri, karamelize soğan, cheddar peyniri, çıtır soğan ve özel tütsülü BBQ sos.',
                        'desc_en' => '180g aged beef patty, smoked ribeye slices, caramelized onions, cheddar cheese, crispy onions and special smoky BBQ sauce.',
                        'price' => 360.00,
                        'old_price' => 390.00,
                        'image' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=800&q=80',
                        'badge' => 'Çok Satan',
                        'calories' => 820,
                        'prep_time' => 18,
                        'allergens' => 'Gluten, Laktoz, Hardal',
                        'is_featured' => 1,
                        'options' => [
                            ['group_name' => 'Pişme Derecesi', 'option_name' => 'Orta Pişmiş (Medium)', 'extra_price' => 0.00, 'is_required' => 1],
                            ['group_name' => 'Pişme Derecesi', 'option_name' => 'İyi Pişmiş (Well Done)', 'extra_price' => 0.00, 'is_required' => 1],
                            ['group_name' => 'Ekstralar', 'option_name' => 'Ekstra Cheddar Peyniri', 'extra_price' => 30.00, 'is_required' => 0],
                            ['group_name' => 'Ekstralar', 'option_name' => 'Füme Kaburga Dilimi', 'extra_price' => 60.00, 'is_required' => 0],
                            ['group_name' => 'Ekstralar', 'option_name' => 'Trüflü Mayonez Sos', 'extra_price' => 25.00, 'is_required' => 0],
                        ]
                    ],
                    [
                        'name' => 'Trüflü Mantarlı Gurme Burger',
                        'name_en' => 'Truffle Mushroom Burger',
                        'name_ar' => 'برغر الكمأة مع الفطر',
                        'name_ru' => 'Трюфельный бургер с грибами',
                        'name_de' => 'Trüffel-Pilz-Burger',
                        'desc' => '180 gr dana burger köftesi, sote yabani mantarlar, trüf mayonez, eritilmiş gravyer peyniri ve taze roka.',
                        'desc_en' => '180g beef burger patty, sautéed wild mushrooms, truffle mayo, melted gruyere cheese and fresh arugula.',
                        'price' => 385.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1586190848861-99aa4a171e90?w=800&q=80',
                        'badge' => 'Şefin Seçimi',
                        'calories' => 790,
                        'prep_time' => 16,
                        'allergens' => 'Gluten, Laktoz, Yumurta',
                        'is_featured' => 1
                    ]
                ]
            ],
            [
                'name' => 'Taş Fırın Pizza',
                'name_en' => 'Stone Oven Pizza',
                'name_ar' => 'بيتزا فرن الحجر',
                'name_ru' => 'Пицца из дровяной печи',
                'name_de' => 'Steinofenpizza',
                'slug' => 'tas-firin-pizza',
                'icon' => 'pizza-slice',
                'image' => 'https://images.unsplash.com/photo-1513104890138-7c749659a591?w=600&q=80',
                'sort_order' => 3,
                'products' => [
                    [
                        'name' => 'Pizza Margherita Napoletana',
                        'name_en' => 'Pizza Margherita Napoletana',
                        'name_ar' => 'بيتزا مارغريتا نابوليتانا',
                        'name_ru' => 'Пицца Маргарита Наполетана',
                        'name_de' => 'Pizza Margherita Napoletana',
                        'desc' => 'İtalyan San Marzano domates sosu, manda mozzarellası, taze fesleğen yaprakları ve sızma zeytinyağı.',
                        'desc_en' => 'Italian San Marzano tomato sauce, buffalo mozzarella, fresh basil leaves and extra virgin olive oil.',
                        'price' => 310.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1604382355076-af4b0eb60143?w=800&q=80',
                        'badge' => 'Vejetaryen',
                        'calories' => 650,
                        'prep_time' => 12,
                        'allergens' => 'Gluten, Laktoz',
                        'is_featured' => 0,
                        'options' => [
                            ['group_name' => 'Kenar Seçimi', 'option_name' => 'Klasik İnce Kenar', 'extra_price' => 0.00, 'is_required' => 1],
                            ['group_name' => 'Kenar Seçimi', 'option_name' => 'Peynir Dolgulu Kenar', 'extra_price' => 45.00, 'is_required' => 1],
                            ['group_name' => 'Ekstralar', 'option_name' => 'Ekstra Manda Mozzarella', 'extra_price' => 40.00, 'is_required' => 0],
                        ]
                    ]
                ]
            ],
            [
                'name' => 'Tatlılar & Pastalar',
                'name_en' => 'Desserts & Cakes',
                'name_ar' => 'الحلويات والكعك',
                'name_ru' => 'Десерты и торты',
                'name_de' => 'Desserts & Kuchen',
                'slug' => 'tatlilar-pastalar',
                'icon' => 'cake-candles',
                'image' => 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?w=600&q=80',
                'sort_order' => 4,
                'products' => [
                    [
                        'name' => 'San Sebastian Cheesecake & Sıcak Çikolata',
                        'name_en' => 'San Sebastian Cheesecake & Hot Chocolate',
                        'name_ar' => 'تشيز كيك سان سيباستيان مع الشوكولاتة الساخنة',
                        'name_ru' => 'Чизкейк Сан-Себастьян с горячим шоколадом',
                        'name_de' => 'San Sebastian Käsekuchen mit heißer Schokolade',
                        'desc' => 'Karamelize yanık üst kabuk, akışkan ipeksi doku ve yanında sıcak eritilmiş Belçika sütlü çikolatası.',
                        'desc_en' => 'Caramelized burnt crust, silky smooth texture with melted warm Belgian milk chocolate on the side.',
                        'price' => 230.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1533134242443-d4fd215305ad?w=800&q=80',
                        'badge' => 'Yeni',
                        'calories' => 560,
                        'prep_time' => 5,
                        'allergens' => 'Laktoz, Yumurta',
                        'is_featured' => 1
                    ]
                ]
            ],
            [
                'name' => 'Kahve & İçecekler',
                'name_en' => 'Coffee & Beverages',
                'name_ar' => 'القهوة والمشروبات',
                'name_ru' => 'Кофе и напитки',
                'name_de' => 'Kaffee & Getränke',
                'slug' => 'kahve-icecekler',
                'icon' => 'coffee',
                'image' => 'https://images.unsplash.com/photo-1517701550927-30cf4ba1dba5?w=600&q=80',
                'sort_order' => 5,
                'products' => [
                    [
                        'name' => 'Iced Salted Caramel Latte',
                        'name_en' => 'Iced Salted Caramel Latte',
                        'name_ar' => 'لاتيه كراميل مملح مثلج',
                        'name_ru' => 'Айс латте с соленой карамелью',
                        'name_de' => 'Eisgekühltes Salted Caramel Latte',
                        'desc' => 'Çift shot taze çekilmiş espresso, soğuk süt, deniz tuzlu karamel sosu ve buz.',
                        'desc_en' => 'Double shot fresh espresso, cold milk, sea salted caramel syrup and ice.',
                        'price' => 145.00,
                        'old_price' => null,
                        'image' => 'https://images.unsplash.com/photo-1517701550927-30cf4ba1dba5?w=800&q=80',
                        'badge' => 'Favori',
                        'calories' => 190,
                        'prep_time' => 4,
                        'allergens' => 'Laktoz',
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
