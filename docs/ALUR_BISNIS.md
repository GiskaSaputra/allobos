# Business Flow

Alur bisnis alloBos secara rinci: pelaku, perjalanan warga, alur tiap modul, status, aturan, dan pengecualian

- **Produk:** alloBos, pendamping usaha berbasis AI untuk warga Gunungkidul
- **Penyusun:** Giska Saputra
- **Versi:** Final 2.0, 18 November 2026 (menambah pembayaran kelas QRIS, alur suara, Warung Bos 3D orang pertama)
- **Aplikasi berjalan:** https://allobos.zite.my.id
- **Dokumen terkait:** Konsep Ide, PRD, Design System

## 1. Pelaku dan pertukaran nilai

| Pelaku | Memberi | Menerima |
| --- | --- | --- |
| Warga | Waktu, cerita usahanya, data profil, produk | Saran, pelatihan, sertifikat terverifikasi, rencana usaha, lapak, pembeli |
| Bos | Waktu dan pengalaman usaha | Jadwal rapi, konteks warga sebelum sesi, penilaian, jejaring |
| Pengelola IPI | Program, pembicara, kurasi | Peserta kelas, data kebutuhan warga, jangkauan lebih luas |
| Admin | Pemeliharaan sistem | Sistem yang tertib dan teraudit |
| Pembeli | Pesanan dan pembayaran langsung ke penjual (perlu akun untuk memesan) | Produk lokal dengan informasi jelas |
| Pihak luar (calon mitra, pemberi modal) | Kepercayaan dan kesempatan | Bukti pelatihan warga yang bisa diperiksa sendiri |
| Bos Allo (AI) | Jawaban, rekomendasi, dokumen, suara | Konteks dari profil dan riwayat warga |
| Layanan pihak ketiga | Gemini (AI), Speechify (suara), Resend (email), SumoPod (QRIS), jaringan blockchain uji | Permintaan yang sudah disamarkan, biaya layanan pembayaran |

Uang yang berpindah di dalam aplikasi hanya biaya kelas berbayar, dibayar warga lewat QRIS SumoPod (biaya layanan 0,7% ditambah Rp300 per transaksi, dana masuk saldo setelah sekitar dua hari). Pembayaran pesanan lapak tetap terjadi langsung antara pembeli dan penjual di luar aplikasi.

## 2. Perjalanan utama warga

Alur ujung ke ujung dari pengunjung menjadi pemilik lapak. Ini juga alur yang ditunjukkan di video demo.

| No | Pelaku | Aksi | Respons sistem | Hasil |
| --- | --- | --- | --- | --- |
| 1 | Pengunjung | Membuka beranda dan menekan Daftar | Menampilkan formulir pendaftaran | Akun Warga |
| 2 | Warga | Mengisi lima langkah Profil Usaha | Menyimpan profil dan menghitung skor awal | Skor Siap Usaha awal |
| 3 | Warga | Membuka Ruang Bos dan bercerita | Bos Allo menjawab dengan suara dan teks | Giliran percakapan |
| 4 | Warga | Menekan Akhiri Sesi | Menyusun Catatan Konsultasi | Catatan dan saran modul |
| 5 | Warga | Mendaftar kelas yang disarankan lewat halaman konfirmasi, membayar QRIS bila kelas berbayar | Mencatat pendaftaran setelah konfirmasi atau setelah pembayaran lunas, mengirim pengingat | Tempat di kelas |
| 6 | Warga | Hadir dan memasukkan kode kehadiran | Mencatat kehadiran dan memperbarui skor | Progres kelas |
| 7 | Pembicara | Menandatangani sertifikat kelas | Membuat PDF dan mencatat sidik digital ke blockchain | Sertifikat Terverifikasi |
| 8 | Warga | Mendengar satu episode siniar dan menandainya | Menyimpan tanda dengar | Skor belajar naik |
| 9 | Warga | Bermain Warung Bos 3D tujuh hari | Menghitung hasil dan menyusun rapor | Nilai simulasi |
| 10 | Warga | Menekan Buka Peluang | Menampilkan tiga rekomendasi | Daftar peluang |
| 11 | Warga | Memilih satu peluang | Menyusun Rencana Usaha dan Langkah 30 Hari | Rencana tersimpan |
| 12 | Warga | Mengisi biaya di kalkulator HPP | Menghitung HPP, margin, titik impas | Harga jual masuk akal |
| 13 | Warga | Memotret produk di Cek Produk | Menilai foto, kemasan, harga, Sinyal Pasar | Laporan dan deskripsi |
| 14 | Warga | Membuat lapak dan menambah produk dari hasil cek | Mengisi nama, deskripsi, saran harga | Lapak berstatus Menunggu |
| 15 | Pengelola IPI | Menyetujui lapak | Menerbitkan dan memberi tahu pemilik | Lapak publik |
| 16 | Pembeli | Membuka lapak, masuk akun, menekan Pesan lewat WhatsApp | Membuka WhatsApp dengan pesan terisi | Pesanan ke penjual |
| 17 | Warga | Bila butuh arahan lanjut, memesan Temu Bos | Mengirim permintaan ke Bos | Sesi dengan bos asli |

