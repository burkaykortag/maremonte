        </main>
    </div>

    <!-- MOBİL ALT HIZLI GEZİNTİ ÇUBUĞU (APP-BAR) -->
    <nav class="admin-bottom-nav">
        <a href="index.php" class="bottom-nav-item <?php echo ($currentPage ?? '') === 'index.php' ? 'active' : ''; ?>">
            <i class="fas fa-chart-pie"></i>
            <span>Özet</span>
        </a>
        <a href="waiter-calls.php" class="bottom-nav-item <?php echo ($currentPage ?? '') === 'waiter-calls.php' ? 'active' : ''; ?>">
            <i class="fas fa-bell"></i>
            <span>Çağrılar</span>
            <?php if (!empty($pendingCallsCount)): ?>
                <span class="bottom-nav-badge"><?php echo (int)$pendingCallsCount; ?></span>
            <?php endif; ?>
        </a>
        <a href="products.php" class="bottom-nav-item <?php echo ($currentPage ?? '') === 'products.php' ? 'active' : ''; ?>">
            <i class="fas fa-burger"></i>
            <span>Ürünler</span>
        </a>
        <a href="kitchen.php" class="bottom-nav-item <?php echo ($currentPage ?? '') === 'kitchen.php' ? 'active' : ''; ?>">
            <i class="fas fa-kitchen-set"></i>
            <span>Mutfak</span>
            <?php if (!empty($pendingOrdersCount)): ?>
                <span class="bottom-nav-badge" style="background:#3b82f6;"><?php echo (int)$pendingOrdersCount; ?></span>
            <?php endif; ?>
        </a>
        <a href="settings.php" class="bottom-nav-item <?php echo ($currentPage ?? '') === 'settings.php' ? 'active' : ''; ?>">
            <i class="fas fa-sliders"></i>
            <span>Ayarlar</span>
        </a>
    </nav>

    <!-- Admin JS -->
    <script src="../assets/js/admin.js"></script>
</body>
</html>
