-- Hotel Mare & Monte Bistro (Altınoluk Est. 1985) QR Menu
-- MySQL / MariaDB Database Dump for Plesk / cPanel Import
-- Compatible with MySQL 5.7+ / 8.0+ / MariaDB 10.3+

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) DEFAULT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES
('1', 'restaurant_name', 'HOTEL MARE MONTE BISTRO', '2026-09-21 14:26:28'),
('2', 'restaurant_slogan', 'Altınoluk (Est. 1985)', '2026-09-21 14:26:28'),
('3', 'currency', '₺', '2026-09-21 14:26:28'),
('4', 'theme_color', '#d97706', '2026-09-21 14:26:28'),
('5', 'theme_mode', 'light', '2026-09-21 14:26:28'),
('6', 'logo_dark_url', '', '2026-09-21 14:26:28'),
('7', 'logo_light_url', '', '2026-09-21 14:26:28'),
('8', 'banner_url', 'http://localhost/menu/uploads/branding/img_6ab13e946e0c69.39493137.jpg', '2026-09-21 14:26:28'),
('9', 'wifi_name', 'MareMonte_Guest', '2026-09-21 14:26:28'),
('10', 'wifi_pass', 'MareMonte1985', '2026-09-21 14:26:28'),
('11', 'phone', '+90 (266) 396 00 00', '2026-09-21 14:26:28'),
('12', 'instagram', 'hotelmaremonte', '2026-09-21 14:26:28'),
('13', 'address', 'İskele Mah. Sahil Cad. No:14, Altınoluk / Balıkesir', '2026-09-21 14:26:28'),
('14', 'enable_order', '0', '2026-09-21 14:26:28'),
('15', 'enable_multi_lang', '1', '2026-09-21 14:26:28'),
('16', 'enable_kitchen', '1', '2026-09-21 14:26:28'),
('17', 'enable_stories', '1', '2026-09-21 14:26:28'),
('18', 'enable_popup', '1', '2026-09-21 14:26:28'),
('19', 'enable_feedback', '1', '2026-09-21 14:26:28'),
('20', 'enable_allergens_filter', '1', '2026-09-21 14:26:28'),
('21', 'enable_waiter_call', '1', '2026-09-21 14:26:28'),
('22', 'popup_title', '🌊 Hotel Mare & Monte Bistro Hoş Geldiniz!', '2026-09-21 14:13:39'),
('23', 'popup_desc', '1985\'ten beri Altınoluk sahilinde eşsiz lezzetler, taze deniz ürünleri ve imza kokteyllerimizi keşfedin!', '2026-09-21 14:13:39'),
('24', 'popup_image', 'https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b?w=800&q=80', '2026-09-21 14:13:39'),
('25', 'popup_btn_text', 'İmza Kokteylleri İncele', '2026-09-21 14:13:39'),
('26', 'popup_btn_link', '#cat-13', '2026-09-21 14:13:39'),
('27', 'google_maps_url', 'https://maps.google.com/?q=Hotel+Mare+Monte+Altinoluk', '2026-09-21 14:26:28'),
('1443', 'enable_currency_converter', '1', '2026-09-22 05:36:04'),
('1444', 'currency_eur_rate', '38.50', '2026-09-22 05:36:04'),
('1445', 'currency_usd_rate', '35.00', '2026-09-22 05:36:04'),
('1446', 'currency_gbp_rate', '46.00', '2026-09-22 05:36:04'),
('1447', 'enable_pairings', '1', '2026-09-22 05:36:04'),
('1448', 'enable_happy_hour', '1', '2026-09-22 05:36:04'),
('1449', 'happy_hour_title', '🌅 Gün Batımı Happy Hour (Tüm Kokteyllerde %15 İndirim)', '2026-09-22 05:36:04'),
('1450', 'happy_hour_start', '17:00', '2026-09-22 05:36:04'),
('1451', 'happy_hour_end', '19:30', '2026-09-22 05:36:04'),
('1452', 'happy_hour_discount', '15', '2026-09-22 05:36:04'),
('1453', 'enable_resort_service', '1', '2026-09-22 05:36:04'),
('1454', 'enable_events', '1', '2026-09-22 05:36:04'),
('1455', 'enable_concierge', '1', '2026-09-22 05:36:04'),
('1456', 'enable_telegram_notify', '0', '2026-09-22 05:36:04'),
('1457', 'telegram_bot_token', '', '2026-09-22 05:36:04'),
('1458', 'telegram_chat_id', '', '2026-09-22 05:36:04'),
('1459', 'enable_whatsapp_notify', '0', '2026-09-22 05:36:04'),
('1460', 'whatsapp_phone', '+902663960000', '2026-09-22 05:36:04'),
('1461', 'enable_lucky_wheel', '1', '2026-09-22 05:36:04'),
('1462', 'wheel_rewards', 'Günün Tatlısı İkramı,%10 Hesap İndirimi,Türk Kahvesi İkramı,Şefin Özel Kokteyli,%15 İndirim,Teşekkürler', '2026-09-22 05:36:04');

DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `name` varchar(150) DEFAULT NULL,
  `role` varchar(50) DEFAULT 'admin',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `admins` (`id`, `username`, `password_hash`, `name`, `role`, `created_at`) VALUES
('1', 'admin', '$2y$10$EQKv5kggjAkGxuD6mnvkYeVfitOz1uAI8bVzNja0E81J4offkImf.', 'Mare & Monte Admin', 'superadmin', '2026-09-21 14:13:39');

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `slug` varchar(150) DEFAULT NULL,
  `icon` varchar(100) DEFAULT 'utensils',
  `image` varchar(255) DEFAULT '',
  `name_en` varchar(150) DEFAULT '',
  `name_ar` varchar(150) DEFAULT '',
  `name_ru` varchar(150) DEFAULT '',
  `name_de` varchar(150) DEFAULT '',
  `sort_order` int(11) DEFAULT 0,
  `is_active` int(11) DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `image`, `name_en`, `name_ar`, `name_ru`, `name_de`, `sort_order`, `is_active`, `created_at`) VALUES
