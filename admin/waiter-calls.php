<?php
/**
 * Garson & Hesap Çağrıları Yönetimi (Canlı Bildirim Paneli)
 */

require_once __DIR__ . '/header.php';

$filterStatus = clean($_GET['status'] ?? 'pending');

$query = "SELECT * FROM waiter_calls";
if ($filterStatus !== 'all') {
    $query .= " WHERE status = " . $pdo->quote($filterStatus);
}
$query .= " ORDER BY id DESC LIMIT 50";

$calls = $pdo->query($query)->fetchAll();
?>

<div class="page-header">
    <div class="page-title">
        <h1>Garson & Hesap Çağrıları</h1>
        <p>Müşterilerden gelen garson çağırma ve hesap isteme taleplerini canlı takip edin</p>
    </div>

    <div style="display: flex; gap: 10px; align-items: center;">
        <div style="display: flex; gap: 6px;">
            <a href="waiter-calls.php?status=pending" class="btn <?php echo $filterStatus === 'pending' ? 'btn-primary' : 'btn-secondary'; ?> btn-sm">
                Bekleyenler
            </a>
            <a href="waiter-calls.php?status=completed" class="btn <?php echo $filterStatus === 'completed' ? 'btn-primary' : 'btn-secondary'; ?> btn-sm">
                Tamamlananlar
            </a>
            <a href="waiter-calls.php?status=all" class="btn <?php echo $filterStatus === 'all' ? 'btn-primary' : 'btn-secondary'; ?> btn-sm">
                Tümü
            </a>
        </div>

        <button type="button" class="btn btn-secondary btn-sm" onclick="location.reload()" title="Yenile">
            <i class="fas fa-rotate"></i>
        </button>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-bell" style="color:var(--warning);"></i> Masalardan Gelen Talepler</h3>
        <span style="font-size: 0.8rem; color: var(--text-muted);"><i class="fas fa-satellite-dish" style="color:var(--success);"></i> Canlı İzleme Aktif (10 sn'de bir yenilenir)</span>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Masa Numarası</th>
                        <th>Talep Türü</th>
                        <th>Özel Not / İstek</th>
                        <th>Çağrı Zamanı</th>
                        <th>Durum</th>
                        <th style="text-align: right;">İşlemler</th>
                    </tr>
                </thead>
                <tbody id="callsTableBody">
                    <?php if (empty($calls)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 36px; color: var(--text-dim);">
                                Bu filtrede herhangi bir çağrı bulunmamaktadır.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($calls as $call): 
                            $typeLabel = 'Garson Çağrısı';
                            $typeIcon = 'fa-user-tie';
                            $typeColor = '#fbbf24';
                            if ($call['call_type'] === 'card_bill') {
                                $typeLabel = 'Kredi Kartı ile Hesap';
                                $typeIcon = 'fa-credit-card';
                                $typeColor = '#60a5fa';
                            } elseif ($call['call_type'] === 'cash_bill') {
                                $typeLabel = 'Nakit Hesap';
                                $typeIcon = 'fa-money-bill-wave';
                                $typeColor = '#34d399';
                            } elseif ($call['call_type'] === 'custom') {
                                $typeLabel = 'Özel İstek';
                                $typeIcon = 'fa-comment-dots';
                                $typeColor = '#c084fc';
                            }
                        ?>
                            <tr id="row-call-<?php echo $call['id']; ?>">
                                <td>
                                    <span style="font-size: 1.1rem; font-weight: 800; color: #fff; background: rgba(217, 119, 6, 0.2); padding: 4px 12px; border-radius: var(--radius-sm); border: 1px solid var(--border-focus);">
                                        Masa <?php echo htmlspecialchars($call['table_number']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; color: <?php echo $typeColor; ?>;">
                                        <i class="fas <?php echo $typeIcon; ?>"></i>
                                        <?php echo $typeLabel; ?>
                                    </span>
                                </td>
                                <td style="color: var(--text-muted); font-size: 0.85rem;">
                                    <?php echo htmlspecialchars($call['note'] ?: '-'); ?>
                                </td>
                                <td style="font-size: 0.82rem; color: var(--text-dim);">
                                    <?php echo date('d.m.Y H:i', strtotime($call['created_at'])); ?>
                                </td>
                                <td>
                                    <?php if ($call['status'] === 'pending'): ?>
                                        <span style="background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid rgba(239,68,68,0.3); padding: 4px 10px; border-radius: 999px; font-size: 0.75rem; font-weight: 700;">
                                            Bekliyor
                                        </span>
                                    <?php else: ?>
                                        <span style="background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(16,185,129,0.3); padding: 4px 10px; border-radius: 999px; font-size: 0.75rem; font-weight: 700;">
                                            Tamamlandı
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: right;">
                                    <?php if ($call['status'] === 'pending'): ?>
                                        <button type="button" class="btn btn-success btn-sm" onclick="changeStatus(<?php echo $call['id']; ?>, 'completed')">
                                            <i class="fas fa-check"></i> Tamamla
                                        </button>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-secondary btn-sm" onclick="changeStatus(<?php echo $call['id']; ?>, 'pending')">
                                            <i class="fas fa-rotate-left"></i> Tekrar Aç
                                        </button>
                                    <?php endif; ?>
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
let lastPendingCount = <?php echo count(array_filter($calls, function($c){ return $c['status'] === 'pending'; })); ?>;
let audioEnabled = true;

// Web Audio API Zil/Çan Sesi Üretici (Sıfır harici dosya, her tarayıcıda çalışır)
function playWaiterBell() {
    if (!audioEnabled) return;
    try {
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        if (!AudioContext) return;
        const ctx = new AudioContext();
        
        const now = ctx.currentTime;
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        
        osc.type = 'sine';
        osc.frequency.setValueAtTime(880, now); // A5
        osc.frequency.exponentialRampToValueAtTime(1760, now + 0.15); // A6
        osc.frequency.exponentialRampToValueAtTime(880, now + 0.35);
        
        gain.gain.setValueAtTime(0.3, now);
        gain.gain.exponentialRampToValueAtTime(0.001, now + 1.2);
        
        osc.connect(gain);
        gain.connect(ctx.destination);
        
        osc.start(now);
        osc.stop(now + 1.2);
    } catch (e) {
        console.log('Audio alert blocked:', e);
    }
}

async function changeStatus(id, status) {
    const formData = new FormData();
    formData.append('action', 'update_call_status');
    formData.append('id', id);
    formData.append('status', status);

    try {
        const res = await fetch('ajax.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
            showAdminToast(status === 'completed' ? 'Çağrı tamamlandı.' : 'Çağrı tekrar açıldı.', 'success');
            setTimeout(() => location.reload(), 400);
        }
    } catch (e) {
        showAdminToast('Hata oluştu.', 'error');
    }
}

// Otomatik 5 saniyede bir bekleyen çağrıları canlı kontrol et
setInterval(async () => {
    try {
        const res = await fetch('ajax.php?action=get_pending_calls');
        const data = await res.json();
        if (data.success && data.data) {
            const newCount = data.data.length;
            if (newCount > lastPendingCount) {
                playWaiterBell();
                showAdminToast('🔔 YENİ GARSON ÇAĞRISI GELDİ!', 'warning');
                setTimeout(() => location.reload(), 1200);
            }
            lastPendingCount = newCount;
        }
    } catch(e) {}
}, 5000);
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
