# Design System

Arsitektur, struktur kode, alur routing, tata letak, komponen, dan detail tombol alloBos

- **Produk:** alloBos, pendamping usaha berbasis AI untuk warga Gunungkidul
- **Arsitektur acuan:** github.com/GiskaSaputra/pnc-elevate (Laravel 12, Blade, Livewire 3, Alpine, Tailwind 3)
- **Referensi visual:** dribbble.com/shots/26954032-Creative-agency-web-ui-design
- **Alat desain:** design-taste-frontend (taste skill) di Claude Code
- **Penyusun:** Giska Saputra
- **Versi:** Final 2.0, 18 November 2026 (disesuaikan dengan sistem yang sudah berjalan: layanan pihak ketiga, pembayaran kelas QRIS, suara Bos Allo, Warung 3D orang pertama, sertifikat premium, jaringan uji alloBos)
- **Aplikasi berjalan:** https://allobos.zite.my.id
- **Kode sumber:** github.com/GiskaSaputra/allobos (cabang main)

## 1. Arsitektur sistem

alloBos memakai arsitektur proyek acuan PNC Elevate: aplikasi Laravel monolit yang merender HTML di server, dengan Livewire dan Alpine untuk interaksi, dan JavaScript mandiri hanya untuk dua halaman berat, yaitu Ruang Bos dan Warung Bos 3D. Layanan luar (Gemini, Speechify, Resend, SumoPod) selalu dipanggil dari server lewat satu kelas klien per layanan, sehingga kunci API tidak pernah sampai ke peramban dan kegagalan satu layanan tidak menghentikan alur.

| Lapisan | Teknologi | Tanggung jawab |
| --- | --- | --- |
| Tampilan | Blade, komponen Blade, Tailwind 3, Alpine | Halaman, tata letak, komponen antarmuka |
| Interaksi | Livewire 3 | Formulir bertahap, daftar bersaring, kalkulator HPP, langkah yang dicentang |
| Skrip halaman | JavaScript modul lewat Vite | boss-room.js (suara, video avatar, tahan untuk bicara), warung-3d.js dan folder warung/ (Three.js orang pertama), motion.js (animasi muncul dan gulir di semua halaman publik), scale-frame.js (pratinjau sertifikat), signature-pad.js, wallet.js, charts.js, theme.js |
| Routing | routes/web.php, routes/auth.php | Kelompok rute per peran dengan awalan dan middleware |
| Controller | app/Http/Controllers/{Public,Warga,Bos,Pengelola,Admin} | Menerima permintaan, memvalidasi, memanggil service, mengembalikan tampilan |
| Service | app/Services/{Ai,Boss,Bosses,Certificates,Chain,Game,Insights,Media,Mentoring,Payments,Plans,Podcasts,Products,Programs,Readiness,Shops} | Aturan bisnis per domain |
| Pekerjaan antrean | app/Jobs, layanan queue | Proses lama: rangkuman sesi, cek produk, ringkasan siniar, pembuatan dan pencatatan sertifikat, pencabutan |
| Penjadwal | app/Console/Commands, layanan scheduler | Pengingat sesi, menutup sesi Bos Allo yang menganggur, kedaluwarsa pembayaran tiap lima menit |
| Model | Eloquent, app/Models, app/Enums | Data dan status |
| Akses | spatie/laravel-permission, middleware role, policy | Peran dan kepemilikan data |
| AI | Google Gemini API lewat App\Services\Ai\GeminiClient | Satu pintu ke Gemini: model gemini-3.1-flash-lite dengan cadangan gemini-flash-lite-latest dan gemini-flash-latest, rotasi lima kunci, JSON berskema dengan propertyOrdering, kuota, log, penyamaran data |
| Data | MySQL 8.4 | Penyimpanan utama |
| Berkas | Laravel Storage (disk public dan private) | Foto produk, sampul, sertifikat |
| Dokumen | barryvdh/laravel-dompdf, phpoffice/phpspreadsheet, pustaka QR | PDF rencana dan sertifikat ber-QR, unduhan XLSX |
| Grafik | Chart.js | Grafik skor dan wawasan |
| 3D | Three.js | Adegan Warung Bos 3D |
| Web3 | Kontrak pintar Solidity, Hardhat, ethers.js, Anvil (Foundry) | Mencatat dan memeriksa sidik digital sertifikat di Jaringan Uji alloBos (chain id 31337) |
| Suara masuk | Web Speech API (SpeechRecognition) | Mengubah ucapan warga menjadi teks di peramban |
| Suara keluar | Speechify API lewat App\Services\Ai\VoiceClient | Suara Bos Allo berbahasa Indonesia (model simba-3.5, suara bima), disimpan di tembolok berkas MP3 |
| Email | Resend (SMTP) | Verifikasi email, reset sandi, undangan Bos, notifikasi |
| Pembayaran | SumoPod Payment (QRIS) lewat App\Services\Payments\SumoPodClient | Pembayaran kelas berbayar dan webhook status |
| Video dan siniar | YouTube oEmbed dan pemutar YouTube | Mengambil judul dan sampul siniar, memutar episode |
| Deploy | Docker Compose di VPS, nginx, Let's Encrypt, GitHub Actions | Layanan app, queue, scheduler, mysql, chain |

### 1.1 Alur satu permintaan

1. Peramban mengirim permintaan ke rute.
2. Middleware berjalan berurutan: auth, verified, role, onboarded, throttle.
3. Controller memvalidasi masukan lewat Form Request.
4. Controller memanggil service domain. Service membaca dan menulis model.
5. Bila butuh AI: service memanggil GeminiClient secara langsung untuk jawaban cepat, atau menaruh pekerjaan di antrean untuk proses lama.
6. Controller mengembalikan tampilan Blade atau JSON untuk skrip halaman.
7. Peristiwa penting memicu notifikasi dan penghitungan ulang Skor Siap Usaha.

### 1.2 Alur panggilan AI

1. Service menyiapkan isi dan skema JSON, lalu memanggil generateJson dengan kunci fitur.
2. Klien memeriksa fitur aktif, kunci API tersedia, dan kuota harian pengguna.
3. PiiRedactor menyamarkan nama, nomor, email, dan alamat.
4. PromptRegistry mengambil instruksi sistem versi aktif dari basis data, atau bawaan dari config/ai.php.
5. Permintaan dikirim. Bila satu kunci terkena batas atau ditolak (401, 403, 429, 503), kunci berikutnya dipakai. Bila model tidak tersedia (404, 500), model cadangan berikutnya dipakai.
6. Jawaban divalidasi terhadap skema. Gagal: ulang satu kali, lalu lempar AiException.
7. Permintaan dicatat di ai_requests: fitur, durasi, token, status.
8. Service menangani AiException dengan cadangan tanpa AI.

### 1.3 Alur pencatatan sertifikat ke blockchain

1. Penanda tangan menekan Tanda Tangani Sertifikat. SigningService mencatat tanda tangan dan menaruh GenerateCertificates di antrean.
2. CertificateBuilder membuat PDF tiap sertifikat dengan spesimen tanda tangan, kode, dan QR.
3. CertificateHasher menyusun data kanonik dan menghitung SHA-256.
4. AnchorCertificates memanggil ChainClient, yang menjalankan skrip web3/anchor.mjs (ethers.js) untuk memanggil issueBatch.
5. Hash transaksi disimpan di chain_transactions. Status sertifikat Menunggu Pencatatan.
6. Setelah transaksi terkonfirmasi, nomor blok disimpan dan status menjadi Tercatat. Notifikasi dikirim.
7. Halaman verifikasi memanggil ChainClient::verify, yang membaca kontrak lewat RPC dengan tembolok 60 detik, lalu membandingkan dengan sidik digital di server.

### 1.3b Alur suara Bos Allo

1. Warga menahan tombol bicara. SpeechRecognition (id-ID) menampilkan transkrip sementara. Saat tombol dilepas, teks langsung terkirim.
2. POST /ruang-bos/{session}/kirim mengembalikan jawaban teks dan id giliran.
3. Skrip memanggil POST /ruang-bos/{session}/suara dengan id giliran itu. VoiceClient meminta MP3 ke Speechify, atau mengambilnya dari tembolok storage/suara/{sha1}.mp3 bila kalimat yang sama pernah dibuat.
4. Teks jawaban dan suara ditampilkan bersamaan: avatar beralih ke video bicara saat audio mulai, kembali ke video diam saat audio selesai.
5. Bila Speechify gagal atau kunci kosong, jawaban tetap tampil sebagai teks yang diketik per kata. Sesi tidak terhenti.

### 1.3c Alur pembayaran kelas

1. Warga menekan Daftar Kelas dan diarahkan ke halaman konfirmasi /warga/kelas/{program}/konfirmasi yang menampilkan jadwal, kuota, dan harga.
2. Kelas gratis: pendaftaran langsung berstatus Terdaftar.
3. Kelas berbayar: PaymentService membuat pendaftaran berstatus Menunggu Bayar dan meminta tagihan QRIS ke SumoPod. Warga diarahkan ke /warga/pembayaran/{payment} yang menampilkan QRIS, nominal, dan batas waktu.
4. SumoPod mengirim webhook ke POST /webhook/sumopod. Permintaan diperiksa dengan X-Webhook-Token atau tanda tangan Svix (HMAC-SHA256). Webhook yang sama diproses sekali saja.
5. payment.completed mengubah pembayaran menjadi Lunas dan pendaftaran menjadi Terdaftar, lalu notifikasi dikirim. payment.failed atau payment.expired membatalkan pendaftaran dan melepas kuota.
6. Perintah ExpirePayments berjalan tiap lima menit untuk menutup tagihan yang lewat batas waktu.

### 1.4 Struktur folder

