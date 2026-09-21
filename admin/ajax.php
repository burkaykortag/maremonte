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

    default:
        echo json_encode(['success' => false, 'message' => 'Bilinmeyen işlem.']);
        break;
}
