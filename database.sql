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
('1', 'restaurant_name', 'HOTEL MARE & MONTE BISTRO', '2026-09-21 13:57:15'),
('2', 'restaurant_slogan', 'Altınoluk (Est. 1985)', '2026-09-21 13:57:15'),
('3', 'currency', '₺', '2026-09-21 13:57:15'),
('4', 'theme_color', '#d97706', '2026-09-21 13:57:15'),
('5', 'theme_mode', 'dark', '2026-09-21 13:57:15'),
('6', 'logo_dark_url', '', '2026-09-21 13:57:15'),
('7', 'logo_light_url', '', '2026-09-21 13:57:15'),
('8', 'banner_url', 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=1200&q=80', '2026-09-21 13:57:15'),
('9', 'wifi_name', 'MareMonte_Guest', '2026-09-21 13:57:15'),
('10', 'wifi_pass', 'MareMonte1985', '2026-09-21 13:57:15'),
('11', 'phone', '+90 (266) 396 00 00', '2026-09-21 13:57:15'),
('12', 'instagram', 'hotelmaremonte', '2026-09-21 13:57:15'),
('13', 'address', 'İskele Mah. Sahil Cad. No:14, Altınoluk / Balıkesir', '2026-09-21 13:57:15'),
('14', 'enable_order', '0', '2026-09-21 13:57:15'),
('15', 'enable_multi_lang', '1', '2026-09-21 13:57:15'),
('16', 'enable_kitchen', '1', '2026-09-21 13:57:15'),
('17', 'enable_stories', '1', '2026-09-21 13:57:15'),
('18', 'enable_popup', '1', '2026-09-21 13:57:15'),
('19', 'enable_feedback', '1', '2026-09-21 13:57:15'),
('20', 'enable_allergens_filter', '1', '2026-09-21 13:57:15'),
('21', 'enable_waiter_call', '1', '2026-09-21 13:57:15'),
('22', 'popup_title', '🌊 Hotel Mare & Monte Bistro Hoş Geldiniz!', '2026-09-21 13:57:15'),
('23', 'popup_desc', '1985\'ten beri Altınoluk sahilinde eşsiz lezzetler. Günlük taze deniz ürünlerimiz ve şefin spesiyallerini keşfedin!', '2026-09-21 13:57:15'),
('24', 'popup_image', 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?w=800&q=80', '2026-09-21 13:57:15'),
('25', 'popup_btn_text', 'Deniz Ürünlerini İncele', '2026-09-21 13:57:15'),
('26', 'popup_btn_link', '#cat-8', '2026-09-21 13:57:15'),
('27', 'google_maps_url', 'https://maps.google.com/?q=Hotel+Mare+Monte+Altinoluk', '2026-09-21 13:57:15');

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
('1', 'admin', '$2y$10$yq5zmI86W7JEje6mKxHuReJU4NJqc4acxSL6Qr0B./.0D.gW1d072', 'Mare & Monte Admin', 'superadmin', '2026-09-21 13:57:15');

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
('1', 'Başlangıçlar', 'baslangiclar', 'utensils', 'https://images.unsplash.com/photo-1547592166-23ac45744acd?w=600&q=80', 'Starters & Soups', 'المقبلات والشوربات', 'Закуски и супы', 'Vorspeisen & Suppen', '1', '1', '2026-09-21 13:57:15'),
('2', 'Atıştırmalıklar', 'atistirmaliklar', 'burger', 'https://images.unsplash.com/photo-1541592106381-b31e9677c0e5?w=600&q=80', 'Snacks & Bites', 'الوجبات الخفيفة والمقبلات', 'Снеки и закуски', 'Snacks & Fingerfood', '2', '1', '2026-09-21 13:57:15'),
('3', 'Makarnalar', 'makarnalar', 'bowl-rice', 'https://images.unsplash.com/photo-1621996346565-e3adc644d946?w=600&q=80', 'Pastas', 'المعكرونة الإيطالية', 'Паста', 'Pasta & Nudeln', '3', '1', '2026-09-21 13:57:15'),
('4', 'Pizzalar', 'pizzalar', 'pizza-slice', 'https://images.unsplash.com/photo-1604382355076-af4b0eb60143?w=600&q=80', 'Pizzas', 'البيتزا', 'Пицца', 'Pizzen', '4', '1', '2026-09-21 13:57:15'),
('5', 'Burgerler', 'burgerler', 'burger', 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=600&q=80', 'Burgers', 'البرغر', 'Бургеры', 'Burger', '5', '1', '2026-09-21 13:57:15'),
('6', 'Salatalar', 'salatalar', 'leaf', 'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=600&q=80', 'Salads', 'السلطات الطازجة', 'Салаты', 'Salate', '6', '1', '2026-09-21 13:57:15'),
('7', 'Ana Yemekler', 'ana-yemekler', 'utensils', 'https://images.unsplash.com/photo-1544025162-d76694265947?w=600&q=80', 'Main Courses', 'الأطباق الرئيسية والمشاوي', 'Основные блюда', 'Hauptgerichte', '7', '1', '2026-09-21 13:57:15'),
('8', 'Deniz Ürünleri', 'deniz-urunleri', 'fish', 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?w=600&q=80', 'Seafood & Fresh Fish', 'المأكولات البحرية والأسماك الطازجة', 'Рыба и морепродукты', 'Meeresfrüchte & Frischer Fisch', '8', '1', '2026-09-21 13:57:15');

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
('1', '1', 'Günün Çorbası', 'Şefimizin günlük olarak taze malzemelerle hazırladığı leziz çorba.', 'Soup of the Day', 'Freshly prepared delicious hot soup of the day by our chef.', 'شوربة اليوم', NULL, 'Суп дня', NULL, 'Tagessuppe', NULL, '120', NULL, 'https://images.unsplash.com/photo-1547592166-23ac45744acd?w=800&q=80', 'Şefin Seçimi', '180', '5', 'Gluten', '1', '1', '1', '0', '2026-09-21 13:57:15'),
('2', '1', 'Ezine Peynir Dilimi', 'Meşhur Ezine peyniri, taze domates ve çıtır salatalık ile.', 'Ezine Cheese Slice', 'Famous local Ezine cheese served with fresh tomatoes and cucumbers.', 'شريحة جبن إزيني', NULL, 'Сыр Эзине', NULL, 'Ezine Käsescheibe', NULL, '150', NULL, 'https://images.unsplash.com/photo-1589881133595-a3c085cb731d?w=800&q=80', 'Yöresel', '210', '5', 'Laktoz', '1', '0', '2', '0', '2026-09-21 13:57:15'),
('3', '1', 'Meze', 'Günün seçkisiyle taze hazırlanan nefis mevsim mezeleri.', 'Traditional Meze', 'Fresh selection of daily traditional cold mezes.', 'مقبلات باردة', NULL, 'Традиционные мезе', NULL, 'Traditionelle Meze', NULL, '200', NULL, 'https://images.unsplash.com/photo-1541544741938-0af808871cc0?w=800&q=80', 'Günlük Taze', '190', '5', '', '1', '0', '3', '0', '2026-09-21 13:57:15'),
('4', '1', 'Yerli Peynir Tabağı', 'Bölgesel seçkin peynir çeşitleri, kuru meyveler ve ceviz ile.', 'Local Cheese Platter', 'Fine selection of regional Turkish cheeses served with dried fruits and walnuts.', 'طبق الأجبان المحلية', NULL, 'Тарелка местных сыров', NULL, 'Lokale Käseplatte', NULL, '600', NULL, 'https://images.unsplash.com/photo-1631379578550-7038263db699?w=800&q=80', 'Popüler', '450', '8', 'Laktoz, Kuruyemiş', '1', '1', '4', '0', '2026-09-21 13:57:15'),
('5', '2', 'Peynirli Tost', 'Eritilmiş kaşar peyniri, domates ve salatalık eşliğinde.', 'Cheese Toast', 'Melted cheese toast served with tomato and cucumber slices.', 'توست الجبن', NULL, 'Тост с сыром', NULL, 'Käsetoast', NULL, '175', NULL, 'https://images.unsplash.com/photo-1528735602780-2552fd46c7af?w=800&q=80', '', '380', '8', 'Gluten, Laktoz', '1', '0', '1', '0', '2026-09-21 13:57:15'),
('6', '2', 'Sucuklu Tost', 'Kızarmış dana sucuğu, çeri domates ve salatalık eşliğinde.', 'Sujuk Toast', 'Spicy beef sujuk toast served with cherry tomatoes and cucumber.', 'توست السجق', NULL, 'Тост с суджуком', NULL, 'Sujuk Toast', NULL, '200', NULL, 'https://images.unsplash.com/photo-1584776296944-ab6fb57b0bdd?w=800&q=80', '', '430', '8', 'Gluten', '1', '0', '2', '0', '2026-09-21 13:57:15'),
('7', '2', 'Karışık Tost', 'Dana sucuğu, kaşar peyniri, salatalık ve çeri domates eşliğinde.', 'Mixed Toast', 'Beef sujuk and kashar cheese toast served with tomato and cucumber.', 'توست مشكل', NULL, 'Смешанный тост', NULL, 'Gemischter Toast', NULL, '250', NULL, 'https://images.unsplash.com/photo-1528735602780-2552fd46c7af?w=800&q=80', 'Klasik', '490', '8', 'Gluten, Laktoz', '1', '1', '3', '0', '2026-09-21 13:57:15'),
('8', '2', 'Gözleme', 'Dana kıyma, peynirli, kaşarlı veya ıspanaklı seçenekleriyle sacda taze pişirilir.', 'Traditional Turkish Gözleme', 'Handmade Turkish flatbread filled with minced beef, feta, kashar or spinach.', 'فطائر غوزليمة التركية', NULL, 'Гёзлеме', NULL, 'Gözleme Fladenbrot', NULL, '300', NULL, 'https://images.unsplash.com/photo-1627308595229-7830a5c91f9f?w=800&q=80', 'El Açması', '450', '12', 'Gluten, Laktoz', '1', '1', '4', '0', '2026-09-21 13:57:15'),
('9', '2', 'Bira Tabağı', 'Patates kızartması, nugget, sigara böreği, sosis ve çıtır soğan halkası.', 'Beer Snack Platter', 'French fries, chicken nuggets, crispy rolls, sausages and onion rings.', 'طبق مقبلات مشكل كبير', NULL, 'Пивная тарелка', NULL, 'Bierteller Snackplatte', NULL, '550', NULL, 'https://images.unsplash.com/photo-1541592106381-b31e9677c0e5?w=800&q=80', 'Favori', '950', '15', 'Gluten, Laktoz', '1', '1', '5', '0', '2026-09-21 13:57:15'),
('10', '2', 'Patates Kızartması', 'Altın sarısı çıtır patates kızartması, özel baharat karışımı ile.', 'French Fries', 'Golden crispy french fries seasoned with house spice blend.', 'بطاطس مقلية مقرمشة', NULL, 'Картофель фри', NULL, 'Pommes Frites', NULL, '200', NULL, 'https://images.unsplash.com/photo-1576107232684-1279f3908594?w=800&q=80', '', '420', '8', '', '1', '0', '6', '0', '2026-09-21 13:57:15'),
('11', '2', 'Karides Cipsi', 'Özel baharat ve dip sos ile servis edilen çıtır karides cipsi.', 'Prawn Crackers', 'Crispy prawn crackers served with seasoning and dipping sauce.', 'رقائق الجمبري المقرمشة', NULL, 'Креветочные чипсы', NULL, 'Krabbenchips', NULL, '250', NULL, 'https://images.unsplash.com/photo-1563379091339-03b21ab4a4f8?w=800&q=80', '', '280', '5', 'Deniz Ürünleri', '1', '0', '7', '0', '2026-09-21 13:57:15'),
('12', '2', 'Soğan Halkası', 'Çıtır kaplamalı soğan halkaları, patates kızartması ve soslar ile.', 'Crispy Onion Rings', 'Crispy battered onion rings served with golden french fries and dips.', 'حلقات البصل المقرمشة', NULL, 'Луковые кольца', NULL, 'Zwiebelringe', NULL, '350', NULL, 'https://images.unsplash.com/photo-1639024471287-032f66e5f039?w=800&q=80', '', '480', '10', 'Gluten', '1', '0', '8', '0', '2026-09-21 13:57:15'),
('13', '2', 'Nuggets', 'Çıtır tavuk nugget dilimleri, patates kızartması ve soslar ile.', 'Chicken Nuggets', 'Crispy chicken nuggets served with golden fries and dipping sauces.', 'قطع الدجاج المقرمشة (ناجتس)', NULL, 'Куриные наггетсы', NULL, 'Chicken Nuggets', NULL, '350', NULL, 'https://images.unsplash.com/photo-1562967914-608f82629710?w=800&q=80', '', '520', '10', 'Gluten', '1', '0', '9', '0', '2026-09-21 13:57:15'),
('14', '2', 'Sosis Tabağı', 'Izgara sosis dilimleri, patates kızartması ve hardal ile.', 'Sausage Platter', 'Grilled sausage slices served with crispy french fries and mustard.', 'طبق النقانق المشوية', NULL, 'Тарелка с колбасками', NULL, 'Würstchenplatte', NULL, '350', NULL, 'https://images.unsplash.com/photo-1585325701165-351af916e581?w=800&q=80', '', '560', '10', 'Hardal', '1', '0', '10', '0', '2026-09-21 13:57:15'),
('15', '3', 'Spaghetti Bolonez', 'Geleneksel ağır ateşte pişmiş dana kıymalı Bolonez sos ve rendelenmiş parmesan peyniri ile.', 'Spaghetti Bolognese', 'Traditional slow-simmered beef Bolognese sauce topped with fresh parmesan.', 'سباغيتي بولونيز', NULL, 'Спагетти Болоньезе', NULL, 'Spaghetti Bolognese', NULL, '450', NULL, 'https://images.unsplash.com/photo-1621996346565-e3adc644d946?w=800&q=80', 'İtalyan', '680', '15', 'Gluten, Laktoz', '1', '1', '1', '0', '2026-09-21 13:57:15'),
('16', '3', 'Spaghetti Pesto', 'Ev yapımı taze fesleğenli pesto sos, çam fıstığı ve parmesan peyniri ile.', 'Spaghetti al Pesto', 'Homemade fresh basil pesto sauce, pine nuts and parmesan cheese.', 'سباغيتي مع صلصة البيستو', NULL, 'Спагетти с песто', NULL, 'Spaghetti Pesto', NULL, '450', NULL, 'https://images.unsplash.com/photo-1551183053-bf91a1d81141?w=800&q=80', '', '610', '14', 'Gluten, Laktoz, Kuruyemiş', '1', '0', '2', '0', '2026-09-21 13:57:15'),
('17', '3', 'Fettuccine Alfredo', 'Kremalı Alfredo sos, ızgara tavuk dilimleri, taze kültür mantarı ve parmesan peyniri ile.', 'Fettuccine Alfredo', 'Creamy Alfredo sauce, tender grilled chicken, fresh mushrooms and parmesan.', 'فيتوتشيني ألفريدو بالدجاج', NULL, 'Феттучини Альфредо', NULL, 'Fettuccine Alfredo', NULL, '450', NULL, 'https://images.unsplash.com/photo-1645112411341-6c4fd023714a?w=800&q=80', 'Çok Satan', '740', '16', 'Gluten, Laktoz', '1', '1', '3', '0', '2026-09-21 13:57:15'),
('18', '3', 'Penne Arrabbiata', 'Acılı domates sosu, sarımsak, acı pul biber, taze fesleğen ve parmesan peyniri ile.', 'Penne all\'Arrabbiata', 'Spicy Italian tomato sauce, fresh garlic, chili flakes, basil and parmesan.', 'بيني أرابياتا الحارة', NULL, 'Пенне Арраббиата', NULL, 'Penne Arrabbiata', NULL, '450', NULL, 'https://images.unsplash.com/photo-1563379091339-03b21ab4a4f8?w=800&q=80', 'Acılı 🌶️', '580', '14', 'Gluten, Laktoz', '1', '0', '4', '0', '2026-09-21 13:57:15'),
('19', '4', 'Pizza Margherita', 'Özel domates sosu, bol mozzarella peyniri ve taze fesleğen yaprakları ile.', 'Pizza Margherita', 'Special tomato sauce, melted mozzarella cheese and fresh aromatic basil.', 'بيتزا مارغريتا', NULL, 'Пицца Маргарита', NULL, 'Pizza Margherita', NULL, '500', NULL, 'https://images.unsplash.com/photo-1604382355076-af4b0eb60143?w=800&q=80', 'Klasik', '690', '15', 'Gluten, Laktoz', '1', '1', '1', '0', '2026-09-21 13:57:15'),
('20', '4', 'Pizza Hawaii', 'Domates sosu, mozzarella peyniri ve tatlı ananas dilimleri ile.', 'Pizza Hawaii', 'Tomato sauce, melted mozzarella cheese and juicy pineapple pieces.', 'بيتزا هاواي بالأناناس', NULL, 'Пицца Гавайская', NULL, 'Pizza Hawaii', NULL, '450', NULL, 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=800&q=80', '', '670', '15', 'Gluten, Laktoz', '1', '0', '2', '0', '2026-09-21 13:57:15'),
('21', '4', 'Pizza 4 Peynirli', 'Mozzarella, parmesan, ezine peyniri ve eritilmiş cheddar peyniri uyumu.', 'Four Cheese Pizza', 'Four cheese blend: Mozzarella, Parmesan, regional Ezine and Cheddar.', 'بيتزا أربعة أجبان', NULL, 'Пицца 4 Сыра', NULL, 'Pizza Vier Käse', NULL, '550', NULL, 'https://images.unsplash.com/photo-1513104890138-7c749659a591?w=800&q=80', 'Özel Peynirli', '780', '15', 'Gluten, Laktoz', '1', '1', '3', '0', '2026-09-21 13:57:15'),
('22', '4', 'Pizza Karışık', 'Dana sucuğu, sosis, mantar, yeşil biber, siyah zeytin ve bol mozzarella peyniri.', 'Supreme Mixed Pizza', 'Beef sujuk, sausages, fresh mushrooms, bell peppers, black olives and mozzarella.', 'بيتزا سوبريم مشكلة', NULL, 'Пицца Ассорти', NULL, 'Pizza Gemischt', NULL, '600', NULL, 'https://images.unsplash.com/photo-1534308983496-4fabb1a015ee?w=800&q=80', 'Popüler', '840', '16', 'Gluten, Laktoz', '1', '1', '4', '0', '2026-09-21 13:57:15'),
('23', '5', 'Mare Burger', '150 gr ızgara dana köftesi, karamelize soğan, marul, domates, turşu ve ev yapımı burger sosu ile servis edilir. Patates kızartması eşliğinde.', 'Mare Special Burger', '150g grilled beef patty, caramelized onions, crisp lettuce, tomato, pickles and house special sauce. Served with fries.', 'ماري برغر الخاص', NULL, 'Фирменный Mare Бургер', NULL, 'Mare Spezialburger', NULL, '350', NULL, 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=800&q=80', 'Şefin İmzası', '790', '16', 'Gluten, Hardal', '1', '1', '1', '0', '2026-09-21 13:57:15'),
('24', '5', 'Cheeseburger', '150 gr ızgara dana köftesi, eritilmiş cheddar peyniri, marul, domates, turşu ve ev yapımı burger sosu ile servis edilir. Patates kızartması eşliğinde.', 'Classic Cheeseburger', '150g grilled beef patty, melted cheddar cheese, crisp lettuce, tomato, pickles and house burger sauce. Served with fries.', 'تشيز برغر كلاسيك', NULL, 'Чизбургер', NULL, 'Cheeseburger', NULL, '400', NULL, 'https://images.unsplash.com/photo-1550547660-d9450f859349?w=800&q=80', 'Popüler', '840', '16', 'Gluten, Laktoz, Hardal', '1', '1', '2', '0', '2026-09-21 13:57:15'),
('25', '6', 'Çoban Salata', 'Taze domates, çıtır salatalık, yeşil biber, mor soğan, maydanoz ve sızma zeytinyağı ile.', 'Shepherd\'s Salad', 'Diced tomatoes, crisp cucumbers, green peppers, red onion, parsley and extra virgin olive oil.', 'سلطة الراعي التركية', NULL, 'Пастуший салат', NULL, 'Hirtensalat', NULL, '200', NULL, 'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=800&q=80', '', '160', '8', '', '1', '0', '1', '0', '2026-09-21 13:57:15'),
('26', '6', 'Mevsim Salata', 'Taze mevsim yeşillikleri, domates, salatalık, rendelenmiş havuç ve limon zeytinyağı sosu ile.', 'Fresh Garden Salad', 'Seasonal fresh garden greens, tomatoes, cucumbers, carrots and lemon olive oil dressing.', 'سلطة الموسم الخضراء', NULL, 'Сезонный салат', NULL, 'Gemischter Gartensalat', NULL, '200', NULL, 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=800&q=80', '', '140', '8', '', '1', '0', '2', '0', '2026-09-21 13:57:15'),
('27', '6', 'Kaşık Salata', 'İncecik kıyılmış domates, salatalık, biber, soğan, ceviz ve ekşi nar ekşisi sosu ile.', 'Finely Chopped Spoon Salad', 'Finely diced tomatoes, cucumbers, peppers, onion, walnuts with rich pomegranate molasses.', 'سلطة الملعقة المفرومة ناعما', NULL, 'Салат Кашик', NULL, 'Löffelsalat', NULL, '250', NULL, 'https://images.unsplash.com/photo-1505253716362-afaea1d3d1af?w=800&q=80', '', '210', '10', 'Kuruyemiş', '1', '0', '3', '0', '2026-09-21 13:57:15'),
('28', '6', 'Caesar Salata', 'Çıtır göbek marul, ızgara tavuk göğsü dilimleri, kruton ekmeği, parmesan peyniri ve özel Caesar sos ile.', 'Chicken Caesar Salad', 'Crisp romaine lettuce, grilled chicken slices, croutons, parmesan flakes and Caesar dressing.', 'سلطة سيزر بالدجاج', NULL, 'Салат Цезарь с курицей', NULL, 'Caesar Salat mit Hähnchen', NULL, '600', NULL, 'https://images.unsplash.com/photo-1550304943-4f24f54ddde9?w=800&q=80', 'Özel', '460', '12', 'Gluten, Laktoz, Yumurta', '1', '1', '4', '0', '2026-09-21 13:57:15'),
('29', '7', 'Köfte', 'Özel baharatlarla harmanlanmış ızgara dana köfte, patates kızartması ve soğan piyazı ile.', 'Grilled Turkish Meatballs', 'Traditional grilled beef meatballs served with french fries and seasoned onion salad.', 'كفتة مشوية تركية', NULL, 'Кёфте на гриле', NULL, 'Gegrillte Frikadellen (Köfte)', NULL, '450', NULL, 'https://images.unsplash.com/photo-1529042410759-befb1204b468?w=800&q=80', 'Geleneksel', '680', '18', 'Gluten', '1', '1', '1', '0', '2026-09-21 13:57:15'),
('30', '7', 'Fajita', 'Cızırdayan döküm tavada sotelenmiş dana eti dilimleri, renkli biberler, patates kızartması ve salsa sos eşliğinde.', 'Sizzling Beef Fajita', 'Sizzling cast-iron beef strips sautéed with colorful peppers, served with fries and salsa.', 'فاهيتا اللحم البقري', NULL, 'Фахита с говядиной', NULL, 'Rindfleisch Fajita', NULL, '700', NULL, 'https://images.unsplash.com/photo-1534422298391-e4f8c172dddb?w=800&q=80', 'Şefin Spesiyali', '740', '20', '', '1', '1', '2', '0', '2026-09-21 13:57:15'),
('31', '7', 'Bonfile Lokum', 'Tereyağında mühürlenmiş pamuk gibi yumuşak dana bonfile lokum dilimleri, patates kızartması eşliğinde.', 'Beef Tenderloin Lokum', 'Ultra tender seared beef tenderloin medallions served with crispy french fries.', 'ستيك لحم بقر لوكوم تندرلوين', NULL, 'Локум из говяжьей вырезки', NULL, 'Rinderfilet Lokum Medaillons', NULL, '800', NULL, 'https://images.unsplash.com/photo-1558030006-450675393462?w=800&q=80', 'Premium ⭐', '710', '20', 'Laktoz', '1', '1', '3', '0', '2026-09-21 13:57:15'),
('32', '7', 'Bonfile', 'Izgara dana bonfile biftek, kremalı taze mantar sosu ve patates kızartması ile.', 'Grilled Tenderloin Steak', 'Prime grilled tenderloin steak served with creamy wild mushroom sauce and french fries.', 'ستيك فيليه اللحم مع الفطر', NULL, 'Стейк из говяжьей вырезки', NULL, 'Gegrilltes Rinderfilet Steak', NULL, '900', NULL, 'https://images.unsplash.com/photo-1544025162-d76694265947?w=800&q=80', 'Premium ⭐', '760', '22', 'Laktoz', '1', '1', '4', '0', '2026-09-21 13:57:15'),
('33', '7', 'Sac Kavurma', 'Özel sac tavada sotelenmiş leziz dana eti, domates, biber ve patates kızartması ile.', 'Traditional Sac Kavurma', 'Traditional wok-sautéed tender beef with tomatoes, peppers and french fries.', 'صاج كاورما لحم تركي', NULL, 'Сач кавурма', NULL, 'Traditionelles Sac Kavurma', NULL, '800', NULL, 'https://images.unsplash.com/photo-1603048588665-791ca8aea617?w=800&q=80', 'Klasik', '720', '20', '', '1', '1', '5', '0', '2026-09-21 13:57:15'),
('34', '7', 'Mantı', 'El yapımı Kayseri mantısı, sarımsaklı süzme yoğurt ve kızgın tereyağlı biber sosu ile servis edilir.', 'Handmade Turkish Manti', 'Handmade Turkish meat dumplings with garlic yogurt and sizzling pepper butter.', 'مانتي تركي يدوي بالزبادي', NULL, 'Турецкие манты', NULL, 'Handgemachte Manti Teigtaschen', NULL, '350', NULL, 'https://images.unsplash.com/photo-1625944525533-473f1a3d54e7?w=800&q=80', 'El Yapımı', '620', '15', 'Gluten, Laktoz, Yumurta', '1', '1', '6', '0', '2026-09-21 13:57:15'),
('35', '7', 'Çin Böreği', 'Tavuk eti, havuç, kabak, soya sosu ve susam ile sarılmış çıtır börekler.', 'Crispy Chicken Spring Rolls', 'Crispy fried spring rolls stuffed with chicken, julienned vegetables, soy sauce and sesame.', 'سبرينغ رول الدجاج المقرمش', NULL, 'Спринг-роллы с курицей', NULL, 'Knusprige Frühlingsrollen', NULL, '300', NULL, 'https://images.unsplash.com/photo-1544025162-d76694265947?w=800&q=80', 'Sıcak Başlangıç', '390', '12', 'Gluten, Soya, Susam', '1', '0', '7', '0', '2026-09-21 13:57:15'),
('36', '7', 'Paçanga Böreği', 'Kayseri pastırması, domates, biber ve eritilmiş kaşar peyniri ile çıtır kızarmış börek.', 'Traditional Pacanga Pastry', 'Crispy fried pastry filled with pastrami, peppers, tomatoes and melted kashar cheese.', 'فطائر باشانغا بالبسطرمة والجبن', NULL, 'Пачанга бёрек', NULL, 'Pacanga Teigtaschen mit Pastirma', NULL, '300', NULL, 'https://images.unsplash.com/photo-1608897013039-887f21d8c804?w=800&q=80', 'Sıcak Lezzet', '430', '12', 'Gluten, Laktoz', '1', '0', '8', '0', '2026-09-21 13:57:15'),
('37', '7', 'Körili Tavuk', 'Sotelenmiş tavuk göğsü, renkli biberler, mantar, aromatik krema köri sosu, mevsim yeşillikleri ve patates kızartması ile.', 'Savory Curry Chicken', 'Tender chicken breast sautéed with peppers, mushrooms in creamy curry sauce, with greens and fries.', 'دجاج بالكاري اللذيذ', NULL, 'Курица в соусе карри', NULL, 'Curry Hähnchen', NULL, '400', NULL, 'https://images.unsplash.com/photo-1604908176997-125f25cc6f3d?w=800&q=80', 'Popüler', '640', '18', 'Laktoz', '1', '1', '9', '0', '2026-09-21 13:57:15'),
('38', '8', 'Mezgit', 'Taze tava mezgit balığı, kırmızı soğan halkaları, taze roka ve domates ile servis edilir.', 'Pan-Fried Whiting Fish', 'Fresh pan-fried whiting fish served with red onion, wild arugula, lemon and tomato.', 'سمك البياض (ميزغيت) الطازج', NULL, 'Мерланг жареный', NULL, 'Gebratener Wittling Fisch', NULL, '700', NULL, 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?w=800&q=80', 'Günlük Taze 🐟', '490', '18', 'Balık', '1', '1', '1', '0', '2026-09-21 13:57:15'),
('39', '8', 'Sardalya', 'Körfez taze sardalya, ızgara edilmiş kırmızı soğan, roka ve domates ile servis edilir.', 'Grilled Gulf Sardines', 'Freshly grilled Gulf sardines served with onion, fresh arugula and lemon.', 'سردين خليج إدremit المشوي', NULL, 'Сардины на гриле', NULL, 'Gegrillte Sardinen', NULL, '500', NULL, 'https://images.unsplash.com/photo-1534604973900-c43ab4c2e0ab?w=800&q=80', 'Ege Lezzeti', '440', '15', 'Balık', '1', '0', '2', '0', '2026-09-21 13:57:15'),
('40', '8', 'Çipura', 'Kömür ateşinde ızgara taze çipura, zeytinyağı sosu, soğan, roka ve domates ile servis edilir.', 'Grilled Sea Bream', 'Charcoal grilled fresh whole sea bream served with olive oil sauce, arugula, onion and lemon.', 'سمك الدنيس المشوي', NULL, 'Дорадо на гриле', NULL, 'Gegrillte Dorade', NULL, '650', NULL, 'https://images.unsplash.com/photo-1534422298391-e4f8c172dddb?w=800&q=80', 'Izgara Balık', '510', '20', 'Balık', '1', '1', '3', '0', '2026-09-21 13:57:15'),
('41', '8', 'Levrek', 'Kömür ateşinde ızgara taze deniz levreği, soğan, roka ve domates ile servis edilir.', 'Grilled Sea Bass', 'Charcoal grilled Mediterranean sea bass served with red onion, fresh arugula and lemon.', 'سمك القاروص المشوي', NULL, 'Сибас на гриле', NULL, 'Gegrillter Wolfsbarsch', NULL, '650', NULL, 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?w=800&q=80', 'Şefin Tavsiyesi', '490', '20', 'Balık', '1', '1', '4', '0', '2026-09-21 13:57:15'),
('42', '8', 'Somon', 'Izgara somon fileto, soğan, taze roka, domates ve özel ev yapımı tartar sos ile servis edilir.', 'Grilled Norwegian Salmon', 'Grilled salmon steak served with house-made tartar sauce, fresh arugula, tomato and lemon.', 'فيليه سلمون مشوي مع صلصة التارتار', NULL, 'Лосось на гриле с тар-таром', NULL, 'Gegrilltes Lachsfilet mit Remoulade', NULL, '700', NULL, 'https://images.unsplash.com/photo-1467003909585-2f8a72700288?w=800&q=80', 'Şefin Spesiyali', '580', '18', 'Balık, Yumurta', '1', '1', '5', '0', '2026-09-21 13:57:15'),
('43', '8', 'Kalamar', 'Altın sarısı çıtır kalamar tava, soğan, taze roka, domates ve nefis cevizli tarator sos ile servis edilir.', 'Crispy Calamari Rings', 'Golden fried crispy calamari rings served with traditional walnut tarator sauce and lemon.', 'حلقات الحبار المقرمشة مع صلصة الطرطور', NULL, 'Кальмары во фритюре с тартаром', NULL, 'Knusprige Calamari Ringe', NULL, '750', NULL, 'https://images.unsplash.com/photo-1599488615731-7e5c2823ff28?w=800&q=80', 'Favori Meze', '540', '15', 'Deniz Ürünleri, Gluten, Kuruyemiş', '1', '1', '6', '0', '2026-09-21 13:57:15'),
('44', '8', 'Karides Güveç', 'Taze karides, sarımsak, domates, biber, tereyağı ve eritilmiş kaşar peyniri fırınlanarak hazırlanır.', 'Baked Shrimp Casserole', 'Sizzling hot clay pot baked shrimp with garlic, tomatoes, peppers, butter and melted cheese.', 'طاجن الروبيان بالزبدة والجبن', NULL, 'Креветки в глиняном горшочке', NULL, 'Gebackener Garnelenauflauf', NULL, '700', NULL, 'https://images.unsplash.com/photo-1565680018434-b513d5e5fd47?w=800&q=80', 'Güveçte Sıcak 🔥', '520', '18', 'Deniz Ürünleri, Laktoz', '1', '1', '7', '0', '2026-09-21 13:57:15');

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
('1', '1', 'Masa 1', '273f5cabb85bbd95', '2026-09-21 13:57:15'),
('2', '2', 'Masa 2', 'cd6f127ae3f5cc26', '2026-09-21 13:57:15'),
('3', '3', 'Masa 3', '8db316882c80d6e7', '2026-09-21 13:57:15'),
('4', '4', 'Masa 4', '6413b6f7c0f30795', '2026-09-21 13:57:15'),
('5', '5', 'Masa 5', 'd19c25cef54f0bb6', '2026-09-21 13:57:15'),
('6', '6', 'Masa 6', '488a01f882514610', '2026-09-21 13:57:15'),
('7', '7', 'Masa 7', '9abd9e2efa3111f0', '2026-09-21 13:57:15'),
('8', '8', 'Masa 8', '74bf1f270e924cf8', '2026-09-21 13:57:15'),
('9', '9', 'Masa 9', 'd51ed672b75c1fb4', '2026-09-21 13:57:15'),
('10', '10', 'Masa 10', '8201d8c7bdae70a1', '2026-09-21 13:57:15'),
('11', 'B1', 'Bahçe 1', '5e9ead195fb7e249', '2026-09-21 13:57:15'),
('12', 'B2', 'Bahçe 2', 'adc9169320764eb4', '2026-09-21 13:57:15'),
('13', 'B3', 'Bahçe 3', '1df6e69d572e9924', '2026-09-21 13:57:15'),
('14', 'B4', 'Bahçe 4', '1f967f388bbce9e9', '2026-09-21 13:57:15'),
('15', 'I1', 'İskele 1', '749c5a493ce2a9f9', '2026-09-21 13:57:15'),
('16', 'I2', 'İskele 2', 'abc6fda5fb5566ad', '2026-09-21 13:57:15'),
('17', 'I3', 'İskele 3', 'b819f53203247bbf', '2026-09-21 13:57:15'),
('18', 'I4', 'İskele 4', '0ea6a0bd4a05cbf5', '2026-09-21 13:57:15'),
('19', 'T1', 'Teras 1', '9598abba03ff8e9b', '2026-09-21 13:57:15'),
('20', 'T2', 'Teras 2', 'd78c59a1798e31f6', '2026-09-21 13:57:15');

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
('1', 'Taze Balıklar', 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?w=600&q=80', '#cat-8', '1', '1', '2026-09-21 13:57:15'),
('2', 'Bonfile & Lokum', 'https://images.unsplash.com/photo-1558030006-450675393462?w=600&q=80', '#cat-7', '2', '1', '2026-09-21 13:57:15'),
('3', 'Taş Fırın Pizza', 'https://images.unsplash.com/photo-1604382355076-af4b0eb60143?w=600&q=80', '#cat-4', '3', '1', '2026-09-21 13:57:15'),
('4', 'Mare Burger', 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=600&q=80', '#cat-5', '4', '1', '2026-09-21 13:57:15'),
('5', 'Atıştırmalıklar', 'https://images.unsplash.com/photo-1541592106381-b31e9677c0e5?w=600&q=80', '#cat-2', '5', '1', '2026-09-21 13:57:15');

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
('1', '4', '5', 'Ahmet Kaya', 'Bonfile lokum ve karides güveç tek kelimeyle muhteşemdi! Altınoluk\'un en iyi lezzeti.', '1', '2026-09-21 13:57:15'),
('2', 'I2', '5', 'Selin Yılmaz', 'İskelede gün batımı eşliğinde taze levrek ve kalamar harikaydı, servis çok hızlı.', '1', '2026-09-21 13:57:15'),
('3', 'B1', '5', 'Mehmet Öz', 'Mare Burger ve pizzalar çok lezzetli, çocuklar da bayıldı. Teşekkürler.', '1', '2026-09-21 13:57:15');

SET FOREIGN_KEY_CHECKS = 1;
