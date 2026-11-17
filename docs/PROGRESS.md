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
| 7 | Warung Bos 3D | Selesai |
| 8 | Dashboard Pengelola dan Admin, CMS, wawasan | Selesai |
| 9 | Beranda, data demo, uji manual, deploy, README, video | Selesai kecuali video demo |
| 10 | Aset baru, halaman masuk, Gemini bergiliran, Resend, Warung Bos 3D orang pertama, deploy VPS | Selesai |

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
### Fase 7

- Mesin permainan di `app/Services/Game`: `GameRules` (tiga jenis warung, tujuh babak, promosi, keputusan khusus tiap babak), `GameEngine` (fungsi murni dengan pembangkit acak mulberry32 berbenih), `EventPicker` (kejadian AI dijepit ke rentang tema babak, cadangan dari 34 kejadian di `ShopEventSeeder`), `BossLines`, dan `GameService` (urutan tahap belanja, harga, buka, laporan).
- Akibat tertunda: menaikkan harga di babak rombongan menurunkan kepuasan 12 poin di babak ulasan. Kenaikan harga bahan di babak 5 terbawa sampai hari 7. Hujan membuat sebagian stok basah terbuang. Kepuasan memengaruhi permintaan, terutama di babak ulasan.
- Nilai akhir = laba (maks 50) + kepuasan (maks 25) + stok terbuang sedikit (maks 15) + pembeli terlayani (maks 10) dikurangi 5 per hari kas minus, dijepit 0 sampai 100. Akhir: ramai (75 ke atas), bertahan (45 sampai 74), belum (di bawah 45).
- Skor Siap Usaha memakai nilai terbaik dari permainan yang sudah disimpan lewat Simpan Pelajaran ke Jejak Usaha (Alur Bisnis 3.7 langkah 8). Rute tambahan `game.save`.
- Adegan 3D dibangun dari geometri di kode (tanpa model GLB), jadi tidak ada aset pihak ketiga yang perlu dicatat lisensinya. Paket Three.js dimuat terpisah lewat impor dinamis (sekitar 139 KB gzip).
- Pemanggilan `initWarung` dipindah ke akhir berkas setelah galat urutan deklarasi ditemukan saat uji peramban.
### Fase 8

- Skema JSON semua fitur AI dipusatkan di `App\Services\Ai\FeatureSchemas`, dipakai service dan tombol Uji Prompt (DA-02). Uji Prompt memakai prompt yang sedang diketik tanpa menyimpannya dan tidak memakan kuota warga.
- Kurasi Bos: Aktifkan hanya bila keempat standar dicentang. Undangan Bos atau Pengelola memakai tautan bertanda tangan Laravel (`signed`) yang berlaku 7 hari. Hanya Admin yang dapat mengundang Pengelola.
- Jadi Bos Pendamping: tamu membuat akun sekaligus profil Bos (Diajukan). Warga yang sudah masuk cukup mengisi profil usaha dan mendapat peran Bos tambahan.
- Halaman CMS (tentang, bantuan, privasi, syarat, kontak) disimpan sebagai Markdown di tabel `pages` dan dirender dengan `html_input: escape`. Isi awal dari `PageSeeder`.
- Wawasan: topik konsultasi, bidang minat, sebaran kapanewon, kehadiran kelas. Dapat diunduh XLSX dengan nama ditulis sebagai teks.
- Pengaturan Admin: bobot Skor Siap Usaha (wajib berjumlah 100) dan batas sesi Ruang Bos per hari, disimpan di tabel `settings`.
### Fase 9

