<?php
/**
 * QR Menü Genel API Uç Noktası (Genişletilmiş Modüller)
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/languages.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$lang = clean($_GET['lang'] ?? $_POST['lang'] ?? 'tr');

/**
 * Telegram Canlı Bildirim Gönderici
 */
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

switch ($action) {

    // 1. MENÜYÜ VE MODÜLLERİ GETİR
    case 'get_menu':
        try {
            // Aktif kategorileri al
            $stmtCat = $pdo->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order ASC, id ASC");
            $categories = $stmtCat->fetchAll();

            // Aktif ürünleri al
            $stmtProd = $pdo->query("SELECT * FROM products WHERE is_available = 1 ORDER BY sort_order ASC, id ASC");
            $products = $stmtProd->fetchAll();

            // Ürün seçeneklerini al
            $stmtOptions = $pdo->query("SELECT * FROM product_options ORDER BY id ASC");
            $allOptions = $stmtOptions->fetchAll();

            // Seçenekleri ürünlere göre grupla
            $optionsByProduct = [];
            foreach ($allOptions as $opt) {
                $optionsByProduct[$opt['product_id']][] = $opt;
            }

            // Ürünleri kategorilere göre grupla ve lokalize et
            $menu = [];
            foreach ($categories as $cat) {
                $catProducts = array_values(array_filter($products, function($p) use ($cat) {
                    return (int)$p['category_id'] === (int)$cat['id'];
                }));

                foreach ($catProducts as &$prod) {
                    $prod['name_localized'] = getLocalizedText($prod, 'name', $lang);
                    $prod['desc_localized'] = getLocalizedText($prod, 'desc', $lang);
                    $prod['options'] = $optionsByProduct[$prod['id']] ?? [];
                }

                $cat['name_localized'] = getLocalizedText($cat, 'name', $lang);
                $cat['products'] = $catProducts;
                $menu[] = $cat;
            }

            // Aktif Hikayeler
            $stories = [];
            if (getSetting('enable_stories', '1') === '1') {
                $stmtStories = $pdo->query("SELECT * FROM stories WHERE is_active = 1 ORDER BY sort_order ASC, id ASC");
                $stories = $stmtStories->fetchAll();
            }

            // Aktif Etkinlikler
            $events = [];
            if (getSetting('enable_events', '1') === '1') {
                $stmtEvents = $pdo->query("SELECT * FROM events WHERE is_active = 1 AND event_date >= date('now', '-1 day') ORDER BY event_date ASC, sort_order ASC");
                $events = $stmtEvents->fetchAll();
            }

            echo json_encode([
                'success' => true,
                'data' => $menu,
                'stories' => $stories,
                'events' => $events,
                'settings' => [
                    'restaurant_name' => getSetting('restaurant_name', 'HOTEL MARE & MONTE BISTRO'),
                    'currency' => getSetting('currency', '₺'),
                    'theme_color' => getSetting('theme_color', '#C5A059'),
                    'theme_mode' => getSetting('theme_mode', 'light'),
                    'enable_order' => getSetting('enable_order', '0'),
                    'enable_multi_lang' => getSetting('enable_multi_lang', '1'),
                    'enable_stories' => getSetting('enable_stories', '1'),
                    'enable_popup' => getSetting('enable_popup', '1'),
                    'enable_feedback' => getSetting('enable_feedback', '1'),
                    'enable_allergens_filter' => getSetting('enable_allergens_filter', '1'),
                    'enable_waiter_call' => getSetting('enable_waiter_call', '1'),
                    'enable_currency_converter' => getSetting('enable_currency_converter', '1'),
                    'currency_eur_rate' => getSetting('currency_eur_rate', '38.50'),
                    'currency_usd_rate' => getSetting('currency_usd_rate', '35.00'),
                    'currency_gbp_rate' => getSetting('currency_gbp_rate', '46.00'),
                    'enable_pairings' => getSetting('enable_pairings', '1'),
                    'enable_happy_hour' => getSetting('enable_happy_hour', '1'),
                    'happy_hour_title' => getSetting('happy_hour_title', '🌅 Gün Batımı Happy Hour (Tüm Kokteyllerde %15 İndirim)'),
                    'happy_hour_start' => getSetting('happy_hour_start', '17:00'),
                    'happy_hour_end' => getSetting('happy_hour_end', '19:30'),
                    'happy_hour_discount' => getSetting('happy_hour_discount', '15'),
                    'enable_resort_service' => getSetting('enable_resort_service', '1'),
                    'enable_events' => getSetting('enable_events', '1'),
                    'enable_concierge' => getSetting('enable_concierge', '1'),
                    'enable_lucky_wheel' => getSetting('enable_lucky_wheel', '1'),
                    'wheel_rewards' => getSetting('wheel_rewards', 'Günün Tatlısı İkramı,%10 Hesap İndirimi,Türk Kahvesi İkramı,Şefin Özel Kokteyli,%15 İndirim,Teşekkürler'),
                    'enable_whatsapp_notify' => getSetting('enable_whatsapp_notify', '0'),
                    'whatsapp_phone' => getSetting('whatsapp_phone', '+902663960000'),
                    'google_maps_url' => getSetting('google_maps_url', '')
                ]
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    // 2. TEKİL ÜRÜN VE OPSİYONLARI
    case 'get_product':
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Geçersiz ürün ID']);
            exit;
        }

        try {
            $pdo->prepare("UPDATE products SET view_count = view_count + 1 WHERE id = ?")->execute([$id]);

            $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
            $stmt->execute([$id]);
            $product = $stmt->fetch();

            if ($product) {
                $product['name_localized'] = getLocalizedText($product, 'name', $lang);
                $product['desc_localized'] = getLocalizedText($product, 'desc', $lang);

                // Seçenekleri çek
                $stmtOpt = $pdo->prepare("SELECT * FROM product_options WHERE product_id = ? ORDER BY id ASC");
                $stmtOpt->execute([$id]);
                $product['options'] = $stmtOpt->fetchAll();

                echo json_encode(['success' => true, 'data' => $product], JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode(['success' => false, 'message' => 'Ürün bulunamadı']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    // 3. MASADAN SİPARİŞ VERME (PLACE ORDER)
    case 'place_order':
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Geçersiz istek türü']);
            exit;
        }

        if (getSetting('enable_order', '1') !== '1') {
            echo json_encode(['success' => false, 'message' => 'Online sipariş şu anda kapalıdır.']);
            exit;
        }

        $tableNumber = clean($_POST['table_number'] ?? '');
        $customerNote = clean($_POST['customer_note'] ?? '');
        $itemsJson = $_POST['items'] ?? '[]';
        $items = json_decode($itemsJson, true);

        if (empty($tableNumber)) {
            echo json_encode(['success' => false, 'message' => 'Lütfen masa numaranızı belirtin.']);
            exit;
        }

        if (empty($items) || !is_array($items)) {
            echo json_encode(['success' => false, 'message' => 'Sepetiniz boş.']);
            exit;
        }

        try {
            $pdo->beginTransaction();

            $totalPrice = 0.00;
            $orderItemsToInsert = [];
            $summaryLines = [];

            foreach ($items as $item) {
                $prodId = (int)($item['id'] ?? 0);
                $quantity = max(1, (int)($item['quantity'] ?? 1));
                $selectedOptions = $item['options'] ?? [];

                // Ürün bilgilerini veritabanından doğrula
                $stmtP = $pdo->prepare("SELECT id, name, price, is_available FROM products WHERE id = ?");
                $stmtP->execute([$prodId]);
                $prodDb = $stmtP->fetch();

                if (!$prodDb || !$prodDb['is_available']) {
                    continue;
                }

                $itemBasePrice = (float)$prodDb['price'];
                $extraTotal = 0.00;

                // Seçeneklerin fiyatlarını doğrula
                if (!empty($selectedOptions)) {
                    foreach ($selectedOptions as $opt) {
                        $extraTotal += (float)($opt['extra_price'] ?? 0);
                    }
                }

                $unitPrice = $itemBasePrice + $extraTotal;
                $itemSubtotal = $unitPrice * $quantity;
                $totalPrice += $itemSubtotal;

                $orderItemsToInsert[] = [
                    'product_id' => $prodDb['id'],
                    'product_name' => $prodDb['name'],
                    'quantity' => $quantity,
                    'price' => $unitPrice,
                    'options_json' => json_encode($selectedOptions, JSON_UNESCAPED_UNICODE)
                ];

                $summaryLines[] = "• {$quantity}x {$prodDb['name']} (" . number_format($itemSubtotal, 2) . " ₺)";
            }

            if (empty($orderItemsToInsert)) {
                $pdo->rollBack();
                echo json_encode(['success' => false, 'message' => 'Sipariş edilebilir ürün bulunamadı.']);
                exit;
            }

            // Siparişi kaydet
            $stmtOrder = $pdo->prepare("INSERT INTO orders (table_number, total_price, status, customer_note) VALUES (?, ?, 'pending', ?)");
            $stmtOrder->execute([$tableNumber, $totalPrice, $customerNote]);
            $orderId = $pdo->lastInsertId();

            // Sipariş kalemlerini kaydet
            $stmtItem = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, price, options_json) VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($orderItemsToInsert as $oi) {
                $stmtItem->execute([
                    $orderId,
                    $oi['product_id'],
                    $oi['product_name'],
                    $oi['quantity'],
                    $oi['price'],
                    $oi['options_json']
                ]);
            }

            $pdo->commit();

            // Telegram Canlı Bildirim
            $telegramMsg = "🍽️ <b>YENİ SİPARİŞ ALINDI!</b>\n";
            $telegramMsg .= "📍 <b>Konum / Masa:</b> {$tableNumber}\n";
            $telegramMsg .= "🧾 <b>Sipariş No:</b> #{$orderId}\n";
            $telegramMsg .= "💰 <b>Toplam:</b> " . number_format($totalPrice, 2) . " ₺\n";
            $telegramMsg .= "📋 <b>Ürünler:</b>\n" . implode("\n", $summaryLines) . "\n";
            if (!empty($customerNote)) {
                $telegramMsg .= "💬 <b>Not:</b> {$customerNote}\n";
            }
            $telegramMsg .= "⏰ <b>Saat:</b> " . date('H:i:s');
            sendTelegramAlert($telegramMsg);

            echo json_encode([
                'success' => true,
                'order_id' => $orderId,
                'total_price' => $totalPrice,
                'message' => __t('order_success', $lang)
            ], JSON_UNESCAPED_UNICODE);

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            echo json_encode(['success' => false, 'message' => 'Sipariş oluşturulamadı: ' . $e->getMessage()]);
        }
        break;

    // 4. MASA SİPARİŞLERİNİ GETİR (CANLI TAKİP)
    case 'get_table_orders':
        $tableNumber = clean($_GET['table_number'] ?? '');
        if (empty($tableNumber)) {
            echo json_encode(['success' => false, 'message' => 'Masa numarası gerekli.']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("SELECT * FROM orders WHERE table_number = ? ORDER BY id DESC LIMIT 10");
            $stmt->execute([$tableNumber]);
            $orders = $stmt->fetchAll();

            foreach ($orders as &$ord) {
                $stmtItems = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
                $stmtItems->execute([$ord['id']]);
                $ord['items'] = $stmtItems->fetchAll();
            }

            echo json_encode(['success' => true, 'data' => $orders], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    // 5. GARSON ÇAĞIRMA, HESAP, VALE & CONCIERGE
    case 'call_waiter':
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Sadece POST isteği kabul edilir']);
            exit;
        }

        $tableNumber = clean($_POST['table_number'] ?? '');
        $callType = clean($_POST['call_type'] ?? 'waiter');
        $note = clean($_POST['note'] ?? '');

        if (empty($tableNumber)) {
            echo json_encode(['success' => false, 'message' => 'Lütfen masa veya konum numaranızı belirtin']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO waiter_calls (table_number, call_type, note, status) VALUES (?, ?, ?, 'pending')");
            $stmt->execute([$tableNumber, $callType, $note]);
            $callId = $pdo->lastInsertId();

            $callLabels = [
                'waiter' => '🔔 Garson Çağrısı',
                'card_bill' => '💳 Kredi Kartı ile Hesap',
                'cash_bill' => '💵 Nakit Hesap',
                'valet' => '🚗 Vale / Aracım',
                'taxi' => '🚕 Taksi Çağrısı',
                'reception' => '🛎️ Resepsiyon Talebi',
                'custom' => '💬 Özel İstek'
            ];
            $typeName = $callLabels[$callType] ?? '🔔 Garson Çağrısı';

            // Telegram Bildirimi
            $telegramMsg = "🔔 <b>YENİ SERVİS ÇAĞRISI!</b>\n";
            $telegramMsg .= "📍 <b>Konum:</b> {$tableNumber}\n";
            $telegramMsg .= "🏷️ <b>Talep:</b> {$typeName}\n";
            if (!empty($note)) {
                $telegramMsg .= "💬 <b>Not:</b> {$note}\n";
            }
            $telegramMsg .= "⏰ <b>Saat:</b> " . date('H:i:s');
            sendTelegramAlert($telegramMsg);

            echo json_encode([
                'success' => true,
                'message' => 'Talebiniz personele iletildi. En kısa sürede yanınıza gelinecektir.'
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Talep gönderilemedi: ' . $e->getMessage()]);
        }
        break;

    // 6. MÜŞTERİ DEĞERLENDİRMESİ VE YORUM (FEEDBACK)
    case 'send_feedback':
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Geçersiz istek türü']);
            exit;
        }

        $tableNumber = clean($_POST['table_number'] ?? '');
        $rating = max(1, min(5, (int)($_POST['rating'] ?? 5)));
        $name = clean($_POST['name'] ?? '');
        $comment = clean($_POST['comment'] ?? '');

        try {
            $stmt = $pdo->prepare("INSERT INTO feedback (table_number, rating, name, comment) VALUES (?, ?, ?, ?)");
            $stmt->execute([$tableNumber, $rating, $name, $comment]);

            echo json_encode([
                'success' => true,
                'rating' => $rating,
                'message' => __t('feedback_success', $lang)
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Geri bildirim kaydedilemedi: ' . $e->getMessage()]);
        }
        break;

    // 7. ARAMA
    case 'search':
        $query = clean($_GET['q'] ?? '');
        if (mb_strlen($query) < 2) {
            echo json_encode(['success' => true, 'data' => []]);
            exit;
        }

        try {
            $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p 
                                   LEFT JOIN categories c ON p.category_id = c.id 
                                   WHERE p.is_available = 1 AND (p.name LIKE ? OR p.description LIKE ? OR c.name LIKE ?)
                                   ORDER BY p.is_featured DESC, p.name ASC LIMIT 20");
            $param = '%' . $query . '%';
            $stmt->execute([$param, $param, $param]);
            $results = $stmt->fetchAll();

            echo json_encode(['success' => true, 'data' => $results], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Bilinmeyen istek']);
        break;
}
