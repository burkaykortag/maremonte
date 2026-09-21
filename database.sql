-- MySQL Database Dump for Hotel Mare Monte QR Menu
-- Compatible with MySQL 5.7+ / 8.0+ / MariaDB

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `settings`;
DROP TABLE IF EXISTS `admins`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `tables`;
DROP TABLE IF EXISTS `waiter_calls`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `product_options`;
DROP TABLE IF EXISTS `stories`;
DROP TABLE IF EXISTS `feedback`;


CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) DEFAULT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `tables` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_no` varchar(50) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `qr_code_url` varchar(255) DEFAULT '',
  `is_active` int(11) DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `table_no` (`table_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `waiter_calls` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_no` varchar(50) NOT NULL,
  `call_type` varchar(50) DEFAULT 'garson',
  `note` varchar(255) DEFAULT '',
  `status` varchar(30) DEFAULT 'bekliyor',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_no` varchar(50) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` varchar(50) DEFAULT 'bekliyor',
  `note` text DEFAULT NULL,
  `customer_name` varchar(100) DEFAULT '',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(200) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `options_text` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `product_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `group_name` varchar(100) NOT NULL,
  `option_name` varchar(150) NOT NULL,
  `extra_price` decimal(10,2) DEFAULT 0.00,
  `is_required` int(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_no` varchar(50) DEFAULT '',
  `rating` int(11) NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data for table `settings`
INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES
('1', 'restaurant_name', 'HOTEL MARE  MONTE', '2026-09-21 09:07:32'),
('2', 'restaurant_slogan', 'Eşsiz Lezzetler &amp;amp;amp; Keyifli Anlar', '2026-09-21 09:07:32'),
('3', 'currency', '₺', '2026-09-21 09:07:32'),
('4', 'theme_color', '#d97706', '2026-09-21 09:07:32'),
('5', 'theme_mode', 'light', '2026-09-21 09:07:32'),
('6', 'logo_url', '', '2026-09-21 05:58:22'),
('7', 'banner_url', 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=1200&amp;amp;amp;q=80', '2026-09-21 09:07:32'),
('8', 'wifi_name', 'Gusto_Guest_5G', '2026-09-21 09:07:32'),
('9', 'wifi_pass', 'Gusto2026!', '2026-09-21 09:07:32'),
('10', 'phone', '+90 (212) 555 0199', '2026-09-21 09:07:32'),
('11', 'instagram', 'gustogourmet', '2026-09-21 09:07:32'),
('12', 'address', 'Bağdat Caddesi No: 142, Kadıköy / İstanbul', '2026-09-21 09:07:32'),
('13', 'enable_waiter_call', '1', '2026-09-21 05:58:22'),
('14', 'enable_order', '1', '2026-09-21 05:58:22'),
('15', 'service_charge', '0', '2026-09-21 05:58:22'),
('17', 'logo_dark_url', '', '2026-09-21 09:07:32'),
('18', 'logo_light_url', '', '2026-09-21 09:07:32'),
('74', 'enable_multi_lang', '1', '2026-09-21 09:23:48'),
('75', 'enable_kitchen', '1', '2026-09-21 09:23:48'),
('76', 'enable_stories', '1', '2026-09-21 09:23:48'),
('77', 'enable_popup', '1', '2026-09-21 09:23:48'),
('78', 'enable_feedback', '1', '2026-09-21 09:23:48'),
('79', 'enable_allergens_filter', '1', '2026-09-21 09:23:48'),
('81', 'popup_title', '🎉 Haftanın Özel Spesiyali!', '2026-09-21 09:23:48'),
('82', 'popup_desc', 'Gusto Smokehouse Burger yanında ev yapımı çıtır patates ve özel trüf mayonez ile şimdi %15 indirimli!', '2026-09-21 09:23:48'),
('83', 'popup_image', 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=800&q=80', '2026-09-21 09:23:48'),
('84', 'popup_btn_text', 'Hemen İncele', '2026-09-21 09:23:48'),
('85', 'popup_btn_link', '#cat-2', '2026-09-21 09:23:48'),
('86', 'google_maps_url', 'https://maps.google.com/?q=Gusto+Gourmet', '2026-09-21 09:23:48');

-- Data for table `admins`
INSERT INTO `admins` (`id`, `username`, `password_hash`, `name`, `role`, `created_at`) VALUES
('1', 'admin', '$2y$10$hwfDQQZng.UmcUG.0y3n5uGJ2q/2ew9MhrSi3ZDKsMGH42MyYohw2', 'Yönetici', 'superadmin', '2026-09-21 05:58:22');

-- Data for table `categories`
INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `image`, `sort_order`, `is_active`, `created_at`) VALUES
('1', 'Kahvaltı & Başlangıçlar', 'kahvalti-baslangiclar', 'egg-fried', 'https://images.unsplash.com/photo-1533089860892-a7c6f0a88666?w=600&q=80', '1', '1', '2026-09-21 05:58:22'),
('2', 'Gurme Burgerler', 'gurme-burgerler', 'hamburger', 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=600&q=80', '2', '1', '2026-09-21 05:58:22'),
('3', 'Taş Fırın Pizza', 'tas-firin-pizza', 'pizza-slice', 'https://images.unsplash.com/photo-1513104890138-7c749659a591?w=600&q=80', '3', '1', '2026-09-21 05:58:22'),
('4', 'Ana Yemekler & Izgaralar', 'ana-yemekler-izgaralar', 'drumstick-bite', 'https://images.unsplash.com/photo-1544025162-d76694265947?w=600&q=80', '4', '1', '2026-09-21 05:58:22'),
('5', 'Tatlılar & Pastalar', 'tatlilar-pastalar', 'birthday-cake', 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?w=600&q=80', '5', '1', '2026-09-21 05:58:22'),
('6', 'Kahve & İçecekler', 'kahve-icecekler', 'coffee', 'https://images.unsplash.com/photo-1517701550927-30cf4ba1dba5?w=600&q=80', '6', '1', '2026-09-21 05:58:22');

-- Data for table `products`
INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `price`, `old_price`, `image`, `badge`, `calories`, `prep_time`, `allergens`, `is_available`, `is_featured`, `sort_order`, `view_count`, `created_at`) VALUES
('1', '1', 'Serpme Ege Kahvaltısı (2 Kişilik)', 'Ezine peyniri, Bergama tulumu, Çeçil peyniri, ev yapımı reçeller, tereyağı, petek bal, kaymak, ızgara zeytinler, sahanda sucuklu yumurta, pişi ve sınırsız çay.', '560', '600', 'https://images.unsplash.com/photo-1533089860892-a7c6f0a88666?w=800&q=80', 'Popüler', '1250', '15', 'Gluten, Laktoz, Yumurta', '1', '1', '1', '0', '2026-09-21 05:58:22'),
('2', '1', 'Avokado Poşe Yumurta & Ekşi Maya', 'Kızarmış ekşi mayalı ekmek üzerine taze avokado püresi, poşe köy yumurtası, labne, çeri domates ve çörek otu.', '240', NULL, 'https://images.unsplash.com/photo-1525351484163-7529414344d8?w=800&q=80', 'Şefin Seçimi', '460', '10', 'Gluten, Laktoz, Yumurta', '1', '1', '2', '0', '2026-09-21 05:58:22'),
('3', '1', 'Çıtır Trüflü Patates Sepeti', 'Taze baharatlar, trüf yağı ve rendelenmiş parmesan peyniri ile harmanlanmış altın sarısı çıtır patatesler.', '180', NULL, 'https://images.unsplash.com/photo-1573080496219-bb080dd4f877?w=800&q=80', '', '380', '8', 'Laktoz', '1', '0', '3', '0', '2026-09-21 05:58:22'),
('4', '2', 'Gusto Smokehouse Burger', '180 gr dinlendirilmiş dana köftesi, füme antrikot dilimleri, karamelize soğan, cheddar peyniri, çıtır soğan ve özel tütsülü BBQ sos.', '360', '390', 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=800&q=80', 'Çok Satan', '820', '18', 'Gluten, Laktoz, Hardal', '1', '1', '1', '0', '2026-09-21 05:58:22'),
('5', '2', 'Trüflü Mantarlı Gurme Burger', '180 gr dana burger köftesi, sote yabani mantarlar, trüf mayonez, eritilmiş gravyer peyniri ve taze roka.', '385', NULL, 'https://images.unsplash.com/photo-1586190848861-99aa4a171e90?w=800&q=80', 'Şefin Seçimi', '790', '16', 'Gluten, Laktoz, Yumurta', '1', '1', '2', '0', '2026-09-21 05:58:22'),
('6', '2', 'Crispy Buttermilk Tavuk Burger', 'Özel marinasyonlu çıtır tavuk fileto, coleslaw salatası, jalapeno turşusu ve acılı ballı hardal sos.', '295', NULL, 'https://images.unsplash.com/photo-1625813506062-0aeb1d7a094b?w=800&q=80', 'Acılı', '670', '14', 'Gluten, Laktoz, Hardal, Yumurta', '1', '0', '3', '0', '2026-09-21 05:58:22'),
('7', '3', 'Pizza Margherita Napoletana', 'İtalyan San Marzano domates sosu, manda mozzarellası, taze fesleğen yaprakları ve sızma zeytinyağı.', '310', NULL, 'https://images.unsplash.com/photo-1604382355076-af4b0eb60143?w=800&q=80', 'Vejetaryen', '650', '12', 'Gluten, Laktoz', '1', '0', '1', '0', '2026-09-21 05:58:22'),
('8', '3', 'Pizza Quattro Formaggi (4 Peynirli)', 'Gorgonzola, parmesan, mozzarella, keçi peyniri, ceviz kırıntıları ve taze kekik.', '370', NULL, 'https://images.unsplash.com/photo-1573821663912-569905455b1c?w=800&q=80', 'Popüler', '780', '14', 'Gluten, Laktoz, Kuruyemiş', '1', '1', '2', '0', '2026-09-21 05:58:22'),
('9', '3', 'Pizza Carne & Bresaola', 'Dana bresaola dilimleri, kurutulmuş domates, mozzarella, parmesan yaprakları ve balzamik sos.', '410', '450', 'https://images.unsplash.com/photo-1534308983496-4fabb1a015ee?w=800&q=80', 'Özel', '720', '15', 'Gluten, Laktoz', '1', '1', '3', '0', '2026-09-21 05:58:22'),
('10', '4', 'Dinlendirilmiş Dana Ribeye Steak (300g)', 'Kömür ateşinde pişmiş 28 gün dry-aged antrikot, trüflü patates püresi, ızgara kuşkonmaz ve taze biberiyeli tereyağı sosu ile.', '680', '750', 'https://images.unsplash.com/photo-1558030006-450675393462?w=800&q=80', 'Şefin İmzası', '880', '22', 'Laktoz', '1', '1', '1', '0', '2026-09-21 05:58:22'),
('11', '4', 'Fırınlanmış Norveç Somon Fileto', 'Taze otlarla fırınlanmış somon balığı, safranlı risotto, bebek havuçlar ve limonlu kapari sos.', '520', NULL, 'https://images.unsplash.com/photo-1467003909585-2f8a72700288?w=800&q=80', 'Sağlıklı', '590', '18', 'Balık, Laktoz', '1', '1', '2', '0', '2026-09-21 05:58:22'),
('12', '5', 'Orijinal İtalyan Tiramisu', 'Savoiardi bisküvileri, espresso, taze mascarpone kreması ve yoğun Belçika kakaosu.', '210', NULL, 'https://images.unsplash.com/photo-1571877227200-a0d98ea607e9?w=800&q=80', 'En Çok Satan', '420', '5', 'Gluten, Laktoz, Yumurta', '1', '1', '1', '0', '2026-09-21 05:58:22'),
('13', '5', 'San Sebastian Cheesecake & Sıcak Çikolata', 'Karamelize yanık üst kabuk, akışkan ipeksi doku ve yanında sıcak eritilmiş Belçika sütlü çikolatası.', '230', NULL, 'https://images.unsplash.com/photo-1533134242443-d4fd215305ad?w=800&q=80', 'Yeni', '560', '5', 'Laktoz, Yumurta', '1', '1', '2', '0', '2026-09-21 05:58:22'),
('14', '6', 'Iced Salted Caramel Latte', 'Çift shot taze çekilmiş espresso, soğuk süt, deniz tuzlu karamel sosu ve buz.', '145', NULL, 'https://images.unsplash.com/photo-1517701550927-30cf4ba1dba5?w=800&q=80', 'Favori', '190', '4', 'Laktoz', '1', '1', '1', '0', '2026-09-21 05:58:22'),
('15', '6', 'Organik Orman Meyveli Limonata', 'Taze sıkılmış Bodrum limonları, taze nane yaprakları, yaban mersini ve frambuaz püresi ile.', '135', NULL, 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?w=800&q=80', 'Ferahlatıcı', '110', '4', '', '1', '0', '2', '0', '2026-09-21 05:58:22');

-- Data for table `tables`
INSERT INTO `tables` (`id`, `table_number`, `table_name`, `token`, `created_at`) VALUES
('1', '1', 'Masa 1', '7c21259e0b4e3139', '2026-09-21 05:58:22'),
('2', '2', 'Masa 2', '87bcded66713edd5', '2026-09-21 05:58:22'),
('3', '3', 'Masa 3', '66748e81fe88ec8c', '2026-09-21 05:58:22'),
('4', '4', 'Masa 4', 'fee52142b16ae1a7', '2026-09-21 05:58:22'),
('5', '5', 'Masa 5', '61285b2b7dd77cf3', '2026-09-21 05:58:22'),
('6', 'B1', 'Bahçe 1', 'e69730d0ec44e629', '2026-09-21 05:58:22'),
('7', 'B2', 'Bahçe 2', '2ac5529175a13a8c', '2026-09-21 05:58:22'),
('8', 'VIP-1', 'Teras VIP 1', 'bef4ec5e789a089a', '2026-09-21 05:58:22');

-- Data for table `waiter_calls`
INSERT INTO `waiter_calls` (`id`, `table_number`, `call_type`, `note`, `status`, `created_at`) VALUES
('1', '2', 'waiter', '', 'completed', '2026-09-21 09:02:57'),
('2', '3', 'cash_bill', '', 'completed', '2026-09-21 09:03:20');

-- Data for table `product_options`
INSERT INTO `product_options` (`id`, `product_id`, `group_name`, `option_name`, `extra_price`, `is_required`) VALUES
('1', '4', 'Pişme Derecesi', 'Orta Pişmiş (Medium)', '0', '1'),
('2', '4', 'Pişme Derecesi', 'İyi Pişmiş (Well Done)', '0', '1'),
('3', '4', 'Ekstralar', 'Ekstra Cheddar Peyniri', '30', '0'),
('4', '4', 'Ekstralar', 'Füme Kaburga Dilimi', '60', '0'),
('5', '4', 'Ekstralar', 'Trüflü Mayonez Sos', '25', '0'),
('6', '7', 'Kenar Seçimi', 'Klasik İnce Kenar', '0', '1'),
('7', '7', 'Kenar Seçimi', 'Peynir Dolgulu Kenar', '45', '1'),
('8', '7', 'Ekstralar', 'Ekstra Manda Mozzarella', '40', '0');

-- Data for table `stories`
INSERT INTO `stories` (`id`, `title`, `image`, `link`, `sort_order`, `is_active`, `created_at`) VALUES
('1', 'Günün Menüsü', 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=600&q=80', '#cat-4', '1', '1', '2026-09-21 09:23:48'),
('2', 'Gurme Burgerler', 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=600&q=80', '#cat-2', '2', '1', '2026-09-21 09:23:48'),
('3', 'Happy Hour %20', 'https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b?w=600&q=80', '#cat-6', '3', '1', '2026-09-21 09:23:48'),
('4', 'Şefin Tatlıları', 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?w=600&q=80', '#cat-5', '4', '1', '2026-09-21 09:23:48');

-- Data for table `feedback`
INSERT INTO `feedback` (`id`, `table_number`, `rating`, `name`, `comment`, `is_read`, `created_at`) VALUES
('1', '4', '5', 'Burak Yılmaz', 'Smokehouse Burger ve trüflü patates inanılmaz lezzetliydi, servis çok hızlı.', '1', '2026-09-21 09:23:48'),
('2', 'B2', '5', 'Elif Demir', 'San Sebastian cheesecake ve kahve muhteşemdi, bahçe ortamı çok keyifli.', '1', '2026-09-21 09:23:48'),
('3', '1', '4', 'Caner K.', 'Serpme kahvaltı çok zengin ve tazeydi. Teşekkürler.', '1', '2026-09-21 09:23:48'),
('4', '', '5', '', '', '0', '2026-09-21 09:31:14');

SET FOREIGN_KEY_CHECKS = 1;
