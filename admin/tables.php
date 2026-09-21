<?php
/**
 * Masa Yönetimi & Yazdırılabilir QR Kod Üretici
 */

require_once __DIR__ . '/header.php';

$successMsg = '';
$errorMsg = '';

// POST: Masa Ekle veya Toplu Oluştur
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $actionType = $_POST['form_action'] ?? 'single';

    if ($actionType === 'single') {
        $tableNum = clean($_POST['table_number'] ?? '');
        $tableName = clean($_POST['table_name'] ?? '');
        $token = bin2hex(random_bytes(8));

        if (empty($tableNum) || empty($tableName)) {
            $errorMsg = 'Lütfen masa numarası ve masa adını girin.';
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO tables (table_number, table_name, token) VALUES (?, ?, ?)");
                $stmt->execute([$tableNum, $tableName, $token]);
                $successMsg = 'Masa başarıyla eklendi!';
            } catch (Exception $e) {
                $errorMsg = 'Masa eklenemedi: ' . $e->getMessage();
            }
        }
    } elseif ($actionType === 'bulk') {
        $prefix = clean($_POST['bulk_prefix'] ?? 'Masa ');
        $start = (int)($_POST['bulk_start'] ?? 1);
        $end = (int)($_POST['bulk_end'] ?? 10);

        if ($start > $end || $end > 100) {
            $errorMsg = 'Geçersiz aralık girdiniz (Maksimum 100 masa).';
        } else {
            try {
                $stmt = $pdo->prepare("INSERT OR IGNORE INTO tables (table_number, table_name, token) VALUES (?, ?, ?)");
                $count = 0;
                for ($i = $start; $i <= $end; $i++) {
                    $tNum = (string)$i;
                    $tName = $prefix . $i;
                    $token = bin2hex(random_bytes(8));
                    $stmt->execute([$tNum, $tName, $token]);
                    $count++;
                }
                $successMsg = "$count adet masa başarıyla oluşturuldu!";
            } catch (Exception $e) {
                $errorMsg = 'Toplu masa hatası: ' . $e->getMessage();
            }
        }
    }
}

// Masaları Çek
$tables = $pdo->query("SELECT * FROM tables ORDER BY id ASC")->fetchAll();
$restaurantName = getSetting('restaurant_name', 'Gusto QR Menü');
?>

<div class="page-header">
    <div class="page-title">
        <h1>Masa & QR Kod Yönetimi</h1>
        <p>Restoran masalarını tanımlayın ve doğrudan masalara konulacak şık yazdırılabilir QR kod kartları üretin</p>
    </div>

    <div style="display: flex; gap: 10px;">
        <button type="button" class="btn btn-primary" onclick="openModal('tableModal')">
            <i class="fas fa-plus"></i> Tek Masa Ekle
        </button>
        <button type="button" class="btn btn-secondary" onclick="openModal('bulkModal')">
            <i class="fas fa-layer-group"></i> Toplu Masa Üret
        </button>
        <button type="button" class="btn btn-success" onclick="printAllQRCards()">
            <i class="fas fa-print"></i> Tüm QR Kartları Yazdır
        </button>
    </div>
</div>

<?php if (!empty($successMsg)): ?>
    <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid var(--success); color: #6ee7b7; padding: 12px 16px; border-radius: var(--radius-sm); font-size: 0.88rem; margin-bottom: 20px;">
        <i class="fas fa-check-circle" style="margin-right: 6px;"></i> <?php echo htmlspecialchars($successMsg); ?>
    </div>
<?php endif; ?>

<?php if (!empty($errorMsg)): ?>
    <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid var(--danger); color: #fca5a5; padding: 12px 16px; border-radius: var(--radius-sm); font-size: 0.88rem; margin-bottom: 20px;">
        <i class="fas fa-circle-exclamation" style="margin-right: 6px;"></i> <?php echo htmlspecialchars($errorMsg); ?>
    </div>
<?php endif; ?>