## 3. Alur per modul

### 3.1 Pendaftaran dan Profil Usaha

| No | Pelaku | Aksi | Respons sistem | Hasil |
| --- | --- | --- | --- | --- |
| 1 | Pengunjung | Mengisi nama, WhatsApp, email, sandi, centang persetujuan | Validasi, membuat akun, mengirim email verifikasi lewat Resend | Akun belum terverifikasi |
| 2 | Warga | Membuka tautan verifikasi | Menandai email terverifikasi | Akses onboarding |
| 3 | Warga | Memilih kapanewon | Menyimpan langkah 1 | Lokasi |
| 4 | Warga | Memilih status: belum punya usaha atau sudah | Menampilkan pertanyaan lanjutan sesuai pilihan | Status usaha |
| 5 | Warga | Memilih bidang minat | Menyimpan maksimal tiga bidang | Bidang minat |
| 6 | Warga | Mengisi kisaran modal dan waktu luang | Menyimpan langkah 4 | Kemampuan |
| 7 | Warga | Mengisi keahlian lalu menekan Simpan Profil | Menandai onboarding selesai, menghitung skor | Dashboard terbuka |

### 3.2 Ruang Bos

| No | Pelaku | Aksi | Respons sistem | Hasil |
| --- | --- | --- | --- | --- |
| 1 | Warga | Memilih topik dan mode, menekan Mulai Bicara dengan Bos Allo | Memeriksa kuota, membuat sesi, meminta izin mikrofon | Sesi Berjalan |
| 2 | Bos Allo | Menyapa dan bertanya pembuka | Klip berbicara tampil dan suara Speechify diputar. Bila peramban menahan suara, tombol Dengarkan Bos Allo muncul | Giliran 1 |
| 3 | Warga | Menahan tombol mikrofon lalu berbicara | Menampilkan transkrip | Teks ucapan |
| 4 | Warga | Melepas tombol mikrofon (atau menekan Kirim pada mode teks) | Pertanyaan langsung terkirim, data pribadi disamarkan, Gemini dipanggil dengan sepuluh giliran terakhir | Status berpikir |
| 5 | Bos Allo | Menjawab | Menyimpan giliran, meminta suara jawaban itu ke Speechify, lalu memutar suara dan memunculkan teks bersamaan | Saran dan pertanyaan lanjutan |
| 6 | Warga | Mengulang tanya jawab | Mengurangi sisa giliran | Percakapan |
| 7 | Warga | Menekan Akhiri Sesi | Memanggil AI untuk merangkum | Catatan Konsultasi |
| 8 | Warga | Menekan Tambahkan ke Langkah 30 Hari | Menyalin langkah lanjut | Langkah baru |

#### Pengecualian Ruang Bos

- Mikrofon ditolak atau tidak didukung: beralih ke mode teks dengan penjelasan.
- AI gagal menjawab: giliran tidak dihitung, tampil tombol Coba Lagi dan tombol Temu Bos.
- Pesan terlalu singkat atau ambigu: Bos Allo bertanya balik dengan dua atau tiga contoh pilihan, bukan menebak.
- Speechify tidak tersedia: peramban membacakan dengan suara Google Bahasa Indonesia, teks tetap tampil.
- Kuota habis: sesi tidak dapat dimulai, pesan menyebut waktu kuota kembali.
- Sesi ditinggal lebih dari 30 menit: ditutup otomatis dan dirangkum.

### 3.3 Temu Bos

| No | Pelaku | Aksi | Respons sistem | Hasil |
| --- | --- | --- | --- | --- |
| 1 | Bos | Membuat slot: tanggal, jam, durasi, daring atau tatap muka | Menyimpan slot berstatus Terbuka | Slot tersedia |
| 2 | Warga | Membuka profil Bos dan memilih slot | Menampilkan formulir topik | Slot dipilih |
| 3 | Warga | Menulis topik, melampirkan Catatan Konsultasi, menekan Ajukan Sesi | Mengunci slot, membuat sesi Menunggu, memberi tahu Bos | Permintaan terkirim |
| 4 | Bos | Menekan Setujui dan mengisi tautan rapat atau alamat | Mengubah status menjadi Disetujui, memberi tahu warga | Sesi terjadwal |
| 5 | Sistem | Sehari dan sejam sebelum sesi | Mengirim pengingat ke kedua pihak | Pengingat |
| 6 | Keduanya | Menjalankan sesi di luar aplikasi | Tidak ada | Sesi berlangsung |
| 7 | Bos | Menulis catatan dan langkah, menekan Tandai Selesai | Mengubah status menjadi Selesai, memperbarui skor warga | Catatan sesi |
| 8 | Warga | Memberi penilaian | Menyimpan dan menghitung rata-rata Bos | Penilaian |

