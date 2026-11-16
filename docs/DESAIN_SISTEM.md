# Design System

Arsitektur, struktur kode, alur routing, tata letak, komponen, dan detail tombol alloBos

- **Produk:** alloBos, pendamping usaha berbasis AI untuk warga Gunungkidul
- **Arsitektur acuan:** github.com/GiskaSaputra/pnc-elevate (Laravel 12, Blade, Livewire 3, Alpine, Tailwind 3)
- **Referensi visual:** dribbble.com/shots/26954032-Creative-agency-web-ui-design
- **Alat desain:** design-taste-frontend (taste skill) di Claude Code
- **Penyusun:** Giska Saputra
- **Versi:** Final 1.1, 17 November 2026 (menambah Sertifikat Terverifikasi berbasis Web3)

## 1. Arsitektur sistem

alloBos memakai arsitektur proyek acuan PNC Elevate: aplikasi Laravel monolit yang merender HTML di server, dengan Livewire dan Alpine untuk interaksi, dan JavaScript mandiri hanya untuk dua halaman berat, yaitu Ruang Bos dan Warung Bos 3D.

| Lapisan | Teknologi | Tanggung jawab |
| --- | --- | --- |
| Tampilan | Blade, komponen Blade, Tailwind 3, Alpine | Halaman, tata letak, komponen antarmuka |
| Interaksi | Livewire 3 | Formulir bertahap, daftar bersaring, kalkulator HPP, langkah yang dicentang |
| Skrip halaman | JavaScript modul lewat Vite | boss-room.js (suara dan GIF), warung-3d.js (Three.js), home-scroll.js (animasi beranda), theme.js |
| Routing | routes/web.php, routes/auth.php | Kelompok rute per peran dengan awalan dan middleware |
| Controller | app/Http/Controllers/{Public,Warga,Bos,Pengelola,Admin} | Menerima permintaan, memvalidasi, memanggil service, mengembalikan tampilan |
| Service | app/Services/{Ai,Boss,Mentoring,Programs,Podcasts,Game,Products,Plans,Shops,Readiness} | Aturan bisnis per domain |
| Pekerjaan antrean | app/Jobs | Panggilan AI yang lama: rangkuman, cek produk, ringkasan siniar, rapor |
| Model | Eloquent, app/Models, app/Enums | Data dan status |
| Akses | spatie/laravel-permission, middleware role, policy | Peran dan kepemilikan data |
| AI | App\Services\Ai\GeminiClient | Satu pintu ke Gemini: JSON berskema, kuota, log, penyamaran data |
| Data | MySQL 8.4 | Penyimpanan utama |
| Berkas | Laravel Storage (disk public dan private) | Foto produk, sampul, sertifikat |
| Dokumen | barryvdh/laravel-dompdf, phpoffice/phpspreadsheet, pustaka QR | PDF rencana dan sertifikat ber-QR, unduhan XLSX |
| Grafik | Chart.js | Grafik skor dan wawasan |
| 3D | Three.js | Adegan Warung Bos 3D |
| Web3 | Kontrak pintar Solidity di jaringan EVM, ethers.js, Hardhat | Mencatat dan memeriksa sidik digital sertifikat |
| Suara | Web Speech API | Pengenalan ucapan dan sintesis suara di peramban |
| Deploy | Docker Compose di VPS, GitHub Actions | Layanan app, mysql, queue, scheduler |

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
5. Permintaan dikirim. Bila satu kunci terkena batas, kunci berikutnya dipakai.
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

### 1.4 Struktur folder

