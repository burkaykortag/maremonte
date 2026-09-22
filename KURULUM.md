# 🚀 Hotel Mare & Monte Bistro - Canlı Sunucu Kurulum Kılavuzu

Bu belge, projenin canlı web sunucunuza (cPanel, Plesk, DirectAdmin, CyberPanel, VPS vb.) sorunsuz ve hızlı bir şekilde kurulması için hazırlanmıştır.

---

## 📋 Canlı Veritabanı Bilgileri

| Parametre | Değer |
|---|---|
| **Veritabanı Türü** | MySQL / MariaDB (veya SQLite) |
| **Veritabanı Sunucusu (Host)** | `localhost` |
| **Veritabanı Adı (DB Name)** | `Maremonte` (veya `cpanelkullanici_Maremonte`) |
| **Veritabanı Kullanıcısı (DB User)** | `Maremonte` (veya `cpanelkullanici_Maremonte`) |
| **Veritabanı Şifresi (DB Password)** | `Maremonte1122334455..` |
| **Karakter Seti (Charset)** | `utf8mb4` |

---

## 🛠️ Kurulum Seçenekleri (2 Kolay Yöntem)

### Yöntem 1: ⚡ 1-Tıkla Otomatik Web Kurulum Sihirbazı (En Kolay)

1. `hotel-mare-monte-menu.zip` dosyasını sunucunuzun ana dizinine (`public_html` veya `httpdocs`) yükleyip **Extract (Arşivden Çıkar)** yapın.
2. Hosting kontrol panelinizden (cPanel / Plesk):
   - `Maremonte` adında bir MySQL veritabanı oluşturun.
   - `Maremonte` adında bir kullanıcı oluşturup şifresini `Maremonte1122334455..` yapın.
   - Kullanıcıyı veritabanına ekleyin ve **"Tüm Yetkileri (ALL PRIVILEGES)"** verin.
3. Tarayıcınızdan **`https://siteniz.com/install.php`** adresini açın.
4. Bilgileri onaylayıp **"Kurulumu Başlat & Veritabanını Yükle"** butonuna basın.
5. Sistem saniyeler içinde tüm tabloları oluşturup 14 kategori, 84 ürün, etkinlikler ve ayarları yükleyecektir!

---

### Yöntem 2: 📦 phpMyAdmin ile Manuel İçe Aktarma (SQL Import)

1. Dosyaları sunucuya yükleyin.
2. Hosting panelinizden **phpMyAdmin**'e girin.
3. `Maremonte` veritabanını seçin.
4. Üst menüden **İçe Aktar (Import)** sekmesine tıklayın.
5. Proje içindeki **`database.sql`** dosyasını seçip **Git (Go)** butonuna basın.
6. `config.php` dosyasını açıp bilgileri kontrol edin:
   ```php
   define('DB_DRIVER', 'mysql');
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'Maremonte');
   define('DB_USER', 'Maremonte');
   define('DB_PASS', 'Maremonte1122334455..');
   ```

---

## 🔑 Varsayılan Giriş & Erişim Bilgileri

- 🌐 **Müşteri QR Menüsü:** `https://siteniz.com/`
- 🔐 **Yönetici Kontrol Paneli:** `https://siteniz.com/admin/`
  - **Kullanıcı Adı:** `admin`
  - **Şifre:** `admin123` (Yönetim panelinden değiştirilebilir)
- 📟 **Garson Sipariş Terminali (POS):** `https://siteniz.com/admin/pos.php`
- 🍳 **Mutfak & Bar KDS Ekranı:** `https://siteniz.com/admin/kitchen.php`

---

## 🔒 Güvenlik Notu

Kurulum tamamlandıktan sonra güvenlik amacıyla `install.php` dosyasını sunucunuzdan silebilir veya adını değiştirebilirsiniz.
