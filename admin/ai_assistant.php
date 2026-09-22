<?php
/**
 * AI Sommelier & Şef Gastronomi Asistanı (Otomatik Eşleştirme ve Menü Önerileri)
 */

require_once __DIR__ . '/header.php';

// Tüm Ürünleri ve Kategorileri Çek
$stmt = $pdo->query("SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.category_id ASC, p.sort_order ASC, p.id ASC");
$allProducts = $stmt->fetchAll();

$totalCount = count($allProducts);
$pairedCount = 0;
foreach ($allProducts as $p) {
    if (!empty($p['pairing_suggestion'])) {
        $pairedCount++;
    }
}
$coveragePercent = $totalCount > 0 ? round(($pairedCount / $totalCount) * 100) : 0;
?>

<div class="page-header">
    <div class="page-title">
        <h1>🤖 AI Sommelier &amp; Şef Eşleştirme Asistanı</h1>
        <p>Bira, şarap, ana yemek ve tatlılar için gastronomiye uygun akıllı "Birlikte İyi Gider" önerilerini otomatik üretin ve yönetin</p>
    </div>

    <div style="display:flex; gap:10px; flex-wrap:wrap;">
        <button type="button" class="btn btn-primary" onclick="batchGenerateAllPairings()" style="background: linear-gradient(135deg, #C5A059 0%, #9E7A2E 100%); color:#fff; border:none;">
            <i class="fas fa-wand-magic-sparkles"></i> ✨ Tüm Menüyü AI ile Eşleştir
        </button>
    </div>
</div>

<!-- AI İSTATİSTİK VE CANLI SİMÜLATÖR KARTLARI -->
<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px; margin-bottom: 24px;">

    <!-- İstatistik Kartı -->
    <div class="card" style="display: flex; flex-direction: column; justify-content: space-between;">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-chart-pie" style="color:var(--primary);"></i> Eşleştirme Kapsamı</h3>
        </div>
        <div class="card-body">
            <div style="text-align: center; padding: 12px 0;">
                <div style="font-size: 2.8rem; font-weight: 800; color: var(--primary); line-height: 1;">%<?php echo $coveragePercent; ?></div>
                <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 6px;">Menü Eşleştirme Oranı</p>
            </div>

            <div style="background: var(--bg-surface); padding: 12px 14px; border-radius: var(--radius-sm); border: 1px solid var(--border); font-size: 0.82rem; color: var(--text-main);">
                <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                    <span>Toplam Ürün:</span>
                    <strong><?php echo $totalCount; ?> adet</strong>
                </div>
                <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                    <span>Öneri Tanımlı:</span>
                    <strong style="color:#10b981;"><?php echo $pairedCount; ?> adet</strong>
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <span>Eksik Öneri:</span>
                    <strong style="color:#f59e0b;"><?php echo $totalCount - $pairedCount; ?> adet</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Canlı AI Şef Simülatörü -->
    <div class="card" style="background: linear-gradient(135deg, rgba(26, 75, 75, 0.25) 0%, rgba(14, 29, 45, 0.95) 100%); border: 1.5px solid var(--primary);">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-brain" style="color:var(--primary);"></i> Canlı AI Gastronomi &amp; Eşleştirme Laboratuvarı</h3>
            <span style="font-size: 0.72rem; background: var(--primary-grad); color: #fff; padding: 2px 8px; border-radius: 999px; font-weight: 700;">Canlı AI Motoru</span>
        </div>
        <div class="card-body">
            <p style="font-size: 0.82rem; color: var(--text-muted); margin-bottom: 12px;">
                Herhangi bir içecek veya yemek yazın (Örn: <em>Efes Bira</em>, <em>Cabernet Sauvignon</em>, <em>Izgara Levrek</em>, <em>Sufle</em>); AI Sommelier en ideal gastronomi eşleşmesini anında önersin:
            </p>

            <div style="display: flex; gap: 10px; margin-bottom: 14px;">
                <input type="text" id="simDishInput" class="form-control" placeholder="Örn: Tuborg Bira, Merlot Şarap, Dana Bonfile..." value="Buz Gibi Bira">
                <button type="button" class="btn btn-primary" onclick="simulateAiPairing()" style="white-space:nowrap;">
                    <i class="fas fa-wand-magic-sparkles"></i> AI Şefe Sor
                </button>
            </div>

            <!-- Sonuç Kutusu -->
            <div id="simResultBox" style="background: rgba(0,0,0,0.3); border: 1px dashed var(--primary); padding: 14px 16px; border-radius: var(--radius-sm); display: flex; align-items: center; gap: 12px;">
                <div style="font-size: 1.8rem;">👨‍🍳</div>
                <div>
                    <div style="font-size: 0.75rem; color: var(--primary); font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">Şefin Önerisi:</div>
                    <div id="simResultText" style="font-size: 0.92rem; font-weight: 700; color: #FFFFFF; margin-top: 2px;">
                        🥜 Çıtır Bira Tabağı, Tuzlu Fıstık &amp; Baharatlı Soğan Halkası
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- MENÜ EŞLEŞTİRME LİSTESİ VE TABLO -->
<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3 class="card-title"><i class="fas fa-list-check" style="color:var(--primary);"></i> Menü Ürünleri &amp; Eşleştirme Önerileri</h3>
        <span style="font-size: 0.80rem; color: var(--text-muted);">Tablodan önerileri doğrudan düzenleyebilir veya AI ile tek tek yenileyebilirsiniz.</span>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 60px;">Görsel</th>
                        <th style="width: 220px;">Ürün &amp; Kategori</th>
                        <th>👨‍🍳 AI Şef Eşleştirme Önerisi ("Birlikte İyi Gider")</th>
                        <th style="width: 140px; text-align: center;">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allProducts as $p): ?>
                        <tr id="row-prod-<?php echo $p['id']; ?>">
                            <td>
                                <img src="<?php echo htmlspecialchars($p['image'] ?: '../assets/images/maremonte_logo.svg'); ?>" alt="" style="width: 44px; height: 44px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border);">
                            </td>
                            <td>
                                <strong style="font-size: 0.90rem; color: var(--text-main); display: block;"><?php echo htmlspecialchars($p['name']); ?></strong>
                                <span style="font-size: 0.75rem; color: var(--primary);"><?php echo htmlspecialchars($p['cat_name'] ?: 'Kategorisiz'); ?></span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 8px; align-items: center;">
                                    <input type="text" id="pairing-input-<?php echo $p['id']; ?>" class="form-control" value="<?php echo htmlspecialchars($p['pairing_suggestion'] ?: ''); ?>" placeholder="Şef önerisi tanımlanmadı..." style="font-size: 0.84rem;">
                                    <button type="button" class="btn btn-secondary btn-icon" title="Bu Ürün İçin AI ile Üret" onclick="quickAiGenerateSingle(<?php echo $p['id']; ?>, '<?php echo addslashes($p['name']); ?>', <?php echo (int)$p['category_id']; ?>)">
                                        <i class="fas fa-wand-magic-sparkles" style="color: var(--primary);"></i>
                                    </button>
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <button type="button" class="btn btn-primary" style="padding: 6px 12px; font-size: 0.78rem;" onclick="saveSinglePairing(<?php echo $p['id']; ?>)">
                                    <i class="fas fa-save"></i> Kaydet
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Canlı AI Simülatör
async function simulateAiPairing() {
    const dish = document.getElementById('simDishInput').value.trim();
    if (!dish) return;

    document.getElementById('simResultText').innerHTML = '<i class="fas fa-spinner fa-spin"></i> AI Sommelier analiz ediyor...';

    try {
        const formData = new FormData();
        formData.append('action', 'generate_ai_pairing');
        formData.append('name', dish);

        const res = await fetch('ajax.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success && data.pairing) {
            document.getElementById('simResultText').textContent = data.pairing;
        } else {
            document.getElementById('simResultText').textContent = 'Öneri üretilemedi.';
        }
    } catch(e) {
        document.getElementById('simResultText').textContent = 'Bağlantı hatası.';
    }
}