| Jalur | Isi |
| --- | --- |
| app/Enums | RoleName, SessionStatus, MentoringStatus, ProgramStatus, EnrollmentStatus, BossStatus, ShopStatus, GameStatus, ContentStatus, ProgramKind, MarketSignal, CertificateStatus |
| app/Http/Controllers/Public | PageController, ProgramController, PodcastController, ShopController, BecomeBossController, CertificateVerifyController |
| app/Http/Controllers/Signing | SigningController, SpecimenController |
| app/Http/Controllers/Warga | DashboardController, OnboardingController, BossRoomController, MentoringController, ProgramController, CertificateController, PodcastController, GameController, ProductCheckController, OpportunityController, PlanController, StepController, ShopController, ProductController |
| app/Http/Controllers/Bos | DashboardController, SlotController, SessionController, ProfileController |
| app/Http/Controllers/Pengelola | DashboardController, ProgramController, SpeakerController, PodcastController, BossController, ShopModerationController, CertificateController, AnnouncementController, InsightController |
| app/Http/Controllers/Admin | DashboardController, UserController, AiController, PageController, SettingController, EventBankController, ChainController, SystemController |
| app/Http/Middleware | EnsureRole, EnsureOnboarded, EnsureAccountIsActive, EnsureActiveBoss |
| app/Services/Ai | GeminiClient, AiSettings, PromptRegistry, PiiRedactor, AiException |
| app/Services | Boss/BossEngine, Boss/NoteBuilder, Mentoring/MentoringService, Programs/ProgramService, Podcasts/PodcastService, Game/GameEngine, Game/EventPicker, Products/ProductChecker, Plans/OpportunityFinder, Plans/PlanBuilder, Plans/CostCalculator, Shops/ShopService, Readiness/ReadinessCalculator, Certificates/SigningService, Certificates/CertificateBuilder, Certificates/CertificateHasher, Chain/ChainClient |
| app/Jobs | SummarizeBossSession, CheckProduct, SummarizePodcast, DebriefShopGame, GenerateCertificates, AnchorCertificates, RevokeCertificate |
| web3 | contracts/AlloBosCertificate.sol, scripts/deploy.mjs, anchor.mjs, revoke.mjs, verify.mjs, test, hardhat.config |
| config | ai.php, chain.php, menus.php, readiness.php, game.php, features.php |
| resources/views | layouts, components, public, auth, warga, boss-room, game, signing, bos, pengelola, admin, pdf, errors |
| resources/js | app.js, boss-room.js, warung-3d.js, home-scroll.js, theme.js |
| public/gif/bos-allo | allo-diam.gif, allo-bicara.gif, allo-transparan.webp |
| public/models | Aset 3D low-poly warung (GLB) |
| docs | SPESIFIKASI.md, DESAIN.md, PROGRESS.md, UJI_MANUAL.md |

### 1.5 Arsitektur deploy

