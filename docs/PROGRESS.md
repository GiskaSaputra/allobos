# Progres pembangunan alloBos

Fase mengikuti PRD bagian 10. Bila sesi terputus, lanjutkan dari fase pertama yang belum bertanda selesai.

## Status fase

| Fase | Isi | Status |
| --- | --- | --- |
| 0 | Fondasi: proyek, autentikasi, peran, tata letak, token desain, klien AI | Selesai |
| 1 | Profil Usaha dan Jejak Usaha | Selesai |
| 2 | Ruang Bos | Selesai |
| 3 | Peluang, Rencana, HPP, Langkah 30 Hari | Selesai |
| 4 | Cek Produk dan Lapak | Selesai |
| 5 | Kelas Bos dan Temu Bos | Selesai |
| 5b | Sertifikat Terverifikasi | Selesai (jaringan Hardhat lokal) |
| 6 | Siniar Bos | Selesai |
| 7 | Warung Bos 3D | Belum |
| 8 | Dashboard Pengelola dan Admin, CMS, wawasan | Belum |
| 9 | Beranda, data demo, uji manual, deploy, README, video | Belum |

## Keputusan

- **Livewire 3.** Composer memasang Livewire 4 secara bawaan, diturunkan ke 3.x sesuai Design System. Alpine diambil dari bundel ESM Livewire (`livewire.esm`) supaya hanya ada satu Alpine. Aset Livewire dimuat lewat `@livewireScriptConfig` dan `app.js`.
- **Satu migrasi skema untuk semua modul** dibuat di Fase 1 supaya Skor Siap Usaha bisa dihitung dari bukti sejak awal. Tabel `session_notes` dan `boss_ratings` di PRD bagian 7 digabung ke kolom `mentoring_sessions` (`boss_notes`, `boss_steps`, `rating`, `rating_comment`) karena hubungannya satu ke satu. Tabel `certificate_signatures` digantikan kolom `signed_at` di `program_signers`, karena satu tanda tangan berlaku untuk seluruh sertifikat kelas. `page_blocks` dan `media` tidak dibuat: halaman CMS cukup satu kolom isi Markdown.
- **Taksonomi IPI.** Daftar 13 bidang usaha dan 5 bidang pendukung di `ReferenceSeeder` disusun mengikuti kerangka IPI tetapi namanya perlu dicocokkan dengan Company Profile IPI 2026, yang tidak tersedia di folder kerja.
- **Verifikasi email** dapat dimatikan lewat `REQUIRE_EMAIL_VERIFICATION=false` bila server belum punya SMTP. Bawaan menyala.
- **Admin** memegang semua hak Pengelola lewat `Gate::before`, kecuali tanda tangan sertifikat yang tetap per orang (AB-18).
- **Batas laju** semua permintaan tulis lewat middleware `ThrottleWrites` (120 per menit per pengguna). Fitur AI punya pembatas tambahan `throttle:ai` (12 per menit).
- **Langkah berikutnya di dashboard** menunjuk komponen dengan poin hilang terbesar. Untuk profil baru itu adalah Belajar (bobot 25), sesuai JU-01.
- **Font** Montserrat Variable (display) dan Ubuntu (isi) dari paket fontsource, dimuat lokal.
- **GIF Bos Allo** berlatar abu polos, bukan transparan. Ditampilkan di dalam bingkai bersudut. `allo-transparan.webp` dibuat dari bingkai pertama GIF diam dengan GD.
- **Proyek acuan PNC Elevate** tidak ada di komputer ini. Pola diambil dari uraian di Design System.

## Belum selesai atau perlu dicek

- Docker daemon di komputer pengembang mati, jadi `docker compose up` diuji di Fase 9 bila daemon dapat dinyalakan.
- Ruang Bos dengan Gemini sungguhan belum diuji karena `GEMINI_API_KEY` belum tersedia. Jalur cadangan (galat, Coba Lagi, catatan tanpa AI) sudah diuji di peramban dan tes.

### Fase 2