### 3.4 Kelas Bos

| No | Pelaku | Aksi | Respons sistem | Hasil |
| --- | --- | --- | --- | --- |
| 1 | Pengelola IPI | Membuat kelas, sesi, pembicara, kuota | Menyimpan sebagai Draf | Draf kelas |
| 2 | Pengelola IPI | Menekan Terbitkan | Menampilkan kelas di publik | Kelas Dibuka |
| 3 | Warga | Menekan Daftar Kelas, memeriksa halaman konfirmasi, mencentang kesanggupan hadir | Memeriksa kuota. Kelas gratis langsung dicatat, kelas berbayar masuk alur pembayaran (3.4b) | Terdaftar atau Menunggu Pembayaran |
| 4 | Sistem | Sehari sebelum sesi | Mengirim pengingat | Pengingat |
| 5 | Pengelola IPI | Membuka kode kehadiran saat sesi | Mengaktifkan kode untuk rentang waktu sesi | Kode aktif |
| 6 | Warga | Memasukkan kode dan menekan Catat Hadir | Memeriksa kode dan waktu, mencatat hadir | Hadir |
| 7 | Pengelola IPI | Menetapkan penanda tangan sertifikat | Menyimpan satu atau dua penanda tangan | Penanda tangan kelas |
| 8 | Pengelola IPI | Menekan Tutup Kelas setelah sesi terakhir | Menghitung kelulusan hadir, membuat draf sertifikat | Kelas Selesai |

### 3.4b Pembayaran kelas berbayar

| No | Pelaku | Aksi | Respons sistem | Hasil |
| --- | --- | --- | --- | --- |
| 1 | Warga | Menekan Bayar dengan QRIS di halaman konfirmasi | Menahan kursi, membuat pesanan dengan nomor unik, meminta tautan QRIS ke SumoPod dengan batas satu jam | Pendaftaran Menunggu Pembayaran |
| 2 | Warga | Memindai QRIS dengan aplikasi bank atau dompet digital | Tidak ada di alloBos | Pembayaran di SumoPod |
| 3 | SumoPod | Mengirim webhook payment.completed | Memeriksa token atau tanda tangan Svix, mencocokkan jumlah, mengunci baris pesanan | Pesanan Lunas |
| 4 | Sistem | Pesanan lunas | Mengubah pendaftaran menjadi Terdaftar, memperbarui status kuota, memberi tahu warga lewat aplikasi dan email | Tempat di kelas |
| 5 | Warga | Kembali dari SumoPod ke alloBos | Menampilkan halaman status yang memuat ulang sendiri sampai webhook tiba | Pembayaran berhasil |

#### Pengecualian pembayaran

- Warga menekan bayar lagi sebelum membayar: tautan yang sama dibuka kembali, tidak ada pesanan ganda.
- Pembayaran gagal atau melewati satu jam: pesanan Gagal atau Kedaluwarsa, kursi dilepas, warga bisa Daftar Lagi.
- Webhook dengan token atau tanda tangan salah: ditolak 401 dan tidak mengubah apa pun.
- Jumlah di webhook tidak sama dengan biaya kelas: pesanan tetap Menunggu dan dicatat untuk diperiksa.
- Webhook dikirim lebih dari sekali: hanya yang pertama diproses.
- SumoPod tidak bisa dihubungi saat membuat tautan: kursi dilepas dan warga melihat pesan untuk mencoba lagi.

### 3.5 Sertifikat Terverifikasi (Web3)

