<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

/** Isi awal halaman publik. Dapat diubah Admin lewat CMS. */
class PageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            'tentang' => ['Tentang alloBos', <<<'MD'
alloBos adalah pendamping usaha untuk warga Gunungkidul, dari belum tahu mau usaha apa sampai punya lapak online yang bisa dipesan.

Polanya seperti konsultasi dokter daring, tetapi untuk urusan usaha. Warga bertanya kepada **Bos Allo**, bos virtual berbasis AI yang bisa diajak bicara. Bila perlu, warga bertemu bos asli: pengusaha aktif yang dikurasi Institusi Pengusaha Indonesia (IPI).

## Lima tahap

1. **Kenali.** Isi Profil Usaha singkat.
2. **Tanya.** Bicara dengan Bos Allo atau pesan sesi Temu Bos.
3. **Belajar.** Ikut Kelas Bos, dengarkan Siniar Bos, latihan di Warung Bos 3D.
4. **Rancang.** Temukan peluang, susun rencana, hitung HPP, periksa foto produk.
5. **Buka.** Pajang produk di Lapak dan terima pesanan lewat WhatsApp.

## Mitra pelaksana

Kelas, pembicara, dan kurasi Bos dijalankan bersama Institusi Pengusaha Indonesia di Gunungkidul. Penerima manfaatnya tetap warga.

## Catatan

Bos Allo adalah pendamping virtual berbasis AI. Jawabannya adalah bahan pertimbangan, bukan nasihat hukum, pajak, atau pinjaman.
MD],
            'bantuan' => ['Bantuan', <<<'MD'
## Ruang Bos tidak bisa mendengar suara saya

Izinkan mikrofon di pengaturan peramban, lalu muat ulang halaman. Bila peramban tidak mendukung, pakai mode Teks.

## Kode kehadiran kelas ditolak

Kode hanya berlaku saat sesi berlangsung. Pastikan huruf dan angkanya sama dengan yang ditampilkan di ruang kelas.

## Lapak saya belum tampil

Lapak tampil setelah diajukan dan disetujui Pengelola IPI. Pastikan minimal satu produk punya foto dan nomor WhatsApp benar.

## Cara memeriksa sertifikat

Pindai kode QR di sertifikat atau buka halaman Verifikasi Sertifikat lalu ketik kodenya.
MD],
            'privasi' => ['Kebijakan Privasi', <<<'MD'
## Data yang kami simpan

Nama, nomor WhatsApp, email, Profil Usaha, catatan konsultasi, kehadiran kelas, hasil Cek Produk, rencana usaha, dan isi lapak.

## Data yang dikirim ke layanan AI

Sebelum dikirim ke layanan AI, nama, nomor telepon, email, dan alamat disamarkan. Foto produk dikirim untuk diperiksa.

## Blockchain

Hanya sidik digital sertifikat yang dicatat di blockchain. Nama dan data pribadi tetap di server alloBos.

## Masa simpan

Transkrip percakapan Ruang Bos dihapus setelah 180 hari. Catatan Konsultasi tetap tersimpan.

## Hak kamu

Kamu dapat mengubah data di halaman Profil dan menghapus akun beserta datanya kapan saja.
MD],
            'syarat' => ['Syarat Pemakaian', <<<'MD'
1. alloBos gratis untuk warga.
2. Saran Bos Allo dan hasil AI adalah perkiraan. Keputusan usaha tetap di tanganmu.
3. Pembayaran pesanan lapak terjadi langsung antara pembeli dan penjual, di luar aplikasi.
4. Dilarang memajang produk yang melanggar hukum atau bukan milik sendiri. Pengelola IPI dapat menurunkan lapak yang melanggar.
5. Sertifikat dapat dicabut bila ditemukan kekeliruan atau kecurangan.
MD],
            'kontak' => ['Kontak', <<<'MD'
Untuk pertanyaan tentang kelas, kurasi Bos, atau lapak, hubungi Pengelola IPI lewat email yang tercantum di halaman pengumuman.

Bila menemukan lapak yang tidak pantas, pakai tautan **Laporkan Lapak** di bagian bawah halaman lapak.
MD],
        ];

        foreach ($pages as $slug => [$title, $body]) {
            Page::query()->firstOrCreate(['slug' => $slug], ['title' => $title, 'body' => $body]);
        }
    }
}
