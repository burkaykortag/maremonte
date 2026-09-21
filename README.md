# 🍷 Hotel Mare Monte - Lüks QR Menü & Yönetim Sistemi

Modern, lüks tasarıma sahip, tam mobil uygulama (PWA) hissi veren, çok dilli ve modüler dijital QR menü & restoran yönetim paneli.

---

## 🌟 Öne Çıkan Özellikler

### 📱 Müşteri QR Menüsü
- **Lüks ve Akıcı Mobil Tasarım:** Cam efekti (Glassmorphism), akıcı dokunmatik geçişler.
- **Dark / Light Tema:** İşletmenin konseptine göre admin tarafından belirlenen Açık veya Koyu tema modu.
- **Çift Logo Desteği:** Açık ve koyu arka planlar için özel 2 ayrı logo yükleme alanı.
- **Ürün Opsiyonları & Varyasyonları (Modifiers):** Pişme derecesi (Az, Orta, İyi), burger sosu, ekstra malzeme (+₺) vb.
- **Masadan Sipariş & Sepet:** Müşterilerin masadan sepete ekleyip garsona veya mutfağa doğrudan sipariş göndermesi.
- **Çoklu Dil Desteği:** Türkçe, İngilizce, Arapça, Rusça ve Almanca (TR, EN, AR, RU, DE).
- **Instagram Hikayeleri (Stories):** Menü başında yuvarlak animasyonlu hikaye halkaları ile promosyon ve günün lezzetleri.
- **Giriş Kampanya Pop-up'ı:** Menü ilk açıldığında gösterilen dikkat çekici karşılama modalı.
- **Google Değerlendirme & Yorumlar:** 1-5 yıldızlı memnuniyet anketi (5 yıldızda doğrudan Google Haritalar işletme profiline yönlendirme).
- **Alerjen ve Diyet Filtreleri:** Glutensiz, Düşük Kalori, Vejetaryen vb. hızlı filtreleme etiketleri.
- **Garson & Hesap Çağrı:** Masadan tek tıkla Garson, Nakit veya Kredi Kartı hesabı talep etme.
- **Wi-Fi Paylaşımı:** Tek tıkla işletmenin Wi-Fi şifresini panoya kopyalama.

---

### 🎛️ Yönetim Paneli (Admin)
- **Modüler Özellik Yönetimi:** Tüm özellikleri (Sipariş, Çoklu Dil, Hikayeler, Pop-up, Yorumlar, Alerjenler vb.) tek tıkla **Aktif / Pasif** yapabilme.
- **Canlı Mutfak/Bar Ekranı (KDS):** Siparişleri 8 saniyede bir otomatik tazeleyen ekran ve sipariş durum akışı (*Bekliyor -> Hazırlanıyor -> Hazır -> Servis Edildi*).
- **Termal Fiş Çıktısı (58mm / 80mm):** Tek tıkla mutfak adisyonu ve müşteri fişi yazdırma.
- **Hızlı Fiyat Düzenleyici:** Sayfayı yenilemeden liste üzerinden anlık fiyat güncelleme.
- **Toplu Fiyat Düzenleme Aracı:** Tüm menüye veya seçili kategoriye tek tıkla % yüzde veya sabit ₺ tutarında zam/indirim uygulama.
- **Ürün & Kategori Yönetimi:** 5 dilde başlık/açıklama, sürükle-bırak resim yükleme ve otomatik boyut optimizasyonu.
- **Masa & Yazdırılabilir QR Stand Kartları:** Masalar için tek tıkla QR kod standee kartları üretme ve tarayıcıdan yazdırma şablonu.
- **Satış Raporları & Analiz:** Günlük/haftalık ciro grafikleri, en çok satan lezzetler ve masa performansı.

---

## 🚀 Kurulum & Çalıştırma

1. Dosyaları web sunucunuzun kök dizinine veya alt klasörüne yerleştirin (Örn: c:/xampp/htdocs/menu/).
2. Tarayıcınızdan açın:
   - **QR Menü:** http://localhost/menu/
   - **Yönetim Paneli:** http://localhost/menu/admin/
   - **Varsayılan Giriş:** dmin / dmin123
3. Sistem sıfır konfigürasyon gerektiren **SQLite** ile otomatik olarak veritabanını oluşturur ve demo verilerle başlatır. İsteğe bağlı olarak config.php üzerinden MySQL bağlantısı da aktif edilebilir.

---

## 🛠️ Teknolojiler
- **Backend:** PHP 8.x, PDO (SQLite / MySQL Hibrit Desteği)
- **Frontend:** Vanilla JavaScript (ES6+), Modern CSS3 (Variables, Flexbox, CSS Grid, Glassmorphism)
- **Kütüphaneler:** FontAwesome 6, Chart.js, Google Fonts (Outfit & Playfair Display)
