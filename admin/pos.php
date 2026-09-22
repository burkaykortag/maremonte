<?php
/**
 * Garson El Terminali & Hızlı POS Sipariş Alma Ekranı (Touch POS)
 * Hotel Mare & Monte Bistro - Est. 1985
 */

require_once __DIR__ . '/header.php';

$adminName = $_SESSION['admin_name'] ?? 'Garson';
$restaurantName = getSetting('restaurant_name', 'HOTEL MARE & MONTE BISTRO');

// Kategorileri ve Aktif Ürünleri Çek
$categories = $pdo->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order ASC, id ASC")->fetchAll();
$products = $pdo->query("SELECT p.*, c.name as category_name, c.slug as category_slug FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.is_available = 1 ORDER BY p.sort_order ASC, p.id ASC")->fetchAll();

// Ürün Opsiyonlarını Çek
$stmtOptions = $pdo->query("SELECT * FROM product_options ORDER BY id ASC");
$allOptions = $stmtOptions->fetchAll();
$optionsByProduct = [];
foreach ($allOptions as $opt) {
    $optionsByProduct[$opt['product_id']][] = $opt;
}

// Masaları Çek
$tables = $pdo->query("SELECT * FROM tables ORDER BY id ASC")->fetchAll();
?>

<style>
/* POS Custom Touch Layout */
.pos-container {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 16px;
    height: calc(100vh - 100px);
    margin-top: -10px;
}

.pos-left {
    display: flex;
    flex-direction: column;
    gap: 12px;
    overflow: hidden;
}

.pos-right {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    box-shadow: var(--shadow-md);
}

/* Call Alert Ticker */
.pos-alert-bar {
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.2) 0%, rgba(245, 158, 11, 0.2) 100%);
    border: 1px solid rgba(239, 68, 68, 0.4);
    border-radius: var(--radius-sm);
    padding: 8px 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    font-size: 0.84rem;
}

.pos-tables-strip {
    display: flex;
    gap: 8px;
    overflow-x: auto;
    padding-bottom: 4px;
    scrollbar-width: thin;
}

.pos-table-pill {
    background: var(--bg-input);
    border: 1px solid var(--border);
    color: var(--text-main);
    padding: 8px 14px;
    border-radius: var(--radius-sm);
    font-weight: 700;
    font-size: 0.85rem;
    cursor: pointer;
    white-space: nowrap;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s ease;
}

.pos-table-pill.active {
    background: var(--primary);
    color: #000;
    border-color: var(--primary);
    box-shadow: 0 4px 12px rgba(197, 160, 89, 0.4);
}

.pos-table-pill.busy {
    border-color: #ef4444;
    background: rgba(239, 68, 68, 0.15);
    color: #fca5a5;
}

.pos-table-pill.busy.active {
    background: #ef4444;
    color: #fff;
    border-color: #ef4444;
}

.pos-cat-strip {
    display: flex;
    gap: 6px;
    overflow-x: auto;
    padding-bottom: 4px;
    scrollbar-width: thin;
}

.pos-cat-btn {
    background: var(--bg-input);
    border: 1px solid var(--border);
    color: var(--text-muted);
    padding: 7px 14px;
    border-radius: var(--radius-full);
    font-size: 0.80rem;
    font-weight: 700;
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.2s ease;
}

.pos-cat-btn.active, .pos-cat-btn:hover {
    background: var(--primary);
    color: #000;
    border-color: var(--primary);
}

.pos-products-scroll {
    flex: 1;
    overflow-y: auto;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(135px, 1fr));
    gap: 10px;
    padding-right: 4px;
}

.pos-prod-card {
    background: var(--bg-input);
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    padding: 10px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    user-select: none;
}

.pos-prod-card:active {
    transform: scale(0.96);
}

.pos-prod-card:hover {
    border-color: var(--primary);
    background: var(--bg-card-hover);
}

.pos-prod-img {
    width: 100%;
    height: 75px;
    object-fit: cover;
    border-radius: var(--radius-xs);
    margin-bottom: 6px;
}

