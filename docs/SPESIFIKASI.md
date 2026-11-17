# Product Requirements Document

Kebutuhan produk alloBos secara rinci: modul, kebutuhan fungsional, AI, data, dan kriteria terima

- **Produk:** alloBos, pendamping usaha berbasis AI untuk warga Gunungkidul
- **Lomba:** IT RUSH 2026, Web Development, Subtema 3
- **Penyusun:** Giska Saputra
- **Versi:** Final 2.0, 18 November 2026 (menyesuaikan sistem yang sudah berjalan)
- **Aplikasi berjalan:** https://allobos.zite.my.id
- **Arsitektur acuan:** github.com/GiskaSaputra/pnc-elevate

## 1. Tujuan produk

- **T1.** Warga dapat berkonsultasi soal usaha kapan saja dengan suara atau teks dan menerima saran yang sesuai profilnya.
- **T2.** Warga punya jalur belajar yang berlanjut: kelas nyata, siniar, dan simulasi.
- **T3.** Warga pulang membawa dokumen yang bisa dipakai: rencana usaha, hitungan HPP, dan daftar langkah.
- **T4.** Warga dapat memajang produknya dan menerima pesanan tanpa keahlian teknis.
- **T5.** IPI dan para Bos dapat mendampingi lebih banyak warga dengan data, bukan perkiraan.
- **T6.** Warga memegang bukti pelatihan yang dapat diverifikasi pihak luar tanpa menghubungi penyelenggara.

### 1.1 Ukuran keberhasilan

| Metrik | Cara hitung | Sasaran pada demo |
| --- | --- | --- |
| Alur utama tuntas | Akun demo warga menempuh Kenali sampai Buka tanpa galat | 100% langkah lulus uji manual |
| Waktu jawaban Bos Allo | Dari kirim sampai teks jawaban tampil | Di bawah 6 detik pada koneksi normal |
| Keluaran AI valid | Jawaban AI lolos validasi skema JSON | Di atas 95%, sisanya ditangani cadangan |
| Halaman lapak ringan | Ukuran transfer halaman lapak tanpa gambar produk | Di bawah 300 KB |
| Verifikasi sertifikat | Sertifikat demo dibuka dari QR | Status Asli tampil beserta hash transaksi, nomor blok, dan alamat kontrak |
| Pembayaran kelas | Pembayaran QRIS sandbox dikonfirmasi webhook SumoPod | Pendaftaran berubah dari Menunggu Pembayaran menjadi Terdaftar tanpa campur tangan manual |
| Suara selaras teks | Suara Bos Allo dan teks jawaban | Teks muncul saat suara mulai terdengar dan selesai bersamaan |
| Skor Siap Usaha konsisten | Data demo dihitung dengan rumus produksi | Tes otomatis menegaskan nilai awal dan akhir |

## 2. Pengguna dan peran

| Peran | Kebutuhan utama | Hak akses ringkas |
| --- | --- | --- |
| Pengunjung | Melihat isi, kelas, lapak, dan memeriksa sertifikat | Halaman publik saja. Memesan dan melaporkan lapak perlu masuk akun |
| Warga | Bertanya, belajar, merancang, berjualan | Semua modul warga dan data miliknya sendiri |
| Bos | Mendampingi tanpa repot administrasi | Slot, sesi, catatan, profil Bos, kelas yang ia isi, tanda tangan sertifikat |
| Pengelola IPI | Mengatur program dan menjaga mutu | Kelas, pembicara, siniar, kurasi Bos, moderasi lapak, sertifikat, wawasan |
| Admin | Menjaga sistem | Semua halaman Pengelola ditambah pengguna, AI, CMS, audit |

Satu akun boleh memegang lebih dari satu peran dan berpindah lewat pemilih peran, mengikuti pola proyek acuan.

### 2.1 Persona

- **Bu Sri, 41 tahun, Ponjong.** Membuat keripik singkong di rumah, menjual titip warung. Lulusan SMP, memakai ponsel Android untuk WhatsApp. Ingin tahu harga jual yang pantas dan cara menjual lebih jauh.
- **Mas Dimas, 23 tahun, Wonosari.** Lulus SMK, belum bekerja, punya tabungan kecil. Ingin usaha tetapi tidak tahu usaha apa yang cocok.
- **Pak Arif, pengusaha penginapan.** Bos yang dikurasi IPI. Bersedia mendampingi dua jam seminggu asalkan jadwal dan catatannya rapi.
- **Mbak Rani, tim IPI.** Mengatur kelas tiap angkatan dan perlu tahu topik apa yang paling dibutuhkan warga.
- **Pak Deni Fuadi, pimpinan PT Institusi Pengusaha Indonesia.** Menandatangani sertifikat kelas atas nama IPI dan ingin sertifikat itu dipercaya pihak luar.

## 3. Ruang lingkup

### 3.1 Termasuk

- Dua belas modul: Profil Usaha, Ruang Bos, Temu Bos, Kelas Bos, Pembayaran Kelas, Sertifikat Terverifikasi, Siniar Bos, Warung Bos 3D, Cek Produk, Peluang dan Rencana Usaha, Lapak, Jejak Usaha.
- Layanan pihak ketiga: Google Gemini (AI), Speechify (suara Bos Allo), Resend (email), SumoPod (pembayaran QRIS), jaringan blockchain uji untuk sertifikat.
- Dashboard untuk Bos, Pengelola IPI, dan Admin.
- Halaman publik: beranda, kelas, siniar, direktori lapak, halaman lapak, verifikasi sertifikat, tentang, bantuan, privasi, syarat.
- Data demo untuk empat peran dan satu skenario warga lengkap.

### 3.2 Tidak termasuk

- Pembayaran pesanan lapak di dalam aplikasi. Pesanan lapak diselesaikan antara pembeli dan penjual lewat WhatsApp. Biaya kelas berbayar termasuk ruang lingkup dan dibayar lewat QRIS.
- Panggilan video bawaan. Sesi daring memakai tautan rapat dari Bos.
- Aplikasi ponsel bawaan. Web dibuat responsif.
- Pengiriman dan pelacakan paket.
- Dompet kripto untuk warga, token, NFT yang diperjualbelikan, atau transaksi berbayar di blockchain.

