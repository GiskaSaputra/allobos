# Konsep Ide alloBos

Pendamping usaha berbasis AI untuk warga Gunungkidul, dari bertanya sampai membuka lapak

- **Lomba:** IT RUSH 2026, Web Development (HIMATIF UNIB)
- **Subtema:** 3. Ekonomi dan Pemberdayaan Masyarakat
- **Mitra:** Institusi Pengusaha Indonesia (IPI), Gunungkidul, DIY
- **Penyusun:** Giska Saputra
- **Versi:** Final 1.1, 17 November 2026 (menambah Sertifikat Terverifikasi berbasis Web3)
- **Dokumen terkait:** PRD, Business Flow, Design System

## 1. Ringkasan

alloBos adalah aplikasi web yang mendampingi warga Gunungkidul membangun usaha, dari tahap belum tahu mau usaha apa sampai punya lapak online yang bisa dipesan. Polanya seperti layanan konsultasi dokter daring, tetapi untuk urusan usaha: warga bertanya kepada Bos Allo (bos virtual berbasis AI yang bisa diajak bicara), lalu bila perlu bertemu bos asli, yaitu pengusaha aktif yang dikurasi Institusi Pengusaha Indonesia (IPI).

Konsultasi hanya pintu masuk. Di belakangnya ada kelas pelatihan nyata dari IPI dengan sertifikat yang ditandatangani pembicara dan tercatat di blockchain, siniar dari YouTube, simulasi warung 3D untuk berlatih mengambil keputusan, pemeriksaan produk lewat foto, penemu peluang usaha, dan katalog lapak. Semua aktivitas itu tercatat sebagai Jejak Usaha dan dihitung menjadi Skor Siap Usaha.

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

Konsultasi tatap muka dengan Bos Allo, maskot orisinal alloBos. Tampilan memakai dua GIF, diam dan berbicara, dengan pola yang sama seperti Vira dan Vito di proyek acuan: GIF berganti ke versi berbicara selama suara diputar. Warga dapat berbicara lewat mikrofon atau mengetik. Jawaban dibacakan dengan suara dan ditampilkan sebagai teks. Bos Allo mengenal profil warga, sehingga saran menyebut modal, lokasi, dan bidang usaha orang itu. Setiap sesi ditutup dengan Catatan Konsultasi terstruktur.

#### 4.2 Temu Bos

Pemesanan sesi mentoring dengan pengusaha asli. Bos membuka slot waktu, warga memilih slot dan menulis topik, Bos menyetujui, sesi berlangsung daring lewat tautan atau tatap muka di lokasi IPI. Catatan konsultasi dari Ruang Bos dapat dilampirkan agar Bos tidak mulai dari nol. Setelah sesi, Bos menulis catatan dan warga memberi penilaian.

#### 4.3 Kelas Bos

Program pelatihan nyata hasil kolaborasi dengan IPI: seminar, workshop, bootcamp, dan mentoring, dikelompokkan menurut 13 bidang usaha dan 5 bidang pendukung IPI. Setiap kelas menampilkan pembicara asli, jadwal, lokasi, kuota, dan materi. Warga mendaftar, hadir dengan kode kehadiran, dan progresnya tercatat. Warga yang memenuhi syarat hadir menerima Sertifikat Terverifikasi.

#### 4.4 Sertifikat Terverifikasi (Web3)

Setelah kelas ditutup, sistem menyusun sertifikat untuk setiap warga yang hadir minimal 75% sesi. Sertifikat ditandatangani oleh pembicara kelas atau pimpinan IPI: penanda tangan menyimpan spesimen tanda tangannya satu kali, lalu menekan satu tombol untuk menandatangani seluruh sertifikat kelas itu. Sistem membuat PDF berisi nama, kelas, tanggal, tanda tangan, kode unik, dan kode QR.

Sidik digital (hash) setiap sertifikat dicatat di blockchain lewat kontrak pintar. Siapa pun dapat memindai QR atau membuka halaman verifikasi untuk memastikan sertifikat asli, belum diubah, dan belum dicabut, tanpa perlu menghubungi IPI. Data pribadi tidak ditulis ke blockchain, hanya sidik digitalnya. Warga tidak perlu punya dompet kripto.

**Mengapa ini memperkuat peluang warga.** Sertifikat kertas dari pelatihan mudah dipalsukan dan sulit dicek. Sertifikat yang bisa diverifikasi siapa saja menjadi bukti kemampuan yang dipercaya calon mitra, pembeli partai besar, pengelola pasar, atau lembaga pemberi modal. Lencana sertifikat tampil di halaman Lapak dan Jejak Usaha.

#### 4.5 Siniar Bos

