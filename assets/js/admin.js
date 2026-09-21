/**
 * QR Menü Yönetim Paneli JavaScript Motoru
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. MOBİL SIDEBAR TOGGLE & BACKDROP
    const mobileToggle = document.getElementById('mobileMenuToggle');
    const sidebar = document.querySelector('.admin-sidebar');
    const sidebarBackdrop = document.getElementById('sidebarBackdrop');
    const sidebarCloseBtn = document.getElementById('sidebarCloseBtn');

    function openSidebar() {
        if (sidebar) sidebar.classList.add('open');
        if (sidebarBackdrop) sidebarBackdrop.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        if (sidebar) sidebar.classList.remove('open');
        if (sidebarBackdrop) sidebarBackdrop.classList.remove('active');
        document.body.style.overflow = '';
    }

    if (mobileToggle) mobileToggle.addEventListener('click', openSidebar);
    if (sidebarCloseBtn) sidebarCloseBtn.addEventListener('click', closeSidebar);
    if (sidebarBackdrop) sidebarBackdrop.addEventListener('click', closeSidebar);

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeSidebar();
            document.querySelectorAll('.admin-modal.active').forEach(m => m.classList.remove('active'));
        }
    });

    // 2. ÜRÜN & KATEGORİ DURUM DEĞİŞTİRİCİLERİ (AJAX SWITCH TOGGLE)
    document.querySelectorAll('.status-toggle').forEach(checkbox => {
        checkbox.addEventListener('change', async function() {
            const id = this.dataset.id;
            const type = this.dataset.type; // 'product' veya 'category'
            const isChecked = this.checked ? 1 : 0;

            try {
                const formData = new FormData();
                formData.append('action', type === 'product' ? 'toggle_product_status' : 'toggle_category_status');
                formData.append('id', id);
                formData.append('status', isChecked);

                const res = await fetch('ajax.php', { method: 'POST', body: formData });
                const data = await res.json();

                if (data.success) {
                    showAdminToast(data.message || 'Durum güncellendi', 'success');
                } else {
                    showAdminToast(data.message || 'Hata oluştu', 'error');
                    this.checked = !isChecked; // Geri al
                }
            } catch (err) {
                showAdminToast('Bağlantı hatası', 'error');
                this.checked = !isChecked;
            }
        });
    });

    // 3. SİLME ONAYI (DELETE HANDLER)
    document.querySelectorAll('.btn-delete-item').forEach(btn => {
        btn.addEventListener('click', async function(e) {
            e.preventDefault();
            const id = this.dataset.id;
            const type = this.dataset.type; // 'product', 'category', 'table'
            const name = this.dataset.name || 'bu öğeyi';

            if (!confirm(`"${name}" adlı kaydı silmek istediğinize emin misiniz?`)) {
                return;
            }

            try {
                const formData = new FormData();
                formData.append('action', `delete_${type}`);
                formData.append('id', id);

                const res = await fetch('ajax.php', { method: 'POST', body: formData });
                const data = await res.json();

                if (data.success) {
                    showAdminToast(data.message || 'Başarıyla silindi', 'success');
                    const row = document.getElementById(`row-${type}-${id}`);
                    if (row) {
                        row.style.opacity = '0';
                        setTimeout(() => row.remove(), 300);
                    }
                    const card = document.getElementById(`card-${type}-${id}`);
                    if (card) {
                        card.style.opacity = '0';
                        setTimeout(() => card.remove(), 300);
                    }
                    if (!row && !card) {
                        setTimeout(() => location.reload(), 500);
                    }
                } else {
                    showAdminToast(data.message || 'Silinemedi', 'error');
                }
            } catch (err) {
                showAdminToast('Bağlantı hatası', 'error');
            }
        });
    });

    // 4. HIZLI FİYAT DÜZENLEYİCİ (QUICK PRICE INLINE EDIT)
    document.querySelectorAll('.quick-price-input').forEach(input => {
        input.addEventListener('change', async function() {
            const id = this.dataset.id;
            const price = parseFloat(this.value.replace(',', '.'));

            if (isNaN(price) || price < 0) {
                showAdminToast('Geçerli bir fiyat girin', 'error');
                return;
            }

            // Diğer eşleşen inputları da güncelle (mobil <-> masaüstü senkronu)
            document.querySelectorAll(`.quick-price-input[data-id="${id}"]`).forEach(el => {
                el.value = price.toFixed(2);
                el.style.borderColor = '#f59e0b';
            });

            try {
                const formData = new FormData();
                formData.append('action', 'quick_price_update');
                formData.append('id', id);
                formData.append('price', price);

                const res = await fetch('ajax.php', { method: 'POST', body: formData });
                const data = await res.json();

                if (data.success) {
                    document.querySelectorAll(`.quick-price-input[data-id="${id}"]`).forEach(el => {
                        el.style.borderColor = '#10b981';
                    });
                    const currPriceEl = document.getElementById(`current-price-${id}`);
                    if (currPriceEl) currPriceEl.textContent = price.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ₺';
                    const currMobilePriceEl = document.getElementById(`mobile-current-price-${id}`);
                    if (currMobilePriceEl) currMobilePriceEl.textContent = price.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ₺';
                    showAdminToast('Fiyat güncellendi! ✓', 'success');
                } else {
                    document.querySelectorAll(`.quick-price-input[data-id="${id}"]`).forEach(el => {
                        el.style.borderColor = '#ef4444';
                    });
                    showAdminToast(data.message || 'Hata oluştu', 'error');
                }
            } catch (err) {
                document.querySelectorAll(`.quick-price-input[data-id="${id}"]`).forEach(el => {
                    el.style.borderColor = '#ef4444';
                });
                showAdminToast('Bağlantı hatası', 'error');
            }
        });
    });

    // 5. GÖRSEL ÖNİZLEME (IMAGE PREVIEWS)
    document.querySelectorAll('.image-upload-input').forEach(input => {
        input.addEventListener('change', function() {
            const previewId = this.dataset.preview;
            const previewEl = document.getElementById(previewId);
            if (previewEl && this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    previewEl.src = e.target.result;
                    previewEl.style.display = 'block';
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    });

    // 6. MODAL AÇ/KAPA YÖNETİCİSİ
    window.openModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) modal.classList.add('active');
    };

    window.closeModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) modal.classList.remove('active');
    };

    document.querySelectorAll('.admin-modal').forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) modal.classList.remove('active');
        });
    });

    // 7. TOAST BİLDİRİMİ
    window.showAdminToast = function(message, type = 'info') {
        let container = document.querySelector('.admin-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'admin-toast-container';
            container.style.position = 'fixed';
            container.style.top = '20px';
            container.style.right = '20px';
            container.style.zIndex = '9999';
            container.style.display = 'flex';
            container.style.flexDirection = 'column';
            container.style.gap = '8px';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.style.padding = '12px 20px';
        toast.style.borderRadius = '8px';
        toast.style.fontSize = '0.85rem';
        toast.style.fontWeight = '600';
        toast.style.color = '#fff';
        toast.style.boxShadow = '0 6px 20px rgba(0,0,0,0.3)';
        toast.style.animation = 'toastIn 0.3s ease forwards';

        if (type === 'success') {
            toast.style.background = '#065f46';
            toast.style.border = '1px solid #10b981';
        } else if (type === 'error') {
            toast.style.background = '#991b1b';
            toast.style.border = '1px solid #ef4444';
        } else {
            toast.style.background = '#1e293b';
            toast.style.border = '1px solid rgba(255,255,255,0.1)';
        }

        toast.textContent = message;
        container.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 3500);
    };
});