## 4. Kebutuhan fungsional

### 4.1 Akun dan Profil Usaha

| ID | Kebutuhan | Kriteria terima |
| --- | --- | --- |
| AK-01 | Pendaftaran dengan nama, nomor WhatsApp, email, kata sandi, dan persetujuan privasi. | Akun dibuat dengan peran Warga dan diarahkan ke onboarding. |
| AK-02 | Masuk, keluar, lupa sandi, verifikasi email. | Email dikirim lewat Resend SMTP (noreply@zite.my.id). Halaman masuk dan daftar memakai panel gambar tetap dan hanya kolom formulir yang bergulir. |
| AK-03 | Onboarding lima langkah: kapanewon, status usaha, bidang minat (13 bidang IPI), modal dan waktu luang, keahlian. | Tiap langkah tersimpan. Modul warga terkunci sampai onboarding selesai. |
| AK-04 | Profil dapat diubah kapan saja. | Perubahan memengaruhi rekomendasi berikutnya, riwayat lama tidak berubah. |
| AK-05 | Pemilih peran untuk akun multi-peran. | Menu dan dashboard berganti sesuai peran aktif. |
| AK-06 | Kembali ke halaman asal setelah masuk atau daftar (parameter tujuan). | Hanya jalur internal yang diterima supaya tidak bisa dipakai mengalihkan ke situs lain. |

### 4.2 Ruang Bos

| ID | Kebutuhan | Kriteria terima |
| --- | --- | --- |
| RB-01 | Halaman penyiapan sesi: pilih topik (bebas atau dari daftar), pilih mode suara atau teks. | Sesi dibuat dengan status Disiapkan dan kuota harian berkurang saat sesi dimulai. |
| RB-02 | Tampilan Bos Allo besar seperti panggilan video dengan dua klip: diam dan berbicara (MP4 1280x720). | Klip berbicara tampil selama suara diputar. Bingkai menyala dan gelombang suara bergerak saat Bos Allo bicara. Gerak dikurangi menampilkan gambar diam. |
| RB-03 | Masukan suara memakai pengenalan ucapan peramban, bahasa Indonesia. Tahan tombol mikrofon untuk bicara. | Saat tombol dilepas, pertanyaan langsung terkirim tanpa menekan Kirim. Transkrip tampil saat warga berbicara. |
| RB-04 | Jawaban dibacakan dengan suara Speechify (suara pria bahasa Indonesia Bima, model simba-3.5) dan ditampilkan sebagai teks. | Cadangan: suara Google Bahasa Indonesia dari peramban. Tombol bisukan menghentikan suara tanpa menghilangkan teks. |
| RB-05 | Bos Allo memakai profil warga dan sepuluh giliran terakhir dalam sesi. | Jawaban langsung menjawab pesan terbaru. Profil hanya dipakai bila relevan. Pesan terlalu singkat dijawab dengan pertanyaan balik beserta contoh pilihan. |
| RB-06 | Cadangan bila peramban tidak mendukung suara. | Mode teks aktif otomatis dan pemberitahuan menjelaskan alasannya. |
| RB-07 | Mengakhiri sesi menghasilkan Catatan Konsultasi: inti masalah, saran utama, langkah lanjut, modul yang disarankan. | Catatan tersimpan, dapat dibuka dari riwayat, dan diunduh sebagai PDF. |
| RB-08 | Langkah lanjut dapat ditambahkan ke Langkah 30 Hari. | Satu klik menyalin langkah ke daftar warga. |
| RB-09 | Batas keamanan: tidak memberi kepastian hukum, pajak, atau pinjaman. | Untuk topik itu Bos Allo menyarankan Temu Bos atau lembaga resmi. |
| RB-10 | Kuota harian per warga dan batas laju per menit. | Saat kuota habis, pesan menyebut kapan bisa dipakai lagi. |
| RB-11 | Suara dan teks selaras. | Status Berpikir tampil sampai suara siap. Teks mulai muncul tepat saat suara terdengar dengan kecepatan yang mengikuti durasi suara. Suara yang diputar selalu milik jawaban terbaru. |
| RB-12 | Peramban menahan suara sebelum ada interaksi. | Sapaan pertama memunculkan tombol Dengarkan Bos Allo di atas video. |
| RB-13 | Suara disimpan per teks. | Ulangi Jawaban tidak membuat ulang suara dan tidak memakai kuota layanan suara lagi. |

### 4.3 Temu Bos

| ID | Kebutuhan | Kriteria terima |
| --- | --- | --- |
| TB-01 | Direktori Bos dengan bidang usaha, kapanewon, dan penilaian. | Dapat disaring menurut bidang. Hanya Bos berstatus Aktif yang tampil. |
| TB-02 | Profil Bos: usaha yang dijalankan, bidang, slot tersedia. | Slot yang sudah lewat atau terisi tidak dapat dipilih. |
| TB-03 | Pemesanan slot dengan topik dan lampiran Catatan Konsultasi opsional. | Sesi berstatus Menunggu dan Bos menerima notifikasi. |
| TB-04 | Bos menyetujui atau menolak dengan alasan. | Warga menerima notifikasi. Slot yang ditolak terbuka lagi. |
| TB-05 | Sesi daring memakai tautan rapat dari Bos, sesi tatap muka memakai alamat. | Tautan hanya terlihat oleh kedua pihak setelah disetujui. |
| TB-06 | Setelah sesi, Bos menulis catatan dan saran langkah. | Sesi berstatus Selesai dan catatan tampil di riwayat warga. |
| TB-07 | Warga memberi penilaian 1 sampai 5 dan komentar. | Satu penilaian per sesi. Rata-rata tampil di profil Bos. |
| TB-08 | Pembatalan oleh warga atau Bos sebelum waktu sesi. | Slot terbuka lagi dan pihak lain diberi tahu. |

### 4.4 Kelas Bos