<!-- GENEL RESTORAN QR KOD KARTI -->
<div class="card" style="background: linear-gradient(135deg, rgba(217, 119, 6, 0.15) 0%, rgba(22, 31, 48, 0.8) 100%); border-color: var(--border-focus); margin-bottom: 24px;">
    <div class="card-body" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
        <div style="display: flex; align-items: center; gap: 20px;">
            <?php
                $mainMenuUrl = BASE_URL . '/index.php';
                $mainQrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' . urlencode($mainMenuUrl);
            ?>
            <img src="<?php echo $mainQrUrl; ?>" alt="Genel Menü QR" style="width: 100px; height: 100px; border-radius: var(--radius-md); background: #fff; padding: 6px; box-shadow: 0 4px 15px rgba(0,0,0,0.4);">
            <div>
                <span style="background: var(--primary); color: #fff; font-size: 0.72rem; font-weight: 700; padding: 3px 8px; border-radius: 4px; text-transform: uppercase;">Ana Menü QR Kodu</span>
                <h3 style="font-size: 1.25rem; font-weight: 800; color: #fff; margin: 6px 0 4px;"><?php echo htmlspecialchars($restaurantName); ?> - Genel QR Menü</h3>
                <p style="font-size: 0.82rem; color: var(--text-muted);">Masa numarası olmadan kapıda veya sosyal medyada paylaşabileceğiniz genel menü bağlantısı.</p>
                <div style="font-size: 0.78rem; color: var(--primary-light); margin-top: 6px; font-family: monospace;"><?php echo htmlspecialchars($mainMenuUrl); ?></div>
            </div>
        </div>

        <div style="display: flex; gap: 10px;">
            <a href="<?php echo $mainQrUrl; ?>" download="genel_menu_qr.png" class="btn btn-secondary" target="_blank">
                <i class="fas fa-download"></i> QR İndir
            </a>
            <button type="button" class="btn btn-primary" onclick="printSingleQR('<?php echo htmlspecialchars($restaurantName); ?>', 'Genel Menü', '<?php echo $mainQrUrl; ?>', '<?php echo $mainMenuUrl; ?>')">
                <i class="fas fa-print"></i> Kartı Yazdır
            </button>
        </div>
    </div>
</div>

<!-- MASALAR LİSTESİ -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-qrcode" style="color:var(--primary);"></i> Tanımlı Masalar ve QR Kodları (<?php echo count($tables); ?> Masa)</h3>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 60px;">QR Kod</th>
                        <th>Masa No / Kod</th>
                        <th>Masa Adı</th>
                        <th>Özel Menü Bağlantısı (URL)</th>
                        <th style="text-align: right;">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tables)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 32px; color: var(--text-dim);">
                                Henüz masa tanımlanmadı.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($tables as $t): 
                            $tableUrl = BASE_URL . '/index.php?table=' . urlencode($t['table_number']);
                            $qrImgUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($tableUrl);
                        ?>
                            <tr id="row-table-<?php echo $t['id']; ?>">
                                <td>
                                    <a href="<?php echo $qrImgUrl; ?>" target="_blank" title="Büyüt">
                                        <img src="<?php echo $qrImgUrl; ?>" alt="QR" style="width: 44px; height: 44px; border-radius: var(--radius-sm); background: #fff; padding: 2px;">
                                    </a>
                                </td>
                                <td>
                                    <span style="font-size: 1rem; font-weight: 800; color: var(--primary); background: rgba(var(--primary-rgb), 0.15); padding: 4px 10px; border-radius: var(--radius-sm); border: 1px solid var(--border-focus);">
                                        <?php echo htmlspecialchars($t['table_number']); ?>
                                    </span>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($t['table_name']); ?></strong>
                                </td>
                                <td>
                                    <a href="<?php echo $tableUrl; ?>" target="_blank" style="color: var(--info); font-size: 0.82rem; text-decoration: none;">
                                        <i class="fas fa-link"></i> <?php echo htmlspecialchars($tableUrl); ?>
                                    </a>
                                </td>
                                <td style="text-align: right;">
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="printSingleQR('<?php echo htmlspecialchars($restaurantName); ?>', '<?php echo htmlspecialchars($t['table_name']); ?>', '<?php echo $qrImgUrl; ?>', '<?php echo $tableUrl; ?>')">
                                        <i class="fas fa-print"></i> Kart Yazdır
                                    </button>
                                    <button type="button" class="btn btn-danger btn-icon btn-delete-item" title="Sil" data-type="table" data-id="<?php echo $t['id']; ?>" data-name="<?php echo htmlspecialchars($t['table_name']); ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- TEK MASA EKLE MODALI -->
<div class="admin-modal" id="tableModal">
    <div class="admin-modal-content" style="max-width: 440px;">
        <div class="admin-modal-header">
            <h3 class="card-title"><i class="fas fa-plus" style="color:var(--primary);"></i> Yeni Masa Tanımla</h3>
            <button type="button" class="btn btn-secondary btn-icon" onclick="closeModal('tableModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" action="tables.php">
            <input type="hidden" name="form_action" value="single">
            <div class="admin-modal-body">
                <div class="form-group">
                    <label class="form-label">Masa Numarası / Kodu *</label>
                    <input type="text" name="table_number" class="form-control" placeholder="Örn: 12 veya B4 veya VIP-1" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Masa Görünür Adı *</label>
                    <input type="text" name="table_name" class="form-control" placeholder="Örn: Masa 12 veya Bahçe 4" required>
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('tableModal')">İptal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Masayı Kaydet</button>
            </div>
        </form>
    </div>
</div>