| No | Pelaku | Aksi | Respons sistem | Hasil |
| --- | --- | --- | --- | --- |
| 1 | Sistem | Kelas ditutup | Membuat draf sertifikat bagi peserta hadir minimal 75%, memberi tahu penanda tangan | Sertifikat Menunggu Tanda Tangan |
| 2 | Penanda tangan | Membuka Tanda Tangan, menyimpan spesimen bila belum ada | Menyimpan spesimen di penyimpanan privat | Spesimen |
| 3 | Penanda tangan | Membuka kelas, memeriksa daftar penerima dan pratinjau | Menampilkan pratinjau sertifikat | Daftar diperiksa |
| 4 | Penanda tangan | Menekan Tanda Tangani Sertifikat dan memasukkan kata sandi | Mencatat tanda tangan, waktu, dan akun di log audit | Sertifikat Ditandatangani |
| 5 | Sistem | Semua penanda tangan lengkap | Membuat PDF dengan tanda tangan, kode, dan QR lewat antrean | Berkas PDF |
| 6 | Sistem | PDF selesai | Menghitung sidik digital tiap sertifikat | Daftar sidik digital |
| 7 | Sistem | Mengirim transaksi | Memanggil issueBatch di kontrak pintar dengan dompet penerbit | Status Menunggu Pencatatan |
| 8 | Blockchain | Transaksi masuk blok | Sistem menyimpan hash transaksi dan nomor blok | Status Tercatat |
| 9 | Sistem | Pencatatan selesai | Memberi tahu warga, memperbarui skor | Notifikasi sertifikat |
| 10 | Warga | Membuka Sertifikat Saya, melihat pratinjau, menekan Unduh PDF atau Salin Tautan Verifikasi | Menampilkan pratinjau dengan templat yang sama, menyajikan berkas atau tautan | Sertifikat di tangan warga |
| 11 | Warga | Menekan Tampilkan di Lapak | Menambah lencana di halaman lapak | Lencana terverifikasi |
| 12 | Pihak luar | Memindai QR atau membuka tautan verifikasi | Menghitung ulang sidik digital, membaca kontrak pintar | Status Asli beserta bukti transaksi |

#### Pengecualian sertifikat

- Penanda tangan mengeluarkan satu nama sebelum menandatangani: sertifikat itu dibatalkan dengan alasan dan warga diberi tahu.
- Pencatatan gagal karena jaringan atau saldo: sertifikat tetap dapat diunduh dengan keterangan Menunggu Pencatatan, sistem mengulang, Pengelola dapat menekan Catat Ulang.
- Sertifikat keliru sudah tercatat: Pengelola mencabut dengan alasan, pencabutan dicatat di blockchain, halaman verifikasi menampilkan Dicabut.
- Kode tidak dikenal di halaman verifikasi: status Tidak Ditemukan dan saran memeriksa ulang kode.
- Sidik digital server berbeda dengan catatan blockchain: status Tidak Cocok dan Admin diberi tahu.

### 3.6 Siniar Bos

| No | Pelaku | Aksi | Respons sistem | Hasil |
| --- | --- | --- | --- | --- |
| 1 | Pengelola IPI | Menempel URL YouTube dan menekan Ambil Data | Memvalidasi URL, mengambil judul dan sampul | Draf episode |
| 2 | Pengelola IPI | Menekan Buat Ringkasan | Menjalankan AI lewat antrean | Ringkasan dan tiga pelajaran |
| 3 | Pengelola IPI | Memeriksa, mengubah, menekan Terbitkan | Menampilkan episode di publik | Episode terbit |
| 4 | Warga | Membuka episode dan menekan Putar | Memuat pemutar YouTube | Video diputar |
| 5 | Warga | Menekan Tandai Sudah Didengar | Menyimpan tanda, memperbarui skor | Skor belajar naik |

### 3.7 Warung Bos 3D

| No | Pelaku | Aksi | Respons sistem | Hasil |
| --- | --- | --- | --- | --- |
| 1 | Warga | Menekan Buka Warung lalu Masuk ke Warung | Membuat permainan sesuai bidang minat, modal awal, kejadian hari 1 | Berdiri di dalam warung (orang pertama) |
| 2 | Warga | Berjalan ke meja pemasok dan belanja | Memeriksa kas, menambah stok gudang | Peti berisi barang di rak gudang |
| 3 | Warga | Mengatur harga di papan harga | Menyimpan harga | Harga hari ini |
| 4 | Warga | Mengambil peti di gudang dan menaruhnya di rak pajang | Memindahkan satu peti (10 unit) ke rak, tahan kiriman ulang | Rak terisi |
| 5 | Warga | Memilih promosi dan keputusan harian di papan pengumuman | Menyimpan pilihan sementara di peramban | Keputusan hari ini |
| 6 | Warga | Membalik papan ke BUKA | Menghitung permintaan dan membagi ke paling banyak delapan pembeli di server | Pembeli berdatangan |
| 7 | Warga | Melayani pembeli paling depan di kasir | Mengambil barang dari rak, mencatat uang masuk | Pembeli membayar dan pergi |
| 8 | Sistem | Pembeli menunggu terlalu lama | Mencatat pembeli pergi, permintaannya dihitung tidak kebagian | Pelajaran stok dan kecepatan |
| 9 | Warga | Membalik papan ke TUTUP | Menutup buku hari itu dari jumlah yang benar-benar terjual, menampilkan laporan | Laporan harian |
| 10 | Warga | Menekan Lanjut ke Hari Berikutnya | Membuka hari berikut sampai hari 7 | Hari 2 sampai 7 |
| 11 | Sistem | Setelah hari 7 | Menghitung nilai, meminta rapor dari AI | Rapor akhir |
| 12 | Warga | Menekan Simpan Pelajaran ke Jejak Usaha | Menyimpan rapor, memperbarui skor | Nilai simulasi |

