<?php
/**
 * Admin Paneli AJAX İşleyicisi (Genişletilmiş Modüller)
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../database.php';

if (!isAdminLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim.']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    // 1. TEKİL HIZLI FİYAT GÜNCELLEME
    case 'quick_price_update':
        $id = (int)($_POST['id'] ?? 0);
        $price = (float)($_POST['price'] ?? 0);

        if ($id <= 0 || $price < 0) {
            echo json_encode(['success' => false, 'message' => 'Geçersiz ürün veya fiyat.']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("UPDATE products SET price = ? WHERE id = ?");
            $stmt->execute([$price, $id]);
            echo json_encode(['success' => true, 'message' => 'Fiyat güncellendi.']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Fiyat güncellenemedi: ' . $e->getMessage()]);
        }
        break;

    // 2. TOPLU FİYAT GÜNCELLEME
    case 'batch_price_update':
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $updateType = $_POST['update_type'] ?? 'percent';
        $amount = (float)($_POST['amount'] ?? 0);
        $isIncrease = ($_POST['direction'] ?? 'increase') === 'increase';

        if ($amount <= 0) {
            echo json_encode(['success' => false, 'message' => 'Lütfen sıfırdan büyük bir değer girin.']);
            exit;
        }

        try {
            $where = $categoryId > 0 ? "WHERE category_id = " . (int)$categoryId : "";
            $stmt = $pdo->query("SELECT id, price FROM products $where");
            $products = $stmt->fetchAll();

            $updateStmt = $pdo->prepare("UPDATE products SET price = ?, old_price = ? WHERE id = ?");

            $updatedCount = 0;
            foreach ($products as $p) {
                $oldPrice = (float)$p['price'];
                if ($updateType === 'percent') {
                    $diff = ($oldPrice * $amount) / 100;
                    $newPrice = $isIncrease ? ($oldPrice + $diff) : max(0, $oldPrice - $diff);
                } else {
                    $newPrice = $isIncrease ? ($oldPrice + $amount) : max(0, $oldPrice - $amount);
                }

                $newPrice = round($newPrice, 2);
                $updateStmt->execute([$newPrice, $oldPrice, $p['id']]);
                $updatedCount++;
            }

            echo json_encode([
                'success' => true,
                'message' => "$updatedCount adet ürünün fiyatı başarıyla güncellendi!"
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Toplu fiyat güncelleme hatası: ' . $e->getMessage()]);
        }
        break;

    // 3. ÜRÜN STOK DURUMU DEĞİŞTİRME
    case 'toggle_product_status':
        $id = (int)($_POST['id'] ?? 0);
        $status = (int)($_POST['status'] ?? 0);

        try {
            $stmt = $pdo->prepare("UPDATE products SET is_available = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
            $msg = $status ? 'Ürün menüde yayına alındı.' : 'Ürün "Tükendi" olarak işaretlendi.';
            echo json_encode(['success' => true, 'message' => $msg]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Durum değiştirilemedi: ' . $e->getMessage()]);
        }
        break;

    // 4. KATEGORİ DURUMU DEĞİŞTİRME
    case 'toggle_category_status':
        $id = (int)($_POST['id'] ?? 0);
        $status = (int)($_POST['status'] ?? 0);

        try {
            $stmt = $pdo->prepare("UPDATE categories SET is_active = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
            echo json_encode(['success' => true, 'message' => 'Kategori durumu güncellendi.']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Kategori güncellenemedi: ' . $e->getMessage()]);
        }
        break;

    // 5. SİPARİŞ DURUMU GÜNCELLEME (MUTFAK & KDS)
    case 'update_order_status':
        $orderId = (int)($_POST['order_id'] ?? 0);
        $status = clean($_POST['status'] ?? 'preparing'); // 'pending', 'preparing', 'ready', 'served', 'cancelled'

        try {
            $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$status, $orderId]);

            $statusLabels = [
                'pending' => 'Bekliyor',
                'preparing' => 'Hazırlanıyor',
                'ready' => 'Hazır',
                'served' => 'Servis Edildi',
                'cancelled' => 'İptal Edildi'
            ];

            echo json_encode([
                'success' => true,
                'status' => $status,
                'message' => 'Sipariş durumu "' . ($statusLabels[$status] ?? $status) . '" olarak güncellendi.'
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Sipariş durumu güncellenemedi: ' . $e->getMessage()]);
        }
        break;

    // 6. CANLI MUTFAK SİPARİŞLERİNİ GETİR (KDS POLLING)
    case 'get_kitchen_orders':
        try {
            $stmt = $pdo->query("SELECT * FROM orders WHERE status IN ('pending', 'preparing', 'ready') ORDER BY id ASC");
            $orders = $stmt->fetchAll();

            foreach ($orders as &$ord) {
                $stmtItems = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
                $stmtItems->execute([$ord['id']]);
                $items = $stmtItems->fetchAll();
                foreach ($items as &$it) {
                    $it['options'] = !empty($it['options_json']) ? json_decode($it['options_json'], true) : [];
                }
                $ord['items'] = $items;
                $ord['time_ago'] = round((time() - strtotime($ord['created_at'])) / 60);
            }

            echo json_encode(['success' => true, 'data' => $orders]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    // 7. GARSON ÇAĞRI DURUMU GÜNCELLEME
    case 'update_call_status':
        $id = (int)($_POST['id'] ?? 0);
        $status = clean($_POST['status'] ?? 'completed');

        try {
            $stmt = $pdo->prepare("UPDATE waiter_calls SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
            echo json_encode(['success' => true, 'message' => 'Çağrı durumu güncellendi.']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Hata: ' . $e->getMessage()]);
        }
        break;

    case 'get_pending_calls':
        try {
            $stmt = $pdo->query("SELECT * FROM waiter_calls WHERE status = 'pending' ORDER BY id DESC");
            $calls = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $calls]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    // 8. HİKAYE (STORY) DURUM / SİLME
    case 'toggle_story_status':
        $id = (int)($_POST['id'] ?? 0);
        $status = (int)($_POST['status'] ?? 0);
        try {
            $pdo->prepare("UPDATE stories SET is_active = ? WHERE id = ?")->execute([$status, $id]);
            echo json_encode(['success' => true, 'message' => 'Hikaye durumu güncellendi.']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'delete_story':
        $id = (int)($_POST['id'] ?? 0);
        try {
            $pdo->prepare("DELETE FROM stories WHERE id = ?")->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Hikaye silindi.']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    // 9. GERİ BİLDİRİM (FEEDBACK) SİLME
    case 'delete_feedback':
        $id = (int)($_POST['id'] ?? 0);
        try {
            $pdo->prepare("DELETE FROM feedback WHERE id = ?")->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Yorum silindi.']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    // 9b. ETKİNLİK (EVENT) DURUM / SİLME
    case 'toggle_event_status':
        $id = (int)($_POST['id'] ?? 0);
        $status = (int)($_POST['status'] ?? 0);
        try {
            $pdo->prepare("UPDATE events SET is_active = ? WHERE id = ?")->execute([$status, $id]);
            echo json_encode(['success' => true, 'message' => 'Etkinlik durumu güncellendi.']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'delete_event':
        $id = (int)($_POST['id'] ?? 0);
        try {
            $pdo->prepare("DELETE FROM events WHERE id = ?")->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Etkinlik silindi.']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    // 10. ÜRÜN OPSİYONLARI YÖNETİMİ
    case 'get_product_options':
        $productId = (int)($_GET['product_id'] ?? $_POST['product_id'] ?? 0);
        try {
            $stmt = $pdo->prepare("SELECT * FROM product_options WHERE product_id = ? ORDER BY id ASC");
            $stmt->execute([$productId]);
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'save_product_option':
        $productId = (int)($_POST['product_id'] ?? 0);
        $groupName = clean($_POST['group_name'] ?? 'Ekstralar');
        $optionName = clean($_POST['option_name'] ?? '');
        $extraPrice = (float)str_replace(',', '.', $_POST['extra_price'] ?? 0);
        $isRequired = !empty($_POST['is_required']) ? 1 : 0;

        if ($productId <= 0 || empty($optionName)) {
            echo json_encode(['success' => false, 'message' => 'Ürün ve seçenek adı gereklidir.']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO product_options (product_id, group_name, option_name, extra_price, is_required) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$productId, $groupName, $optionName, $extraPrice, $isRequired]);
            echo json_encode(['success' => true, 'message' => 'Seçenek eklendi!', 'id' => $pdo->lastInsertId()]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'delete_product_option':
        $id = (int)($_POST['id'] ?? 0);
        try {
            $pdo->prepare("DELETE FROM product_options WHERE id = ?")->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Seçenek silindi.']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    // 11. SİLME İŞLEMLERİ
    case 'delete_product':
        $id = (int)($_POST['id'] ?? 0);
        try {
            $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM product_options WHERE product_id = ?")->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Ürün silindi.']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Silme başarısız: ' . $e->getMessage()]);
        }
        break;

    case 'delete_category':
        $id = (int)($_POST['id'] ?? 0);
        try {
            $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Kategori silindi.']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Silme başarısız: ' . $e->getMessage()]);
        }
        break;

    case 'delete_table':
        $id = (int)($_POST['id'] ?? 0);
        try {
            $pdo->prepare("DELETE FROM tables WHERE id = ?")->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Masa silindi.']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Silme başarısız: ' . $e->getMessage()]);
        }
        break;

    // =========================================================================
    // 12. GARSON POS & SİPARİŞ TERMİNALİ İŞLEMLERİ
    // =========================================================================

    // 12.1. TÜM MASALARIN CANLI DURUMLARI & ÇAĞRILAR
    case 'pos_get_tables_status':
        try {
            // Masaları al
            $tables = $pdo->query("SELECT * FROM tables ORDER BY id ASC")->fetchAll();

            // Açık siparişleri al (pending, preparing, ready)
            $openOrders = $pdo->query("SELECT * FROM orders WHERE status IN ('pending', 'preparing', 'ready') ORDER BY id ASC")->fetchAll();

            // Masalara göre grupla
            $tableStats = [];
            foreach ($openOrders as $ord) {
                $tn = $ord['table_number'];
                if (!isset($tableStats[$tn])) {
                    $tableStats[$tn] = [
                        'order_count' => 0,
                        'total_price' => 0.0,
                        'latest_order_time' => $ord['created_at'],
                        'orders' => []
                    ];
                }
                $tableStats[$tn]['order_count']++;
                $tableStats[$tn]['total_price'] += (float)$ord['total_price'];
                $tableStats[$tn]['orders'][] = $ord;
            }

            // Bekleyen Garson/Concierge Çağrıları
            $pendingCalls = $pdo->query("SELECT * FROM waiter_calls WHERE status = 'pending' ORDER BY id DESC LIMIT 20")->fetchAll();

            echo json_encode([
                'success' => true,
                'tables' => $tables,
                'table_stats' => $tableStats,
                'pending_calls' => $pendingCalls
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    // 12.2. SEÇİLİ MASANIN ADİSYON / SİPARİŞ DETAYLARI
    case 'pos_get_table_orders':
        $tableNumber = clean($_GET['table_number'] ?? $_POST['table_number'] ?? '');
        if (empty($tableNumber)) {
            echo json_encode(['success' => false, 'message' => 'Masa numarası gerekli.']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("SELECT * FROM orders WHERE table_number = ? AND status IN ('pending', 'preparing', 'ready') ORDER BY id ASC");
            $stmt->execute([$tableNumber]);
            $orders = $stmt->fetchAll();

            $totalBill = 0.0;
            $allItems = [];

            foreach ($orders as &$ord) {
                $totalBill += (float)$ord['total_price'];
                $stmtItems = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
                $stmtItems->execute([$ord['id']]);
                $items = $stmtItems->fetchAll();
                foreach ($items as &$it) {
                    $it['options'] = !empty($it['options_json']) ? json_decode($it['options_json'], true) : [];
                    $allItems[] = $it;
                }
                $ord['items'] = $items;
            }

            echo json_encode([
                'success' => true,
                'table_number' => $tableNumber,
                'total_bill' => $totalBill,
                'orders' => $orders,
                'all_items' => $allItems
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    // 12.3. GARSONUN POS'TAN SİPARİŞ GİRMESİ
    case 'pos_create_order':
        $tableNumber = clean($_POST['table_number'] ?? '');
        $customerNote = clean($_POST['customer_note'] ?? '');
        $waiterName = clean($_POST['waiter_name'] ?? 'Garson');
        $itemsJson = $_POST['items'] ?? '[]';
        $items = json_decode($itemsJson, true);

        if (empty($tableNumber)) {
            echo json_encode(['success' => false, 'message' => 'Lütfen masa / konum belirtin.']);
            exit;
        }

        if (empty($items) || !is_array($items)) {
            echo json_encode(['success' => false, 'message' => 'Adisyonda ürün bulunmuyor.']);
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
                $itemNote = clean($item['item_note'] ?? '');

                // Ürün doğrula
                $stmtP = $pdo->prepare("SELECT id, name, price, is_available FROM products WHERE id = ?");
                $stmtP->execute([$prodId]);
                $prodDb = $stmtP->fetch();

                if (!$prodDb) continue;

                $itemBasePrice = (float)$prodDb['price'];
                $extraTotal = 0.00;

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
                    'product_name' => $prodDb['name'] . ($itemNote ? " ({$itemNote})" : ""),
                    'quantity' => $quantity,
                    'price' => $unitPrice,
                    'options_json' => json_encode($selectedOptions, JSON_UNESCAPED_UNICODE)
                ];

                $summaryLines[] = "• {$quantity}x {$prodDb['name']}" . ($itemNote ? " [{$itemNote}]" : "") . " (" . number_format($itemSubtotal, 2) . " ₺)";
            }

            if (empty($orderItemsToInsert)) {
                $pdo->rollBack();
                echo json_encode(['success' => false, 'message' => 'Geçerli ürün bulunamadı.']);
                exit;
            }

            // Siparişi kaydet (Garson tarafından girilen sipariş doğrudan mutfağa 'preparing' gider)
            $noteWithWaiter = "👨‍🍳 [Garson: {$waiterName}]" . ($customerNote ? " - {$customerNote}" : "");
            $stmtOrder = $pdo->prepare("INSERT INTO orders (table_number, total_price, status, customer_note) VALUES (?, ?, 'preparing', ?)");
            $stmtOrder->execute([$tableNumber, $totalPrice, $noteWithWaiter]);
            $orderId = $pdo->lastInsertId();

            // Kalemleri kaydet
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

            // Telegram Bildirimi
            $telegramMsg = "👨‍🍳 <b>GARSON EL TERMİNALİ SİPARİŞİ!</b>\n";
            $telegramMsg .= "📍 <b>Masa / Konum:</b> {$tableNumber}\n";
            $telegramMsg .= "👤 <b>Personel:</b> {$waiterName}\n";
            $telegramMsg .= "🧾 <b>Sipariş No:</b> #{$orderId}\n";
            $telegramMsg .= "💰 <b>Tutar:</b> " . number_format($totalPrice, 2) . " ₺\n";
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
                'message' => 'Sipariş mutfağa iletildi!'
            ]);
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            echo json_encode(['success' => false, 'message' => 'Sipariş oluşturulamadı: ' . $e->getMessage()]);
        }
        break;

    // 12.4. HESAP KAPATMA & TAHSİLAT
    case 'pos_close_table':
        $tableNumber = clean($_POST['table_number'] ?? '');
        $paymentMethod = clean($_POST['payment_method'] ?? 'cash'); // 'cash', 'card', 'room', 'complimentary'
        $waiterName = clean($_POST['waiter_name'] ?? 'Garson');

        if (empty($tableNumber)) {
            echo json_encode(['success' => false, 'message' => 'Masa numarası gerekli.']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("UPDATE orders SET status = 'served' WHERE table_number = ? AND status IN ('pending', 'preparing', 'ready')");
            $stmt->execute([$tableNumber]);

            $payLabels = [
                'cash' => '💵 Nakit',
                'card' => '💳 Kredi Kartı',
                'room' => '🛎️ Odaya Yazıldı',
                'complimentary' => '🎁 İkram / Yetkili'
            ];
            $payText = $payLabels[$paymentMethod] ?? 'Nakit';

            // Telegram Bildirimi
            $telegramMsg = "✅ <b>HESAP TAHSİL EDİLDİ & MASASI KAPATILDI</b>\n";
            $telegramMsg .= "📍 <b>Masa:</b> {$tableNumber}\n";
            $telegramMsg .= "💳 <b>Ödeme Türü:</b> {$payText}\n";
            $telegramMsg .= "👤 <b>Personel:</b> {$waiterName}\n";
            $telegramMsg .= "⏰ <b>Saat:</b> " . date('H:i:s');
            sendTelegramAlert($telegramMsg);

            echo json_encode([
                'success' => true,
                'message' => "Masa {$tableNumber} hesabı ({$payText}) başarıyla kapatıldı!"
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Hesap kapatılamadı: ' . $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Bilinmeyen işlem.']);
        break;
}
