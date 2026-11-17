# Konsep Ide alloBos

Pendamping usaha berbasis AI untuk warga Gunungkidul, dari bertanya sampai membuka lapak

- **Lomba:** IT RUSH 2026, Web Development (HIMATIF UNIB)
- **Subtema:** 3. Ekonomi dan Pemberdayaan Masyarakat
- **Mitra:** Institusi Pengusaha Indonesia (IPI), Gunungkidul, DIY
- **Penyusun:** Giska Saputra
- **Versi:** Final 2.0, 18 November 2026 (menyesuaikan sistem yang sudah berjalan: kelas berbayar QRIS, suara AI, Warung Bos 3D orang pertama, sertifikat premium, deploy produksi)
- **Aplikasi berjalan:** https://allobos.zite.my.id
- **Kode sumber:** https://github.com/GiskaSaputra/allobos (privat)
- **Dokumen terkait:** PRD, Business Flow, Design System

## 1. Ringkasan

alloBos adalah aplikasi web yang mendampingi warga Gunungkidul membangun usaha, dari tahap belum tahu mau usaha apa sampai punya lapak online yang bisa dipesan. Polanya seperti layanan konsultasi dokter daring, tetapi untuk urusan usaha: warga bertanya kepada Bos Allo (bos virtual berbasis AI yang bisa diajak bicara), lalu bila perlu bertemu bos asli, yaitu pengusaha aktif yang dikurasi Institusi Pengusaha Indonesia (IPI).

Konsultasi hanya pintu masuk. Di belakangnya ada kelas pelatihan nyata dari IPI (gratis maupun berbayar lewat QRIS) dengan sertifikat yang ditandatangani pembicara dan pimpinan IPI lalu tercatat di blockchain, siniar dari YouTube, simulasi warung 3D untuk berlatih mengambil keputusan, pemeriksaan produk lewat foto, penemu peluang usaha, dan katalog lapak. Semua aktivitas itu tercatat sebagai Jejak Usaha dan dihitung menjadi Skor Siap Usaha.

> **Kalimat inti:** Tanya bos, belajar, lalu buka usaha. Satu aplikasi untuk seluruh perjalanan, dengan AI sebagai pendamping harian dan pengusaha asli sebagai rujukan.

## 2. Masalah yang ditangani

Masalah di bawah ini diambil dari data dan laporan lapangan tentang Gunungkidul, bukan asumsi umum tentang UMKM.

| Masalah | Bukti | Akibat bagi warga |
| --- | --- | --- |
| Pendidikan formal rendah | Rata-rata lama sekolah penduduk usia 25 tahun ke atas 7,64 tahun pada 2025, di bawah rata-rata DIY 10,2 tahun (BPS, dikutip Databoks). | Materi bisnis yang penuh teks dan istilah sulit dipakai. Butuh pendamping yang bisa diajak bicara dengan bahasa sehari-hari. |
| Pelaku usaha banyak, pendamping sedikit | Sekitar 57.761 pelaku UMKM (Bappeda, dikutip Jurnal Parikesit UGM). Pelaku UMKM Desa Kepek, Wonosari, merasa tidak terwadahi dalam pendampingan pemasaran digital, branding, dan permodalan sehingga pendampingan tidak berlanjut (JPMB UMMAT). | Pelatihan datang sekali lalu selesai. Setelah itu tidak ada yang bisa ditanya. |
| Pemasaran konvensional dan pembukuan lemah | Observasi di Genjahan, Ponjong, menemukan kesenjangan literasi digital dan pengelolaan keuangan UMKM (Kacanegara ITDA). UMKM Beji, Patuk, masih memasarkan secara konvensional (Jurnal Parikesit UGM). | Produk ada, pembeli sulit menemukan. Harga jual sering tidak dihitung dari biaya. |
| Akses internet tidak merata | Paparan di forum SMERU menyebut kendala akses internet di pelosok Gunungkidul. | Aplikasi harus ringan dan tetap berguna pada sinyal lemah. |
| Usaha dimulai karena terpaksa | Filosofi IPI: banyak orang baru memulai usaha saat kehilangan pekerjaan atau terdesak kebutuhan (Company Profile IPI 2026). | Usaha dibuka tanpa persiapan dan tanpa perhitungan. |