Mode Ringan menjalankan aturan yang sama tanpa 3D: belanja, harga, keputusan, lalu seluruh permintaan dilayani sebanyak stok.

**Rumus inti simulasi.** Permintaan dasar per barang dikalikan faktor harga (makin jauh di atas harga wajar makin turun), faktor promosi, dan faktor kejadian. Terjual adalah nilai terkecil antara permintaan dan stok. Laba harian adalah omzet dikurangi HPP barang terjual, biaya promosi, dan biaya tetap. Nilai akhir menggabungkan laba, sisa stok terbuang, dan hari dengan kas negatif.

### 3.8 Cek Produk

| No | Pelaku | Aksi | Respons sistem | Hasil |
| --- | --- | --- | --- | --- |
| 1 | Warga | Menekan Ambil Foto atau Pilih dari Galeri | Memvalidasi dan mengompres gambar | Pratinjau foto |
| 2 | Warga | Menekan Periksa Produk | Menyimpan foto (sampai 15 MB, dikecilkan ke WEBP) dan menjalankan AI penglihatan lewat antrean | Status Diperiksa |
| 3 | Sistem | AI selesai | Menyimpan hasil dan memberi tahu | Laporan Cek Produk |
| 4 | Warga | Memperbaiki nama produk bila salah | Menyimpan koreksi | Nama benar |
| 5 | Warga | Menekan Tambahkan ke Lapak | Membuka formulir produk terisi | Produk baru di lapak |

Pengecualian: bila pemeriksaan tertahan lebih dari tiga menit (antrean berhenti atau jaringan putus), status menjadi Gagal dengan penjelasan dan tombol Periksa Ulang memeriksa foto yang sama tanpa unggah lagi.

### 3.9 Peluang dan Rencana Usaha

| No | Pelaku | Aksi | Respons sistem | Hasil |
| --- | --- | --- | --- | --- |
| 1 | Warga | Menekan Buka Peluang | Mengumpulkan profil dan riwayat, memanggil AI | Tiga peluang |
| 2 | Warga | Menekan Pilih Usaha Ini pada satu peluang | Menyimpan Usaha Tujuan | Usaha tujuan |
| 3 | Warga | Menekan Susun Rencana | Memanggil AI, menyimpan rencana dan langkah | Rencana Usaha |
| 4 | Warga | Mengubah isi rencana | Menyimpan versi warga | Rencana milik warga |
| 5 | Warga | Mengisi komponen biaya dan jumlah produksi | Menghitung HPP, margin, titik impas | Harga jual |
| 6 | Warga | Mencentang langkah yang selesai | Memperbarui persentase dan skor | Progres 30 hari |
| 7 | Warga | Menekan Unduh PDF | Membuat dokumen | Rencana siap cetak |

### 3.10 Lapak dan pesanan

| No | Pelaku | Aksi | Respons sistem | Hasil |
| --- | --- | --- | --- | --- |
| 1 | Warga | Mengisi data lapak dan menekan Simpan Lapak | Membuat alamat lapak | Lapak Draf |
| 2 | Warga | Menambah produk | Menyimpan produk dan mengompres foto | Produk |
| 3 | Warga | Menekan Ajukan Terbit | Mengubah status menjadi Menunggu, memberi tahu Pengelola | Antre moderasi |
| 4 | Pengelola IPI | Menekan Setujui atau Tolak dengan alasan | Menerbitkan atau mengembalikan ke Draf | Lapak Terbit atau perbaikan |
| 5 | Pembeli | Membuka lapak | Mencatat kunjungan | Halaman lapak |
| 6 | Pembeli | Menekan Pesan lewat WhatsApp (pengunjung diminta masuk dulu lalu kembali ke lapak) | Mencatat klik, membuka WhatsApp dengan pesan terisi | Percakapan pesanan |
| 7 | Warga | Melayani pesanan dan menerima pembayaran | Tidak ada | Transaksi di luar aplikasi |
| 8 | Warga | Menandai produk Habis bila stok kosong | Menonaktifkan tombol pesan produk itu | Informasi stok benar |
| 9 | Pengguna masuk | Menekan Laporkan Lapak dan menulis alasan | Menyimpan laporan untuk Pengelola | Laporan moderasi |

