<?php
/**
 * Garson, Hesap, Vale & Concierge Çağrıları Yönetimi (Canlı Bildirim Paneli)
 */

require_once __DIR__ . '/header.php';

$filterStatus = clean($_GET['status'] ?? 'pending');
$filterType = clean($_GET['type'] ?? 'all');

$query = "SELECT * FROM waiter_calls WHERE 1=1";
if ($filterStatus !== 'all') {
    $query .= " AND status = " . $pdo->quote($filterStatus);
}
if ($filterType !== 'all') {
    $query .= " AND call_type = " . $pdo->quote($filterType);
}
$query .= " ORDER BY id DESC LIMIT 60";

$calls = $pdo->query($query)->fetchAll();

function getCallTypeDetails($type) {
    switch ($type) {
        case 'card_bill':
            return ['label' => 'Kredi Kartı ile Hesap', 'icon' => 'fa-credit-card', 'color' => '#60a5fa', 'bg' => 'rgba(96,165,250,0.15)'];
        case 'cash_bill':
            return ['label' => 'Nakit Hesap', 'icon' => 'fa-money-bill-wave', 'color' => '#34d399', 'bg' => 'rgba(52,211,153,0.15)'];
        case 'valet':
            return ['label' => 'Vale / Aracım', 'icon' => 'fa-car', 'color' => '#f59e0b', 'bg' => 'rgba(245,158,11,0.15)'];
        case 'taxi':
            return ['label' => 'Taksi Çağrısı', 'icon' => 'fa-taxi', 'color' => '#eab308', 'bg' => 'rgba(234,179,8,0.15)'];
        case 'reception':
            return ['label' => 'Resepsiyon Talebi', 'icon' => 'fa-bell-concierge', 'color' => '#ec4899', 'bg' => 'rgba(236,72,153,0.15)'];
        case 'custom':
            return ['label' => 'Özel İstek', 'icon' => 'fa-comment-dots', 'color' => '#c084fc', 'bg' => 'rgba(192,132,252,0.15)'];
        default:
            return ['label' => 'Garson Çağrısı', 'icon' => 'fa-user-tie', 'color' => '#fbbf24', 'bg' => 'rgba(251,191,36,0.15)'];
    }
}
?>