| Jalur | Isi |
| --- | --- |
| app/Enums | RoleName, SessionStatus, MentoringStatus, ProgramStatus, EnrollmentStatus (termasuk Menunggu Bayar), PaymentStatus, BossStatus, ShopStatus, GameStatus, ContentStatus, ProgramKind, MarketSignal, CertificateStatus, CheckStatus, StepOrigin |
| app/Http/Controllers/Public | PageController, ProgramController, PodcastController, ShopController, BecomeBossController, CertificateVerifyController |
| app/Http/Controllers/Signing | SigningController, SpecimenController |
| app/Http/Controllers/Warga | DashboardController, OnboardingController, BossRoomController, MentoringController, ProgramController, PaymentController, CertificateController, PodcastController, GameController, ProductCheckController, OpportunityController, PlanController, StepController, ShopController, ProductController |
| app/Http/Controllers | SumoPodWebhookController (webhook pembayaran, tanpa CSRF, diverifikasi token atau tanda tangan) |
| app/Http/Controllers/Bos | DashboardController, SlotController, SessionController, ProfileController |
| app/Http/Controllers/Pengelola | DashboardController, ProgramController, SpeakerController, PodcastController, BossController, ShopModerationController, CertificateController, AnnouncementController, InsightController |
| app/Http/Controllers/Admin | DashboardController, UserController, AiController, PageController, SettingController, EventBankController, ChainController, SystemController |
| app/Http/Middleware | EnsureRole, EnsureOnboarded, EnsureAccountIsActive, EnsureActiveBoss |
| app/Services/Ai | GeminiClient, VoiceClient (Speechify), AiSettings, PromptRegistry, FeatureSchemas, Schema, PiiRedactor, AiException |
| app/Services | Payments/SumoPodClient, Payments/PaymentService, Media/ImageStore, Insights/InsightService, Bosses/CurationService, Certificates/AnchorService, Certificates/VerificationService, Game/GameService, Game/GameRules, Plans/LearningContext, Plans/StepService, Boss/BossEngine, Boss/NoteBuilder, Mentoring/MentoringService, Programs/ProgramService, Podcasts/PodcastService, Game/GameEngine, Game/EventPicker, Products/ProductChecker, Plans/OpportunityFinder, Plans/PlanBuilder, Plans/CostCalculator, Shops/ShopService, Readiness/ReadinessCalculator, Certificates/SigningService, Certificates/CertificateBuilder, Certificates/CertificateHasher, Chain/ChainClient |
| app/Jobs | SummarizeBossSession, CheckProduct, SummarizePodcast, GenerateCertificates, AnchorCertificates, RevokeCertificate |
| app/Console/Commands | SendReminders, CloseIdleBossSessions, ExpirePayments, ResetDemo |
| app/Support | Nav (penentu menu aktif berdasarkan nama rute) |
| web3 | contracts/AlloBosCertificate.sol, scripts/deploy.mjs, deploy-ethers.mjs (deploy ke Anvil tanpa Hardhat), anchor.mjs, revoke.mjs, verify.mjs, test, hardhat.config |
| config | ai.php, chain.php, menus.php, readiness.php, game.php, features.php |
| resources/views | components/layouts, components, public, auth, warga, boss-room, game, signing, bos, pengelola, admin, pdf, errors |
| resources/js | app.js, boss-room.js, avatar.js, warung-3d.js, warung/ (game, world, player, input, interact, customers), motion.js, scale-frame.js, signature-pad.js, wallet.js, charts.js, forms.js, theme.js |
| public/gif/bos-allo | allo-diam.mp4, allo-bicara.mp4 (720p, pengganti GIF agar tajam dan ringan), GIF lama sebagai cadangan, allo-transparan.webp |
| public/image | Logo, favikon, pose Bos Allo, latar sertifikat (sertifikat-latar.png) |
| docs | KONSEP.md, SPESIFIKASI.md, ALUR_BISNIS.md, DESAIN_SISTEM.md, DESAIN.md, PROGRESS.md, UJI_MANUAL.md |

### 1.5 Arsitektur deploy

| Lapisan | Isi |
| --- | --- |
| Domain | allobos.zite.my.id, sertifikat HTTPS Let's Encrypt yang diperbarui otomatis |
| Proksi | nginx di VPS meneruskan ke 127.0.0.1:3040. Batas unggah 25 MB |
| app | PHP 8.4 dan server web dalam satu image. upload_max_filesize 20 MB, post_max_size 25 MB, validasi aplikasi 15 MB per gambar |
| queue | Image yang sama, menjalankan queue:work untuk pekerjaan AI dan sertifikat |
| scheduler | Image yang sama, menjalankan schedule:work (pengingat, sesi menganggur, kedaluwarsa pembayaran) |
| mysql | MySQL 8.4 dengan volume tetap |
| chain | Anvil (ghcr.io/foundry-rs/foundry:v1.5.1), chain id 31337, hanya dapat dicapai dari jaringan Docker di http://chain:8545. Status rantai disimpan ke volume tiap 30 detik sehingga catatan sertifikat bertahan setelah mulai ulang |

- Penerapan: arsip git dikirim lewat SSH ke VPS, lalu docker compose up -d --build. GitHub Actions (ci.yml) menjalankan tes, Pint, dan build aset di tiap push.
- Variabel lingkungan: APP_URL, APP_DEMO, DB_*, GEMINI_API_KEY (lima kunci dipisah koma), GEMINI_MODEL, GEMINI_FALLBACK_MODELS, SPEECHIFY_API_KEY, SPEECHIFY_VOICE, MAIL_* (Resend), SUMOPOD_API_KEY, SUMOPOD_BASE_URL, SUMOPOD_WEBHOOK_TOKEN, SUMOPOD_WEBHOOK_SECRET, DEMO_PASSWORD, serta CHAIN_NAME, CHAIN_ID, CHAIN_RPC_URL, CHAIN_EXPLORER_URL, CERT_CONTRACT_ADDRESS, ISSUER_PRIVATE_KEY, CERT_HASH_SALT.
- Semua nilai rahasia hanya ada di berkas .env server dan lokal, tidak pernah di repositori. Dompet penerbit dibuat di server.
- Image aplikasi menyertakan Node agar pekerjaan antrean dapat menjalankan skrip ethers.js. Kontrak di-deploy sekali ke Anvil dengan deploy-ethers.mjs dan alamatnya disimpan di .env.
- APP_DEMO=false di produksi, sehingga lencana Demo tidak tampil.

## 2. Peran dan penjaga akses

| Middleware | Fungsi | Bila gagal |
| --- | --- | --- |
| auth | Harus masuk | Ke halaman Masuk |
| verified | Email terverifikasi | Ke halaman verifikasi |
| role:warga dan sejenisnya | Peran aktif sesuai | Halaman 403 dengan tautan ke dashboard peran |
| onboarded | Profil Usaha selesai | Ke langkah onboarding yang belum diisi |
| boss.active | Bos berstatus Aktif | Ke halaman status kurasi |
| throttle | Batas laju pada semua POST | Pesan menunggu beserta lamanya |
| signed | Tautan undangan sah | Halaman tautan tidak berlaku |

## 3. Alur routing

Penamaan mengikuti proyek acuan: URI berbahasa Indonesia, nama rute berawalan peran. Setelah masuk, /dashboard mengarahkan ke dashboard peran aktif.

### 3.1 Publik (tanpa middleware)

| Metode | URI | Nama rute | Controller dan aksi |
| --- | --- | --- | --- |
| GET | / | home | Public\PageController@home |
| GET | /kelas | programs.index | Public\ProgramController@index |
| GET | /kelas/{program:slug} | programs.show | Public\ProgramController@show |
| GET | /siniar | podcasts.index | Public\PodcastController@index |
| GET | /siniar/{podcast:slug} | podcasts.show | Public\PodcastController@show |
| GET | /lapak | shops.index | Public\ShopController@index |
| GET | /lapak/{shop:slug} | shops.show | Public\ShopController@show |
| POST | /lapak/{shop:slug}/pesan/{product} | shops.order | Public\ShopController@order (wajib masuk, catat klik, alihkan ke WhatsApp) |
| POST | /lapak/{shop:slug}/lapor | shops.report | Public\ShopController@report (wajib masuk) |
| GET | /bos | bosses.index | Public\PageController@bosses |
| GET | /jadi-bos | become-boss | Public\BecomeBossController@create |
| POST | /jadi-bos | become-boss.store | Public\BecomeBossController@store |
| GET | /verifikasi | certificates.verify.form | Public\CertificateVerifyController@form |
| POST | /verifikasi | certificates.verify.lookup | Public\CertificateVerifyController@lookup |
| GET | /verifikasi/{code} | certificates.verify | Public\CertificateVerifyController@show (baca kontrak pintar) |
| GET | /tentang, /bantuan, /privasi, /syarat, /kontak | pages.{slug} | Public\PageController@show |
| GET | /undangan/{invitation} | invitations.accept | InvitationAcceptController@show (signed) |
| POST | /undangan/{invitation} | invitations.accept.store | InvitationAcceptController@store (signed) |
| POST | /webhook/sumopod | webhooks.sumopod | SumoPodWebhookController (tanpa CSRF, diverifikasi token atau tanda tangan Svix) |

### 3.2 Autentikasi (routes/auth.php)

