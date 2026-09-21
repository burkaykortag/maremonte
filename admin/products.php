<?php
/**
 * Ürün Yönetimi (Ekleme, Düzenleme, Silme, Resim Yükleme, Stok Durumu, Çoklu Dil ve Ekstralar)
 */

require_once __DIR__ . '/header.php';

$successMsg = '';
$errorMsg = '';

// Kategori Listesini Çek
$categories = $pdo->query("SELECT * FROM categories ORDER BY sort_order ASC, name ASC")->fetchAll();

// POST: Ürün Ekle veya Güncelle
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $productId = (int)($_POST['product_id'] ?? 0);
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $name = clean($_POST['name'] ?? '');
    $description = clean($_POST['description'] ?? '');
    
    // Çoklu Dil Alanları
    $nameEn = clean($_POST['name_en'] ?? '');
    $descEn = clean($_POST['desc_en'] ?? '');
    $nameAr = clean($_POST['name_ar'] ?? '');
    $descAr = clean($_POST['desc_ar'] ?? '');
    $nameRu = clean($_POST['name_ru'] ?? '');
    $descRu = clean($_POST['desc_ru'] ?? '');
    $nameDe = clean($_POST['name_de'] ?? '');
    $descDe = clean($_POST['desc_de'] ?? '');

    $price = (float)str_replace(',', '.', $_POST['price'] ?? 0);
    $oldPrice = !empty($_POST['old_price']) ? (float)str_replace(',', '.', $_POST['old_price']) : null;
    $badge = clean($_POST['badge'] ?? '');
    $calories = (int)($_POST['calories'] ?? 0);
    $prepTime = (int)($_POST['prep_time'] ?? 15);
    $allergens = clean($_POST['allergens'] ?? '');
    $isFeatured = !empty($_POST['is_featured']) ? 1 : 0;
    $isAvailable = !empty($_POST['is_available']) ? 1 : 0;
    $imageUrl = clean($_POST['image_url'] ?? '');

    // Dosya Yüklendi mi?
    if (!empty($_FILES['image_file']['name'])) {
        $uploadRes = uploadImage($_FILES['image_file'], 'products');
        if ($uploadRes['success']) {
            $imageUrl = $uploadRes['full_url'];
        } else {
            $errorMsg = $uploadRes['error'];
        }
    }

    if (empty($name) || $categoryId <= 0 || $price < 0) {
        $errorMsg = 'Lütfen ürün adı, kategori ve geçerli bir fiyat girin.';
    } elseif (empty($errorMsg)) {
        try {
            if ($productId > 0) {
                // Güncelleme
                if (!empty($imageUrl)) {
                    $stmt = $pdo->prepare("UPDATE products SET category_id = ?, name = ?, description = ?, name_en = ?, desc_en = ?, name_ar = ?, desc_ar = ?, name_ru = ?, desc_ru = ?, name_de = ?, desc_de = ?, price = ?, old_price = ?, image = ?, badge = ?, calories = ?, prep_time = ?, allergens = ?, is_featured = ?, is_available = ? WHERE id = ?");
                    $stmt->execute([$categoryId, $name, $description, $nameEn, $descEn, $nameAr, $descAr, $nameRu, $descRu, $nameDe, $descDe, $price, $oldPrice, $imageUrl, $badge, $calories, $prepTime, $allergens, $isFeatured, $isAvailable, $productId]);
                } else {
                    $stmt = $pdo->prepare("UPDATE products SET category_id = ?, name = ?, description = ?, name_en = ?, desc_en = ?, name_ar = ?, desc_ar = ?, name_ru = ?, desc_ru = ?, name_de = ?, desc_de = ?, price = ?, old_price = ?, badge = ?, calories = ?, prep_time = ?, allergens = ?, is_featured = ?, is_available = ? WHERE id = ?");
                    $stmt->execute([$categoryId, $name, $description, $nameEn, $descEn, $nameAr, $descAr, $nameRu, $descRu, $nameDe, $descDe, $price, $oldPrice, $badge, $calories, $prepTime, $allergens, $isFeatured, $isAvailable, $productId]);
                }
                $successMsg = 'Ürün başarıyla güncellendi!';
            } else {
                // Yeni Ürün Ekle
                $stmt = $pdo->prepare("INSERT INTO products (category_id, name, description, name_en, desc_en, name_ar, desc_ar, name_ru, desc_ru, name_de, desc_de, price, old_price, image, badge, calories, prep_time, allergens, is_featured, is_available) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$categoryId, $name, $description, $nameEn, $descEn, $nameAr, $descAr, $nameRu, $descRu, $nameDe, $descDe, $price, $oldPrice, $imageUrl, $badge, $calories, $prepTime, $allergens, $isFeatured, $isAvailable]);
                $successMsg = 'Yeni ürün başarıyla eklendi!';
            }
        } catch (Exception $e) {
            $errorMsg = 'Veritabanı hatası: ' . $e->getMessage();
        }
    }
}