**Rumusan masalah:** warga Gunungkidul yang ingin atau sedang berusaha tidak punya pendamping yang bisa ditanya kapan saja dengan bahasa sederhana, tidak punya jalur belajar yang berlanjut setelah pelatihan, dan tidak punya tempat mudah untuk memajang produknya.

## 3. Solusi: ekosistem lima tahap

Seluruh fitur disusun sebagai satu perjalanan. Setiap tahap menghasilkan data yang dipakai tahap berikutnya, sehingga fitur tidak berdiri sendiri-sendiri.

| Tahap | Modul | Yang dilakukan warga | Hasil |
| --- | --- | --- | --- |
| 1. Kenali | Profil Usaha | Menjawab pertanyaan singkat: kapanewon, modal, keahlian, waktu luang, sudah punya usaha atau belum, bidang minat. | Profil yang mempersonalisasi semua modul lain. |
| 2. Tanya | Ruang Bos<br>Temu Bos | Bicara atau mengetik kepada Bos Allo. Jika butuh manusia, memesan sesi dengan bos asli. | Catatan konsultasi berisi inti masalah, saran, dan langkah lanjut. |
| 3. Belajar | Kelas Bos<br>Siniar Bos<br>Warung Bos 3D | Mendaftar kelas IPI dan mencatat kehadiran, mendengar siniar, bermain simulasi warung 7 hari. | Kehadiran, Sertifikat Terverifikasi, catatan belajar, rapor simulasi. |
| 4. Rancang | Peluang Usaha<br>Rencana Usaha<br>Cek Produk | Meminta rekomendasi usaha, menyusun rencana satu halaman, menghitung HPP dan titik impas, memeriksa foto produk. | Rencana usaha, daftar Langkah 30 Hari, laporan Cek Produk. |
| 5. Buka | Lapak | Membuka katalog, mengunggah produk, membagikan tautan lapak. | Halaman lapak publik dengan tombol pesan lewat WhatsApp. |

**Siklus ekosistem.** Pemilik lapak yang berjalan dapat diundang Pengelola IPI menjadi narasumber siniar, pembicara kelas, atau Bos pendamping. Warga baru belajar dari warga yang lebih dulu berhasil. Di titik ini alloBos menjadi ekosistem, bukan kumpulan fitur.

## 4. Fitur final

#### 4.1 Ruang Bos (fitur AI utama)

Konsultasi tatap muka dengan Bos Allo, maskot orisinal alloBos yang tampil besar seperti panggilan video. Tampilan memakai dua klip video (diam dan berbicara, 1280x720) yang berganti selama suara diputar, dengan bingkai menyala dan gelombang suara saat Bos Allo bicara. Warga menahan tombol mikrofon untuk bicara dan pertanyaan langsung terkirim saat tombol dilepas, atau mengetik.

Jawaban disusun Gemini, lalu dibacakan dengan suara manusia bahasa Indonesia dari Speechify (suara Bima). Teks jawaban muncul tepat saat suara mulai terdengar dan kecepatannya mengikuti panjang suara, sehingga suara dan teks selesai bersamaan. Bila Speechify tidak tersedia, peramban membacakan dengan suara Google Bahasa Indonesia. Bos Allo mengenal profil warga dan sepuluh giliran percakapan terakhir, menjawab langsung pesan terbaru, dan bertanya balik bila pesan terlalu singkat. Setiap sesi ditutup dengan Catatan Konsultasi terstruktur yang bisa diunduh PDF dan dijadikan Langkah 30 Hari.

#### 4.2 Temu Bos

Pemesanan sesi mentoring dengan pengusaha asli. Bos membuka slot waktu, warga memilih slot dan menulis topik, Bos menyetujui, sesi berlangsung daring lewat tautan atau tatap muka di lokasi IPI. Catatan konsultasi dari Ruang Bos dapat dilampirkan agar Bos tidak mulai dari nol. Setelah sesi, Bos menulis catatan dan warga memberi penilaian.