// Tekil Ürün İçin AI Üret
async function quickAiGenerateSingle(id, name, catId) {
    const input = document.getElementById(`pairing-input-${id}`);
    const origVal = input.value;
    input.value = 'AI üretiliyor... ⏳';

    try {
        const formData = new FormData();
        formData.append('action', 'generate_ai_pairing');
        formData.append('name', name);
        formData.append('category_id', catId);

        const res = await fetch('ajax.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success && data.pairing) {
            input.value = data.pairing;
            showAdminToast(`${name} için AI önerisi oluşturuldu! ✨`, 'success');
        } else {
            input.value = origVal;
            showAdminToast('Öneri üretilemedi', 'error');
        }
    } catch(e) {
        input.value = origVal;
        showAdminToast('Bağlantı hatası', 'error');
    }
}

// Tekil Kaydet
async function saveSinglePairing(id) {
    const input = document.getElementById(`pairing-input-${id}`);
    const val = input.value.trim();

    try {
        const formData = new FormData();
        formData.append('action', 'save_single_pairing');
        formData.append('id', id);
        formData.append('pairing_suggestion', val);

        const res = await fetch('ajax.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
            showAdminToast('Eşleştirme başarıyla kaydedildi! ✓', 'success');
        } else {
            showAdminToast(data.message || 'Kaydedilemedi', 'error');
        }
    } catch(e) {
        showAdminToast('Bağlantı hatası', 'error');
    }
}

// Tüm Menüyü Toplu Eşleştir
async function batchGenerateAllPairings() {
    if (!confirm('Tüm menüdeki ürünler için Yapay Zeka Şef eşleştirme önerileri üretilecek ve kaydedilecek. Onaylıyor musunuz?')) return;

    try {
        const formData = new FormData();
        formData.append('action', 'batch_generate_ai_pairings');

        const res = await fetch('ajax.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
            showAdminToast(data.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showAdminToast('Hata: ' + data.message, 'error');
        }
    } catch(e) {
        showAdminToast('Bağlantı hatası', 'error');
    }
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