// Filtreleme
$filterCat = (int)($_GET['category'] ?? 0);
$filterSearch = clean($_GET['q'] ?? '');

$query = "SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE 1=1";
$params = [];

if ($filterCat > 0) {
    $query .= " AND p.category_id = ?";
    $params[] = $filterCat;
}
if (!empty($filterSearch)) {
    $query .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$filterSearch%";
    $params[] = "%$filterSearch%";
}

$query .= " ORDER BY p.category_id ASC, p.sort_order ASC, p.id DESC";
$stmtP = $pdo->prepare($query);
$stmtP->execute($params);
$products = $stmtP->fetchAll();
?>

<div class="page-header">
    <div class="page-title">
        <h1>Ürün Yönetimi</h1>
        <p>Menünüzdeki ürünleri, fiyatları, görselleri, çoklu dilleri ve ekstra malzemeleri yönetin</p>
    </div>

    <button type="button" class="btn btn-primary" onclick="openProductModal()">
        <i class="fas fa-plus"></i> Yeni Ürün Ekle
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

<!-- FİLTRE VE ARAMA KARTI -->
<div class="card" style="margin-bottom: 20px;">
    <div class="card-body" style="padding: 16px 20px;">
        <form method="GET" action="products.php" style="display: flex; gap: 14px; flex-wrap: wrap; align-items: center;">
            <div style="flex: 1; min-width: 200px;">
                <input type="text" name="q" value="<?php echo htmlspecialchars($filterSearch); ?>" placeholder="Ürün adı veya açıklama ile ara..." class="form-control">
            </div>

            <div style="min-width: 180px;">
                <select name="category" class="form-control" onchange="this.form.submit()">
                    <option value="0">Tüm Kategoriler</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?php echo $c['id']; ?>" <?php echo $filterCat === (int)$c['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($c['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-secondary">
                <i class="fas fa-search"></i> Filtrele
            </button>

            <?php if ($filterCat > 0 || !empty($filterSearch)): ?>
                <a href="products.php" class="btn btn-secondary" title="Filtreyi Temizle">
                    <i class="fas fa-rotate-left"></i>
                </a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- ÜRÜN LİSTESİ TABLOSU -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-utensils" style="color:var(--primary);"></i> Ürün Listesi (<?php echo count($products); ?> Ürün)</h3>
        <a href="quick-price.php" class="btn btn-secondary btn-sm"><i class="fas fa-tags"></i> Hızlı Fiyat Düzenleyici</a>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 60px;">Görsel</th>
                        <th>Ürün Adı & Etiket</th>
                        <th>Kategori</th>
                        <th>Fiyat</th>
                        <th>Ekstralar</th>
                        <th>Stok / Menü</th>
                        <th style="text-align: right;">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 32px; color: var(--text-dim);">
                                Kriterlere uygun ürün bulunamadı.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products as $p): ?>
                            <tr id="row-product-<?php echo $p['id']; ?>">
                                <td>
                                    <?php if (!empty($p['image'])): ?>
                                        <img src="<?php echo htmlspecialchars($p['image']); ?>" class="table-thumb" alt="">
                                    <?php else: ?>
                                        <div class="table-thumb" style="display:flex;align-items:center;justify-content:center;color:var(--text-dim);">
                                            <i class="fas fa-utensils"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($p['name']); ?></strong>
                                    <?php if (!empty($p['badge'])): ?>
                                        <span style="background: rgba(217, 119, 6, 0.2); color: #fbbf24; border:1px solid rgba(217,119,6,0.3); font-size: 0.68rem; font-weight:700; padding:2px 6px; border-radius:4px; margin-left:6px;">
                                            <?php echo htmlspecialchars($p['badge']); ?>
                                        </span>
                                    <?php endif; ?>
                                    <div style="font-size: 0.75rem; color: var(--text-dim); max-width: 320px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; margin-top: 2px;">
                                        <?php echo htmlspecialchars($p['description']); ?>
                                    </div>
                                </td>
                                <td>
                                    <span style="font-size: 0.82rem; color: var(--text-muted); background: var(--bg-input); padding: 4px 10px; border-radius: var(--radius-sm); border: 1px solid var(--border);">
                                        <?php echo htmlspecialchars($p['cat_name'] ?: 'Kategorisiz'); ?>
                                    </span>
                                </td>
                                <td style="font-weight: 800; color: var(--primary);">
                                    <?php echo formatPrice($p['price']); ?>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="openOptionsManager(<?php echo $p['id']; ?>, '<?php echo htmlspecialchars(addslashes($p['name'])); ?>')">
                                        <i class="fas fa-sliders"></i> Ekstralar
                                    </button>
                                </td>
                                <td>
                                    <label class="switch" title="Menüde göster / Tükendi">
                                        <input type="checkbox" class="status-toggle" data-type="product" data-id="<?php echo $p['id']; ?>" <?php echo $p['is_available'] ? 'checked' : ''; ?>>
                                        <span class="slider"></span>
                                    </label>
                                </td>
                                <td style="text-align: right;">
                                    <button type="button" class="btn btn-secondary btn-icon" title="Düzenle" onclick='editProduct(<?php echo json_encode($p); ?>)'>
                                        <i class="fas fa-pen"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-icon btn-delete-item" title="Sil" data-type="product" data-id="<?php echo $p['id']; ?>" data-name="<?php echo htmlspecialchars($p['name']); ?>">
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

