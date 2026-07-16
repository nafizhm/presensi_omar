# Integrasi Template Presensi ke Laravel

## 1. Install dependency
```bash
composer require laravel/breeze --dev   # kalau belum ada auth & Vite di project
php artisan breeze:install blade
npm install alpinejs @alpinejs/collapse
```

## 2. Copy file ke project
Salin folder/file berikut ke project Laravel kamu (timpa/gabung sesuai kondisi):
- `resources/views/layouts/app.blade.php`
- `resources/views/partials/bottom-nav.blade.php`
- `resources/views/presensi/*.blade.php`
- `resources/css/presensi.css`
- `resources/js/app.js`
- `routes/web.php` (gabungkan isinya dengan routes/web.php yang sudah ada)
- `app/Http/Controllers/PresensiController.php`
- `app/Models/Presensi.php`
- `database/migrations/2026_07_16_000000_create_presensis_table.php`
- `tailwind.config.js` (gabungkan bagian `theme.extend.colors` & `fontFamily` ke config Tailwind yang sudah ada)

## 3. Migrate & storage link
```bash
php artisan migrate
php artisan storage:link
```

## 4. Set koordinat kantor
Buka `app/Http/Controllers/PresensiController.php`, ubah:
```php
protected float $officeLat = -6.200000;
protected float $officeLng = 106.816666;
protected int $radiusMeter = 100;
protected string $namaKantor = 'Kantor Pusat';
```
Kalau nanti butuh multi-cabang, pindahkan 4 field ini ke tabel `kantors` dan pilih kantor terdekat per user.

## 5. Jalankan
```bash
npm run dev   # atau npm run build untuk produksi
php artisan serve
```
Buka `/presensi` (perlu login dulu lewat Breeze).

## Catatan penting
- **GPS**: `navigator.geolocation` HANYA aktif di konteks HTTPS (atau `localhost`). Saat testing di HP fisik lewat jaringan lokal, pakai `php artisan serve --host=0.0.0.0` + tunnel HTTPS (ngrok/Cloudflare Tunnel), browser akan menolak izin lokasi di HTTP biasa.
- **Validasi ganda**: jarak dihitung di JS (untuk UX instan) dan divalidasi ulang di server (`PresensiController::haversine`) — jangan hanya percaya perhitungan client-side karena bisa dimanipulasi.
- **Foto swafoto**: pakai `<input type="file" accept="image/*" capture="user">`, otomatis membuka kamera depan di HP. Kalau mau live-camera preview (bukan buka app kamera terpisah), perlu `getUserMedia` + `<canvas>` — bisa aku tambahkan kalau diperlukan.
- **Jam masuk batas**: saat ini hardcode jam 08:00 di `store()`. Sesuaikan dengan jadwal kerja kalau tiap user beda shift.