- Empat layanan Docker Compose: app (server web), mysql, queue (pekerja antrean), scheduler (pengingat dan pembersihan transkrip).
- GitHub Actions membangun image dan menerapkannya ke VPS saat cabang utama berubah.
- Variabel lingkungan: APP_URL, APP_DEMO, DB_*, GEMINI_API_KEY (boleh beberapa kunci dipisah koma), GEMINI_MODEL, DEMO_PASSWORD, pengaturan email, serta CHAIN_NAME, CHAIN_ID, CHAIN_RPC_URL, CHAIN_EXPLORER_URL, CERT_CONTRACT_ADDRESS, ISSUER_PRIVATE_KEY, CERT_HASH_SALT.
- Image aplikasi menyertakan Node agar pekerjaan antrean dapat menjalankan skrip ethers.js. Kontrak di-deploy satu kali dengan Hardhat dan alamatnya dicatat di README.
- Saat APP_DEMO aktif, lencana Demo tampil di samping logo.

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
| POST | /lapak/{shop:slug}/pesan/{product} | shops.order | Public\ShopController@order (catat klik, alihkan ke WhatsApp) |
| GET | /bos | bosses.index | Public\PageController@bosses |
| GET | /jadi-bos | become-boss | Public\BecomeBossController@create |
| POST | /jadi-bos | become-boss.store | Public\BecomeBossController@store |
| GET | /verifikasi | certificates.verify.form | Public\CertificateVerifyController@form |
| POST | /verifikasi | certificates.verify.lookup | Public\CertificateVerifyController@lookup |
| GET | /verifikasi/{code} | certificates.verify | Public\CertificateVerifyController@show (baca kontrak pintar) |
| GET | /tentang, /bantuan, /privasi, /syarat, /kontak | pages.{slug} | Public\PageController@show |
| GET | /undangan/{invitation} | invitations.accept | InvitationAcceptController@show (signed) |
| POST | /undangan/{invitation} | invitations.accept.store | InvitationAcceptController@store (signed) |

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
| POST | /warga/kelas/{program}/daftar | warga.programs.enroll | Warga\ProgramController@enroll |
| POST | /warga/kelas/{program}/batal | warga.programs.cancel | Warga\ProgramController@cancel |
| POST | /warga/kelas/sesi/{session}/hadir | warga.programs.attend | Warga\ProgramController@attend |
| GET | /warga/sertifikat | warga.certificates.index | Warga\CertificateController@index |
| GET | /warga/sertifikat/{certificate} | warga.certificates.show | Warga\CertificateController@show |
| GET | /warga/sertifikat/{certificate}/pdf | warga.certificates.pdf | Warga\CertificateController@pdf |
| PATCH | /warga/sertifikat/{certificate} | warga.certificates.update | Warga\CertificateController@update (nama atau inisial, tampil di lapak) |
| POST | /warga/siniar/{podcast}/dengar | warga.podcasts.listen | Warga\PodcastController@listen |
| POST | /warga/siniar/{podcast}/catatan | warga.podcasts.note | Warga\PodcastController@note |
| GET | /warga/cek-produk | warga.checks.index | Warga\ProductCheckController@index |
| POST | /warga/cek-produk | warga.checks.store | Warga\ProductCheckController@store |
| GET | /warga/cek-produk/{check} | warga.checks.show | Warga\ProductCheckController@show |
| PATCH | /warga/cek-produk/{check} | warga.checks.update | Warga\ProductCheckController@update |
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
| POST | /ruang-bos/{session}/kirim | boss-room.send | Warga\BossRoomController@send (JSON) |
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
| POST | /warung/{game}/belanja | game.buy | Warga\GameController@buy (JSON) |
| POST | /warung/{game}/harga | game.price | Warga\GameController@price (JSON) |
| POST | /warung/{game}/buka | game.run | Warga\GameController@run (JSON) |
| POST | /warung/{game}/lanjut | game.next | Warga\GameController@next (JSON) |
| GET | /warung/{game}/rapor | game.report | Warga\GameController@report |

### 3.6 Tanda Tangan (awalan /tanda-tangan, role:bos,pengelola,admin)

| Metode | URI | Nama rute | Controller dan aksi |
| --- | --- | --- | --- |
| GET | /tanda-tangan | signing.index | Signing\SigningController@index (kelas yang menunggu) |
| GET | /tanda-tangan/spesimen | signing.specimen.edit | Signing\SpecimenController@edit |
| POST | /tanda-tangan/spesimen | signing.specimen.store | Signing\SpecimenController@store |
| GET | /tanda-tangan/kelas/{program} | signing.show | Signing\SigningController@show (penerima dan pratinjau) |
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
| Tamu membuka halaman berlogin | /masuk, lalu kembali ke halaman yang dituju |
| Tamu menekan Daftar Kelas atau Tanya Bos Allo | /daftar dengan catatan tujuan |
| Memindai QR sertifikat | /verifikasi/{code}, tanpa perlu masuk |
| Penanda tangan belum punya spesimen membuka kelas | /tanda-tangan/spesimen, lalu kembali ke kelas |
| Peran tidak sesuai | Halaman 403 dengan tombol ke dashboard peran aktif |

## 4. Arah visual