#### 4.3 Kelas Bos

Program pelatihan nyata hasil kolaborasi dengan IPI: seminar, workshop, bootcamp, dan mentoring, dikelompokkan menurut 13 bidang usaha dan 5 bidang pendukung IPI. Setiap kelas menampilkan pembicara asli, jadwal, lokasi, kuota, biaya, dan materi. Kelas bisa gratis atau berbayar.

Pendaftaran selalu lewat halaman konfirmasi (jadwal, lokasi, biaya, dan centang kesanggupan hadir). Kelas gratis langsung terdaftar. Kelas berbayar menahan kursi selama satu jam dan membuka pembayaran QRIS lewat SumoPod. Kursi baru resmi menjadi milik warga setelah SumoPod mengonfirmasi pembayaran lewat webhook yang tanda tangannya diperiksa. Pembayaran gagal atau kedaluwarsa otomatis melepas kursi. Warga hadir dengan kode kehadiran, progresnya tercatat, dan yang memenuhi syarat menerima Sertifikat Terverifikasi.

#### 4.4 Sertifikat Terverifikasi (Web3)

Setelah kelas ditutup, sistem menyusun sertifikat untuk setiap warga yang hadir minimal 75% sesi. Sertifikat ditandatangani oleh pembicara kelas dan pimpinan IPI (Deni Fuadi, pimpinan PT Institusi Pengusaha Indonesia): penanda tangan menyimpan spesimen tanda tangannya satu kali, lalu menekan satu tombol untuk menandatangani seluruh sertifikat kelas itu. Sistem membuat PDF A4 lanskap berdesain premium: bingkai ganda, ornamen sudut dan pita diagonal hijau zaitun dan lime, tanda air logo, logo alloBos di atas, medali berikon alloBos, nama penerima, kelas, tanggal, tanda tangan, kode unik, dan kode QR. Warga melihat pratinjau sertifikat di halaman sebelum mengunduh.

Sidik digital (hash) setiap sertifikat dicatat di blockchain lewat kontrak pintar. Siapa pun dapat memindai QR atau membuka halaman verifikasi untuk memastikan sertifikat asli, belum diubah, dan belum dicabut, tanpa perlu menghubungi IPI. Data pribadi tidak ditulis ke blockchain, hanya sidik digitalnya. Warga tidak perlu punya dompet kripto.

**Mengapa ini memperkuat peluang warga.** Sertifikat kertas dari pelatihan mudah dipalsukan dan sulit dicek. Sertifikat yang bisa diverifikasi siapa saja menjadi bukti kemampuan yang dipercaya calon mitra, pembeli partai besar, pengelola pasar, atau lembaga pemberi modal. Lencana sertifikat tampil di halaman Lapak dan Jejak Usaha.

#### 4.5 Siniar Bos

Kumpulan episode siniar yang bersumber dari URL YouTube. Pengelola IPI menempelkan tautan, sistem mengambil judul dan gambar sampul, lalu AI menyusun ringkasan dan tiga pelajaran utama. Warga dapat menandai episode sebagai sudah didengar dan menyimpan catatan.

#### 4.6 Warung Bos 3D

Game simulasi menjalankan warung simbah dari sudut pandang orang pertama dalam dunia 3D low-poly berbasis Three.js. Pemain berjalan di dalam warung (papan ketik dan tetikus, gamepad, atau layar sentuh) dan memakai tujuh stasiun: meja pemasok untuk belanja, rak gudang berisi peti, rak pajang, papan harga, papan pengumuman untuk promosi dan keputusan harian, papan BUKA dan TUTUP, serta kasir. Pemain membawa peti dari gudang ke rak, membuka warung, lalu melayani pembeli yang antre di kasir. Pembeli yang menunggu terlalu lama pergi, barang yang tidak ada di rak tidak bisa dibeli.

Tujuh babak bertema (hujan, pesaing baru, rombongan bus, harga bahan naik, ulasan pelanggan, hari pasaran) dengan kejadian dari AI. Seluruh perhitungan uang dilakukan di server dan tahan kiriman ulang. Mode Ringan tanpa 3D tersedia untuk ponsel berat. Di akhir, pemain menerima rapor dan tiga pelajaran dari AI.

