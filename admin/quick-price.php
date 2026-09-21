<?php
/**
 * Hızlı Fiyat Güncelleme ve Toplu Fiyat Düzenleme Aracı
 */

require_once __DIR__ . '/header.php';

// Kategorileri Çek
$categories = $pdo->query("SELECT * FROM categories ORDER BY sort_order ASC, name ASC")->fetchAll();

// Ürünleri Çek
$filterCat = (int)($_GET['category'] ?? 0);
$query = "SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id";
if ($filterCat > 0) {
    $query .= " WHERE p.category_id = " . (int)$filterCat;
}
$query .= " ORDER BY p.category_id ASC, p.sort_order ASC, p.name ASC";
$products = $pdo->query($query)->fetchAll();
?>

<div class="page-header">
    <div class="page-title">
        <h1>Hızlı Fiyat Düzenleyici</h1>
        <p>Ürün fiyatlarını tek tıkla liste üzerinden anında güncelleyin veya toplu zam/indirim uygulayın</p>
    </div>

    <div style="display: flex; gap: 10px;">
        <button type="button" class="btn btn-primary" onclick="openModal('batchPriceModal')">
            <i class="fas fa-calculator"></i> Toplu Fiyat Güncelle
        </button>
        <a href="products.php" class="btn btn-secondary">
            <i class="fas fa-list"></i> Ürün Listesine Dön
        </a>
    </div>
</div>

<!-- KATEGORİ FİLTRESİ VE BİLGİ KUTUSU -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 16px; margin-bottom: 20px;">
    <div class="card" style="margin-bottom: 0;">
        <div class="card-body" style="padding: 14px 20px; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 14px;">
                <label class="form-label" style="margin-bottom: 0; white-space: nowrap;">KATEGORİYE GÖRE FİLTRELE:</label>
                <select class="form-control" style="width: auto; min-width: 220px;" onchange="location.href='quick-price.php?category=' + this.value">
                    <option value="0">Tüm Kategoriler (<?php echo count($products); ?> Ürün)</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?php echo $c['id']; ?>" <?php echo $filterCat === (int)$c['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($c['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <span style="font-size: 0.8rem; color: var(--text-dim);"><i class="fas fa-info-circle"></i> Fiyatı yazıp kutudan çıkınca otomatik kaydedilir.</span>
        </div>
    </div>

    <div class="card" style="margin-bottom: 0; background: rgba(217, 119, 6, 0.08); border-color: var(--border-focus);">
        <div class="card-body" style="padding: 14px 20px; display: flex; align-items: center; gap: 12px;">
            <i class="fas fa-bolt" style="font-size: 1.5rem; color: var(--primary);"></i>
            <div>
                <div style="font-weight: 700; color: #fff; font-size: 0.88rem;">Anlık Senkronizasyon</div>
                <div style="font-size: 0.75rem; color: var(--text-muted);">Değiştirdiğiniz fiyatlar QR menüde anında aktif olur.</div>
            </div>
        </div>
    </div>
</div>

