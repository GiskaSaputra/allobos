# Aturan kerja alloBos

alloBos adalah pendamping usaha berbasis AI untuk warga Gunungkidul (IT RUSH 2026, Web Development, Subtema 3).

## Sumber kebenaran

Baca `docs/` sebelum mengubah kode. Bila bertentangan, urutan prioritas:

1. `docs/SPESIFIKASI.md` (PRD)
2. `docs/DESAIN_SISTEM.md` (Design System)
3. `docs/ALUR_BISNIS.md` (Business Flow)
4. `docs/KONSEP.md` (Konsep Ide)

`docs/DESAIN.md` berisi nilai token hasil turunan referensi visual. `docs/PROGRESS.md` berisi fase yang selesai, keputusan, dan sisa pekerjaan. Bila sesi terputus, baca `docs/PROGRESS.md` lalu lanjutkan dari fase yang belum selesai.

## Cara kerja

- Kerjakan per fase menurut PRD bagian 10. Di akhir tiap fase: `php artisan test` hijau, `npm run build` berhasil, `./vendor/bin/pint` bersih, halaman dibuka di peramban pada 360 px dan desktop, `docs/PROGRESS.md` diperbarui, commit, push.
- Jangan berhenti meminta persetujuan rencana. Ambil keputusan paling masuk akal, catat di `docs/PROGRESS.md`.
- Boleh berhenti dan bertanya hanya bila: referensi desain tidak terlihat dan `referensi/` kosong, butuh nilai rahasia (`GEMINI_API_KEY`, `ISSUER_PRIVATE_KEY`, `CHAIN_RPC_URL`, data VPS), push ditolak, atau dua dokumen bertentangan dan urutan prioritas tidak menyelesaikannya.
- Tanpa nilai rahasia, lanjutkan dengan jaringan Hardhat lokal dan `Http::fake()`.

## Stack

Laravel 12, PHP 8.2+, Blade, Livewire 3, Alpine, Tailwind 3, Vite, MySQL 8.4, spatie/laravel-permission, Breeze (Blade), dompdf, phpspreadsheet, endroid/qr-code, Chart.js, Three.js, Gemini, Solidity + Hardhat + ethers.js di `web3/`, Docker Compose (`app`, `mysql`, `queue`, `scheduler`).

## Aturan kode

- Status di enum PHP (`app/Enums`), bukan teks tersebar.
- Semua panggilan Gemini lewat `App\Services\Ai\GeminiClient`. Semua akses blockchain lewat `App\Services\Chain\ChainClient`.
- Controller tipis, aturan bisnis di service (`app/Services/<Domain>`), validasi di Form Request, akses data per pemilik lewat policy.
- Semua rute POST memakai CSRF dan pembatasan laju.
- Komentar hanya untuk alasan atau batasan yang tidak terlihat dari kode, satu sampai dua baris.
- Tidak ada `dd()`, `dump()`, `console.log`, TODO samar, kode mati, atau dependensi yang tidak dipakai.
- Kegagalan AI atau blockchain tidak boleh menghentikan alur. Pakai cadangan di PRD bagian 5.
- Tes fitur untuk alur utama tiap fase. Panggilan AI di tes memakai `Http::fake()`. Tes memalsukan `ChainClient`.
- Proyek acuan PNC Elevate hanya rujukan pola. Jangan menyalin berkas, nama model, teks, atau datanya.

## Aturan tampilan

- Pakai skill design-taste-frontend untuk pekerjaan antarmuka.
- Tidak ada nilai hex di berkas Blade. Semua warna lewat token Tailwind (`bg-surface`, `text-fg`, `bg-brand`, dan seterusnya) yang menunjuk variabel CSS di `resources/css/app.css`.
- Tombol mengikuti `docs/DESAIN_SISTEM.md` bagian 7: varian, ukuran, keadaan, dan rincian per halaman.
- Rancang dari lebar 360 px. Tidak boleh ada gulir mendatar.
- Animasi menghormati `prefers-reduced-motion`.
- Ikon dari satu keluarga: Phosphor (`<x-phosphor-... />`).

## Aturan teks antarmuka

- Bahasa Indonesia sehari-hari. Istilah bisnis dijelaskan saat pertama muncul.
- Tanpa em dash, emoji, tanda seru yang dipaksakan, atau kata pemasaran kosong.
- Label tombol menyebut aksinya. Pesan galat menyebut apa yang terjadi dan langkah berikutnya.
- Hasil AI tentang pasar dan peluang selalu berlencana Perkiraan AI. Data contoh selalu berlencana Demo.
- Tidak ada statistik, testimoni, atau nama pembicara karangan yang tampil sebagai data nyata.
- Kata hash diganti sidik digital di antarmuka warga.

## Aturan git

- Repositori `https://github.com/GiskaSaputra/allobos.git`, cabang `main`.
- Identitas: `Giska Saputra <218528892+GiskaSaputra@users.noreply.github.com>`. Jangan mengubah identitas penulis.
- Pesan commit: bahasa Indonesia sehari-hari, huruf kecil, satu baris, sekitar 72 karakter, tanpa awalan seperti `feat:`, tanpa emoji, tanpa titik di akhir. Contoh: `menambah halaman ruang bos dengan mode suara`.
- Jangan menambahkan `Co-Authored-By`, `Generated with`, atau baris tambahan apa pun di pesan commit.
- Commit kecil dan sering, beberapa commit per fase. Push ke `origin main` di akhir tiap fase.
- Jangan pernah `git push --force` dan jangan menulis ulang riwayat.
- Sebelum commit pastikan `.env`, kunci API, kunci privat dompet, `vendor`, `node_modules`, dan isi `storage` tidak ikut. Bila rahasia terlanjur ter-commit, berhenti dan beri tahu pemilik proyek.
- Bila push ditolak karena autentikasi, berhenti dan beri tahu pemilik proyek. Jangan mencoba cara lain.

## Perintah

- Tes: `php artisan test`
- Format: `./vendor/bin/pint`
- Build aset: `npm run build`
- Data demo: `php artisan migrate:fresh --seed` lalu `php artisan db:seed --class=DemoSeeder`
- Tes kontrak: `cd web3 && npx hardhat test`