- Sapaan pembuka disusun tanpa AI dari profil warga supaya tidak memakan jatah giliran.
- Kuota Ruang Bos dua lapis: jumlah sesi per hari (bawaan 5, `BOSS_SESSIONS_PER_DAY`) dihitung saat sesi dimulai (RB-01), dan jatah giliran `boss_chat` 30 per hari dihitung `GeminiClient` (RB-10). Batas laju per menit lewat `throttle:ai`.
- Bila AI gagal, giliran warga tidak disimpan dan tidak dihitung. Tombol Coba Lagi mengirim ulang pesan yang sama.
- Pengaman RB-09 dua lapis: instruksi sistem, dan pemeriksaan kata kunci (pajak, hukum, pinjaman, KUR, izin) yang selalu menambahkan saran Temu Bos.
- Catatan Konsultasi disusun lewat antrean (`SummarizeBossSession`). Halaman catatan menampilkan kerangka dan memuat ulang sendiri sampai catatan siap.
- Perintah `allobos:ruang-bos-rapikan` tiap 5 menit menutup sesi diam lebih dari 30 menit dan menghapus transkrip lebih dari 180 hari (AB-16).
- Zona waktu aplikasi diganti ke `Asia/Jakarta` supaya jam di antarmuka dan reset kuota tengah malam WIB (AB-11) benar.
- PDF memakai dompdf. Karena dompdf tidak membaca variabel CSS, nilai token ditulis ulang sebagai `rgb()` di `resources/views/pdf/layout.blade.php`.
### Fase 3

- Rumus HPP: biaya bahan per sekali produksi dibagi hasil produksi, ditambah biaya tetap per bulan dibagi produksi sebulan. Titik impas per bulan adalah biaya tetap dibagi selisih harga jual dan biaya bahan per unit. Kolom `batches_per_month` ditambahkan lewat migrasi baru.
- Cadangan Peluang tanpa AI mencocokkan bidang minat profil (bobot 5) dan kata kunci profil, keahlian, potensi kapanewon, serta Catatan Konsultasi terhadap kata kunci tiap bidang IPI. Alasan selalu menyebut data milik warga (PU-02).
- Bila kuota AI habis, peluang dan rencana tidak memakai cadangan, tetapi menampilkan pesan kuota, supaya warga tahu kenapa hasilnya berbeda.
- Menyusun ulang rencana mengganti langkah asal Rencana yang belum dikerjakan. Langkah yang sudah dicentang dan langkah dari sumber lain tetap.
- Kalkulator HPP memakai Livewire (hasil berubah langsung). Langkah 30 Hari memakai formulir biasa dengan kotak centang yang langsung terkirim, agar tetap jalan tanpa skrip.
- `.grid > * { min-width: 0 }` ditambahkan di CSS dasar setelah grafik Jejak Usaha melebarkan kolom di layar 360 px.
### Fase 4

- Foto unggahan (Cek Produk, produk, sampul) disimpan sebagai WEBP dengan sisi terpanjang 1280 px (sampul 1600 px) lewat `ImageStore` berbasis GD. Arah putar EXIF dipakai bila ekstensi exif tersedia.
- Cek Produk berjalan lewat antrean (`CheckProduct`). Bila AI tidak tersedia, status menjadi Gagal dengan penjelasan dan foto tetap tersimpan. Kisaran harga dibandingkan dengan HPP dari Rencana Usaha bila ada (Alur Bisnis 7).
- Moderasi lapak (setujui, tolak, turunkan) dibuat di fase ini supaya alur Buka bisa diuji ujung ke ujung. Daftar Pengelola lain menyusul di Fase 8.
- Tombol Laporkan Lapak membutuhkan rute tambahan `shops.report` (POST `/lapak/{slug}/lapor`, dibatasi 5 kali per 10 menit) yang tidak tertulis di Design System 3.1.
- Pemilik, Pengelola, dan Admin dapat melihat pratinjau lapak yang belum terbit. Kunjungan pemilik tidak dihitung.
### Fase 5