('1', 'Başlangıçlar', 'baslangiclar', 'utensils', 'https://images.unsplash.com/photo-1547592166-23ac45744acd?w=600&q=80', 'Starters & Soups', 'المقبلات والشوربات', 'Закуски и супы', 'Vorspeisen & Suppen', '1', '1', '2026-09-21 14:13:39'),
('2', 'Atıştırmalıklar', 'atistirmaliklar', 'burger', 'https://images.unsplash.com/photo-1541592106381-b31e9677c0e5?w=600&q=80', 'Snacks & Bites', 'الوجبات الخفيفة والمقبلات', 'Снеки и закуски', 'Snacks & Fingerfood', '2', '1', '2026-09-21 14:13:39'),
('3', 'Makarnalar', 'makarnalar', 'bowl-rice', 'https://images.unsplash.com/photo-1551183053-bf91a1d81141?w=600&q=80', 'Pastas', 'المعكرونة الإيطالية', 'Паста', 'Pasta & Nudeln', '3', '1', '2026-09-21 14:13:39'),
('4', 'Pizzalar', 'pizzalar', 'pizza-slice', 'https://images.unsplash.com/photo-1604382355076-af4b0eb60143?w=600&q=80', 'Pizzas', 'البيتزا', 'Пицца', 'Pizzen', '4', '1', '2026-09-21 14:13:39'),
('5', 'Burgerler', 'burgerler', 'burger', 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=600&q=80', 'Burgers', 'البرغر', 'Бургеры', 'Burger', '5', '1', '2026-09-21 14:13:39'),
('6', 'Salatalar', 'salatalar', 'leaf', 'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=600&q=80', 'Salads', 'السلطات الطازجة', 'Салаты', 'Salate', '6', '1', '2026-09-21 14:13:39'),
('7', 'Ana Yemekler', 'ana-yemekler', 'utensils', 'https://images.unsplash.com/photo-1544025162-d76694265947?w=600&q=80', 'Main Courses', 'الأطباق الرئيسية والمشاوي', 'Основные блюда', 'Hauptgerichte', '7', '1', '2026-09-21 14:13:39'),
('8', 'Deniz Ürünleri', 'deniz-urunleri', 'fish', 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?w=600&q=80', 'Seafood & Fresh Fish', 'المأكولات البحرية والأسماك الطازجة', 'Рыба и морепродукты', 'Meeresfrüchte & Frischer Fisch', '8', '1', '2026-09-21 14:13:39'),
('9', 'Meşrubatlar & İçecekler', 'mesrubatlar-icecekler', 'bottle-water', 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?w=600&q=80', 'Soft Drinks & Beverages', 'المشروبات الغازية والباردة', 'Безалкогольные напитки', 'Alkoholfreie Getränke', '9', '1', '2026-09-21 14:13:39'),
('10', 'Kahveler & Tatlılar', 'kahveler-tatlilar', 'coffee', 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?w=600&q=80', 'Coffees & Desserts', 'القهوة والحلويات', 'Кофе и десерты', 'Kaffee & Desserts', '10', '1', '2026-09-21 14:13:39'),
('11', 'Biralar', 'biralar', 'beer-mug-empty', 'https://images.unsplash.com/photo-1535958636474-b021ee887b13?w=600&q=80', 'Beers', 'البيرة', 'Пиво', 'Biere', '11', '1', '2026-09-21 14:13:39'),
('12', 'Şaraplar (Kadeh)', 'saraplar', 'wine-glass', 'https://images.unsplash.com/photo-1510812431401-41d2bd2722f3?w=600&q=80', 'Wines by the Glass', 'النبيذ بالكأس', 'Вина по бокалам', 'Weine im Glas', '12', '1', '2026-09-21 14:13:39'),
('13', 'Kokteyller', 'kokteyller', 'martini-glass-citrus', 'https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b?w=600&q=80', 'Classic & Signature Cocktails', 'الكوكتيلات الكلاسيكية والخاصة', 'Классические и авторские коктейли', 'Klassische & Signature Cocktails', '13', '1', '2026-09-21 14:13:39'),
('14', 'Alkollü İçecekler & Rakı', 'alkollu-icecekler-raki', 'wine-bottle', 'https://images.unsplash.com/photo-1527061011665-3652c757a4d4?w=600&q=80', 'Spirits & Traditional Raki', 'المشروبات الروحية والعرق التركي', 'Крепкий алкоголь и ракы', 'Spirituosen & Raki', '14', '1', '2026-09-21 14:13:39');

DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `name_en` varchar(200) DEFAULT '',
  `desc_en` text DEFAULT NULL,
  `name_ar` varchar(200) DEFAULT '',
  `desc_ar` text DEFAULT NULL,
  `name_ru` varchar(200) DEFAULT '',
  `desc_ru` text DEFAULT NULL,
  `name_de` varchar(200) DEFAULT '',
  `desc_de` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `old_price` decimal(10,2) DEFAULT NULL,
  `image` varchar(255) DEFAULT '',
  `badge` varchar(50) DEFAULT '',
  `calories` int(11) DEFAULT 0,
  `prep_time` int(11) DEFAULT 15,
  `allergens` varchar(255) DEFAULT '',
  `is_available` int(11) DEFAULT 1,
  `is_featured` int(11) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `view_count` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `name_en`, `desc_en`, `name_ar`, `desc_ar`, `name_ru`, `desc_ru`, `name_de`, `desc_de`, `price`, `old_price`, `image`, `badge`, `calories`, `prep_time`, `allergens`, `is_available`, `is_featured`, `sort_order`, `view_count`, `created_at`) VALUES
('1', '1', 'Günün Çorbası', 'Şefimizin günlük olarak taze malzemelerle hazırladığı leziz çorba.', 'Soup of the Day', 'Freshly prepared delicious hot soup of the day by our chef.', 'شوربة اليوم', NULL, 'Суп дня', NULL, 'Tagessuppe', NULL, '120', NULL, 'https://images.unsplash.com/photo-1547592166-23ac45744acd?w=800&q=80', 'Şefin Seçimi', '180', '5', 'Gluten', '1', '1', '1', '0', '2026-09-21 14:13:39'),
('2', '1', 'Ezine Peynir Dilimi', 'Meşhur Ezine peyniri, taze domates ve çıtır salatalık ile.', 'Ezine Cheese Slice', 'Famous local Ezine cheese served with fresh tomatoes and cucumbers.', 'شريحة جبن إزيني', NULL, 'Сыр Эзине', NULL, 'Ezine Käsescheibe', NULL, '150', NULL, 'https://images.unsplash.com/photo-1589881133595-a3c085cb731d?w=800&q=80', 'Yöresel', '210', '5', 'Laktoz', '1', '0', '2', '0', '2026-09-21 14:13:39'),
('3', '1', 'Meze', 'Günün seçkisiyle taze hazırlanan nefis mevsim mezeleri.', 'Traditional Meze', 'Fresh selection of daily traditional cold mezes.', 'مقبلات باردة', NULL, 'Традиционные мезе', NULL, 'Traditionelle Meze', NULL, '200', NULL, 'https://images.unsplash.com/photo-1541544741938-0af808871cc0?w=800&q=80', 'Günlük Taze', '190', '5', '', '1', '0', '3', '0', '2026-09-21 14:13:39'),
('4', '1', 'Yerli Peynir Tabağı', 'Bölgesel seçkin peynir çeşitleri, kuru meyveler ve ceviz ile.', 'Local Cheese Platter', 'Fine selection of regional Turkish cheeses served with dried fruits and walnuts.', 'طبق الأجبان المحلية', NULL, 'Тарелка местных сыров', NULL, 'Lokale Käseplatte', NULL, '600', NULL, 'https://images.unsplash.com/photo-1631379578550-7038263db699?w=800&q=80', 'Popüler', '450', '8', 'Laktoz, Kuruyemiş', '1', '1', '4', '0', '2026-09-21 14:13:39'),
('5', '2', 'Peynirli Tost', 'Eritilmiş kaşar peyniri, domates ve salatalık eşliğinde.', 'Cheese Toast', 'Melted cheese toast served with tomato and cucumber slices.', 'توست الجبن', NULL, 'Тост с сыром', NULL, 'Käsetoast', NULL, '175', NULL, 'https://images.unsplash.com/photo-1528735602780-2552fd46c7af?w=800&q=80', '', '380', '8', 'Gluten, Laktoz', '1', '0', '1', '0', '2026-09-21 14:13:39'),
('6', '2', 'Sucuklu Tost', 'Kızarmış dana sucuğu, çeri domates ve salatalık eşliğinde.', 'Sujuk Toast', 'Spicy beef sujuk toast served with cherry tomatoes and cucumber.', 'توست السجق', NULL, 'Тост с суджуком', NULL, 'Sujuk Toast', NULL, '200', NULL, 'https://images.unsplash.com/photo-1584776296944-ab6fb57b0bdd?w=800&q=80', '', '430', '8', 'Gluten', '1', '0', '2', '0', '2026-09-21 14:13:39'),
('7', '2', 'Karışık Tost', 'Dana sucuğu, kaşar peyniri, salatalık ve çeri domates eşliğinde.', 'Mixed Toast', 'Beef sujuk and kashar cheese toast served with tomato and cucumber.', 'توست مشكل', NULL, 'Смешанный тост', NULL, 'Gemischter Toast', NULL, '250', NULL, 'https://images.unsplash.com/photo-1528735602780-2552fd46c7af?w=800&q=80', 'Klasik', '490', '8', 'Gluten, Laktoz', '1', '1', '3', '0', '2026-09-21 14:13:39'),
('8', '2', 'Gözleme', 'Dana kıyma, peynirli, kaşarlı veya ıspanaklı seçenekleriyle sacda taze pişirilir.', 'Traditional Turkish Gözleme', 'Handmade Turkish flatbread filled with minced beef, feta, kashar or spinach.', 'فطائر غوزليمة التركية', NULL, 'Гёзлеме', NULL, 'Gözleme Fladenbrot', NULL, '300', NULL, 'https://images.unsplash.com/photo-1627308595229-7830a5c91f9f?w=800&q=80', 'El Açması', '450', '12', 'Gluten, Laktoz', '1', '1', '4', '0', '2026-09-21 14:13:39'),
('9', '2', 'Bira Tabağı', 'Patates kızartması, nugget, sigara böreği, sosis ve çıtır soğan halkası.', 'Beer Snack Platter', 'French fries, chicken nuggets, crispy rolls, sausages and onion rings.', 'طبق مقبلات مشكل كبير', NULL, 'Пивная тарелка', NULL, 'Bierteller Snackplatte', NULL, '550', NULL, 'https://images.unsplash.com/photo-1541592106381-b31e9677c0e5?w=800&q=80', 'Favori', '950', '15', 'Gluten, Laktoz', '1', '1', '5', '0', '2026-09-21 14:13:39'),
('10', '2', 'Patates Kızartması', 'Altın sarısı çıtır patates kızartması, özel baharat karışımı ile.', 'French Fries', 'Golden crispy french fries seasoned with house spice blend.', 'بطاطس مقلية مقرمشة', NULL, 'Картофель фри', NULL, 'Pommes Frites', NULL, '200', NULL, 'https://images.unsplash.com/photo-1576107232684-1279f3908594?w=800&q=80', '', '420', '8', '', '1', '0', '6', '0', '2026-09-21 14:13:39'),
('11', '2', 'Karides Cipsi', 'Özel baharat ve dip sos ile servis edilen çıtır karides cipsi.', 'Prawn Crackers', 'Crispy prawn crackers served with seasoning and dipping sauce.', 'رقائق الجمبري المقرمشة', NULL, 'Креветочные чипсы', NULL, 'Krabbenchips', NULL, '250', NULL, 'https://images.unsplash.com/photo-1563379091339-03b21ab4a4f8?w=800&q=80', '', '280', '5', 'Deniz Ürünleri', '1', '0', '7', '0', '2026-09-21 14:13:39'),
('12', '2', 'Soğan Halkası', 'Çıtır kaplamalı soğan halkaları, patates kızartması ve soslar ile.', 'Crispy Onion Rings', 'Crispy battered onion rings served with golden french fries and dips.', 'حلقات البصل المقرمشة', NULL, 'Луковые кольца', NULL, 'Zwiebelringe', NULL, '350', NULL, 'https://images.unsplash.com/photo-1639024471287-032f66e5f039?w=800&q=80', '', '480', '10', 'Gluten', '1', '0', '8', '0', '2026-09-21 14:13:39'),
('13', '2', 'Nuggets', 'Çıtır tavuk nugget dilimleri, patates kızartması ve soslar ile.', 'Chicken Nuggets', 'Crispy chicken nuggets served with golden fries and dipping sauces.', 'قطع الدجاج المقرمشة (ناجتس)', NULL, 'Куриные наггетсы', NULL, 'Chicken Nuggets', NULL, '350', NULL, 'https://images.unsplash.com/photo-1562967914-608f82629710?w=800&q=80', '', '520', '10', 'Gluten', '1', '0', '9', '0', '2026-09-21 14:13:39'),
('14', '2', 'Sosis Tabağı', 'Izgara sosis dilimleri, patates kızartması ve hardal ile.', 'Sausage Platter', 'Grilled sausage slices served with crispy french fries and mustard.', 'طبق النقانق المشوية', NULL, 'Тарелка с колбасками', NULL, 'Würstchenplatte', NULL, '350', NULL, 'https://images.unsplash.com/photo-1585325701165-351af916e581?w=800&q=80', '', '560', '10', 'Hardal', '1', '0', '10', '0', '2026-09-21 14:13:39'),
('15', '3', 'Spaghetti Bolonez', 'Geleneksel ağır ateşte pişmiş dana kıymalı Bolonez sos ve rendelenmiş parmesan peyniri ile.', 'Spaghetti Bolognese', 'Traditional slow-simmered beef Bolognese sauce topped with fresh parmesan.', 'سباغيتي بولونيز', NULL, 'Спагетти Болоньезе', NULL, 'Spaghetti Bolognese', NULL, '450', NULL, 'https://images.unsplash.com/photo-1621996346565-e3adc644d946?w=800&q=80', 'İtalyan', '680', '15', 'Gluten, Laktoz', '1', '1', '1', '0', '2026-09-21 14:13:39'),
('16', '3', 'Spaghetti Pesto', 'Ev yapımı taze fesleğenli pesto sos, çam fıstığı ve parmesan peyniri ile.', 'Spaghetti al Pesto', 'Homemade fresh basil pesto sauce, pine nuts and parmesan cheese.', 'سباغيتي مع صلصة البيستو', NULL, 'Спагетти с песто', NULL, 'Spaghetti Pesto', NULL, '450', NULL, 'https://images.unsplash.com/photo-1551183053-bf91a1d81141?w=800&q=80', '', '610', '14', 'Gluten, Laktoz, Kuruyemiş', '1', '0', '2', '0', '2026-09-21 14:13:39'),
('17', '3', 'Fettuccine Alfredo', 'Kremalı Alfredo sos, ızgara tavuk dilimleri, taze kültür mantarı ve parmesan peyniri ile.', 'Fettuccine Alfredo', 'Creamy Alfredo sauce, tender grilled chicken, fresh mushrooms and parmesan.', 'فيتوتشيني ألفريدو بالدجاج', NULL, 'Феттучини Альфредо', NULL, 'Fettuccine Alfredo', NULL, '450', NULL, 'https://images.unsplash.com/photo-1645112411341-6c4fd023714a?w=800&q=80', 'Çok Satan', '740', '16', 'Gluten, Laktoz', '1', '1', '3', '0', '2026-09-21 14:13:39'),
('18', '3', 'Penne Arrabbiata', 'Acılı domates sosu, sarımsak, acı pul biber, taze fesleğen ve parmesan peyniri ile.', 'Penne all\'Arrabbiata', 'Spicy Italian tomato sauce, fresh garlic, chili flakes, basil and parmesan.', 'بيني أرابياتا الحارة', NULL, 'Пенне Арраббиата', NULL, 'Penne Arrabbiata', NULL, '450', NULL, 'https://images.unsplash.com/photo-1563379091339-03b21ab4a4f8?w=800&q=80', 'Acılı 🌶️', '580', '14', 'Gluten, Laktoz', '1', '0', '4', '0', '2026-09-21 14:13:39'),
('19', '4', 'Pizza Margherita', 'Özel domates sosu, bol mozzarella peyniri ve taze fesleğen yaprakları ile.', 'Pizza Margherita', 'Special tomato sauce, melted mozzarella cheese and fresh aromatic basil.', 'بيتزا مارغريتا', NULL, 'Пицца Маргарита', NULL, 'Pizza Margherita', NULL, '500', NULL, 'https://images.unsplash.com/photo-1604382355076-af4b0eb60143?w=800&q=80', 'Klasik', '690', '15', 'Gluten, Laktoz', '1', '1', '1', '0', '2026-09-21 14:13:39'),
('20', '4', 'Pizza Hawaii', 'Domates sosu, mozzarella peyniri ve tatlı ananas dilimleri ile.', 'Pizza Hawaii', 'Tomato sauce, melted mozzarella cheese and juicy pineapple pieces.', 'بيتزا هاواي بالأناناس', NULL, 'Пицца Гавайская', NULL, 'Pizza Hawaii', NULL, '450', NULL, 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=800&q=80', '', '670', '15', 'Gluten, Laktoz', '1', '0', '2', '0', '2026-09-21 14:13:39'),
('21', '4', 'Pizza 4 Peynirli', 'Mozzarella, parmesan, ezine peyniri ve eritilmiş cheddar peyniri uyumu.', 'Four Cheese Pizza', 'Four cheese blend: Mozzarella, Parmesan, regional Ezine and Cheddar.', 'بيتزا أربعة أجبان', NULL, 'Пицца 4 Сыра', NULL, 'Pizza Vier Käse', NULL, '550', NULL, 'https://images.unsplash.com/photo-1513104890138-7c749659a591?w=800&q=80', 'Özel Peynirli', '780', '15', 'Gluten, Laktoz', '1', '1', '3', '0', '2026-09-21 14:13:39'),
('22', '4', 'Pizza Karışık', 'Dana sucuğu, sosis, mantar, yeşil biber, siyah zeytin ve bol mozzarella peyniri.', 'Supreme Mixed Pizza', 'Beef sujuk, sausages, fresh mushrooms, bell peppers, black olives and mozzarella.', 'بيتزا سوبريم مشكلة', NULL, 'Пицца Ассорти', NULL, 'Pizza Gemischt', NULL, '600', NULL, 'https://images.unsplash.com/photo-1534308983496-4fabb1a015ee?w=800&q=80', 'Popüler', '840', '16', 'Gluten, Laktoz', '1', '1', '4', '0', '2026-09-21 14:13:39'),
('23', '5', 'Mare Burger', '150 gr ızgara dana köftesi, karamelize soğan, marul, domates, turşu ve ev yapımı burger sosu ile servis edilir. Patates kızartması eşliğinde.', 'Mare Special Burger', '150g grilled beef patty, caramelized onions, crisp lettuce, tomato, pickles and house special sauce. Served with fries.', 'ماري برغر الخاص', NULL, 'Фирменный Mare Бургер', NULL, 'Mare Spezialburger', NULL, '350', NULL, 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=800&q=80', 'Şefin İmzası', '790', '16', 'Gluten, Hardal', '1', '1', '1', '0', '2026-09-21 14:13:39'),
('24', '5', 'Cheeseburger', '150 gr ızgara dana köftesi, eritilmiş cheddar peyniri, marul, domates, turşu ve ev yapımı burger sosu ile servis edilir. Patates kızartması eşliğinde.', 'Classic Cheeseburger', '150g grilled beef patty, melted cheddar cheese, crisp lettuce, tomato, pickles and house burger sauce. Served with fries.', 'تشيز برغر كلاسيك', NULL, 'Чизбургер', NULL, 'Cheeseburger', NULL, '400', NULL, 'https://images.unsplash.com/photo-1550547660-d9450f859349?w=800&q=80', 'Popüler', '840', '16', 'Gluten, Laktoz, Hardal', '1', '1', '2', '0', '2026-09-21 14:13:39'),
('25', '6', 'Çoban Salata', 'Taze domates, çıtır salatalık, yeşil biber, mor soğan, maydanoz ve sızma zeytinyağı ile.', 'Shepherd\'s Salad', 'Diced tomatoes, crisp cucumbers, green peppers, red onion, parsley and extra virgin olive oil.', 'سلطة الراعي التركية', NULL, 'Пастуший салат', NULL, 'Hirtensalat', NULL, '200', NULL, 'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=800&q=80', '', '160', '8', '', '1', '0', '1', '0', '2026-09-21 14:13:39'),
('26', '6', 'Mevsim Salata', 'Taze mevsim yeşillikleri, domates, salatalık, rendelenmiş havuç ve limon zeytinyağı sosu ile.', 'Fresh Garden Salad', 'Seasonal fresh garden greens, tomatoes, cucumbers, carrots and lemon olive oil dressing.', 'سلطة الموسم الخضراء', NULL, 'Сезонный салат', NULL, 'Gemischter Gartensalat', NULL, '200', NULL, 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=800&q=80', '', '140', '8', '', '1', '0', '2', '0', '2026-09-21 14:13:39'),
('27', '6', 'Kaşık Salata', 'İncecik kıyılmış domates, salatalık, biber, soğan, ceviz ve ekşi nar ekşisi sosu ile.', 'Finely Chopped Spoon Salad', 'Finely diced tomatoes, cucumbers, peppers, onion, walnuts with rich pomegranate molasses.', 'سلطة الملعقة المفرومة ناعما', NULL, 'Салат Кашик', NULL, 'Löffelsalat', NULL, '250', NULL, 'https://images.unsplash.com/photo-1505253716362-afaea1d3d1af?w=800&q=80', '', '210', '10', 'Kuruyemiş', '1', '0', '3', '0', '2026-09-21 14:13:39'),
('28', '6', 'Caesar Salata', 'Çıtır göbek marul, ızgara tavuk göğsü dilimleri, kruton ekmeği, parmesan peyniri ve özel Caesar sos ile.', 'Chicken Caesar Salad', 'Crisp romaine lettuce, grilled chicken slices, croutons, parmesan flakes and Caesar dressing.', 'سلطة سيزر بالدجاج', NULL, 'Салат Цезарь с курицей', NULL, 'Caesar Salat mit Hähnchen', NULL, '600', NULL, 'https://images.unsplash.com/photo-1550304943-4f24f54ddde9?w=800&q=80', 'Özel', '460', '12', 'Gluten, Laktoz, Yumurta', '1', '1', '4', '0', '2026-09-21 14:13:39'),
('29', '7', 'Köfte', 'Özel baharatlarla harmanlanmış ızgara dana köfte, patates kızartması ve soğan piyazı ile.', 'Grilled Turkish Meatballs', 'Traditional grilled beef meatballs served with french fries and seasoned onion salad.', 'كفتة مشوية تركية', NULL, 'Кёфте на гриле', NULL, 'Gegrillte Frikadellen (Köfte)', NULL, '450', NULL, 'https://images.unsplash.com/photo-1529042410759-befb1204b468?w=800&q=80', 'Geleneksel', '680', '18', 'Gluten', '1', '1', '1', '0', '2026-09-21 14:13:39'),
('30', '7', 'Fajita', 'Cızırdayan döküm tavada sotelenmiş dana eti dilimleri, renkli biberler, patates kızartması ve salsa sos eşliğinde.', 'Sizzling Beef Fajita', 'Sizzling cast-iron beef strips sautéed with colorful peppers, served with fries and salsa.', 'فاهيتا اللحم البقري', NULL, 'Фахита с говядиной', NULL, 'Rindfleisch Fajita', NULL, '700', NULL, 'https://images.unsplash.com/photo-1534422298391-e4f8c172dddb?w=800&q=80', 'Şefin Spesiyali', '740', '20', '', '1', '1', '2', '0', '2026-09-21 14:13:39'),
('31', '7', 'Bonfile Lokum', 'Tereyağında mühürlenmiş pamuk gibi yumuşak dana bonfile lokum dilimleri, patates kızartması eşliğinde.', 'Beef Tenderloin Lokum', 'Ultra tender seared beef tenderloin medallions served with crispy french fries.', 'ستيك لحم بقر لوكوم تندرلوين', NULL, 'Локум из говяжьей вырезки', NULL, 'Rinderfilet Lokum Medaillons', NULL, '800', NULL, 'https://images.unsplash.com/photo-1558030006-450675393462?w=800&q=80', 'Premium ⭐', '710', '20', 'Laktoz', '1', '1', '3', '0', '2026-09-21 14:13:39'),
('32', '7', 'Bonfile', 'Izgara dana bonfile biftek, kremalı taze mantar sosu ve patates kızartması ile.', 'Grilled Tenderloin Steak', 'Prime grilled tenderloin steak served with creamy wild mushroom sauce and french fries.', 'ستيك فيليه اللحم مع الفطر', NULL, 'Стейк из говяжьей вырезки', NULL, 'Gegrilltes Rinderfilet Steak', NULL, '900', NULL, 'https://images.unsplash.com/photo-1544025162-d76694265947?w=800&q=80', 'Premium ⭐', '760', '22', 'Laktoz', '1', '1', '4', '0', '2026-09-21 14:13:39'),
('33', '7', 'Sac Kavurma', 'Özel sac tavada sotelenmiş leziz dana eti, domates, biber ve patates kızartması ile.', 'Traditional Sac Kavurma', 'Traditional wok-sautéed tender beef with tomatoes, peppers and french fries.', 'صاج كاورما لحم تركي', NULL, 'Сач кавурма', NULL, 'Traditionelles Sac Kavurma', NULL, '800', NULL, 'https://images.unsplash.com/photo-1603048588665-791ca8aea617?w=800&q=80', 'Klasik', '720', '20', '', '1', '1', '5', '0', '2026-09-21 14:13:39'),
('34', '7', 'Mantı', 'El yapımı Kayseri mantısı, sarımsaklı süzme yoğurt ve kızgın tereyağlı biber sosu ile servis edilir.', 'Handmade Turkish Manti', 'Handmade Turkish meat dumplings with garlic yogurt and sizzling pepper butter.', 'مانتي تركي يدوي بالزبادي', NULL, 'Турецкие манты', NULL, 'Handgemachte Manti Teigtaschen', NULL, '350', NULL, 'https://images.unsplash.com/photo-1625944525533-473f1a3d54e7?w=800&q=80', 'El Yapımı', '620', '15', 'Gluten, Laktoz, Yumurta', '1', '1', '6', '0', '2026-09-21 14:13:39'),
('35', '7', 'Çin Böreği', 'Tavuk eti, havuç, kabak, soya sosu ve susam ile sarılmış çıtır börekler.', 'Crispy Chicken Spring Rolls', 'Crispy fried spring rolls stuffed with chicken, julienned vegetables, soy sauce and sesame.', 'سبرينغ رول الدجاج المقرمش', NULL, 'Спринг-роллы с курицей', NULL, 'Knusprige Frühlingsrollen', NULL, '300', NULL, 'https://images.unsplash.com/photo-1544025162-d76694265947?w=800&q=80', 'Sıcak Başlangıç', '390', '12', 'Gluten, Soya, Susam', '1', '0', '7', '0', '2026-09-21 14:13:39'),
('36', '7', 'Paçanga Böreği', 'Kayseri pastırması, domates, biber ve eritilmiş kaşar peyniri ile çıtır kızarmış börek.', 'Traditional Pacanga Pastry', 'Crispy fried pastry filled with pastrami, peppers, tomatoes and melted kashar cheese.', 'فطائر باشانغا بالبسطرمة والجبن', NULL, 'Пачанга бёрек', NULL, 'Pacanga Teigtaschen mit Pastirma', NULL, '300', NULL, 'https://images.unsplash.com/photo-1608897013039-887f21d8c804?w=800&q=80', 'Sıcak Lezzet', '430', '12', 'Gluten, Laktoz', '1', '0', '8', '0', '2026-09-21 14:13:39'),
('37', '7', 'Körili Tavuk', 'Sotelenmiş tavuk göğsü, renkli biberler, mantar, aromatik krema köri sosu, mevsim yeşillikleri ve patates kızartması ile.', 'Savory Curry Chicken', 'Tender chicken breast sautéed with peppers, mushrooms in creamy curry sauce, with greens and fries.', 'دجاج بالكاري اللذيذ', NULL, 'Курица в соусе карри', NULL, 'Curry Hähnchen', NULL, '400', NULL, 'https://images.unsplash.com/photo-1604908176997-125f25cc6f3d?w=800&q=80', 'Popüler', '640', '18', 'Laktoz', '1', '1', '9', '0', '2026-09-21 14:13:39'),
('38', '8', 'Mezgit', 'Taze tava mezgit balığı, kırmızı soğan halkaları, taze roka ve domates ile servis edilir.', 'Pan-Fried Whiting Fish', 'Fresh pan-fried whiting fish served with red onion, wild arugula, lemon and tomato.', 'سمك البياض (ميزغيت) الطازج', NULL, 'Мерланг жареный', NULL, 'Gebratener Wittling Fisch', NULL, '700', NULL, 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?w=800&q=80', 'Günlük Taze 🐟', '490', '18', 'Balık', '1', '1', '1', '0', '2026-09-21 14:13:39'),
('39', '8', 'Sardalya', 'Körfez taze sardalya, ızgara edilmiş kırmızı soğan, roka ve domates ile servis edilir.', 'Grilled Gulf Sardines', 'Freshly grilled Gulf sardines served with onion, fresh arugula and lemon.', 'سردين خليج إدremit المشوي', NULL, 'Сардины на гриле', NULL, 'Gegrillte Sardinen', NULL, '500', NULL, 'https://images.unsplash.com/photo-1534604973900-c43ab4c2e0ab?w=800&q=80', 'Ege Lezzeti', '440', '15', 'Balık', '1', '0', '2', '0', '2026-09-21 14:13:39'),
('40', '8', 'Çipura', 'Kömür ateşinde ızgara taze çipura, zeytinyağı sosu, soğan, roka ve domates ile servis edilir.', 'Grilled Sea Bream', 'Charcoal grilled fresh whole sea bream served with olive oil sauce, arugula, onion and lemon.', 'سمك الدنيس المشوي', NULL, 'Дорадо на гриле', NULL, 'Gegrillte Dorade', NULL, '650', NULL, 'https://images.unsplash.com/photo-1534422298391-e4f8c172dddb?w=800&q=80', 'Izgara Balık', '510', '20', 'Balık', '1', '1', '3', '0', '2026-09-21 14:13:39'),
('41', '8', 'Levrek', 'Kömür ateşinde ızgara taze deniz levreği, soğan, roka ve domates ile servis edilir.', 'Grilled Sea Bass', 'Charcoal grilled Mediterranean sea bass served with red onion, fresh arugula and lemon.', 'سمك القاروص المشوي', NULL, 'Сибас на гриле', NULL, 'Gegrillter Wolfsbarsch', NULL, '650', NULL, 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?w=800&q=80', 'Şefin Tavsiyesi', '490', '20', 'Balık', '1', '1', '4', '0', '2026-09-21 14:13:39'),
('42', '8', 'Somon', 'Izgara somon fileto, soğan, taze roka, domates ve özel ev yapımı tartar sos ile servis edilir.', 'Grilled Norwegian Salmon', 'Grilled salmon steak served with house-made tartar sauce, fresh arugula, tomato and lemon.', 'فيليه سلمون مشوي مع صلصة التارتار', NULL, 'Лосось на гриле с тар-таром', NULL, 'Gegrilltes Lachsfilet mit Remoulade', NULL, '700', NULL, 'https://images.unsplash.com/photo-1467003909585-2f8a72700288?w=800&q=80', 'Şefin Spesiyali', '580', '18', 'Balık, Yumurta', '1', '1', '5', '0', '2026-09-21 14:13:39'),
('43', '8', 'Kalamar', 'Altın sarısı çıtır kalamar tava, soğan, taze roka, domates ve nefis cevizli tarator sos ile servis edilir.', 'Crispy Calamari Rings', 'Golden fried crispy calamari rings served with traditional walnut tarator sauce and lemon.', 'حلقات الحبار المقرمشة مع صلصة الطرطور', NULL, 'Кальмары во фритюре с тартаром', NULL, 'Knusprige Calamari Ringe', NULL, '750', NULL, 'https://images.unsplash.com/photo-1599488615731-7e5c2823ff28?w=800&q=80', 'Favori Meze', '540', '15', 'Deniz Ürünleri, Gluten, Kuruyemiş', '1', '1', '6', '0', '2026-09-21 14:13:39'),
('44', '8', 'Karides Güveç', 'Taze karides, sarımsak, domates, biber, tereyağı ve eritilmiş kaşar peyniri fırınlanarak hazırlanır.', 'Baked Shrimp Casserole', 'Sizzling hot clay pot baked shrimp with garlic, tomatoes, peppers, butter and melted cheese.', 'طاجن الروبيان بالزبدة والجبن', NULL, 'Креветки в глиняном горшочке', NULL, 'Gebackener Garnelenauflauf', NULL, '700', NULL, 'https://images.unsplash.com/photo-1565680018434-b513d5e5fd47?w=800&q=80', 'Güveçte Sıcak 🔥', '520', '18', 'Deniz Ürünleri, Laktoz', '1', '1', '7', '0', '2026-09-21 14:13:39'),
('45', '9', 'Çay', 'Taze demlenmiş geleneksel Türk çayı ince belli bardakta.', 'Turkish Black Tea', 'Freshly brewed traditional Turkish black tea.', 'شاي تركي أسود مخدر', NULL, 'Турецкий черный чай', NULL, 'Türkischer Schwarztee', NULL, '50', NULL, 'https://images.unsplash.com/photo-1576092768241-dec231879fc3?w=800&q=80', 'Taze Demleme', '2', '2', '', '1', '0', '1', '0', '2026-09-21 14:13:39'),
('46', '9', 'Su', 'Şişe doğal kaynak suyu.', 'Natural Spring Water', 'Bottled natural spring water.', 'مياه معدنية طبيعية', NULL, 'Минеральная вода', NULL, 'Stilles Mineralwasser', NULL, '25', NULL, 'https://images.unsplash.com/photo-1548839140-29a749e1bc4e?w=800&q=80', '', '0', '1', '', '1', '0', '2', '0', '2026-09-21 14:13:39'),
('47', '9', 'Soda', 'Doğal maden suyu şişe.', 'Sparkling Mineral Water', 'Bottled natural sparkling mineral water.', 'مياه فوارة معدنية', NULL, 'Газированная вода', NULL, 'Mineralwasser mit Kohlensäure', NULL, '50', NULL, 'https://images.unsplash.com/photo-1622483767028-3f66f32aef97?w=800&q=80', '', '0', '1', '', '1', '0', '3', '0', '2026-09-21 14:13:39'),
('48', '9', 'Coca Cola', 'Buz gibi soğuk Coca-Cola (Orijinal / Zero).', 'Coca Cola', 'Chilled Coca-Cola (Classic / Zero).', 'كوكاكولا مثلجة', NULL, 'Кока-Кола', NULL, 'Coca Cola', NULL, '120', NULL, 'https://images.unsplash.com/photo-1622483767028-3f66f32aef97?w=800&q=80', '', '140', '2', '', '1', '0', '4', '0', '2026-09-21 14:13:39'),
('49', '9', 'Fanta', 'Buz gibi soğuk portakallı Fanta.', 'Fanta Orange', 'Chilled sparkling orange Fanta.', 'فانتا برتقال', NULL, 'Фанта', NULL, 'Fanta Orange', NULL, '120', NULL, 'https://images.unsplash.com/photo-1624517452488-04869289c4ca?w=800&q=80', '', '150', '2', '', '1', '0', '5', '0', '2026-09-21 14:13:39'),
('50', '9', 'Sprite', 'Buz gibi ferahlatıcı limonlu gazoz.', 'Sprite Lemon-Lime', 'Chilled refreshing lemon-lime soda.', 'سبرايت ليمون منعش', NULL, 'Спрайт', NULL, 'Sprite', NULL, '120', NULL, 'https://images.unsplash.com/photo-1625772299848-391b6a87d7b3?w=800&q=80', '', '140', '2', '', '1', '0', '6', '0', '2026-09-21 14:13:39'),
('51', '9', 'Meyve Suyu', 'Şeftali, Vişne, Portakal veya Karışık meyve suyu seçenekleriyle.', 'Fruit Juice Selection', 'Choice of Peach, Sour Cherry, Orange or Mixed fruit juice.', 'عصائر فواكه مشكلة', NULL, 'Фруктовый сок', NULL, 'Fruchtsaft', NULL, '120', NULL, 'https://images.unsplash.com/photo-1613478223719-2ab802602423?w=800&q=80', '', '120', '2', '', '1', '0', '7', '0', '2026-09-21 14:13:39'),
('52', '9', 'Red Bull', 'Orijinal enerji içeceği (250 ml kutu).', 'Red Bull Energy Drink', 'Original energy drink (250 ml can).', 'مشروب الطاقة ريد بول', NULL, 'Ред Булл', NULL, 'Red Bull Energy Drink', NULL, '150', NULL, 'https://images.unsplash.com/photo-1527661591475-527312dd65f5?w=800&q=80', 'Enerji', '110', '2', '', '1', '0', '8', '0', '2026-09-21 14:13:39'),
('53', '9', 'Ice tea çeşitleri', 'Şeftali, Limon veya Mango aromalı soğuk çay.', 'Iced Tea Selection', 'Chilled iced tea with Peach, Lemon or Mango flavor.', 'شاي مثلج بنكهات مختلفة', NULL, 'Холодный чай в ассортименте', NULL, 'Eistee Auswahl', NULL, '120', NULL, 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?w=800&q=80', 'Soğuk & Ferah', '90', '2', '', '1', '0', '9', '0', '2026-09-21 14:13:39'),
('54', '9', 'Koruk Suyu', 'Ege\'nin meşhur geleneksel ekşi ve ferahlatıcı koruk suyu.', 'Traditional Verjuice (Koruk Suyu)', 'Famous traditional Aegean refreshing sour unripe grape juice.', 'عصير الحصرم التقليدي (كوروك)', NULL, 'Традиционный виноградный сок Корук', NULL, 'Traditioneller Verjus (Koruk Suyu)', NULL, '150', NULL, 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?w=800&q=80', 'Ege Spesiyali ⭐', '85', '2', '', '1', '1', '10', '0', '2026-09-21 14:13:39'),
('55', '9', 'Karadut Suyu', 'Doğal Ege karadut suyu, buz gibi servis edilir.', 'Natural Black Mulberry Juice', 'Pure natural Aegean black mulberry juice served ice cold.', 'عصير التوت الأسود الطبيعي', NULL, 'Сок черной шелковицы', NULL, 'Schwarzer Maulbeersaft', NULL, '150', NULL, 'https://images.unsplash.com/photo-1546173159-315724a31696?w=800&q=80', 'Doğal & Taze', '110', '2', '', '1', '1', '11', '0', '2026-09-21 14:13:39'),
('56', '9', 'Limonata', 'Taze sıkılmış limon, nane yaprakları ve buz ile ev yapımı nefis limonata.', 'Homemade Fresh Lemonade', 'Freshly squeezed homemade lemonade with fresh mint and crushed ice.', 'ليموناضة طبيعية طازجة بالنعناع', NULL, 'Домашний лимонад', NULL, 'Hausgemachte Limonade', NULL, '150', NULL, 'https://images.unsplash.com/photo-1523371067-2708361719b0?w=800&q=80', 'Ev Yapımı', '130', '3', '', '1', '1', '12', '0', '2026-09-21 14:13:39'),
('57', '10', 'Espresso', 'Yoğun aromalı taze çekilmiş tek shot espresso.', 'Single Espresso', 'Rich and intense single shot espresso.', 'إسبريسو سينغل', NULL, 'Эспрессо', NULL, 'Espresso', NULL, '150', NULL, 'https://images.unsplash.com/photo-1510591509098-f4fdc6d0ff04?w=800&q=80', '', '5', '3', '', '1', '0', '1', '0', '2026-09-21 14:13:39'),
('58', '10', 'Americano', 'Sıcak su ile dengelenmiş çift shot taze espresso.', 'Caffe Americano', 'Double shot espresso balanced with hot water.', 'أمريكانو ساخن', NULL, 'Американо', NULL, 'Americano', NULL, '150', NULL, 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?w=800&q=80', '', '10', '3', '', '1', '0', '2', '0', '2026-09-21 14:13:39'),
('59', '10', 'Filtre Kahve', 'Özel çekirdeklerden taze demlenmiş filtre kahve.', 'Brewed Filter Coffee', 'Freshly brewed aromatic filter roast coffee.', 'قهوة مقطرة بالفلتر', NULL, 'Фильтр-кофе', NULL, 'Filterkaffee', NULL, '150', NULL, 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=800&q=80', '', '5', '3', '', '1', '0', '3', '0', '2026-09-21 14:13:39'),
('60', '10', 'Türk Kahvesi', 'Lokum ve su eşliğinde közde pişirilmiş geleneksel bol köpüklü Türk kahvesi.', 'Traditional Turkish Coffee', 'Traditional Turkish coffee served with Turkish delight and water.', 'قهوة تركية تقليدية مع الحلقوم', NULL, 'Турецкий кофе', NULL, 'Türkischer Kaffee', NULL, '80', NULL, 'https://images.unsplash.com/photo-1578374173705-969cbe6f2d6b?w=800&q=80', 'Geleneksel', '15', '5', '', '1', '1', '4', '0', '2026-09-21 14:13:39'),
('61', '10', 'Nescafe', 'Klasik sıcak Nescafe (Sütlü veya Sade).', 'Nescafe Coffee', 'Classic instant Nescafe coffee (with or without milk).', 'نسكافيه كلاسيك ساخن', NULL, 'Нескафе', NULL, 'Nescafe', NULL, '125', NULL, 'https://images.unsplash.com/photo-1541167760496-1628856ab772?w=800&q=80', '', '50', '2', 'Laktoz', '1', '0', '5', '0', '2026-09-21 14:13:39'),
('62', '10', 'Cappuccino', 'Espresso, buharda ısıtılmış süt ve kadifemsi süt köpüğü.', 'Cappuccino', 'Espresso topped with steamed milk and rich velvety foam.', 'كابتشينو برغوة كريمية', NULL, 'Капучино', NULL, 'Cappuccino', NULL, '200', NULL, 'https://images.unsplash.com/photo-1534778101976-62847782c213?w=800&q=80', '', '130', '4', 'Laktoz', '1', '0', '6', '0', '2026-09-21 14:13:39'),
('63', '10', 'Latte', 'Espresso ve yumuşak içimli sıcak sütün buluşması.', 'Caffe Latte', 'Smooth espresso combined with steamed milk and light foam.', 'كافيه لاتيه', NULL, 'Латте', NULL, 'Caffe Latte', NULL, '175', NULL, 'https://images.unsplash.com/photo-1570968915860-54d5c301fa9f?w=800&q=80', '', '150', '4', 'Laktoz', '1', '0', '7', '0', '2026-09-21 14:13:39'),
('64', '10', 'Mocha', 'Espresso, sıcak çikolata sosu, süt ve süt köpüğü.', 'Caffe Mocha', 'Espresso with rich chocolate sauce, steamed milk and foam.', 'كافيه موكا بالشوكولاتة', NULL, 'Мокка', NULL, 'Caffe Mocha', NULL, '200', NULL, 'https://images.unsplash.com/photo-1578314670553-33350f601b1d?w=800&q=80', '', '240', '4', 'Laktoz', '1', '0', '8', '0', '2026-09-21 14:13:39'),
('65', '10', 'Iced Americano', 'Buz dolu bardakta çift shot espresso ve soğuk su.', 'Iced Americano', 'Chilled double shot espresso over ice and cold water.', 'آيس أمريكانو مثلج', NULL, 'Айс Американо', NULL, 'Iced Americano', NULL, '150', NULL, 'https://images.unsplash.com/photo-1517701550927-30cf4ba1dba5?w=800&q=80', 'Soğuk Kahve', '10', '3', '', '1', '0', '9', '0', '2026-09-21 14:13:39'),
('66', '10', 'Iced Latte', 'Espresso, soğuk süt ve bol buz ile ferahlatıcı lezzet.', 'Iced Latte', 'Espresso layered with cold milk and ice cubes.', 'آيس لاتيه بارد', NULL, 'Айс Латте', NULL, 'Iced Latte', NULL, '175', NULL, 'https://images.unsplash.com/photo-1517701550927-30cf4ba1dba5?w=800&q=80', 'Favori Soğuk', '130', '3', 'Laktoz', '1', '1', '10', '0', '2026-09-21 14:13:39'),
('67', '10', 'Iced Mocha', 'Çikolata sosu, taze espresso, soğuk süt ve buz.', 'Iced Mocha', 'Rich chocolate sauce, espresso, cold milk over ice.', 'آيس موكا بالشوكولاتة والثلج', NULL, 'Айс Мокка', NULL, 'Iced Mocha', NULL, '200', NULL, 'https://images.unsplash.com/photo-1578314670553-33350f601b1d?w=800&q=80', '', '230', '4', 'Laktoz', '1', '0', '11', '0', '2026-09-21 14:13:39'),
('68', '10', 'Frappe Çeşitleri', 'Buz gibi köpüklü soğuk Frappe kahvesi.', 'Frappe Selection', 'Refreshing frothy iced Greek style frappe coffee.', 'فرابيه بارد برغوة كثيفة', NULL, 'Фраппе в ассортименте', NULL, 'Frappe Auswahl', NULL, '200', NULL, 'https://images.unsplash.com/photo-1461023058943-07fcbe16d735?w=800&q=80', 'Köpüklü & Buzlu', '160', '4', 'Laktoz', '1', '0', '12', '0', '2026-09-21 14:13:39'),
('69', '10', 'Cheesecake', 'Günün tatlı seçeneği: San Sebastian, Frambuazlı veya Limonlu taze cheesecake.', 'Fresh Cheesecake of the Day', 'Fresh cake of the day: San Sebastian, Raspberry or Lemon cheesecake.', 'تشيز كيك حلوى اليوم الطازجة', NULL, 'Чизкейк (Десерт дня)', NULL, 'Käsekuchen (Tagesdessert)', NULL, '250', NULL, 'https://images.unsplash.com/photo-1533134242443-d4fd215305ad?w=800&q=80', 'Günün Tatlısı 🍰', '420', '3', 'Gluten, Laktoz, Yumurta', '1', '1', '13', '0', '2026-09-21 14:13:39'),
('70', '11', 'Tuborg Fıçı 33 cl', 'Buz gibi taze fıçı bira (33 cl).', 'Tuborg Draft Beer 33 cl', 'Crisp and cold draft beer (33 cl).', 'توبورغ برميل 33 مل', NULL, 'Туборг разливное 33 сл', NULL, 'Tuborg Fassbier 33 cl', NULL, '150', NULL, 'https://images.unsplash.com/photo-1535958636474-b021ee887b13?w=800&q=80', 'Fıçı', '140', '2', 'Gluten', '1', '0', '1', '0', '2026-09-21 14:13:39'),
('71', '11', 'Tuborg Fıçı 50 cl', 'Buz gibi taze fıçı bira (50 cl).', 'Tuborg Draft Beer 50 cl', 'Crisp and cold draft beer (50 cl).', 'توبورغ برميل 50 مل', NULL, 'Туборг разливное 50 сл', NULL, 'Tuborg Fassbier 50 cl', NULL, '200', NULL, 'https://images.unsplash.com/photo-1535958636474-b021ee887b13?w=800&q=80', 'Popüler Fıçı', '210', '2', 'Gluten', '1', '1', '2', '0', '2026-09-21 14:13:39'),
('72', '11', 'Carlsberg Fıçı 50 cl', 'Premium Danimarka fıçı birası (50 cl).', 'Carlsberg Draft Beer 50 cl', 'Premium Danish draft lager (50 cl).', 'كارلسبيرغ برميل 50 مل', NULL, 'Карлсберг разливное 50 сл', NULL, 'Carlsberg Fassbier 50 cl', NULL, '250', NULL, 'https://images.unsplash.com/photo-1535958636474-b021ee887b13?w=800&q=80', 'Premium Fıçı', '215', '2', 'Gluten', '1', '1', '3', '0', '2026-09-21 14:13:39'),
('73', '11', 'Tuborg Şişe', 'Tuborg Gold 50 cl şişe bira.', 'Tuborg Gold Bottle 50 cl', 'Tuborg Gold 50 cl bottled lager.', 'توبورغ زجاجة 50 مل', NULL, 'Туборг Голд бутылочное 50 сл', NULL, 'Tuborg Flasche 50 cl', NULL, '200', NULL, 'https://images.unsplash.com/photo-1608270199043-a616428c0576?w=800&q=80', '', '210', '2', 'Gluten', '1', '0', '4', '0', '2026-09-21 14:13:39'),
('74', '11', 'Tuborg Filtresiz', 'Yoğun aromalı Tuborg Filtresiz 50 cl şişe bira.', 'Tuborg Unfiltered 50 cl', 'Rich cloudy unfiltered lager 50 cl bottle.', 'توبورغ غير مفلتر 50 مل', NULL, 'Туборг нефильтрованное 50 сл', NULL, 'Tuborg Ungefiltert 50 cl', NULL, '250', NULL, 'https://images.unsplash.com/photo-1608270199043-a616428c0576?w=800&q=80', 'Filtresiz', '225', '2', 'Gluten', '1', '0', '5', '0', '2026-09-21 14:13:39'),
('75', '11', 'Carlsberg Şişe', 'Carlsberg 50 cl şişe bira.', 'Carlsberg Bottle 50 cl', 'Carlsberg 50 cl bottled beer.', 'كارلسبيرغ زجاجة 50 مل', NULL, 'Карлсберг бутылочное 50 сл', NULL, 'Carlsberg Flasche 50 cl', NULL, '250', NULL, 'https://images.unsplash.com/photo-1608270199043-a616428c0576?w=800&q=80', '', '210', '2', 'Gluten', '1', '0', '6', '0', '2026-09-21 14:13:39'),
('76', '11', 'Carlsberg Luna', 'Carlsberg Luna 50 cl şişe bira.', 'Carlsberg Luna 50 cl', 'Carlsberg Luna dry hopped lager 50 cl bottle.', 'كارلسبيرغ لونا 50 مل', NULL, 'Карлсберг Луна 50 сл', NULL, 'Carlsberg Luna 50 cl', NULL, '250', NULL, 'https://images.unsplash.com/photo-1608270199043-a616428c0576?w=800&q=80', '', '210', '2', 'Gluten', '1', '0', '7', '0', '2026-09-21 14:13:39'),
('77', '11', 'Tuborg Ice', 'Tuborg Ice ekstra ferahlatıcı şişe bira.', 'Tuborg Ice Bottle', 'Tuborg Ice extra refreshing bottled lager.', 'توبورغ آيس زجاجة', NULL, 'Туборг Айс', NULL, 'Tuborg Ice', NULL, '250', NULL, 'https://images.unsplash.com/photo-1608270199043-a616428c0576?w=800&q=80', 'Buz Gibi', '195', '2', 'Gluten', '1', '0', '8', '0', '2026-09-21 14:13:39'),
('78', '11', 'Sol', 'Meksika\'nın ferahlatıcı hafif lager birası (33 cl şişe).', 'Sol Mexican Beer 33 cl', 'Mexican refreshing crisp lager bottled beer (33 cl).', 'بيرة سول المكسيكية', NULL, 'Мексиканское пиво Сол', NULL, 'Sol Mexikanisches Bier', NULL, '250', NULL, 'https://images.unsplash.com/photo-1608270199043-a616428c0576?w=800&q=80', 'Meksika', '140', '2', 'Gluten', '1', '0', '9', '0', '2026-09-21 14:13:39'),
('79', '11', 'Heineken', 'Dünyaca ünlü Hollanda lager birası (33 cl şişe).', 'Heineken Premium Beer 33 cl', 'World famous Dutch premium lager bottled beer (33 cl).', 'هاينكن بريميوم 33 مل', NULL, 'Хайнекен 33 сл', NULL, 'Heineken Premium 33 cl', NULL, '300', NULL, 'https://images.unsplash.com/photo-1608270199043-a616428c0576?w=800&q=80', 'İthal', '145', '2', 'Gluten', '1', '1', '10', '0', '2026-09-21 14:13:39'),
('80', '11', 'Desperados', 'Tekila aromalı benzersiz lezzetli lager bira (33 cl şişe).', 'Desperados Tequila Flavored Beer', 'Tequila flavored distinctive lager beer (33 cl).', 'ديسبيرادوس بنكهة التيكيلا', NULL, 'Десперадос со вкусом текилы', NULL, 'Desperados Tequila Bier', NULL, '300', NULL, 'https://images.unsplash.com/photo-1608270199043-a616428c0576?w=800&q=80', 'Özel Lezzet', '170', '2', 'Gluten', '1', '1', '11', '0', '2026-09-21 14:13:39'),
('81', '11', 'Blanc', 'Fransız narenciye ve kişniş aromalı premium buğday birası (33 cl).', 'Kronenbourg 1664 Blanc 33 cl', 'French wheat beer with hints of citrus and coriander (33 cl).', 'كرونينبورغ 1664 بلانك بيرة القمح الفرنسية', NULL, 'Кроненбург 1664 Бланк', NULL, '1664 Blanc Weizenbier', NULL, '300', NULL, 'https://images.unsplash.com/photo-1608270199043-a616428c0576?w=800&q=80', 'Buğday Birası', '150', '2', 'Gluten', '1', '1', '12', '0', '2026-09-21 14:13:39'),
('82', '11', 'Guinness Kutu', 'İrlanda\'nın efsanevi kremamsı köpüklü siyah birası (44 cl kutu).', 'Guinness Draught Stout Can 44 cl', 'Legendary rich and creamy Irish dry stout (44 cl can).', 'غينيس بيرة سوداء أيرلندية 44 مل', NULL, 'Гиннесс стаут банка 44 сл', NULL, 'Guinness Extra Stout Dose 44 cl', NULL, '350', NULL, 'https://images.unsplash.com/photo-1567696911980-2eed69a46042?w=800&q=80', 'İrlanda Stout ⭐', '180', '2', 'Gluten', '1', '1', '13', '0', '2026-09-21 14:13:39'),
('83', '12', 'Kırmızı Şarap (Kadeh)', 'Seçkin yerli bağlardan kadeh kırmızı şarap. (Şişe seçenekleri için garsonunuza danışınız).', 'Red Wine (Glass)', 'Selected Turkish regional red wine by the glass.', 'كأس نبيذ أحمر فاخر', NULL, 'Красное вино (бокал)', NULL, 'Rotwein (Glas)', NULL, '400', NULL, 'https://images.unsplash.com/photo-1506377247377-2a5b3b417ebb?w=800&q=80', 'Kadeh', '125', '2', 'Sülfit', '1', '1', '1', '0', '2026-09-21 14:13:39'),
('84', '12', 'Beyaz Şarap (Kadeh)', 'Soğuk servis edilen aromatik kadeh beyaz şarap.', 'White Wine (Glass)', 'Chilled aromatic white wine by the glass.', 'كأس نبيذ أبيض منعش', NULL, 'Белое вино (бокал)', NULL, 'Weißwein (Glas)', NULL, '400', NULL, 'https://images.unsplash.com/photo-1584916201218-f4242ceb4809?w=800&q=80', 'Kadeh', '120', '2', 'Sülfit', '1', '1', '2', '0', '2026-09-21 14:13:39'),
('85', '12', 'Rosé Şarap (Kadeh)', 'Meyvemsi ve canlı kadeh pembe şarap.', 'Rosé Wine (Glass)', 'Fruity and fresh rosé wine by the glass.', 'كأس نبيذ روزيه وردي', NULL, 'Розовое вино (бокал)', NULL, 'Roséwein (Glas)', NULL, '400', NULL, 'https://images.unsplash.com/photo-1558001373-7b93ee48ffa0?w=800&q=80', 'Kadeh', '120', '2', 'Sülfit', '1', '0', '3', '0', '2026-09-21 14:13:39'),
('86', '12', 'Blush Şarap (Kadeh)', 'Hafif gövdeli, zarif ve ferahlatıcı kadeh blush şarap.', 'Blush Wine (Glass)', 'Delicate, crisp and refreshing blush wine by the glass.', 'كأس نبيذ بلش خفيف', NULL, 'Блаш вино (бокал)', NULL, 'Blush Wein (Glas)', NULL, '400', NULL, 'https://images.unsplash.com/photo-1558001373-7b93ee48ffa0?w=800&q=80', 'Kadeh', '115', '2', 'Sülfit', '1', '0', '4', '0', '2026-09-21 14:13:39'),
('87', '13', 'Negroni', 'Gin, Campari ve Sweet Vermouth\'un eşit oranlarda birleşiminden doğan İtalyan klasiği. Güçlü, acımsı ve aromatik.', 'Negroni', 'Gin, Campari, Sweet Vermouth. Bold, bitter-sweet and deeply aromatic Italian classic.', 'نيغروني إيطالي كلاسيك', NULL, 'Негрони', NULL, 'Negroni', NULL, '550', NULL, 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?w=800&q=80', 'Klasik', '195', '4', '', '1', '1', '1', '0', '2026-09-21 14:13:39'),
('88', '13', 'Whiskey Sour', 'Bourbon viski, limon ve şeker şurubunun dengesiyle hazırlanan zamansız klasik. Kadifemsi köpüğü ve canlı asiditesiyle öne çıkar. İçerik: Bourbon, Limon Suyu, Şeker Şurubu, Yumurta (opsiyonel).', 'Whiskey Sour', 'Bourbon, fresh lemon juice, sugar syrup, optional egg white foam.', 'ويسكي ساور بالليمون', NULL, 'Виски Сауэр', NULL, 'Whiskey Sour', NULL, '650', NULL, 'https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b?w=800&q=80', 'Popüler ⭐', '180', '5', 'Yumurta', '1', '1', '2', '0', '2026-09-21 14:13:39'),
('89', '13', 'Mojito', 'Beyaz rom, nane, lime ve soda ile hazırlanan Küba klasiği. Ferahlatıcı ve her zaman favori.', 'Classic Cuban Mojito', 'White rum, fresh mint, lime wedges, sugar and sparkling soda.', 'موهيتو كوبي كلاسيك بالنعناع', NULL, 'Мохито', NULL, 'Mojito', NULL, '550', NULL, 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?w=800&q=80', 'Çok Satan', '170', '4', '', '1', '1', '3', '0', '2026-09-21 14:13:39'),
('90', '13', 'Margarita', 'Tekilanın limon ve triple sec ile buluştuğu, dünyanın en çok sipariş edilen klasik kokteyllerinden biri.', 'Classic Margarita', 'Tequila, triple sec, fresh lime juice with salted rim.', 'مارغريتا مكسيكية بالتيكيلا', NULL, 'Маргарита', NULL, 'Margarita', NULL, '550', NULL, 'https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b?w=800&q=80', 'Klasik', '165', '4', '', '1', '1', '4', '0', '2026-09-21 14:13:39'),
('91', '13', 'Espresso Martini', 'Vodka, taze espresso ve kahve likörü ile gece sizi uyaracak modern klasik.', 'Espresso Martini', 'Vodka, freshly brewed espresso and coffee liqueur.', 'إسبريسو مارتيني بالقهوة والفودكا', NULL, 'Эспрессо Мартини', NULL, 'Espresso Martini', NULL, '550', NULL, 'https://images.unsplash.com/photo-1545438102-799c3991ffb2?w=800&q=80', 'Kahveli Kokteyl', '190', '4', '', '1', '1', '5', '0', '2026-09-21 14:13:39'),
('92', '13', 'Dry Martini', 'Sadelik ve zarafetin sembolü. Gin ve dry vermouth\'un kusursuz uyumu.', 'Classic Dry Martini', 'Gin, dry vermouth and green olive garnish.', 'دراي مارتيني بالزيتون', NULL, 'Драй Мартини', NULL, 'Dry Martini', NULL, '550', NULL, 'https://images.unsplash.com/photo-1575023782549-62ca0d244b39?w=800&q=80', '', '160', '3', '', '1', '0', '6', '0', '2026-09-21 14:13:39'),
('93', '13', 'Aperol Spritz', 'Aperol, prosecco ve soda ile hazırlanan hafif, ferahlatıcı ve yaz akşamlarının vazgeçilmezi.', 'Aperol Spritz', 'Aperol, prosecco, splash of club soda and fresh orange slice.', 'أبيرول سبريتز الإيطالي الصيفي', NULL, 'Апероль Спритц', NULL, 'Aperol Spritz', NULL, '550', NULL, 'https://images.unsplash.com/photo-1560512823-829485b8bf24?w=800&q=80', 'Yaz Favorisi ☀️', '135', '3', '', '1', '1', '7', '0', '2026-09-21 14:13:39'),
('94', '13', 'Gin Smash', 'Taze fesleğen, limon ve gin ile hazırlanan aromatik ve ferahlatıcı bir klasik.', 'Gin Basil Smash', 'Gin, fresh muddled basil leaves, lemon juice and sugar syrup.', 'جين سماش بالريحان والليمون', NULL, 'Джин Смэш', NULL, 'Gin Smash', NULL, '550', NULL, 'https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b?w=800&q=80', 'Aromatik', '165', '4', '', '1', '0', '8', '0', '2026-09-21 14:13:39'),
('95', '13', 'Aegean Bloom', 'Ege\'nin aromatik ruhundan ilham alan hafif ve zarif bir kokteyl. Otların ve narenciyenin dengeli uyumu ile ferahlatıcı bir imza lezzet. İçerik: Vodka, Triple Sec, Reyhan Şerbeti, Lime, Zeytinyağı.', 'Aegean Bloom (Signature)', 'Signature cocktail: Vodka, Triple Sec, purple basil syrup, lime juice, dash of olive oil.', 'إيجيان بلوم (كوكتيل بحر إيجة الخاص)', NULL, 'Aegean Bloom (Фирменный)', NULL, 'Aegean Bloom (Signature)', NULL, '650', NULL, 'https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b?w=800&q=80', 'İmza Kokteyl ⭐', '185', '5', '', '1', '1', '9', '0', '2026-09-21 14:13:39'),
('96', '13', 'Scarlet Breeze', 'Frambuazın canlı aroması, narenciye ve fesleğenle buluşarak meyvemsi ve ferah bir karakter sunar. İçerik: Gin, Limon Suyu, Frambuaz Likörü, Zencefil Şurubu, Fesleğen.', 'Scarlet Breeze (Signature)', 'Gin, lemon juice, raspberry liqueur, ginger syrup, fresh basil.', 'سكارليت بريز بالتوت والزنجبيل', NULL, 'Scarlet Breeze (Фирменный)', NULL, 'Scarlet Breeze', NULL, '650', NULL, 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?w=800&q=80', 'İmza Kokteyl ⭐', '190', '5', '', '1', '1', '10', '0', '2026-09-21 14:13:39'),
('97', '13', 'Gin Fizz', 'Gin, limon suyu, şeker şurubu ve soda ile hazırlanan narenciye ferahlığı.', 'Gin Fizz', 'Gin, fresh lemon juice, simple syrup topped with sparkling soda.', 'جين فيز المنعش', NULL, 'Джин Физз', NULL, 'Gin Fizz', NULL, '550', NULL, 'https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b?w=800&q=80', '', '155', '3', '', '1', '0', '11', '0', '2026-09-21 14:13:39'),
('98', '13', 'White Russian', 'Vodka, kahve likörü ve krema ile hazırlanan yumuşak ve tatlı karakterli klasik.', 'White Russian', 'Vodka, coffee liqueur and rich fresh cream.', 'وايت راشن بالكريمة والقهوة', NULL, 'Белый русский', NULL, 'White Russian', NULL, '650', NULL, 'https://images.unsplash.com/photo-1545438102-799c3991ffb2?w=800&q=80', 'Kremamsı', '240', '4', 'Laktoz', '1', '0', '12', '0', '2026-09-21 14:13:39'),
('99', '13', 'Black Russian', 'Vodka ve kahve likörünün güçlü ve sade birlikteliği.', 'Black Russian', 'Bold combination of vodka and rich coffee liqueur over ice.', 'بلاك راشن بالفودكا والقهوة', NULL, 'Черный русский', NULL, 'Black Russian', NULL, '650', NULL, 'https://images.unsplash.com/photo-1545438102-799c3991ffb2?w=800&q=80', '', '190', '3', '', '1', '0', '13', '0', '2026-09-21 14:13:39'),
('100', '13', 'Boulevardier', 'Negroni\'nin viskiyle güçlendirilmiş, daha gövdeli ve sıcak versiyonu. İçerik: Bourbon, Campari, Sweet Vermouth.', 'Boulevardier', 'Bourbon, Campari, Sweet Vermouth. Rich, bold whiskey version of Negroni.', 'بوليفاردييه بالويسكي والكامباري', NULL, 'Бульвардье', NULL, 'Boulevardier', NULL, '650', NULL, 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?w=800&q=80', 'Özel', '200', '4', '', '1', '0', '14', '0', '2026-09-21 14:13:39'),
('101', '13', 'Lynchburg Lemonade', 'Jack Daniel\'s, triple sec, limon ve şurup ile hazırlanan tatlı-ekşi dengesi mükemmel klasik.', 'Lynchburg Lemonade', 'Jack Daniel\'s whiskey, triple sec, lemon juice, sugar syrup, sprite.', 'لينشبورغ ليموناضة بالويسكي', NULL, 'Линчбург Лимонад', NULL, 'Lynchburg Lemonade', NULL, '550', NULL, 'https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b?w=800&q=80', 'Çok Sevilen', '195', '4', '', '1', '1', '15', '0', '2026-09-21 14:13:39'),
('102', '13', 'Long Island Iced Tea', 'Beş beyaz içkinin, turunçgil ve cola ile birleştiği efsane kokteyl. İçerik: Vodka, Gin, Rom, Tekila, Triple Sec, Limon, Cola.', 'Long Island Iced Tea', 'Vodka, Gin, White Rum, Tequila, Triple Sec, lemon juice, splash of cola.', 'لونغ آيلاند آيس تي القوي', NULL, 'Лонг Айленд Айс Ти', NULL, 'Long Island Iced Tea', NULL, '650', NULL, 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?w=800&q=80', 'Efsane Klasik', '260', '5', '', '1', '1', '16', '0', '2026-09-21 14:13:39'),
('103', '13', 'Cuba Libre', 'Rom, cola ve lime ile Küba\'nın en tanınan uzun içkisi.', 'Cuba Libre', 'Rum, fresh lime juice and Coca-Cola over ice.', 'كوبا ليبري بالروم والليمون والكولا', NULL, 'Куба Либре', NULL, 'Cuba Libre', NULL, '550', NULL, 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?w=800&q=80', '', '175', '3', '', '1', '0', '17', '0', '2026-09-21 14:13:39'),
('104', '13', 'Olympus Sour', 'Bitkisel aromalar ve Safari likörünün dengesiyle hazırlanan güçlü, kadifemsi ve canlı bir sour. İçerik: Tekila, Cin, Safari Likörü, Reyhan Şurubu, Lime.', 'Olympus Sour (Signature)', 'Tequila, Gin, Safari liqueur, purple basil syrup, fresh lime juice.', 'أوليمبوس ساور (كوكتيل أسطوري)', NULL, 'Olympus Sour (Фирменный)', NULL, 'Olympus Sour', NULL, '650', NULL, 'https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b?w=800&q=80', 'İmza Kokteyl ⭐', '205', '5', '', '1', '1', '18', '0', '2026-09-21 14:13:39'),
('105', '13', 'Moonflower', 'Mürver çiçeği ve kavunun zarif uyumuyla hazırlanan yumuşak içimli, çiçeksi ve meyvemsi premium kokteyl. İçerik: Vodka, St-Germain, Kavun Likörü, Limon.', 'Moonflower (Signature)', 'Vodka, St-Germain elderflower, melon liqueur, fresh lemon juice.', 'مون فلاور بزهر الخمان والشمام', NULL, 'Moonflower (Фирменный)', NULL, 'Moonflower', NULL, '650', NULL, 'https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b?w=800&q=80', 'İmza Kokteyl ⭐', '195', '5', '', '1', '1', '19', '0', '2026-09-21 14:13:39'),
('106', '13', 'Vanilla Noir', 'Vanilya ve viskinin buluştuğu yoğun aromalı, kadifemsi dokulu tatlı karakterli bir kokteyl. İçerik: Viski, Vanilya Şurubu, Vanilyalı Dondurma, Krema.', 'Vanilla Noir (Signature)', 'Whiskey, vanilla syrup, rich vanilla ice cream, fresh cream.', 'فانيليا نوار بالويسكي والآيس كريم', NULL, 'Vanilla Noir (Фирменный)', NULL, 'Vanilla Noir', NULL, '650', NULL, 'https://images.unsplash.com/photo-1545438102-799c3991ffb2?w=800&q=80', 'İmza Kokteyl ⭐', '290', '5', 'Laktoz', '1', '1', '20', '0', '2026-09-21 14:13:39'),
('107', '14', 'Absolute Votka Duble', 'Duble Absolut votka (Soda veya Tonik ile servis edilir).', 'Absolut Vodka Double', 'Absolut Vodka double measure served with soda or tonic.', 'فودكا أبسولوت دبل مع صودا أو تونيك', NULL, 'Абсолют Водка (Двойная)', NULL, 'Absolut Wodka Doppel', NULL, '350', NULL, 'https://images.unsplash.com/photo-1527061011665-3652c757a4d4?w=800&q=80', 'Vodka', '130', '2', '', '1', '0', '1', '0', '2026-09-21 14:13:39'),
('108', '14', 'Gordon\'s Gin Tek', 'Tek porsiyon Gordon\'s cin (Soda veya Tonik ile servis edilir).', 'Gordon\'s Gin Single', 'Gordon\'s London dry gin single served with soda or tonic.', 'جين غوردونز سينغل مع تونيك', NULL, 'Гордонс Джин (Одинарный)', NULL, 'Gordon\'s Gin Einzel', NULL, '300', NULL, 'https://images.unsplash.com/photo-1527061011665-3652c757a4d4?w=800&q=80', 'Gin', '110', '2', '', '1', '0', '2', '0', '2026-09-21 14:13:39'),
('109', '14', 'Gordon\'s Gin Duble', 'Duble porsiyon Gordon\'s cin (Soda veya Tonik ile servis edilir).', 'Gordon\'s Gin Double', 'Gordon\'s London dry gin double served with soda or tonic.', 'جين غوردونز دبل مع تونيك', NULL, 'Гордонс Джин (Двойной)', NULL, 'Gordon\'s Gin Doppel', NULL, '350', NULL, 'https://images.unsplash.com/photo-1527061011665-3652c757a4d4?w=800&q=80', 'Gin', '180', '2', '', '1', '0', '3', '0', '2026-09-21 14:13:39'),
('110', '14', 'Viski Tek', 'Tek porsiyon kaliteli viski (Buz veya su ile).', 'Whiskey Single', 'Premium whiskey single measure served on the rocks.', 'ويسكي فاخر سينغل', NULL, 'Виски (Одинарный)', NULL, 'Whisky Einzel', NULL, '350', NULL, 'https://images.unsplash.com/photo-1527061011665-3652c757a4d4?w=800&q=80', 'Viski', '115', '2', '', '1', '0', '4', '0', '2026-09-21 14:13:39'),
('111', '14', 'Viski Duble', 'Duble porsiyon kaliteli viski (Buz veya su ile).', 'Whiskey Double', 'Premium whiskey double measure served on the rocks.', 'ويسكي فاخر دبل', NULL, 'Виски (Двойной)', NULL, 'Whisky Doppel', NULL, '600', NULL, 'https://images.unsplash.com/photo-1527061011665-3652c757a4d4?w=800&q=80', 'Viski Duble', '230', '2', '', '1', '1', '5', '0', '2026-09-21 14:13:39'),
('112', '14', 'Tekila Shot', 'Tuz ve limon dilimi eşliğinde tekila shot.', 'Tequila Shot', 'Tequila shot served with salt and lemon slice.', 'شوت تيكيلا مع الملح والليمون', NULL, 'Шот Текилы', NULL, 'Tequila Shot', NULL, '250', NULL, 'https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b?w=800&q=80', 'Shot', '65', '1', '', '1', '0', '6', '0', '2026-09-21 14:13:40'),
('113', '14', 'Jägermeister Shot', 'Buz gibi dondurulmuş Jägermeister shot.', 'Jägermeister Shot', 'Ice cold Jägermeister herbal shot.', 'شوت ياغرميستر المثلج', NULL, 'Шот Егермейстер', NULL, 'Jägermeister Shot', NULL, '250', NULL, 'https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b?w=800&q=80', 'Shot', '70', '1', '', '1', '0', '7', '0', '2026-09-21 14:13:40'),
('114', '14', 'Votka Shot', 'Buz gibi soğutulmuş votka shot.', 'Vodka Shot', 'Chilled vodka shot.', 'شوت فودكا بارد', NULL, 'Шот Водки', NULL, 'Wodka Shot', NULL, '200', NULL, 'https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b?w=800&q=80', 'Shot', '65', '1', '', '1', '0', '8', '0', '2026-09-21 14:13:40'),
('115', '14', 'Yeni Rakı Kadeh Tek', 'Tek kadeh Yeni Rakı (Soğuk su ve buz ile servis edilir).', 'Yeni Raki Single Glass', 'Single glass Yeni Raki served with chilled water and ice.', 'عرق يني راكي كأس مفرد', NULL, 'Ени Ракы (Одинарный бокал)', NULL, 'Yeni Raki Einzelglas', NULL, '250', NULL, 'https://images.unsplash.com/photo-1527061011665-3652c757a4d4?w=800&q=80', 'Rakı', '130', '2', '', '1', '0', '9', '0', '2026-09-21 14:13:40'),
('116', '14', 'Yeni Rakı Kadeh Duble', 'Duble kadeh Yeni Rakı (Soğuk su ve buz ile servis edilir).', 'Yeni Raki Double Glass', 'Double glass Yeni Raki served with chilled water and ice.', 'عرق يني راكي كأس مضاعف', NULL, 'Ени Ракы (Двойной бокал)', NULL, 'Yeni Raki Doppelglas', NULL, '400', NULL, 'https://images.unsplash.com/photo-1527061011665-3652c757a4d4?w=800&q=80', 'Rakı Duble', '250', '2', '', '1', '1', '10', '0', '2026-09-21 14:13:40'),
('117', '14', 'Yeni Rakı 20 cl', '20 cl Yeni Rakı şişe.', 'Yeni Raki 20 cl Bottle', '20 cl bottle Yeni Raki.', 'عرق يني راكي زجاجة 20 مل', NULL, 'Ени Ракы 20 сл', NULL, 'Yeni Raki 20 cl Flasche', NULL, '1000', NULL, 'https://images.unsplash.com/photo-1527061011665-3652c757a4d4?w=800&q=80', 'Şişe 20 cl', '500', '2', '', '1', '0', '11', '0', '2026-09-21 14:13:40'),
('118', '14', 'Yeni Rakı 35 cl', '35 cl Yeni Rakı şişe.', 'Yeni Raki 35 cl Bottle', '35 cl bottle Yeni Raki (35 cl).', 'عرق يني راكي زجاجة 35 مل', NULL, 'Ени Ракы 35 сл', NULL, 'Yeni Raki 35 cl Flasche', NULL, '1400', NULL, 'https://images.unsplash.com/photo-1527061011665-3652c757a4d4?w=800&q=80', 'Şişe 35 cl', '875', '2', '', '1', '1', '12', '0', '2026-09-21 14:13:40'),
('119', '14', 'Yeni Rakı 50 cl', '50 cl Yeni Rakı şişe.', 'Yeni Raki 50 cl Bottle', '50 cl bottle Yeni Raki (50 cl).', 'عرق يني راكي زجاجة 50 مل', NULL, 'Ени Ракى 50 сл', NULL, 'Yeni Raki 50 cl Flasche', NULL, '2000', NULL, 'https://images.unsplash.com/photo-1527061011665-3652c757a4d4?w=800&q=80', 'Şişe 50 cl', '1250', '2', '', '1', '0', '13', '0', '2026-09-21 14:13:40'),
('120', '14', 'Yeni Rakı 70 cl', '70 cl Yeni Rakı şişe (Büyük).', 'Yeni Raki 70 cl Bottle', '70 cl bottle Yeni Raki (70 cl).', 'عرق يني راكي زجاجة 70 مل', NULL, 'Ени Ракы 70 сл', NULL, 'Yeni Raki 70 cl Flasche', NULL, '2400', NULL, 'https://images.unsplash.com/photo-1527061011665-3652c757a4d4?w=800&q=80', 'Şişe 70 cl', '1750', '2', '', '1', '1', '14', '0', '2026-09-21 14:13:40'),
('121', '14', 'Yeni Rakı 100 cl', '100 cl Yeni Rakı şişe (1 Litre).', 'Yeni Raki 100 cl Bottle (1L)', '100 cl bottle Yeni Raki (1 Liter).', 'عرق يني راكي زجاجة 100 مل (1 لتر)', NULL, 'Ени Ракы 100 сл (1 Литр)', NULL, 'Yeni Raki 100 cl Flasche (1 Liter)', NULL, '3000', NULL, 'https://images.unsplash.com/photo-1527061011665-3652c757a4d4?w=800&q=80', 'Şişe 1 Litre', '2500', '2', '', '1', '1', '15', '0', '2026-09-21 14:13:40');

DROP TABLE IF EXISTS `tables`;
CREATE TABLE `tables` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_number` varchar(50) NOT NULL,
  `table_name` varchar(100) NOT NULL,
  `token` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `table_number` (`table_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `tables` (`id`, `table_number`, `table_name`, `token`, `created_at`) VALUES
('1', '1', 'Masa 1', '6df5741e7e459b7f', '2026-09-21 14:13:39'),
('2', '2', 'Masa 2', '4310ccf7bdcfcd50', '2026-09-21 14:13:39'),
('3', '3', 'Masa 3', '7958ac4b8f7b204d', '2026-09-21 14:13:39'),
('4', '4', 'Masa 4', 'b5bbb39370df0dbb', '2026-09-21 14:13:39'),
('5', '5', 'Masa 5', '4543d3f513a807f1', '2026-09-21 14:13:39'),
('6', '6', 'Masa 6', '7bf1002f2c72d6a3', '2026-09-21 14:13:39'),
('7', '7', 'Masa 7', '268dfcece587d327', '2026-09-21 14:13:39'),
('8', '8', 'Masa 8', '71cc31d0dd07b3e0', '2026-09-21 14:13:39'),
('9', '9', 'Masa 9', '9d4f887d00058960', '2026-09-21 14:13:39'),
('10', '10', 'Masa 10', '48730c6dbabc3a92', '2026-09-21 14:13:39'),
('11', 'B1', 'Bahçe 1', '6940d6daab5c9892', '2026-09-21 14:13:39'),
('12', 'B2', 'Bahçe 2', '021fb37bd4bb31d8', '2026-09-21 14:13:39'),
('13', 'B3', 'Bahçe 3', 'b18fefd7bb77d11e', '2026-09-21 14:13:39'),
('14', 'B4', 'Bahçe 4', '2fa03b77d65b15f9', '2026-09-21 14:13:39'),
('15', 'I1', 'İskele 1', '61fb1f06f6484e1b', '2026-09-21 14:13:39'),
('16', 'I2', 'İskele 2', '3aeac040c4e07e03', '2026-09-21 14:13:39'),
('17', 'I3', 'İskele 3', 'e017a3313bad5076', '2026-09-21 14:13:39'),
('18', 'I4', 'İskele 4', 'ec4c39b98c1bfc0e', '2026-09-21 14:13:39'),
('19', 'T1', 'Teras 1', '17c3d96ffb6d64f3', '2026-09-21 14:13:39'),
('20', 'T2', 'Teras 2', '1f7289c6cb510828', '2026-09-21 14:13:39');

DROP TABLE IF EXISTS `waiter_calls`;
CREATE TABLE `waiter_calls` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_number` varchar(50) NOT NULL,
  `call_type` varchar(50) DEFAULT 'waiter',
  `note` text DEFAULT NULL,
  `status` varchar(30) DEFAULT 'pending',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_number` varchar(50) NOT NULL,
  `total_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` varchar(30) DEFAULT 'pending',
  `customer_note` text DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(200) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `options_json` text DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `product_options`;
CREATE TABLE `product_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `group_name` varchar(100) NOT NULL,
  `option_name` varchar(100) NOT NULL,
  `extra_price` decimal(10,2) DEFAULT 0.00,
  `is_required` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `stories`;
CREATE TABLE `stories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL,
  `image` varchar(255) NOT NULL,
  `link` varchar(255) DEFAULT '',
  `sort_order` int(11) DEFAULT 0,
  `is_active` int(11) DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `stories` (`id`, `title`, `image`, `link`, `sort_order`, `is_active`, `created_at`) VALUES
('1', 'İmza Kokteyller', 'https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b?w=600&q=80', '#cat-13', '1', '1', '2026-09-21 14:13:39'),
('2', 'Taze Balıklar', 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?w=600&q=80', '#cat-8', '2', '1', '2026-09-21 14:13:39'),
('3', 'Bonfile & Lokum', 'https://images.unsplash.com/photo-1558030006-450675393462?w=600&q=80', '#cat-7', '3', '1', '2026-09-21 14:13:39'),
('4', 'Taş Fırın Pizza', 'https://images.unsplash.com/photo-1604382355076-af4b0eb60143?w=600&q=80', '#cat-4', '4', '1', '2026-09-21 14:13:39'),
('5', 'Soğuk Kahveler', 'https://images.unsplash.com/photo-1517701550927-30cf4ba1dba5?w=600&q=80', '#cat-10', '5', '1', '2026-09-21 14:13:39'),
('6', 'Buz Gibi Biralar', 'https://images.unsplash.com/photo-1535958636474-b021ee887b13?w=600&q=80', '#cat-11', '6', '1', '2026-09-21 14:13:39');

DROP TABLE IF EXISTS `feedback`;
CREATE TABLE `feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_number` varchar(50) DEFAULT '',
  `rating` int(11) NOT NULL DEFAULT 5,
  `name` varchar(100) DEFAULT '',
  `comment` text DEFAULT NULL,
  `is_read` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `feedback` (`id`, `table_number`, `rating`, `name`, `comment`, `is_read`, `created_at`) VALUES
('1', '4', '5', 'Ahmet Kaya', 'Bonfile lokum, karides güveç ve Aegean Bloom kokteyl tek kelimeyle muhteşemdi! Altınoluk\'un en iyi yeri.', '1', '2026-09-21 14:13:40'),
('2', 'I2', '5', 'Selin Yılmaz', 'İskelede gün batımı eşliğinde taze levrek, kalamar ve soğuk bira harikaydı, servis çok hızlı.', '1', '2026-09-21 14:13:40'),
('3', 'B1', '5', 'Mehmet Öz', 'Mare Burger, pizzalar ve buz gibi ev yapımı limonata çok lezzetli, bayıldık.', '1', '2026-09-21 14:13:40'),
('4', 'T1', '5', 'Canan D.', 'Whiskey Sour ve San Sebastian cheesecake harika bir ikili oldu. Manzara ve müzikler çok kaliteli.', '1', '2026-09-21 14:13:40');

DROP TABLE IF EXISTS `events`;
CREATE TABLE `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `performer` varchar(150) DEFAULT '',
  `event_date` date DEFAULT NULL,
  `event_time` varchar(20) DEFAULT '20:30',
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT '',
  `is_active` int(11) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `events` (`id`, `title`, `performer`, `event_date`, `event_time`, `description`, `image`, `is_active`, `sort_order`, `created_at`) VALUES
('1', 'Gün Batımı Akustik Caz & Saksafon', 'Tuna Trio & Zeynep (Saksafon)', '2026-09-22', '20:30', 'Altınoluk Körfezi gün batımında şarap ve özel kokteyller eşliğinde canlı caz ziyafeti.', 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?w=800&q=80', '1', '1', '2026-09-22 05:36:04'),
('2', 'Ege & Akdeniz Şarap ve Peynir Tadımı', 'Mare & Monte Sommelier Atölyesi', '2026-09-24', '19:00', 'Kaz Dağları eteklerinden yerel peynirler ve seçkin şarap eşleştirmeleri.', 'https://images.unsplash.com/photo-1510812431401-41d2bd2722f3?w=800&q=80', '1', '2', '2026-09-22 05:36:04'),
('3', 'Gitar & Akustik Riviera Melodileri', 'Caner Arslan (Solo Akustik)', '2026-09-26', '21:00', 'Deniz kenarında nostaljik Akdeniz şarkıları ve İtalyan ezgileri.', 'https://images.unsplash.com/photo-1465847899084-d164df4dedc6?w=800&q=80', '1', '3', '2026-09-22 05:36:04');

SET FOREIGN_KEY_CHECKS = 1;