| ID | Kebutuhan | Kriteria terima |
| --- | --- | --- |
| KB-01 | Daftar kelas publik dengan jenis (seminar, workshop, bootcamp, mentoring), bidang, tanggal, lokasi, kuota. | Dapat disaring menurut bidang dan jenis. Kelas lampau masuk arsip. |
| KB-02 | Detail kelas: deskripsi, pembicara, jadwal sesi, materi, sisa kuota. | Pembicara menampilkan nama, usaha, dan foto yang diisi Pengelola. |
| KB-03 | Pendaftaran kelas oleh warga lewat halaman konfirmasi: jadwal sesi, lokasi, sisa kuota, biaya, dan centang kesanggupan hadir. | Tanpa centang, pendaftaran ditolak. Pendaftaran ditolak bila kuota penuh atau warga sudah terdaftar. Kelas gratis langsung Terdaftar. |
| KB-04 | Pencatatan kehadiran dengan kode per sesi. | Kode salah atau di luar waktu sesi ditolak dengan pesan yang jelas. |
| KB-05 | Progres per kelas: sesi dihadiri dan materi dibuka. | Persentase tampil di Kelas Saya dan masuk Skor Siap Usaha. |
| KB-06 | Pengelola membuat, mengubah, menerbitkan, dan menutup kelas. | Kelas berstatus Draf tidak tampil di publik. |
| KB-07 | Pengelola mengelola pembicara dan mengunduh daftar peserta. | Unduhan berformat XLSX. |
| KB-08 | Pengelola menetapkan satu atau dua penanda tangan sertifikat per kelas: pembicara (akun Bos) atau pimpinan IPI (akun Pengelola). | Kelas tidak dapat ditutup sebelum penanda tangan ditetapkan. |
| KB-09 | Menutup kelas membuat draf sertifikat untuk peserta yang hadir minimal 75% sesi. | Penanda tangan menerima notifikasi berisi jumlah sertifikat. |
| KB-10 | Kelas punya biaya. Nol berarti gratis. | Biaya tampil di kartu kelas, detail kelas, dan halaman konfirmasi. Pengelola mengisi biaya di formulir kelas. |
| KB-11 | Pengingat, kehadiran, sertifikat, dan agenda hanya untuk peserta sah. | Pendaftar yang belum membayar hanya menahan kursi dan tidak dihitung sebagai peserta. |

### 4.4b Pembayaran Kelas

| ID | Kebutuhan | Kriteria terima |
| --- | --- | --- |
| PB-01 | Kelas berbayar menahan kursi setelah konfirmasi dan membuat pembayaran QRIS lewat SumoPod Managed Payment. | Pendaftaran berstatus Menunggu Pembayaran. Warga diarahkan ke halaman QRIS SumoPod. |
| PB-02 | Pembayaran yang masih terbuka dipakai ulang. | Mendaftar ulang sebelum membayar membuka tautan yang sama, tidak membuat pembayaran baru. |
| PB-03 | Status lunas hanya dari webhook SumoPod. | Webhook diterima bila token X-Webhook-Token cocok atau tanda tangan Svix (HMAC SHA-256) cocok, dan jumlah sama dengan biaya kelas. Peristiwa ganda tidak diproses dua kali. |
| PB-04 | Setelah lunas, pendaftaran menjadi Terdaftar. | Warga menerima notifikasi dan email, aktivitas masuk Jejak Usaha. |
| PB-05 | Pembayaran gagal atau kedaluwarsa melepas kursi. | Batas pembayaran satu jam. Perintah terjadwal tiap lima menit menandai pembayaran kedaluwarsa. |
| PB-06 | Halaman status pembayaran. | Menampilkan Berhasil, Menunggu (memuat ulang sendiri), atau Gagal dengan tombol Daftar Lagi. Hanya pemilik yang dapat membuka. |

### 4.5 Sertifikat Terverifikasi (Web3)

| ID | Kebutuhan | Kriteria terima |
| --- | --- | --- |
| SR-01 | Penanda tangan menyimpan spesimen tanda tangan: digambar di layar atau unggah PNG transparan. | Spesimen tersimpan di penyimpanan privat dan hanya dipakai untuk sertifikat yang ia tanda tangani. |
| SR-02 | Halaman Tanda Tangan menampilkan kelas yang menunggu, daftar penerima, dan pratinjau sertifikat. | Penanda tangan dapat mengeluarkan nama tertentu sebelum menandatangani, dengan alasan. |
| SR-03 | Tombol Tanda Tangani Sertifikat menandatangani seluruh sertifikat kelas dengan konfirmasi kata sandi. | Waktu, akun, dan alamat IP penanda tangan tercatat di log audit. |
| SR-04 | Bila kelas punya dua penanda tangan, sertifikat terbit setelah keduanya menandatangani. | Status tetap Menunggu Tanda Tangan sampai lengkap. |
| SR-05 | Sistem membuat PDF A4 lanskap berdesain premium: latar dengan bingkai ganda, ornamen sudut, pita diagonal, tanda air dan medali berlogo alloBos, logo di atas, nama penerima, nama kelas, bidang, tanggal, nama dan tanda tangan penanda tangan, kode unik, kode QR ke halaman verifikasi. | PDF dibuat lewat antrean dan tersimpan di penyimpanan privat. |
| SR-06 | Sistem menghitung sidik digital SHA-256 dari data kanonik sertifikat. | Data kanonik: kode, id kelas, tanggal terbit, nama penanda tangan, dan sidik nama penerima yang diberi garam. |
| SR-07 | Sidik digital seluruh sertifikat satu kelas dicatat ke kontrak pintar dalam satu transaksi. | Hash transaksi, nomor blok, dan alamat kontrak tersimpan di tiap sertifikat. Status menjadi Tercatat. |
| SR-08 | Bila pencatatan gagal, sistem mengulang otomatis dan Pengelola dapat menekan Catat Ulang. | Sertifikat tetap dapat diunduh dengan keterangan pencatatan sedang diproses. |
| SR-09 | Halaman verifikasi publik lewat QR, tautan, atau ketik kode. | Menampilkan status Asli, Dicabut, Belum Tercatat, atau Tidak Ditemukan, beserta kelas, tanggal, penanda tangan, jaringan, dan tautan ke penjelajah blok. |
| SR-10 | Verifikasi membaca kontrak pintar secara langsung, bukan hanya basis data. | Bila sidik digital di server berbeda dengan catatan blockchain, status menjadi Tidak Cocok. |
| SR-11 | Nama penerima di halaman verifikasi mengikuti pilihan warga: nama lengkap atau inisial. | Bawaan nama lengkap. Dapat diubah warga kapan saja. |
| SR-12 | Warga melihat semua sertifikat di Sertifikat Saya, mengunduh PDF, menyalin tautan verifikasi. | Hanya pemilik yang dapat mengunduh PDF dari akun. |
| SR-13 | Warga dapat menampilkan lencana sertifikat di Lapak. | Lencana menaut ke halaman verifikasi dan hanya tampil untuk sertifikat berstatus Tercatat. |
| SR-14 | Pengelola dapat mencabut sertifikat dengan alasan. | Pencabutan dicatat di kontrak pintar. Halaman verifikasi menampilkan Dicabut dan alasannya. |
| SR-15 | Opsional: penanda tangan menghubungkan dompet dan menandatangani sidik digital kelas secara kriptografis. | Tanda tangan dompet tersimpan sebagai bukti tambahan dan tampil di halaman verifikasi. Tanpa dompet, alur tetap berjalan. |
| SR-16 | Admin melihat status blockchain: jaringan, alamat kontrak, alamat dan saldo dompet penerbit, transaksi terakhir. | Peringatan tampil bila saldo di bawah ambang. |
| SR-17 | Warga melihat pratinjau sertifikat di halaman sebelum mengunduh. | Pratinjau memakai templat yang sama dengan PDF dan menyesuaikan lebar layar. Hanya pemilik yang dapat membuka. |
| SR-18 | Tanda tangan orang sungguhan tidak dibuat oleh sistem. | Pimpinan IPI menandatangani sendiri dari akunnya. |

