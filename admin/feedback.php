<?php
/**
 * Müşteri Değerlendirmeleri ve Google Yorumları Yönetimi
 */

require_once __DIR__ . '/header.php';

// Ortalama Puan ve Toplam Sayıyı Hesapla
$avgRating = $pdo->query("SELECT AVG(rating) FROM feedback")->fetchColumn();
$totalFeedback = $pdo->query("SELECT COUNT(*) FROM feedback")->fetchColumn();
$fiveStarsCount = $pdo->query("SELECT COUNT(*) FROM feedback WHERE rating = 5")->fetchColumn();

// Yorumları Çek
$filterRating = (int)($_GET['rating'] ?? 0);
$query = "SELECT * FROM feedback";
if ($filterRating > 0) {
    $query .= " WHERE rating = " . $filterRating;
}
$query .= " ORDER BY id DESC";
$feedbacks = $pdo->query($query)->fetchAll();
?>

<div class="page-header">
    <div class="page-title">
        <h1>⭐ Müşteri Değerlendirmeleri & Yorumlar</h1>
        <p>Masalardan gelen müşteri puanlamaları, yorumlar ve Google Haritalar yönlendirme istatistikleri</p>
    </div>
</div>

<!-- KPI İSTATİSTİK KARTLARI -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon primary">
            <i class="fas fa-star"></i>
        </div>
        <div class="stat-data">
            <h3><?php echo number_format((float)$avgRating, 1, ',', '.'); ?> / 5.0</h3>
            <span>Ortalama Memnuniyet Puanı</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon info">
            <i class="fas fa-comments"></i>
        </div>
        <div class="stat-data">
            <h3><?php echo (int)$totalFeedback; ?></h3>
            <span>Toplam Geri Bildirim</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon success">
            <i class="fas fa-thumbs-up"></i>
        </div>
        <div class="stat-data">
            <h3><?php echo (int)$fiveStarsCount; ?></h3>
            <span>5 Yıldızlı Mükemmel Deneyim</span>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-list" style="color:var(--primary);"></i> Müşteri Yorumları</h3>
        <div style="display:flex; gap:6px;">
            <a href="feedback.php" class="btn <?php echo $filterRating === 0 ? 'btn-primary' : 'btn-secondary'; ?> btn-sm">Tümü</a>
            <a href="feedback.php?rating=5" class="btn <?php echo $filterRating === 5 ? 'btn-primary' : 'btn-secondary'; ?> btn-sm">⭐⭐⭐⭐⭐ 5 Yıldız</a>
            <a href="feedback.php?rating=4" class="btn <?php echo $filterRating === 4 ? 'btn-primary' : 'btn-secondary'; ?> btn-sm">⭐⭐⭐⭐ 4 Yıldız</a>
        </div>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Masa</th>
                        <th>Puan</th>
                        <th>Müşteri Adı</th>
                        <th>Yorum & Değerlendirme</th>
                        <th>Tarih</th>
                        <th style="text-align: right;">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($feedbacks)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 32px; color: var(--text-dim);">
                                Henüz değerlendirme bulunmamaktadır.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($feedbacks as $fb): ?>
                            <tr id="row-feedback-<?php echo $fb['id']; ?>">
                                <td>
                                    <strong style="color:var(--primary); font-size:1rem;">Masa <?php echo htmlspecialchars($fb['table_number'] ?: '-'); ?></strong>
                                </td>
                                <td>
                                    <div style="color: #fbbf24; font-size: 0.9rem;">
                                        <?php for ($i=1; $i<=5; $i++): ?>
                                            <i class="<?php echo $i <= $fb['rating'] ? 'fas fa-star' : 'far fa-star'; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                </td>
                                <td><strong><?php echo htmlspecialchars($fb['name'] ?: 'Misafir'); ?></strong></td>
                                <td style="color: var(--text-muted); font-size: 0.85rem; max-width: 360px;">
                                    <?php echo htmlspecialchars($fb['comment'] ?: 'Yorum bırakılmadı.'); ?>
                                </td>
                                <td style="font-size: 0.8rem; color: var(--text-dim);">
                                    <?php echo date('d.m.Y H:i', strtotime($fb['created_at'])); ?>
                                </td>
                                <td style="text-align: right;">
                                    <button type="button" class="btn btn-danger btn-icon" onclick="deleteFeedback(<?php echo $fb['id']; ?>)">
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

<script>
async function deleteFeedback(id) {
    if (!confirm('Bu yorumu silmek istediğinize emin misiniz?')) return;
    const formData = new FormData();
    formData.append('action', 'delete_feedback');
    formData.append('id', id);
    const res = await fetch('ajax.php', { method: 'POST', body: formData });
    const data = await res.json();
    if (data.success) {
        showAdminToast('Yorum silindi.', 'success');
        document.getElementById(`row-feedback-${id}`).remove();
    }
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
