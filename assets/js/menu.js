/**
 * QR Menü Müşteri Arayüzü JavaScript Motoru (Tüm Gelişmiş Modüller Dahil)
 */

document.addEventListener('DOMContentLoaded', () => {

    // 1. SEPET DURUM YÖNETİMİ (CART STATE)
    let cart = JSON.parse(sessionStorage.getItem('gusto_cart') || '[]');
    let currentSelectedProduct = null;
    let currentOptions = [];

    // DOM Elementleri
    const searchInput = document.getElementById('menuSearch');
    const searchClear = document.getElementById('searchClear');
    const categoryPills = document.querySelectorAll('.category-pill');
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
    const tableOrdersModal = document.getElementById('tableOrdersModal');
    const popupModal = document.getElementById('popupModal');

    // 2. ARAMA VE GELİŞMİŞ ALERJEN / DİYET FİLTRELERİ
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
                if (filterType === 'chef') matchesBadge = badge.includes('şef') || badge.includes('imza') || badge.includes('chef');
                else if (filterType === 'popular') matchesBadge = badge.includes('popüler') || badge.includes('satan') || badge.includes('popular') || isFeatured;
                else if (filterType === 'discount') matchesBadge = hasDiscount;
                else if (filterType === 'vegan') matchesBadge = badge.includes('vejetaryen') || badge.includes('vegan') || desc.includes('vejetaryen') || desc.includes('vegan');
                else if (filterType === 'gluten_free') matchesBadge = !allergens.includes('gluten');
                else if (filterType === 'low_cal') matchesBadge = calories > 0 && calories <= 450;

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

    // 3. KATEGORİ SCROLL SPY
    categoryPills.forEach(pill => {
        pill.addEventListener('click', (e) => {
            e.preventDefault();
            const targetId = pill.getAttribute('href');
            const targetSection = document.querySelector(targetId);
            if (targetSection) {
                targetSection.scrollIntoView({ behavior: 'smooth' });
                categoryPills.forEach(p => p.classList.remove('active'));
                pill.classList.add('active');
            }
        });
    });

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const id = entry.target.getAttribute('id');
                categoryPills.forEach(pill => {
                    if (pill.getAttribute('href') === `#${id}`) {
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

    // 4. ÜRÜN DETAY MODALI VE OPSİYON SEÇİMİ
    productCards.forEach(card => {
        card.addEventListener('click', () => {
            const rawOptions = card.dataset.options ? JSON.parse(card.dataset.options) : [];
            currentSelectedProduct = {
                id: card.dataset.id,
                name: card.dataset.name,
                desc: card.dataset.desc,
                basePrice: parseFloat(card.dataset.price || '0'),
                priceFormatted: card.dataset.priceFormatted,
                oldPriceFormatted: card.dataset.oldPriceFormatted,
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
        drawerPrice.textContent = p.priceFormatted;

        if (p.oldPriceFormatted) {
            drawerOldPrice.textContent = p.oldPriceFormatted;
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

        if (drawerAllergensBox && drawerAllergensText) {
            if (p.allergens) {
                drawerAllergensText.textContent = p.allergens;
                drawerAllergensBox.style.display = 'block';
            } else {
                drawerAllergensBox.style.display = 'none';
            }
        }

        // Opsiyonları / Ekstraları Çiz
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

        // Grupla
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

                row.innerHTML = `
                    <label for="${optId}">
                        <input type="${inputType}" name="${inputName}" id="${optId}" value="${opt.id}" data-price="${opt.extra_price}" data-name="${opt.option_name}" data-group="${groupName}" ${checked} class="option-input">
                        <span>${opt.option_name}</span>
                    </label>
                    <span style="font-weight:700; color:${parseFloat(opt.extra_price) > 0 ? 'var(--primary-light)' : 'var(--text-dim)'}; font-size:0.8rem;">
                        ${parseFloat(opt.extra_price) > 0 ? `+${parseFloat(opt.extra_price).toFixed(2)} ₺` : 'Ücretsiz'}
                    </span>
                `;
                groupWrap.appendChild(row);
            });

            drawerOptionsContainer.appendChild(groupWrap);
        }

        // Fiyatı yeniden hesapla
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

        drawerPrice.textContent = total.toFixed(2).replace('.', ',') + ' ₺';
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

    // 5. SEPETE EKLEME İŞLEMİ
    if (drawerAddToCartBtn) {
        drawerAddToCartBtn.addEventListener('click', () => {
            if (!currentSelectedProduct) return;

            // Seçili opsiyonları topla
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

            // Sepete ekle
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

    // 6. SEPET ARAYÜZÜ VE FONKSİYONLARI
    function saveCart() {
        sessionStorage.setItem('gusto_cart', JSON.stringify(cart));
    }

    function updateCartUI() {
        const totalItemsCount = cart.reduce((sum, it) => sum + it.quantity, 0);
        const totalPrice = cart.reduce((sum, it) => sum + (it.price * it.quantity), 0);

        if (cartFloatingBtn) {
            cartFloatingBtn.style.display = totalItemsCount > 0 ? 'flex' : 'none';
            if (cartFloatingCount) cartFloatingCount.textContent = totalItemsCount;
            if (cartFloatingTotal) cartFloatingTotal.textContent = totalPrice.toFixed(2).replace('.', ',') + ' ₺';
        }

        if (cartCountBadge) {
            cartCountBadge.textContent = totalItemsCount;
            cartCountBadge.style.display = totalItemsCount > 0 ? 'inline-block' : 'none';
        }

        if (cartTotalEl) cartTotalEl.textContent = totalPrice.toFixed(2).replace('.', ',') + ' ₺';

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
                            ${(it.price * it.quantity).toFixed(2).replace('.', ',')} ₺
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

    // 7. SİPARİŞİ MUTFAĞA GÖNDERME
    if (submitOrderBtn) {
        submitOrderBtn.addEventListener('click', async () => {
            if (cart.length === 0) return;

            const tableNumInput = document.getElementById('orderTableNumber');
            const noteInput = document.getElementById('orderCustomerNote');
            const tableNumber = tableNumInput ? tableNumInput.value.trim() : '';
            const note = noteInput ? noteInput.value.trim() : '';

            if (!tableNumber) {
                showToast('Lütfen masa numaranızı girin.', 'error');
                return;
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

    // 8. INSTAGRAM HİKAYE OYNATICISI (STORY VIEWER)
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

        // 5 saniyelik ilerleme çubuğu
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

    // 9. MÜŞTERİ YORUM & PUANLAMA (FEEDBACK & GOOGLE REVIEWS)
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

                    // 4 veya 5 yıldızsa ve Google Maps linki varsa yönlendirme kartı göster
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

    // 10. DİL DEĞİŞTİRİCİ (MULTI-LANGUAGE SELECTOR)
    window.changeLanguage = function(langCode) {
        const url = new URL(window.location.href);
        url.searchParams.set('lang', langCode);
        window.location.href = url.toString();
    };

    // 11. GARSON ÇAĞIRMA & WI-FI MODAL
    const openWaiterBtn = document.getElementById('openWaiterBtn');
    const closeWaiterBtn = document.getElementById('closeWaiterBtn');
    const sendWaiterCallBtn = document.getElementById('sendWaiterCallBtn');
    const callOptionBtns = document.querySelectorAll('.call-option-btn');
    let selectedCallType = 'waiter';

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
        sendWaiterCallBtn.addEventListener('click', async () => {
            const tableNumberInput = document.getElementById('waiterTableNumber');
            const tableNoteInput = document.getElementById('waiterNote');
            const tableNumber = tableNumberInput ? tableNumberInput.value.trim() : '';
            const note = tableNoteInput ? tableNoteInput.value.trim() : '';

            if (!tableNumber) {
                showToast('Lütfen masa numaranızı girin', 'error');
                return;
            }

            sendWaiterCallBtn.disabled = true;
            sendWaiterCallBtn.textContent = 'İletiliyor...';

            try {
                const formData = new FormData();
                formData.append('action', 'call_waiter');
                formData.append('table_number', tableNumber);
                formData.append('call_type', selectedCallType);
                formData.append('note', note);

                const response = await fetch('api.php', { method: 'POST', body: formData });
                const result = await response.json();

                if (result.success) {
                    showToast(result.message || 'Talebiniz personele iletildi!', 'success');
                    if (waiterModal) waiterModal.classList.remove('active');
                    if (tableNoteInput) tableNoteInput.value = '';
                } else {
                    showToast(result.message || 'Hata oluştu.', 'error');
                }
            } catch (err) {
                showToast('Bağlantı hatası.', 'error');
            } finally {
                sendWaiterCallBtn.disabled = false;
                sendWaiterCallBtn.textContent = 'Çağrıyı Gönder';
            }
        });
    }

    // WI-FI & KOPYALAMA
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

    // AÇILIŞ POP-UP KONTROLÜ
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

    // TOAST MOTORU
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

    // İlk yüklemede sepeti güncelle
    updateCartUI();
});