- Beranda: hero dengan judul muncul per kata, pita bidang usaha (satu-satunya marquee), lima tahap ekosistem bertumpuk dengan `position: sticky` dan animasi berbasis gulir CSS, seksi Bos Allo, kelas, siniar, lapak, sertifikat, ajakan untuk pengusaha, dan ajakan akhir. Angka beranda dihitung dari basis data tanpa data Demo (AB-15) dan hanya tampil bila ada.
- `DemoSeeder`: empat akun demo dan skenario Bu Sri dari Profil Usaha sampai lapak terbit dan sertifikat Tercatat. Peluang, rencana, dan Warung Bos dihitung oleh service produksi (tanpa AI supaya hasil stabil). Skor naik dari 10 ke di atas 80. Riwayat skor disebar ke 20 hari terakhir supaya grafik Jejak Usaha bermakna. `tests/Feature/DemoSeederTest.php` menegaskan skor awal, skor akhir, kenaikan bertahap, dan keempat akun bisa masuk.
- Perintah `php artisan allobos:demo --force` mengosongkan dan mengisi ulang data demo. Opsi `--jika-kosong` dipakai entrypoint Docker.
- Dua episode siniar demo memakai video publik YouTube yang relevan (kanal kasisolusi dan Pojok Umkm). Ringkasan disusun dari keterangan video, berlabel Demo.
- Sertifikat demo dicatat sungguhan ke jaringan Hardhat lokal saat seeding bila jaringan tersedia. Bila tidak, statusnya Menunggu Pencatatan dan seeding tetap berhasil.
- `ChainClient` meneruskan `PATH` dan `SystemRoot` ke proses Node. Tanpa itu, di Windows proses Node yang dipanggil dari server web tidak bisa membuka koneksi jaringan dan halaman verifikasi kehabisan waktu.
- Composer dikunci ke platform PHP 8.2 (`config.platform.php`). `endroid/qr-code` turun ke 5.1 dan `spatie/laravel-permission` ke 6.16 karena seri terbarunya butuh PHP 8.5, sementara image Docker memakai PHP 8.4 dan spesifikasi meminta PHP 8.2 ke atas.
- Docker: image dibangun sekali di layanan `app`, dipakai juga oleh `queue` dan `scheduler`. Entry point menunggu MySQL, layanan `app` menjalankan migrasi, data acuan, data demo bila kosong, dan `optimize`.
- GitHub Actions: `ci.yml` (tes Laravel, Pint, build aset, tes Hardhat) dan `deploy.yml` (SSH biasa dengan `known_hosts` terkunci, tanpa aksi pihak ketiga).
- Daftar periksa rilis Design System 12: tidak ada nilai hex, em dash, `console.log`, `dd()` di kode tampilan dan aplikasi. Semua halaman publik dan halaman berlogin keempat peran diperiksa di lebar 360 px tanpa gulir mendatar.- `docker compose up -d --build` dari klon bersih dengan `.env` dari contoh lulus di Docker 29.7.2. Percobaan pertama gagal karena `endroid/qr-code` 6.2 butuh PHP 8.5, percobaan kedua gagal karena galat jaringan npm saat tiga layanan membangun image yang sama bersamaan. Keduanya sudah diperbaiki.

### Fase 10: perbaikan dan pengembangan