<!-- ÜRÜN EKLE / DÜZENLE MODALI -->
<div class="admin-modal" id="productModal">
    <div class="admin-modal-content" style="max-width: 750px;">
        <div class="admin-modal-header">
            <h3 class="card-title" id="modalTitle"><i class="fas fa-burger" style="color:var(--primary);"></i> Yeni Ürün Ekle</h3>
            <button type="button" class="btn btn-secondary btn-icon" onclick="closeModal('productModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form method="POST" action="products.php" enctype="multipart/form-data">
            <input type="hidden" name="product_id" id="formProductId" value="0">

            <div class="admin-modal-body">
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label class="form-label">Ürün Adı (Türkçe) *</label>
                        <input type="text" name="name" id="formName" class="form-control" placeholder="Örn: Trüflü Gurme Burger" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Kategori *</label>
                        <select name="category_id" id="formCategory" class="form-control" required>
                            <option value="">Kategori Seçin</option>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label class="form-label">Satış Fiyatı (₺) *</label>
                        <input type="text" name="price" id="formPrice" class="form-control" placeholder="Örn: 290.00" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Eski / İndirimsiz Fiyat</label>
                        <input type="text" name="old_price" id="formOldPrice" class="form-control" placeholder="Örn: 340.00 (Opsiyonel)">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Özel Rozet / Etiket</label>
                        <select name="badge" id="formBadge" class="form-control">
                            <option value="">Rozet Yok</option>
                            <option value="Şefin Seçimi">Şefin Seçimi</option>
                            <option value="Popüler">Popüler</option>
                            <option value="Yeni">Yeni</option>
                            <option value="Çok Satan">Çok Satan</option>
                            <option value="Acılı">Acılı</option>
                            <option value="Vejetaryen">Vejetaryen</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Ürün Açıklaması & İçerik (Türkçe)</label>
                    <textarea name="description" id="formDesc" rows="2" class="form-control" placeholder="Ürünün içindekiler, sunum şekli ve lezzet detayları..."></textarea>
                </div>

                <!-- ÇOKLU DİL ALANLARI (AKORDİYON) -->
                <details style="background: var(--bg-input); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 12px 14px; margin-bottom: 16px;">
                    <summary style="font-weight: 700; color: #60a5fa; cursor: pointer; font-size: 0.85rem;">
                        <i class="fas fa-language"></i> Diğer Dillerde İsim ve Açıklamalar (İngilizce, Arapça, Rusça, Almanca)
                    </summary>
                    <div style="margin-top: 14px; display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                        <div>
                            <label class="form-label">🇬🇧 English Name</label>
                            <input type="text" name="name_en" id="formNameEn" class="form-control" placeholder="e.g. Truffle Gourmet Burger">
                        </div>
                        <div>
                            <label class="form-label">🇬🇧 English Description</label>
                            <input type="text" name="desc_en" id="formDescEn" class="form-control" placeholder="Description in English...">
                        </div>
                        <div>
                            <label class="form-label">🇸🇦 Arabic Name</label>
                            <input type="text" name="name_ar" id="formNameAr" class="form-control" placeholder="اسم الوجبة...">
                        </div>
                        <div>
                            <label class="form-label">🇸🇦 Arabic Description</label>
                            <input type="text" name="desc_ar" id="formDescAr" class="form-control" placeholder="الوصف بالعربية...">
                        </div>
                        <div>
                            <label class="form-label">🇷🇺 Russian Name</label>
                            <input type="text" name="name_ru" id="formNameRu" class="form-control" placeholder="Название на русском...">
                        </div>
                        <div>
                            <label class="form-label">🇷🇺 Russian Description</label>
                            <input type="text" name="desc_ru" id="formDescRu" class="form-control" placeholder="Описание на русском...">
                        </div>
                        <div>
                            <label class="form-label">🇩🇪 German Name</label>
                            <input type="text" name="name_de" id="formNameDe" class="form-control" placeholder="Name auf Deutsch...">
                        </div>
                        <div>
                            <label class="form-label">🇩🇪 German Description</label>
                            <input type="text" name="desc_de" id="formDescDe" class="form-control" placeholder="Beschreibung auf Deutsch...">
                        </div>
                    </div>
                </details>

                <!-- Resim Yükleme / URL -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; background: var(--bg-input); padding: 14px; border-radius: var(--radius-sm); border: 1px solid var(--border); margin-bottom: 16px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Görsel Yükle (Bilgisayardan)</label>
                        <input type="file" name="image_file" class="form-control image-upload-input" data-preview="imgPreview" accept="image/*">
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">veya Görsel URL</label>
                        <input type="url" name="image_url" id="formImageUrl" class="form-control" placeholder="https://...">
                    </div>
                </div>

                <div style="margin-bottom: 16px; text-align: center;">
                    <img id="imgPreview" src="" alt="Görsel Önizleme" style="max-height: 120px; border-radius: var(--radius-sm); display: none; margin: 0 auto; border: 1px solid var(--border);">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 2fr; gap: 14px;">
                    <div class="form-group">
                        <label class="form-label">Hazırlık Süresi (Dk)</label>
                        <input type="number" name="prep_time" id="formPrepTime" class="form-control" value="15">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Kalori (Kcal)</label>
                        <input type="number" name="calories" id="formCalories" class="form-control" value="0">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Alerjenler</label>
                        <input type="text" name="allergens" id="formAllergens" class="form-control" placeholder="Örn: Gluten, Laktoz, Fındık">
                    </div>
                </div>

                <div style="display: flex; gap: 24px; padding-top: 6px;">
                    <label style="display: inline-flex; align-items: center; gap: 8px; font-size: 0.85rem; color: var(--text-main); cursor: pointer;">
                        <input type="checkbox" name="is_available" id="formIsAvailable" value="1" checked>
                        <span>Menüde Aktif / Satışta</span>
                    </label>

                    <label style="display: inline-flex; align-items: center; gap: 8px; font-size: 0.85rem; color: var(--text-main); cursor: pointer;">
                        <input type="checkbox" name="is_featured" id="formIsFeatured" value="1">
                        <span>Öne Çıkan Ürün Yap</span>
                    </label>
                </div>
            </div>

            <div class="admin-modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('productModal')">İptal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Kaydet</button>
            </div>
        </form>
    </div>
