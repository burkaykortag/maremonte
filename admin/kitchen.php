<?php
/**
 * Canlı Mutfak & Bar Ekranı (KDS - Kitchen Display System)
 */

require_once __DIR__ . '/header.php';

// Siparişleri Çek (Bekleyen, Hazırlanan, Hazır olanlar)
$stmtOrders = $pdo->query("SELECT * FROM orders WHERE status IN ('pending', 'preparing', 'ready') ORDER BY id ASC");
$orders = $stmtOrders->fetchAll();

foreach ($orders as &$ord) {
    $stmtItems = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $stmtItems->execute([$ord['id']]);
    $items = $stmtItems->fetchAll();
    foreach ($items as &$it) {
        $it['options'] = !empty($it['options_json']) ? json_decode($it['options_json'], true) : [];
    }
    $ord['items'] = $items;
}
?>

<div class="page-header">
    <div class="page-title">
        <h1>👨‍🍳 Canlı Mutfak & Bar Ekranı (KDS)</h1>
        <p>Masalardan gelen anlık siparişler, hazırlık süreleri ve sipariş durum yönetimi</p>
    </div>

    <div style="display: flex; gap: 10px; align-items: center;">
        <span style="font-size: 0.8rem; color: #34d399; background: rgba(16, 185, 129, 0.15); padding: 6px 12px; border-radius: 999px; border: 1px solid rgba(16,185,129,0.3);">
            <i class="fas fa-circle-dot" style="animation: pulse 1.5s infinite;"></i> Canlı Bağlantı Aktif
        </span>
        <button type="button" class="btn btn-secondary btn-sm" onclick="location.reload()">
            <i class="fas fa-rotate"></i> Yenile
        </button>
    </div>
</div>

<style>
@keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.3; } 100% { opacity: 1; } }

.kds-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 18px;
}

.kds-card {
    background: var(--bg-card);
    border: 2px solid var(--border);
    border-radius: var(--radius-md);
    overflow: hidden;
    box-shadow: 0 6px 20px rgba(0,0,0,0.3);
    display: flex;
    flex-direction: column;
    transition: all 0.25s ease;
}