### 4.6 Siniar Bos

| ID | Kebutuhan | Kriteria terima |
| --- | --- | --- |
| SB-01 | Pengelola menambah episode dengan menempel URL YouTube. | URL divalidasi. Judul dan gambar sampul diambil otomatis dan dapat diubah. |
| SB-02 | AI menyusun ringkasan dan tiga pelajaran dari video atau dari catatan pengelola. | Hasil berstatus Draf sampai Pengelola menyetujui. |
| SB-03 | Daftar episode publik dengan bidang dan durasi. | Dapat disaring menurut bidang. |
| SB-04 | Halaman episode memutar video YouTube tertanam dengan mode hemat data. | Pemutar baru dimuat setelah tombol putar ditekan. |
| SB-05 | Warga menandai sudah didengar dan menulis catatan. | Tanda tersimpan dan masuk Skor Siap Usaha. |
| SB-06 | Episode disarankan menurut bidang minat warga. | Dashboard menampilkan maksimal tiga saran. |

### 4.7 Warung Bos 3D

| ID | Kebutuhan | Kriteria terima |
| --- | --- | --- |
| WB-01 | Dunia 3D low-poly warung dengan Three.js dari sudut pandang orang pertama: dinding, rak, jalan, bukit karst, pesaing, bus, hujan, siang dan senja. | Berjalan di peramban ponsel kelas menengah. Tabrakan kotak sejajar sumbu, tinggi mata 1,65 m. |
| WB-02 | Permainan tujuh hari. Tiap hari: Belanja Stok, Atur Harga, pajang peti ke rak, keputusan dan promosi, Buka Warung, Layani Pembeli, Tutup, Laporan. | Pemain tidak dapat melompati tahap. Modal tidak boleh negatif. Belanja boleh diulang sebelum buka. |
| WB-03 | Jenis warung mengikuti bidang minat profil (misalnya makanan olahan, kelontong, oleh-oleh wisata). | Daftar barang dan harga dasar berbeda per jenis. |
| WB-04 | Perhitungan permintaan, omzet, HPP, dan laba dilakukan di server. | Hasil tidak dapat diubah dari sisi peramban. |
| WB-05 | AI memunculkan satu kejadian per hari sesuai konteks. | Bila AI tidak tersedia, kejadian diambil dari bank kejadian. |
| WB-06 | Laporan harian: terjual, sisa stok, omzet, laba, kas. | Angka sama dengan catatan server. |
| WB-07 | Rapor akhir: laba total, nilai 0 sampai 100, keputusan terbaik dan terburuk, tiga pelajaran. | Rapor tersimpan dan nilai terbaik masuk Skor Siap Usaha. |
| WB-08 | Mode ringan tanpa 3D untuk perangkat lemah. | Tombol beralih tersedia. Aturan dan hasil permainan sama. |
| WB-09 | Permainan dapat dilanjutkan setelah halaman ditutup. | Keadaan tersimpan di server tiap transaksi. Posisi pemain disimpan di peramban. Memuat ulang saat warung buka memunculkan lagi pembeli yang belum dilayani. |
| WB-10 | Kontrol papan ketik dan tetikus (pointer lock), gamepad (zona mati, label Xbox atau PlayStation), dan layar sentuh (stik kiri, geser kanan). | Semua masukan dipetakan ke aksi yang sama: jalan, lihat, interaksi, ambil, amati, isi warung, jeda. |
| WB-11 | Tujuh stasiun diegetik: meja pemasok, rak gudang, rak pajang, papan harga, papan pengumuman, papan BUKA dan TUTUP, kasir. | Sinar dari tengah layar paling jauh 3 meter dengan oklusi. Benda yang dibidik disorot dan petunjuk tombol tampil. |
| WB-12 | Pembeli berperilaku: datang, melihat rak, antre, memesan, menunggu, membayar, pergi. | Permintaan hari itu dibagi ke paling banyak delapan pembeli. Barang yang tidak ada di rak dihitung tidak kebagian. Pembeli yang terlalu lama pergi. |
| WB-13 | Aksi di server tahan kiriman ulang. | Tiap aksi membawa id permintaan, dikunci per baris, dan hasil pertamanya dikembalikan bila dikirim lagi. |

### 4.8 Cek Produk

