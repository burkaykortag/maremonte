<?php
/**
 * Satış ve Sipariş Analiz Raporları
 */

require_once __DIR__ . '/header.php';

// Ciro ve Sipariş İstatistikleri
try {
    $totalRevenue = $pdo->query("SELECT SUM(total_price) FROM orders WHERE status != 'cancelled'")->fetchColumn() ?: 0;
    $totalOrdersCount = $pdo->query("SELECT COUNT(*) FROM orders WHERE status != 'cancelled'")->fetchColumn() ?: 0;
    $avgOrderValue = $totalOrdersCount > 0 ? ($totalRevenue / $totalOrdersCount) : 0;

    // En Çok Satan Ürünler (Top 5)
    $stmtTopItems = $pdo->query("SELECT product_name, SUM(quantity) as total_qty, SUM(price * quantity) as total_sum FROM order_items GROUP BY product_name ORDER BY total_qty DESC LIMIT 6");
    $topItems = $stmtTopItems->fetchAll();

    // Masalara Göre Sipariş Yoğunluğu
    $stmtTables = $pdo->query("SELECT table_number, COUNT(*) as order_count, SUM(total_price) as table_revenue FROM orders WHERE status != 'cancelled' GROUP BY table_number ORDER BY table_revenue DESC LIMIT 6");
    $tableStats = $stmtTables->fetchAll();

    // Son Tamamlanan Siparişler
    $stmtRecent = $pdo->query("SELECT * FROM orders ORDER BY id DESC LIMIT 15");
    $recentOrders = $stmtRecent->fetchAll();

} catch (Exception $e) {
    $totalRevenue = $totalOrdersCount = $avgOrderValue = 0;
    $topItems = $tableStats = $recentOrders = [];
}
?>

<div class="page-header">
    <div class="page-title">
        <h1>📊 Satış & Sipariş Analiz Raporları</h1>
        <p>Restoranınızın dijital menü üzerinden aldığı siparişler, ciro ve en çok satan lezzetler</p>
    </div>
</div>