Seluruh tampilan dan animasi mengikuti referensi yang ditetapkan pemilik proyek: dribbble.com/shots/26954032-Creative-agency-web-ui-design. Penerapan dilakukan di Claude Code dengan taste skill (design-taste-frontend). Dokumen ini tidak menetapkan nilai warna atau nama font. Nilai itu diturunkan dari referensi pada Fase 0 dan dicatat di docs/DESAIN.md.

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

### 4.2 Tipografi, jarak, sudut

| Elemen | Aturan |
| --- | --- |
| Font | Satu keluarga display dan satu keluarga isi mengikuti referensi, dimuat dengan display swap. Maksimal dua keluarga. |
| Skala huruf | Display 48 sampai 72 px (beranda), H1 36, H2 28, H3 20, isi 16, kecil 14, label 12 huruf besar berjarak. |
| Tinggi baris | Judul 1,1 sampai 1,2. Isi 1,6. |
| Lebar teks | Paragraf maksimal sekitar 68 karakter. |
| Kisi | Kontainer maksimal 1200 px, 12 kolom desktop, 4 kolom ponsel, celah 24 px. |
| Jarak | Kelipatan 4 px. Antar seksi 96 px desktop, 56 px ponsel. |
| Sudut | Tombol dan masukan mengikuti referensi (satu nilai untuk seluruh aplikasi). Kartu satu tingkat lebih besar. |
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
| Transisi halaman dashboard | Dashboard | Tidak ada. Dashboard mengutamakan kecepatan. |
| Kurangi gerak | Semua | prefers-reduced-motion mematikan animasi gulir dan ketik. |

Detail kurva dan durasi disamakan dengan animasi pada referensi saat penerapan.

## 5. Tata letak

| Tata letak | Dipakai oleh | Susunan |
| --- | --- | --- |
| layouts/public | Halaman publik | Navbar transparan yang memadat saat digulir, isi, footer |
| layouts/app | Warga | Navbar atas dengan menu modul, isi lebar terbatas, navigasi bawah di ponsel |
| layouts/dashboard | Bos, Pengelola, Admin | Sidebar kiri dari config/menus.php, topbar, isi |
| layouts/auth | Masuk, Daftar, sandi | Dua kolom: visual dan formulir. Satu kolom di ponsel |
| layouts/stage | Ruang Bos, Warung Bos 3D | Layar penuh tanpa navbar, bilah atas tipis dengan tombol Keluar |
| layouts/shop | Halaman lapak publik dan halaman verifikasi sertifikat | Ringan: tanpa skrip animasi, tanpa pustaka berat |
| errors/layout | 403, 404, 419, 500, 503 | Pesan, penjelasan, satu tombol tindakan |

### 5.1 Navigasi

