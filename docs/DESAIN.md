# Desain alloBos

Nilai di bawah diturunkan dari referensi `dribbble.com/shots/26954032-Creative-agency-web-ui-design` yang diamati lewat peramban pada Fase 0. Isi, teks, ilustrasi, dan logo milik alloBos. Tidak ada gambar atau logo referensi yang dipakai.

Bacaan desain: beranda komunitas untuk warga Gunungkidul dan juri lomba, dengan bahasa visual agensi kreatif yang hangat, memakai Tailwind 3 dan token CSS. Dial: variasi 7, gerak 6, kepadatan 4 di halaman publik. Dashboard memakai kepadatan 5 dan gerak 2.

## Amatan dari referensi

- Latar halaman hijau sage pucat. Isi berada di dalam panel besar bersudut sangat bulat.
- Hero berupa panel bergradasi hijau zaitun ke kuning lumut. Judul sangat besar berwarna krem lime di atas blok kaca gelap.
- Navigasi berbentuk pil: deret tautan di dalam trek kaca, pil aktif berwarna zaitun tua. Di kanan ada pil bergaris dan pil padat.
- Angka statistik kecil di kanan atas hero, kartu tab kaca bertumpuk di kanan bawah.
- Seksi berikutnya berupa panel hitam dengan kartu abu sangat gelap di dalamnya. Judul besar tegak, penekanan memakai miring tebal dari keluarga yang sama.
- Satu kartu ajakan bergradasi teal ke mint sebagai penutup.
- Tombol selalu pil. Tombol kedua bergaris tipis dengan panah.
- Gerak: judul muncul dari kabur dan bergeser ke atas, kartu bergeser masuk, pita logo berjalan mendatar, galeri bergeser.

## Token warna

Nilai ditulis sebagai kanal RGB di `resources/css/app.css` supaya kelas Tailwind bisa memakai opasitas.

| Token | Terang | Gelap |
| --- | --- | --- |
| --surface | 230 235 211 | 12 14 9 |
| --surface-alt | 219 226 196 | 18 21 13 |
| --surface-raised | 245 248 234 | 26 30 19 |
| --fg | 20 26 12 | 244 251 200 |
| --fg-body | 50 60 36 | 214 221 190 |
| --fg-muted | 84 96 62 | 156 166 130 |
| --line | 196 205 168 | 44 50 32 |
| --brand | 62 77 30 | 233 245 166 |
| --brand-fg | 244 251 200 | 20 26 12 |
| --accent | 205 224 72 | 205 224 72 |
| --accent-fg | 20 26 12 | 20 26 12 |
| --accent-text | 82 102 14 | 205 224 72 |
| --success | 38 122 68 | 110 200 140 |
| --warning | 150 96 12 | 232 180 80 |
| --danger | 180 52 36 | 240 120 100 |
| --info | 30 106 140 | 110 190 225 |
| --status-fg | 245 248 234 | 12 14 9 |

Token tambahan untuk bidang dekoratif dari referensi, sama di kedua tema:

| Token | Nilai | Dipakai |
| --- | --- | --- |
| --ink | 10 12 7 | Panel hitam |
| --ink-raised | 24 27 18 | Kartu di dalam panel hitam |
| --ink-fg | 244 251 200 | Teks di panel hitam |
| --ink-muted | 170 180 140 | Keterangan di panel hitam |
| --hero-from, --hero-to | 139 161 58, 199 185 46 | Gradasi hero |
| --mint-from, --mint-to | 94 196 182, 143 221 184 | Kartu ajakan penutup |

Kontras teks isi terhadap latar di atas 4,5 banding 1 pada kedua tema.

## Tipografi

- Display: Montserrat Variable (tegak dan miring), dimuat lokal lewat `@fontsource-variable/montserrat`, `font-display: swap`.
- Isi: Ubuntu 400, 500, 700 lewat `@fontsource/ubuntu`.
- Penekanan di judul memakai miring tebal Montserrat, bukan keluarga lain.
- Skala mengikuti Design System 4.2: display 48 sampai 72, H1 36, H2 28, H3 20, isi 16, kecil 14, label 12.

## Bentuk

| Elemen | Sudut |
| --- | --- |
| Tombol, chip, masukan satu baris, pilihan | 999 px (pil) |
| Area teks dan masukan banyak baris | 20 px |
| Kartu | 28 px |
| Panel seksi | 40 px, 28 px di ponsel |

Aturannya: kontrol berbentuk pil, wadah bersudut besar. Area teks memakai 20 px karena pil tidak mungkin untuk banyak baris.

## Bayangan

- Kartu: `0 1px 2px rgb(20 26 12 / .06), 0 8px 24px -12px rgb(20 26 12 / .18)`
- Melayang (modal, menu): `0 24px 60px -20px rgb(20 26 12 / .35)`

## Gerak

- Kurva: `cubic-bezier(0.16, 1, 0.3, 1)`.
- Sorot tombol dan kartu 180 ms. Ditekan skala 0,98.
- Muncul saat digulir: geser 16 px dan pudar, 600 ms, bertingkat 60 ms, memakai IntersectionObserver.
- Judul hero: tiap kata muncul dari kabur 12 px dan geser 24 px, 700 ms, bertingkat 80 ms, sekali saat dimuat.
- Lima tahap ekosistem: kartu lengket bertumpuk dengan `position: sticky`, kartu sebelumnya mengecil lewat animasi berbasis gulir CSS bila didukung.
- Pita bidang usaha berjalan 40 detik per putaran, berhenti saat disorot. Hanya satu pita di beranda.
- `prefers-reduced-motion: reduce` mematikan semua animasi di atas. Isi langsung tampil.

## Lapisan

| Lapisan | z-index |
| --- | --- |
| Navbar | 40 |
| Navigasi bawah ponsel | 40 |
| Laci dan menu | 50 |
| Modal | 60 |
| Pesan kilat | 70 |

## Ikon

Phosphor (garis, 24 px) lewat `codeat3/blade-phosphor-icons`. Satu keluarga untuk seluruh aplikasi.

## Catatan aset

GIF Bos Allo yang diterima berlatar abu polos, bukan transparan, berukuran 400 x 225. Karena itu `x-boss-avatar` menampilkannya di dalam bingkai bersudut kartu, bukan mengambang di atas latar.