<!-- KPI İSTATİSTİK KARTLARI -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon primary">
            <i class="fas fa-coins"></i>
        </div>
        <div class="stat-data">
            <h3><?php echo formatPrice($totalRevenue); ?></h3>
            <span>Toplam Sipariş Cirosu</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon info">
            <i class="fas fa-receipt"></i>
        </div>
        <div class="stat-data">
            <h3><?php echo (int)$totalOrdersCount; ?> Adet</h3>
            <span>Toplam Verilen Sipariş</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon success">
            <i class="fas fa-scale-balanced"></i>
        </div>
        <div class="stat-data">
            <h3><?php echo formatPrice($avgOrderValue); ?></h3>
            <span>Ortalama Sepet / Sipariş Tutarı</span>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">

    <!-- EN ÇOK SATAN LEZZETLER -->
    <div class="card" style="margin-bottom: 0;">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-trophy" style="color:#fbbf24;"></i> En Çok Satan Lezzetler</h3>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Ürün Adı</th>
                            <th>Satış Adedi</th>
                            <th>Toplam Ciro</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($topItems)): ?>
                            <tr><td colspan="3" style="text-align:center; padding:24px; color:var(--text-dim);">Henüz sipariş verisi yok.</td></tr>
                        <?php else: ?>
                            <?php foreach ($topItems as $item): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($item['product_name']); ?></strong></td>
                                    <td><span style="font-weight:800; background:rgba(217,119,6,0.15); color:var(--primary); padding:3px 8px; border-radius:4px;"><?php echo (int)$item['total_qty']; ?> Adet</span></td>
                                    <td style="font-weight:700; color:#34d399;"><?php echo formatPrice($item['total_sum']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- MASALARA GÖRE SİPARİŞ YOĞUNLUĞU -->
    <div class="card" style="margin-bottom: 0;">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-chair" style="color:var(--info);"></i> Masa Bazlı Sipariş Dağılımı</h3>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Masa</th>
                            <th>Sipariş Sayısı</th>
                            <th>Masa Toplamı</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($tableStats)): ?>
                            <tr><td colspan="3" style="text-align:center; padding:24px; color:var(--text-dim);">Henüz masa siparişi yok.</td></tr>
                        <?php else: ?>
                            <?php foreach ($tableStats as $ts): ?>
                                <tr>
                                    <td><strong style="color:var(--primary);">Masa <?php echo htmlspecialchars($ts['table_number']); ?></strong></td>
                                    <td><?php echo (int)$ts['order_count']; ?> Sipariş</td>
                                    <td style="font-weight:700; color:#fff;"><?php echo formatPrice($ts['table_revenue']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- TÜM SİPARİŞ GEÇMİŞİ -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-clock-rotate-left" style="color:var(--primary);"></i> Son Sipariş Geçmişi</h3>
    </div>
    <div class="card-body" style="padding: 0;">
        <!-- MASAÜSTÜ TABLO GÖRÜNÜMÜ -->
        <div class="table-responsive desktop-table-view">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Sipariş No</th>
                        <th>Masa</th>
                        <th>Tutar</th>
                        <th>Durum</th>
                        <th>Not</th>
                        <th>Tarih & Saat</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentOrders)): ?>
                        <tr><td colspan="6" style="text-align:center; padding:32px; color:var(--text-dim);">Henüz sipariş kaydı yok.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentOrders as $ord): 
                            $statusBadge = '<span style="background:rgba(245,158,11,0.2);color:#fbbf24;padding:3px 8px;border-radius:4px;font-size:0.75rem;font-weight:700;">Bekliyor</span>';
                            if ($ord['status'] === 'preparing') $statusBadge = '<span style="background:rgba(59,130,246,0.2);color:#60a5fa;padding:3px 8px;border-radius:4px;font-size:0.75rem;font-weight:700;">Hazırlanıyor</span>';
                            elseif ($ord['status'] === 'ready') $statusBadge = '<span style="background:rgba(16,185,129,0.2);color:#34d399;padding:3px 8px;border-radius:4px;font-size:0.75rem;font-weight:700;">Hazır</span>';
                            elseif ($ord['status'] === 'served') $statusBadge = '<span style="background:rgba(100,116,139,0.2);color:#94a3b8;padding:3px 8px;border-radius:4px;font-size:0.75rem;font-weight:700;">Servis Edildi</span>';
                            elseif ($ord['status'] === 'cancelled') $statusBadge = '<span style="background:rgba(239,68,68,0.2);color:#f87171;padding:3px 8px;border-radius:4px;font-size:0.75rem;font-weight:700;">İptal</span>';
                        ?>
                            <tr>
                                <td>#<?php echo $ord['id']; ?></td>
                                <td><strong style="color:var(--primary);">Masa <?php echo htmlspecialchars($ord['table_number']); ?></strong></td>
                                <td style="font-weight:700; color:#fff;"><?php echo formatPrice($ord['total_price']); ?></td>
                                <td><?php echo $statusBadge; ?></td>
                                <td style="font-size:0.8rem; color:var(--text-muted); max-width:200px;"><?php echo htmlspecialchars($ord['customer_note'] ?: '-'); ?></td>
                                <td style="font-size:0.8rem; color:var(--text-dim);"><?php echo date('d.m.Y H:i', strtotime($ord['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- MOBİL KARTLAR GÖRÜNÜMÜ -->
        <div class="mobile-cards-view" style="padding: 12px;">
            <?php if (empty($recentOrders)): ?>
                <div style="text-align: center; padding: 24px; color: var(--text-dim);">Henüz sipariş kaydı yok.</div>
            <?php else: ?>
                <?php foreach ($recentOrders as $ord): 
                    $statusBadge = '<span style="background:rgba(245,158,11,0.2);color:#fbbf24;padding:3px 8px;border-radius:4px;font-size:0.72rem;font-weight:700;">Bekliyor</span>';
                    if ($ord['status'] === 'preparing') $statusBadge = '<span style="background:rgba(59,130,246,0.2);color:#60a5fa;padding:3px 8px;border-radius:4px;font-size:0.72rem;font-weight:700;">Hazırlanıyor</span>';
                    elseif ($ord['status'] === 'ready') $statusBadge = '<span style="background:rgba(16,185,129,0.2);color:#34d399;padding:3px 8px;border-radius:4px;font-size:0.72rem;font-weight:700;">Hazır</span>';
                    elseif ($ord['status'] === 'served') $statusBadge = '<span style="background:rgba(100,116,139,0.2);color:#94a3b8;padding:3px 8px;border-radius:4px;font-size:0.72rem;font-weight:700;">Servis Edildi</span>';
                    elseif ($ord['status'] === 'cancelled') $statusBadge = '<span style="background:rgba(239,68,68,0.2);color:#f87171;padding:3px 8px;border-radius:4px;font-size:0.72rem;font-weight:700;">İptal</span>';
                ?>
                    <div class="mobile-card">
                        <div class="mobile-card-top">
                            <div style="flex: 1;">
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                                    <span style="font-size: 0.95rem; font-weight: 800; color: #fff;">
                                        #<?php echo $ord['id']; ?> - <span style="color:var(--primary);">Masa <?php echo htmlspecialchars($ord['table_number']); ?></span>
                                    </span>
                                    <?php echo $statusBadge; ?>
                                </div>
                                <div style="font-size: 1.1rem; font-weight: 800; color: #34d399;">
                                    <?php echo formatPrice($ord['total_price']); ?>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($ord['customer_note'])): ?>
                            <div style="background: var(--bg-input); padding: 8px 10px; border-radius: var(--radius-xs); font-size: 0.8rem; color: var(--text-muted); border: 1px solid var(--border);">
                                <strong>Not:</strong> <?php echo htmlspecialchars($ord['customer_note']); ?>
                            </div>
                        <?php endif; ?>

                        <div class="mobile-card-footer">
                            <span style="font-size: 0.75rem; color: var(--text-dim);"><i class="far fa-clock"></i> <?php echo date('d.m.Y H:i', strtotime($ord['created_at'])); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
