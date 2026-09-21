<?php
/**
 * Admin Paneli - Ana Kontrol Paneli (Dashboard)
 */

require_once __DIR__ . '/header.php';

// İstatistikleri Çek
try {
    $totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $outOfStock = $pdo->query("SELECT COUNT(*) FROM products WHERE is_available = 0")->fetchColumn();
    $totalCategories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
    $totalTables = $pdo->query("SELECT COUNT(*) FROM tables")->fetchColumn();
    $pendingCalls = $pdo->query("SELECT COUNT(*) FROM waiter_calls WHERE status = 'pending'")->fetchColumn();

    // Son Garson Çağrıları
    $stmtRecentCalls = $pdo->query("SELECT * FROM waiter_calls ORDER BY created_at DESC LIMIT 6");
    $recentCalls = $stmtRecentCalls->fetchAll();

    // En Çok Bakılan Ürünler
    $stmtPopular = $pdo->query("SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.view_count DESC, p.id DESC LIMIT 5");
    $popularProducts = $stmtPopular->fetchAll();

} catch (Exception $e) {
    $totalProducts = $outOfStock = $totalCategories = $totalTables = $pendingCalls = 0;
    $recentCalls = [];
    $popularProducts = [];
}
?>

<div class="page-header">
    <div class="page-title">
        <h1>Kontrol Paneli</h1>
        <p>Restoranınızın QR menü durumuna genel bakış ve hızlı yönetim</p>
    </div>

    <div style="display: flex; gap: 10px;">
        <a href="products.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Yeni Ürün Ekle
        </a>
        <a href="quick-price.php" class="btn btn-secondary">
            <i class="fas fa-tags"></i> Hızlı Fiyat Düzenle
        </a>
    </div>
</div>

