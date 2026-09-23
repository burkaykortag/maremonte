<?php
/**
 * Kategori Yönetimi (Ekleme, Düzenleme, Sıralama, Görsel & İkon, Silme)
 */

require_once __DIR__ . '/header.php';

$successMsg = '';
$errorMsg = '';

// POST: Kategori Ekle veya Güncelle
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $catId = (int)($_POST['category_id'] ?? 0);
    $name = clean($_POST['name'] ?? '');
    $icon = clean($_POST['icon'] ?? 'utensils');
    $sortOrder = (int)($_POST['sort_order'] ?? 0);
    $imageUrl = clean($_POST['image_url'] ?? '');
    $isActive = !empty($_POST['is_active']) ? 1 : 0;

    // Resim Yükleme Kontrolü
    if (!empty($_FILES['image_file']['name'])) {
        $uploadRes = uploadImage($_FILES['image_file'], 'categories');
        if ($uploadRes['success']) {
            $imageUrl = $uploadRes['full_url'];
        } else {
            $errorMsg = $uploadRes['error'];
        }
    }

    if (empty($name)) {
        $errorMsg = 'Lütfen bir kategori adı girin.';
    } elseif (empty($errorMsg)) {
        try {
            // Slug üret
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));

            if ($catId > 0) {
                if (!empty($imageUrl)) {
                    $stmt = $pdo->prepare("UPDATE categories SET name = ?, slug = ?, icon = ?, image = ?, sort_order = ?, is_active = ? WHERE id = ?");
                    $stmt->execute([$name, $slug, $icon, $imageUrl, $sortOrder, $isActive, $catId]);
                } else {
                    $stmt = $pdo->prepare("UPDATE categories SET name = ?, slug = ?, icon = ?, sort_order = ?, is_active = ? WHERE id = ?");
                    $stmt->execute([$name, $slug, $icon, $sortOrder, $isActive, $catId]);
                }
                $successMsg = 'Kategori başarıyla güncellendi!';
            } else {
                $stmt = $pdo->prepare("INSERT INTO categories (name, slug, icon, image, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $slug, $icon, $imageUrl, $sortOrder, $isActive]);
                $successMsg = 'Yeni kategori başarıyla eklendi!';
            }
        } catch (Exception $e) {
            $errorMsg = 'Veritabanı hatası: ' . $e->getMessage();
        }
    }
}

// Kategorileri ve Ürün Sayılarını Çek
$stmt = $pdo->query("SELECT c.*, COUNT(p.id) as product_count FROM categories c LEFT JOIN products p ON c.id = p.category_id GROUP BY c.id ORDER BY c.sort_order ASC, c.id ASC");
$categories = $stmt->fetchAll();
?>