| Metode | URI | Nama rute | Controller dan aksi |
| --- | --- | --- | --- |
| GET, POST | /daftar | register | Auth\RegisteredUserController |
| GET, POST | /masuk | login | Auth\AuthenticatedSessionController |
| POST | /keluar | logout | Auth\AuthenticatedSessionController@destroy |
| GET, POST | /lupa-sandi | password.request, password.email | Auth\PasswordResetLinkController |
| GET, POST | /reset-sandi/{token} | password.reset, password.store | Auth\NewPasswordController |
| GET | /verifikasi-email | verification.notice | Auth\EmailVerificationPromptController |
| GET | /dashboard | dashboard | DashboardController (alihkan sesuai peran aktif) |
| POST | /peran-aktif | active-role.update | ActiveRoleController@update |
| GET, PATCH | /profil | profile.edit, profile.update | ProfileController |
| GET, POST | /notifikasi | notifications.index, notifications.read | NotificationController |

### 3.3 Warga (awalan /warga, auth, verified, role:warga, onboarded)

| Metode | URI | Nama rute | Controller dan aksi |
| --- | --- | --- | --- |
| GET | /warga/kenali/{step?} | warga.onboarding.show | Warga\OnboardingController@show (tanpa onboarded) |
| POST | /warga/kenali/{step} | warga.onboarding.store | Warga\OnboardingController@store |
| GET | /warga | warga.dashboard | Warga\DashboardController |
| GET | /warga/jejak | warga.journey | Warga\DashboardController@journey |
| GET | /warga/temu-bos | warga.mentoring.index | Warga\MentoringController@index |
| GET | /warga/temu-bos/bos/{boss} | warga.mentoring.boss | Warga\MentoringController@boss |
| POST | /warga/temu-bos/slot/{slot} | warga.mentoring.book | Warga\MentoringController@book |
| GET | /warga/temu-bos/sesi/{session} | warga.mentoring.show | Warga\MentoringController@show |
| POST | /warga/temu-bos/sesi/{session}/batal | warga.mentoring.cancel | Warga\MentoringController@cancel |
| POST | /warga/temu-bos/sesi/{session}/nilai | warga.mentoring.rate | Warga\MentoringController@rate |
| GET | /warga/kelas | warga.programs.index | Warga\ProgramController@index |
| GET | /warga/kelas/{program}/konfirmasi | warga.programs.confirm | Warga\ProgramController@confirm (ringkasan jadwal dan harga) |
| POST | /warga/kelas/{program}/daftar | warga.programs.enroll | Warga\ProgramController@enroll (gratis langsung terdaftar, berbayar ke pembayaran) |
| GET | /warga/pembayaran/{payment} | warga.payments.show | Warga\PaymentController@show (QRIS, nominal, batas waktu, status) |
| POST | /warga/kelas/{program}/batal | warga.programs.cancel | Warga\ProgramController@cancel |
| POST | /warga/kelas/sesi/{session}/hadir | warga.programs.attend | Warga\ProgramController@attend |
| GET | /warga/sertifikat | warga.certificates.index | Warga\CertificateController@index |
| GET | /warga/sertifikat/{certificate} | warga.certificates.show | Warga\CertificateController@show |
| GET | /warga/sertifikat/{certificate}/pratinjau | warga.certificates.preview | Warga\CertificateController@preview (tampilan sertifikat sebelum diunduh) |
| GET | /warga/sertifikat/{certificate}/pdf | warga.certificates.pdf | Warga\CertificateController@pdf |
| PATCH | /warga/sertifikat/{certificate} | warga.certificates.update | Warga\CertificateController@update (nama atau inisial, tampil di lapak) |
| POST | /warga/siniar/{podcast}/dengar | warga.podcasts.listen | Warga\PodcastController@listen |
| POST | /warga/siniar/{podcast}/catatan | warga.podcasts.note | Warga\PodcastController@note |
| GET | /warga/cek-produk | warga.checks.index | Warga\ProductCheckController@index |
| POST | /warga/cek-produk | warga.checks.store | Warga\ProductCheckController@store |
| GET | /warga/cek-produk/{check} | warga.checks.show | Warga\ProductCheckController@show |
| PATCH | /warga/cek-produk/{check} | warga.checks.update | Warga\ProductCheckController@update |
| POST | /warga/cek-produk/{check}/ulang | warga.checks.retry | Warga\ProductCheckController@retry (pemeriksaan macet lebih dari 3 menit) |
| GET | /warga/peluang | warga.opportunities.index | Warga\OpportunityController@index |
| POST | /warga/peluang | warga.opportunities.generate | Warga\OpportunityController@generate |
| POST | /warga/peluang/{opportunity}/pilih | warga.opportunities.choose | Warga\OpportunityController@choose |
| GET | /warga/rencana | warga.plan.show | Warga\PlanController@show |
| POST | /warga/rencana | warga.plan.build | Warga\PlanController@build |
| PATCH | /warga/rencana | warga.plan.update | Warga\PlanController@update |
| GET | /warga/rencana/pdf | warga.plan.pdf | Warga\PlanController@pdf |
| GET | /warga/rencana/hpp | warga.plan.cost | Warga\PlanController@cost (Livewire) |
| GET | /warga/langkah | warga.steps.index | Warga\StepController@index |
| POST | /warga/langkah | warga.steps.store | Warga\StepController@store |
| PATCH | /warga/langkah/{step} | warga.steps.update | Warga\StepController@update |
| DELETE | /warga/langkah/{step} | warga.steps.destroy | Warga\StepController@destroy |
| GET | /warga/lapak-saya | warga.shop.edit | Warga\ShopController@edit |
| POST, PATCH | /warga/lapak-saya | warga.shop.store, warga.shop.update | Warga\ShopController |
| POST | /warga/lapak-saya/ajukan | warga.shop.submit | Warga\ShopController@submit |
| POST | /warga/lapak-saya/tutup | warga.shop.toggle | Warga\ShopController@toggle |
| GET, POST | /warga/lapak-saya/produk | warga.products.create, warga.products.store | Warga\ProductController |
| GET, PATCH, DELETE | /warga/lapak-saya/produk/{product} | warga.products.edit, update, destroy | Warga\ProductController |

### 3.4 Ruang Bos (awalan /ruang-bos, role:warga, onboarded)

| Metode | URI | Nama rute | Controller dan aksi |
| --- | --- | --- | --- |
| GET | /ruang-bos | boss-room.setup | Warga\BossRoomController@setup |
| POST | /ruang-bos | boss-room.store | Warga\BossRoomController@store |
| GET | /ruang-bos/{session} | boss-room.show | Warga\BossRoomController@show |
| POST | /ruang-bos/{session}/mulai | boss-room.start | Warga\BossRoomController@start (JSON) |
| POST | /ruang-bos/{session}/kirim | boss-room.send | Warga\BossRoomController@send (JSON, mengembalikan id giliran) |
| POST | /ruang-bos/{session}/suara | boss-room.voice | Warga\BossRoomController@voice (MP3 Speechify untuk id giliran) |
| POST | /ruang-bos/{session}/akhiri | boss-room.finish | Warga\BossRoomController@finish |
| GET | /ruang-bos/{session}/catatan | boss-room.note | Warga\BossRoomController@note |
| GET | /ruang-bos/{session}/catatan/pdf | boss-room.note.pdf | Warga\BossRoomController@pdf |
| POST | /ruang-bos/{session}/langkah | boss-room.steps | Warga\BossRoomController@addSteps |
| GET | /ruang-bos/riwayat | boss-room.history | Warga\BossRoomController@history |

### 3.5 Warung Bos 3D (awalan /warung, role:warga, onboarded)

| Metode | URI | Nama rute | Controller dan aksi |
| --- | --- | --- | --- |
| GET | /warung | game.index | Warga\GameController@index |
| POST | /warung | game.store | Warga\GameController@store |
| GET | /warung/{game} | game.play | Warga\GameController@play |
| POST | /warung/{game}/belanja | game.buy | Warga\GameController@buy (JSON, belanja stok pagi) |
| POST | /warung/{game}/rak | game.stock | Warga\GameController@stock (JSON, menata barang ke rak) |
| POST | /warung/{game}/harga | game.price | Warga\GameController@price (JSON) |
| POST | /warung/{game}/buka-warung | game.open | Warga\GameController@open (JSON, menyusun rencana pembeli hari itu) |
| POST | /warung/{game}/layani | game.serve | Warga\GameController@serve (JSON, satu pembeli dilayani, idempoten) |
| POST | /warung/{game}/pergi | game.miss | Warga\GameController@miss (JSON, pembeli pergi karena menunggu terlalu lama) |
| POST | /warung/{game}/tutup | game.close | Warga\GameController@close (JSON, menutup hari dan menghitung hasil) |
| POST | /warung/{game}/buka | game.run | Warga\GameController@run (JSON, Mode Ringan: satu hari dihitung sekaligus) |
| POST | /warung/{game}/lanjut | game.next | Warga\GameController@next (JSON) |
| POST | /warung/{game}/simpan | game.save | Warga\GameController@save (simpan pelajaran ke Jejak Usaha) |
| GET | /warung/{game}/rapor | game.report | Warga\GameController@report |

### 3.6 Tanda Tangan (awalan /tanda-tangan, role:bos,pengelola,admin)

| Metode | URI | Nama rute | Controller dan aksi |
| --- | --- | --- | --- |
| GET | /tanda-tangan | signing.index | Signing\SigningController@index (kelas yang menunggu) |
| GET | /tanda-tangan/spesimen | signing.specimen.edit | Signing\SpecimenController@edit |
| POST | /tanda-tangan/spesimen | signing.specimen.store | Signing\SpecimenController@store |
| GET | /tanda-tangan/kelas/{program} | signing.show | Signing\SigningController@show (penerima dan pratinjau) |
| GET | /tanda-tangan/kelas/{program}/pratinjau/{certificate} | signing.preview | Signing\SigningController@preview |
| POST | /tanda-tangan/kelas/{program}/keluarkan/{certificate} | signing.exclude | Signing\SigningController@exclude |
| POST | /tanda-tangan/kelas/{program} | signing.sign | Signing\SigningController@sign (password.confirm) |
| POST | /tanda-tangan/kelas/{program}/dompet | signing.wallet | Signing\SigningController@wallet (opsional, JSON) |

