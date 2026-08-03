# KP-BPSDMJABAR2
# KP-PresensiDigitalJabarCorpU

## 📑 Daftar Isi

- [Tentang Proyek](#-tentang-proyek)
- [Struktur File](#-struktur-file)
- [Fitur yang Dikerjakan](#-fitur-yang-dikerjakan)
- [Cara Menjalankan](#-cara-menjalankan)
- [Tim Pengembang](#-tim-pengembang)

## Tentang Proyek

Repositori ini berisi source code proyek Kerja Praktik (KP) terkait **redesain Website Presensi Digital** yang terdapat pada platform **Integral Jabar CorpU** (BPSDM Provinsi Jawa Barat), website ini digunakan untuk mencatat kehadiran peserta pada setiap sesi kegiatan pelatihan secara digital, cepat, dan terdokumentasi.

🔗 Website: [integral-bpsdm.jabarprov.go.id/presensi-digital](https://integral-bpsdm.jabarprov.go.id/presensi-digital/index.php)

Proyek ini merupakan bagian dari program magang/KP di Badan Pengembangan Sumber Daya Manusia (BPSDM) Provinsi Jawa Barat. Tim kami secara khusus bertanggung jawab pada bagian **Presensi Digital**, dengan fokus kontribusi pada pengembangan sisi desain UI/UX halaman tersebut.

## Struktur File

| File / Folder | Keterangan |
|----------------|------------|
| `assets/css/dashboard-redesign.css` | Stylesheet khusus untuk redesign UI (sidebar, kartu statistik, banner, responsive, hover effect) |
| `header.php` | Layout utama (sidebar navigasi + top bar), digunakan di seluruh halaman internal |
| `footer.php` | Penutup layout + pemanggilan Bootstrap JS bundle |
| `index.php` | Halaman Dashboard — ringkasan statistik presensi (kartu Total Pelatihan, Total Peserta Hadir, Jumlah Pengguna) |
| `login.php` | Halaman login panel Presensi Digital — *akan ditambahkan* |
| *(file lain menyusul)* | Struktur halaman lain (Kelola Kategori, Kelola Presensi, Kelola User) akan disusun seiring proses desain ulang |

## Fitur yang Dikerjakan

- **Dashboard** ✅ *(selesai — branch `redesain-ui`)*
  - Redesign layout dari navbar atas menjadi sidebar navigasi
  - Kartu statistik dinamis: Total Pelatihan, Total Peserta Hadir, Jumlah Pengguna (khusus role admin)
  - Banner ajakan aksi menuju halaman Kelola Presensi
  - Sidebar & menu responsive (collapse menjadi offcanvas di layar mobile)
  - Hover effect pada menu navigasi (desktop & mobile) dan kartu statistik
- **Download Barcode** ✅ *(selesai)*
  - Fitur unduh barcode presensi
- Rencana kerja tim selanjutnya pada bagian **Presensi Digital**:
  - **Presensi Semua Kegiatan** — menampilkan dan mengelola data presensi dari seluruh kegiatan
  - **Kelola Kategori & Kelola Presensi** — menggabungkan halaman kelola kategori dengan halaman kelola presensi menjadi satu halaman agar lebih efisien
- Daftar fitur akan diperbarui seiring progres pengecekan dan pengerjaan pada website

## Cara Menjalankan

1. Clone repositori ini ke lokal: 
git clone https://github.com/mulyadelani/KP-BPSDMJABAR2.git
2. Untuk melihat tampilan hasil redesign terbaru, pindah ke branch `redesain-ui`:
3. Jalankan menggunakan XAMPP (Apache + MySQL aktif), lalu buka `http://localhost/presensi/index.php` di browser.

## Tim Pengembang

1. Mulya Delani (123140019)
2. Eka Putri Azhari Ritonga (123140028)
3. Atalie Salsabila (123140027)

*Proyek ini merupakan bagian dari Kerja Praktik (KP) Program Studi Teknik Informatika, Institut Teknologi Sumatera (ITERA).*