| ID | Kebutuhan | Kriteria terima |
| --- | --- | --- |
| CP-01 | Unggah foto produk (JPG, PNG, WEBP, maksimal 15 MB) dari kamera atau galeri. | Foto dikecilkan dan diubah ke WEBP di server. Berkas tidak valid ditolak dengan alasan. Pemeriksaan berjalan di antrean latar. |
| CP-02 | AI mengenali produk dan kategorinya. | Warga dapat memperbaiki nama produk bila salah. |
| CP-03 | Nilai foto dan kemasan 1 sampai 4 dengan saran perbaikan. | Minimal satu hal yang sudah baik dan satu sampai tiga perbaikan. |
| CP-04 | Kisaran harga jual yang disarankan dengan alasan. | Ditampilkan sebagai rentang, bukan satu angka. |
| CP-05 | Sinyal Pasar: naik, stabil, atau turun, dengan alasan. | Label Perkiraan AI selalu tampil. Sumber web ditampilkan bila penelusuran aktif. |
| CP-06 | Deskripsi produk siap pakai. | Tombol menyalin deskripsi dan tombol menambahkannya ke Lapak. |
| CP-07 | Riwayat Cek Produk. | Warga dapat membandingkan hasil sebelum dan sesudah perbaikan. |
| CP-08 | Pemeriksaan yang tertahan lebih dari tiga menit dihentikan. | Halaman berhenti memuat ulang dan tombol Periksa Ulang memeriksa foto yang sama tanpa unggah lagi. |

### 4.9 Peluang Usaha dan Rencana Usaha

| ID | Kebutuhan | Kriteria terima |
| --- | --- | --- |
| PU-01 | Tombol Buka Peluang menghasilkan tiga rekomendasi usaha. | Tiap rekomendasi memuat alasan cocok, perkiraan modal, risiko utama, dan bidang IPI terkait. |
| PU-02 | Rekomendasi mempertimbangkan profil, kapanewon, catatan konsultasi, kelas, dan rapor simulasi. | Alasan menyebut minimal satu data milik warga. |
| PU-03 | Warga memilih satu peluang atau menulis ide sendiri. | Pilihan tersimpan sebagai Usaha Tujuan. |
| PU-04 | Rencana Usaha satu halaman: produk, pembeli, cara jual, kebutuhan modal, pesaing, risiko. | Tiap bagian dapat diubah warga. Dapat diunduh PDF. |
| PU-05 | Kalkulator HPP dan titik impas. | Menghitung HPP per unit, margin, dan jumlah jual untuk impas. Rumus berjalan tanpa AI. |
| PU-06 | Langkah 30 Hari: daftar langkah mingguan yang dapat dicentang. | Persentase selesai tampil dan masuk Skor Siap Usaha. |
| PU-07 | Warga dapat menambah, mengubah, dan menghapus langkah. | Langkah dari Ruang Bos dan Temu Bos diberi tanda asal. |

### 4.10 Lapak

| ID | Kebutuhan | Kriteria terima |
| --- | --- | --- |
| LP-01 | Warga membuat lapak: nama, deskripsi, kapanewon, nomor WhatsApp, foto sampul. | Alamat lapak unik dibuat dari nama. |
| LP-02 | Kelola produk: nama, foto, harga, satuan, deskripsi, stok tersedia atau habis. | Produk dapat diurutkan dan disembunyikan. |
| LP-03 | Deskripsi dapat diisi dari hasil Cek Produk. | Satu klik mengisi nama, deskripsi, dan saran harga. |
| LP-04 | Terbitkan lapak. | Lapak tampil publik setelah punya minimal satu produk dan lolos moderasi. |
| LP-05 | Halaman lapak publik dan direktori lapak per kapanewon dan bidang. | Dapat dibuka tanpa akun. |
| LP-06 | Tombol Pesan lewat WhatsApp dengan pesan terisi nama produk. | Membuka WhatsApp dengan nomor penjual. Pengunjung yang belum masuk melihat tombol Masuk untuk Memesan dan kembali ke lapak setelah masuk. |
| LP-07 | Pengelola memoderasi lapak dan dapat menurunkannya dengan alasan. | Pemilik menerima notifikasi berisi alasan. |
| LP-08 | Hitungan kunjungan dan klik pesan per lapak. | Tampil di dashboard pemilik, dihitung dari basis data. |
| LP-09 | Melaporkan lapak yang tidak pantas. | Hanya pengguna yang sudah masuk. Laporan masuk ke Pengelola. |
| LP-10 | Warga menjelajahi lapak warga lain dari menu Lapak dan halaman Lapak Saya. | Hanya lapak berstatus Terbit yang tampil. |
| LP-11 | Foto sampul dan produk sampai 15 MB. | Foto dikecilkan otomatis. Ubah data lapak dan foto tersimpan. |

### 4.11 Jejak Usaha

| ID | Kebutuhan | Kriteria terima |
| --- | --- | --- |
| JU-01 | Dashboard warga: Skor Siap Usaha, tahap saat ini, langkah berikutnya, agenda. | Langkah berikutnya menunjuk modul yang paling menaikkan skor. |
| JU-02 | Skor dihitung dengan bobot yang dapat diatur Admin. | Bobot bawaan: profil 10, konsultasi 15, belajar 25, simulasi 15, rencana 25, lapak 10. |
| JU-03 | Grafik perkembangan skor. | Cuplikan skor disimpan tiap ada bukti baru. |
| JU-04 | Riwayat aktivitas lintas modul. | Dapat disaring per modul. |
| JU-05 | Notifikasi di aplikasi dan email untuk sesi, kelas, dan moderasi. | Lonceng menampilkan jumlah belum dibaca. |

### 4.12 Dashboard Bos, Pengelola IPI, dan Admin

| ID | Kebutuhan | Kriteria terima |
| --- | --- | --- |
| DB-01 | Bos: ringkasan sesi, kelola slot, daftar permintaan, catatan sesi, profil, sertifikat yang menunggu tanda tangan. | Bos nonaktif tidak dapat membuka slot. |
| DP-01 | Pengelola: kelas, pembicara, siniar, kurasi Bos, moderasi lapak, sertifikat, pengumuman. | Semua daftar punya cari, saring, dan status. |
| DP-02 | Wawasan: topik konsultasi terbanyak, bidang paling diminati, sebaran kapanewon, kehadiran kelas. | Dihitung dari data, dapat diunduh. |
| DA-01 | Admin: pengguna dan peran, undangan Bos dan Pengelola. | Undangan memakai tautan bertanda tangan. |
| DA-02 | Pengaturan AI per fitur: aktif, model, suhu, kuota, prompt berversi, uji prompt. | Perubahan berlaku tanpa mengubah kode. |
| DA-03 | CMS halaman publik, bank kejadian warung, bobot skor, log audit. | Setiap perubahan penting tercatat di log audit. |