<div class="page-header">
    <div class="page-title">
        <h1>🔔 Garson, Vale &amp; Concierge Çağrıları</h1>
        <p>Masa, şezlong, oda ve cabanalardan gelen canlı talepleri anlık takip edin</p>
    </div>

    <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
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
        <span style="font-size: 0.8rem; color: var(--text-muted);"><i class="fas fa-satellite-dish" style="color:var(--success);"></i> Canlı İzleme Aktif (5 sn'de bir yenilenir)</span>
    </div>
    <div class="card-body" style="padding: 0;">
        <!-- MASAÜSTÜ TABLO GÖRÜNÜMÜ -->
        <div class="table-responsive desktop-table-view">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Masa / Konum</th>
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
                            $info = getCallTypeDetails($call['call_type']);
                        ?>
                            <tr id="row-call-<?php echo $call['id']; ?>">
                                <td>
                                    <span style="font-size: 1.05rem; font-weight: 800; color: #fff; background: rgba(217, 119, 6, 0.2); padding: 4px 12px; border-radius: var(--radius-sm); border: 1px solid var(--border-focus); display:inline-flex; align-items:center; gap:6px;">
                                        <i class="fas fa-location-dot" style="color:var(--primary);font-size:0.85rem;"></i>
                                        <?php echo htmlspecialchars($call['table_number']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; color: <?php echo $info['color']; ?>; background: <?php echo $info['bg']; ?>; padding: 4px 10px; border-radius: 999px; font-size: 0.82rem;">
                                        <i class="fas <?php echo $info['icon']; ?>"></i>
                                        <?php echo $info['label']; ?>
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
                                            <i class="fas fa-clock"></i> Bekliyor
                                        </span>
                                    <?php else: ?>
                                        <span style="background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(16,185,129,0.3); padding: 4px 10px; border-radius: 999px; font-size: 0.75rem; font-weight: 700;">
                                            <i class="fas fa-check"></i> Tamamlandı
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: right;">
                                    <div style="display:inline-flex; gap:6px;">
                                        <button type="button" class="btn btn-secondary btn-sm" onclick='printCallSlip(<?php echo json_encode($call); ?>)' title="Fiş Yazdır">
                                            <i class="fas fa-print"></i>
                                        </button>
                                        <?php if ($call['status'] === 'pending'): ?>
                                            <button type="button" class="btn btn-success btn-sm" onclick="changeStatus(<?php echo $call['id']; ?>, 'completed')">
                                                <i class="fas fa-check"></i> Tamamla
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-secondary btn-sm" onclick="changeStatus(<?php echo $call['id']; ?>, 'pending')">
                                                <i class="fas fa-rotate-left"></i> Tekrar Aç
                                            </button>
                                        <?php endif; ?>
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
            <?php if (empty($calls)): ?>
                <div style="text-align: center; padding: 24px; color: var(--text-dim);">Bu filtrede çağrı bulunmamaktadır.</div>
            <?php else: ?>
                <?php foreach ($calls as $call): 
                    $info = getCallTypeDetails($call['call_type']);
                ?>
                    <div class="mobile-card" id="card-call-<?php echo $call['id']; ?>" style="<?php echo $call['status'] === 'pending' ? 'border-color: rgba(239, 68, 68, 0.4);' : ''; ?>">
                        <div class="mobile-card-top">
                            <div style="flex: 1;">
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                                    <span style="font-size: 1.05rem; font-weight: 800; color: #fff; background: rgba(217, 119, 6, 0.2); padding: 3px 10px; border-radius: var(--radius-xs); border: 1px solid var(--border-focus);">
                                        <i class="fas fa-location-dot" style="color:var(--primary); font-size:0.85rem;"></i>
                                        <?php echo htmlspecialchars($call['table_number']); ?>
                                    </span>
                                    <?php if ($call['status'] === 'pending'): ?>
                                        <span style="background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid rgba(239,68,68,0.3); padding: 3px 8px; border-radius: 999px; font-size: 0.70rem; font-weight: 700;">
                                            <i class="fas fa-clock"></i> Bekliyor
                                        </span>
                                    <?php else: ?>
                                        <span style="background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(16,185,129,0.3); padding: 3px 8px; border-radius: 999px; font-size: 0.70rem; font-weight: 700;">
                                            <i class="fas fa-check"></i> Tamamlandı
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div style="font-weight: 700; color: <?php echo $info['color']; ?>; font-size: 0.88rem; display: flex; align-items: center; gap: 6px;">
                                    <i class="fas <?php echo $info['icon']; ?>"></i> <?php echo $info['label']; ?>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($call['note'])): ?>
                            <div style="background: var(--bg-input); padding: 8px 10px; border-radius: var(--radius-xs); font-size: 0.82rem; color: var(--text-main); border: 1px solid var(--border); margin-top:6px;">
                                <strong style="color: var(--text-dim); font-size: 0.75rem;">Müşteri Notu:</strong><br>
                                <?php echo htmlspecialchars($call['note']); ?>
                            </div>
                        <?php endif; ?>

                        <div class="mobile-card-footer">
                            <span style="font-size: 0.75rem; color: var(--text-dim);"><i class="far fa-clock"></i> <?php echo date('d.m.Y H:i', strtotime($call['created_at'])); ?></span>
                            <div style="display:flex; gap:6px;">
                                <button type="button" class="btn btn-secondary btn-sm" onclick='printCallSlip(<?php echo json_encode($call); ?>)'>
                                    <i class="fas fa-print"></i>
                                </button>
                                <?php if ($call['status'] === 'pending'): ?>
                                    <button type="button" class="btn btn-success btn-sm" onclick="changeStatus(<?php echo $call['id']; ?>, 'completed')">
                                        <i class="fas fa-check"></i> Tamamla
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="changeStatus(<?php echo $call['id']; ?>, 'pending')">
                                        <i class="fas fa-rotate-left"></i> Tekrar Aç
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
let lastPendingCount = <?php echo count(array_filter($calls, function($c){ return $c['status'] === 'pending'; })); ?>;
let audioEnabled = true;

// Web Audio API Zil/Çan Sesi Üretici
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
        osc.frequency.setValueAtTime(880, now);
        osc.frequency.exponentialRampToValueAtTime(1760, now + 0.15);
        osc.frequency.exponentialRampToValueAtTime(880, now + 0.35);
        
        gain.gain.setValueAtTime(0.35, now);
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

function printCallSlip(call) {
    const restaurantName = <?php echo json_encode($restaurantName); ?>;
    const printWin = window.open('', '_blank');
    printWin.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Çağrı Fişi #${call.id}</title>
            <style>
                body { font-family: 'Courier New', monospace; width: 260px; margin: 0 auto; padding: 8px; font-size: 13px; }
                .center { text-align: center; }
                .bold { font-weight: bold; }
                .line { border-top: 1px dashed #000; margin: 8px 0; }
                @media print { body { width: 100%; } }
            </style>
        </head>
        <body onload="window.print()">
            <div class="center bold" style="font-size:15px;">${restaurantName}</div>
            <div class="center">GARSON / SERVİS ÇAĞRISI</div>
            <div class="line"></div>
            <div style="font-size:16px; font-weight:bold;">KONUM: ${call.table_number}</div>
            <div>Talep: <strong>${call.call_type}</strong></div>
            <div>Zaman: ${call.created_at}</div>
            <div class="line"></div>
            ${call.note ? `<div><strong>NOT:</strong> ${call.note}</div><div class="line"></div>` : ''}
            <div class="center" style="font-size:11px;">Mare & Monte Bistro Servis</div>
        </body>
        </html>
    `);
    printWin.document.close();
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
                showAdminToast('🔔 YENİ GARSON / CONCIERGE ÇAĞRISI GELDİ!', 'warning');
                setTimeout(() => location.reload(), 1200);
            }
            lastPendingCount = newCount;
        }
    } catch(e) {}
}, 5000);
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