- Aset baru dari pemilik proyek: logo dipangkas dan dibuat varian terang untuk tema gelap, favicon dan ikon dari bagian ikon logo, pose Bos Allo diubah ke WebP lebar 720 (sekitar 30 KB per pose). GIF diam dan bicara (21 MB) diubah ke MP4 H.264 (240 KB) dengan ffmpeg dan diputar lewat `<video muted loop playsinline>`. Saat gerak dikurangi, video tidak diputar dan poster diam yang tampil. GIF asli tetap di repositori sebagai sumber.
- Beranda: hero memakai potongan pose Bos Allo setinggi panel dengan gelembung kaca dan pil Suara atau teks, mengikuti panel hijau zaitun dari referensi. Kartu Kamu pengusaha dan ajakan akhir memakai pose lain.
- Halaman masuk, daftar, lupa sandi, atur ulang, konfirmasi, dan undangan mengikuti contoh Jogja Music School: panel gambar penuh di kiri dengan gradasi merek dan judul, kartu di kanan dengan logo, ikon di kolom, tombol tampilkan kata sandi, dan tombol utama berpanah. Di ponsel panel gambar menjadi pita pendek di atas kartu.
- Gemini: lima kunci dipakai bergiliran. Kunci yang mendapat 401, 403, 429, atau 503 dilewati. Model bawaan `gemini-3.1-flash-lite` karena `gemini-2.5-flash` sudah tidak tersedia untuk kunci baru (404). Bila model hilang atau galat (404, 500), model cadangan `gemini-flash-lite-latest` lalu `gemini-flash-latest` dicoba. `phpunit.xml` mengosongkan `GEMINI_API_KEY` supaya tes tidak pernah memanggil API sungguhan.
- Email lewat Resend SMTP (`smtp.resend.com:465`, pengirim `noreply@zite.my.id`). Verifikasi email dinyalakan di server.
- Warung Bos 3D dibangun ulang menjadi orang pertama:
  - Server: `GameEngine::run` dipecah menjadi `plan` (efek tertunda dan permintaan) dan `settle` (tutup buku dari jumlah yang benar-benar terjual). `run` tetap ada untuk Mode Ringan dan hasilnya sama persis dengan sebelumnya. `customers` membagi permintaan ke paling banyak delapan pembeli secara deterministik.
  - Keadaan baru: `display` (isi rak pajang, gudang adalah stok dikurangi rak) dan `day_plan` (rencana hari, pembeli, jumlah terlayani, uang masuk). Tahap baru `melayani` di antara `buka` dan `laporan`.
  - Rute baru: `rak` (pindah satu peti ke rak), `buka-warung`, `layani`, `pergi`, `tutup`. Semuanya membawa `rid`. Perubahan dijalankan di bawah kunci baris dan hasil per `rid` disimpan, jadi kiriman ulang tidak diterapkan dua kali. Belanja boleh diulang sebelum warung dibuka.
  - Klien: `resources/js/warung/` berisi `input.js` (pusat aksi untuk papan ketik, tetikus, gamepad, sentuh), `player.js` (pengendali orang pertama dengan tabrakan kotak), `world.js` (warung dan tujuh stasiun), `interact.js` (sinar tengah layar 3 m dengan oklusi, sorotan, soket tangan dengan `attach()`), `customers.js` (mesin keadaan pembeli), dan `game.js` (putaran bingkai). `warung-3d.js` memegang panel, HUD, dan kiriman ke server.
  - Pustaka fisika tidak dipakai. Warung hanya berisi dinding dan perabot persegi, jadi tabrakan kotak sejajar sumbu cukup dan unduhan tetap kecil.
  - Pointer lock dipakai untuk tetikus. Gamepad memakai tata letak standar, zona mati 0,18, deteksi tekan sekali, dan label tombol Xbox atau PlayStation dari id perangkat. Di panel, arah gamepad memindah fokus, A menekan, B menutup.
  - Posisi dan arah pandang pemain disimpan di `localStorage`. Keadaan permainan tetap di server. Memuat ulang di tengah hari memunculkan lagi pembeli yang belum dilayani.
- Kepala halaman panggung memakai ikon logo saja di ponsel supaya tombol tidak bertabrakan.
- Deploy ke VPS di `https://allobos.zite.my.id` (lihat bagian Deploy di README). Repositori privat, jadi kode dikirim dengan `git archive` lewat SSH, bukan `git clone` di server.

## Belum selesai

- Video demo 3 sampai 5 menit belum dibuat (perlu direkam pemilik proyek).
- Alur `deploy.yml` (GitHub Actions) belum dipakai karena secret `VPS_*` belum diisi dan server tidak bisa `git pull` dari repositori privat. Deploy saat ini manual (README).
- Kontrol gamepad dan sentuh Warung Bos 3D diuji lewat kode dan peristiwa tiruan, belum dengan gamepad dan ponsel sungguhan.
- Kontrak belum di-deploy ke Polygon Amoy karena `ISSUER_PRIVATE_KEY` dan saldo POL uji belum tersedia. Setelah tersedia: `cd web3 && npm run deploy:amoy`, isi `CERT_CONTRACT_ADDRESS`, lalu di pengelola tekan Catat Ulang ke Blockchain untuk kelas demo.
- Semua fitur AI baru teruji dengan jawaban Gemini dipalsukan dan jalur cadangan. Perlu uji ulang dengan `GEMINI_API_KEY` sungguhan, terutama Ruang Bos mode suara, Cek Produk dengan foto nyata, dan ringkasan siniar dari video.
- Taksonomi 13 bidang usaha dan 5 bidang pendukung, nama program, dan pembicara IPI perlu dicocokkan dengan data resmi IPI. Saat ini semua kelas dan pembicara berlabel Demo.