## 5. Spesifikasi AI

Semua panggilan AI melewati satu kelas klien, mengikuti proyek acuan: keluaran JSON berskema, pengaturan per fitur, kuota harian per pengguna, pencatatan permintaan, dan penyamaran data pribadi sebelum dikirim.

| Kunci fitur | Dipakai di | Keluaran utama (JSON) | Suhu | Kuota harian |
| --- | --- | --- | --- | --- |
| boss_chat | Ruang Bos | understood, reply, follow_up, suggested_modules[] | 0,5 | 30 giliran |
| boss_summary | Catatan Konsultasi | problem, advice[], next_steps[], modules[] | 0,3 | 5 |
| opportunity | Peluang Usaha | items[3]: title, why, capital_range, risk, ipi_field | 0,6 | 3 |
| business_plan | Rencana Usaha | product, customers, channels, capital[], rivals, risks[], steps[] | 0,4 | 3 |
| product_check | Cek Produk | name, category, photo_score, pack_score, fixes[], price_range, signal, reasons[], sources[], description | 0,3 | 5 |
| podcast_summary | Siniar Bos | summary, lessons[3] | 0,3 | Pengelola |
| shop_event | Warung Bos 3D | title, story, effect{demand, cost, cash} | 0,8 | 14 |
| shop_debrief | Rapor Warung | best, worst, lessons[3] | 0,4 | 2 |
| boss_voice | Suara Ruang Bos | Berkas MP3 dari Speechify, disimpan per teks | Tidak ada | Mengikuti batas laju Ruang Bos |

- **Model.** Gemini 3.1 Flash Lite lewat variabel lingkungan. Model cadangan Gemini Flash Lite Latest lalu Gemini Flash Latest dicoba bila model utama hilang atau galat. Cek Produk memakai masukan gambar. Sinyal Pasar memakai penelusuran web bila diaktifkan Admin.
- **Kunci bergiliran.** Lima kunci API dipisah koma. Kunci yang mendapat 401, 403, 429, atau 503 dilewati, kunci terakhir yang berhasil diingat.
- **Urutan isian.** Skema JSON memakai propertyOrdering. boss_chat menulis isian understood (ringkasan maksud pesan warga) sebelum reply supaya jawaban terikat pada pertanyaannya.
- **Suara.** Speechify Text to Speech API, suara Bima, model simba-3.5, bahasa id-ID. Tanpa kunci, peramban membacakan sendiri.
- **Validasi.** Jawaban yang tidak sesuai skema diulang satu kali, lalu memakai cadangan.
- **Cadangan tanpa AI.** Ruang Bos menampilkan pesan dan tombol Temu Bos. Peluang memakai pencocokan kata kunci terhadap bidang IPI. Kejadian warung memakai bank kejadian. Kalkulator HPP tidak bergantung AI.
- **Keamanan prompt.** Instruksi sistem meminta model mengabaikan perintah yang ada di dalam masukan pengguna.
- **Privasi.** Nama, nomor, email, dan alamat disamarkan sebelum dikirim.

## 6. Spesifikasi Web3

Blockchain dipakai hanya untuk mencatat dan memeriksa keaslian sertifikat kelas. Warga dan pengunjung tidak membutuhkan dompet. Semua biaya jaringan ditanggung dompet penerbit milik sistem.

| Aspek | Ketentuan |
| --- | --- |
| Jaringan | Jaringan EVM diatur lewat variabel lingkungan sehingga dapat dipindah tanpa mengubah kode. Masa lomba: Jaringan Uji alloBos, jaringan uji milik sendiri (Anvil dari Foundry, chain ID 31337) yang berjalan sebagai layanan Docker di server dan hanya dapat dijangkau dari dalam jaringan Docker. Keadaan rantai disimpan ke volume tiap 30 detik. Polygon Amoy (chain ID 80002) disiapkan sebagai tujuan berikutnya, tetapi faucet POL uji kini mensyaratkan saldo ETH jaringan utama. RPC publik Amoy bawaan: PublicNode. |
| Kontrak pintar | AlloBosCertificate (Solidity). Fungsi: issueBatch(bytes32[] hashes, bytes32 classId), revoke(bytes32 hash, string reason), verify(bytes32 hash) yang mengembalikan penerbit, waktu terbit, dan status cabut. Peristiwa: CertificateIssued, CertificateRevoked. |
| Hak akses kontrak | Hanya alamat penerbit yang terdaftar dapat menerbitkan dan mencabut. Pemilik kontrak dapat menambah atau mengganti penerbit. |
| Dompet penerbit | Satu dompet milik sistem. Kunci privat hanya di variabel lingkungan server, tidak pernah di repositori atau peramban. |
| Yang ditulis ke blockchain | Sidik digital sertifikat, id kelas tersamarkan, waktu, status cabut, alasan cabut singkat. Tidak ada nama, nomor, atau email. |
| Pustaka | ethers.js dijalankan dari pekerjaan antrean Laravel lewat skrip Node di folder web3 (anchor, revoke, verify, status). Hardhat untuk kompilasi dan uji kontrak di mesin pengembang. deploy-ethers.mjs memasang kontrak dari server tanpa Hardhat. |
| Pekerjaan antrean | GenerateCertificates (PDF), AnchorCertificates (catat ke blockchain), RevokeCertificate. Mengulang sampai tiga kali dengan jeda. |
| Pembacaan | Halaman verifikasi memanggil verify lewat ChainClient dengan tembolok 60 detik. |
| Variabel lingkungan | CHAIN_NAME, CHAIN_ID, CHAIN_RPC_URL, CHAIN_EXPLORER_URL, CERT_CONTRACT_ADDRESS, ISSUER_PRIVATE_KEY, CERT_HASH_SALT. |
| Tanda tangan dompet opsional | Penanda tangan menghubungkan dompet peramban dan menandatangani pesan berisi id kelas dan sidik gabungan (EIP-191). Server memeriksa alamat penanda tangan. |
| Kegagalan | RPC tidak tersedia atau saldo habis: sertifikat tetap terbit dengan status Menunggu Pencatatan dan dicatat saat jaringan pulih. |
| Keterbukaan | Halaman verifikasi menyebut nama jaringan dan menandai bila memakai jaringan uji. |
| Kode sumber | Kontrak, skrip deploy, dan alamat kontrak dicantumkan di README agar juri dapat memeriksanya. |