.kds-card.pending { border-color: #f59e0b; }
.kds-card.preparing { border-color: #3b82f6; }
.kds-card.ready { border-color: #10b981; }

.kds-header {
    padding: 12px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: rgba(0,0,0,0.25);
    border-bottom: 1px solid var(--border);
}

.kds-table-badge {
    font-size: 1.15rem;
    font-weight: 800;
    color: #fff;
}

.kds-time {
    font-size: 0.75rem;
    color: var(--text-muted);
    display: flex;
    align-items: center;
    gap: 4px;
}

.kds-body {
    padding: 16px;
    flex: 1;
}

.kds-item {
    padding: 8px 0;
    border-bottom: 1px dashed rgba(255,255,255,0.08);
}
.kds-item:last-child { border-bottom: none; }

.kds-item-title {
    font-weight: 700;
    font-size: 0.95rem;
    color: #fff;
    display: flex;
    align-items: baseline;
    justify-content: space-between;
}

.kds-qty {
    background: var(--primary);
    color: #fff;
    font-weight: 800;
    font-size: 0.8rem;
    padding: 2px 7px;
    border-radius: 4px;
    margin-right: 6px;
}

.kds-options-list {
    margin-top: 4px;
    padding-left: 24px;
    font-size: 0.76rem;
    color: #94a3b8;
}

.kds-note-box {
    margin-top: 12px;
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.3);
    color: #fca5a5;
    padding: 8px 12px;
    border-radius: var(--radius-sm);
    font-size: 0.8rem;
}

.kds-footer {
    padding: 12px 16px;
    background: rgba(0,0,0,0.2);
    border-top: 1px solid var(--border);
    display: flex;
    gap: 8px;
}
</style>

<!-- SİPARİŞ KARTLARI GRİDİ -->
<div class="kds-grid" id="kdsContainer">
    <?php if (empty($orders)): ?>
        <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; background: var(--bg-card); border-radius: var(--radius-md); border: 1px solid var(--border);">
            <i class="fas fa-kitchen-set" style="font-size: 3.5rem; color: var(--text-dim); margin-bottom: 14px; display: block;"></i>
            <h3 style="font-size: 1.2rem; font-weight: 700; color: #fff; margin-bottom: 6px;">Mutfakta Bekleyen Sipariş Yok</h3>
            <p style="color: var(--text-muted); font-size: 0.85rem;">Masalardan yeni bir sipariş geldiğinde sesli uyarı ile bu ekranda görünecektir.</p>
        </div>
    <?php else: ?>
        <?php foreach ($orders as $ord): 
            $status = $ord['status'];
            $timeAgo = round((time() - strtotime($ord['created_at'])) / 60);
        ?>
            <div class="kds-card <?php echo $status; ?>" id="order-card-<?php echo $ord['id']; ?>">
                <div class="kds-header">
                    <span class="kds-table-badge">
                        <i class="fas fa-utensils" style="color:var(--primary);margin-right:6px;"></i>
                        Masa <?php echo htmlspecialchars($ord['table_number']); ?>
                    </span>
                    <span class="kds-time">
                        <i class="fas fa-clock"></i> <?php echo $timeAgo <= 0 ? 'Az önce' : $timeAgo . ' dk önce'; ?>
                    </span>
                </div>

                <div class="kds-body">
                    <div style="font-size: 0.72rem; font-weight: 700; color: var(--text-dim); text-transform: uppercase; margin-bottom: 8px;">
                        SİPARİŞ #<?php echo $ord['id']; ?> • <?php echo date('H:i', strtotime($ord['created_at'])); ?>
                    </div>

                    <?php foreach ($ord['items'] as $item): ?>
                        <div class="kds-item">
                            <div class="kds-item-title">
                                <div>
                                    <span class="kds-qty"><?php echo (int)$item['quantity']; ?>x</span>
                                    <span><?php echo htmlspecialchars($item['product_name']); ?></span>
                                </div>
                                <span style="font-size:0.8rem;color:var(--text-muted);"><?php echo formatPrice($item['price'] * $item['quantity']); ?></span>
                            </div>

                            <?php if (!empty($item['options'])): ?>
                                <div class="kds-options-list">
                                    <?php foreach ($item['options'] as $opt): ?>
                                        <div>• <?php echo htmlspecialchars($opt['group_name'] ?? ''); ?>: <strong style="color:#fff;"><?php echo htmlspecialchars($opt['option_name'] ?? ''); ?></strong></div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                    <?php if (!empty($ord['customer_note'])): ?>
                        <div class="kds-note-box">
                            <strong><i class="fas fa-message"></i> Not:</strong> <?php echo htmlspecialchars($ord['customer_note']); ?>
                        </div>
                    <?php endif; ?>

                    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:14px;padding-top:10px;border-top:1px solid var(--border);">
                        <span style="font-size:0.8rem;color:var(--text-muted);">Toplam Tutar:</span>
                        <strong style="color:var(--primary);font-size:1.05rem;"><?php echo formatPrice($ord['total_price']); ?></strong>
                    </div>
                </div>

                <div class="kds-footer">
                    <?php if ($status === 'pending'): ?>
                        <button type="button" class="btn btn-primary btn-sm" style="flex:1;" onclick="updateOrderStatus(<?php echo $ord['id']; ?>, 'preparing')">
                            <i class="fas fa-fire-burner"></i> Hazırlanıyor
                        </button>
                    <?php elseif ($status === 'preparing'): ?>
                        <button type="button" class="btn btn-success btn-sm" style="flex:1;" onclick="updateOrderStatus(<?php echo $ord['id']; ?>, 'ready')">
                            <i class="fas fa-bell"></i> Hazır Yap
                        </button>
                    <?php elseif ($status === 'ready'): ?>
                        <button type="button" class="btn btn-secondary btn-sm" style="flex:1;background:#065f46;color:#34d399;border-color:#10b981;" onclick="updateOrderStatus(<?php echo $ord['id']; ?>, 'served')">
                            <i class="fas fa-check-double"></i> Masaya Servis Edildi
                        </button>
                    <?php endif; ?>

                    <button type="button" class="btn btn-secondary btn-icon" title="Termal Fiş Yazdır" onclick='printThermalSlip(<?php echo json_encode($ord); ?>)'>
                        <i class="fas fa-print"></i>
                    </button>

                    <button type="button" class="btn btn-danger btn-icon" title="İptal Et" onclick="cancelOrder(<?php echo $ord['id']; ?>)">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- TERMAL FİŞ YAZDIRMA ŞABLONU -->
<script>
async function updateOrderStatus(orderId, status) {
    try {
        const formData = new FormData();
        formData.append('action', 'update_order_status');
        formData.append('order_id', orderId);
        formData.append('status', status);

        const res = await fetch('ajax.php', { method: 'POST', body: formData });
        const data = await res.json();

        if (data.success) {
            showAdminToast(data.message, 'success');
            setTimeout(() => location.reload(), 400);
        } else {
            showAdminToast(data.message || 'Hata oluştu', 'error');
        }
    } catch(err) {
        showAdminToast('Bağlantı hatası', 'error');
    }
}

async function cancelOrder(orderId) {
    if (!confirm('Bu siparişi iptal etmek istediğinize emin misiniz?')) return;
    updateOrderStatus(orderId, 'cancelled');
}

function printThermalSlip(order) {
    const restaurantName = <?php echo json_encode($restaurantName); ?>;
    let itemsHtml = '';
    
    order.items.forEach(it => {
        let optText = '';
        if (it.options && it.options.length > 0) {
            optText = it.options.map(o => `+ ${o.option_name}`).join('<br>');
        }
        itemsHtml += `
            <tr>
                <td style="padding:4px 0; vertical-align:top;"><strong>${it.quantity}x</strong> ${it.product_name} ${optText ? `<br><small style="color:#555;">${optText}</small>` : ''}</td>
                <td style="text-align:right; vertical-align:top; white-space:nowrap;">${parseFloat(it.price * it.quantity).toFixed(2)} ₺</td>
            </tr>
        `;
    });

    const printWin = window.open('', '_blank');
    printWin.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Adisyon Fişi #${order.id}</title>
            <style>
                body { font-family: 'Courier New', monospace; width: 280px; margin: 0 auto; padding: 10px; font-size: 13px; }
                .center { text-align: center; }
                .bold { font-weight: bold; }
                .line { border-top: 1px dashed #000; margin: 8px 0; }
                table { width: 100%; border-collapse: collapse; }
                @media print { body { width: 100%; } }
            </style>
        </head>
        <body onload="window.print()">
            <div class="center bold" style="font-size:16px;">${restaurantName}</div>
            <div class="center">MUTFAK / SİPARİŞ FİŞİ</div>
            <div class="line"></div>
            <div><strong>MASA: ${order.table_number}</strong></div>
            <div>Sipariş No: #${order.id}</div>
            <div>Tarih: ${order.created_at}</div>
            <div class="line"></div>
            <table>
                ${itemsHtml}
            </table>
            <div class="line"></div>
            ${order.customer_note ? `<div style="margin-bottom:6px;"><strong>NOT:</strong> ${order.customer_note}</div><div class="line"></div>` : ''}
            <div style="font-size:15px; font-weight:bold; display:flex; justify-content:space-between;">
                <span>TOPLAM:</span>
                <span>${parseFloat(order.total_price).toFixed(2)} ₺</span>
            </div>
            <div class="line"></div>
            <div class="center" style="font-size:11px;">Afiyet Olsun!</div>
        </body>
        </html>
    `);
    printWin.document.close();
}

// Canlı Sipariş Polling (Her 8 saniyede bir kontrol et)
let knownOrderCount = <?php echo count($orders); ?>;
setInterval(async () => {
    try {
        const res = await fetch('ajax.php?action=get_kitchen_orders');
        const data = await res.json();
        if (data.success && data.data) {
            if (data.data.length !== knownOrderCount) {
                // Yeni sipariş geldiğinde bildirim ver ve sayfayı yenile
                location.reload();
            }
        }
    } catch(e) {}
}, 8000);
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
