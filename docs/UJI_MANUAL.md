# Uji manual alloBos

Langkah uji per peran untuk penjurian dan sebelum rilis. Data awal: `php artisan allobos:demo --force` (atau `SEED_DEMO=true` di Docker). Sandi keempat akun demo sama dengan `DEMO_PASSWORD` (bawaan `password123`).

| Akun | Email |
| --- | --- |
| Warga (Bu Sri) | warga@allobos.com |
| Bos (Pak Arif) | bos@allobos.com |
| Pengelola IPI (Mbak Rani) | pengelola@allobos.com |
| Admin | admin@allobos.com |

Kolom Hasil diisi saat uji dijalankan. Lingkungan uji 17 November 2026: Windows 11, PHP 8.5, MySQL 8.0 Laragon, `php artisan serve`, Chrome desktop, lebar 360 px ditiru lewat iframe, jaringan Hardhat lokal (chain 31337), tanpa `GEMINI_API_KEY`.

Keterangan hasil: **Lulus** = dicoba dan sesuai. **Lulus (tes)** = dibuktikan tes otomatis, belum diklik manual. **Belum** = belum bisa diuji, alasan ditulis.

## Pengunjung

| No | Langkah | Harapan | Hasil |
| --- | --- | --- | --- |
| P1 | Buka beranda | Hero, pita bidang usaha, lima tahap bertumpuk saat digulir, kelas, siniar, lapak, ajakan akhir | Lulus |
| P2 | Lebar 360 px pada beranda, kelas, lapak, siniar, bos, verifikasi, tentang, jadi bos, masuk, daftar | Tidak ada gulir mendatar | Lulus |
| P3 | Buka `/lapak/keripik-mbok-sri`, tekan Pesan lewat WhatsApp | WhatsApp terbuka dengan pesan berisi nama produk dan harga | Lulus (tes) |
| P4 | Buka kode sertifikat demo di `/verifikasi` | Status Asli, data kelas, penanda tangan, bukti blockchain, label Jaringan uji | Lulus |
| P5 | Ketik kode asal di `/verifikasi` | Status Tidak Ditemukan dengan saran memeriksa kode | Lulus (tes) |
| P6 | Tema gelap lewat tombol bulan | Semua warna berganti lewat token, teks tetap terbaca | Lulus |
| P7 | Daftar akun baru, centang persetujuan | Masuk ke langkah 1 Profil Usaha | Lulus (tes) |

## Warga

| No | Langkah | Harapan | Hasil |
| --- | --- | --- | --- |
| W1 | Isi lima langkah Profil Usaha | Tidak bisa melompat langkah, skor awal 10 | Lulus |
| W2 | Dashboard | Skor Siap Usaha, langkah berikutnya, agenda, rincian skor | Lulus |
| W3 | Ruang Bos mode teks, kirim pertanyaan tanpa kunci Gemini | Sapaan tampil dengan GIF bicara, pesan galat dengan Coba Lagi | Lulus |
| W4 | Ruang Bos mode suara dengan kunci Gemini | Transkrip tampil, jawaban dibacakan, video bicara selama suara | Belum: kunci Gemini sudah aktif di server (D4), mode suara perlu dicoba dengan mikrofon sungguhan |
| W5 | Akhiri Sesi | Catatan Konsultasi, Tambahkan ke Langkah 30 Hari, Unduh PDF | Lulus |
| W6 | Buka Peluang, Pilih Usaha Ini, Susun Rencana | Tiga peluang berlabel, rencana bisa diubah dan diunduh PDF | Lulus (tanpa AI) |
| W7 | Kalkulator HPP | Hasil berubah langsung, HPP Rp2.200 untuk contoh bahan Rp100.000, 50 unit, 8 produksi, harga Rp5.000 | Lulus |
| W8 | Centang Langkah 30 Hari | Persentase naik, skor ikut dihitung ulang | Lulus (tes) |
| W9 | Cek Produk dengan foto | Foto dikompres, hasil AI berlabel Perkiraan AI | Lulus (tes, Gemini dipalsukan). Tanpa kunci: status Gagal dengan penjelasan, Lulus |
| W10 | Buat lapak, tambah produk berfoto, Ajukan Terbit | Status Menunggu, Pengelola diberi tahu | Lulus |
| W11 | Daftar kelas, catat hadir dengan kode saat sesi | Kode salah dan di luar waktu ditolak dengan pesan jelas | Lulus (tes) |
| W12 | Sertifikat Saya, ganti nama ke inisial, sakelar Tampilkan di Lapak | Halaman verifikasi dan lencana lapak mengikuti | Lulus (tes) |
| W13 | Siniar, Putar, Tandai Sudah Didengar | Pemutar dimuat setelah Putar, tanda tersimpan | Lulus (tes) |
| W14 | Warung Bos 3D orang pertama: meja pemasok, papan harga, peti dari gudang ke rak, papan pengumuman, balik papan BUKA | Sorotan dan petunjuk muncul saat membidik dalam 3 m, panel terbuka, rak terisi, pembeli datang | Lulus (17 November 2026, papan ketik dan sentuh tiruan, hari 1) |
| W14a | Layani pembeli di kasir, biarkan satu pembeli menunggu terlalu lama | Kas bertambah, kekurangan barang disebut Bos Allo, pembeli yang terlalu lama pergi | Lulus |
| W14b | Muat ulang halaman saat warung buka, lalu balik papan TUTUP | Pembeli yang belum dilayani muncul lagi, laporan harian tampil dengan jumlah pembeli terlayani | Lulus |
| W14c | Gamepad: jalan, lihat, A untuk interaksi, navigasi panel dengan arah | Label tombol mengikuti jenis gamepad | Belum: belum diuji dengan gamepad sungguhan |
| W15 | Warung tujuh hari sampai rapor, Simpan Pelajaran | Rapor, tiga pelajaran, nilai masuk skor | Lulus (tes) |
| W16 | Mode Ringan di Warung | Panel sama tanpa kanvas 3D | Lulus |
| W17 | Temu Bos: pilih slot, Ajukan Sesi, beri penilaian | Slot kurang dari 12 jam tidak bisa dipilih | Lulus (tes) |
| W18 | Semua halaman warga di 360 px | Tidak ada gulir mendatar | Lulus |