#### 4.7 Cek Produk

Warga memotret atau mengunggah foto produk (sampai 15 MB, dikecilkan otomatis). Pemeriksaan berjalan di antrean latar sehingga halaman tidak macet, dan bila tertahan lebih dari tiga menit warga bisa Periksa Ulang tanpa mengunggah lagi. AI penglihatan mengenali produknya, menilai foto dan kemasan, menyarankan kisaran harga, dan memberi Sinyal Pasar (naik, stabil, atau turun) disertai alasan dan sumber dari penelusuran web bila fitur itu diaktifkan. Hasil diberi label jelas sebagai perkiraan AI, bukan data penjualan. Deskripsi yang dihasilkan dapat langsung dipakai di Lapak.

#### 4.8 Peluang Usaha dan Rencana Usaha

Setelah berkonsultasi dan belajar, warga meminta AI membuka peluang: tiga rekomendasi usaha yang cocok dengan profil, potensi lokal kapanewon, dan riwayat belajarnya, masing-masing dengan alasan, perkiraan modal, dan risiko. Warga memilih satu, lalu sistem menyusun Rencana Usaha satu halaman, kalkulator HPP dan titik impas, serta Langkah 30 Hari yang bisa dicentang.

#### 4.9 Lapak

Katalog milik warga dengan alamat publik. Berisi profil usaha, foto sampul, daftar produk dengan foto, harga, dan deskripsi, lencana Sertifikat Terverifikasi milik pemilik, serta tombol pesan yang membuka WhatsApp penjual dengan pesan terisi. Katalog dan detail lapak bisa dilihat siapa saja, sedangkan memesan dan melaporkan lapak perlu masuk akun supaya tercatat siapa pelakunya. Lapak baru diperiksa Pengelola IPI sebelum terbit. Warga juga bisa menjelajahi lapak warga lain untuk inspirasi. Halaman dirancang ringan agar cepat dibuka pembeli.

#### 4.10 Jejak Usaha dan personalisasi

Dashboard pribadi yang menampilkan Skor Siap Usaha 0 sampai 100, langkah berikutnya yang disarankan, agenda kelas dan sesi, serta riwayat. Skor dihitung dari bukti, bukan dari klaim pengguna.

| Komponen Skor Siap Usaha | Bobot | Sumber bukti |
| --- | --- | --- |
| Profil Usaha lengkap | 10% | Onboarding |
| Konsultasi | 15% | Sesi Ruang Bos yang selesai dan sesi Temu Bos |
| Belajar | 25% | Kehadiran Kelas Bos, Sertifikat Terverifikasi, dan episode Siniar Bos yang ditandai |
| Simulasi | 15% | Nilai rapor Warung Bos 3D terbaik |
| Rencana dan langkah | 25% | Rencana Usaha tersimpan dan persentase Langkah 30 Hari |
| Lapak | 10% | Lapak terbit dengan minimal satu produk |

## 5. Peran AI

AI wajib ada menurut ketentuan lomba, dan di alloBos AI bekerja di tujuh titik ditambah suara. Semua keluaran berbentuk JSON terstruktur dengan skema dan urutan isian yang tetap agar dapat disimpan, ditampilkan rapi, dan dihitung. Model bawaan Gemini 3.1 Flash Lite dengan lima kunci API bergiliran: kunci yang terkena batas langsung dilewati, dan bila model utama tidak tersedia, model cadangan dicoba. Ketujuh titik sudah diuji dengan Gemini sungguhan.