<!-- HIZLI FİYAT DÜZENLEME TABLOSU -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-tags" style="color:var(--primary);"></i> Ürün Fiyat Tablosu</h3>
        <span style="font-size: 0.82rem; color: var(--text-muted);"><?php echo count($products); ?> Ürün Listeleniyor</span>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">Görsel</th>
                        <th>Ürün Adı</th>
                        <th>Kategori</th>
                        <th>Mevcut Fiyat (₺)</th>
                        <th style="width: 200px;">Yeni Fiyat Düzenle</th>
                        <th>Eski / İndirim Fiyatı</th>
                        <th>Durum</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): ?>
                        <tr id="row-product-<?php echo $p['id']; ?>">
                            <td>
                                <?php if (!empty($p['image'])): ?>
                                    <img src="<?php echo htmlspecialchars($p['image']); ?>" class="table-thumb" alt="" style="width:38px;height:38px;">
                                <?php else: ?>
                                    <div class="table-thumb" style="width:38px;height:38px;display:flex;align-items:center;justify-content:center;color:var(--text-dim);">
                                        <i class="fas fa-utensils"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($p['name']); ?></strong>
                            </td>
                            <td>
                                <span style="font-size: 0.8rem; color: var(--text-muted);">
                                    <?php echo htmlspecialchars($p['cat_name'] ?: 'Kategorisiz'); ?>
                                </span>
                            </td>
                            <td>
                                <span style="font-weight: 700; color: var(--text-main);" id="current-price-<?php echo $p['id']; ?>">
                                    <?php echo formatPrice($p['price']); ?>
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <input type="text" 
                                           class="form-control quick-price-input" 
                                           data-id="<?php echo $p['id']; ?>" 
                                           value="<?php echo number_format((float)$p['price'], 2, '.', ''); ?>" 
                                           style="width: 110px; font-weight: 800; color: var(--primary); text-align: right; height: 36px;">
                                    <span style="font-weight: 700; color: var(--text-muted);">₺</span>
                                </div>
                            </td>
                            <td style="color: var(--text-dim); font-size: 0.82rem;">
                                <?php echo !empty($p['old_price']) ? formatPrice($p['old_price']) : '-'; ?>
                            </td>
                            <td>
                                <label class="switch">
                                    <input type="checkbox" class="status-toggle" data-type="product" data-id="<?php echo $p['id']; ?>" <?php echo $p['is_available'] ? 'checked' : ''; ?>>
                                    <span class="slider"></span>
                                </label>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- TOPLU FİYAT GÜNCELLEME MODALI -->
<div class="admin-modal" id="batchPriceModal">
    <div class="admin-modal-content" style="max-width: 500px;">
        <div class="admin-modal-header">
            <h3 class="card-title"><i class="fas fa-calculator" style="color:var(--primary);"></i> Toplu Fiyat Güncelleme</h3>
            <button type="button" class="btn btn-secondary btn-icon" onclick="closeModal('batchPriceModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="batchPriceForm">
            <div class="admin-modal-body">
                <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 18px;">
                    Enflasyon veya menü revizyonlarında tüm ürünlere veya seçili kategoriye tek işlemle yüzdelik/sabit fiyat artışı veya indirimi uygulayın.
                </p>

                <div class="form-group">
                    <label class="form-label">Uygulanacak Kategori</label>
                    <select name="category_id" id="batchCategory" class="form-control">
                        <option value="0">Tüm Kategoriler (Tüm Menü)</option>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label class="form-label">İşlem Türü</label>
                        <select name="direction" id="batchDirection" class="form-control">
                            <option value="increase">Fiyat Artışı (Zam +)</option>
                            <option value="decrease">İndirim (Düşür -)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Hesaplama Şekli</label>
                        <select name="update_type" id="batchType" class="form-control">
                            <option value="percent">Yüzde Olarak (%)</option>
                            <option value="fixed">Sabit Tutar (₺)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Değer / Miktar *</label>
                    <input type="number" step="0.1" name="amount" id="batchAmount" class="form-control" placeholder="Örn: 10 (yani %10 veya 10 TL)" required>
                </div>
            </div>

            <div class="admin-modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('batchPriceModal')">Vazgeç</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Fiyatları Uygula</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('batchPriceForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const category_id = document.getElementById('batchCategory').value;
    const direction = document.getElementById('batchDirection').value;
    const update_type = document.getElementById('batchType').value;
    const amount = document.getElementById('batchAmount').value;

    if (!amount || amount <= 0) {
        showAdminToast('Lütfen geçerli bir miktar girin.', 'error');
        return;
    }

    if (!confirm('Seçilen ürünlerin fiyatları güncellenecektir. Onaylıyor musunuz?')) {
        return;
    }

    try {
        const formData = new FormData();
        formData.append('action', 'batch_price_update');
        formData.append('category_id', category_id);
        formData.append('direction', direction);
        formData.append('update_type', update_type);
        formData.append('amount', amount);

        const res = await fetch('ajax.php', { method: 'POST', body: formData });
        const data = await res.json();

        if (data.success) {
            showAdminToast(data.message, 'success');
            closeModal('batchPriceModal');
            setTimeout(() => location.reload(), 800);
        } else {
            showAdminToast(data.message, 'error');
        }
    } catch (err) {
        showAdminToast('Bağlantı hatası.', 'error');
    }
});
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