## Bos

| No | Langkah | Harapan | Hasil |
| --- | --- | --- | --- |
| B1 | Tambah Slot | Slot muncul, bentrok ditolak | Lulus (tes) |
| B2 | Setujui permintaan dengan tautan rapat | Tautan hanya terlihat kedua pihak setelah disetujui | Lulus (tes) |
| B3 | Tandai Selesai dengan catatan dan langkah | Warga melihat catatan dan bisa menyalin langkah | Lulus (tes) |
| B4 | Tanda Tangan Sertifikat: simpan spesimen di kanvas | Spesimen tersimpan, kembali ke kelas | Lulus (tes) |
| B5 | Tanda Tangani dengan kata sandi salah | Ditolak | Lulus (tes) |
| B6 | Halaman Bos di 360 px | Tidak ada gulir mendatar | Lulus |

## Pengelola IPI

| No | Langkah | Harapan | Hasil |
| --- | --- | --- | --- |
| G1 | Buat kelas dengan sesi, Terbitkan | Tampil di publik | Lulus (tes) |
| G2 | Buka Kode Kehadiran | Kode besar di layar penuh | Lulus (tes) |
| G3 | Tutup Kelas tanpa penanda tangan | Ditolak | Lulus (tes) |
| G4 | Tetapkan penanda tangan lalu Tutup Kelas | Draf sertifikat untuk yang hadir 75 persen, penanda tangan diberi tahu | Lulus (tes) |
| G5 | Unduh Peserta (XLSX) | Berkas XLSX, nama ditulis sebagai teks | Lulus (tes) |
| G6 | Tambah episode dari URL YouTube, Buat Ringkasan, Terbitkan | Judul terambil, ringkasan draf, warga sesuai minat diberi tahu | Lulus (tes, oEmbed dan Gemini dipalsukan) |
| G7 | Kurasi Bos: Aktifkan tanpa empat centang | Tombol terkunci, server menolak | Lulus (tes) |
| G8 | Moderasi lapak: Setujui, Turunkan dengan alasan | Pemilik diberi tahu alasannya | Lulus (tes) |
| G9 | Cabut sertifikat | Status Dicabut di halaman verifikasi dan di kontrak | Lulus (tes, dan nyata di Hardhat lokal lewat ChainClient) |
| G10 | Catat Ulang setelah jaringan gagal | Sertifikat menjadi Tercatat | Lulus (tes) |
| G11 | Wawasan dan unduhan XLSX | Grafik dan berkas | Lulus |

## Admin

| No | Langkah | Harapan | Hasil |
| --- | --- | --- | --- |
| A1 | Pengaturan AI: matikan fitur, simpan versi prompt, Uji Prompt | Berlaku tanpa mengubah kode | Lulus (tes) |
| A2 | Blockchain | Jaringan, kontrak, alamat dan saldo penerbit, transaksi | Lulus (Hardhat lokal) |
| A3 | Bobot skor tidak berjumlah 100 | Ditolak | Lulus (tes) |
| A4 | Ubah halaman Tentang | Tampil di `/tentang`, HTML mentah tidak dijalankan | Lulus (tes) |
| A5 | Undang Pengelola | Tautan bertanda tangan berlaku 7 hari | Lulus (tes) |
| A6 | Log audit | Tanda tangan, pencabutan, moderasi tercatat | Lulus |

## Deploy

| No | Langkah | Harapan | Hasil |
| --- | --- | --- | --- |
| D1 | `docker compose up -d --build` dari klon bersih dengan `.env` dari contoh | Empat wadah berjalan, migrasi dan data demo otomatis, empat akun bisa masuk | Lulus (17 November 2026, Docker 29.7.2: app, mysql, queue, scheduler berjalan, keempat akun masuk ke dashboardnya, halaman publik 200. Sertifikat demo Menunggu Pencatatan karena jaringan belum diatur) |
| D3 | Deploy VPS `https://allobos.zite.my.id` di belakang nginx dan Let's Encrypt | Empat wadah berjalan, HTTP dialihkan ke HTTPS, keempat akun demo masuk ke dashboardnya, Warung Bos 3D termuat | Lulus (17 November 2026) |
| D4 | Gemini sungguhan dan email Resend dari server | Ringkasan dari Gemini kembali, email terkirim | Lulus (Gemini `gemini-3.1-flash-lite` 2,5 detik, email ke alamat uji Resend) |
| D2 | Kontrak di Polygon Amoy dan QR sertifikat demo di jaringan uji publik | Penjelajah blok menampilkan transaksi | Belum: `ISSUER_PRIVATE_KEY` dan saldo POL uji belum tersedia |