## 7. Model data

| Kelompok | Tabel | Isi pokok |
| --- | --- | --- |
| Akun | users, roles, permissions, invitations | Identitas, peran, undangan |
| Profil | resident_profiles, districts, business_fields | Profil usaha, 18 kapanewon, 13 bidang dan 5 pendukung |
| Ruang Bos | boss_sessions, boss_turns, consult_notes | Sesi, giliran bicara, catatan konsultasi |
| Temu Bos | boss_profiles, boss_slots, mentoring_sessions, session_notes, boss_ratings | Profil Bos, slot, sesi, catatan, penilaian |
| Kelas Bos | programs (termasuk price), program_sessions, speakers, program_speaker, program_signers, enrollments, attendances | Kelas dan biayanya, sesi, pembicara, penanda tangan, pendaftaran, kehadiran |
| Pembayaran | payments | Pesanan per pendaftaran: order_id, id pembayaran SumoPod, jumlah, biaya layanan, status, tautan bayar, batas waktu, waktu lunas, peristiwa terakhir |
| Sertifikat | certificates, certificate_signatures, signature_specimens, chain_transactions | Sertifikat (kode, hash, status, berkas PDF, hash transaksi, nomor blok), tanda tangan per penanda tangan, spesimen, catatan transaksi |
| Siniar | podcasts, podcast_listens | Episode dan tanda dengar |
| Warung 3D | shop_games, shop_days, shop_event_bank | Permainan, catatan harian, bank kejadian |
| Cek Produk | product_checks | Foto, hasil AI, sumber |
| Rencana | opportunities, business_plans, cost_items, plan_steps | Peluang, rencana, komponen biaya, langkah |
| Lapak | shops, products, shop_visits | Lapak, produk, kunjungan dan klik |
| Jejak | readiness_snapshots, activity_logs, notifications | Cuplikan skor, aktivitas, notifikasi |
| Sistem | settings, ai_prompts, ai_requests, pages, page_blocks, audit_logs, media | Pengaturan, prompt, log AI, CMS, audit |

Status disimpan sebagai enum PHP, bukan teks tersebar: SessionStatus, MentoringStatus, ProgramStatus, EnrollmentStatus (MenungguBayar, Terdaftar, LulusHadir, Batal), PaymentStatus (Menunggu, Lunas, Gagal, Kedaluwarsa), BossStatus, ShopStatus, GameStatus, ContentStatus, CheckStatus, CertificateStatus (MenungguTandaTangan, Ditandatangani, MenungguPencatatan, Tercatat, Dicabut).

## 8. Kebutuhan non-fungsional

| Aspek | Kebutuhan |
| --- | --- |
| Kinerja | Halaman publik tampil di bawah 3 detik pada jaringan 4G. Aset 3D dimuat hanya di halaman permainan. |
| Hemat data | Gambar dikompres dan dimuat malas. Video YouTube dimuat setelah diminta. Lapak tanpa pustaka berat. |
| Responsif | Dirancang dari lebar 360 px. Tidak ada gulir mendatar. |
| Aksesibilitas | Kontras teks memadai, fokus papan ketik terlihat, tombol minimal 44 px, teks alternatif gambar, menghormati pengaturan kurangi gerak. |
| Keamanan | CSRF (kecuali webhook yang diverifikasi tanda tangannya), pembatasan laju pada semua POST, kebijakan akses per pemilik data, validasi unggahan, rahasia hanya di berkas lingkungan server. |
| Keamanan pembayaran | Lunas hanya dari webhook terverifikasi dengan jumlah yang cocok. Halaman kembali dari SumoPod hanya membaca status, tidak mengubahnya. |
| Unggahan | Foto sampai 15 MB (PHP 20 MB, isi formulir 25 MB, nginx 25 MB), dikecilkan dan diubah ke WEBP. |
| Keamanan Web3 | Kunci privat penerbit hanya di server. Kontrak diuji otomatis untuk hak akses terbit dan cabut. Spesimen tanda tangan di penyimpanan privat. |
| Privasi | Persetujuan saat daftar, transkrip konsultasi dihapus setelah masa simpan, warga dapat menghapus akunnya. |
| Keandalan | Pekerjaan AI yang lama dijalankan lewat antrean. Kegagalan AI tidak menghentikan alur. Pekerjaan yang tertahan dapat diulang. |
| Infrastruktur | Docker Compose di VPS Ubuntu: app (Apache dan PHP 8.4), queue, scheduler, mysql 8.4, chain (Anvil). nginx dengan sertifikat Let's Encrypt di depan aplikasi yang hanya mendengar di 127.0.0.1. |
| Bahasa | Antarmuka bahasa Indonesia sehari-hari. Tanpa istilah bisnis yang tidak dijelaskan. |
| Mutu kode | Controller tipis, service per domain, tes fitur untuk alur utama, format kode otomatis. |

## 9. Kepatuhan ketentuan lomba

| Ketentuan | Pemenuhan |
| --- | --- |
| Integrasi AI wajib | Delapan fitur AI pada bagian 5. Web3 adalah tambahan, bukan pengganti AI. |
| Website di-deploy dan publik sampai 30 November 2026 | Sudah berjalan di https://allobos.zite.my.id (Docker di VPS, nginx, HTTPS). |
| Repositori publik, riwayat commit utuh | GitHub github.com/GiskaSaputra/allobos, commit bertahap berbahasa Indonesia, tanpa force push. Repositori dijadikan publik sebelum pengumpulan. |
| Pemakaian template, pustaka, dan AI coding assistant dicantumkan | Bagian khusus di README dan proposal. |
| Akun demo untuk fitur login | Akun demo tiap peran di proposal dan README (bagian 9.1), sandi password123. |
| Video demo 3 sampai 5 menit | Perkenalan, latar belakang dan solusi, demo alur Bu Sri dari Kenali sampai Buka. |
| Orisinalitas | Dibangun sebagai repositori baru. Arsitektur acuan dipakai sebagai pola, bukan disalin sebagai karya. |

