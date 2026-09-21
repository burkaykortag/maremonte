<?php
/**
 * Hikayeler (Stories) & Açılış Pop-up Kampanya Yönetimi
 */

require_once __DIR__ . '/header.php';

$successMsg = '';
$errorMsg = '';

// POST: Hikaye Ekle veya Pop-up Güncelle
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $formType = $_POST['form_type'] ?? 'story';

    if ($formType === 'story') {
        $storyId = (int)($_POST['story_id'] ?? 0);
        $title = clean($_POST['title'] ?? '');
        $link = clean($_POST['link'] ?? '');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        $imageUrl = clean($_POST['image_url'] ?? '');

        if (!empty($_FILES['image_file']['name'])) {
            $uploadRes = uploadImage($_FILES['image_file'], 'branding', 800, 1200);
            if ($uploadRes['success']) {
                $imageUrl = $uploadRes['full_url'];
            } else {
                $errorMsg = $uploadRes['error'];
            }
        }

        if (empty($title) || (empty($imageUrl) && $storyId <= 0)) {
            $errorMsg = 'Lütfen hikaye başlığı ve görseli girin.';
        } elseif (empty($errorMsg)) {
            try {
                if ($storyId > 0) {
                    if (!empty($imageUrl)) {
                        $stmt = $pdo->prepare("UPDATE stories SET title = ?, image = ?, link = ?, sort_order = ? WHERE id = ?");
                        $stmt->execute([$title, $imageUrl, $link, $sortOrder, $storyId]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE stories SET title = ?, link = ?, sort_order = ? WHERE id = ?");
                        $stmt->execute([$title, $link, $sortOrder, $storyId]);
                    }
                    $successMsg = 'Hikaye güncellendi!';
                } else {
                    $stmt = $pdo->prepare("INSERT INTO stories (title, image, link, sort_order, is_active) VALUES (?, ?, ?, ?, 1)");
                    $stmt->execute([$title, $imageUrl, $link, $sortOrder]);
                    $successMsg = 'Yeni hikaye eklendi!';
                }
            } catch (Exception $e) {
                $errorMsg = 'Hata: ' . $e->getMessage();
            }
        }
    } elseif ($formType === 'popup') {
        $popupTitle = clean($_POST['popup_title'] ?? '');
        $popupDesc = clean($_POST['popup_desc'] ?? '');
        $popupBtnText = clean($_POST['popup_btn_text'] ?? '');
        $popupBtnLink = clean($_POST['popup_btn_link'] ?? '');
        $popupImage = clean($_POST['popup_image'] ?? getSetting('popup_image', ''));

        if (!empty($_FILES['popup_image_file']['name'])) {
            $uploadRes = uploadImage($_FILES['popup_image_file'], 'branding', 800, 800);
            if ($uploadRes['success']) {
                $popupImage = $uploadRes['full_url'];
            }
        }

        updateSetting('popup_title', $popupTitle);
        updateSetting('popup_desc', $popupDesc);
        updateSetting('popup_btn_text', $popupBtnText);
        updateSetting('popup_btn_link', $popupBtnLink);
        updateSetting('popup_image', $popupImage);
        $successMsg = 'Giriş pop-up kampanya ayarları kaydedildi!';
    }
}

// Hikayeleri Çek
$stories = $pdo->query("SELECT * FROM stories ORDER BY sort_order ASC, id ASC")->fetchAll();