| Titik AI | Masukan | Keluaran | Jenis |
| --- | --- | --- | --- |
| Bos Allo | Ucapan atau teks warga, profil, riwayat sesi | Jawaban, pertanyaan lanjutan, Catatan Konsultasi | Percakapan bersuara |
| Peluang Usaha | Profil, kapanewon, riwayat belajar | 3 rekomendasi dengan alasan, modal, risiko | Rekomendasi |
| Rencana Usaha | Peluang terpilih, modal | Rencana 1 halaman, HPP awal, Langkah 30 Hari | Penyusunan dokumen |
| Cek Produk | Foto produk | Nama produk, nilai foto dan kemasan, kisaran harga, Sinyal Pasar, deskripsi | Penglihatan komputer |
| Ringkasan Siniar | URL YouTube atau catatan pengelola | Ringkasan dan 3 pelajaran | Peringkasan |
| Kejadian Warung | Profil usaha, kondisi permainan | Kejadian harian dan dampaknya | Generasi skenario |
| Rapor Warung | Catatan keputusan 7 hari | Keputusan terbaik, terburuk, 3 pelajaran | Evaluasi |
| Suara Bos Allo | Teks jawaban Bos Allo | Berkas suara MP3 bahasa Indonesia (Speechify, suara Bima) | Teks ke suara |

### 5.1 Peran Web3

Web3 dipakai untuk satu hal yang memang membutuhkannya: bukti yang dapat diperiksa pihak luar tanpa harus percaya pada data di server alloBos. Kontrak pintar AlloBosCertificate (Solidity) menyimpan sidik digital sertifikat, penerbit, waktu terbit, dan status cabut. Halaman verifikasi membandingkan sidik digital sertifikat dengan catatan di blockchain.

Pada masa lomba pencatatan memakai Jaringan Uji alloBos, jaringan blockchain uji milik sendiri (Anvil) yang berjalan di server dan datanya tetap tersimpan walau server dinyalakan ulang. Pilihan ini diambil karena faucet jaringan uji publik Polygon Amoy sekarang mensyaratkan saldo ETH jaringan utama, sehingga tidak bisa gratis. Kode dan pengaturannya sudah siap pindah ke Polygon Amoy atau Polygon utama cukup dengan mengganti alamat RPC dan kunci penerbit.

### 5.2 Teknologi dan layanan yang dipakai

| Kebutuhan | Teknologi atau layanan | Keterangan |
| --- | --- | --- |
| Kerangka aplikasi | Laravel 12 (PHP 8.4), Blade, Livewire 3, Alpine | Controller tipis, service per domain, enum status, policy |
| Tampilan | Tailwind CSS 3 dengan token warna, Vite, Montserrat dan Ubuntu, ikon Phosphor | Ramah ponsel mulai 360 px, tema terang dan gelap |
| Basis data dan antrean | MySQL 8.4, antrean basis data Laravel, penjadwal | Pekerjaan AI, PDF, email, dan pencatatan blockchain berjalan di latar |
| Kecerdasan buatan | Google Gemini API (Gemini 3.1 Flash Lite, cadangan Flash Lite Latest dan Flash Latest) | Lima kunci bergiliran, keluaran JSON berskema, penglihatan untuk Cek Produk, penelusuran web untuk Sinyal Pasar |
| Suara Bos Allo | Speechify Text to Speech API (model simba-3.5, suara Bima) | Suara disimpan per teks supaya Ulangi Jawaban tidak memakai kuota lagi |
| Pengenalan suara warga | Web Speech API di peramban (id-ID) | Tahan untuk bicara, lepas untuk mengirim |
| Email | Resend SMTP, pengirim noreply@zite.my.id | Verifikasi email, pemberitahuan kelas, pembayaran, dan sesi |
| Pembayaran | SumoPod Managed Payment, QRIS (sandbox pada masa lomba) | Webhook diverifikasi dengan token dan tanda tangan Svix (HMAC SHA-256) |
| Blockchain | Solidity, Hardhat (uji kontrak), ethers.js, Anvil (Foundry) | Jaringan Uji alloBos di server, siap pindah ke Polygon Amoy |
| Game 3D | Three.js, Gamepad API, Pointer Lock API | Dunia warung dibangun dari geometri di kode, tanpa model luar |
| Dokumen | dompdf (PDF), endroid/qr-code (QR), PhpSpreadsheet (XLSX) | Sertifikat, Catatan Konsultasi, Rencana Usaha, rekap peserta |
| Grafik | Chart.js | Wawasan Pengelola dan Jejak Usaha |
| Siniar | YouTube oEmbed dan pemutar youtube-nocookie | Judul dan sampul otomatis, video diringkas Gemini |
| Infrastruktur | Docker Compose (app, queue, scheduler, mysql, chain), VPS Ubuntu, nginx, Let's Encrypt | Domain allobos.zite.my.id dengan HTTPS |
| Kualitas | PHPUnit (lebih dari 99 tes), Pint, GitHub Actions | Tes untuk tiap alur utama, AI dan blockchain dipalsukan di tes |