<!-- KPI İSTATİSTİK KARTLARI -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon primary">
            <i class="fas fa-burger"></i>
        </div>
        <div class="stat-data">
            <h3><?php echo $totalProducts; ?></h3>
            <span>Toplam Ürün (<?php echo $outOfStock; ?> Tükendi)</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon info">
            <i class="fas fa-layer-group"></i>
        </div>
        <div class="stat-data">
            <h3><?php echo $totalCategories; ?></h3>
            <span>Aktif Kategori</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon success">
            <i class="fas fa-qrcode"></i>
        </div>
        <div class="stat-data">
            <h3><?php echo $totalTables; ?></h3>
            <span>Tanımlı Masa</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon <?php echo $pendingCalls > 0 ? 'danger' : 'info'; ?>">
            <i class="fas fa-bell"></i>
        </div>
        <div class="stat-data">
            <h3><?php echo $pendingCalls; ?></h3>
            <span>Bekleyen Garson Çağrısı</span>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 340px), 1fr)); gap: 20px;">

    <!-- SON GARSON ÇAĞRILARI -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-bell" style="color:var(--warning);"></i> Son Masalardan Gelen Çağrılar</h3>
            <a href="waiter-calls.php" class="btn btn-secondary btn-sm">Tümünü Gör</a>
        </div>
        <div class="card-body" style="padding: 0;">
            <!-- MASAÜSTÜ TABLO GÖRÜNÜMÜ -->
            <div class="table-responsive desktop-table-view">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Masa</th>
                            <th>Talep Türü</th>
                            <th>Not</th>
                            <th>Zaman</th>
                            <th>Durum</th>
                            <th>İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentCalls)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 24px; color: var(--text-dim);">
                                    Henüz bekleyen veya gelen bir çağrı yok.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentCalls as $call): 
                                $typeLabel = 'Garson';
                                $typeIcon = 'fa-user-tie';
                                if ($call['call_type'] === 'card_bill') { $typeLabel = 'Kartlı Hesap'; $typeIcon = 'fa-credit-card'; }
                                elseif ($call['call_type'] === 'cash_bill') { $typeLabel = 'Nakit Hesap'; $typeIcon = 'fa-money-bill-wave'; }
                                elseif ($call['call_type'] === 'custom') { $typeLabel = 'Özel İstek'; $typeIcon = 'fa-comment-dots'; }
                            ?>
                                <tr id="row-call-<?php echo $call['id']; ?>">
                                    <td><strong style="color:var(--primary); font-size:1rem;"><?php echo htmlspecialchars($call['table_number']); ?></strong></td>
                                    <td>
                                        <span style="display:inline-flex;align-items:center;gap:6px;font-size:0.82rem;">
                                            <i class="fas <?php echo $typeIcon; ?>" style="color:var(--primary);"></i>
                                            <?php echo $typeLabel; ?>
                                        </span>
                                    </td>
                                    <td style="max-width: 140px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: var(--text-muted); font-size:0.82rem;">
                                        <?php echo htmlspecialchars($call['note'] ?: '-'); ?>
                                    </td>
                                    <td style="font-size: 0.78rem; color: var(--text-dim);">
                                        <?php echo date('H:i', strtotime($call['created_at'])); ?>
                                    </td>
                                    <td>
                                        <?php if ($call['status'] === 'pending'): ?>
                                            <span style="background: rgba(239, 68, 68, 0.2); color: #f87171; padding: 4px 8px; border-radius: 999px; font-size: 0.72rem; font-weight: 700;">Bekliyor</span>
                                        <?php else: ?>
                                            <span style="background: rgba(16, 185, 129, 0.2); color: #34d399; padding: 4px 8px; border-radius: 999px; font-size: 0.72rem; font-weight: 700;">Tamamlandı</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($call['status'] === 'pending'): ?>
                                            <button type="button" class="btn btn-success btn-sm" onclick="markCallDone(<?php echo $call['id']; ?>)">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- MOBİL KARTLAR GÖRÜNÜMÜ -->
            <div class="mobile-cards-view" style="padding: 12px;">
                <?php if (empty($recentCalls)): ?>
                    <div style="text-align: center; padding: 20px; color: var(--text-dim);">Gelen çağrı yok.</div>
                <?php else: ?>
                    <?php foreach ($recentCalls as $call): 
                        $typeLabel = 'Garson Çağrısı';
                        $typeIcon = 'fa-user-tie';
                        if ($call['call_type'] === 'card_bill') { $typeLabel = 'Kartlı Hesap'; $typeIcon = 'fa-credit-card'; }
                        elseif ($call['call_type'] === 'cash_bill') { $typeLabel = 'Nakit Hesap'; $typeIcon = 'fa-money-bill-wave'; }
                        elseif ($call['call_type'] === 'custom') { $typeLabel = 'Özel İstek'; $typeIcon = 'fa-comment-dots'; }
                    ?>
                        <div class="mobile-card">
                            <div class="mobile-card-top">
                                <div style="flex: 1;">
                                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
                                        <span style="font-weight: 800; color: #fff; background: rgba(217, 119, 6, 0.2); padding: 2px 8px; border-radius: var(--radius-xs); border: 1px solid var(--border-focus); font-size: 0.85rem;">
                                            Masa <?php echo htmlspecialchars($call['table_number']); ?>
                                        </span>
                                        <?php if ($call['status'] === 'pending'): ?>
                                            <span style="background: rgba(239, 68, 68, 0.2); color: #f87171; padding: 2px 6px; border-radius: 999px; font-size: 0.68rem; font-weight: 700;">Bekliyor</span>
                                        <?php else: ?>
                                            <span style="background: rgba(16, 185, 129, 0.2); color: #34d399; padding: 2px 6px; border-radius: 999px; font-size: 0.68rem; font-weight: 700;">Tamamlandı</span>
                                        <?php endif; ?>
                                    </div>
                                    <div style="font-size: 0.85rem; font-weight: 600; color: var(--primary);">
                                        <i class="fas <?php echo $typeIcon; ?>"></i> <?php echo $typeLabel; ?>
                                    </div>
                                </div>
                            </div>

                            <?php if (!empty($call['note'])): ?>
                                <div style="background: var(--bg-input); padding: 6px 8px; border-radius: var(--radius-xs); font-size: 0.78rem; color: var(--text-muted);">
                                    <?php echo htmlspecialchars($call['note']); ?>
                                </div>
                            <?php endif; ?>

                            <div class="mobile-card-footer">
                                <span style="font-size: 0.72rem; color: var(--text-dim);"><i class="far fa-clock"></i> <?php echo date('H:i', strtotime($call['created_at'])); ?></span>
                                <?php if ($call['status'] === 'pending'): ?>
                                    <button type="button" class="btn btn-success btn-sm" onclick="markCallDone(<?php echo $call['id']; ?>)">
                                        <i class="fas fa-check"></i> Tamamla
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- EN ÇOK GÖRÜNTÜLENEN ÜRÜNLER -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-fire" style="color:var(--danger);"></i> Popüler & Çok Bakılan Ürünler</h3>
            <a href="products.php" class="btn btn-secondary btn-sm">Ürünleri Yönet</a>
        </div>
        <div class="card-body" style="padding: 0;">
            <!-- MASAÜSTÜ TABLO GÖRÜNÜMÜ -->
            <div class="table-responsive desktop-table-view">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Görsel</th>
                            <th>Ürün Adı</th>
                            <th>Kategori</th>
                            <th>Fiyat</th>
                            <th>Görüntülenme</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($popularProducts)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 24px; color: var(--text-dim);">
                                    Henüz ürün görüntülenmesi kaydedilmedi.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($popularProducts as $prod): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($prod['image'])): ?>
                                            <img src="<?php echo htmlspecialchars($prod['image']); ?>" class="table-thumb" alt="">
                                        <?php else: ?>
                                            <div class="table-thumb" style="display:flex;align-items:center;justify-content:center;color:var(--text-dim);">
                                                <i class="fas fa-utensils"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($prod['name']); ?></strong>
                                    </td>
                                    <td style="color: var(--text-muted); font-size:0.82rem;">
                                        <?php echo htmlspecialchars($prod['cat_name'] ?: 'Kategorisiz'); ?>
                                    </td>
                                    <td style="font-weight: 700; color: var(--primary);">
                                        <?php echo formatPrice($prod['price']); ?>
                                    </td>
                                    <td>
                                        <span style="display:inline-flex;align-items:center;gap:4px;color:var(--text-muted);font-size:0.82rem;">
                                            <i class="fas fa-eye" style="color:var(--info);"></i>
                                            <?php echo (int)$prod['view_count']; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- MOBİL KARTLAR GÖRÜNÜMÜ -->
            <div class="mobile-cards-view" style="padding: 12px;">
                <?php if (empty($popularProducts)): ?>
                    <div style="text-align: center; padding: 20px; color: var(--text-dim);">Görüntülenme yok.</div>
                <?php else: ?>
                    <?php foreach ($popularProducts as $prod): ?>
                        <div class="mobile-card">
                            <div class="mobile-card-top">
                                <?php if (!empty($prod['image'])): ?>
                                    <img src="<?php echo htmlspecialchars($prod['image']); ?>" class="mobile-card-thumb" alt="">
                                <?php else: ?>
                                    <div class="mobile-card-thumb"><i class="fas fa-utensils"></i></div>
                                <?php endif; ?>
                                <div class="mobile-card-info">
                                    <div class="mobile-card-title"><?php echo htmlspecialchars($prod['name']); ?></div>
                                    <div class="mobile-card-tags">
                                        <span class="mobile-tag"><?php echo htmlspecialchars($prod['cat_name'] ?: 'Kategorisiz'); ?></span>
                                        <span style="font-weight: 800; color: var(--primary); font-size: 0.95rem; margin-left: auto;">
                                            <?php echo formatPrice($prod['price']); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="mobile-card-footer">
                                <span style="font-size: 0.75rem; color: var(--text-muted);"><i class="fas fa-eye" style="color:var(--info);"></i> <?php echo (int)$prod['view_count']; ?> kez görüntülendi</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<script>
async function markCallDone(id) {
    const formData = new FormData();
    formData.append('action', 'update_call_status');
    formData.append('id', id);
    formData.append('status', 'completed');

    try {
        const res = await fetch('ajax.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
            showAdminToast('Çağrı tamamlandı olarak işaretlendi.', 'success');
            setTimeout(() => location.reload(), 600);
        }
    } catch(e) {}
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