.pos-prod-name {
    font-size: 0.84rem;
    font-weight: 700;
    color: #fff;
    line-height: 1.25;
    margin-bottom: 4px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.pos-prod-price {
    font-size: 0.90rem;
    font-weight: 800;
    color: var(--primary-light);
    margin-top: auto;
}

/* Right Cart Panel */
.pos-cart-header {
    padding: 14px 16px;
    background: rgba(0,0,0,0.25);
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.pos-cart-items {
    flex: 1;
    overflow-y: auto;
    padding: 12px 14px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.pos-item-row {
    background: var(--bg-input);
    border: 1px solid var(--border);
    border-radius: var(--radius-xs);
    padding: 8px 10px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.pos-item-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    font-size: 0.85rem;
    font-weight: 700;
    color: #fff;
}

.pos-item-bottom {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 4px;
}

.pos-qty-btns {
    display: flex;
    align-items: center;
    gap: 4px;
}

.pos-qty-btn {
    width: 26px;
    height: 26px;
    border-radius: 4px;
    background: rgba(255,255,255,0.1);
    border: 1px solid var(--border);
    color: #fff;
    font-weight: 800;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.pos-cart-footer {
    padding: 14px 16px;
    background: rgba(0,0,0,0.25);
    border-top: 1px solid var(--border);
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.pos-note-chips {
    display: flex;
    gap: 4px;
    flex-wrap: wrap;
    margin-top: 6px;
}

.pos-note-chip {
    background: var(--bg-card);
    border: 1px solid var(--border);
    color: var(--text-dim);
    font-size: 0.70rem;
    padding: 3px 8px;
    border-radius: var(--radius-full);
    cursor: pointer;
}

.pos-note-chip:hover {
    color: #fff;
    border-color: var(--primary);
}

@media (max-width: 992px) {
    .pos-container {
        grid-template-columns: 1fr;
        height: auto;
    }
    .pos-products-scroll {
        max-height: 400px;
    }
}
</style>

<!-- CANLI ÇAĞRI UYARI ÇUBUĞU -->
<div class="pos-alert-bar" id="posAlertBar" style="display:none; margin-bottom:12px;">
    <div style="display:flex; align-items:center; gap:8px; font-weight:700; color:#fca5a5;">
        <i class="fas fa-bell" style="animation: pulse 1s infinite; color:#ef4444;"></i>
        <span id="posAlertText">Yeni Garson Çağrısı Var!</span>
    </div>
    <div style="display:flex; gap:6px;">
        <button type="button" class="btn btn-sm btn-success" onclick="dismissTopCall()" style="padding:2px 8px; font-size:0.75rem;">
            <i class="fas fa-check"></i> Yanıtla
        </button>
        <a href="waiter-calls.php" class="btn btn-sm btn-secondary" style="padding:2px 8px; font-size:0.75rem;">
            Tümü
        </a>
    </div>
</div>

<div class="pos-container">

    <!-- SOL TARAF: MASA SEÇİCİ, ARAMA, KATEGORİLER & ÜRÜNLER -->
    <div class="pos-left">
        
        <!-- Üst Arama & Hızlı Masa Girişi -->
        <div style="display: flex; gap: 8px;">
            <div style="position:relative; flex:1;">
                <i class="fas fa-search" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--text-dim);"></i>
                <input type="text" id="posSearch" class="form-control" placeholder="Ürün veya kategori ara (örn: burger, levrek, şarap)..." style="padding-left:36px; height:42px;">
            </div>
            
            <div style="display:flex; gap:6px; width:220px;">
                <input type="text" id="customTableInput" class="form-control" placeholder="Masa/Şezlong No" style="height:42px; font-weight:800; text-align:center;">
                <button type="button" class="btn btn-primary" onclick="setCustomTable()" style="height:42px; padding:0 14px;">
                    Seç
                </button>
            </div>
        </div>

        <!-- Masalar Listesi (Canlı Durumlar) -->
        <div class="pos-tables-strip" id="posTablesStrip">
            <?php foreach ($tables as $idx => $t): ?>
                <div class="pos-table-pill <?php echo $idx === 0 ? 'active' : ''; ?>" data-table="<?php echo htmlspecialchars($t['table_number']); ?>" onclick="selectTable('<?php echo htmlspecialchars($t['table_number']); ?>')">
                    <i class="fas fa-chair"></i>
                    <span>Masa <?php echo htmlspecialchars($t['table_number']); ?></span>
                    <span class="table-badge-mini" style="display:none; font-size:0.65rem; background:rgba(0,0,0,0.4); padding:1px 5px; border-radius:4px;"></span>
                </div>
            <?php endforeach; ?>
            <div class="pos-table-pill" data-table="Şezlong 1" onclick="selectTable('Şezlong 1')">🏖️ Şezlong 1</div>
            <div class="pos-table-pill" data-table="Şezlong 2" onclick="selectTable('Şezlong 2')">🏖️ Şezlong 2</div>
            <div class="pos-table-pill" data-table="Cabana 1" onclick="selectTable('Cabana 1')">🏡 Cabana 1</div>
            <div class="pos-table-pill" data-table="Oda 101" onclick="selectTable('Oda 101')">🛎️ Oda 101</div>
        </div>

        <!-- Kategoriler Şeridi -->
        <div class="pos-cat-strip">
            <button type="button" class="pos-cat-btn active" data-cat="all" onclick="filterCategory('all', this)">Tümü</button>
            <?php foreach ($categories as $cat): ?>
                <button type="button" class="pos-cat-btn" data-cat="<?php echo $cat['id']; ?>" onclick="filterCategory(<?php echo $cat['id']; ?>, this)">
                    <i class="fas fa-<?php echo htmlspecialchars($cat['icon'] ?: 'utensils'); ?>"></i> <?php echo htmlspecialchars($cat['name']); ?>
                </button>
            <?php endforeach; ?>
        </div>

        <!-- Ürünler Dokunmatik Grid -->
        <div class="pos-products-scroll" id="posProductsGrid">
            <?php foreach ($products as $p): 
                $opts = $optionsByProduct[$p['id']] ?? [];
            ?>
                <div class="pos-prod-card" 
                     data-id="<?php echo $p['id']; ?>"
                     data-cat="<?php echo $p['category_id']; ?>"
                     data-name="<?php echo htmlspecialchars($p['name']); ?>"
                     data-price="<?php echo $p['price']; ?>"
                     data-options='<?php echo json_encode($opts, JSON_UNESCAPED_UNICODE); ?>'
                     onclick="handleProductClick(<?php echo htmlspecialchars(json_encode($p)); ?>, <?php echo htmlspecialchars(json_encode($opts)); ?>)">
                    
                    <?php if (!empty($p['image'])): ?>
                        <img src="<?php echo htmlspecialchars($p['image']); ?>" alt="" class="pos-prod-img" loading="lazy" onerror="this.src='../assets/images/maremonte_logo.svg'">
                    <?php endif; ?>

                    <div>
                        <div class="pos-prod-name"><?php echo htmlspecialchars($p['name']); ?></div>
                    </div>

                    <div class="pos-prod-price"><?php echo number_format($p['price'], 2); ?> ₺</div>
                </div>
            <?php endforeach; ?>
        </div>

    </div>

    <!-- SAĞ TARAF: SEÇİLİ MASA ADİSYONU & SİPARİŞ KONTROLÜ -->
    <div class="pos-right">
        
        <div class="pos-cart-header">
            <div>
                <span style="font-size:0.75rem; color:var(--text-dim); text-transform:uppercase; font-weight:800;">Açık Masa</span>
                <div style="font-size:1.15rem; font-weight:800; color:#fff; display:flex; align-items:center; gap:6px;">
                    <i class="fas fa-utensils" style="color:var(--primary);"></i>
                    <span id="activeTableLabel">Masa 1</span>
                </div>
            </div>

            <div style="text-align:right;">
                <span style="font-size:0.75rem; color:var(--text-dim); display:block;">Personel</span>
                <span style="font-size:0.85rem; font-weight:700; color:var(--primary-light);"><?php echo htmlspecialchars($adminName); ?></span>
            </div>
        </div>

        <!-- Adisyon Kalemleri -->
        <div class="pos-cart-items" id="posCartItems">
            <div style="text-align:center; padding:40px 10px; color:var(--text-dim);">
                <i class="fas fa-receipt" style="font-size:2.5rem; margin-bottom:10px; display:block;"></i>
                <p style="font-size:0.85rem;">Bu masaya henüz ürün eklenmedi.<br>Sol menüden ürün seçebilirsiniz.</p>
            </div>
        </div>

        <!-- Alt Toplam & Aksiyon Butonları -->
        <div class="pos-cart-footer">
            
            <div>
                <input type="text" id="posOrderNote" class="form-control form-control-sm" placeholder="Masa sipariş notu (örn: 2. kata servis)..." style="font-size:0.80rem;">
                <div class="pos-note-chips">
                    <span class="pos-note-chip" onclick="addQuickNote('Az Pişmiş')">+ Az Pişmiş</span>
                    <span class="pos-note-chip" onclick="addQuickNote('Orta Pişmiş')">+ Orta Pişmiş</span>
                    <span class="pos-note-chip" onclick="addQuickNote('İyi Pişmiş')">+ İyi Pişmiş</span>
                    <span class="pos-note-chip" onclick="addQuickNote('Tuzsuz')">+ Tuzsuz</span>
                    <span class="pos-note-chip" onclick="addQuickNote('Buzsuz')">+ Buzsuz</span>
                    <span class="pos-note-chip" onclick="addQuickNote('Sosu Ayrı')">+ Sosu Ayrı</span>
                </div>
            </div>

            <div style="display:flex; justify-content:space-between; align-items:baseline; padding-top:6px; border-top:1px solid var(--border);">
                <span style="font-weight:700; color:var(--text-muted); font-size:0.9rem;">Toplam Tutar:</span>
                <strong style="font-size:1.35rem; color:var(--primary); font-weight:800;" id="posTotalEl">0,00 ₺</strong>
            </div>

            <!-- Ana Aksiyon Butonları -->
            <div style="display:flex; flex-direction:column; gap:8px;">
                <button type="button" class="btn btn-primary" id="btnSendToKitchen" onclick="sendOrderToKitchen()" style="width:100%; justify-content:center; padding:12px; font-weight:800; font-size:0.95rem;">
                    <i class="fas fa-paper-plane"></i> Mutfağa Gönder
                </button>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="printTableReceipt()" style="justify-content:center;">
                        <i class="fas fa-print"></i> Adisyon Bas
                    </button>
                    <button type="button" class="btn btn-success btn-sm" onclick="openPaymentModal()" style="justify-content:center;">
                        <i class="fas fa-cash-register"></i> Hesap Kapat
                    </button>
                </div>
            </div>

        </div>

    </div>

</div>

<!-- OPSİYON / VARYASYON SEÇİM MODALI -->
<div class="admin-modal" id="posOptionModal">
    <div class="admin-modal-content" style="max-width:440px;">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title" id="posOptTitle">Ürün Seçenekleri</h3>
            <button type="button" class="admin-modal-close" onclick="closeOptionModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="admin-modal-body" id="posOptBody"></div>
        <div class="admin-modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeOptionModal()">İptal</button>
            <button type="button" class="btn btn-primary" onclick="confirmProductWithOptions()"><i class="fas fa-plus"></i> Adisyona Ekle</button>
        </div>
    </div>
</div>

<!-- HESAP KAPATMA / TAHSİLAT MODALI -->
<div class="admin-modal" id="posPaymentModal">
    <div class="admin-modal-content" style="max-width:420px; text-align:center;">
        <div class="admin-modal-header" style="justify-content:space-between;">
            <h3 class="admin-modal-title"><i class="fas fa-cash-register" style="color:var(--success);"></i> Hesap Tahsilatı</h3>
            <button type="button" class="admin-modal-close" onclick="closePaymentModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="admin-modal-body" style="padding:20px 10px;">
            <div style="font-size:0.85rem; color:var(--text-muted); margin-bottom:4px;">Tahsil Edilecek Tutar</div>
            <div style="font-size:2rem; font-weight:800; color:var(--primary); margin-bottom:18px;" id="payModalAmount">0,00 ₺</div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:14px;">
                <button type="button" class="btn btn-secondary" onclick="executePayment('cash')" style="padding:16px; font-weight:700; justify-content:center; flex-direction:column; gap:6px;">
                    <i class="fas fa-money-bill-wave" style="font-size:1.4rem; color:#34d399;"></i>
                    <span>Nakit Ödeme</span>
                </button>
                <button type="button" class="btn btn-secondary" onclick="executePayment('card')" style="padding:16px; font-weight:700; justify-content:center; flex-direction:column; gap:6px;">
                    <i class="fas fa-credit-card" style="font-size:1.4rem; color:#60a5fa;"></i>
                    <span>Kredi Kartı</span>
                </button>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="executePayment('room')" style="justify-content:center;">
                    <i class="fas fa-bell-concierge"></i> Odaya Yaz
                </button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="executePayment('complimentary')" style="justify-content:center; color:#f43f5e;">
                    <i class="fas fa-gift"></i> İkram / Yetkili
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentTable = '1';
let tableCart = {}; // { '1': [ { id, name, price, quantity, options, item_note } ] }
let activeProductForModal = null;
let waiterName = <?php echo json_encode($adminName); ?>;
let restaurantName = <?php echo json_encode($restaurantName); ?>;
let latestTopCallId = 0;

// Sayfa Yüklendiğinde Masa Durumlarını ve Çağrıları Çek
document.addEventListener('DOMContentLoaded', () => {
    refreshTablesStatus();
    setInterval(refreshTablesStatus, 5000);

    // Arama filtrelemesi
    document.getElementById('posSearch').addEventListener('input', (e) => {
        const q = e.target.value.toLowerCase().trim();
        document.querySelectorAll('.pos-prod-card').forEach(card => {
            const name = (card.dataset.name || '').toLowerCase();
            card.style.display = (!q || name.includes(q)) ? 'flex' : 'none';
        });
    });
});

function selectTable(tableNum) {
    currentTable = tableNum.trim();
    document.getElementById('activeTableLabel').textContent = currentTable.startsWith('Masa') || currentTable.startsWith('Şezlong') || currentTable.startsWith('Cabana') || currentTable.startsWith('Oda') ? currentTable : `Masa ${currentTable}`;
    
    document.querySelectorAll('.pos-table-pill').forEach(pill => {
        if (pill.dataset.table === currentTable) pill.classList.add('active');
        else pill.classList.remove('active');
    });

    renderCart();
    loadExistingTableOrders(currentTable);
}

function setCustomTable() {
    const val = document.getElementById('customTableInput').value.trim();
    if (val) {
        selectTable(val);
        document.getElementById('customTableInput').value = '';
    }
}

function filterCategory(catId, btn) {
    document.querySelectorAll('.pos-cat-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    document.querySelectorAll('.pos-prod-card').forEach(card => {
        if (catId === 'all' || card.dataset.cat == catId) {
            card.style.display = 'flex';
        } else {
            card.style.display = 'none';
        }
    });
}

function handleProductClick(product, options) {
    if (options && options.length > 0) {
        openOptionModal(product, options);
    } else {
        addToCart(product, []);
    }
}

function openOptionModal(product, options) {
    activeProductForModal = { product, options };
    document.getElementById('posOptTitle').textContent = product.name;
    
    const body = document.getElementById('posOptBody');
    body.innerHTML = '';

    const groups = {};
    options.forEach(opt => {
        if (!groups[opt.group_name]) groups[opt.group_name] = [];
        groups[opt.group_name].push(opt);
    });

    for (const [groupName, groupOpts] of Object.entries(groups)) {
        const isRequired = groupOpts.some(o => o.is_required == 1);
        const inputType = isRequired ? 'radio' : 'checkbox';
        const inputName = `pos_opt_${groupName.replace(/\s+/g, '_')}`;

        let groupHtml = `<div style="margin-bottom:12px; background:var(--bg-input); padding:10px; border-radius:var(--radius-xs);">
            <div style="font-weight:700; font-size:0.82rem; margin-bottom:6px; color:#fff;">${groupName} ${isRequired ? '<span style="color:#ef4444;">*</span>' : ''}</div>`;
        
        groupOpts.forEach((opt, idx) => {
            const checked = isRequired && idx === 0 ? 'checked' : '';
            groupHtml += `
                <label style="display:flex; justify-content:space-between; align-items:center; padding:6px 0; font-size:0.85rem; cursor:pointer;">
                    <span><input type="${inputType}" name="${inputName}" value="${opt.id}" data-extra="${opt.extra_price}" data-name="${opt.option_name}" data-group="${groupName}" ${checked}> ${opt.option_name}</span>
                    <span style="font-weight:700; color:var(--primary-light);">${parseFloat(opt.extra_price) > 0 ? `+${parseFloat(opt.extra_price).toFixed(2)} ₺` : 'Ücretsiz'}</span>
                </label>
            `;
        });
        groupHtml += `</div>`;
        body.innerHTML += groupHtml;
    }

    document.getElementById('posOptionModal').classList.add('active');
}

function closeOptionModal() {
    document.getElementById('posOptionModal').classList.remove('active');
    activeProductForModal = null;
}

function confirmProductWithOptions() {
    if (!activeProductForModal) return;
    const selectedOptions = [];
    document.querySelectorAll('#posOptBody input:checked').forEach(input => {
        selectedOptions.push({
            id: input.value,
            group_name: input.dataset.group,
            option_name: input.dataset.name,
            extra_price: parseFloat(input.dataset.extra || 0)
        });
    });

    addToCart(activeProductForModal.product, selectedOptions);
    closeOptionModal();
}

function addToCart(product, selectedOptions) {
    if (!tableCart[currentTable]) tableCart[currentTable] = [];

    const extraTotal = selectedOptions.reduce((sum, o) => sum + o.extra_price, 0);
    const unitPrice = parseFloat(product.price) + extraTotal;

    // Aynı ürün ve aynı opsiyon var mı kontrol et
    const optSignature = JSON.stringify(selectedOptions);
    const existing = tableCart[currentTable].find(it => it.id === product.id && JSON.stringify(it.options) === optSignature);

    if (existing) {
        existing.quantity += 1;
    } else {
        tableCart[currentTable].push({
            id: product.id,
            name: product.name,
            price: unitPrice,
            quantity: 1,
            options: selectedOptions,
            item_note: ''
        });
    }

    renderCart();
}

function changeQty(index, delta) {
    if (!tableCart[currentTable] || !tableCart[currentTable][index]) return;
    tableCart[currentTable][index].quantity += delta;
    if (tableCart[currentTable][index].quantity <= 0) {
        tableCart[currentTable].splice(index, 1);
    }
    renderCart();
}

function setItemNote(index) {
    const item = tableCart[currentTable][index];
    if (!item) return;
    const note = prompt(`"${item.name}" için özel not girin:`, item.item_note || '');
    if (note !== null) {
        item.item_note = note.trim();
        renderCart();
    }
}

function addQuickNote(text) {
    const noteInput = document.getElementById('posOrderNote');
    if (noteInput.value) {
        noteInput.value += `, ${text}`;
    } else {
        noteInput.value = text;
    }
}

function renderCart() {
    const container = document.getElementById('posCartItems');
    const items = tableCart[currentTable] || [];

    if (items.length === 0) {
        container.innerHTML = `
            <div style="text-align:center; padding:40px 10px; color:var(--text-dim);">
                <i class="fas fa-receipt" style="font-size:2.5rem; margin-bottom:10px; display:block;"></i>
                <p style="font-size:0.85rem;">Bu masaya henüz ürün eklenmedi.<br>Sol menüden ürün seçebilirsiniz.</p>
            </div>
        `;
        document.getElementById('posTotalEl').textContent = '0,00 ₺';
        return;
    }

    let total = 0;
    let html = '';

    items.forEach((it, idx) => {
        const itemTotal = it.price * it.quantity;
        total += itemTotal;
        let optText = (it.options || []).map(o => o.option_name).join(', ');

        html += `
            <div class="pos-item-row">
                <div class="pos-item-top">
                    <div>
                        <span>${it.name}</span>
                        ${optText ? `<div style="font-size:0.75rem; color:var(--primary-light); font-weight:normal;">+ ${optText}</div>` : ''}
                        ${it.item_note ? `<div style="font-size:0.75rem; color:#fca5a5; font-weight:normal;"><i class="fas fa-comment"></i> ${it.item_note}</div>` : ''}
                    </div>
                    <span style="color:var(--primary-light);">${itemTotal.toFixed(2)} ₺</span>
                </div>
                <div class="pos-item-bottom">
                    <button type="button" class="btn btn-sm btn-secondary" onclick="setItemNote(${idx})" style="padding:2px 6px; font-size:0.70rem;">
                        <i class="fas fa-pencil"></i> Not
                    </button>
                    <div class="pos-qty-btns">
                        <button type="button" class="pos-qty-btn" onclick="changeQty(${idx}, -1)">-</button>
                        <span style="min-width:20px; text-align:center; font-weight:800; font-size:0.9rem;">${it.quantity}</span>
                        <button type="button" class="pos-qty-btn" onclick="changeQty(${idx}, 1)">+</button>
                    </div>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
    document.getElementById('posTotalEl').textContent = total.toFixed(2).replace('.', ',') + ' ₺';
}

async function sendOrderToKitchen() {
    const items = tableCart[currentTable] || [];
    if (items.length === 0) {
        showAdminToast('Adisyonda gönderilecek ürün bulunmuyor.', 'error');
        return;
    }

    const note = document.getElementById('posOrderNote').value.trim();
    const btn = document.getElementById('btnSendToKitchen');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> İletiliyor...';

    try {
        const formData = new FormData();
        formData.append('action', 'pos_create_order');
        formData.append('table_number', currentTable);
        formData.append('customer_note', note);
        formData.append('waiter_name', waiterName);
        formData.append('items', JSON.stringify(items));

        const res = await fetch('ajax.php', { method: 'POST', body: formData });
        const data = await res.json();

        if (data.success) {
            showAdminToast(`Sipariş #${data.order_id} mutfağa iletildi!`, 'success');
            tableCart[currentTable] = [];
            document.getElementById('posOrderNote').value = '';
            renderCart();
            refreshTablesStatus();
        } else {
            showAdminToast(data.message || 'Sipariş iletilemedi', 'error');
        }
    } catch(e) {
        showAdminToast('Bağlantı hatası', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane"></i> Mutfağa Gönder';
    }
}

async function refreshTablesStatus() {
    try {
        const res = await fetch('ajax.php?action=pos_get_tables_status');
        const data = await res.json();

        if (data.success) {
            const stats = data.table_stats || {};
            
            // Masaların rozetlerini güncelle
            document.querySelectorAll('.pos-table-pill').forEach(pill => {
                const tNum = pill.dataset.table;
                const stat = stats[tNum];
                const badge = pill.querySelector('.table-badge-mini');
                
                if (stat && stat.total_price > 0) {
                    pill.classList.add('busy');
                    if (badge) {
                        badge.style.display = 'inline';
                        badge.textContent = `${stat.total_price.toFixed(0)}₺`;
                    }
                } else {
                    pill.classList.remove('busy');
                    if (badge) badge.style.display = 'none';
                }
            });

            // Bekleyen Çağrı Bildirimi
            const calls = data.pending_calls || [];
            const alertBar = document.getElementById('posAlertBar');
            if (calls.length > 0) {
                const topCall = calls[0];
                latestTopCallId = topCall.id;
                document.getElementById('posAlertText').textContent = `🔔 Masa ${topCall.table_number}: ${topCall.call_type === 'card_bill' ? 'Kartla Hesap İstiyor' : (topCall.call_type === 'valet' ? 'Vale Talebi' : 'Garson Çağırıyor')}`;
                alertBar.style.display = 'flex';
            } else {
                alertBar.style.display = 'none';
            }
        }
    } catch(e) {}
}

async function dismissTopCall() {
    if (!latestTopCallId) return;
    const formData = new FormData();
    formData.append('action', 'update_call_status');
    formData.append('id', latestTopCallId);
    formData.append('status', 'completed');
    await fetch('ajax.php', { method: 'POST', body: formData });
    refreshTablesStatus();
}

async function loadExistingTableOrders(tableNum) {
    try {
        const res = await fetch(`ajax.php?action=pos_get_table_orders&table_number=${encodeURIComponent(tableNum)}`);
        const data = await res.json();
        if (data.success && data.orders && data.orders.length > 0) {
            // Eğer masada mevcut açık siparişler varsa bunu toplam tutara yansıt
        }
    } catch(e) {}
}

function openPaymentModal() {
    const totalText = document.getElementById('posTotalEl').textContent;
    document.getElementById('payModalAmount').textContent = totalText;
    document.getElementById('posPaymentModal').classList.add('active');
}

function closePaymentModal() {
    document.getElementById('posPaymentModal').classList.remove('active');
}

async function executePayment(method) {
    try {
        const formData = new FormData();
        formData.append('action', 'pos_close_table');
        formData.append('table_number', currentTable);
        formData.append('payment_method', method);
        formData.append('waiter_name', waiterName);

        const res = await fetch('ajax.php', { method: 'POST', body: formData });
        const data = await res.json();

        if (data.success) {
            showAdminToast(data.message, 'success');
            tableCart[currentTable] = [];
            renderCart();
            closePaymentModal();
            refreshTablesStatus();
        } else {
            showAdminToast(data.message || 'Hata oluştu', 'error');
        }
    } catch(e) {
        showAdminToast('Bağlantı hatası', 'error');
    }
}

function printTableReceipt() {
    const items = tableCart[currentTable] || [];
    const totalStr = document.getElementById('posTotalEl').textContent;
    
    let itemsHtml = '';
    items.forEach(it => {
        itemsHtml += `
            <tr>
                <td style="padding:4px 0;"><strong>${it.quantity}x</strong> ${it.name}</td>
                <td style="text-align:right;">${(it.price * it.quantity).toFixed(2)} ₺</td>
            </tr>
        `;
    });

    const printWin = window.open('', '_blank');
    printWin.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Ön Adisyon Fişi - ${currentTable}</title>
            <style>
                body { font-family: 'Courier New', monospace; width: 280px; margin: 0 auto; padding: 10px; font-size: 13px; }
                .center { text-align: center; }
                .bold { font-weight: bold; }
                .line { border-top: 1px dashed #000; margin: 8px 0; }
                table { width: 100%; border-collapse: collapse; }
                @media print { body { width: 100%; } }
            </style>
        </head>
        <body onload="window.print()">
            <div class="center bold" style="font-size:16px;">${restaurantName}</div>
            <div class="center">ÖN HESAP / ADİSYON FİŞİ</div>
            <div class="line"></div>
            <div><strong>MASA: ${currentTable}</strong></div>
            <div>Garson: ${waiterName}</div>
            <div>Tarih: ${new Date().toLocaleString('tr-TR')}</div>
            <div class="line"></div>
            <table>
                ${itemsHtml}
            </table>
            <div class="line"></div>
            <div style="font-size:15px; font-weight:bold; display:flex; justify-content:space-between;">
                <span>TOPLAM:</span>
                <span>${totalStr}</span>
            </div>
            <div class="line"></div>
            <div class="center" style="font-size:11px;">Bizi Tercih Ettiğiniz İçin Teşekkür Ederiz!</div>
        </body>
        </html>
    `);
    printWin.document.close();
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