## 6. Pengguna

| Peran | Siapa | Yang dilakukan |
| --- | --- | --- |
| Warga | Warga Gunungkidul yang ingin atau sedang berusaha | Konsultasi, belajar, bermain simulasi, merancang usaha, membuka lapak |
| Bos | Pengusaha aktif yang dikurasi IPI | Membuka slot, menerima sesi, menulis catatan, mengisi kelas, menandatangani sertifikat kelas yang ia isi |
| Pengelola IPI | Tim Institusi Pengusaha Indonesia | Mengelola kelas, pembicara, siniar, kurasi Bos, moderasi lapak, menetapkan penanda tangan, menandatangani atas nama pimpinan IPI, mencabut sertifikat |
| Admin | Pengelola sistem | Pengguna, pengaturan AI dan prompt, halaman publik, audit |
| Pengunjung | Siapa pun tanpa akun | Melihat beranda, kelas, siniar, bos, dan lapak, serta memverifikasi sertifikat. Memesan dan melaporkan lapak perlu masuk akun |

## 7. Peran mitra IPI

- **Sumber pengetahuan.** Kerangka 13 bidang usaha dan 5 bidang pendukung IPI menjadi taksonomi kelas, rekomendasi, dan bahan Bos Allo.
- **Penyedia pelatihan nyata.** Kelas Bos memuat program IPI yang sungguh diselenggarakan, dengan pusat bootcamp di Resort Villa Satu Indonesia. Kelas berbayar menjadi sumber pemasukan yang menjaga program tetap berjalan.
- **Penanda tangan sertifikat.** Pimpinan PT Institusi Pengusaha Indonesia, Deni Fuadi, menandatangani sertifikat bersama pembicara kelas lewat akunnya sendiri.
- **Kurator Bos.** Standar kurasi IPI dipakai untuk menyetujui Bos: bisnis aktif, rekam jejak jelas, pengalaman riil, dapat ditelusuri.
- **Penerima manfaat tetap warga.** IPI adalah mitra pelaksana. Halaman dan teks alloBos berbicara kepada warga, bukan mempromosikan lembaga.

## 8. Pembeda

| Dibanding | Yang berbeda di alloBos |
| --- | --- |
| Chatbot AI umum | Mengenal profil dan kapanewon warga, berbicara dengan suara, menghasilkan dokumen terstruktur, dan terhubung ke bos asli serta kelas nyata. |
| Pelatihan UMKM sekali datang | Pendampingan berlanjut setiap hari lewat Bos Allo, Langkah 30 Hari, dan Skor Siap Usaha. |
| Sertifikat pelatihan biasa | Ditandatangani pembicara, tercatat di blockchain, dan dapat diverifikasi siapa pun lewat QR. |
| Marketplace besar | Lapak sederhana tanpa biaya dan tanpa syarat rumit, dipesan lewat WhatsApp yang sudah dipakai warga. |
| Pendaftaran pelatihan manual | Kuota, pembayaran QRIS, kehadiran, dan sertifikat tercatat otomatis dalam satu alur. |
| Aplikasi belajar bisnis | Ada latihan aman lewat simulasi warung 3D sebelum mempertaruhkan modal nyata. |

## 9. Kesesuaian dengan kriteria IT RUSH 2026

