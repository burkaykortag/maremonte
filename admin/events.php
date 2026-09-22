<?php
/**
 * Canlı Müzik & Haftalık Etkinlik Takvimi Yönetimi
 */

require_once __DIR__ . '/header.php';

$successMsg = '';
$errorMsg = '';

// POST: Yeni Etkinlik Ekle veya Düzenle
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $action = clean($_POST['action'] ?? '');

    if ($action === 'save_event') {
        $id = (int)($_POST['id'] ?? 0);
        $title = clean($_POST['title'] ?? '');
        $performer = clean($_POST['performer'] ?? '');
        $eventDate = clean($_POST['event_date'] ?? date('Y-m-d'));
        $eventTime = clean($_POST['event_time'] ?? '20:30');
        $description = clean($_POST['description'] ?? '');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        $isActive = !empty($_POST['is_active']) ? 1 : 0;
        $imageUrl = clean($_POST['image_url'] ?? '');

        // Görsel Dosyası Yüklendi mi?
        if (!empty($_FILES['image_file']['name'])) {
            $uploadRes = uploadImage($_FILES['image_file'], 'events', 1200, 800);
            if ($uploadRes['success']) {
                $imageUrl = $uploadRes['full_url'];
            } else {
                $errorMsg = 'Görsel Yükleme Hatası: ' . $uploadRes['error'];
            }
        }

        if (empty($title)) {
            $errorMsg = 'Lütfen etkinlik başlığını girin.';
        }

        if (empty($errorMsg)) {
            try {
                if ($id > 0) {
                    $stmt = $pdo->prepare("UPDATE events SET title = ?, performer = ?, event_date = ?, event_time = ?, description = ?, image = ?, sort_order = ?, is_active = ? WHERE id = ?");
                    $stmt->execute([$title, $performer, $eventDate, $eventTime, $description, $imageUrl, $sortOrder, $isActive, $id]);
                    $successMsg = 'Etkinlik başarıyla güncellendi!';
                } else {
                    $stmt = $pdo->prepare("INSERT INTO events (title, performer, event_date, event_time, description, image, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$title, $performer, $eventDate, $eventTime, $description, $imageUrl, $sortOrder, $isActive]);
                    $successMsg = 'Yeni etkinlik başarıyla eklendi!';
                }
            } catch (Exception $e) {
                $errorMsg = 'Veritabanı hatası: ' . $e->getMessage();
            }
        }
    }
}

// Tüm Etkinlikleri Çek
$events = $pdo->query("SELECT * FROM events ORDER BY event_date ASC, sort_order ASC, id ASC")->fetchAll();
?>

