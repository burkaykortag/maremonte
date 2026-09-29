/**
 * QR Menü Müşteri Arayüzü JavaScript Motoru (Tüm Gelişmiş Modüller Dahil)
 * Hotel Mare & Monte Bistro - Est. 1985
 */

document.addEventListener('DOMContentLoaded', () => {

    // 1. DÖVİZ VE KUR AYARLARI
    const eurRate = parseFloat(document.body.dataset.eurRate || '38.50');
    const usdRate = parseFloat(document.body.dataset.usdRate || '35.00');
    const gbpRate = parseFloat(document.body.dataset.gbpRate || '46.00');
    let currentCurrency = sessionStorage.getItem('maremonte_currency') || 'TRY';

    // 2. SEPET DURUM YÖNETİMİ (CART STATE)
    let cart = JSON.parse(sessionStorage.getItem('gusto_cart') || '[]');
    let currentSelectedProduct = null;

    // DOM Elementleri
    const searchInput = document.getElementById('menuSearch');
    const searchClear = document.getElementById('searchClear');
    const categoryPills = document.querySelectorAll('.category-item, .category-pill');
    const filterBadges = document.querySelectorAll('.filter-badge');
    const productCards = document.querySelectorAll('.product-card');
    const categorySections = document.querySelectorAll('.category-section');
    
    // Detay Drawer Elementleri
    const drawerBackdrop = document.getElementById('productDrawer');
    const drawerCloseBtn = document.getElementById('drawerClose');
    const drawerImg = document.getElementById('drawerImg');
    const drawerTitle = document.getElementById('drawerTitle');
    const drawerDesc = document.getElementById('drawerDesc');
    const drawerPrice = document.getElementById('drawerPrice');
    const drawerOldPrice = document.getElementById('drawerOldPrice');
    const drawerCalories = document.getElementById('drawerCalories');
    const drawerPrepTime = document.getElementById('drawerPrepTime');
    const drawerAllergensBox = document.getElementById('drawerAllergensBox');
    const drawerAllergensText = document.getElementById('drawerAllergensText');
    const drawerBadge = document.getElementById('drawerBadge');
    const drawerPairingBox = document.getElementById('drawerPairingBox');
    const drawerPairingText = document.getElementById('drawerPairingText');
    const drawerOptionsContainer = document.getElementById('drawerOptionsContainer');
    const drawerAddToCartBtn = document.getElementById('drawerAddToCartBtn');

    // Sepet Elementleri
    const cartFloatingBtn = document.getElementById('cartFloatingBtn');
    const cartDrawer = document.getElementById('cartDrawer');
    const cartCloseBtn = document.getElementById('cartCloseBtn');
    const cartItemsContainer = document.getElementById('cartItemsContainer');
    const cartTotalEl = document.getElementById('cartTotalEl');
    const cartCountBadge = document.getElementById('cartCountBadge');
    const cartFloatingCount = document.getElementById('cartFloatingCount');
    const cartFloatingTotal = document.getElementById('cartFloatingTotal');
    const submitOrderBtn = document.getElementById('submitOrderBtn');

    // Modallar
    const waiterModal = document.getElementById('waiterModal');
    const wifiModal = document.getElementById('wifiModal');
    const popupModal = document.getElementById('popupModal');
    const wheelModal = document.getElementById('wheelModal');

    // =========================================================================
    // DÖVİZ ÇEVİRİCİ MOTORU (MULTI-CURRENCY CONVERTER)
    // =========================================================================
    function formatCurrencyPrice(amountInTry, curr = currentCurrency) {
        let val = parseFloat(amountInTry || 0);
        if (curr === 'EUR') {
            return (val / eurRate).toFixed(2).replace('.', ',') + ' €';
        } else if (curr === 'USD') {
            return (val / usdRate).toFixed(2).replace('.', ',') + ' $';
        } else if (curr === 'GBP') {
            return (val / gbpRate).toFixed(2).replace('.', ',') + ' £';
        }
        return val.toFixed(2).replace('.', ',') + ' ₺';
    }

    function updateAllMenuPrices() {
        // Ürün Kartları
        document.querySelectorAll('.product-price').forEach(el => {
            const raw = el.dataset.rawPrice;
            if (raw) el.textContent = formatCurrencyPrice(raw);
        });

        document.querySelectorAll('.product-old-price').forEach(el => {
            const raw = el.dataset.rawOldPrice;
            if (raw) el.textContent = formatCurrencyPrice(raw);
        });

        // Çekmece açıksa güncelle
        if (currentSelectedProduct) {
            calculateDrawerTotalPrice();
        }

        // Sepet toplamlarını güncelle
        updateCartUI();
    }

    const currencyBtns = document.querySelectorAll('.currency-btn');
    currencyBtns.forEach(btn => {
        if (btn.dataset.currency === currentCurrency) {
            currencyBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        }

        btn.addEventListener('click', () => {
            currencyBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            currentCurrency = btn.dataset.currency || 'TRY';
            sessionStorage.setItem('maremonte_currency', currentCurrency);
            updateAllMenuPrices();
            showToast(`Para birimi ${currentCurrency} olarak güncellendi`, 'info');
        });
    });

    // =========================================================================
    // TEMA YÖNETİCİSİ (LIGHT / DARK THEME SWITCHER)
    // =========================================================================
    const themeToggleBtn = document.getElementById('themeToggleBtn');
    const themeToggleIcon = document.getElementById('themeToggleIcon');

    function applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('maremonte_theme', theme);
        if (themeToggleIcon) {
            if (theme === 'dark') {
                themeToggleIcon.className = 'fas fa-sun';
            } else {
                themeToggleIcon.className = 'fas fa-moon';
            }
        }
    }

    // Başlangıç temasını uygula (localStorage > html attribute)
    const savedTheme = localStorage.getItem('maremonte_theme') || document.documentElement.getAttribute('data-theme') || 'light';
    applyTheme(savedTheme);

    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', () => {
            const current = document.documentElement.getAttribute('data-theme') || 'light';
            const nextTheme = current === 'dark' ? 'light' : 'dark';
            applyTheme(nextTheme);
            showToast(nextTheme === 'dark' ? 'Koyu Tema Aktif 🌙' : 'Açık Tema Aktif ☀️', 'info');
        });
    }

    // =========================================================================
    // SUNSET HAPPY HOUR SAAT KONTROLÜ
    // =========================================================================
    const happyHourBanner = document.getElementById('happyHourBanner');
    if (happyHourBanner) {
        const startStr = happyHourBanner.dataset.start || '17:00';
        const endStr = happyHourBanner.dataset.end || '19:30';

        function checkHappyHour() {
            const now = new Date();
            const currentMinutes = now.getHours() * 60 + now.getMinutes();

            const [startH, startM] = startStr.split(':').map(Number);
            const [endH, endM] = endStr.split(':').map(Number);
            const startMinutes = startH * 60 + startM;
            const endMinutes = endH * 60 + endM;

            if (currentMinutes >= startMinutes && currentMinutes <= endMinutes) {
                happyHourBanner.style.display = 'block';
            } else {
                happyHourBanner.style.display = 'none';
            }
        }
        checkHappyHour();
        setInterval(checkHappyHour, 60000);
    }

    // =========================================================================
    // ARAMA VE GELİŞMİŞ ALERJEN / DİYET FİLTRELERİ
    // =========================================================================
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase().trim();
            if (searchClear) searchClear.style.display = query.length > 0 ? 'block' : 'none';
            filterMenu(query, getActiveFilterBadge());
        });

        if (searchClear) {
            searchClear.addEventListener('click', () => {
                searchInput.value = '';
                searchClear.style.display = 'none';
                filterMenu('', getActiveFilterBadge());
            });
        }
    }

    filterBadges.forEach(badge => {
        badge.addEventListener('click', () => {
            filterBadges.forEach(b => b.classList.remove('active'));
            badge.classList.add('active');
            const filterType = badge.dataset.filter || 'all';
            const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
            filterMenu(query, filterType);
        });
    });

    function getActiveFilterBadge() {
        const active = document.querySelector('.filter-badge.active');
        return active ? (active.dataset.filter || 'all') : 'all';
    }

    function filterMenu(query, filterType) {
        let visibleCount = 0;

        if ((query || filterType !== 'all') && menuContainer && menuContainer.style.display === 'none') {
            if (categoryLandingView) categoryLandingView.style.display = 'none';
            if (categoriesBar) categoriesBar.style.display = 'flex';
            if (menuContainer) menuContainer.style.display = 'block';
            if (typeof updateCatSlideButtons === 'function') updateCatSlideButtons();
        }

        categorySections.forEach(section => {
            let sectionHasVisible = false;
            const cards = section.querySelectorAll('.product-card');

            cards.forEach(card => {
                const name = card.dataset.name ? card.dataset.name.toLowerCase() : '';
                const desc = card.dataset.desc ? card.dataset.desc.toLowerCase() : '';
                const badge = card.dataset.badge ? card.dataset.badge.toLowerCase() : '';
                const allergens = card.dataset.allergens ? card.dataset.allergens.toLowerCase() : '';
                const calories = parseInt(card.dataset.calories || '0');
                const isFeatured = card.dataset.featured === '1';
                const hasDiscount = card.dataset.discount === '1';

                const matchesQuery = !query || name.includes(query) || desc.includes(query);
                
                let matchesBadge = true;
                if (filterType === 'chef') {
                    matchesBadge = badge.includes('şef') || badge.includes('imza') || badge.includes('chef') || isFeatured;
                } else if (filterType === 'popular') {
                    matchesBadge = badge.includes('popüler') || badge.includes('satan') || badge.includes('popular') || isFeatured;
                } else if (filterType === 'discount') {
                    matchesBadge = hasDiscount;
                } else if (filterType === 'vegan') {
                    matchesBadge = badge.includes('vejetaryen') || badge.includes('vegan') || desc.includes('vejetaryen') || desc.includes('vegan');
                } else if (filterType === 'gluten_free') {
                    matchesBadge = !allergens.includes('gluten');
                } else if (filterType === 'lactose_free') {
                    matchesBadge = !allergens.includes('laktoz') && !allergens.includes('lactose');
                } else if (filterType === 'low_cal') {
                    matchesBadge = calories > 0 && calories <= 450;
                }

                if (matchesQuery && matchesBadge) {
                    card.style.display = 'flex';
                    sectionHasVisible = true;
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            section.style.display = sectionHasVisible ? 'block' : 'none';
        });

        const noResultsEl = document.getElementById('noResultsMessage');
        if (noResultsEl) {
            noResultsEl.style.display = visibleCount === 0 ? 'block' : 'none';
        }
    }

    // =========================================================================
    // 2'Lİ KATEGORİ IZGARASI & ÜST SLIDER GEZİNTİ MOTORU
    // =========================================================================
    const categoryLandingView = document.getElementById('categoryLandingView');
    const categoriesBar = document.getElementById('categoriesBar');
    const menuContainer = document.getElementById('menuContainer');
    const backToGridBtn = document.getElementById('backToGridBtn');
    const landingCards = document.querySelectorAll('.landing-category-card');
    const categoriesCarousel = document.getElementById('categoriesCarousel');
    const catSlidePrev = document.getElementById('catSlidePrev');
    const catSlideNext = document.getElementById('catSlideNext');
    const allCategoryItems = document.querySelectorAll('.category-item');

    function updateCatSlideButtons() {
        if (!categoriesCarousel || !catSlidePrev || !catSlideNext) return;
        const maxScroll = categoriesCarousel.scrollWidth - categoriesCarousel.clientWidth;
        catSlidePrev.disabled = categoriesCarousel.scrollLeft <= 4;
        catSlideNext.disabled = categoriesCarousel.scrollLeft >= maxScroll - 4;
    }

    if (categoriesCarousel) {
        categoriesCarousel.addEventListener('scroll', updateCatSlideButtons, { passive: true });
        window.addEventListener('resize', updateCatSlideButtons);
        setTimeout(updateCatSlideButtons, 300);
    }

    if (catSlidePrev && categoriesCarousel) {
        catSlidePrev.addEventListener('click', () => {
            const scrollDist = (categoriesCarousel.clientWidth || 240) * 0.75;
            categoriesCarousel.scrollBy({ left: -scrollDist, behavior: 'smooth' });
        });
    }

    if (catSlideNext && categoriesCarousel) {
        catSlideNext.addEventListener('click', () => {
            const scrollDist = (categoriesCarousel.clientWidth || 240) * 0.75;
            categoriesCarousel.scrollBy({ left: scrollDist, behavior: 'smooth' });
        });
    }

    // Ürün Görünümüne Geç (Kategori Seçildiğinde)
    function openCategoryProducts(catId, smoothScroll = true) {
        if (categoryLandingView) categoryLandingView.style.display = 'none';
        if (categoriesBar) categoriesBar.style.display = 'flex';
        if (menuContainer) menuContainer.style.display = 'block';

        // Slider'da aktif kategoriyi işaretle
        allCategoryItems.forEach(item => {
            if (item.dataset.catId == catId || item.getAttribute('href') === `#cat-${catId}`) {
                item.classList.add('active');
                item.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
            } else {
                item.classList.remove('active');
            }
        });

        updateCatSlideButtons();

        // Sayfayı seçilen kategoriye kaydır
        if (catId) {
            const targetSection = document.getElementById(`cat-${catId}`);
            if (targetSection) {
                if (smoothScroll) {
                    setTimeout(() => {
                        targetSection.scrollIntoView({ behavior: 'smooth' });
                    }, 50);
                } else {
                    targetSection.scrollIntoView();
                }
            }
        }
    }

    // Ana 2'li Kategori Izgarasına Geri Dön
    function openLandingGrid() {
        if (menuContainer) menuContainer.style.display = 'none';
        if (categoriesBar) categoriesBar.style.display = 'none';
        if (categoryLandingView) categoryLandingView.style.display = 'block';

        if (window.location.hash) {
            history.pushState('', document.title, window.location.pathname + window.location.search);
        }

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // 2'li Izgara Kartlarına Tıklama
    landingCards.forEach(card => {
        card.addEventListener('click', (e) => {
            e.preventDefault();
            const catId = card.dataset.catId;
            openCategoryProducts(catId, true);
        });
    });

    // Geri Butonuna Tıklama
    if (backToGridBtn) {
        backToGridBtn.addEventListener('click', (e) => {
            e.preventDefault();
            openLandingGrid();
        });
    }

    // Üst Kategori Barındaki Butonlara Tıklama
    allCategoryItems.forEach(item => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            const targetId = item.getAttribute('href');
            const targetSection = document.querySelector(targetId);
            if (targetSection) {
                targetSection.scrollIntoView({ behavior: 'smooth' });
                allCategoryItems.forEach(p => p.classList.remove('active'));
                item.classList.add('active');
                item.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
            }
        });
    });

    // Arama Yapıldığında veya Filtre Seçildiğinde Otomatik Ürünler Görünümüne Geç
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            if (searchInput.value.trim().length > 0 && menuContainer && menuContainer.style.display === 'none') {
                if (categoryLandingView) categoryLandingView.style.display = 'none';
                if (categoriesBar) categoriesBar.style.display = 'flex';
                if (menuContainer) menuContainer.style.display = 'block';
                updateCatSlideButtons();
            }
        });
    }

    filterBadges.forEach(btn => {
        btn.addEventListener('click', () => {
            if (btn.dataset.filter !== 'all' && menuContainer && menuContainer.style.display === 'none') {
                if (categoryLandingView) categoryLandingView.style.display = 'none';
                if (categoriesBar) categoriesBar.style.display = 'flex';
                if (menuContainer) menuContainer.style.display = 'block';
                updateCatSlideButtons();
            }
        });
    });

    // IntersectionObserver (Scroll Spy)
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && menuContainer && menuContainer.style.display !== 'none') {
                const id = entry.target.getAttribute('id');
                allCategoryItems.forEach(pill => {
                    if (pill.getAttribute('href') === `#${id}` || pill.dataset.catId === id.replace('cat-', '')) {
                        pill.classList.add('active');
                        pill.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
                    } else {
                        pill.classList.remove('active');
                    }
                });
            }
        });
    }, { rootMargin: '-130px 0px -70% 0px' });

    categorySections.forEach(sec => observer.observe(sec));

    // Sayfa Açılışında URL Hash Kontrolü
    if (window.location.hash && window.location.hash.startsWith('#cat-')) {
        const hashCatId = window.location.hash.replace('#cat-', '');
        openCategoryProducts(hashCatId, false);
    }

    // =========================================================================
    // ŞEFİN AKILLI EŞLEŞTİRME MOTORU ("BİRLİKTE İYİ GİDER")
    // =========================================================================
    function getSmartPairing(product) {
        const name = (product.name || '').toLowerCase();
        const desc = (product.desc || '').toLowerCase();
        const category = (product.category || '').toLowerCase();

        if (name.includes('balık') || name.includes('levrek') || name.includes('çipura') || name.includes('karides') || name.includes('kalamar') || category.includes('deniz')) {
            return '🥂 Şefin Önerisi: Soğuk Ege Beyaz Şarabı veya Taze Sıkılmış Limonata ile kusursuz uyum sağlar.';
        } else if (name.includes('bonfile') || name.includes('köfte') || name.includes('steak') || name.includes('et') || name.includes('makarna') || name.includes('spaghetti') || name.includes('fettuccine')) {
            return '🍷 Şefin Önerisi: Kazdağları Meşe Fıçı Cabernet Sauvignon veya Ege Roze ile eşsiz bir deneyim.';
        } else if (name.includes('burger') || name.includes('pizza') || name.includes('tost') || name.includes('nugget') || name.includes('sosis')) {
            return '🍸 Şefin Önerisi: Mare İmza Sunset Kokteyli veya Buz Gibi Soğuk Draft Bira ile mükemmel ikili.';
        } else if (name.includes('tatlı') || name.includes('sufle') || name.includes('cheesecake') || name.includes('tiramisu') || name.includes('pasta') || category.includes('tatli')) {
            return '☕ Şefin Önerisi: Damla Sakızlı Türk Kahvesi veya Taze Çekilmiş Double Espresso ile deneyiniz.';
        } else if (name.includes('salata') || name.includes('bowl')) {
            return '🍹 Şefin Önerisi: Detox Yeşil Elmalı & Naneli Buzlu Cooler ile ferahlayın.';
        } else if (name.includes('kokteyl') || name.includes('şarap') || name.includes('bira')) {
            return '🧀 Şefin Önerisi: Gurme Yerli Peynir Tabağı veya Çıtır Bira Tabağı ile taçlandırın.';
        }
        return '🍷 Şefin Önerisi: Özel soğuk içeceklerimiz ve şarap menümüz ile lezzeti tamamlayın.';
    }

    // =========================================================================
    // ÜRÜN DETAY MODALI VE OPSİYON SEÇİMİ
    // =========================================================================
    productCards.forEach(card => {
        card.addEventListener('click', () => {
            const rawOptions = card.dataset.options ? JSON.parse(card.dataset.options) : [];
            currentSelectedProduct = {
                id: card.dataset.id,
                name: card.dataset.name,
                desc: card.dataset.desc,
                category: card.dataset.category || '',
                pairing: card.dataset.pairing || '',
                basePrice: parseFloat(card.dataset.price || '0'),
                oldPrice: card.dataset.oldPrice ? parseFloat(card.dataset.oldPrice) : null,
                image: card.dataset.image,
                calories: card.dataset.calories,
                prepTime: card.dataset.prepTime,
                allergens: card.dataset.allergens,
                badge: card.dataset.badge,
                isAvailable: card.dataset.available === '1',
                options: rawOptions
            };

            openProductDrawer(currentSelectedProduct);
        });
    });

    function openProductDrawer(p) {
        if (!drawerBackdrop) return;

        drawerImg.src = p.image || 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=800&q=80';
        drawerTitle.textContent = p.name;
        drawerDesc.textContent = p.desc || '';
        drawerPrice.textContent = formatCurrencyPrice(p.basePrice);

        if (p.oldPrice && p.oldPrice > p.basePrice) {
            drawerOldPrice.textContent = formatCurrencyPrice(p.oldPrice);
            drawerOldPrice.style.display = 'inline';
        } else {
            drawerOldPrice.style.display = 'none';
        }

        if (drawerCalories) drawerCalories.textContent = p.calories ? `${p.calories} kcal` : 'Standart Porsiyon';
        if (drawerPrepTime) drawerPrepTime.textContent = p.prepTime ? `~${p.prepTime} dk` : '15 dk';

        if (drawerBadge) {
            if (p.badge) {
                drawerBadge.textContent = p.badge;
                drawerBadge.style.display = 'inline-block';
            } else {
                drawerBadge.style.display = 'none';
            }
        }

        // Şefin Eşleştirme Önerisi
        if (drawerPairingBox && drawerPairingText) {
            const pairingEnabled = document.body.dataset.pairingsEnabled === '1';
            if (pairingEnabled) {
                const suggestion = p.pairing ? p.pairing : getSmartPairing(p);
                if (suggestion) {
                    drawerPairingText.textContent = suggestion;
                    drawerPairingBox.style.display = 'block';
                } else {
                    drawerPairingBox.style.display = 'none';
                }
            } else {
                drawerPairingBox.style.display = 'none';
            }
        }

        // Alerjen Kutusu
        if (drawerAllergensBox && drawerAllergensText) {
            if (p.allergens) {
                drawerAllergensText.textContent = p.allergens;
                drawerAllergensBox.style.display = 'block';
            } else {
                drawerAllergensBox.style.display = 'none';
            }
        }

        // Opsiyonları Çiz
        renderProductOptions(p.options, p.basePrice);

        drawerBackdrop.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function renderProductOptions(options, basePrice) {
        if (!drawerOptionsContainer) return;
        drawerOptionsContainer.innerHTML = '';

        if (!options || options.length === 0) {
            drawerOptionsContainer.style.display = 'none';
            return;
        }

        drawerOptionsContainer.style.display = 'block';

        const groups = {};
        options.forEach(opt => {
            if (!groups[opt.group_name]) groups[opt.group_name] = [];
            groups[opt.group_name].push(opt);
        });

        for (const [groupName, groupOpts] of Object.entries(groups)) {
            const groupWrap = document.createElement('div');
            groupWrap.className = 'drawer-options-group';

            const isRequired = groupOpts.some(o => o.is_required == 1);
            const inputType = isRequired ? 'radio' : 'checkbox';
            const inputName = `opt_group_${groupName.replace(/\s+/g, '_')}`;

            groupWrap.innerHTML = `
                <div class="options-group-title">
                    <span>${groupName}</span>
                    ${isRequired ? '<span style="color:#ef4444; font-size:0.72rem;">* Zorunlu Seçim</span>' : '<span style="color:var(--text-dim); font-size:0.72rem;">İsteğe Bağlı</span>'}
                </div>
            `;

            groupOpts.forEach((opt, idx) => {
                const row = document.createElement('div');
                row.className = 'option-row';
                const optId = `opt_${opt.id}`;
                const checked = isRequired && idx === 0 ? 'checked' : '';
                const extraP = parseFloat(opt.extra_price || 0);

                row.innerHTML = `
                    <label for="${optId}">
                        <input type="${inputType}" name="${inputName}" id="${optId}" value="${opt.id}" data-price="${extraP}" data-name="${opt.option_name}" data-group="${groupName}" ${checked} class="option-input">
                        <span>${opt.option_name}</span>
                    </label>
                    <span style="font-weight:700; color:${extraP > 0 ? 'var(--primary-light)' : 'var(--text-dim)'}; font-size:0.8rem;">
                        ${extraP > 0 ? `+${formatCurrencyPrice(extraP)}` : 'Ücretsiz'}
                    </span>
                `;
                groupWrap.appendChild(row);
            });

            drawerOptionsContainer.appendChild(groupWrap);
        }

        document.querySelectorAll('.option-input').forEach(input => {
            input.addEventListener('change', calculateDrawerTotalPrice);
        });

        calculateDrawerTotalPrice();
    }

    function calculateDrawerTotalPrice() {
        if (!currentSelectedProduct) return;
        let total = currentSelectedProduct.basePrice;

        document.querySelectorAll('.option-input:checked').forEach(checkedOpt => {
            total += parseFloat(checkedOpt.dataset.price || '0');
        });

        drawerPrice.textContent = formatCurrencyPrice(total);
    }

    function closeProductDrawer() {
        if (drawerBackdrop) {
            drawerBackdrop.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    if (drawerCloseBtn) drawerCloseBtn.addEventListener('click', closeProductDrawer);
    if (drawerBackdrop) {
        drawerBackdrop.addEventListener('click', (e) => {
            if (e.target === drawerBackdrop) closeProductDrawer();
        });
    }

    // =========================================================================
    // SEPETE EKLEME & SEPET YÖNETİMİ
    // =========================================================================
    if (drawerAddToCartBtn) {
        drawerAddToCartBtn.addEventListener('click', () => {
            if (!currentSelectedProduct) return;

            const selectedOptions = [];
            let totalItemPrice = currentSelectedProduct.basePrice;

            document.querySelectorAll('.option-input:checked').forEach(opt => {
                const extraPrice = parseFloat(opt.dataset.price || '0');
                totalItemPrice += extraPrice;
                selectedOptions.push({
                    id: opt.value,
                    group_name: opt.dataset.group,
                    option_name: opt.dataset.name,
                    extra_price: extraPrice
                });
            });

            const cartItem = {
                cart_item_id: Date.now() + Math.random().toString(36).substring(2, 5),
                id: currentSelectedProduct.id,
                name: currentSelectedProduct.name,
                image: currentSelectedProduct.image,
                basePrice: currentSelectedProduct.basePrice,
                price: totalItemPrice,
                quantity: 1,
                options: selectedOptions
            };

            cart.push(cartItem);
            saveCart();
            updateCartUI();
            closeProductDrawer();
            showToast(`"${currentSelectedProduct.name}" sepete eklendi! 🛒`, 'success');
        });
    }

    function saveCart() {
        sessionStorage.setItem('gusto_cart', JSON.stringify(cart));
    }

    function updateCartUI() {
        const totalItemsCount = cart.reduce((sum, it) => sum + it.quantity, 0);
        const totalPrice = cart.reduce((sum, it) => sum + (it.price * it.quantity), 0);

        if (cartFloatingBtn) {
            cartFloatingBtn.style.display = totalItemsCount > 0 ? 'flex' : 'none';
            if (cartFloatingCount) cartFloatingCount.textContent = totalItemsCount;
            if (cartFloatingTotal) cartFloatingTotal.textContent = formatCurrencyPrice(totalPrice);
        }

        if (cartCountBadge) {
            cartCountBadge.textContent = totalItemsCount;
            cartCountBadge.style.display = totalItemsCount > 0 ? 'inline-block' : 'none';
        }

        if (cartTotalEl) cartTotalEl.textContent = formatCurrencyPrice(totalPrice);

        renderCartItems();
    }

    function renderCartItems() {
        if (!cartItemsContainer) return;

        if (cart.length === 0) {
            cartItemsContainer.innerHTML = `
                <div style="text-align:center; padding:40px 10px; color:var(--text-dim);">
                    <i class="fas fa-basket-shopping" style="font-size:3rem; margin-bottom:12px; display:block;"></i>
                    <p style="font-size:0.9rem;">Sepetinizde henüz ürün bulunmuyor.</p>
                </div>
            `;
            if (submitOrderBtn) submitOrderBtn.disabled = true;
            return;
        }

        if (submitOrderBtn) submitOrderBtn.disabled = false;

        let html = '';
        cart.forEach((it, index) => {
            let optsText = '';
            if (it.options && it.options.length > 0) {
                optsText = it.options.map(o => `${o.group_name}: ${o.option_name}`).join(', ');
            }

            html += `
                <div class="cart-item-row">
                    <div class="cart-item-info">
                        <div class="cart-item-name">${it.name}</div>
                        ${optsText ? `<div class="cart-item-opts">${optsText}</div>` : ''}
                        <div style="font-weight:800; color:var(--primary-light); font-size:0.9rem; margin-top:4px;">
                            ${formatCurrencyPrice(it.price * it.quantity)}
                        </div>
                    </div>
                    <div class="cart-qty-ctrl">
                        <button type="button" class="qty-btn" onclick="changeCartQty(${index}, -1)">-</button>
                        <span style="font-weight:800; font-size:0.95rem; min-width:18px; text-align:center;">${it.quantity}</span>
                        <button type="button" class="qty-btn" onclick="changeCartQty(${index}, 1)">+</button>
                    </div>
                </div>
            `;
        });

        cartItemsContainer.innerHTML = html;
    }

    window.changeCartQty = function(index, delta) {
        if (!cart[index]) return;
        cart[index].quantity += delta;
        if (cart[index].quantity <= 0) {
            cart.splice(index, 1);
        }
        saveCart();
        updateCartUI();
    };

    if (cartFloatingBtn) {
        cartFloatingBtn.addEventListener('click', () => {
            if (cartDrawer) cartDrawer.classList.add('active');
        });
    }

    if (cartCloseBtn && cartDrawer) {
        cartCloseBtn.addEventListener('click', () => {
            cartDrawer.classList.remove('active');
        });
    }

    // =========================================================================
    // RESORT & KONUM SEÇİMİ (ŞEZLONG, CABANA, ODA, MASA)
    // =========================================================================
    let currentAreaType = 'Masa';
    const waiterAreaChips = document.querySelectorAll('#waiterAreaChips .resort-chip');
    const cartAreaChips = document.querySelectorAll('#cartAreaChips .resort-chip');

    function setupAreaChips(chips) {
        chips.forEach(chip => {
            chip.addEventListener('click', () => {
                chips.forEach(c => c.classList.remove('active'));
                chip.classList.add('active');
                currentAreaType = chip.dataset.area || 'Masa';
                
                // Diğer chip grubunu senkronize et
                document.querySelectorAll('.resort-chip').forEach(c => {
                    if (c.dataset.area === currentAreaType) c.classList.add('active');
                    else c.classList.remove('active');
                });

                // Input placeholder'ını güncelle
                const wInput = document.getElementById('waiterTableNumber');
                const oInput = document.getElementById('orderTableNumber');
                const label = document.getElementById('waiterLocationLabel');
                if (label) label.textContent = `${currentAreaType} Numarası`;
                if (wInput && !wInput.value) wInput.placeholder = `${currentAreaType} No (Örn: 4, 12, B2...)`;
                if (oInput && !oInput.value) oInput.placeholder = `${currentAreaType} No (Örn: 4, 12, B2...)`;
            });
        });
    }

    setupAreaChips(waiterAreaChips);
    setupAreaChips(cartAreaChips);

    // =========================================================================
    // SİPARİŞİ MUTFAĞA GÖNDERME
    // =========================================================================
    if (submitOrderBtn) {
        submitOrderBtn.addEventListener('click', async () => {
            if (cart.length === 0) return;

            const tableNumInput = document.getElementById('orderTableNumber');
            const noteInput = document.getElementById('orderCustomerNote');
            let tableNumber = tableNumInput ? tableNumInput.value.trim() : '';
            const note = noteInput ? noteInput.value.trim() : '';

            if (!tableNumber) {
                showToast('Lütfen masa / konum numaranızı girin.', 'error');
                return;
            }

            // Eğer başında alan tipi yoksa ekle (Örn: Şezlong 14)
            if (currentAreaType !== 'Masa' && !tableNumber.toLowerCase().includes(currentAreaType.toLowerCase())) {
                tableNumber = `${currentAreaType} ${tableNumber}`;
            }

            submitOrderBtn.disabled = true;
            submitOrderBtn.textContent = 'Sipariş İletiliyor...';

            try {
                const formData = new FormData();
                formData.append('action', 'place_order');
                formData.append('table_number', tableNumber);
                formData.append('customer_note', note);
                formData.append('items', JSON.stringify(cart));

                const res = await fetch('api.php', { method: 'POST', body: formData });
                const data = await res.json();

                if (data.success) {
                    showToast(data.message || 'Siparişiniz mutfağa iletildi! Afiyet olsun.', 'success');
                    cart = [];
                    saveCart();
                    updateCartUI();
                    if (cartDrawer) cartDrawer.classList.remove('active');
                    if (noteInput) noteInput.value = '';
                } else {
                    showToast(data.message || 'Sipariş gönderilemedi.', 'error');
                }
            } catch(e) {
                showToast('Bağlantı hatası.', 'error');
            } finally {
                submitOrderBtn.disabled = false;
                submitOrderBtn.textContent = 'Siparişi Onayla & Gönder';
            }
        });
    }

    // =========================================================================
    // =========================================================================
    // GARSON ÇAĞIRMA, MASA YÖNETİMİ & GERÇEK ZAMANLI BİLDİRİM
    // =========================================================================
    const openWaiterBtn = document.getElementById('openWaiterBtn');
    const closeWaiterBtn = document.getElementById('closeWaiterBtn');
    const drawerCallWaiterBtn = document.getElementById('drawerCallWaiterBtn');
    const quickCallWaiterBtn = document.getElementById('quickCallWaiterBtn');
    const quickCallBillBtn = document.getElementById('quickCallBillBtn');
    const quickCallWaiterText = document.getElementById('quickCallWaiterText');
    const quickCallBillText = document.getElementById('quickCallBillText');
    const sendWaiterCallBtn = document.getElementById('sendWaiterCallBtn');
    const callOptionBtns = document.querySelectorAll('.call-option-btn');

    // Masa Seçim Modalı Elementleri
    const tableSelectModal = document.getElementById('tableSelectModal');
    const closeTableSelectBtn = document.getElementById('closeTableSelectBtn');
    const confirmTableSelectBtn = document.getElementById('confirmTableSelectBtn');
    const quickTableInput = document.getElementById('quickTableInput');
    const headerTableBtn = document.getElementById('headerTableBtn');
    const headerTableText = document.getElementById('headerTableText');
    const tableModalTitle = document.getElementById('tableModalTitle');
    const tableModalDesc = document.getElementById('tableModalDesc');
    const confirmTableBtnText = document.getElementById('confirmTableBtnText');
    const skipTableSelectBtn = document.getElementById('skipTableSelectBtn');
    const quickTblBtns = document.querySelectorAll('.quick-tbl-btn');

    let selectedCallType = 'waiter';
    let pendingCallAction = null; // 'waiter' | 'bill' | null

    // Masa Numarası Formatlayıcı
    function normalizeTableInput(raw) {
        if (!raw) return '';
        let s = String(raw).trim();
        // Sadece sayı girildiyse (örn: "4" veya "12") -> "Masa 4"
        if (/^\d+$/.test(s)) {
            return `Masa ${s}`;
        }
        return s;
    }

    function formatShortTableLabel(tbl) {
        if (!tbl) return '';
        let s = String(tbl).trim();
        if (/^Masa\s+(\d+)$/i.test(s)) {
            return `M.${s.replace(/^Masa\s+/i, '')}`;
        }
        return s;
    }

    // Aktif Masayı Oku
    function getActiveTableNumber() {
        const urlParams = new URLSearchParams(window.location.search);
        let t = urlParams.get('table') || urlParams.get('masa') || urlParams.get('t');
        if (t && t.trim()) {
            const norm = normalizeTableInput(t);
            localStorage.setItem('maremonte_table', norm);
            return norm;
        }
        const saved = localStorage.getItem('maremonte_table');
        if (saved && saved.trim()) return saved.trim();
        return '';
    }

    // Aktif Masayı Kaydet ve Arayüzü Güncelle
    function setActiveTableNumber(tbl) {
        const norm = normalizeTableInput(tbl);
        if (norm) {
            localStorage.setItem('maremonte_table', norm);
        } else {
            localStorage.removeItem('maremonte_table');
        }
        updateTableUI();
        updateCallCooldownUI();
    }

    // Üst Header ve Modal İçi Senkron
    function updateTableUI() {
        const currentTable = getActiveTableNumber();
        if (headerTableText) {
            headerTableText.textContent = currentTable ? currentTable : 'Masa Seçin';
        }
        if (quickTableInput && currentTable) {
            quickTableInput.value = currentTable;
        }
        const waiterInput = document.getElementById('waiterTableNumber');
        if (waiterInput && currentTable) {
            waiterInput.value = currentTable;
        }

        // Hızlı buton seçimini vurgula
        quickTblBtns.forEach(btn => {
            const btnTbl = btn.dataset.tbl;
            if (currentTable && (btnTbl === currentTable || `Masa ${btn.textContent.trim()}` === currentTable)) {
                btn.classList.add('selected');
            } else {
                btn.classList.remove('selected');
            }
        });
    }

    // Cooldown (Geri Sayım & Spam Engeli) Yönetimi
    function getCallCooldownRemaining() {
        const until = parseInt(localStorage.getItem('maremonte_call_cooldown_until') || '0', 10);
        const rem = Math.ceil((until - Date.now()) / 1000);
        return rem > 0 ? rem : 0;
    }

    function startCallCooldown(seconds = 60) {
        localStorage.setItem('maremonte_call_cooldown_until', String(Date.now() + seconds * 1000));
        updateCallCooldownUI();
    }

    function updateCallCooldownUI() {
        const rem = getCallCooldownRemaining();
        const currentTable = getActiveTableNumber();
        const shortTable = currentTable ? formatShortTableLabel(currentTable) : '';

        if (rem > 0) {
            if (quickCallWaiterBtn) {
                quickCallWaiterBtn.disabled = true;
                quickCallWaiterBtn.classList.add('cooldown');
            }
            if (quickCallWaiterText) {
                quickCallWaiterText.textContent = shortTable ? `${shortTable} (${rem}s)` : `Garson (${rem}s)`;
            }

            if (quickCallBillBtn) {
                quickCallBillBtn.disabled = true;
                quickCallBillBtn.classList.add('cooldown');
            }
            if (quickCallBillText) {
                quickCallBillText.textContent = shortTable ? `${shortTable} (${rem}s)` : `Hesap (${rem}s)`;
            }

            if (drawerCallWaiterBtn) {
                drawerCallWaiterBtn.disabled = true;
                drawerCallWaiterBtn.classList.add('cooldown');
                drawerCallWaiterBtn.innerHTML = `<i class="fas fa-hourglass-half"></i> ${shortTable ? shortTable + ' ' : ''}Çağrıldı (${rem}s)`;
            }
        } else {
            if (quickCallWaiterBtn) {
                quickCallWaiterBtn.disabled = false;
                quickCallWaiterBtn.classList.remove('cooldown');
            }
            if (quickCallWaiterText) {
                quickCallWaiterText.textContent = currentTable ? `Garson (${shortTable})` : 'Garson (Masa No)';
            }

            if (quickCallBillBtn) {
                quickCallBillBtn.disabled = false;
                quickCallBillBtn.classList.remove('cooldown');
            }
            if (quickCallBillText) {
                quickCallBillText.textContent = currentTable ? `Hesap (${shortTable})` : 'Hesap (Masa No)';
            }

            if (drawerCallWaiterBtn) {
                drawerCallWaiterBtn.disabled = false;
                drawerCallWaiterBtn.classList.remove('cooldown');
                drawerCallWaiterBtn.innerHTML = `<i class="fas fa-bell"></i> Garson Çağır ${currentTable ? '(' + shortTable + ')' : ''}`;
            }
        }
    }

    setInterval(updateCallCooldownUI, 1000);
    updateTableUI();
    updateCallCooldownUI();

    // Masa Seçim Modalı Açma / Kapama
    function openTableSelectModal(action = null) {
        pendingCallAction = action;
        const currentTable = getActiveTableNumber();

        if (quickTableInput) {
            quickTableInput.value = currentTable || '';
        }

        if (action === 'waiter') {
            if (tableModalTitle) tableModalTitle.textContent = '🛎️ Garson Çağır';
            if (tableModalDesc) tableModalDesc.textContent = "Lütfen QR'ın yanında yazan masa numaranızı seçin";
            if (confirmTableBtnText) confirmTableBtnText.textContent = currentTable ? `${currentTable} İçin Garson Çağır` : 'Masa No Onayla & Garson Çağır';
        } else if (action === 'bill') {
            if (tableModalTitle) tableModalTitle.textContent = '💳 Hesap İste';
            if (tableModalDesc) tableModalDesc.textContent = "Lütfen QR'ın yanında yazan masa numaranızı seçin";
            if (confirmTableBtnText) confirmTableBtnText.textContent = currentTable ? `${currentTable} İçin Hesap İste` : 'Masa No Onayla & Hesap İste';
        } else {
            if (tableModalTitle) tableModalTitle.textContent = '🌟 Hoş Geldiniz';
            if (tableModalDesc) tableModalDesc.textContent = "Lütfen QR'ın yanında yazan masa numaranızı seçin";
            if (confirmTableBtnText) confirmTableBtnText.textContent = currentTable ? `${currentTable} Olarak Onayla & Menüyü Gör` : 'Masa Numarasını Onayla & Menüyü Gör';
        }

        updateTableUI();
        if (tableSelectModal) {
            tableSelectModal.classList.add('active');
            setTimeout(() => {
                if (quickTableInput) {
                    quickTableInput.focus();
                    quickTableInput.select();
                }
            }, 250);
        }
    }

    function closeTableSelectModal() {
        if (tableSelectModal) tableSelectModal.classList.remove('active');
        pendingCallAction = null;
    }

    if (headerTableBtn) {
        headerTableBtn.addEventListener('click', () => openTableSelectModal(null));
    }
    if (closeTableSelectBtn) {
        closeTableSelectBtn.addEventListener('click', closeTableSelectModal);
    }
    if (skipTableSelectBtn) {
        skipTableSelectBtn.addEventListener('click', () => {
            sessionStorage.setItem('maremonte_table_prompted', 'true');
            closeTableSelectModal();
        });
    }

    // QR Okutulduğunda Otomatik Açılan Masa Seçim Pop-up'ı (İlk Açılış Kontrolü)
    const urlParams = new URLSearchParams(window.location.search);
    const hasUrlTable = Boolean(urlParams.get('table') || urlParams.get('masa') || urlParams.get('t'));
    const isAlreadyPromptedThisSession = sessionStorage.getItem('maremonte_table_prompted') === 'true';

    if (!hasUrlTable && !isAlreadyPromptedThisSession) {
        setTimeout(() => {
            openTableSelectModal(null);
        }, 300);
    }

    // Hızlı Masa Butonlarına Tıklama
    quickTblBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const tbl = btn.dataset.tbl;
            if (quickTableInput) quickTableInput.value = tbl;
            quickTblBtns.forEach(b => b.classList.remove('selected'));
            btn.classList.add('selected');

            if (confirmTableBtnText) {
                if (pendingCallAction === 'waiter') {
                    confirmTableBtnText.textContent = `${tbl} İçin Garson Çağır`;
                } else if (pendingCallAction === 'bill') {
                    confirmTableBtnText.textContent = `${tbl} İçin Hesap İste`;
                } else {
                    confirmTableBtnText.textContent = `${tbl} Olarak Onayla & Menüyü Gör`;
                }
            }
        });
    });

    if (quickTableInput) {
        quickTableInput.addEventListener('input', () => {
            const val = quickTableInput.value.trim();
            const norm = normalizeTableInput(val);
            if (confirmTableBtnText) {
                if (pendingCallAction === 'waiter') {
                    confirmTableBtnText.textContent = norm ? `${norm} İçin Garson Çağır` : 'Garson Çağır';
                } else if (pendingCallAction === 'bill') {
                    confirmTableBtnText.textContent = norm ? `${norm} İçin Hesap İste` : 'Hesap İste';
                } else {
                    confirmTableBtnText.textContent = norm ? `${norm} Olarak Onayla & Menüyü Gör` : 'Masa Numarasını Onayla & Menüyü Gör';
                }
            }
        });

        quickTableInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                confirmTableSelectBtn?.click();
            }
        });
    }

    // Modal Onay Butonu
    if (confirmTableSelectBtn) {
        confirmTableSelectBtn.addEventListener('click', () => {
            const rawVal = quickTableInput ? quickTableInput.value.trim() : '';
            if (!rawVal) {
                showToast('Lütfen masa numaranızı belirtin (Örn: 4)', 'error');
                if (quickTableInput) quickTableInput.focus();
                return;
            }

            const norm = normalizeTableInput(rawVal);
            setActiveTableNumber(norm);
            sessionStorage.setItem('maremonte_table_prompted', 'true');

            const actionToDispatch = pendingCallAction;
            closeTableSelectModal();

            if (actionToDispatch) {
                dispatchServiceCall(actionToDispatch);
            } else {
                showToast(`Masanız "${norm}" olarak ayarlandı. Keyifli lezzetler dileriz!`, 'success');
            }
        });
    }

    // Merkezi Servis Çağrısı Gönderme Fonksiyonu (Next.js Port 3000 + Fallback)
    async function dispatchServiceCall(type = 'waiter', note = '') {
        const rem = getCallCooldownRemaining();
        if (rem > 0) {
            showToast(`Talebiniz zaten iletildi. Lütfen bekleyiniz (${rem} sn)`, 'info');
            return;
        }

        let tableNumber = getActiveTableNumber();
        if (!tableNumber) {
            // Masa bilinmiyorsa hemen şık masa modalını aç
            openTableSelectModal(type);
            return;
        }

        // UI Buton Geçici Durumu
        if (quickCallWaiterBtn) quickCallWaiterBtn.disabled = true;
        if (quickCallBillBtn) quickCallBillBtn.disabled = true;
        if (drawerCallWaiterBtn) drawerCallWaiterBtn.disabled = true;
        if (sendWaiterCallBtn) {
            sendWaiterCallBtn.disabled = true;
            sendWaiterCallBtn.textContent = 'İletiliyor...';
        }

        const normalizedType = (type === 'bill' || type === 'card_bill' || type === 'cash_bill') ? 'bill' : 'waiter';

        let callSucceeded = false;
        let responseMessage = '';
        let cooldownDuration = 60;
        let returnedTable = tableNumber;

        const isLocalHost = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';

        // 1. Eğer yerel geliştirme ortamındaysak doğrudan Next.js Port 3000'i dene
        if (isLocalHost) {
            try {
                const hotelRes = await fetch('http://localhost:3000/api/public/call-waiter', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        tableNumber: tableNumber,
                        type: normalizedType,
                        note: note || undefined
                    })
                });

                if (hotelRes.ok) {
                    const hotelData = await hotelRes.json();
                    if (hotelData.success) {
                        callSucceeded = true;
                        cooldownDuration = hotelData.remainingCooldown || 60;
                        returnedTable = hotelData.tableName || tableNumber;
                        responseMessage = normalizedType === 'bill'
                            ? `${returnedTable} için hesap talebiniz iletildi.`
                            : `${returnedTable} için garson çağrınız iletildi. Personel masanıza geliyor.`;
                    }
                }
            } catch (hotelErr) {
                console.warn('Otel Port 3000 direkt çağrı denenemedi, menü api.php iletilecek.', hotelErr);
            }
        }

        // 2. Telegram Bildirimi ve Menü DB Kaydı için api.php'yi de bilgilendir
        try {
            const formData = new FormData();
            formData.append('action', 'call_waiter');
            formData.append('table_number', tableNumber);
            formData.append('call_type', type);
            formData.append('note', note);

            const phpRes = await fetch('api.php', { method: 'POST', body: formData });
            const phpData = await phpRes.json();
            if (phpData.success && !callSucceeded) {
                callSucceeded = true;
                responseMessage = phpData.message || `${tableNumber} talebi iletildi!`;
            }
        } catch (phpErr) {
            console.error('Menü api.php çağrı hatası:', phpErr);
        }

        // Sonuç Değerlendirmesi
        if (callSucceeded) {
            showToast(responseMessage || `${tableNumber} talebiniz iletildi.`, 'success');
            startCallCooldown(cooldownDuration);
            if (waiterModal) waiterModal.classList.remove('active');
            const noteInput = document.getElementById('waiterNote');
            if (noteInput) noteInput.value = '';
        } else {
            showToast('Çağrı iletilemedi. Lütfen tekrar deneyin.', 'error');
            updateCallCooldownUI();
        }

        if (sendWaiterCallBtn) {
            sendWaiterCallBtn.disabled = false;
            sendWaiterCallBtn.textContent = 'Çağrıyı Gönder';
        }
    }

    // Buton Dinleyicileri
    if (quickCallWaiterBtn) {
        quickCallWaiterBtn.addEventListener('click', () => {
            const t = getActiveTableNumber();
            if (!t) {
                openTableSelectModal('waiter');
            } else {
                dispatchServiceCall('waiter');
            }
        });
    }

    if (quickCallBillBtn) {
        quickCallBillBtn.addEventListener('click', () => {
            const t = getActiveTableNumber();
            if (!t) {
                openTableSelectModal('bill');
            } else {
                dispatchServiceCall('bill');
            }
        });
    }

    if (drawerCallWaiterBtn) {
        drawerCallWaiterBtn.addEventListener('click', () => {
            if (drawerBackdrop) drawerBackdrop.classList.remove('active');
            document.body.style.overflow = '';
            const prodName = currentSelectedProduct?.name ? `(${currentSelectedProduct.name} siparişi)` : '';
            const t = getActiveTableNumber();
            if (!t) {
                openTableSelectModal('waiter');
            } else {
                dispatchServiceCall('waiter', prodName);
            }
        });
    }

    // Eski Garson Modalı Açma Butonu ve Seçenekleri (varsa)
    if (openWaiterBtn && waiterModal) openWaiterBtn.addEventListener('click', () => waiterModal.classList.add('active'));
    if (closeWaiterBtn && waiterModal) closeWaiterBtn.addEventListener('click', () => waiterModal.classList.remove('active'));

    callOptionBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            callOptionBtns.forEach(b => b.classList.remove('selected'));
            btn.classList.add('selected');
            selectedCallType = btn.dataset.type || 'waiter';
        });
    });

    if (sendWaiterCallBtn) {
        sendWaiterCallBtn.addEventListener('click', () => {
            const tableNumberInput = document.getElementById('waiterTableNumber');
            const tableNoteInput = document.getElementById('waiterNote');
            const note = tableNoteInput ? tableNoteInput.value.trim() : '';
            if (tableNumberInput && tableNumberInput.value.trim()) {
                setActiveTableNumber(tableNumberInput.value.trim());
            }
            dispatchServiceCall(selectedCallType, note);
        });
    }

    // =========================================================================
    // ŞANS ÇARKI / GAMIFICATION ENGINE (HTML5 CANVAS)
    // =========================================================================
    const openWheelBtn = document.getElementById('openWheelBtn');
    const closeWheelBtn = document.getElementById('closeWheelBtn');
    const spinWheelBtn = document.getElementById('spinWheelBtn');
    const wheelCanvas = document.getElementById('luckyWheelCanvas');
    const wheelActiveView = document.getElementById('wheelActiveView');
    const wheelRewardView = document.getElementById('wheelRewardView');
    const rewardBoxText = document.getElementById('rewardBoxText');
    const rewardCodeText = document.getElementById('rewardCodeText');

    if (openWheelBtn && wheelModal && wheelCanvas) {
        const rewardsRaw = document.body.dataset.wheelRewards || 'Günün Tatlısı İkramı,%10 İndirim,Türk Kahvesi İkramı,Şefin Özel Kokteyli,%15 İndirim,Teşekkürler';
        const segments = rewardsRaw.split(',').map(s => s.trim()).filter(Boolean);
        const segmentCount = segments.length;
        const ctx = wheelCanvas.getContext('2d');
        const colors = ['#C5A059', '#161F30', '#10B981', '#1E293B', '#D97706', '#0F172A'];

        let startAngle = 0;
        const arc = Math.PI / (segmentCount / 2);
        let spinTimeout = null;
        let spinAngleStart = 10;
        let spinTime = 0;
        let spinTimeTotal = 0;

        function drawRouletteWheel() {
            if (!wheelCanvas.getContext) return;
            const outsideRadius = 130;
            const textRadius = 90;
            const insideRadius = 30;

            ctx.clearRect(0, 0, 280, 280);

            for (let i = 0; i < segmentCount; i++) {
                const angle = startAngle + i * arc;
                ctx.fillStyle = colors[i % colors.length];

                ctx.beginPath();
                ctx.arc(140, 140, outsideRadius, angle, angle + arc, false);
                ctx.arc(140, 140, insideRadius, angle + arc, angle, true);
                ctx.fill();
                ctx.strokeStyle = 'rgba(255,255,255,0.3)';
                ctx.lineWidth = 2;
                ctx.stroke();

                ctx.save();
                ctx.fillStyle = '#FFFFFF';
                ctx.font = 'bold 11px Plus Jakarta Sans, sans-serif';
                ctx.translate(
                    140 + Math.cos(angle + arc / 2) * textRadius,
                    140 + Math.sin(angle + arc / 2) * textRadius
                );
                ctx.rotate(angle + arc / 2 + Math.PI / 2);
                const text = segments[i];
                ctx.fillText(text.length > 14 ? text.substring(0, 12) + '..' : text, -ctx.measureText(text).width / 2, 0);
                ctx.restore();
            }

            // Merkez Düğme
            ctx.beginPath();
            ctx.arc(140, 140, 28, 0, Math.PI * 2);
            ctx.fillStyle = '#C5A059';
            ctx.fill();
            ctx.strokeStyle = '#FFFFFF';
            ctx.lineWidth = 3;
            ctx.stroke();
        }

        drawRouletteWheel();

        openWheelBtn.addEventListener('click', () => {
            const today = new Date().toDateString();
            const lastSpinDate = localStorage.getItem('maremonte_wheel_spin_date');
            const savedReward = localStorage.getItem('maremonte_wheel_reward');
            const savedCode = localStorage.getItem('maremonte_wheel_code');

            if (lastSpinDate === today && savedReward && savedCode) {
                // Bugün zaten çevrilmiş, ödülü göster
                rewardBoxText.textContent = savedReward;
                rewardCodeText.textContent = savedCode;
                wheelActiveView.style.display = 'none';
                wheelRewardView.style.display = 'block';
            } else {
                wheelActiveView.style.display = 'block';
                wheelRewardView.style.display = 'none';
            }
            wheelModal.classList.add('active');
        });

        if (closeWheelBtn) closeWheelBtn.addEventListener('click', () => wheelModal.classList.remove('active'));

        function rotateWheel() {
            spinTime += 30;
            if (spinTime >= spinTimeTotal) {
                stopRotateWheel();
                return;
            }
            const spinAngle = spinAngleStart - easeOut(spinTime, 0, spinAngleStart, spinTimeTotal);
            startAngle += (spinAngle * Math.PI / 180);
            drawRouletteWheel();
            spinTimeout = setTimeout(rotateWheel, 30);
        }

        function stopRotateWheel() {
            clearTimeout(spinTimeout);
            const degrees = startAngle * 180 / Math.PI + 90;
            const arcd = arc * 180 / Math.PI;
            const index = Math.floor((360 - degrees % 360) / arcd) % segmentCount;
            const wonReward = segments[index] || 'Günün Tatlısı İkramı';

            const today = new Date().toDateString();
            const voucherCode = 'MM-' + Math.floor(1000 + Math.random() * 9000);

            localStorage.setItem('maremonte_wheel_spin_date', today);
            localStorage.setItem('maremonte_wheel_reward', wonReward);
            localStorage.setItem('maremonte_wheel_code', voucherCode);

            rewardBoxText.textContent = wonReward;
            rewardCodeText.textContent = voucherCode;

            setTimeout(() => {
                wheelActiveView.style.display = 'none';
                wheelRewardView.style.display = 'block';
                showToast(`Tebrikler! ${wonReward} kazandınız! 🎉`, 'success');
            }, 600);
        }

        function easeOut(t, b, c, d) {
            const ts = (t /= d) * t;
            const tc = ts * t;
            return b + c * (tc + -3 * ts + 3 * t);
        }

        spinWheelBtn.addEventListener('click', () => {
            spinAngleStart = Math.random() * 10 + 15;
            spinTime = 0;
            spinTimeTotal = Math.random() * 2000 + 3500;
            spinWheelBtn.disabled = true;
            rotateWheel();
        });
    }

    // =========================================================================
    // INSTAGRAM HİKAYE OYNATICISI (STORY VIEWER)
    // =========================================================================
    const storyItems = document.querySelectorAll('.story-item');
    const storyModal = document.getElementById('storyViewerModal');
    const storyViewerImg = document.getElementById('storyViewerImg');
    const storyViewerTitle = document.getElementById('storyViewerTitle');
    const storyViewerLink = document.getElementById('storyViewerLink');
    const storyProgressFill = document.getElementById('storyProgressFill');
    const storyViewerClose = document.getElementById('storyViewerClose');

    let storyTimer = null;
    let storyProgressInterval = null;

    storyItems.forEach(item => {
        item.addEventListener('click', () => {
            const title = item.dataset.title;
            const img = item.dataset.image;
            const link = item.dataset.link;
            openStoryViewer(title, img, link);
        });
    });

    function openStoryViewer(title, img, link) {
        if (!storyModal) return;
        storyViewerTitle.textContent = title;
        storyViewerImg.src = img;
        if (storyViewerLink) {
            if (link) {
                storyViewerLink.href = link;
                storyViewerLink.style.display = 'inline-flex';
            } else {
                storyViewerLink.style.display = 'none';
            }
        }

        storyModal.classList.add('active');
        document.body.style.overflow = 'hidden';

        let progress = 0;
        if (storyProgressFill) storyProgressFill.style.width = '0%';
        clearInterval(storyProgressInterval);
        clearTimeout(storyTimer);

        storyProgressInterval = setInterval(() => {
            progress += 2;
            if (storyProgressFill) storyProgressFill.style.width = progress + '%';
            if (progress >= 100) {
                clearInterval(storyProgressInterval);
                closeStoryViewer();
            }
        }, 100);
    }

    function closeStoryViewer() {
        if (!storyModal) return;
        storyModal.classList.remove('active');
        document.body.style.overflow = '';
        clearInterval(storyProgressInterval);
        clearTimeout(storyTimer);
    }

    if (storyViewerClose) storyViewerClose.addEventListener('click', closeStoryViewer);
    if (storyModal) {
        storyModal.addEventListener('click', (e) => {
            if (e.target === storyModal) closeStoryViewer();
        });
    }

    // =========================================================================
    // MÜŞTERİ YORUM & PUANLAMA (FEEDBACK & GOOGLE REVIEWS)
    // =========================================================================
    const starBtns = document.querySelectorAll('.star-btn');
    const sendFeedbackBtn = document.getElementById('sendFeedbackBtn');
    let selectedRating = 5;

    starBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            selectedRating = parseInt(btn.dataset.star || '5');
            starBtns.forEach(b => {
                const starVal = parseInt(b.dataset.star || '0');
                if (starVal <= selectedRating) {
                    b.classList.add('active');
                    b.innerHTML = '<i class="fas fa-star"></i>';
                } else {
                    b.classList.remove('active');
                    b.innerHTML = '<i class="far fa-star"></i>';
                }
            });
        });
    });

    if (sendFeedbackBtn) {
        sendFeedbackBtn.addEventListener('click', async () => {
            const nameInput = document.getElementById('feedbackName');
            const commentInput = document.getElementById('feedbackComment');
            const tableNumInput = document.getElementById('feedbackTableNumber');
            const name = nameInput ? nameInput.value.trim() : '';
            const comment = commentInput ? commentInput.value.trim() : '';
            const tableNum = tableNumInput ? tableNumInput.value.trim() : '';

            sendFeedbackBtn.disabled = true;
            sendFeedbackBtn.textContent = 'Gönderiliyor...';

            try {
                const formData = new FormData();
                formData.append('action', 'send_feedback');
                formData.append('rating', selectedRating);
                formData.append('name', name);
                formData.append('comment', comment);
                formData.append('table_number', tableNum);

                const res = await fetch('api.php', { method: 'POST', body: formData });
                const data = await res.json();

                if (data.success) {
                    showToast(data.message || 'Değerlendirmeniz için teşekkür ederiz!', 'success');
                    if (commentInput) commentInput.value = '';

                    const googleCtaBox = document.getElementById('googleReviewCtaBox');
                    if (selectedRating >= 4 && googleCtaBox) {
                        googleCtaBox.style.display = 'block';
                    }
                }
            } catch(e) {
                showToast('Hata oluştu.', 'error');
            } finally {
                sendFeedbackBtn.disabled = false;
                sendFeedbackBtn.textContent = 'Değerlendirmeyi Gönder';
            }
        });
    }

    // =========================================================================
    // DİL DEĞİŞTİRİCİ (MULTI-LANGUAGE SELECTOR)
    // =========================================================================
    window.changeLanguage = function(langCode) {
        const url = new URL(window.location.href);
        url.searchParams.set('lang', langCode);
        window.location.href = url.toString();
    };

    const langBtn = document.getElementById('langSelectorBtn');
    const langDd = document.getElementById('langDropdown');
    if (langBtn && langDd) {
        langBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            langDd.style.display = langDd.style.display === 'none' || !langDd.style.display ? 'block' : 'none';
        });
        document.addEventListener('click', (e) => {
            if (!e.target.closest('#langSelectorBtn') && !e.target.closest('#langDropdown')) {
                langDd.style.display = 'none';
            }
        });
    }

    // =========================================================================
    // WI-FI & KOPYALAMA
    // =========================================================================
    const openWifiBtn = document.getElementById('openWifiBtn');
    const closeWifiBtn = document.getElementById('closeWifiBtn');
    const copyWifiBtn = document.getElementById('copyWifiBtn');

    if (openWifiBtn && wifiModal) openWifiBtn.addEventListener('click', () => wifiModal.classList.add('active'));
    if (closeWifiBtn && wifiModal) closeWifiBtn.addEventListener('click', () => wifiModal.classList.remove('active'));
    if (copyWifiBtn) {
        copyWifiBtn.addEventListener('click', () => {
            const passEl = document.getElementById('wifiPasswordText');
            if (passEl) {
                navigator.clipboard.writeText(passEl.textContent.trim()).then(() => {
                    showToast('Wi-Fi şifresi kopyalandı! 📋', 'success');
                });
            }
        });
    }

    // =========================================================================
    // AÇILIŞ POP-UP KONTROLÜ
    // =========================================================================
    if (popupModal && !sessionStorage.getItem('gusto_seen_popup')) {
        setTimeout(() => {
            popupModal.classList.add('active');
            sessionStorage.setItem('gusto_seen_popup', 'true');
        }, 1200);
    }

    const closePopupBtn = document.getElementById('closePopupBtn');
    if (closePopupBtn && popupModal) {
        closePopupBtn.addEventListener('click', () => popupModal.classList.remove('active'));
    }

    // =========================================================================
    // TOAST BİLDİRİM MOTORU
    // =========================================================================
    window.showToast = function(message, type = 'info') {
        let container = document.querySelector('.toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.textContent = message;
        container.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(-20px)';
            setTimeout(() => toast.remove(), 300);
        }, 3500);
    };

    // İlk yüklemede sepeti ve fiyatları güncelle
    updateCartUI();
    if (currentCurrency !== 'TRY') {
        updateAllMenuPrices();
    }
});