| Area | Isi menu |
| --- | --- |
| Navbar publik | Kelas, Siniar, Lapak, Bos, Tentang. Kanan: Masuk (ghost), Daftar (primary). Footer: Verifikasi Sertifikat |
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
| x-button, x-link-button | Tombol dan tautan bergaya tombol | Lihat bagian 7 |
| x-card | Wadah isi | Opsi: polos, tautan, sorot |
| x-field, x-text-input, x-textarea, x-select | Masukan formulir dengan label dan galat | Label selalu terlihat, bukan hanya placeholder |
| x-badge | Status | Warna dari token status, selalu disertai teks |
| x-demo-badge, x-ai-badge | Lencana Demo dan Perkiraan AI | Wajib di data contoh dan hasil AI pasar |
| x-modal | Dialog konfirmasi | Fokus terkunci, tutup dengan Esc |
| x-empty-state | Keadaan kosong | Satu kalimat dan satu tombol tindakan |
| x-page-header | Judul halaman dashboard | Judul, keterangan, tombol tindakan utama di kanan |
| x-table | Tabel data | Menjadi kartu di ponsel |
| x-chart | Grafik Chart.js | Warna dari token |
| x-step-progress | Penunjuk langkah onboarding dan fase permainan | Angka dan nama langkah |
| x-score-ring | Skor Siap Usaha | Angka besar, label tingkat, rincian saat diketuk |
| x-boss-avatar | Bos Allo | Lihat bagian 8 |
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
| Tahan untuk Bicara | icon xl | Mulai dan hentikan rekam ucapan | Berdenyut saat merekam, nonaktif saat Bos Allo berbicara |
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
| Daftar Kelas | primary lg | POST daftar | Berganti Kuota Penuh (nonaktif) atau Sudah Terdaftar |
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
| Tambah dan Kurang stok | icon | Mengubah jumlah belanja | Tambah nonaktif bila kas tidak cukup |
| Selesai Belanja | primary md | POST belanja |  |
| Naik dan Turun harga | icon | Mengubah harga jual | Ada penunjuk harga wajar |
| Pilihan promosi | segmen | Tanpa promosi, spanduk, diskon | Biaya tampil di tiap pilihan |
| Buka Warung Hari Ini | primary lg | POST buka | Memuat: Warung sedang buka |
| Lanjut ke Hari Berikutnya | primary md | POST lanjut | Di hari 7: Lihat Rapor |
| Putar kamera, Perbesar | icon glass | Mengendalikan kamera |  |
| Mode Ringan | ghost sm | Beralih ke tampilan tanpa 3D | Pilihan disimpan |
| Simpan Pelajaran ke Jejak Usaha | primary md | Menyimpan rapor | Di halaman rapor |
| Main Lagi | secondary md | POST /warung |  |

#### Cek Produk, Peluang, Rencana

| Tombol | Varian | Aksi | Keadaan khusus |
| --- | --- | --- | --- |
| Ambil Foto | primary md | Membuka kamera | Hanya di perangkat berkamera |
| Pilih dari Galeri | secondary md | Membuka pemilih berkas |  |
| Periksa Produk | primary md | POST cek-produk | Memuat: Memeriksa foto |
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
| Pesan lewat WhatsApp | whatsapp lg | POST pesan, lalu ke WhatsApp | Nonaktif bila produk habis |
| Laporkan Lapak | tautan teks | Membuka formulir lapor | Di kaki halaman lapak |

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
| Kirim Reset Sandi | secondary sm | POST reset-sandi |  |

## 8. Spesifikasi Bos Allo

| Aspek | Ketentuan |
| --- | --- |
| Aset | Dua GIF berlatar transparan dengan ukuran dan posisi sama: allo-diam.gif dan allo-bicara.gif, ditambah satu gambar diam WEBP untuk pratinjau dan mode kurangi gerak. |
| Pergantian | Fungsi gifFor mengembalikan GIF bicara bila keadaan sama dengan speaking, selain itu GIF diam. Kedua GIF dimuat lebih dulu agar tidak berkedip. |
| Keadaan | idle (diam), listening (diam dengan cincin denyut di tombol bicara), thinking (diam dengan teks Bos Allo sedang berpikir), speaking (bicara), error (diam dengan pesan). |
| Suara keluar | speechSynthesis berbahasa id-ID. Pemilihan suara mengutamakan suara Indonesia yang tersedia. Peristiwa mulai dan selesai suara menggerakkan keadaan. |
| Suara masuk | SpeechRecognition atau webkitSpeechRecognition, id-ID, hasil sementara ditampilkan. |
| Tanpa dukungan suara | Mode teks. GIF bicara tampil selama teks jawaban diketik per kata. |
| Tata letak | Desktop: Bos Allo di kiri, percakapan di kanan. Ponsel: Bos Allo di atas sepertiga layar, percakapan di bawah, tombol bicara menempel di bawah. |
| Aksesibilitas | Jawaban selalu tersedia sebagai teks. Area percakapan memakai aria-live. Tombol bicara dapat dioperasikan papan ketik. |
| Kepribadian | Ramah, lugas, memakai bahasa sehari-hari, bertanya balik sebelum menyimpulkan, tidak menggurui. |

## 9. Spesifikasi Warung Bos 3D

