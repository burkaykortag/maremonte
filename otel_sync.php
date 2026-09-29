<?php
/**
 * ==============================================================================
 * Mare Monte Otel Yönetimi & Canlı QR Menü Çift Yönlü Köprüsü (Bridge API)
 * ==============================================================================
 * Bu dosya, Otel Yönetim Sistemi (Next.js / Local POS) ile
 * Canlı Web Sunucusundaki Mare Monte QR Menüsü arasında
 * çift yönlü anlık garson çağrısı, 86 stok durumu ve fiyat senkronizasyonunu sağlar.
 *
 * Akış Yönleri:
 * 1. Canlı Menü -> Local POS: Misafir canlıda garson/hesap istediğinde çağrı burada
 *    toplanır, oteldeki local POS arka planda bu çağrıları çekip zili çalar.
 * 2. Local POS -> Canlı Menü: Otelde bir fiyat değiştiğinde veya yemek bittiğinde (86)
 *    local sistem bu API'ye POST atarak canlı menüyü anında günceller.
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Sync-Key');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/config.php';

// JSON Girdisini Oku
$rawInput = file_get_contents('php://input');
$body = json_decode($rawInput, true) ?: [];
$action = $_GET['action'] ?? $body['action'] ?? '';

// Güvenlik Anahtarı Kontrolü (Varsa doğrula, ping hariç)
$configuredKey = defined('SYNC_API_KEY') ? SYNC_API_KEY : '';
$providedKey = $_SERVER['HTTP_X_SYNC_KEY'] ?? $_GET['sync_key'] ?? $body['sync_key'] ?? '';

if (!empty($configuredKey) && $action !== 'ping') {
    if ($providedKey !== $configuredKey) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'Geçersiz veya eksik Senkronizasyon Anahtarı (X-Sync-Key / sync_key).'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

try {
    switch ($action) {
        // ---------------------------------------------------------------------
        // 1. DURUM TESTİ (PING)
        // ---------------------------------------------------------------------
        case 'ping':
            $prodCount = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
            $pendingCalls = (int)$pdo->query("SELECT COUNT(*) FROM waiter_calls WHERE status = 'pending'")->fetchColumn();
            echo json_encode([
                'success' => true,
                'message' => 'Mare Monte Canlı QR Menü Köprüsü Aktif & Çalışıyor',
                'timestamp' => date('Y-m-d H:i:s'),
                'db_driver' => DB_DRIVER,
                'product_count' => $prodCount,
                'pending_calls_count' => $pendingCalls
            ], JSON_UNESCAPED_UNICODE);
            break;

        // ---------------------------------------------------------------------
        // 2. BEKLEYEN GARSON / HESAP ÇAĞRILARINI LİSTELE (LOCAL POS İÇİN)
        // ---------------------------------------------------------------------
        case 'get_pending_calls':
            $stmt = $pdo->query("
                SELECT id, table_number, call_type, note, status, created_at 
                FROM waiter_calls 
                WHERE status = 'pending' 
                ORDER BY id ASC
                LIMIT 50
            ");
            $calls = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'count' => count($calls),
                'calls' => $calls,
                'timestamp' => date('Y-m-d H:i:s')
            ], JSON_UNESCAPED_UNICODE);
            break;

        // ---------------------------------------------------------------------
        // 3. ÇAĞRIYI ONAYLA (ACK - LOCAL POS ALDI, ZİLİ ÇALDI)
        // ---------------------------------------------------------------------
        case 'ack_call':
            $callId = (int)($body['call_id'] ?? $_GET['call_id'] ?? 0);
            if ($callId <= 0) {
                echo json_encode(['success' => false, 'error' => 'Geçersiz çağrı ID'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $stmt = $pdo->prepare("UPDATE waiter_calls SET status = 'acknowledged' WHERE id = ?");
            $stmt->execute([$callId]);

            echo json_encode([
                'success' => true,
                'message' => "Çağrı #{$callId} local POS tarafından onaylandı.",
                'call_id' => $callId
            ], JSON_UNESCAPED_UNICODE);
            break;

        // ---------------------------------------------------------------------
        // 4. ÇAĞRIYI TAMAMLA (GARSON MASAYA GİTTİ)
        // ---------------------------------------------------------------------
        case 'complete_call':
            $callId = (int)($body['call_id'] ?? $_GET['call_id'] ?? 0);
            if ($callId <= 0) {
                echo json_encode(['success' => false, 'error' => 'Geçersiz çağrı ID'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $stmt = $pdo->prepare("UPDATE waiter_calls SET status = 'completed' WHERE id = ?");
            $stmt->execute([$callId]);

            echo json_encode([
                'success' => true,
                'message' => "Çağrı #{$callId} tamamlandı olarak işaretlendi.",
                'call_id' => $callId
            ], JSON_UNESCAPED_UNICODE);
            break;

        // ---------------------------------------------------------------------
        // 5. TEKİL 86 (TÜKENDİ / SATIŞTA) DURUM SENKRONİZASYONU
        // ---------------------------------------------------------------------
        case 'toggle_stock':
            $productId = (int)($body['product_id'] ?? $_GET['product_id'] ?? 0);
            $isAvailable = isset($body['is_available']) ? (int)$body['is_available'] : (isset($_GET['is_available']) ? (int)$_GET['is_available'] : 1);

            if ($productId <= 0) {
                echo json_encode(['success' => false, 'error' => 'Geçersiz ürün ID'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $stmt = $pdo->prepare("UPDATE products SET is_available = ? WHERE id = ?");
            $stmt->execute([$isAvailable ? 1 : 0, $productId]);

            echo json_encode([
                'success' => true,
                'message' => "Ürün #{$productId} stok durumu " . ($isAvailable ? 'Satışta' : '86 - Tükendi') . " olarak güncellendi.",
                'product_id' => $productId,
                'is_available' => (bool)$isAvailable
            ], JSON_UNESCAPED_UNICODE);
            break;

        // ---------------------------------------------------------------------
        // 6. ÜRÜN FİYAT VE BİLGİ GÜNCELLEME (LOCAL OTEL -> CANLI MENÜ)
        // ---------------------------------------------------------------------
        case 'update_product':
            $productId = (int)($body['product_id'] ?? 0);
            $price = isset($body['price']) ? (float)$body['price'] : null;
            $name = isset($body['name']) ? trim($body['name']) : null;
            $description = isset($body['description']) ? trim($body['description']) : null;
            $isAvailable = isset($body['is_available']) ? ($body['is_available'] ? 1 : 0) : null;

            if ($productId <= 0) {
                echo json_encode(['success' => false, 'error' => 'Geçersiz ürün ID'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $fields = [];
            $params = [];

            if ($price !== null) {
                $fields[] = 'price = ?';
                $params[] = $price;
            }
            if ($name !== null && $name !== '') {
                $fields[] = 'name = ?';
                $params[] = $name;
            }
            if ($description !== null) {
                $fields[] = 'description = ?';
                $params[] = $description;
            }
            if ($isAvailable !== null) {
                $fields[] = 'is_available = ?';
                $params[] = $isAvailable;
            }

            if (!empty($fields)) {
                $params[] = $productId;
                $sql = "UPDATE products SET " . implode(', ', $fields) . " WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
            }

            echo json_encode([
                'success' => true,
                'message' => "Ürün #{$productId} başarıyla güncellendi.",
                'product_id' => $productId,
                'updated_fields' => count($fields)
            ], JSON_UNESCAPED_UNICODE);
            break;

        // ---------------------------------------------------------------------
        // 7. ÇOKLU / TOPLU FİYAT GÜNCELLEME (BATCH UPDATE)
        // ---------------------------------------------------------------------
        case 'batch_update_prices':
            $items = $body['items'] ?? [];
            if (!is_array($items) || empty($items)) {
                echo json_encode(['success' => false, 'error' => 'Güncellenecek ürün listesi (items) bulunamadı.'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $pdo->beginTransaction();
            $stmt = $pdo->prepare("UPDATE products SET price = ? WHERE id = ?");
            $updatedCount = 0;

            foreach ($items as $item) {
                $pid = (int)($item['id'] ?? 0);
                $prc = isset($item['price']) ? (float)$item['price'] : null;
                if ($pid > 0 && $prc !== null) {
                    $stmt->execute([$prc, $pid]);
                    $updatedCount++;
                }
            }

            $pdo->commit();

            echo json_encode([
                'success' => true,
                'message' => "{$updatedCount} ürünün fiyatı başarıyla güncellendi.",
                'updated_count' => $updatedCount
            ], JSON_UNESCAPED_UNICODE);
            break;

        // ---------------------------------------------------------------------
        // 8. TÜM ÜRÜNLERİ LİSTELE (OTEL YÖNETİMİ İÇİN KATALOG)
        // ---------------------------------------------------------------------
        case 'get_catalog':
            $stmt = $pdo->query("
                SELECT p.id, p.category_id, c.name as category_name, p.name, p.description, 
                       p.price, p.image, p.is_available, p.sort_order
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                ORDER BY p.sort_order ASC, p.id ASC
            ");
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'total' => count($products),
                'products' => $products
            ], JSON_UNESCAPED_UNICODE);
            break;

        default:
            echo json_encode([
                'success' => false,
                'error' => 'Geçersiz eylem (action). Desteklenenler: ping, get_pending_calls, ack_call, complete_call, toggle_stock, update_product, batch_update_prices, get_catalog.'
            ], JSON_UNESCAPED_UNICODE);
            break;
    }
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Köprü işlem hatası: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
