# Progres pembangunan alloBos

Fase mengikuti PRD bagian 10. Bila sesi terputus, lanjutkan dari fase pertama yang belum bertanda selesai.

## Status fase

| Fase | Isi | Status |
| --- | --- | --- |
| 0 | Fondasi: proyek, autentikasi, peran, tata letak, token desain, klien AI | Selesai |
| 1 | Profil Usaha dan Jejak Usaha | Selesai |
| 2 | Ruang Bos | Belum |
| 3 | Peluang, Rencana, HPP, Langkah 30 Hari | Belum |
| 4 | Cek Produk dan Lapak | Belum |
| 5 | Kelas Bos dan Temu Bos | Belum |
| 5b | Sertifikat Terverifikasi | Belum |
| 6 | Siniar Bos | Belum |
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
- `npm run build` di komputer ini lambat (sekitar 8 menit). Penyebabnya belum diselidiki.