<div class="page-header">
    <div class="page-title">
        <h1>🎷 Canlı Müzik &amp; Etkinlik Takvimi</h1>
        <p>Konserleri, akustik geceleri, şarap tadımı ve özel etkinlikleri yönetin</p>
    </div>

    <div>
        <button type="button" class="btn btn-primary" onclick="openEventModal()">
            <i class="fas fa-plus"></i> Yeni Etkinlik Ekle
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

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-calendar-days" style="color:var(--primary);"></i> Planlanan Etkinlikler (<?php echo count($events); ?>)</h3>
    </div>
    <div class="card-body" style="padding: 0;">
        
        <!-- MASAÜSTÜ TABLO GÖRÜNÜMÜ -->
        <div class="table-responsive desktop-table-view">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 70px;">Afiş</th>
                        <th>Etkinlik &amp; Sanatçı</th>
                        <th>Tarih &amp; Saat</th>
                        <th>Açıklama</th>
                        <th>Durum</th>
                        <th style="text-align: right;">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($events)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 36px; color: var(--text-dim);">
                                Henüz planlanmış etkinlik bulunmuyor. Yeni bir tane ekleyebilirsiniz!
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($events as $ev): ?>
                            <tr id="row-event-<?php echo $ev['id']; ?>">
                                <td>
                                    <img src="<?php echo htmlspecialchars($ev['image'] ?: '../assets/images/maremonte_logo.svg'); ?>" alt="" style="width: 54px; height: 54px; object-fit: cover; border-radius: var(--radius-xs); border: 1px solid var(--border);" onerror="this.src='../assets/images/maremonte_logo.svg'">
                                </td>
                                <td>
                                    <div style="font-weight: 800; color: #fff; font-size: 0.95rem; margin-bottom: 2px;">
                                        <?php echo htmlspecialchars($ev['title']); ?>
                                    </div>
                                    <?php if (!empty($ev['performer'])): ?>
                                        <div style="font-size: 0.8rem; color: var(--primary-light);">
                                            <i class="fas fa-microphone" style="margin-right: 4px;"></i> <?php echo htmlspecialchars($ev['performer']); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: #fff; font-size: 0.85rem;">
                                        <i class="far fa-calendar" style="color:var(--primary); margin-right: 4px;"></i>
                                        <?php echo date('d.m.Y', strtotime($ev['event_date'])); ?>
                                    </div>
                                    <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 2px;">
                                        <i class="far fa-clock" style="margin-right: 4px;"></i> <?php echo htmlspecialchars($ev['event_time']); ?>
                                    </div>
                                </td>
                                <td style="color: var(--text-muted); font-size: 0.82rem; max-width: 260px;">
                                    <?php echo htmlspecialchars(mb_strimwidth($ev['description'] ?? '', 0, 80, '...')); ?>
                                </td>
                                <td>
                                    <label class="switch">
                                        <input type="checkbox" onchange="toggleEventStatus(<?php echo $ev['id']; ?>, this.checked)" <?php echo $ev['is_active'] ? 'checked' : ''; ?>>
                                        <span class="slider"></span>
                                    </label>
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: inline-flex; gap: 6px;">
                                        <button type="button" class="btn btn-secondary btn-sm" onclick='editEvent(<?php echo json_encode($ev); ?>)' title="Düzenle">
                                            <i class="fas fa-pen"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteEvent(<?php echo $ev['id']; ?>)" title="Sil">
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
        <div class="mobile-cards-view" style="padding: 12px;">
            <?php if (empty($events)): ?>
                <div style="text-align: center; padding: 24px; color: var(--text-dim);">Etkinlik bulunamadı.</div>
            <?php else: ?>
                <?php foreach ($events as $ev): ?>
                    <div class="mobile-card" id="card-event-<?php echo $ev['id']; ?>">
                        <div class="mobile-card-top">
                            <img src="<?php echo htmlspecialchars($ev['image'] ?: '../assets/images/maremonte_logo.svg'); ?>" alt="" style="width: 50px; height: 50px; object-fit: cover; border-radius: var(--radius-xs); border: 1px solid var(--border);" onerror="this.src='../assets/images/maremonte_logo.svg'">
                            <div style="flex: 1; min-width: 0;">
                                <div style="font-weight: 800; color: #fff; font-size: 0.95rem; margin-bottom: 2px;">
                                    <?php echo htmlspecialchars($ev['title']); ?>
                                </div>
                                <?php if (!empty($ev['performer'])): ?>
                                    <div style="font-size: 0.8rem; color: var(--primary-light);">
                                        <i class="fas fa-microphone"></i> <?php echo htmlspecialchars($ev['performer']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div style="display: flex; gap: 14px; font-size: 0.8rem; color: var(--text-muted); margin: 6px 0;">
                            <span><i class="far fa-calendar" style="color:var(--primary);"></i> <?php echo date('d.m.Y', strtotime($ev['event_date'])); ?></span>
                            <span><i class="far fa-clock" style="color:var(--primary);"></i> <?php echo htmlspecialchars($ev['event_time']); ?></span>
                        </div>

                        <?php if (!empty($ev['description'])): ?>
                            <p style="font-size: 0.8rem; color: var(--text-dim); margin-bottom: 8px;">
                                <?php echo htmlspecialchars($ev['description']); ?>
                            </p>
                        <?php endif; ?>

                        <div class="mobile-card-footer">
                            <div style="display:flex; align-items:center; gap:8px;">
                                <span style="font-size:0.75rem; color:var(--text-dim);">Yayında:</span>
                                <label class="switch">
                                    <input type="checkbox" onchange="toggleEventStatus(<?php echo $ev['id']; ?>, this.checked)" <?php echo $ev['is_active'] ? 'checked' : ''; ?>>
                                    <span class="slider"></span>
                                </label>
                            </div>

                            <div style="display:flex; gap:6px;">
                                <button type="button" class="btn btn-secondary btn-sm" onclick='editEvent(<?php echo json_encode($ev); ?>)'>
                                    <i class="fas fa-pen"></i> Düzenle
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" onclick="deleteEvent(<?php echo $ev['id']; ?>)">
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

<!-- ETKİNLİK EKLE / DÜZENLE MODALI -->
<div class="admin-modal" id="eventModal">
    <div class="admin-modal-content" style="max-width: 600px;">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title" id="eventModalTitle"><i class="fas fa-calendar-plus" style="color:var(--primary);"></i> Yeni Etkinlik Ekle</h3>
            <button type="button" class="admin-modal-close" onclick="closeEventModal()"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="events.php" enctype="multipart/form-data">
            <input type="hidden" name="action" value="save_event">
            <input type="hidden" name="id" id="eventId" value="0">

            <div class="admin-modal-body">
                <div class="form-group">
                    <label class="form-label">Etkinlik Başlığı *</label>
                    <input type="text" name="title" id="eventTitle" class="form-control" placeholder="Örn: Gün Batımı Akustik Caz & Saksafon" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Sanatçı / Grup / DJ</label>
                    <input type="text" name="performer" id="eventPerformer" class="form-control" placeholder="Örn: Tuna Trio & Zeynep">
                </div>

                <div class="form-2col-grid" style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div class="form-group">
                        <label class="form-label">Etkinlik Tarihi *</label>
                        <input type="date" name="event_date" id="eventDate" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Başlangıç Saati</label>
                        <input type="text" name="event_time" id="eventTime" class="form-control" placeholder="20:30" value="20:30">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Etkinlik Açıklaması</label>
                    <textarea name="description" id="eventDescription" rows="3" class="form-control" placeholder="Etkinlik detayları, rezervasyon notu vb."></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Afiş / Görsel</label>
                    <input type="file" name="image_file" class="form-control image-upload-input" data-preview="eventImagePreview" accept="image/*" style="margin-bottom: 6px;">
                    <input type="url" name="image_url" id="eventImageUrl" placeholder="veya Görsel URL" class="form-control">
                    <div style="margin-top: 8px; text-align: center;">
                        <img id="eventImagePreview" src="" alt="" style="max-height: 90px; border-radius: var(--radius-xs); border: 1px solid var(--border); display: none;">
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; background: var(--bg-input); padding: 12px; border-radius: var(--radius-sm); border: 1px solid var(--border);">
                    <span style="font-weight: 700; color: #fff; font-size: 0.88rem;">Etkinlik Menüde Yayında Olsun</span>
                    <label class="switch">
                        <input type="checkbox" name="is_active" id="eventIsActive" value="1" checked>
                        <span class="slider"></span>
                    </label>
                </div>
            </div>

            <div class="admin-modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeEventModal()">İptal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Kaydet</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEventModal() {
    document.getElementById('eventId').value = '0';
    document.getElementById('eventTitle').value = '';
    document.getElementById('eventPerformer').value = '';
    document.getElementById('eventDate').value = '<?php echo date('Y-m-d'); ?>';
    document.getElementById('eventTime').value = '20:30';
    document.getElementById('eventDescription').value = '';
    document.getElementById('eventImageUrl').value = '';
    document.getElementById('eventIsActive').checked = true;
    document.getElementById('eventImagePreview').style.display = 'none';
    document.getElementById('eventModalTitle').innerHTML = '<i class="fas fa-calendar-plus" style="color:var(--primary);"></i> Yeni Etkinlik Ekle';
    document.getElementById('eventModal').classList.add('active');
}

function closeEventModal() {
    document.getElementById('eventModal').classList.remove('active');
}

function editEvent(ev) {
    document.getElementById('eventId').value = ev.id;
    document.getElementById('eventTitle').value = ev.title;
    document.getElementById('eventPerformer').value = ev.performer || '';
    document.getElementById('eventDate').value = ev.event_date;
    document.getElementById('eventTime').value = ev.event_time || '20:30';
    document.getElementById('eventDescription').value = ev.description || '';
    document.getElementById('eventImageUrl').value = ev.image || '';
    document.getElementById('eventIsActive').checked = ev.is_active == 1;

    if (ev.image) {
        document.getElementById('eventImagePreview').src = ev.image;
        document.getElementById('eventImagePreview').style.display = 'inline-block';
    } else {
        document.getElementById('eventImagePreview').style.display = 'none';
    }

    document.getElementById('eventModalTitle').innerHTML = '<i class="fas fa-pen" style="color:var(--primary);"></i> Etkinliği Düzenle';
    document.getElementById('eventModal').classList.add('active');
}

async function toggleEventStatus(id, isActive) {
    try {
        const formData = new FormData();
        formData.append('action', 'toggle_event_status');
        formData.append('id', id);
        formData.append('status', isActive ? 1 : 0);

        const res = await fetch('ajax.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
            showAdminToast(data.message, 'success');
        } else {
            showAdminToast('Hata oluştu', 'error');
        }
    } catch(e) {
        showAdminToast('Bağlantı hatası', 'error');
    }
}

async function deleteEvent(id) {
    if (!confirm('Bu etkinliği silmek istediğinize emin misiniz?')) return;

    try {
        const formData = new FormData();
        formData.append('action', 'delete_event');
        formData.append('id', id);

        const res = await fetch('ajax.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
            showAdminToast('Etkinlik silindi.', 'success');
            const row = document.getElementById(`row-event-${id}`);
            const card = document.getElementById(`card-event-${id}`);
            if (row) row.remove();
            if (card) card.remove();
        } else {
            showAdminToast('Silinemedi: ' + data.message, 'error');
        }
    } catch(e) {
        showAdminToast('Bağlantı hatası', 'error');
    }
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