$popupTitle = getSetting('popup_title', '🎉 Haftanın Özel Spesiyali!');
$popupDesc = getSetting('popup_desc', 'Gusto Smokehouse Burger yanında çıtır patates ve özel sos ile şimdi %15 indirimli!');
$popupImage = getSetting('popup_image', 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=800&q=80');
$popupBtnText = getSetting('popup_btn_text', 'Hemen İncele');
$popupBtnLink = getSetting('popup_btn_link', '#cat-2');
?>

<div class="page-header">
    <div class="page-title">
        <h1>📸 Hikayeler & Açılış Pop-up Kampanyası</h1>
        <p>Instagram tarzı menü üstü hikayeleri ve açılış duyuru pop-up pencerelerini yönetin</p>
    </div>

    <button type="button" class="btn btn-primary" onclick="openStoryModal()">
        <i class="fas fa-plus"></i> Yeni Hikaye Ekle
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

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">

    <!-- HİKAYELER LİSTESİ -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-circle-play" style="color:var(--primary);"></i> Menü Üstü Hikayeleri (<?php echo count($stories); ?> Hikaye)</h3>
        </div>
        <div class="card-body" style="padding: 0;">
            <!-- MASAÜSTÜ TABLO GÖRÜNÜMÜ -->
            <div class="table-responsive desktop-table-view">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th style="width: 70px;">Görsel</th>
                            <th>Hikaye Başlığı</th>
                            <th>Yönlendirme Linki</th>
                            <th>Sıra</th>
                            <th>Aktif</th>
                            <th style="text-align: right;">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($stories)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 32px; color: var(--text-dim);">
                                    Henüz hikaye eklenmedi.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($stories as $s): ?>
                                <tr id="row-story-<?php echo $s['id']; ?>">
                                    <td>
                                        <img src="<?php echo htmlspecialchars($s['image']); ?>" alt="" style="width:48px;height:48px;border-radius:50%;object-fit:cover;border:2px solid var(--primary);">
                                    </td>
                                    <td><strong><?php echo htmlspecialchars($s['title']); ?></strong></td>
                                    <td style="color:var(--info); font-size:0.8rem;"><?php echo htmlspecialchars($s['link'] ?: '-'); ?></td>
                                    <td><span style="font-weight:700; background:var(--bg-input); padding:3px 8px; border-radius:4px;"><?php echo $s['sort_order']; ?></span></td>
                                    <td>
                                        <label class="switch">
                                            <input type="checkbox" class="story-toggle" data-id="<?php echo $s['id']; ?>" <?php echo $s['is_active'] ? 'checked' : ''; ?>>
                                            <span class="slider"></span>
                                        </label>
                                    </td>
                                    <td style="text-align: right;">
                                        <button type="button" class="btn btn-secondary btn-icon" onclick='editStory(<?php echo json_encode($s); ?>)'>
                                            <i class="fas fa-pen"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger btn-icon" onclick="deleteStory(<?php echo $s['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- MOBİL KARTLAR GÖRÜNÜMÜ -->
            <div class="mobile-cards-view" style="padding: 12px;">
                <?php if (empty($stories)): ?>
                    <div style="text-align: center; padding: 24px; color: var(--text-dim);">Henüz hikaye eklenmedi.</div>
                <?php else: ?>
                    <?php foreach ($stories as $s): ?>
                        <div class="mobile-card" id="card-story-<?php echo $s['id']; ?>">
                            <div class="mobile-card-top">
                                <img src="<?php echo htmlspecialchars($s['image']); ?>" alt="" style="width:52px;height:52px;border-radius:50%;object-fit:cover;border:2px solid var(--primary); flex-shrink:0;">
                                <div class="mobile-card-info">
                                    <div class="mobile-card-title"><?php echo htmlspecialchars($s['title']); ?></div>
                                    <div class="mobile-card-tags">
                                        <span class="mobile-tag"><i class="fas fa-arrow-down-1-9"></i> Sıra: <?php echo $s['sort_order']; ?></span>
                                        <?php if (!empty($s['link'])): ?>
                                            <span class="mobile-tag" style="color: var(--info);"><i class="fas fa-link"></i> <?php echo htmlspecialchars($s['link']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div style="flex-shrink: 0;">
                                    <label class="switch" title="Aktif / Pasif">
                                        <input type="checkbox" class="story-toggle" data-id="<?php echo $s['id']; ?>" <?php echo $s['is_active'] ? 'checked' : ''; ?>>
                                        <span class="slider"></span>
                                    </label>
                                </div>
                            </div>

                            <div class="mobile-card-footer">
                                <span style="font-size: 0.75rem; color: var(--text-dim);">Durum: <?php echo $s['is_active'] ? '<strong style="color:var(--success);">Yayında</strong>' : '<strong style="color:var(--danger);">Kapalı</strong>'; ?></span>
                                <div style="display: flex; gap: 6px;">
                                    <button type="button" class="btn btn-secondary btn-icon" onclick='editStory(<?php echo json_encode($s); ?>)'>
                                        <i class="fas fa-pen"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-icon" onclick="deleteStory(<?php echo $s['id']; ?>)">
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

    <!-- AÇILIŞ POP-UP KAMPANYASI -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-bullhorn" style="color:var(--warning);"></i> Açılış Pop-Up Kampanyası</h3>
        </div>
        <form method="POST" action="stories.php" enctype="multipart/form-data">
            <input type="hidden" name="form_type" value="popup">
            <div class="card-body">
                <p style="font-size:0.8rem; color:var(--text-muted); margin-bottom:14px;">
                    Müşteri menüyü ilk açtığında ekranda beliren özel kampanya veya karşılama görseli.
                </p>

                <div class="form-group">
                    <label class="form-label">Kampanya Başlığı</label>
                    <input type="text" name="popup_title" value="<?php echo htmlspecialchars($popupTitle); ?>" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Kampanya Açıklaması</label>
                    <textarea name="popup_desc" rows="3" class="form-control"><?php echo htmlspecialchars($popupDesc); ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Görsel Yükle</label>
                    <input type="file" name="popup_image_file" class="form-control image-upload-input" data-preview="popImgPrev" accept="image/*" style="margin-bottom:6px;">
                    <input type="url" name="popup_image" value="<?php echo htmlspecialchars($popupImage); ?>" placeholder="veya Görsel URL" class="form-control">
                    <div style="margin-top:8px; text-align:center;">
                        <img id="popImgPrev" src="<?php echo htmlspecialchars($popupImage); ?>" alt="" style="max-height:80px; border-radius:6px;">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                    <div class="form-group">
                        <label class="form-label">Buton Metni</label>
                        <input type="text" name="popup_btn_text" value="<?php echo htmlspecialchars($popupBtnText); ?>" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Buton Linki</label>
                        <input type="text" name="popup_btn_link" value="<?php echo htmlspecialchars($popupBtnLink); ?>" class="form-control" placeholder="#cat-2 veya URL">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%;"><i class="fas fa-save"></i> Pop-Up'ı Kaydet</button>
            </div>
        </form>
    </div>

</div>

<!-- HİKAYE EKLE / DÜZENLE MODALI -->
<div class="admin-modal" id="storyModal">
    <div class="admin-modal-content" style="max-width: 450px;">
        <div class="admin-modal-header">
            <h3 class="card-title" id="storyModalTitle"><i class="fas fa-circle-play" style="color:var(--primary);"></i> Yeni Hikaye Ekle</h3>
            <button type="button" class="btn btn-secondary btn-icon" onclick="closeModal('storyModal')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="stories.php" enctype="multipart/form-data">
            <input type="hidden" name="form_type" value="story">
            <input type="hidden" name="story_id" id="formStoryId" value="0">
            <div class="admin-modal-body">
                <div class="form-group">
                    <label class="form-label">Hikaye Başlığı *</label>
                    <input type="text" name="title" id="formStoryTitle" class="form-control" placeholder="Örn: Günün Spesiyali" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Yönlendirme Linki (Opsiyonel)</label>
                    <input type="text" name="link" id="formStoryLink" class="form-control" placeholder="Örn: #cat-3 veya https://...">
                </div>
                <div class="form-group">
                    <label class="form-label">Sıra No</label>
                    <input type="number" name="sort_order" id="formStorySort" class="form-control" value="1">
                </div>
                <div class="form-group">
                    <label class="form-label">Görsel Yükle *</label>
                    <input type="file" name="image_file" class="form-control image-upload-input" data-preview="storyImgPrev" accept="image/*" style="margin-bottom:6px;">
                    <input type="url" name="image_url" id="formStoryImageUrl" class="form-control" placeholder="veya Görsel URL">
                </div>
                <div style="text-align:center; margin-bottom:12px;">
                    <img id="storyImgPrev" src="" alt="" style="max-height:90px; border-radius:8px; display:none; margin:0 auto;">
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('storyModal')">İptal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Kaydet</button>
            </div>
        </form>
    </div>
</div>

<script>
function openStoryModal() {
    document.getElementById('storyModalTitle').innerHTML = '<i class="fas fa-circle-play" style="color:var(--primary);"></i> Yeni Hikaye Ekle';
    document.getElementById('formStoryId').value = '0';
    document.getElementById('formStoryTitle').value = '';
    document.getElementById('formStoryLink').value = '';
    document.getElementById('formStorySort').value = '1';
    document.getElementById('formStoryImageUrl').value = '';
    document.getElementById('storyImgPrev').style.display = 'none';
    openModal('storyModal');
}

function editStory(s) {
    document.getElementById('storyModalTitle').innerHTML = '<i class="fas fa-pen" style="color:var(--primary);"></i> Hikayeyi Düzenle: ' + s.title;
    document.getElementById('formStoryId').value = s.id;
    document.getElementById('formStoryTitle').value = s.title;
    document.getElementById('formStoryLink').value = s.link || '';
    document.getElementById('formStorySort').value = s.sort_order || 1;
    document.getElementById('formStoryImageUrl').value = s.image || '';

    const preview = document.getElementById('storyImgPrev');
    if (s.image) {
        preview.src = s.image;
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }

    openModal('storyModal');
}

async function deleteStory(id) {
    if (!confirm('Bu hikayeyi silmek istediğinize emin misiniz?')) return;
    const formData = new FormData();
    formData.append('action', 'delete_story');
    formData.append('id', id);
    const res = await fetch('ajax.php', { method: 'POST', body: formData });
    const data = await res.json();
    if (data.success) {
        showAdminToast('Hikaye silindi.', 'success');
        const row = document.getElementById(`row-story-${id}`);
        if (row) row.remove();
        const card = document.getElementById(`card-story-${id}`);
        if (card) card.remove();
    }
}

document.querySelectorAll('.story-toggle').forEach(chk => {
    chk.addEventListener('change', async function() {
        const formData = new FormData();
        formData.append('action', 'toggle_story_status');
        formData.append('id', this.dataset.id);
        formData.append('status', this.checked ? 1 : 0);
        await fetch('ajax.php', { method: 'POST', body: formData });
        showAdminToast('Hikaye durumu güncellendi.', 'success');
    });
});
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