Hanya penanda tangan yang ditetapkan untuk kelas itu yang lolos policy. Akun lain menerima 403.

### 3.7 Bos (awalan /bos-area, role:bos)

| Metode | URI | Nama rute | Controller dan aksi |
| --- | --- | --- | --- |
| GET | /bos-area | bos.dashboard | Bos\DashboardController |
| GET, PATCH | /bos-area/profil | bos.profile.edit, bos.profile.update | Bos\ProfileController |
| GET, POST | /bos-area/slot | bos.slots.index, bos.slots.store | Bos\SlotController (boss.active) |
| DELETE | /bos-area/slot/{slot} | bos.slots.destroy | Bos\SlotController@destroy |
| GET | /bos-area/sesi | bos.sessions.index | Bos\SessionController@index |
| GET | /bos-area/sesi/{session} | bos.sessions.show | Bos\SessionController@show |
| POST | /bos-area/sesi/{session}/setujui | bos.sessions.approve | Bos\SessionController@approve |
| POST | /bos-area/sesi/{session}/tolak | bos.sessions.reject | Bos\SessionController@reject |
| POST | /bos-area/sesi/{session}/selesai | bos.sessions.complete | Bos\SessionController@complete |
| POST | /bos-area/sesi/{session}/tidak-hadir | bos.sessions.no-show | Bos\SessionController@noShow |

### 3.8 Pengelola IPI (awalan /pengelola, role:pengelola,admin)

| Metode | URI | Nama rute | Controller dan aksi |
| --- | --- | --- | --- |
| GET | /pengelola | pengelola.dashboard | Pengelola\DashboardController |
| Resource | /pengelola/kelas | pengelola.programs.* | Pengelola\ProgramController |
| POST | /pengelola/kelas/{program}/terbitkan | pengelola.programs.publish | Pengelola\ProgramController@publish |
| POST | /pengelola/kelas/{program}/tutup | pengelola.programs.close | Pengelola\ProgramController@close |
| GET | /pengelola/kelas/{program}/peserta | pengelola.programs.participants | Pengelola\ProgramController@participants |
| GET | /pengelola/kelas/{program}/peserta/xlsx | pengelola.programs.export | Pengelola\ProgramController@export |
| POST | /pengelola/kelas/sesi/{session}/kode | pengelola.programs.code | Pengelola\ProgramController@code |
| POST | /pengelola/kelas/{program}/penanda-tangan | pengelola.programs.signers | Pengelola\ProgramController@signers |
| GET | /pengelola/sertifikat | pengelola.certificates.index | Pengelola\CertificateController@index |
| POST | /pengelola/sertifikat/kelas/{program}/catat-ulang | pengelola.certificates.retry | Pengelola\CertificateController@retry |
| POST | /pengelola/sertifikat/{certificate}/cabut | pengelola.certificates.revoke | Pengelola\CertificateController@revoke |
| Resource | /pengelola/pembicara | pengelola.speakers.* | Pengelola\SpeakerController |
| Resource | /pengelola/siniar | pengelola.podcasts.* | Pengelola\PodcastController |
| POST | /pengelola/siniar/ambil | pengelola.podcasts.fetch | Pengelola\PodcastController@fetch |
| POST | /pengelola/siniar/{podcast}/ringkas | pengelola.podcasts.summarize | Pengelola\PodcastController@summarize |
| POST | /pengelola/siniar/{podcast}/terbitkan | pengelola.podcasts.publish | Pengelola\PodcastController@publish |
| GET | /pengelola/bos | pengelola.bosses.index | Pengelola\BossController@index |
| POST | /pengelola/bos/undang | pengelola.bosses.invite | Pengelola\BossController@invite |
| POST | /pengelola/bos/{boss}/aktifkan | pengelola.bosses.activate | Pengelola\BossController@activate |
| POST | /pengelola/bos/{boss}/tolak | pengelola.bosses.reject | Pengelola\BossController@reject |
| GET | /pengelola/lapak | pengelola.shops.index | Pengelola\ShopModerationController@index |
| POST | /pengelola/lapak/{shop}/setujui | pengelola.shops.approve | Pengelola\ShopModerationController@approve |
| POST | /pengelola/lapak/{shop}/tolak | pengelola.shops.reject | Pengelola\ShopModerationController@reject |
| POST | /pengelola/lapak/{shop}/turunkan | pengelola.shops.takedown | Pengelola\ShopModerationController@takedown |
| Resource | /pengelola/pengumuman | pengelola.announcements.* | Pengelola\AnnouncementController |
| GET | /pengelola/wawasan | pengelola.insights | Pengelola\InsightController |

### 3.9 Admin (awalan /admin, role:admin)

| Metode | URI | Nama rute | Controller dan aksi |
| --- | --- | --- | --- |
| GET | /admin | admin.dashboard | Admin\DashboardController |
| Resource | /admin/pengguna | admin.users.* | Admin\UserController |
| POST | /admin/pengguna/{user}/reset-sandi | admin.users.reset | Admin\UserController@sendReset |
| GET | /admin/ai | admin.ai.index | Admin\AiController@index |
| PATCH | /admin/ai/{feature} | admin.ai.update | Admin\AiController@update |
| POST | /admin/ai/prompt/{feature} | admin.ai.prompts.store | Admin\AiController@storePrompt |
| POST | /admin/ai/prompt/{feature}/uji | admin.ai.prompts.test | Admin\AiController@testPrompt |
| Resource | /admin/halaman | admin.pages.* | Admin\PageController |
| Resource | /admin/bank-kejadian | admin.events.* | Admin\EventBankController |
| GET, PATCH | /admin/pengaturan | admin.settings.* | Admin\SettingController (bobot skor, kuota, fitur) |
| GET | /admin/blockchain | admin.chain | Admin\ChainController@index (jaringan, kontrak, saldo penerbit, transaksi) |
| GET | /admin/audit | admin.audit | Admin\SystemController@audit |

### 3.10 Peta pengalihan

| Keadaan | Tujuan |
| --- | --- |
| Selesai daftar | /verifikasi-email, lalu /warga/kenali/1 |
| Masuk sebagai warga, onboarding belum selesai | Langkah onboarding pertama yang kosong |
| Masuk sebagai warga, onboarding selesai | /warga |
| Masuk sebagai bos, pengelola, admin | /bos-area, /pengelola, /admin |
| Tamu membuka halaman berlogin | /masuk?tujuan=..., lalu kembali ke halaman yang dituju |
| Tamu menekan Pesan lewat WhatsApp atau Laporkan Lapak | /masuk dengan tujuan halaman lapak |
| Mendaftar kelas berbayar | /warga/pembayaran/{payment}, lalu kembali ke kelas setelah Lunas |
| Pembayaran kedaluwarsa | Halaman kelas dengan pesan dan tombol Daftar Lagi |
| Tamu menekan Daftar Kelas atau Tanya Bos Allo | /daftar dengan catatan tujuan |
| Memindai QR sertifikat | /verifikasi/{code}, tanpa perlu masuk |
| Penanda tangan belum punya spesimen membuka kelas | /tanda-tangan/spesimen, lalu kembali ke kelas |
| Peran tidak sesuai | Halaman 403 dengan tombol ke dashboard peran aktif |

## 4. Arah visual

Seluruh tampilan dan animasi mengikuti referensi yang ditetapkan pemilik proyek: dribbble.com/shots/26954032-Creative-agency-web-ui-design. Penerapan dilakukan di Claude Code dengan taste skill (design-taste-frontend). Nilai hasil turunan referensi dicatat di docs/DESAIN.md dan dirangkum di bawah: palet hijau sage dan kuning limau (diambil dari logo dan nuansa alam Gunungkidul), huruf Montserrat untuk judul dan Ubuntu untuk isi.

> **Aturan pakai:** tampilan tidak menyimpan nilai hex. Semua warna lewat token Tailwind yang menunjuk variabel CSS, sehingga mengganti palet cukup di satu berkas.

### 4.1 Token warna semantik

| Token | Kelas Tailwind | Dipakai untuk |
| --- | --- | --- |
| --surface | bg-surface | Latar halaman |
| --surface-alt | bg-surface-alt | Latar seksi selang-seling |
| --surface-raised | bg-surface-raised | Kartu, modal, menu |
| --fg | text-fg | Judul |
| --fg-body | text-fg-body | Teks isi |
| --fg-muted | text-fg-muted | Keterangan |
| --line | border-line | Garis dan batas |
| --brand | bg-brand, text-brand | Warna utama dari referensi |
| --brand-fg | text-brand-fg | Teks di atas warna utama |
| --accent | bg-accent | Aksen dan sorotan dari referensi |
| --accent-text | text-accent-text | Aksen yang aman untuk teks |
| --success, --warning, --danger, --info | bg-success dan seterusnya | Status |

Tema terang dan gelap memakai token yang sama dengan nilai berbeda, dikendalikan theme.js seperti proyek acuan. Kontras teks isi minimal 4,5 banding 1.

#### 4.1b Nilai token (RGB, dipakai lewat rgb(var(--token)))

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
| --accent | 205 224 72 | sama |
| --accent-text | 82 102 14 | 205 224 72 |
| --success | 38 122 68 | 110 200 140 |
| --warning | 150 96 12 | 232 180 80 |
| --danger | 180 52 36 | 240 120 100 |
| --info | 32 108 88 (sage, pengganti biru) | 128 206 170 |
| --ink, --ink-raised, --ink-fg | 10 12 7, 24 27 18, 244 251 200 | sama (panel gelap di kedua tema) |
| --hero-from, --hero-to | 139 161 58 ke 199 185 46 | sama |
| --mint-from, --mint-to | 116 184 140 ke 190 222 140 | sama |
| --ease-out | cubic-bezier(0.16, 1, 0.3, 1) | sama |

