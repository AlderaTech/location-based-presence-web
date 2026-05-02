# Presensi Web

Backend API dan dashboard administrasi Presensi Guru berbasis Laravel. Aplikasi web ini menjadi pusat pengelolaan data sekolah, validasi presensi berbasis lokasi, persetujuan pengajuan izin, analitik, serta ekspor laporan.

## Ringkasan Bisnis

Presensi Guru dirancang untuk membantu sekolah memantau kehadiran guru secara lebih akurat dengan validasi lokasi sekolah. Guru mencatat presensi dari aplikasi mobile, sedangkan pengelola sekolah memantau data, mengelola master data, meninjau pengajuan izin, dan mengekspor rekap untuk arsip atau kebutuhan administrasi.

Tujuan utama:

- Mengurangi presensi manual dan kesalahan rekap.
- Memvalidasi presensi berdasarkan radius lokasi sekolah.
- Mendeteksi indikasi lokasi palsu dari aplikasi mobile.
- Menyediakan laporan kehadiran yang siap diekspor.
- Memisahkan hak akses operasional admin dan monitoring kepala sekolah.

## Role dan Hak Akses

| Role | Fokus | Fitur |
| --- | --- | --- |
| Admin | Operasional penuh | Dashboard, kelola guru, reset perangkat guru, lokasi sekolah, jadwal kerja, hari libur, akun admin/kepala sekolah, koreksi presensi, setujui/tolak izin, laporan, ekspor PDF/XLSX. |
| Kepala Sekolah | Analitik dan pelaporan | Dashboard analitik, data presensi read-only, monitoring pengajuan izin read-only, laporan, ekspor PDF/XLSX. |
| Guru | Pengguna mobile | Masuk dari aplikasi mobile, presensi masuk/pulang, riwayat presensi, pengajuan izin/sakit/cuti, profil dan notifikasi. |

Role `tata_usaha` sudah digabungkan ke `admin`. Migration `2026_05_02_000001_gabungkan_role_tata_usaha_ke_admin.php` akan mengubah data lama dengan role `tata_usaha` menjadi `admin`.

## Fitur Web

- Dashboard analitik: ringkasan kehadiran hari ini, tren harian, status bulanan, pengajuan izin, guru belum presensi, dan presensi terbaru.
- Manajemen guru: akun guru, jadwal bawaan, status aktif/nonaktif, dan reset perangkat terikat.
- Manajemen lokasi sekolah: titik latitude/longitude, radius validasi, dan status lokasi.
- Manajemen jadwal kerja: jam masuk, jam pulang, toleransi keterlambatan, dan status jadwal.
- Manajemen hari libur: libur sekolah/nasional untuk pengecualian presensi.
- Data presensi: filter, sort, deteksi lokasi palsu, status presensi, dan koreksi manual oleh admin.
- Pengajuan izin: pantau izin/sakit/cuti, dokumen pendukung, persetujuan, penolakan, dan alasan penolakan.
- Laporan: filter lanjutan, sort, ekspor PDF landscape, dan ekspor XLSX dengan filter/freeze header.
- Audit: pencatatan koreksi presensi, approval izin, dan percobaan lokasi palsu.
- API mobile: autentikasi guru, profil, presensi, riwayat, jadwal, hari libur, dan pengajuan izin.

## Stack Teknis

- PHP `^8.4`
- Laravel `^13.0`
- MySQL atau database SQL kompatibel Laravel
- Session, cache, dan queue berbasis database
- Vite `^8`, Tailwind CSS `^4`
- Chart.js untuk grafik dashboard
- Lucide untuk ikon
- SweetAlert2 untuk dialog konfirmasi
- PHPUnit untuk test

## Struktur Penting

```text
app/Http/Controllers/Admin      Controller dashboard dan halaman admin
app/Http/Controllers/Api/Mobile API untuk aplikasi mobile guru
app/Http/Middleware             Middleware akses admin, role, dan token mobile
app/Services/LayananPresensi    Logika presensi dan validasi lokasi
app/Support                     Formatter, export PDF/XLSX, sorting, session handler
database/migrations             Skema database
database/seeders                Seeder demo
resources/views/admin           Blade dashboard admin
resources/css, resources/js     Asset frontend Vite
routes/web.php                  Route dashboard web
routes/api.php                  Route API mobile
tests                           Test fitur dan unit
```

## Instalasi Lokal

1. Install dependency PHP dan JavaScript.

```bash
composer install
npm install
```

2. Siapkan `.env`.