### 9.1 Akun dan data demo

- Skenario demo: Bu Sri (keripik singkong, Ponjong) dengan skor awal rendah yang naik setelah konsultasi, kelas, simulasi, rencana, dan lapak. Nilai dihitung dengan rumus produksi.
- Kelas dan pembicara nyata dari IPI ditandai Resmi. Data contoh ditandai di basis data dan README. Lencana Demo di antarmuka dapat dinyalakan dengan APP_DEMO=true.
- Satu kelas demo yang sudah ditutup dengan sertifikat Bu Sri berstatus Tercatat, sehingga juri dapat memindai QR dan melihat status Asli beserta bukti transaksinya.
- Isi demo: 7 kelas (3 berbayar), 9 episode siniar dari video YouTube publik yang diringkas Gemini, 5 Bos, 3 lapak dengan 3 produk.
- Perintah satu baris untuk mengisi ulang data demo (php artisan allobos:demo --force, hanya untuk basis data kosong atau lingkungan uji).

| Peran | Email | Keterangan |
| --- | --- | --- |
| Warga | warga@allobos.com | Bu Sri, pemilik lapak Keripik Mbok Sri |
| Warga pemilik lapak | lapak.batik@allobos.com | Batik Tulis Bu Siti |
| Warga pemilik lapak | lapak.topeng@allobos.com | Topeng Kayu Pak Sugeng |
| Bos | bos@allobos.com | Pak Arif, pembicara kelas dan Bos pendamping |
| Pengelola IPI | pengelola@allobos.com | Mbak Rani |
| Pimpinan IPI | pimpinan@allobos.com | Deni Fuadi, penanda tangan sertifikat |
| Admin | admin@allobos.com | Pengguna, AI, blockchain, audit |

## 10. Urutan pembangunan

| Fase | Isi | Selesai bila |
| --- | --- | --- |
| 0 | Fondasi: proyek, autentikasi, peran, tata letak, token desain, klien AI | Empat peran dapat masuk ke dashboard kosong |
| 1 | Profil Usaha dan Jejak Usaha | Onboarding tersimpan, skor tampil |
| 2 | Ruang Bos | Sesi suara dan teks berjalan, catatan tersimpan |
| 3 | Peluang, Rencana, HPP, Langkah 30 Hari | Rencana dapat diunduh |
| 4 | Cek Produk dan Lapak | Lapak publik dapat dipesan lewat WhatsApp |
| 5 | Kelas Bos dan Temu Bos | Daftar, hadir, pesan sesi, catatan |
| 5b | Sertifikat Terverifikasi: kontrak pintar, tanda tangan, PDF, pencatatan, halaman verifikasi | QR sertifikat demo menampilkan status Asli dari blockchain |
| 6 | Siniar Bos | Episode dari URL YouTube dengan ringkasan |
| 7 | Warung Bos 3D | Tujuh hari tuntas dengan rapor |
| 8 | Dashboard Pengelola dan Admin, CMS, wawasan | Semua daftar dapat dikelola |
| 9 | Beranda, data demo, uji manual, deploy, README, video | Uji manual lulus di alamat publik |
| 10 | Aset baru dan desain premium, halaman masuk, Gemini bergiliran, Resend, Warung Bos 3D orang pertama, deploy VPS | Situs berjalan di allobos.zite.my.id |
| 11 | Kerapian responsif, animasi, palet, logo di kartu, data contoh | Tidak ada tumpang tindih dari 360 sampai 1440 px |
| 12 | Kelas berbayar QRIS, sertifikat premium dan pratinjau, suara Speechify, perbaikan CRUD, jaringan blockchain uji di server | Pembayaran sandbox, suara, dan verifikasi berjalan di alamat publik |

Setiap fase ditutup dengan tes hijau, build berhasil, halaman dibuka di peramban, dan commit.

## 11. Risiko produk dan penanganan

| Risiko | Penanganan |
| --- | --- |
| Jawaban AI keliru atau terlalu yakin | Label perkiraan, batas topik, jalur ke Temu Bos, prompt berversi yang dapat diuji Admin. |
| Kuota layanan AI habis saat penjurian | Beberapa kunci API bergiliran, kuota per pengguna, cadangan tanpa AI. |
| Peramban juri tidak mendukung suara | Mode teks otomatis dengan penjelasan. |
| Perangkat lemah untuk 3D | Mode ringan dan pemuatan aset bertahap. |
| Konten lapak tidak pantas | Moderasi sebelum terbit dan tombol lapor. |
| Data pembicara tidak akurat | Hanya Pengelola IPI yang dapat mengisi dan menerbitkan. |
| Jaringan blockchain lambat atau RPC mati saat penjurian | Pencatatan lewat antrean dengan pengulangan, status jelas di antarmuka, sertifikat demo sudah tercatat sebelumnya. |
| Saldo dompet penerbit habis | Peringatan saldo di dashboard Admin dan satu transaksi per kelas agar hemat. |
| Kunci privat bocor | Kunci hanya di server, penerbit dapat diganti lewat pemilik kontrak. |
| Sertifikat terbit untuk orang yang salah | Pratinjau dan daftar penerima sebelum tanda tangan, serta pencabutan tercatat. |
| Webhook pembayaran palsu | Token atau tanda tangan Svix diperiksa, jumlah harus cocok, peristiwa ganda diabaikan. |
| Warga membayar tetapi webhook terlambat | Halaman status memuat ulang sendiri. Pembayaran yang lunas setelah batas waktu tetap memberi kursi. |
| Foto dari ponsel terlalu besar | Batas unggah 15 MB dan foto dikecilkan otomatis. |
| Layanan suara tidak tersedia | Peramban membacakan dengan suara bawaan. Teks tetap tampil. |
| Faucet jaringan uji publik berbayar atau menolak | Jaringan Uji alloBos di server yang gratis, siap pindah ke Polygon Amoy. |