Kumpulan episode siniar yang bersumber dari URL YouTube. Pengelola IPI menempelkan tautan, sistem mengambil judul dan gambar sampul, lalu AI menyusun ringkasan dan tiga pelajaran utama. Warga dapat menandai episode sebagai sudah didengar dan menyimpan catatan.

#### 4.6 Warung Bos 3D

Game simulasi menjalankan warung dalam tampilan 3D low-poly berbasis Three.js. Pemain mengelola warung selama tujuh hari permainan: belanja stok, menentukan harga, memilih promosi, lalu melihat pelanggan datang. AI memunculkan kejadian yang sesuai dengan profil usaha pemain, misalnya hujan seharian, pesaing baru, atau pesanan besar. Perhitungan laba dilakukan di server. Di akhir, pemain menerima rapor dan tiga pelajaran dari AI.

#### 4.7 Cek Produk

Warga mengunggah foto produk. AI penglihatan mengenali produknya, menilai foto dan kemasan, menyarankan kisaran harga, dan memberi Sinyal Pasar (naik, stabil, atau turun) disertai alasan dan sumber dari penelusuran web bila fitur itu diaktifkan. Hasil diberi label jelas sebagai perkiraan AI, bukan data penjualan. Deskripsi yang dihasilkan dapat langsung dipakai di Lapak.

#### 4.8 Peluang Usaha dan Rencana Usaha

Setelah berkonsultasi dan belajar, warga meminta AI membuka peluang: tiga rekomendasi usaha yang cocok dengan profil, potensi lokal kapanewon, dan riwayat belajarnya, masing-masing dengan alasan, perkiraan modal, dan risiko. Warga memilih satu, lalu sistem menyusun Rencana Usaha satu halaman, kalkulator HPP dan titik impas, serta Langkah 30 Hari yang bisa dicentang.

#### 4.9 Lapak

Katalog milik warga dengan alamat publik. Berisi profil usaha, daftar produk dengan foto, harga, dan deskripsi, lencana Sertifikat Terverifikasi milik pemilik, serta tombol pesan yang membuka WhatsApp penjual dengan pesan terisi. Halaman dirancang ringan agar cepat dibuka pembeli.

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

AI wajib ada menurut ketentuan lomba, dan di alloBos AI bekerja di tujuh titik. Semua keluaran berbentuk JSON terstruktur agar dapat disimpan, ditampilkan rapi, dan dihitung.

| Titik AI | Masukan | Keluaran | Jenis |
| --- | --- | --- | --- |
| Bos Allo | Ucapan atau teks warga, profil, riwayat sesi | Jawaban, pertanyaan lanjutan, Catatan Konsultasi | Percakapan bersuara |
| Peluang Usaha | Profil, kapanewon, riwayat belajar | 3 rekomendasi dengan alasan, modal, risiko | Rekomendasi |
| Rencana Usaha | Peluang terpilih, modal | Rencana 1 halaman, HPP awal, Langkah 30 Hari | Penyusunan dokumen |
| Cek Produk | Foto produk | Nama produk, nilai foto dan kemasan, kisaran harga, Sinyal Pasar, deskripsi | Penglihatan komputer |
| Ringkasan Siniar | URL YouTube atau catatan pengelola | Ringkasan dan 3 pelajaran | Peringkasan |
| Kejadian Warung | Profil usaha, kondisi permainan | Kejadian harian dan dampaknya | Generasi skenario |
| Rapor Warung | Catatan keputusan 7 hari | Keputusan terbaik, terburuk, 3 pelajaran | Evaluasi |

### 5.1 Peran Web3

Web3 dipakai untuk satu hal yang memang membutuhkannya: bukti yang dapat diperiksa pihak luar tanpa harus percaya pada server alloBos. Kontrak pintar menyimpan sidik digital sertifikat, penerbit, waktu terbit, dan status cabut. Halaman verifikasi membandingkan sidik digital sertifikat dengan catatan di blockchain.

## 6. Pengguna

| Peran | Siapa | Yang dilakukan |
| --- | --- | --- |
| Warga | Warga Gunungkidul yang ingin atau sedang berusaha | Konsultasi, belajar, bermain simulasi, merancang usaha, membuka lapak |
| Bos | Pengusaha aktif yang dikurasi IPI | Membuka slot, menerima sesi, menulis catatan, mengisi kelas, menandatangani sertifikat kelas yang ia isi |
| Pengelola IPI | Tim Institusi Pengusaha Indonesia | Mengelola kelas, pembicara, siniar, kurasi Bos, moderasi lapak, menetapkan penanda tangan, menandatangani atas nama pimpinan IPI, mencabut sertifikat |
| Admin | Pengelola sistem | Pengguna, pengaturan AI dan prompt, halaman publik, audit |
| Pengunjung | Siapa pun tanpa akun | Melihat beranda, kelas, siniar, dan lapak, memesan lewat WhatsApp, memverifikasi sertifikat |