Warna biru tidak dipakai agar seluruh antarmuka satu keluarga dengan logo. Keadaan info memakai hijau sage.

### 4.2 Tipografi, jarak, sudut

| Elemen | Aturan |
| --- | --- |
| Font | Montserrat Variable untuk judul, Ubuntu untuk isi, keduanya di-host sendiri lewat Fontsource dengan display swap. PDF memakai DejaVu Sans dan DejaVu Serif bawaan dompdf. |
| Skala huruf | Display 48 sampai 72 px (beranda), H1 36, H2 28, H3 20, isi 16, kecil 14, label 12 huruf besar berjarak. |
| Tinggi baris | Judul 1,1 sampai 1,2. Isi 1,6. |
| Lebar teks | Paragraf maksimal sekitar 68 karakter. |
| Kisi | Kontainer .container-page maksimal 1440 px dengan tepi 16, 24, dan 40 px. 12 kolom desktop, 4 kolom ponsel, celah 24 px. Kartu dalam satu baris disamakan tingginya. |
| Jarak | Kelipatan 4 px. Antar seksi 96 px desktop, 56 px ponsel. |
| Sudut | Tombol dan masukan berbentuk pil. Kartu rounded-card 28 px. Panel besar dan hero rounded-panel 40 px. |
| Bayangan | Dua tingkat saja: kartu dan lapisan melayang. |
| Ikon | Satu set garis 24 px, tebal garis seragam. |

### 4.3 Gerak

| Pola | Tempat | Aturan |
| --- | --- | --- |
| Judul muncul per kata atau huruf | Hero beranda | Sekali saat dimuat. Teks utuh tetap ada untuk pembaca layar. |
| Muncul saat digulir | Seksi beranda, kartu | Geser 16 px dan pudar, 400 sampai 600 ms, bertingkat 60 ms. |
| Seksi lengket bertahap | Lima tahap ekosistem di beranda | Kartu tahap bertumpuk saat digulir, pola home-scroll.js. |
| Teks berjalan | Pita bidang usaha atau mitra | Berhenti saat disorot. |
| Sorot tombol dan kartu | Semua tombol dan kartu tautan | 150 sampai 200 ms, tanpa lompatan tata letak. |
| motion.js | Semua halaman publik | Elemen bertanda data-reveal muncul saat masuk layar lewat IntersectionObserver, berjalan di ponsel dan desktop, tidak mengecil ke tengah saat diperbesar. |
| Tanda air logo | Kartu kelas, siniar, lapak, bos | Logo transparan di sudut kartu dan chip logo kecil, diam, tanpa animasi berulang. |
| Avatar Bos Allo | Ruang Bos, dashboard | Video MP4 diam dan bicara bergantian, bukan GIF. |
| Transisi halaman dashboard | Dashboard | Tidak ada. Dashboard mengutamakan kecepatan. |
| Kurangi gerak | Semua | prefers-reduced-motion mematikan animasi gulir dan ketik. |

Detail kurva dan durasi disamakan dengan animasi pada referensi saat penerapan.

## 5. Tata letak

| Tata letak | Dipakai oleh | Susunan |
| --- | --- | --- |
| layouts/public | Halaman publik | Navbar transparan yang memadat saat digulir, isi, footer |
| layouts/app | Warga | Navbar atas dengan menu modul, isi lebar terbatas, navigasi bawah di ponsel |
| layouts/dashboard | Bos, Pengelola, Admin | Sidebar kiri dari config/menus.php, topbar dengan tombol Ke Beranda, isi. Menu aktif ditentukan App\Support\Nav sehingga hanya satu menu yang menyala |
| layouts/auth | Masuk, Daftar, sandi | Dua kolom: visual pose Bos Allo yang menetap (tidak ikut bergulir) dan kolom formulir yang bergulir sendiri. Satu kolom di ponsel |
| layouts/stage | Ruang Bos, Warung Bos 3D | Layar penuh tanpa navbar, bilah atas tipis dengan tombol Keluar |
| layouts/shop | Halaman lapak publik dan halaman verifikasi sertifikat | Ringan: tanpa skrip animasi, tanpa pustaka berat |
| errors/layout | 403, 404, 419, 500, 503 | Pesan, penjelasan, satu tombol tindakan |

### 5.1 Navigasi

| Area | Isi menu |
| --- | --- |
| Navbar publik | Kelas, Siniar, Lapak, Bos, Tentang. Kanan: Masuk (ghost), Daftar (primary). Footer: Verifikasi Sertifikat, halaman informasi, tanpa keterangan lomba |
| Halaman detail | Tiap halaman detail (kelas, siniar, lapak, sertifikat, pembayaran) punya x-back-link ke daftar induknya |
| Navbar warga | Beranda, Ruang Bos, Belajar (Kelas, Sertifikat Saya, Siniar, Warung 3D), Rancang (Peluang, Rencana, Langkah, Cek Produk), Lapak Saya, lonceng, menu akun |
| Navigasi bawah warga (ponsel) | Beranda, Ruang Bos, Belajar, Rancang, Lapak |
| Sidebar Bos | Ringkasan, Permintaan Sesi, Jadwal dan Slot, Tanda Tangan Sertifikat, Profil Bos |
| Sidebar Pengelola | Ringkasan, Kelas, Pembicara, Sertifikat, Tanda Tangan Sertifikat, Siniar, Kurasi Bos, Moderasi Lapak, Pengumuman, Wawasan |
| Sidebar Admin | Semua menu Pengelola, lalu Pengguna, Pengaturan AI, Blockchain, Halaman, Bank Kejadian, Pengaturan, Audit |
| Menu akun | Profil, Ganti Peran, Tema, Keluar |

### 5.2 Titik henti

Dirancang dari 360 px. Titik henti Tailwind: sm 640, md 768, lg 1024, xl 1280. Sidebar menjadi laci di bawah lg. Tabel menjadi kartu bertumpuk di bawah md.

## 6. Komponen

| Komponen Blade | Fungsi | Catatan |
| --- | --- | --- |
| x-logo | Logo alloBos (gambar) | Opsi compact untuk navbar sempit |
| x-logo-mark, x-logo-chip | Tanda air logo di kartu dan chip logo kecil | Hiasan, aria-hidden |
| x-back-link | Tautan kembali di halaman detail | Ikon panah dan nama halaman induk |
| x-theme-toggle | Tombol ganti tema | Ikon berganti, pilihan disimpan |
| x-button, x-link-button | Tombol dan tautan bergaya tombol | Lihat bagian 7. Mendukung ikon kiri dan iconRight |
| x-card | Wadah isi | Opsi: polos, tautan, sorot |
| x-field, x-text-input, x-textarea, x-select | Masukan formulir dengan label dan galat | Label selalu terlihat. x-text-input mendukung ikon di kiri dan tombol mata untuk menampilkan sandi |
| x-badge, x-status-badge, x-official-badge | Status dan lencana resmi IPI | Warna dari token status, selalu disertai teks |
| x-demo-badge, x-ai-badge | Lencana Demo dan Perkiraan AI | Perkiraan AI wajib di hasil AI pasar. Demo hanya tampil saat APP_DEMO=true |
| x-modal | Dialog konfirmasi | Fokus terkunci, tutup dengan Esc |
| x-empty-state | Keadaan kosong | Satu kalimat dan satu tombol tindakan |
| x-page-header | Judul halaman dashboard | Judul, keterangan, tombol tindakan utama di kanan |
| x-table | Tabel data | Menjadi kartu di ponsel |
| x-chart | Grafik Chart.js | Warna dari token |
| x-step-progress | Penunjuk langkah onboarding dan fase permainan | Angka dan nama langkah |
| x-score-ring | Skor Siap Usaha | Angka besar, label tingkat, rincian saat diketuk |
| x-boss-avatar | Bos Allo dalam video MP4 16:9 dengan poster WEBP | Lihat bagian 8 |
| x-certificate-card | Kartu sertifikat di Sertifikat Saya | Nama kelas, tanggal, status, tombol unduh dan salin tautan |
| x-verified-badge | Lencana terverifikasi di Lapak dan Jejak Usaha | Menaut ke halaman verifikasi, hanya untuk status Tercatat |
| x-chain-proof | Bukti blockchain di halaman verifikasi | Jaringan, hash transaksi terpotong dengan tombol salin, nomor blok, tautan penjelajah blok |
| x-signature-pad | Kanvas menggambar tanda tangan | Mendukung sentuhan, tombol Hapus dan Simpan, alternatif unggah berkas |
| x-verify-status | Panel status verifikasi | Asli, Dicabut, Belum Tercatat, Tidak Cocok, Tidak Ditemukan: ikon, warna status, dan teks |
| x-product-card, x-shop-card, x-program-card, x-podcast-card, x-boss-card | Kartu daftar | Seluruh kartu dapat diklik, satu tombol utama |
| x-notification-bell, x-account-menu, x-flash | Lonceng, menu akun, pesan kilat | Pesan kilat hilang sendiri kecuali galat |

### 6.1 Keadaan halaman

- **Memuat.** Kerangka abu-abu berbentuk isi, bukan pemutar di tengah layar. Proses AI menampilkan teks keadaan: Bos Allo sedang berpikir, Foto sedang diperiksa.
- **Kosong.** Menyebut apa yang belum ada dan tombol untuk memulainya.
- **Galat.** Menyebut apa yang terjadi dan langkah yang harus dilakukan. Tidak ada kode galat mentah.
- **Berhasil.** Pesan kilat singkat yang menyebut hasilnya, misalnya Lapak diajukan untuk diperiksa.