</div>

<!-- EKSTRALAR VE OPSİYONLAR MODALI -->
<div class="admin-modal" id="optionsModal">
    <div class="admin-modal-content" style="max-width: 580px;">
        <div class="admin-modal-header">
            <h3 class="card-title" id="optionsModalTitle"><i class="fas fa-sliders" style="color:var(--primary);"></i> Ürün Ekstraları</h3>
            <button type="button" class="btn btn-secondary btn-icon" onclick="closeModal('optionsModal')"><i class="fas fa-times"></i></button>
        </div>
        <div class="admin-modal-body">
            <p style="font-size:0.8rem; color:var(--text-muted); margin-bottom:14px;">
                Müşterinin sepete eklerken seçebileceği seçenekler (Örn: Pişme derecesi, ekstra peynir, sos vb.)
            </p>

            <!-- Yeni Opsiyon Ekleme Formu -->
            <form id="addOptionForm" style="background:var(--bg-input); padding:14px; border-radius:var(--radius-sm); border:1px solid var(--border); margin-bottom:16px;">
                <input type="hidden" id="optProductId" value="0">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:10px;">
                    <div>
                        <label class="form-label" style="font-size:0.75rem;">Grup Adı</label>
                        <input type="text" id="optGroupName" class="form-control" placeholder="Örn: Ekstralar veya Pişme" required>
                    </div>
                    <div>
                        <label class="form-label" style="font-size:0.75rem;">Seçenek Adı</label>
                        <input type="text" id="optName" class="form-control" placeholder="Örn: Ekstra Cheddar" required>
                    </div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr auto; gap:10px; align-items:flex-end;">
                    <div>
                        <label class="form-label" style="font-size:0.75rem;">Ek Ücret (₺)</label>
                        <input type="number" step="0.5" id="optPrice" class="form-control" placeholder="0.00" value="0.00">
                    </div>
                    <div>
                        <label style="display:flex; align-items:center; gap:6px; font-size:0.8rem; color:var(--text-muted); cursor:pointer; height:42px;">
                            <input type="checkbox" id="optRequired" value="1">
                            <span>Zorunlu Seçim</span>
                        </label>
                    </div>
                    <button type="submit" class="btn btn-primary" style="height:42px;"><i class="fas fa-plus"></i> Ekle</button>
                </div>
            </form>

            <!-- Mevcut Opsiyonlar Listesi -->
            <div id="optionsListContainer">
                <div style="text-align:center; padding:16px; color:var(--text-dim);">Yükleniyor...</div>
            </div>
        </div>
    </div>