```bash
cp .env.example .env
php artisan key:generate
```

3. Sesuaikan konfigurasi database di `.env`.

```env
APP_NAME="Presensi Guru"
APP_TIMEZONE=Asia/Jakarta
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=location_based_presence
DB_USERNAME=root
DB_PASSWORD=
SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
```

4. Jalankan migrasi dan seeder demo.

```bash
php artisan migrate --seed
```

5. Jalankan frontend dan server.

```bash
npm run dev
php artisan serve
```

Dashboard tersedia di `http://127.0.0.1:8000/admin`.

## Akun Demo

Seeder demo membuat akun berikut dengan password default `password`.

| Role | Email |
| --- | --- |
| Admin | `admin@presensi.test` |
| Kepala Sekolah | `kepsek@presensi.test` |
| Guru | `guru@presensi.test` |

## API Mobile

Base URL lokal umum:

```text
http://127.0.0.1:8000/api/mobile
```

Untuk emulator Android gunakan:

```text
http://10.0.2.2:8000/api/mobile
```

Endpoint utama:

| Method | Endpoint | Keterangan |
| --- | --- | --- |
| POST | `/autentikasi/masuk` | Login guru dan ikat perangkat pertama. |
| POST | `/autentikasi/keluar` | Logout token mobile. |
| GET | `/profil` | Profil guru aktif. |
| PUT | `/profil/pengaturan-notifikasi` | Ubah status notifikasi guru. |
| GET | `/jadwal-kerja` | Jadwal kerja guru dan lokasi aktif. |
| GET | `/hari-libur` | Daftar hari libur. |
| GET | `/presensi/hari-ini` | Status presensi hari ini. |
| POST | `/presensi/catat` | Catat presensi masuk/pulang. |
| GET | `/presensi/riwayat` | Riwayat presensi bulanan. |
| GET | `/pengajuan-izin` | Riwayat pengajuan izin guru. |
| POST | `/pengajuan-izin` | Kirim izin/sakit/cuti dengan dokumen opsional. |

## Alur Presensi

1. Admin membuat akun guru, jadwal kerja, dan lokasi sekolah aktif.
2. Guru login dari aplikasi mobile. Perangkat pertama yang berhasil login akan terikat.
3. Aplikasi mobile mengirim latitude, longitude, akurasi, status lokasi palsu, dan ID perangkat.
4. Backend memvalidasi akun, perangkat, hari libur, jadwal, radius lokasi, dan lokasi palsu.
5. Presensi masuk/pulang tersimpan dan audit dibuat jika ada kejadian penting.
6. Admin dapat mengoreksi presensi manual dengan alasan koreksi.
7. Kepala sekolah memantau analitik dan laporan tanpa akses mutasi data.

## Laporan dan Ekspor

Laporan presensi mendukung filter:

- Kata kunci guru/email/NIP
- Guru
- Status presensi
- Jadwal kerja
- Lokasi sekolah
- Validasi lokasi palsu
- Rentang tanggal
- Sort kolom

Format ekspor:

- `PDF`: laporan landscape untuk arsip/cetak.
- `XLSX`: spreadsheet dengan header rapi, filter, dan freeze pane.

## Testing dan Kualitas

Jalankan seluruh test:

```bash
php artisan test
```

Jalankan formatter:

```bash
./vendor/bin/pint
```

Build asset produksi:

```bash
npm run build
```

## CI/CD

Workflow berada di `.github/workflows/web.yml`.

- Pull request: menjalankan test Laravel dan build asset frontend.
- Push ke `main` atau `master`: menjalankan test, build Docker image, lalu push image ke GHCR.
- Push tag `web-v*` atau `v*`: menjalankan test, build Docker image, lalu push image tag release ke GHCR.
- Manual workflow dispatch: bisa dipakai untuk publish ulang image dari branch yang dipilih.

Image GHCR dipublish ke:

```text
ghcr.io/<owner>/<repo>
```

Tag yang dibuat:

- `latest` untuk default branch.
- Nama branch, misalnya `main`.
- Nama tag Git, misalnya `web-v1.0.0`.
- `sha-<commit>` untuk jejak build.

Contoh pull image:

```bash
docker pull ghcr.io/<owner>/<repo>:latest
```

Jika tab Packages masih kosong setelah workflow sukses, pastikan repository berada di root repo GitHub yang benar dan workflow memiliki permission `packages: write`.

## Branding

Logo aplikasi web berada di `public/assets/brand/logo.png`.
Favicon web berada di `public/favicon.png` dan `public/favicon.ico`.
