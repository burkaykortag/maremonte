<?php
/**
 * Export current SQLite database to database.sql for MySQL/MariaDB (Plesk/cPanel)
 */
require_once __DIR__ . '/config.php';

try {
    $pdo = new PDO('sqlite:' . DB_SQLITE_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $sql = "-- Hotel Mare & Monte Bistro (Altınoluk Est. 1985) QR Menu\n";
    $sql .= "-- MySQL / MariaDB Database Dump for Plesk / cPanel Import\n";
    $sql .= "-- Compatible with MySQL 5.7+ / 8.0+ / MariaDB 10.3+\n\n";
    $sql .= "SET NAMES utf8mb4;\n";
    $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

    $tables = [
        'settings' => "CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) DEFAULT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

        'admins' => "CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `name` varchar(150) DEFAULT NULL,
  `role` varchar(50) DEFAULT 'admin',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

        'categories' => "CREATE TABLE `categories` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

        'products' => "CREATE TABLE `products` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

        'tables' => "CREATE TABLE `tables` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_number` varchar(50) NOT NULL,
  `table_name` varchar(100) NOT NULL,
  `token` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `table_number` (`table_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

        'waiter_calls' => "CREATE TABLE `waiter_calls` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_number` varchar(50) NOT NULL,
  `call_type` varchar(50) DEFAULT 'waiter',
  `note` text DEFAULT NULL,
  `status` varchar(30) DEFAULT 'pending',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

        'orders' => "CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_number` varchar(50) NOT NULL,
  `total_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` varchar(30) DEFAULT 'pending',
  `customer_note` text DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

        'order_items' => "CREATE TABLE `order_items` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

        'product_options' => "CREATE TABLE `product_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `group_name` varchar(100) NOT NULL,
  `option_name` varchar(100) NOT NULL,
  `extra_price` decimal(10,2) DEFAULT 0.00,
  `is_required` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

        'stories' => "CREATE TABLE `stories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL,
  `image` varchar(255) NOT NULL,
  `link` varchar(255) DEFAULT '',
  `sort_order` int(11) DEFAULT 0,
  `is_active` int(11) DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

        'feedback' => "CREATE TABLE `feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_number` varchar(50) DEFAULT '',
  `rating` int(11) NOT NULL DEFAULT 5,
  `name` varchar(100) DEFAULT '',
  `comment` text DEFAULT NULL,
  `is_read` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

        'events' => "CREATE TABLE `events` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
    ];

    foreach ($tables as $tName => $createSql) {
        $sql .= "DROP TABLE IF EXISTS `$tName`;\n";
        $sql .= $createSql . "\n\n";

        $rows = $pdo->query("SELECT * FROM `$tName`")->fetchAll();
        if (!empty($rows)) {
            $cols = array_keys($rows[0]);
            $colList = implode('`, `', $cols);
            $sql .= "INSERT INTO `$tName` (`$colList`) VALUES\n";

            $valueLines = [];
            foreach ($rows as $row) {
                $escaped = [];
                foreach ($row as $val) {
                    if ($val === null) {
                        $escaped[] = 'NULL';
                    } else {
                        $escaped[] = "'" . addslashes($val) . "'";
                    }
                }
                $valueLines[] = "(" . implode(', ', $escaped) . ")";
            }
            $sql .= implode(",\n", $valueLines) . ";\n\n";
        }
    }

    $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";

    file_put_contents(__DIR__ . '/database.sql', $sql);
    echo "database.sql başarıyla güncellendi!\n";

} catch (Exception $e) {
    echo "Hata: " . $e->getMessage() . "\n";
    exit(1);
}