</div>

<script>
function openProductModal() {
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-burger" style="color:var(--primary);"></i> Yeni Ürün Ekle';
    document.getElementById('formProductId').value = '0';
    document.getElementById('formName').value = '';
    document.getElementById('formCategory').value = '';
    document.getElementById('formPrice').value = '';
    document.getElementById('formOldPrice').value = '';
    document.getElementById('formBadge').value = '';
    document.getElementById('formDesc').value = '';
    document.getElementById('formNameEn').value = '';
    document.getElementById('formDescEn').value = '';
    document.getElementById('formNameAr').value = '';
    document.getElementById('formDescAr').value = '';
    document.getElementById('formNameRu').value = '';
    document.getElementById('formDescRu').value = '';
    document.getElementById('formNameDe').value = '';
    document.getElementById('formDescDe').value = '';
    document.getElementById('formImageUrl').value = '';
    document.getElementById('formPrepTime').value = '15';
    document.getElementById('formCalories').value = '0';
    document.getElementById('formAllergens').value = '';
    document.getElementById('formIsAvailable').checked = true;
    document.getElementById('formIsFeatured').checked = false;
    document.getElementById('imgPreview').style.display = 'none';
    openModal('productModal');
}

function editProduct(p) {
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-pen" style="color:var(--primary);"></i> Ürünü Düzenle: ' + p.name;
    document.getElementById('formProductId').value = p.id;
    document.getElementById('formName').value = p.name;
    document.getElementById('formCategory').value = p.category_id;
    document.getElementById('formPrice').value = p.price;
    document.getElementById('formOldPrice').value = p.old_price || '';
    document.getElementById('formBadge').value = p.badge || '';
    document.getElementById('formDesc').value = p.description || '';
    document.getElementById('formNameEn').value = p.name_en || '';
    document.getElementById('formDescEn').value = p.desc_en || '';
    document.getElementById('formNameAr').value = p.name_ar || '';
    document.getElementById('formDescAr').value = p.desc_ar || '';
    document.getElementById('formNameRu').value = p.name_ru || '';
    document.getElementById('formDescRu').value = p.desc_ru || '';
    document.getElementById('formNameDe').value = p.name_de || '';
    document.getElementById('formDescDe').value = p.desc_de || '';
    document.getElementById('formImageUrl').value = p.image || '';
    document.getElementById('formPrepTime').value = p.prep_time || 15;
    document.getElementById('formCalories').value = p.calories || 0;
    document.getElementById('formAllergens').value = p.allergens || '';
    document.getElementById('formIsAvailable').checked = p.is_available == 1;
    document.getElementById('formIsFeatured').checked = p.is_featured == 1;

    const preview = document.getElementById('imgPreview');
    if (p.image) {
        preview.src = p.image;
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }

    openModal('productModal');
}