## 7. Tombol

### 7.1 Varian

| Varian | Tampilan | Dipakai untuk | Batas |
| --- | --- | --- | --- |
| primary | Latar brand, teks brand-fg | Tindakan utama halaman | Satu per tampilan |
| accent | Latar accent | Ajakan di beranda dan hero | Hanya halaman publik |
| secondary | Latar surface-raised, garis line | Tindakan pendamping | Bebas |
| ghost | Tanpa latar, teks fg | Tindakan ringan, navbar | Bebas |
| danger | Latar danger | Hapus, batalkan, turunkan | Selalu dengan konfirmasi |
| glass | Latar tembus di atas gambar atau adegan | Hero bergambar, layar stage | Hanya di atas media |
| icon | Ikon saja, lingkaran | Bisukan, tutup, putar kamera | Wajib aria-label |
| whatsapp | Latar success dengan ikon | Pesan lewat WhatsApp | Hanya di lapak |
| segmen | Deret pilihan, satu aktif berlatar brand | Memilih satu dari beberapa: mode, slot, promosi | Dua sampai lima pilihan |
| chip | Pil kecil bergaris, aktif berlatar brand | Saringan daftar | Dapat digulir mendatar di ponsel |
| sakelar | Tuas hidup dan mati dengan label | Pengaturan yang langsung berlaku | Keadaan juga ditulis dengan teks |

### 7.2 Ukuran dan keadaan

| Ukuran | Tinggi | Huruf | Dipakai |
| --- | --- | --- | --- |
| sm | 36 px | 14 px | Baris tabel, kartu padat |
| md | 44 px | 14 sampai 16 px | Bawaan semua formulir |
| lg | 52 px | 16 px | Hero dan ajakan utama |
| xl | 64 px, lingkaran 72 px | 18 px | Tombol bicara di Ruang Bos |

| Keadaan | Aturan |
| --- | --- |
| Bawaan | Sesuai varian |
| Sorot | Latar satu tingkat lebih gelap atau terang, 150 ms |
| Fokus | Cincin 2 px warna accent dengan jarak 2 px, selalu terlihat saat memakai papan ketik |
| Ditekan | Skala 0,98 |
| Memuat | Teks berganti kata kerja berjalan (Menyimpan, Memeriksa), tombol terkunci, lebar tidak berubah |
| Nonaktif | Opasitas 50%, kursor dilarang, alasan ditulis di dekatnya |

- Label tombol menyebut aksinya: Daftar Kelas, Ajukan Sesi, Periksa Produk. Tidak ada Mulai Sekarang atau Pelajari Lebih Lanjut.
- Ikon berada di kiri label. Tombol ikon saja wajib punya aria-label.
- Aksi yang mengubah data memakai formulir POST dengan CSRF, bukan tautan.
- Di ponsel tombol utama formulir selebar penuh dan menempel di bawah bila formulir panjang.

### 7.3 Rincian tombol per halaman

#### Beranda dan navbar publik

| Tombol | Varian | Aksi | Keadaan khusus |
| --- | --- | --- | --- |
| Masuk | ghost md | Ke /masuk | Diganti menu akun bila sudah masuk |
| Daftar | primary md | Ke /daftar | Diganti Buka Dashboard bila sudah masuk |
| Tanya Bos Allo | accent lg | Ke /ruang-bos, tamu ke /daftar | Ajakan utama hero |
| Lihat Kelas | glass lg | Ke /kelas | Ajakan kedua hero |
| Lihat Semua Kelas, Siniar, Lapak | secondary md | Ke daftar masing-masing | Di akhir tiap seksi |
| Jadi Bos Pendamping | secondary md | Ke /jadi-bos | Seksi untuk pengusaha |
| Ganti tema | icon | Beralih terang dan gelap | Pilihan disimpan di peramban |

#### Daftar, Masuk, dan Kenali

| Tombol | Varian | Aksi | Keadaan khusus |
| --- | --- | --- | --- |
| Buat Akun | primary md penuh | POST /daftar | Nonaktif sampai persetujuan dicentang |
| Masuk | primary md penuh | POST /masuk | Memuat: Memeriksa |
| Lupa sandi | tautan teks | Ke /lupa-sandi |  |
| Kirim Ulang Email Verifikasi | secondary md | POST kirim ulang | Jeda 60 detik |
| Lanjut | primary md | POST /warga/kenali/{step} | Nonaktif sampai langkah terisi |
| Kembali | ghost md | Ke langkah sebelumnya | Tidak ada di langkah 1 |
| Simpan Profil | primary md | POST langkah terakhir, ke /warga | Menggantikan Lanjut di langkah 5 |

#### Dashboard warga

| Tombol | Varian | Aksi | Keadaan khusus |
| --- | --- | --- | --- |
| Kerjakan Langkah Ini | primary md | Ke modul yang disarankan | Label mengikuti saran, misalnya Bicara dengan Bos Allo |
| Lihat Rincian Skor | ghost sm | Membuka rincian enam komponen |  |
| Lihat Jejak Usaha | secondary md | Ke /warga/jejak |  |
| Tandai Semua Dibaca | ghost sm | POST notifikasi | Di panel lonceng |

#### Ruang Bos

| Tombol | Varian | Aksi | Keadaan khusus |
| --- | --- | --- | --- |
| Mulai Bicara dengan Bos Allo | primary lg | POST /ruang-bos | Nonaktif bila kuota habis, alasan ditulis |
| Pilihan mode Suara atau Teks | segmen | Mengubah mode sesi | Suara nonaktif bila peramban tidak mendukung |
| Tahan untuk Bicara | icon xl | Rekam selama ditahan, kirim otomatis saat dilepas | Berdenyut saat merekam, nonaktif saat Bos Allo berbicara |
| Putar Suara Bos Allo | secondary sm | Membuka izin audio peramban | Muncul hanya bila peramban memblokir putar otomatis |
| Kirim | primary md | POST kirim (JSON) | Nonaktif bila teks kosong atau sedang menunggu jawaban |
| Ulangi Rekaman | ghost sm | Menghapus transkrip sementara | Hanya mode suara |
| Bisukan suara | icon | Menghentikan dan mematikan suara | Ikon berganti |
| Ulangi Jawaban | ghost sm | Membacakan lagi jawaban terakhir |  |
| Akhiri Sesi | secondary md | Konfirmasi, lalu POST akhiri | Memuat: Menyusun catatan |
| Keluar | ghost sm | Kembali ke /warga | Konfirmasi bila sesi berjalan |
| Tambahkan ke Langkah 30 Hari | primary md | POST langkah | Di halaman catatan |
| Unduh PDF | secondary md | GET catatan/pdf |  |
| Ajukan ke Bos Asli | secondary md | Ke /warga/temu-bos dengan catatan terlampir |  |
| Coba Lagi | secondary sm | Mengirim ulang giliran yang gagal | Muncul hanya saat galat |

#### Temu Bos

| Tombol | Varian | Aksi | Keadaan khusus |
| --- | --- | --- | --- |
| Lihat Profil | secondary sm | Ke profil Bos | Di kartu Bos |
| Pilih slot (tiap slot) | segmen | Memilih slot | Slot terisi nonaktif |
| Ajukan Sesi | primary md | POST slot | Nonaktif sampai slot dan topik terisi |
| Lampirkan Catatan Konsultasi | ghost sm | Memilih catatan | Tersembunyi bila belum ada catatan |
| Buka Tautan Rapat | primary md | Membuka tautan di tab baru | Muncul setelah disetujui |
| Batalkan Sesi | danger sm | Konfirmasi, POST batal | Nonaktif kurang dari 6 jam sebelum sesi |
| Kirim Penilaian | primary md | POST nilai | Hanya sesi Selesai |

#### Kelas Bos dan Siniar Bos

| Tombol | Varian | Aksi | Keadaan khusus |
| --- | --- | --- | --- |
| Daftar Kelas | primary lg | Ke halaman konfirmasi | Berganti Kuota Penuh (nonaktif), Sudah Terdaftar, atau Lanjutkan Pembayaran bila tagihan masih terbuka |
| Konfirmasi Pendaftaran (gratis) atau Bayar dengan QRIS (berbayar) | primary lg penuh | POST daftar | Berganti Lanjutkan Pembayaran bila tagihan masih terbuka. Memuat: Mendaftar atau Menyiapkan pembayaran |
| Kembali ke Detail Kelas | ghost md penuh | Ke /kelas/{slug} | Di halaman konfirmasi |
| Buka Halaman Pembayaran | primary md penuh | Membuka halaman QRIS SumoPod | Selama Menunggu Bayar |
| Buka Kelas Saya | primary md penuh | Ke /warga/kelas | Setelah Lunas |
| Daftar Lagi | primary md penuh | Ke halaman konfirmasi | Setelah Gagal atau Kedaluwarsa |
| Batalkan Pendaftaran | danger sm | Konfirmasi, POST batal | Hilang setelah kelas mulai |
| Catat Hadir | primary md | POST hadir dengan kode | Aktif hanya pada waktu sesi |
| Lihat Sertifikat | secondary md | Ke /warga/sertifikat/{certificate} | Muncul setelah sertifikat dibuat, berlabel statusnya |
| Saring bidang (chip) | chip | Menyaring daftar | Chip aktif berwarna brand |
| Putar | icon xl | Memuat pemutar YouTube | Sampul diganti pemutar |
| Tandai Sudah Didengar | primary md | POST dengar | Berganti Sudah Didengar |
| Simpan Catatan | secondary md | POST catatan |  |

#### Sertifikat, Tanda Tangan, dan Verifikasi