- Status kelas Dibuka, Penuh, dan Berjalan disinkronkan saat kelas dibuka, saat pendaftaran, dan oleh perintah `allobos:pengingat` tiap 5 menit. Perintah yang sama mengirim pengingat kelas dan Temu Bos (H-1 dan sejam sebelum) serta menutup permintaan Temu Bos lebih dari 48 jam.
- Progres kelas (KB-05) = sesi dihadiri ditambah materi dibuka, dibagi jumlah sesi dan materi. Materi dibuka lewat rute tambahan `warga.programs.material` yang mencatat lalu mengalihkan ke alamat materi.
- Kode kehadiran 6 karakter dibuat ulang setiap Pengelola menekan Buka Kode Kehadiran dan hanya diterima di rentang waktu sesi (AB-06). Halaman kode memakai tata letak stage agar mudah diproyeksikan.
- Menutup kelas (KB-09) langsung membuat draf sertifikat untuk peserta yang hadir minimal 75 persen dan memberi tahu penanda tangan. Penanda tangan wajib pembicara kelas yang punya akun Bos, atau akun Pengelola atau Admin sebagai pimpinan IPI.
- Jadwal sesi kelas tidak ditulis ulang bila sudah ada catatan hadir, supaya kehadiran tidak hilang.
- Temu Bos: rute tambahan `bos.sessions.cancel` (Bos membatalkan dengan alasan) dan `warga.mentoring.steps` (menyalin saran langkah Bos ke Langkah 30 Hari).

### Fase 5b

- Kontrak `AlloBosCertificate` (Solidity 0.8.24) dengan `issueBatch`, `revoke`, `verify`, `setIssuer`, `transferOwnership`, dan galat khusus. Enam tes Hardhat lulus (`cd web3 && npm test`).
- Skrip ethers.js di `web3/scripts` dipanggil `ChainClient` lewat proses Node dengan masukan JSON dari stdin. Kunci privat hanya dikirim sebagai variabel lingkungan proses.
- `anchor.mjs` aman diulang: sidik yang sudah tercatat tidak dikirim lagi, bukti transaksinya diambil dari peristiwa `CertificateIssued`.
- Sidik digital dihitung dari JSON kanonik: kode, id kelas, tanggal terbit, nama penanda tangan berurutan, dan SHA-256 nama penerima bergaram `CERT_HASH_SALT`. Halaman verifikasi menghitung ulang sidik dari basis data dan membandingkannya dengan sidik tersimpan dan catatan kontrak. Beda di salah satunya berarti Tidak Cocok.
- Konfirmasi kata sandi saat menandatangani memakai isian kata sandi di formulir yang sama (aturan `current_password`), bukan middleware `password.confirm`, karena middleware itu mengalihkan permintaan POST ke GET.
- Pratinjau sertifikat untuk penanda tangan memakai rute tambahan `signing.preview` (halaman HTML tanpa tanda tangan dan QR).
- Diuji nyata di jaringan Hardhat lokal (chain 31337): catat, verifikasi, cabut, dan catat ulang. Jaringan uji Polygon Amoy belum diuji karena `ISSUER_PRIVATE_KEY` dan saldo POL uji belum tersedia.
### Fase 6

- Judul dan sampul diambil dari oEmbed YouTube. Bila gagal, episode tetap dibuat dengan judul sementara yang bisa diubah.
- Ringkasan AI dicoba langsung dari video YouTube (bagian `fileData` Gemini). Bila gagal dan ada catatan pengelola, dicoba lagi dari judul dan catatan. Status ringkasan: kosong, memproses, draf, gagal, disetujui. Menerbitkan episode sama dengan menyetujui ringkasan (SB-02).
- Pemutar memakai `youtube-nocookie.com` dan baru dimuat setelah tombol Putar ditekan (SB-04).
- Ekspor XLSX peserta kelas menulis isian pengguna sebagai teks eksplisit supaya tidak bisa disisipi rumus (temuan tinjauan keamanan).