<div class="page-header">
    <div class="page-title">
        <h1>Kategori Yönetimi</h1>
        <p>Menünüzün ana bölümlerini, sıralamasını ve ikonlarını düzenleyin</p>
    </div>

    <button type="button" class="btn btn-primary" onclick="openCategoryModal()">
        <i class="fas fa-plus"></i> Yeni Kategori Ekle
    </button>
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

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-layer-group" style="color:var(--primary);"></i> Mevcut Kategoriler (<?php echo count($categories); ?>)</h3>
        <span style="font-size: 0.78rem; color: var(--text-dim); display: inline-flex; align-items: center; gap: 6px;">
            <i class="fas fa-arrows-up-down" style="color:var(--primary);"></i> Sürükle-Bırak veya Ok Tuşlarıyla Sıralayabilirsiniz
        </span>
    </div>

    <!-- HIZLI SIRALAMA BİLGİLENDİRME BANNERI -->
    <div style="background: rgba(197, 160, 89, 0.08); border-bottom: 1px solid rgba(197, 160, 89, 0.2); padding: 10px 18px; font-size: 0.82rem; color: var(--primary-light); display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-hand-pointer" style="font-size: 0.95rem;"></i>
            <span><strong>Menü Sıralaması:</strong> Kategorileri sıralamak için <i class="fas fa-grip-vertical"></i> ikonundan sürükleyin veya <i class="fas fa-arrow-up"></i> <i class="fas fa-arrow-down"></i> butonlarını kullanın. Değişiklikler anında menüye yansır.</span>
        </div>
        <span id="orderStatusBadge" style="font-weight: 700; font-size: 0.74rem; color: var(--success); display: none;">
            <i class="fas fa-check-double"></i> Sıralama Güncel
        </span>
    </div>

    <div class="card-body" style="padding: 0;">
        <!-- MASAÜSTÜ TABLO GÖRÜNÜMÜ -->
        <div class="table-responsive desktop-table-view">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center;">Taşı</th>
                        <th style="width: 70px; text-align: center;">Görsel / İkon</th>
                        <th style="min-width: 180px;">Kategori Bilgisi</th>
                        <th style="width: 140px; text-align: center;">Sıra No &amp; Taşıma</th>
                        <th style="width: 130px; text-align: center;">Kayıtlı Ürün</th>
                        <th style="width: 110px; text-align: center;">Menüde Aktif</th>
                        <th style="width: 110px; text-align: right;">İşlemler</th>
                    </tr>
                </thead>
                <tbody id="categoriesTableBody">
                    <?php if (empty($categories)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 36px; color: var(--text-dim);">
                                Henüz kategori eklenmedi.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($categories as $index => $c): ?>
                            <tr id="row-category-<?php echo $c['id']; ?>" data-id="<?php echo $c['id']; ?>" class="category-sort-row">
                                <td style="text-align: center;" class="drag-handle" title="Sürükleyip bırakarak sıralayın">
                                    <i class="fas fa-grip-vertical"></i>
                                </td>
                                <td style="text-align: center;">
                                    <?php if (!empty($c['image'])): ?>
                                        <img src="<?php echo htmlspecialchars($c['image']); ?>" class="table-thumb" alt="<?php echo htmlspecialchars($c['name']); ?>" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';">
                                        <div class="table-thumb" style="display:none;">
                                            <i class="fas fa-<?php echo htmlspecialchars($c['icon'] ?: 'utensils'); ?>"></i>
                                        </div>
                                    <?php else: ?>
                                        <div class="table-thumb">
                                            <i class="fas fa-<?php echo htmlspecialchars($c['icon'] ?: 'utensils'); ?>"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="font-weight: 800; color: #fff; font-size: 0.95rem;">
                                        <?php echo htmlspecialchars($c['name']); ?>
                                    </div>
                                    <div style="font-size: 0.74rem; color: var(--text-dim); display: flex; align-items: center; gap: 6px; margin-top: 2px;">
                                        <span><i class="fas fa-tag" style="color:var(--primary); font-size:0.7rem;"></i> /<?php echo htmlspecialchars($c['slug']); ?></span>
                                        <span>•</span>
                                        <span>İkon: <code>fa-<?php echo htmlspecialchars($c['icon'] ?: 'utensils'); ?></code></span>
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    <div style="display: inline-flex; align-items: center; gap: 6px;">
                                        <button type="button" class="sort-btn btn-move-up" title="Yukarı Taşı" onclick="moveCategoryOrder(<?php echo $c['id']; ?>, 'up')">
                                            <i class="fas fa-arrow-up"></i>
                                        </button>
                                        <span class="cat-order-badge" id="cat-order-badge-<?php echo $c['id']; ?>" style="font-weight: 800; font-size: 0.82rem; color: var(--primary-light); background: var(--bg-input); padding: 4px 10px; border-radius: var(--radius-full); border: 1px solid var(--border); min-width: 38px; text-align: center;">
                                            #<?php echo (int)$c['sort_order']; ?>
                                        </span>
                                        <button type="button" class="sort-btn btn-move-down" title="Aşağı Taşı" onclick="moveCategoryOrder(<?php echo $c['id']; ?>, 'down')">
                                            <i class="fas fa-arrow-down"></i>
                                        </button>
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    <a href="products.php?category=<?php echo $c['id']; ?>" class="btn btn-secondary btn-sm" style="font-weight: 700; color: #60a5fa; border-color: rgba(59,130,246,0.3); background: rgba(59,130,246,0.1); border-radius: var(--radius-full); padding: 4px 12px; text-decoration: none;" title="Bu kategorideki ürünleri filtrele">
                                        <i class="fas fa-burger"></i> <?php echo (int)$c['product_count']; ?> Ürün
                                    </a>
                                </td>
                                <td style="text-align: center;">
                                    <label class="switch" title="Menüde Aktif / Pasif">
                                        <input type="checkbox" class="status-toggle" data-type="category" data-id="<?php echo $c['id']; ?>" <?php echo $c['is_active'] ? 'checked' : ''; ?>>
                                        <span class="slider"></span>
                                    </label>
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: inline-flex; gap: 6px;">
                                        <button type="button" class="btn btn-secondary btn-icon" title="Düzenle" onclick='editCategory(<?php echo json_encode($c); ?>)'>
                                            <i class="fas fa-pen"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger btn-icon btn-delete-item" title="Sil" data-type="category" data-id="<?php echo $c['id']; ?>" data-name="<?php echo htmlspecialchars($c['name']); ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- MOBİL KARTLAR GÖRÜNÜMÜ -->
        <div class="mobile-cards-view" id="categoriesMobileCards" style="padding: 12px; display: flex; flex-direction: column; gap: 10px;">
            <?php if (empty($categories)): ?>
                <div style="text-align: center; padding: 24px; color: var(--text-dim);">Henüz kategori eklenmedi.</div>
            <?php else: ?>
                <?php foreach ($categories as $c): ?>
                    <div class="mobile-card category-sort-card" id="card-category-<?php echo $c['id']; ?>" data-id="<?php echo $c['id']; ?>">
                        <div class="mobile-card-top">
                            <div class="drag-handle" style="width: 24px; display: flex; align-items: center; justify-content: center;" title="Sürükle">
                                <i class="fas fa-grip-vertical"></i>
                            </div>
                            <?php if (!empty($c['image'])): ?>
                                <img src="<?php echo htmlspecialchars($c['image']); ?>" class="mobile-card-thumb" alt="">
                            <?php else: ?>
                                <div class="mobile-card-thumb"><i class="fas fa-<?php echo htmlspecialchars($c['icon'] ?: 'utensils'); ?>"></i></div>
                            <?php endif; ?>
                            <div class="mobile-card-info">
                                <div class="mobile-card-title"><?php echo htmlspecialchars($c['name']); ?></div>
                                <div class="mobile-card-tags">
                                    <span class="mobile-tag cat-order-badge-mobile" id="cat-order-badge-mob-<?php echo $c['id']; ?>"><i class="fas fa-arrow-down-1-9"></i> Sıra: #<?php echo (int)$c['sort_order']; ?></span>
                                    <a href="products.php?category=<?php echo $c['id']; ?>" class="mobile-tag" style="color: var(--info); text-decoration: none;">
                                        <i class="fas fa-burger"></i> <?php echo (int)$c['product_count']; ?> Ürün
                                    </a>
                                </div>
                            </div>
                            <div style="flex-shrink: 0; display: flex; flex-direction: column; align-items: flex-end; gap: 8px;">
                                <label class="switch" title="Menüde Aktif / Pasif">
                                    <input type="checkbox" class="status-toggle" data-type="category" data-id="<?php echo $c['id']; ?>" <?php echo $c['is_active'] ? 'checked' : ''; ?>>
                                    <span class="slider"></span>
                                </label>
                                <div class="sort-btn-group">
                                    <button type="button" class="sort-btn" title="Yukarı Taşı" onclick="moveCategoryOrder(<?php echo $c['id']; ?>, 'up')">
                                        <i class="fas fa-arrow-up"></i>
                                    </button>
                                    <button type="button" class="sort-btn" title="Aşağı Taşı" onclick="moveCategoryOrder(<?php echo $c['id']; ?>, 'down')">
                                        <i class="fas fa-arrow-down"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="mobile-card-footer">
                            <span style="font-size: 0.75rem; color: var(--text-dim);">Menü Durumu: <?php echo $c['is_active'] ? '<strong style="color:var(--success);">Aktif</strong>' : '<strong style="color:var(--danger);">Pasif</strong>'; ?></span>
                            <div style="display: flex; gap: 6px;">
                                <button type="button" class="btn btn-secondary btn-icon" title="Düzenle" onclick='editCategory(<?php echo json_encode($c); ?>)'>
                                    <i class="fas fa-pen"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-icon btn-delete-item" title="Sil" data-type="category" data-id="<?php echo $c['id']; ?>" data-name="<?php echo htmlspecialchars($c['name']); ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- KATEGORİ EKLE / DÜZENLE MODALI -->
<div class="admin-modal" id="categoryModal">
    <div class="admin-modal-content" style="max-width: 500px;">
        <div class="admin-modal-header">
            <h3 class="card-title" id="catModalTitle"><i class="fas fa-layer-group" style="color:var(--primary);"></i> Yeni Kategori Ekle</h3>
            <button type="button" class="btn btn-secondary btn-icon" onclick="closeModal('categoryModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form method="POST" action="categories.php" enctype="multipart/form-data">
            <input type="hidden" name="category_id" id="formCatId" value="0">

            <div class="admin-modal-body">
                <div class="form-group">
                    <label class="form-label">Kategori Adı *</label>
                    <input type="text" name="name" id="formCatName" class="form-control" placeholder="Örn: Gurme Burgerler" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label class="form-label">FontAwesome İkon Adı</label>
                        <select name="icon" id="formCatIcon" class="form-control">
                            <option value="utensils">utensils (Çatal Bıçak)</option>
                            <option value="burger">burger (Burger)</option>
                            <option value="pizza-slice">pizza-slice (Pizza)</option>
                            <option value="egg">egg (Kahvaltı / Yumurta)</option>
                            <option value="drumstick-bite">drumstick-bite (Tavuk / Et)</option>
                            <option value="bowl-food">bowl-food (Çorba / Kase)</option>
                            <option value="fish">fish (Balık / Deniz Ürünü)</option>
                            <option value="cake-candles">cake-candles (Tatlı / Pasta)</option>
                            <option value="coffee">coffee (Kahve / Sıcak İçecek)</option>
                            <option value="martini-glass">martini-glass (Kokteyl / Bar)</option>
                            <option value="wine-glass">wine-glass (Şarap)</option>
                            <option value="beer-mug-empty">beer-mug-empty (Bira)</option>
                            <option value="leaf">leaf (Salata / Vegan)</option>
                            <option value="fire">fire (Izgara / Spesiyal)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Görüntüleme Sırası (Sıra No)</label>
                        <input type="number" name="sort_order" id="formCatSort" class="form-control" value="1">
                    </div>
                </div>

                <div style="background: var(--bg-input); padding: 14px; border-radius: var(--radius-sm); border: 1px solid var(--border); margin-bottom: 16px;">
                    <div class="form-group" style="margin-bottom: 10px;">
                        <label class="form-label">Kategori Görseli Yükle</label>
                        <input type="file" name="image_file" class="form-control image-upload-input" data-preview="catImgPreview" accept="image/*">
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">veya Görsel URL</label>
                        <input type="url" name="image_url" id="formCatImageUrl" class="form-control" placeholder="https://...">
                    </div>
                </div>

                <div style="margin-bottom: 16px; text-align: center;">
                    <img id="catImgPreview" src="" alt="Önizleme" style="max-height: 100px; border-radius: var(--radius-sm); display: none; margin: 0 auto; border: 1px solid var(--border);">
                </div>

                <label style="display: inline-flex; align-items: center; gap: 8px; font-size: 0.85rem; color: var(--text-main); cursor: pointer;">
                    <input type="checkbox" name="is_active" id="formCatIsActive" value="1" checked>
                    <span>Menüde Aktif Olarak Göster</span>
                </label>
            </div>

            <div class="admin-modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('categoryModal')">İptal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Kaydet</button>
            </div>
        </form>
    </div>
</div>

<!-- SortableJS CDN -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

<script>
function openCategoryModal() {
    document.getElementById('catModalTitle').innerHTML = '<i class="fas fa-layer-group" style="color:var(--primary);"></i> Yeni Kategori Ekle';
    document.getElementById('formCatId').value = '0';
    document.getElementById('formCatName').value = '';
    document.getElementById('formCatIcon').value = 'utensils';
    document.getElementById('formCatSort').value = '<?php echo count($categories) + 1; ?>';
    document.getElementById('formCatImageUrl').value = '';
    document.getElementById('formCatIsActive').checked = true;
    document.getElementById('catImgPreview').style.display = 'none';
    openModal('categoryModal');
}

function editCategory(c) {
    document.getElementById('catModalTitle').innerHTML = '<i class="fas fa-pen" style="color:var(--primary);"></i> Kategoriyi Düzenle: ' + c.name;
    document.getElementById('formCatId').value = c.id;
    document.getElementById('formCatName').value = c.name;
    document.getElementById('formCatIcon').value = c.icon || 'utensils';
    document.getElementById('formCatSort').value = c.sort_order || 1;
    document.getElementById('formCatImageUrl').value = c.image || '';
    document.getElementById('formCatIsActive').checked = c.is_active == 1;

    const preview = document.getElementById('catImgPreview');
    if (c.image) {
        preview.src = c.image;
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }

    openModal('categoryModal');
}

// Sıralama Badgelerini Güncelle
function updateOrderBadges() {
    const desktopRows = document.querySelectorAll('#categoriesTableBody tr.category-sort-row');
    desktopRows.forEach((row, idx) => {
        const id = row.dataset.id;
        const badge = document.getElementById(`cat-order-badge-${id}`);
        if (badge) badge.textContent = `#${idx + 1}`;
        const mobBadge = document.getElementById(`cat-order-badge-mob-${id}`);
        if (mobBadge) mobBadge.innerHTML = `<i class="fas fa-arrow-down-1-9"></i> Sıra: #${idx + 1}`;
    });
}

// AJAX ile Sıralamayı Kaydet
async function saveCategoryOrder(orderIds) {
    try {
        const formData = new FormData();
        formData.append('action', 'update_category_order');
        orderIds.forEach(id => formData.append('order[]', id));

        const res = await fetch('ajax.php', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();

        if (data.success) {
            updateOrderBadges();
            showAdminToast(data.message || 'Kategori sıralaması güncellendi! ✨', 'success');
        } else {
            showAdminToast(data.message || 'Sıralama kaydedilemedi', 'error');
        }
    } catch (err) {
        showAdminToast('Bağlantı hatası', 'error');
    }
}

// Tekil Yukarı / Aşağı Taşı
async function moveCategoryOrder(id, direction) {
    try {
        const formData = new FormData();
        formData.append('action', 'move_category_order');
        formData.append('id', id);
        formData.append('direction', direction);

        const res = await fetch('ajax.php', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();

        if (data.success) {
            // DOM üzerinde kaydır
            const row = document.getElementById(`row-category-${id}`);
            if (row) {
                if (direction === 'up' && row.previousElementSibling) {
                    row.parentNode.insertBefore(row, row.previousElementSibling);
                } else if (direction === 'down' && row.nextElementSibling) {
                    row.parentNode.insertBefore(row.nextElementSibling, row);
                }
            }

            const card = document.getElementById(`card-category-${id}`);
            if (card) {
                if (direction === 'up' && card.previousElementSibling) {
                    card.parentNode.insertBefore(card, card.previousElementSibling);
                } else if (direction === 'down' && card.nextElementSibling) {
                    card.parentNode.insertBefore(card.nextElementSibling, card);
                }
            }

            updateOrderBadges();
            showAdminToast(data.message || 'Sıralama güncellendi! ✓', 'success');
        } else {
            showAdminToast(data.message || 'İşlem yapılamadı', 'error');
        }
    } catch (err) {
        showAdminToast('Bağlantı hatası', 'error');
    }
}

// SortableJS Başlatıcı
document.addEventListener('DOMContentLoaded', () => {
    const tableBody = document.getElementById('categoriesTableBody');
    if (tableBody && typeof Sortable !== 'undefined') {
        new Sortable(tableBody, {
            handle: '.drag-handle',
            animation: 200,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            onEnd: function() {
                const order = Array.from(tableBody.querySelectorAll('tr.category-sort-row')).map(el => el.dataset.id);
                saveCategoryOrder(order);
            }
        });
    }

    const mobileList = document.getElementById('categoriesMobileCards');
    if (mobileList && typeof Sortable !== 'undefined') {
        new Sortable(mobileList, {
            handle: '.drag-handle',
            animation: 200,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            onEnd: function() {
                const order = Array.from(mobileList.querySelectorAll('.category-sort-card')).map(el => el.dataset.id);
                saveCategoryOrder(order);
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