| Tombol | Varian | Aksi | Keadaan khusus |
| --- | --- | --- | --- |
| Lihat Sertifikat | secondary md | Ke pratinjau | Pratinjau diskalakan pas layar dengan scale-frame.js |
| Unduh PDF | primary md | GET sertifikat/pdf | Nonaktif saat Menunggu Tanda Tangan, alasan ditulis |
| Salin Tautan Verifikasi | secondary md | Menyalin /verifikasi/{code} | Berganti Tersalin 2 detik |
| Lihat Bukti Blockchain | ghost sm | Membuka penjelajah blok di tab baru | Hanya status Tercatat |
| Tampilkan di Lapak (sakelar) | sakelar | PATCH sertifikat | Nonaktif bila belum punya lapak atau belum Tercatat |
| Tampilkan nama lengkap atau inisial | segmen | PATCH sertifikat | Mengubah tampilan di halaman verifikasi |
| Simpan Tanda Tangan | primary md | POST spesimen | Nonaktif bila kanvas kosong |
| Hapus (kanvas) | ghost sm | Mengosongkan kanvas |  |
| Unggah Berkas Tanda Tangan | secondary sm | Membuka pemilih berkas PNG | Maksimal 1 MB |
| Lihat Pratinjau | secondary sm | Membuka pratinjau sertifikat di modal | Di tiap baris penerima |
| Keluarkan dari Daftar | danger sm | Konfirmasi dengan alasan, POST keluarkan | Hanya sebelum ditandatangani |
| Tanda Tangani Sertifikat | primary lg | Konfirmasi kata sandi, POST tanda tangan | Label memuat jumlah, misalnya Tanda Tangani 24 Sertifikat. Memuat: Menandatangani |
| Hubungkan Dompet (opsional) | secondary md | Meminta tanda tangan dompet, POST dompet | Tersembunyi bila peramban tanpa dompet |
| Periksa Sertifikat | primary md | POST /verifikasi dengan kode | Nonaktif bila kode kosong. Memuat: Memeriksa di blockchain |
| Salin hash transaksi | icon | Menyalin hash | aria-label wajib |
| Buka di Penjelajah Blok | secondary sm | Tautan keluar tab baru |  |
| Lihat Lapak Pemilik | ghost sm | Ke /lapak/{slug} | Hanya bila pemilik menampilkan sertifikat di lapak |
| Tetapkan Penanda Tangan | secondary md | POST penanda-tangan | Di formulir kelas, wajib sebelum Tutup Kelas |
| Catat Ulang ke Blockchain | secondary sm | POST catat-ulang | Muncul hanya bila pencatatan gagal |
| Cabut Sertifikat | danger sm | Konfirmasi dengan alasan, POST cabut | Hanya status Tercatat |

#### Warung Bos 3D

| Tombol | Varian | Aksi | Keadaan khusus |
| --- | --- | --- | --- |
| Buka Warung | primary lg | POST /warung | Bila ada permainan berjalan: Lanjutkan Warung |
| Mulai Main | primary lg | Mengunci tetikus dan masuk ke warung | Petunjuk kontrol tampil sesuai perangkat |
| Tambah dan Kurang stok | icon | Mengubah jumlah belanja | Tambah nonaktif bila kas tidak cukup |
| Selesai Belanja | primary md | POST belanja |  |
| Tata ke Rak (E) | aksi di dunia 3D | POST rak | Petunjuk tombol muncul saat melihat rak dalam jarak 3 meter |
| Naik dan Turun harga | icon | Mengubah harga jual | Ada penunjuk harga wajar |
| Pilihan promosi | segmen | Tanpa promosi, spanduk, diskon | Biaya tampil di tiap pilihan |
| Buka Warung Hari Ini | primary lg | POST buka-warung | Pembeli mulai berdatangan |
| Layani (E) | aksi di dunia 3D | POST layani | Muncul saat melihat pembeli di meja kasir |
| Tutup Warung | primary md | POST tutup | Ringkasan hari tampil |
| Lanjut ke Hari Berikutnya | primary md | POST lanjut | Di hari 7: Lihat Rapor |
| Jeda (Esc) | icon glass | Membuka menu jeda | Juga tombol Start pada gamepad |
| Mode Ringan | ghost sm | Beralih ke tampilan tanpa 3D | Pilihan disimpan |
| Simpan Pelajaran ke Jejak Usaha | primary md | Menyimpan rapor | Di halaman rapor |
| Main Lagi | secondary md | POST /warung |  |

#### Cek Produk, Peluang, Rencana

| Tombol | Varian | Aksi | Keadaan khusus |
| --- | --- | --- | --- |
| Ambil Foto | primary md | Membuka kamera | Hanya di perangkat berkamera |
| Pilih dari Galeri | secondary md | Membuka pemilih berkas |  |
| Periksa Produk | primary md | POST cek-produk | Memuat: Memeriksa foto |
| Periksa Ulang | secondary md | POST cek-produk/{check}/ulang | Muncul bila pemeriksaan belum selesai setelah 3 menit |
| Perbaiki Nama Produk | ghost sm | PATCH cek |  |
| Salin Deskripsi | secondary sm | Menyalin ke papan klip | Berganti Tersalin 2 detik |
| Tambahkan ke Lapak | primary md | Ke formulir produk terisi | Bila belum punya lapak: Buat Lapak Dulu |
| Buka Peluang | primary lg | POST peluang | Memuat: Mencari peluang. Nonaktif bila kuota habis |
| Pilih Usaha Ini | primary md | POST pilih | Di tiap kartu peluang |
| Tulis Ide Sendiri | ghost md | Membuka formulir ide |  |
| Susun Rencana | primary md | POST rencana | Memuat: Menyusun rencana |
| Ubah bagian (tiap bagian) | ghost sm | Membuka penyunting bagian |  |
| Simpan Perubahan | primary md | PATCH rencana |  |
| Unduh PDF | secondary md | GET rencana/pdf |  |
| Tambah Biaya, Hapus baris | secondary sm, icon | Mengubah baris kalkulator | Hasil dihitung langsung |
| Tambah Langkah | secondary md | POST langkah |  |
| Centang langkah | kotak centang | PATCH langkah | Persentase langsung berubah |

#### Lapak

| Tombol | Varian | Aksi | Keadaan khusus |
| --- | --- | --- | --- |
| Simpan Lapak | primary md | POST atau PATCH lapak |  |
| Tambah Produk | primary md | Ke formulir produk |  |
| Isi dari Cek Produk | secondary sm | Mengisi formulir dari hasil cek | Tersembunyi bila belum ada hasil |
| Simpan Produk | primary md | POST atau PATCH produk |  |
| Hapus Produk | danger sm | Konfirmasi, DELETE |  |
| Tandai Habis, Tandai Tersedia | secondary sm | PATCH stok |  |
| Ajukan Terbit | primary md | POST ajukan | Nonaktif tanpa produk berfoto, alasan ditulis |
| Tutup Sementara, Buka Kembali | secondary md | POST tutup |  |
| Salin Tautan Lapak | secondary sm | Menyalin alamat | Hanya lapak Terbit |
| Pesan lewat WhatsApp | whatsapp lg | POST pesan, lalu ke WhatsApp | Nonaktif bila produk habis. Tamu diarahkan masuk dulu |
| Laporkan Lapak | tautan teks | Membuka formulir lapor | Di kaki halaman lapak. Tamu diarahkan masuk dulu |
| Unggah Sampul dan Foto Produk | secondary sm | Memilih gambar | JPG, PNG, WEBP maksimal 15 MB, pratinjau langsung |

#### Dashboard Bos, Pengelola, Admin

| Tombol | Varian | Aksi | Keadaan khusus |
| --- | --- | --- | --- |
| Tambah Slot | primary md | POST slot | Nonaktif bila Bos belum Aktif |
| Setujui, Tolak | primary sm, danger sm | POST setujui atau tolak | Tolak meminta alasan |
| Tandai Selesai | primary md | POST selesai | Nonaktif sampai catatan diisi |
| Tandai Tidak Hadir | secondary sm | POST tidak-hadir | Aktif setelah waktu sesi lewat |
| Buat Kelas, Tambah Pembicara, Tambah Episode | primary md | Ke formulir | Di x-page-header |
| Terbitkan, Tutup Kelas | primary md, secondary md | POST terbitkan atau tutup | Dengan konfirmasi |
| Buka Kode Kehadiran | secondary md | POST kode | Menampilkan kode besar untuk diproyeksikan |
| Unduh Peserta (XLSX) | secondary md | GET xlsx |  |
| Ambil Data | secondary md | POST ambil dari URL YouTube | Memuat: Mengambil data |
| Buat Ringkasan | secondary md | POST ringkas | Memuat: Meringkas |
| Undang Bos, Aktifkan, Tolak | primary md, primary sm, danger sm | POST undang, aktifkan, tolak | Aktifkan nonaktif sampai empat standar dicentang |
| Setujui Lapak, Tolak, Turunkan | primary sm, secondary sm, danger sm | POST moderasi | Tolak dan Turunkan meminta alasan |
| Simpan Pengaturan AI | primary md | PATCH ai |  |
| Uji Prompt | secondary md | POST uji | Menampilkan jawaban dan durasi |
| Simpan sebagai Versi Baru | primary md | POST prompt |  |
| Kirim Reset Sandi | secondary sm | POST reset-sandi | Email dikirim lewat Resend |
| Ke Beranda | ghost sm | Ke / | Di topbar semua dashboard |

## 8. Spesifikasi Bos Allo