## 7. Peran mitra IPI

- **Sumber pengetahuan.** Kerangka 13 bidang usaha dan 5 bidang pendukung IPI menjadi taksonomi kelas, rekomendasi, dan bahan Bos Allo.
- **Penyedia pelatihan nyata.** Kelas Bos memuat program IPI yang sungguh diselenggarakan, dengan pusat bootcamp di Resort Villa Satu Indonesia.
- **Kurator Bos.** Standar kurasi IPI dipakai untuk menyetujui Bos: bisnis aktif, rekam jejak jelas, pengalaman riil, dapat ditelusuri.
- **Penerima manfaat tetap warga.** IPI adalah mitra pelaksana. Halaman dan teks alloBos berbicara kepada warga, bukan mempromosikan lembaga.

## 8. Pembeda

| Dibanding | Yang berbeda di alloBos |
| --- | --- |
| Chatbot AI umum | Mengenal profil dan kapanewon warga, berbicara dengan suara, menghasilkan dokumen terstruktur, dan terhubung ke bos asli serta kelas nyata. |
| Pelatihan UMKM sekali datang | Pendampingan berlanjut setiap hari lewat Bos Allo, Langkah 30 Hari, dan Skor Siap Usaha. |
| Sertifikat pelatihan biasa | Ditandatangani pembicara, tercatat di blockchain, dan dapat diverifikasi siapa pun lewat QR. |
| Marketplace besar | Lapak sederhana tanpa biaya dan tanpa syarat rumit, dipesan lewat WhatsApp yang sudah dipakai warga. |
| Aplikasi belajar bisnis | Ada latihan aman lewat simulasi warung 3D sebelum mempertaruhkan modal nyata. |

## 9. Kesesuaian dengan kriteria IT RUSH 2026

| Kriteria | Bobot | Jawaban alloBos |
| --- | --- | --- |
| Fungsionalitas dan kualitas teknis | 25% | Seluruh alur dapat dicoba juri sendiri dengan akun demo empat peran. Fitur yang butuh manusia (Temu Bos, Kelas Bos) memiliki data nyata dari IPI dan data contoh berlabel Demo. |
| Inovasi dan ketepatan solusi | 20% | Bos virtual bersuara untuk warga berpendidikan rendah, simulasi warung 3D, sertifikat kelas yang terverifikasi di blockchain, dan keluaran AI terstruktur yang menyambung sampai lapak. |
| Relevansi tema dan dampak | 15% | Subtema 3. Masalah berbasis data Gunungkidul, mitra pelaksana nyata. |
| UI dan UX | 15% | Arah visual mengikuti referensi Dribbble yang ditetapkan, diterapkan dengan taste skill, ramah ponsel. |
| Kualitas source code | 15% | Arsitektur berlapis dari proyek acuan: controller tipis, service per domain, enum status, satu pintu AI, tes otomatis. |
| Dokumentasi | 10% | Proposal, video demo 3 sampai 5 menit, README berisi akun demo dan daftar pemakaian AI coding assistant. |

## 10. Batasan dan etika

- Sinyal Pasar dan rekomendasi usaha adalah perkiraan AI. Label ini selalu tampil di samping hasilnya.
- Tidak ada statistik, testimoni, atau nama pembicara karangan. Angka di halaman publik dihitung dari basis data.
- Data contoh diberi lencana Demo di antarmuka dan dicatat di README.
- Bos Allo adalah karakter fiktif dan tidak meniru orang nyata.
- Bos Allo tidak memberi nasihat hukum, pajak, atau pinjaman secara pasti. Untuk hal itu ia menyarankan Temu Bos atau lembaga resmi.
- Data pribadi warga disamarkan sebelum dikirim ke layanan AI.
- Blockchain hanya menyimpan sidik digital sertifikat. Nama dan data pribadi tetap di server alloBos.
- Pada masa lomba pencatatan memakai jaringan uji (testnet) tanpa biaya. Halaman verifikasi menyebut nama jaringan yang dipakai secara terbuka.

## 11. Hal yang perlu dikonfirmasi

- Izin tertulis IPI untuk pemakaian nama, logo, daftar program, dan nama pembicara.
- Aset dua GIF Bos Allo (diam dan berbicara) dengan latar transparan.
- Siapa yang menandatangani sertifikat tiap kelas: pembicara, pimpinan IPI, atau keduanya.
- Jaringan blockchain yang dipakai dan dompet penerbit beserta saldo jaringan ujinya.
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