### 3.11 Kurasi Bos oleh IPI

| No | Pelaku | Aksi | Respons sistem | Hasil |
| --- | --- | --- | --- | --- |
| 1 | Pengelola IPI | Mengirim undangan ke pengusaha | Membuat tautan undangan bertanda tangan | Undangan |
| 2 | Calon Bos | Membuka tautan dan mengisi profil usaha | Membuat akun peran Bos berstatus Diajukan | Profil diajukan |
| 3 | Pengelola IPI | Memeriksa empat standar kurasi | Menampilkan daftar periksa | Hasil kurasi |
| 4 | Pengelola IPI | Menekan Aktifkan atau Tolak | Mengubah status dan memberi tahu | Bos Aktif |
| 5 | Bos | Membuka slot | Menampilkan Bos di direktori | Siap menerima sesi |

Empat standar kurasi mengikuti IPI: bisnis aktif saat ini, rekam jejak jelas, pengalaman riil, dan dapat ditelusuri.

## 4. Daur status

### 4.1 Sesi Temu Bos

| Dari | Pemicu | Ke | Pelaku |
| --- | --- | --- | --- |
| Menunggu | Bos menyetujui | Disetujui | Bos |
| Menunggu | Bos menolak dengan alasan | Ditolak | Bos |
| Menunggu | 48 jam tanpa jawaban | Kedaluwarsa | Sistem |
| Disetujui | Salah satu pihak membatalkan sebelum waktu sesi | Dibatalkan | Warga atau Bos |
| Disetujui | Bos menandai selesai dan menulis catatan | Selesai | Bos |
| Disetujui | Bos menandai warga tidak hadir | Tidak Hadir | Bos |

### 4.2 Kelas dan pendaftaran

| Dari | Pemicu | Ke | Pelaku |
| --- | --- | --- | --- |
| Draf | Pengelola menerbitkan | Dibuka | Pengelola |
| Dibuka | Kuota terisi penuh | Penuh | Sistem |
| Dibuka atau Penuh | Sesi pertama dimulai | Berjalan | Sistem |
| Berjalan | Pengelola menutup kelas | Selesai | Pengelola |
| Berjalan | Pengelola menutup kelas tanpa penanda tangan | Ditolak, tetap Berjalan | Sistem |
| (baru) | Kelas gratis dikonfirmasi | Terdaftar | Warga |
| (baru) | Kelas berbayar dikonfirmasi | Menunggu Pembayaran | Warga |
| Menunggu Pembayaran | Webhook SumoPod lunas dengan jumlah cocok | Terdaftar | Sistem |
| Menunggu Pembayaran | Pembayaran gagal atau lewat satu jam | Batal (kursi dilepas) | Sistem |
| Terdaftar (peserta) | Hadir memenuhi syarat | Lulus Hadir | Sistem |
| Terdaftar (peserta) | Peserta membatalkan sebelum kelas mulai | Batal | Warga |

### 4.3 Sertifikat

| Dari | Pemicu | Ke | Pelaku |
| --- | --- | --- | --- |
| (baru) | Kelas ditutup dan peserta lulus hadir | Menunggu Tanda Tangan | Sistem |
| Menunggu Tanda Tangan | Penanda tangan mengeluarkan nama | Dibatalkan | Penanda tangan |
| Menunggu Tanda Tangan | Semua penanda tangan menandatangani | Ditandatangani | Penanda tangan |
| Ditandatangani | PDF dibuat dan transaksi dikirim | Menunggu Pencatatan | Sistem |
| Menunggu Pencatatan | Transaksi masuk blok | Tercatat | Sistem |
| Menunggu Pencatatan | Transaksi gagal tiga kali | Menunggu Pencatatan (perlu Catat Ulang) | Sistem |
| Tercatat | Pengelola mencabut dengan alasan | Dicabut | Pengelola |

### 4.3b Pembayaran

| Dari | Pemicu | Ke | Pelaku |
| --- | --- | --- | --- |
| (baru) | Warga menekan Bayar dengan QRIS | Menunggu | Warga |
| Menunggu | Webhook payment.completed terverifikasi | Lunas | SumoPod |
| Menunggu | Webhook payment.failed | Gagal | SumoPod |
| Menunggu | Webhook payment.expired atau lewat batas waktu | Kedaluwarsa | SumoPod atau Sistem |

### 4.4 Lapak