| Aspek | Ketentuan |
| --- | --- |
| Aset | Dua video MP4 720p dengan ukuran dan posisi sama: allo-diam.mp4 dan allo-bicara.mp4 (hasil konversi dan peningkatan resolusi dari GIF agar tidak buram), ditambah allo-transparan.webp sebagai poster dan untuk mode kurangi gerak. |
| Pergantian | avatar.js memutar video bicara selama audio Speechify berjalan, lalu kembali ke video diam. Kedua video dimuat lebih dulu agar tidak berkedip. Ukuran avatar besar: separuh layar di desktop. |
| Keadaan | idle (diam), listening (diam dengan cincin denyut di tombol bicara), thinking (diam dengan teks Bos Allo sedang berpikir), speaking (bicara), error (diam dengan pesan). |
| Suara keluar | Speechify (model simba-3.5, suara bima, id-ID, laki-laki). Audio diminta per id giliran sehingga suara selalu cocok dengan jawaban yang sedang tampil. Bila peramban memblokir putar otomatis, muncul tombol Putar Suara Bos Allo. Cadangan: teks saja. |
| Sinkron teks dan suara | Teks jawaban ditampilkan bersamaan dengan mulainya audio, bukan sebelum. Giliran baru membatalkan audio lama. |
| Suara masuk | SpeechRecognition atau webkitSpeechRecognition, id-ID, hasil sementara ditampilkan. Tahan untuk bicara, lepas untuk kirim. |
| Isi jawaban | Gemini dengan skema berisi understood (ringkasan maksud warga), reply, dan saran langkah, berurutan lewat propertyOrdering. Riwayat yang dikirim adalah 10 giliran terakhir. Suhu 0,5. |
| Tanpa dukungan suara | Mode teks. GIF bicara tampil selama teks jawaban diketik per kata. |
| Tata letak | Desktop: Bos Allo di kiri, percakapan di kanan. Ponsel: Bos Allo di atas sepertiga layar, percakapan di bawah, tombol bicara menempel di bawah. |
| Aksesibilitas | Jawaban selalu tersedia sebagai teks. Area percakapan memakai aria-live. Tombol bicara dapat dioperasikan papan ketik. |
| Kepribadian | Ramah, lugas, memakai bahasa sehari-hari, bertanya balik sebelum menyimpulkan, tidak menggurui. |

## 9. Spesifikasi Warung Bos 3D

| Aspek | Ketentuan |
| --- | --- |
| Mesin | Three.js dimuat hanya di halaman permainan lewat impor dinamis. |
| Sudut pandang | Orang pertama. Pemain berjalan di dalam warung (tinggi mata 1,65 m, jalan 2,6 m/detik, lari 4,4 m/detik) dengan tabrakan dinding dan rak. |
| Kontrol | Papan ketik dan tetikus (WASD, E interaksi, F ambil, Shift lari, Tab isi, Esc jeda), gamepad standar (Xbox dan PlayStation, petunjuk tombol ikut menyesuaikan), dan sentuh (joystick virtual dan tombol aksi). Semua masukan dipetakan ke aksi yang sama. |
| Adegan | Warung low-poly dengan rak, meja kasir, papan harga, dan jalan depan. Bangunan dan tabrakan dari geometri di kode. Setelah aset termuat, langit foto Poly Haven (siang dan senja), pohon dan tanaman Kenney Nature Kit, perabot Kenney Furniture Kit, dan barang dagangan Kenney Food Kit dipasang. Barang di rak memakai InstancedMesh dari model makanan. Semua aset CC0, total sekitar 4 MB. |
| Pembeli | Tokoh Quaternius beranimasi (jalan dan diam) lewat AnimationMixer. Bila aset gagal dimuat, pembeli dan dunia tetap memakai bentuk dasar sehingga permainan tidak terhenti. |
| Pelanggan | Pembeli datang sesuai rencana dari server, antre di kasir, dan pergi bila menunggu lebih dari 60 detik. |
| Antarmuka | Panel HTML di atas kanvas (kas, hari, stok, petunjuk aksi), bukan teks di dalam 3D, agar terbaca dan dapat diakses. |
| Alur server | Rencana dan penyelesaian: buka-warung menyusun daftar pembeli, layani dan pergi mencatat tiap pembeli secara idempoten, tutup menghitung hasil hari. Klik ganda atau jaringan putus tidak menggandakan penjualan. |
| Anggaran | Aset total di bawah 3 MB, sasaran 30 bingkai per detik di ponsel kelas menengah, bayangan sederhana. |
| Sumber kebenaran | Server. Kanvas hanya menampilkan hasil yang dikirim server. |
| Mode Ringan | Ilustrasi diam dan panel yang sama. Aktif otomatis bila WebGL tidak tersedia. |
| Lisensi aset | Aset 3D buatan sendiri atau berlisensi bebas, dicantumkan di README. |

## 10. Spesifikasi Sertifikat Terverifikasi

| Aspek | Ketentuan |
| --- | --- |
| Ukuran | A4 mendatar, satu halaman, dibuat dengan dompdf dari resources/views/pdf/certificate.blade.php. |
| Isi | Logo alloBos, judul Sertifikat, nama penerima, nama kelas dan bidang, tanggal kelas, tempat, satu atau dua blok tanda tangan (gambar spesimen, nama, jabatan), kode sertifikat, kode QR, kalimat cara verifikasi. Penanda tangan utama: Deni Fuadi, Pemilik PT Institusi Pengusaha Indonesia. Tanda tangannya diunggah sendiri lewat akunnya, tidak pernah dibuat-buat sistem. |
| Desain | Gaya premium: latar sertifikat-latar.png dengan bingkai, nama penerima dalam huruf serif besar, garis pemisah, blok tanda tangan kiri dan kanan, QR dan kode di sudut bawah. |
| Pratinjau | Halaman pratinjau menampilkan sertifikat yang sama dengan PDF, diskalakan pas layar oleh scale-frame.js, sebelum warga mengunduh. |
| Kode sertifikat | Format ALB-TAHUN-6 karakter acak tanpa huruf yang mirip angka, misalnya ALB-2026-7KQ4MX. |
| Kode QR | Berisi alamat /verifikasi/{code}. Ukuran cetak minimal 25 mm dengan tepi kosong. |
| Gaya | Memakai token brand dan accent serta font dari referensi. Tanpa hiasan yang mengurangi keterbacaan saat dicetak hitam putih. |
| Halaman verifikasi | Urutan: panel status besar, data sertifikat, penanda tangan, bukti blockchain, penjelasan singkat cara kerja verifikasi dalam dua kalimat. |
| Warna status | Asli memakai success, Dicabut dan Tidak Cocok memakai danger, Belum Tercatat memakai warning, Tidak Ditemukan memakai netral. Status selalu ditulis dengan teks, bukan warna saja. |
| Jaringan uji | Sertifikat dicatat di Jaringan Uji alloBos (Anvil, chain id 31337) agar gratis dan stabil. x-chain-proof menampilkan keterangan Jaringan uji, hash transaksi, dan nomor blok. Kontrak yang sama dapat dipindah ke Polygon dengan mengganti CHAIN_RPC_URL. |
| Lencana di Lapak | Satu baris di bawah nama lapak: ikon centang, teks Lulus kelas dan nama kelas, tautan Periksa. Maksimal tiga lencana, sisanya di tautan Lihat Semua. |
| Tanda tangan di ponsel | x-signature-pad selebar layar dengan tinggi 180 px dan garis panduan. Layar tidak ikut bergulir saat menggambar. |

## 11. Aturan teks antarmuka

- Bahasa Indonesia sehari-hari. Istilah bisnis dijelaskan saat pertama muncul, misalnya HPP (biaya membuat satu barang).
- Tanpa emoji, tanpa tanda seru yang dipaksakan, tanpa kata pemasaran kosong.
- Tidak ada statistik atau testimoni karangan. Angka publik dihitung dari basis data.
- Pesan galat menyebut kejadian dan langkah berikutnya.
- Hasil AI tentang pasar dan peluang selalu disertai lencana Perkiraan AI.
- Lencana Demo hanya tampil bila APP_DEMO=true. Situs produksi tampil seperti situs umum, tanpa keterangan lomba atau karakter fiktif.
- Istilah blockchain dijelaskan dengan bahasa awam: catatan publik yang tidak bisa diubah. Kata hash diganti sidik digital di antarmuka warga.

## 12. Daftar periksa sebelum rilis

- Semua rute di bagian 3 terdaftar dan tiap tombol di bagian 7.3 mengarah ke rute yang ada.
- Tidak ada nilai hex di berkas tampilan.
- Semua halaman lulus pada lebar 360 px tanpa gulir mendatar.
- Fokus papan ketik terlihat di semua tombol dan masukan.
- Ruang Bos berjalan di mode suara dan mode teks.
- Warung Bos 3D berjalan di mode 3D dan Mode Ringan.
- QR sertifikat demo membuka halaman verifikasi berstatus Asli dan tautan penjelajah blok menampilkan transaksinya.
- Kunci privat penerbit tidak ada di repositori dan riwayat commit.
- Akun tiap peran (@allobos.com) dapat masuk dan dashboard tiap peran terbuka, dengan tombol Ke Beranda dan satu menu aktif.
- Kelas gratis langsung terdaftar. Kelas berbayar membuat QRIS SumoPod sandbox, webhook salah token ditolak 401, webhook benar mengubah status menjadi Lunas.
- Bos Allo menjawab sesuai pertanyaan, suara Speechify terdengar bersamaan dengan teksnya, dan tetap berjalan sebagai teks bila Speechify mati.
- Email verifikasi dan reset sandi terkirim lewat Resend.
- Unggah sampul lapak dan foto produk hingga 15 MB tersimpan dan tampil.
- Cek Produk selesai lewat antrean, atau menawarkan Periksa Ulang setelah 3 menit.
- Layanan chain bertahan setelah mulai ulang dan sertifikat demo tetap berstatus Asli.
- README memuat akun demo, daftar pustaka, aset, dan pemakaian AI coding assistant.