// OPSİYON YÖNETİCİSİ
async function openOptionsManager(productId, productName) {
    document.getElementById('optionsModalTitle').innerHTML = '<i class="fas fa-sliders" style="color:var(--primary);"></i> Ekstralar: ' + productName;
    document.getElementById('optProductId').value = productId;
    openModal('optionsModal');
    loadProductOptions(productId);
}

async function loadProductOptions(productId) {
    const container = document.getElementById('optionsListContainer');
    container.innerHTML = '<div style="text-align:center; padding:16px; color:var(--text-dim);">Yükleniyor...</div>';
    try {
        const res = await fetch(`ajax.php?action=get_product_options&product_id=${productId}`);
        const data = await res.json();
        if (data.success && data.data) {
            if (data.data.length === 0) {
                container.innerHTML = '<div style="text-align:center; padding:16px; color:var(--text-dim);">Bu ürün için henüz bir ekstra veya seçenek tanımlanmamış.</div>';
            } else {
                let html = '<div style="display:flex; flex-direction:column; gap:8px;">';
                data.data.forEach(opt => {
                    html += `
                        <div style="background:var(--bg-card); padding:10px 14px; border-radius:6px; border:1px solid var(--border); display:flex; justify-content:space-between; align-items:center;">
                            <div>
                                <span style="font-size:0.75rem; color:var(--primary); font-weight:700;">[${opt.group_name}]</span>
                                <strong style="margin-left:6px; font-size:0.88rem;">${opt.option_name}</strong>
                                ${opt.is_required == 1 ? '<span style="font-size:0.7rem; background:#ef4444; color:#fff; padding:1px 5px; border-radius:3px; margin-left:6px;">Zorunlu</span>' : ''}
                            </div>
                            <div style="display:flex; align-items:center; gap:10px;">
                                <span style="font-weight:700; color:#34d399; font-size:0.85rem;">+${parseFloat(opt.extra_price).toFixed(2)} ₺</span>
                                <button type="button" class="btn btn-danger btn-sm btn-icon" onclick="deleteOption(${opt.id}, ${productId})"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                container.innerHTML = html;
            }
        }
    } catch(e) {
        container.innerHTML = '<div style="color:#ef4444; text-align:center;">Seçenekler yüklenemedi.</div>';
    }
}

document.getElementById('addOptionForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const productId = document.getElementById('optProductId').value;
    const groupName = document.getElementById('optGroupName').value;
    const optionName = document.getElementById('optName').value;
    const extraPrice = document.getElementById('optPrice').value;
    const isRequired = document.getElementById('optRequired').checked ? 1 : 0;

    const formData = new FormData();
    formData.append('action', 'save_product_option');
    formData.append('product_id', productId);
    formData.append('group_name', groupName);
    formData.append('option_name', optionName);
    formData.append('extra_price', extraPrice);
    formData.append('is_required', isRequired);

    const res = await fetch('ajax.php', { method: 'POST', body: formData });
    const data = await res.json();
    if (data.success) {
        showAdminToast('Seçenek eklendi!', 'success');
        document.getElementById('optName').value = '';
        document.getElementById('optPrice').value = '0.00';
        loadProductOptions(productId);
    }
});

async function deleteOption(optId, productId) {
    const formData = new FormData();
    formData.append('action', 'delete_product_option');
    formData.append('id', optId);
    await fetch('ajax.php', { method: 'POST', body: formData });
    showAdminToast('Seçenek silindi.', 'success');
    loadProductOptions(productId);
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