| Dari | Pemicu | Ke | Pelaku |
| --- | --- | --- | --- |
| Draf | Pemilik mengajukan terbit dengan minimal satu produk | Menunggu | Warga |
| Menunggu | Pengelola menyetujui | Terbit | Pengelola |
| Menunggu | Pengelola menolak dengan alasan | Draf | Pengelola |
| Terbit | Pemilik menutup sementara | Tutup | Warga |
| Terbit | Pengelola menurunkan karena pelanggaran | Diturunkan | Pengelola |
| Tutup | Pemilik membuka kembali | Terbit | Warga |

### 4.5 Sesi Ruang Bos dan permainan

| Dari | Pemicu | Ke | Pelaku |
| --- | --- | --- | --- |
| Disiapkan | Warga memulai | Berjalan | Warga |
| Berjalan | Warga mengakhiri atau 30 menit diam | Dirangkum | Warga atau Sistem |
| Dirangkum | Catatan tersimpan | Selesai | Sistem |
| Permainan Berjalan | Hari 7 ditutup | Selesai | Sistem |
| Permainan Berjalan | Warga memulai permainan baru | Ditinggalkan | Warga |

## 5. Aturan bisnis

| Kode | Aturan |
| --- | --- |
| AB-01 | Modul warga hanya terbuka setelah Profil Usaha selesai. |
| AB-02 | Satu warga hanya boleh punya satu sesi Temu Bos berstatus Menunggu per Bos. |
| AB-03 | Slot hanya dapat dipesan minimal 12 jam sebelum waktunya. |
| AB-04 | Pembatalan sesi oleh warga paling lambat 6 jam sebelum waktu sesi. |
| AB-05 | Penilaian Bos hanya untuk sesi berstatus Selesai, satu kali per sesi. |
| AB-06 | Kode kehadiran hanya berlaku pada rentang waktu sesi kelas. |
| AB-07 | Sertifikat kelas diberikan bila hadir minimal 75% sesi dan kelas sudah ditutup. |
| AB-08 | Lapak butuh minimal satu produk berfoto dan nomor WhatsApp yang valid untuk diajukan. |
| AB-09 | Satu warga satu lapak. |
| AB-10 | Hasil AI yang menyangkut pasar dan peluang selalu berlabel Perkiraan AI. |
| AB-11 | Kuota AI dihitung per warga per hari dan kembali pada tengah malam waktu Indonesia barat. |
| AB-12 | Nilai simulasi yang masuk skor adalah nilai terbaik, bukan terakhir. |
| AB-13 | Skor Siap Usaha dihitung ulang setiap ada bukti baru dan cuplikannya disimpan. |
| AB-14 | Hanya Pengelola IPI dan Admin yang dapat menerbitkan kelas, pembicara, dan episode siniar. |
| AB-15 | Statistik publik dihitung dari basis data. Saat mode demo menyala (APP_DEMO=true), data contoh tidak ikut dihitung. |
| AB-16 | Transkrip Ruang Bos disimpan 180 hari lalu dihapus. Catatan Konsultasi tetap tersimpan. |
| AB-17 | Setiap kelas wajib punya minimal satu penanda tangan sebelum ditutup. Penanda tangan adalah pembicara kelas itu atau pimpinan IPI. |
| AB-18 | Tanda tangan sertifikat membutuhkan konfirmasi kata sandi dan tidak dapat diwakilkan ke akun lain. |
| AB-19 | Satu warga satu sertifikat per kelas. Kode sertifikat unik dan tidak dipakai ulang. |
| AB-20 | Blockchain hanya menerima sidik digital. Data pribadi tidak pernah ditulis ke blockchain. |
| AB-21 | Sertifikat yang sudah tercatat tidak dapat diubah. Koreksi dilakukan dengan mencabut lalu menerbitkan sertifikat baru. |
| AB-22 | Hanya sertifikat berstatus Tercatat yang dihitung dalam Skor Siap Usaha dan dapat ditampilkan di Lapak. |
| AB-23 | Pencabutan wajib beralasan dan hanya oleh Pengelola IPI atau Admin. |
| AB-24 | Pendaftaran kelas selalu melalui halaman konfirmasi dengan centang kesanggupan hadir. |
| AB-25 | Kelas berbayar menahan kursi paling lama satu jam. Kursi menjadi milik warga hanya setelah pembayaran lunas. |
| AB-26 | Status lunas hanya ditetapkan webhook SumoPod yang terverifikasi dan jumlahnya cocok. Halaman kembali dari SumoPod tidak mengubah status. |
| AB-27 | Pendaftar yang belum membayar tidak menerima pengingat, tidak bisa mencatat hadir, dan tidak masuk agenda. |
| AB-28 | Memesan dan melaporkan lapak membutuhkan akun. Melihat katalog dan memverifikasi sertifikat tidak. |
| AB-29 | Tanda tangan orang sungguhan hanya berasal dari spesimen yang ia simpan sendiri. |
| AB-30 | Data sertifikat yang berubah setelah tercatat (misalnya penanda tangan diganti) wajib dihitung ulang sidiknya dan dicatat ulang, karena verifikasi akan menampilkan Tidak Cocok. |