| Aspek | Ketentuan |
| --- | --- |
| Mesin | Three.js dimuat hanya di halaman permainan lewat impor dinamis. |
| Adegan | Satu warung low-poly, jalan, tiga sampai lima pelanggan, langit siang dan senja. Gaya warna mengikuti token brand dan accent. |
| Kamera | Perspektif miring dari atas, putar terbatas 90 derajat, perbesar dua tingkat. |
| Antarmuka | Panel HTML di atas kanvas, bukan teks di dalam 3D, agar terbaca dan dapat diakses. |
| Animasi | Pelanggan berjalan ke warung sesuai jumlah terjual, rak berkurang, papan harga berubah, hujan saat kejadian hujan. |
| Anggaran | Aset total di bawah 3 MB, sasaran 30 bingkai per detik di ponsel kelas menengah, bayangan sederhana. |
| Sumber kebenaran | Server. Kanvas hanya menampilkan hasil yang dikirim server. |
| Mode Ringan | Ilustrasi diam dan panel yang sama. Aktif otomatis bila WebGL tidak tersedia. |
| Lisensi aset | Aset 3D buatan sendiri atau berlisensi bebas, dicantumkan di README. |

## 10. Spesifikasi Sertifikat Terverifikasi

| Aspek | Ketentuan |
| --- | --- |
| Ukuran | A4 mendatar, satu halaman, dibuat dengan dompdf dari resources/views/pdf/certificate.blade.php. |
| Isi | Logo alloBos dan IPI, judul Sertifikat, nama penerima, nama kelas dan bidang, tanggal kelas, tempat, satu atau dua blok tanda tangan (gambar spesimen, nama, jabatan atau usaha), kode sertifikat, kode QR, kalimat cara verifikasi. |
| Kode sertifikat | Format ALB-TAHUN-6 karakter acak tanpa huruf yang mirip angka, misalnya ALB-2026-7KQ4MX. |
| Kode QR | Berisi alamat /verifikasi/{code}. Ukuran cetak minimal 25 mm dengan tepi kosong. |
| Gaya | Memakai token brand dan accent serta font dari referensi. Tanpa hiasan yang mengurangi keterbacaan saat dicetak hitam putih. |
| Halaman verifikasi | Urutan: panel status besar, data sertifikat, penanda tangan, bukti blockchain, penjelasan singkat cara kerja verifikasi dalam dua kalimat. |
| Warna status | Asli memakai success, Dicabut dan Tidak Cocok memakai danger, Belum Tercatat memakai warning, Tidak Ditemukan memakai netral. Status selalu ditulis dengan teks, bukan warna saja. |
| Jaringan uji | Bila CHAIN_NAME adalah jaringan uji, x-chain-proof menampilkan keterangan Jaringan uji di samping nama jaringan. |
| Lencana di Lapak | Satu baris di bawah nama lapak: ikon centang, teks Lulus kelas dan nama kelas, tautan Periksa. Maksimal tiga lencana, sisanya di tautan Lihat Semua. |
| Tanda tangan di ponsel | x-signature-pad selebar layar dengan tinggi 180 px dan garis panduan. Layar tidak ikut bergulir saat menggambar. |

## 11. Aturan teks antarmuka

- Bahasa Indonesia sehari-hari. Istilah bisnis dijelaskan saat pertama muncul, misalnya HPP (biaya membuat satu barang).
- Tanpa emoji, tanpa tanda seru yang dipaksakan, tanpa kata pemasaran kosong.
- Tidak ada statistik atau testimoni karangan. Angka publik dihitung dari basis data.
- Pesan galat menyebut kejadian dan langkah berikutnya.
- Hasil AI tentang pasar dan peluang selalu disertai lencana Perkiraan AI.
- Data contoh selalu disertai lencana Demo.
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
- Empat akun demo dapat masuk dan dashboard tiap peran terbuka.
- README memuat akun demo, daftar pustaka, aset, dan pemakaian AI coding assistant.