<!-- TOPLU MASA ÜRET MODALI -->
<div class="admin-modal" id="bulkModal">
    <div class="admin-modal-content" style="max-width: 460px;">
        <div class="admin-modal-header">
            <h3 class="card-title"><i class="fas fa-layer-group" style="color:var(--primary);"></i> Toplu Masa Oluştur</h3>
            <button type="button" class="btn btn-secondary btn-icon" onclick="closeModal('bulkModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" action="tables.php">
            <input type="hidden" name="form_action" value="bulk">
            <div class="admin-modal-body">
                <div class="form-group">
                    <label class="form-label">Masa İsmi Ön Eki</label>
                    <input type="text" name="bulk_prefix" class="form-control" value="Masa " placeholder="Örn: Masa veya Bahçe ">
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label class="form-label">Başlangıç No</label>
                        <input type="number" name="bulk_start" class="form-control" value="1" min="1">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Bitiş No</label>
                        <input type="number" name="bulk_end" class="form-control" value="20" max="100">
                    </div>
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('bulkModal')">İptal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-bolt"></i> Masaları Oluştur</button>
            </div>
        </form>
    </div>
</div>

<!-- YAZDIRMA ŞABLONU (PRINT TEMPLATE SCRIPT) -->
<script>
function printSingleQR(restaurant, table, qrUrl, fullUrl) {
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>QR Menü Stand Kartı - ${table}</title>
            <style>
                body { font-family: 'Segoe UI', Arial, sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; background: #fff; }
                .qr-card { width: 340px; border: 2px solid #222; border-radius: 20px; padding: 30px 24px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
                .restaurant-name { font-size: 20px; font-weight: 800; color: #111; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 1px; }
                .tagline { font-size: 12px; color: #666; margin-bottom: 20px; }
                .table-badge { display: inline-block; background: #d97706; color: #fff; font-size: 16px; font-weight: 800; padding: 6px 18px; border-radius: 50px; margin-bottom: 20px; }
                .qr-frame { background: #fff; padding: 12px; border: 2px dashed #ccc; border-radius: 16px; display: inline-block; margin-bottom: 16px; }
                .qr-frame img { width: 200px; height: 200px; display: block; }
                .instruction { font-size: 13px; font-weight: 700; color: #333; margin-bottom: 4px; }
                .sub-instruction { font-size: 11px; color: #777; }
                @media print { body { background: #fff; } .qr-card { box-shadow: none; page-break-inside: avoid; } }
            </style>
        </head>
        <body onload="window.print()">
            <div class="qr-card">
                <div class="restaurant-name">${restaurant}</div>
                <div class="tagline">DİJİTAL QR MENÜ</div>
                <div class="table-badge">${table}</div>
                <div class="qr-frame">
                    <img src="${qrUrl}" alt="QR">
                </div>
                <div class="instruction">📱 Menüyü görmek için kameranızı okutun</div>
                <div class="sub-instruction">Uygulama yüklemenize gerek yoktur</div>
            </div>
        </body>
        </html>
    `);
    printWindow.document.close();
}

function printAllQRCards() {
    const tablesData = <?php echo json_encode($tables); ?>;
    const restaurant = <?php echo json_encode($restaurantName); ?>;
    const baseUrl = <?php echo json_encode(BASE_URL); ?>;

    let cardsHtml = '';
    tablesData.forEach(t => {
        const tableUrl = baseUrl + '/index.php?table=' + encodeURIComponent(t.table_number);
        const qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' + encodeURIComponent(tableUrl);
        cardsHtml += `
            <div class="qr-card">
                <div class="restaurant-name">${restaurant}</div>
                <div class="tagline">DİJİTAL QR MENÜ</div>
                <div class="table-badge">${t.table_name}</div>
                <div class="qr-frame">
                    <img src="${qrUrl}" alt="QR">
                </div>
                <div class="instruction">📱 Menüyü görmek için kameranızı okutun</div>
                <div class="sub-instruction">Uygulama yüklemenize gerek yoktur</div>
            </div>
        `;
    });

    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Tüm Masa QR Menü Stand Kartları</title>
            <style>
                body { font-family: 'Segoe UI', Arial, sans-serif; margin: 0; padding: 20px; background: #fff; }
                .grid-container { display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px; }
                .qr-card { border: 2px solid #222; border-radius: 20px; padding: 24px; text-align: center; page-break-inside: avoid; }
                .restaurant-name { font-size: 18px; font-weight: 800; color: #111; margin-bottom: 4px; text-transform: uppercase; }
                .tagline { font-size: 11px; color: #666; margin-bottom: 14px; }
                .table-badge { display: inline-block; background: #d97706; color: #fff; font-size: 14px; font-weight: 800; padding: 4px 14px; border-radius: 50px; margin-bottom: 14px; }
                .qr-frame { background: #fff; padding: 10px; border: 2px dashed #ccc; border-radius: 12px; display: inline-block; margin-bottom: 12px; }
                .qr-frame img { width: 170px; height: 170px; display: block; }
                .instruction { font-size: 12px; font-weight: 700; color: #333; margin-bottom: 4px; }
                .sub-instruction { font-size: 10px; color: #777; }
                @media print { body { padding: 0; } }
            </style>
        </head>
        <body onload="window.print()">
            <div class="grid-container">
                ${cardsHtml}
            </div>
        </body>
        </html>
    `);
    printWindow.document.close();
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