| Kriteria | Bobot | Jawaban alloBos |
| --- | --- | --- |
| Fungsionalitas dan kualitas teknis | 25% | Aplikasi sudah berjalan di https://allobos.zite.my.id. Seluruh alur dapat dicoba juri sendiri dengan akun demo tiap peran. AI, suara, email, pembayaran QRIS sandbox, dan pencatatan blockchain terhubung ke layanan sungguhan. |
| Inovasi dan ketepatan solusi | 20% | Bos virtual bersuara untuk warga berpendidikan rendah, simulasi warung 3D, sertifikat kelas yang terverifikasi di blockchain, dan keluaran AI terstruktur yang menyambung sampai lapak. |
| Relevansi tema dan dampak | 15% | Subtema 3. Masalah berbasis data Gunungkidul, mitra pelaksana nyata. |
| UI dan UX | 15% | Arah visual mengikuti referensi Dribbble yang ditetapkan, diterapkan dengan taste skill, ramah ponsel. |
| Kualitas source code | 15% | Arsitektur berlapis: controller tipis, service per domain, enum status, satu pintu AI (GeminiClient), satu pintu blockchain (ChainClient), lebih dari 99 tes otomatis, Pint, dan CI. |
| Dokumentasi | 10% | Proposal, video demo 3 sampai 5 menit, README berisi akun demo dan daftar pemakaian AI coding assistant. |

## 10. Batasan dan etika

- Sinyal Pasar dan rekomendasi usaha adalah perkiraan AI. Label ini selalu tampil di samping hasilnya.
- Tidak ada statistik, testimoni, atau nama pembicara karangan. Angka di halaman publik dihitung dari basis data.
- Data contoh dicatat di README dan basis data (penanda is_demo). Lencana Demo di antarmuka bisa dinyalakan dengan APP_DEMO=true.
- Bos Allo adalah pendamping virtual berbasis AI dengan karakter orisinal yang tidak meniru orang nyata.
- Tanda tangan orang sungguhan tidak pernah dibuat-buat oleh sistem. Pimpinan IPI menandatangani sendiri dari akunnya.
- Pembayaran hanya dinyatakan lunas oleh webhook SumoPod yang tanda tangannya cocok dan jumlahnya sesuai, bukan oleh halaman kembali di peramban.
- Bos Allo tidak memberi nasihat hukum, pajak, atau pinjaman secara pasti. Untuk hal itu ia menyarankan Temu Bos atau lembaga resmi.
- Data pribadi warga disamarkan sebelum dikirim ke layanan AI.
- Blockchain hanya menyimpan sidik digital sertifikat. Nama dan data pribadi tetap di server alloBos.
- Pada masa lomba pencatatan memakai jaringan uji tanpa biaya (Jaringan Uji alloBos). Halaman verifikasi menyebut nama jaringan yang dipakai secara terbuka.

## 11. Hal yang perlu dikonfirmasi

- Izin tertulis IPI untuk pemakaian nama, logo, daftar program, dan nama pembicara.
- Sudah: aset Bos Allo (dua klip diam dan berbicara, enam pose transparan, logo alloBos).
- Sudah: sertifikat ditandatangani pembicara kelas dan pimpinan IPI (Deni Fuadi).
- Sudah: jaringan blockchain (Jaringan Uji alloBos di server). Pindah ke Polygon Amoy menunggu dompet dengan saldo POL uji.
- Sudah: layanan pihak ketiga terpasang di server (Gemini, Speechify, Resend, SumoPod sandbox).
- Pindah SumoPod dari sandbox ke mode live setelah pendaftaran Live Access disetujui.
- Status kepesertaan mahasiswa D3 pada ketentuan peserta butir IV.4 ke narahubung lomba.
- Angka resmi jumlah UMKM dan kemiskinan Gunungkidul dari BPS untuk proposal.

## 12. Sumber

- Guidebook Web Development IT RUSH 2026.
- Company Profile Institusi Pengusaha Indonesia 2026.
- Databoks Katadata, Rata-rata Lama Sekolah di Gunung Kidul (data BPS 2025).
- Jurnal Parikesit UGM, pengabdian UMKM Kalurahan Beji, Patuk, Gunungkidul.
- JPMB UMMAT, digitalisasi pemasaran UMKM Desa Kepek, Wonosari.
- Kacanegara ITDA, pelatihan digital marketing dan keuangan UMKM Genjahan, Ponjong.
- SMERU, paparan pengentasan kemiskinan melalui pemberdayaan UMKM di DIY.