## 6. Notifikasi

| Peristiwa | Penerima | Saluran |
| --- | --- | --- |
| Permintaan sesi baru | Bos | Aplikasi, email |
| Sesi disetujui, ditolak, dibatalkan | Warga | Aplikasi, email |
| Pengingat sesi dan kelas | Warga, Bos | Aplikasi, email |
| Pendaftaran kelas berhasil (gratis atau setelah lunas) | Warga | Aplikasi, email |
| Sertifikat menunggu tanda tangan | Penanda tangan | Aplikasi, email |
| Sertifikat tercatat dan siap diunduh | Warga | Aplikasi, email |
| Sertifikat dicabut | Warga | Aplikasi, email |
| Pencatatan blockchain gagal atau saldo penerbit menipis | Admin, Pengelola IPI | Aplikasi |
| Hasil Cek Produk siap | Warga | Aplikasi |
| Lapak disetujui, ditolak, diturunkan | Warga | Aplikasi, email |
| Lapak menunggu moderasi | Pengelola IPI | Aplikasi |
| Profil Bos diajukan | Pengelola IPI | Aplikasi |
| Episode siniar baru sesuai minat | Warga | Aplikasi |

Email dikirim lewat Resend SMTP dari noreply@zite.my.id dengan antrean, sehingga lambatnya layanan email tidak memperlambat halaman.

## 7. Alur data antar modul

| Dari | Ke | Data yang mengalir |
| --- | --- | --- |
| Profil Usaha | Semua modul | Kapanewon, modal, bidang minat, status usaha |
| Ruang Bos | Temu Bos | Catatan Konsultasi sebagai lampiran |
| Ruang Bos, Temu Bos | Langkah 30 Hari | Langkah lanjut |
| Ruang Bos | Kelas Bos, Siniar Bos | Saran modul sesuai topik |
| Kelas, Siniar, Warung 3D | Peluang Usaha | Riwayat belajar dan nilai simulasi |
| Peluang Usaha | Rencana Usaha | Usaha tujuan |
| Rencana Usaha | Cek Produk | Kategori produk dan harga hasil HPP sebagai pembanding |
| Cek Produk | Lapak | Nama, deskripsi, saran harga |
| Kelas Bos | Pembayaran | Biaya kelas dan pendaftaran yang menunggu bayar |
| Pembayaran | Kelas Bos, Jejak Usaha | Status lunas yang mengubah pendaftaran menjadi Terdaftar |
| Kelas Bos | Sertifikat | Daftar lulus hadir dan penanda tangan |
| Sertifikat | Blockchain | Sidik digital dan status cabut |
| Sertifikat | Lapak, Jejak Usaha | Lencana terverifikasi dan bukti belajar |
| Semua modul | Jejak Usaha | Bukti untuk Skor Siap Usaha |
| Semua modul | Wawasan Pengelola | Agregat tanpa identitas pribadi |

## 8. Keberlanjutan setelah lomba

Bagian ini adalah rencana, bukan klaim capaian.

- **Gratis untuk warga.** Konsultasi AI, siniar, simulasi, rencana, dan lapak tidak dipungut biaya.
- **Kelas berbayar lewat QRIS.** alloBos menjadi pintu pendaftaran dan pembayaran program IPI. Masa lomba memakai SumoPod sandbox. Setelah lomba cukup mengaktifkan Live Access SumoPod dan mengganti alamat API, dan pemasukan kelas menutup biaya server dan layanan.
- **Sertifikat di jaringan publik.** Masa lomba memakai Jaringan Uji alloBos di server. Setelah lomba, pencatatan dapat dipindah ke Polygon Amoy atau jaringan utama berbiaya rendah dengan mengganti alamat RPC dan kunci penerbit.
- **Biaya operasional.** Server dan pemakaian layanan AI ditanggung mitra dan pemasukan kelas. Kuota per pengguna, model Gemini Flash Lite, suara yang disimpan per teks, dan gambar yang dikecilkan menjaga biaya tetap terkendali.
- **Perluasan.** Pola yang sama dapat dipakai kabupaten lain dengan mengganti data kapanewon, bidang unggulan, dan mitra pelatihan.
