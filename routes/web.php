<?php

use App\Http\Controllers\ActiveRoleController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Bos;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvitationAcceptController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Pengelola;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public;
use App\Http\Controllers\Signing;
use App\Http\Controllers\SumoPodWebhookController;
use App\Http\Controllers\Warga;
use App\Models\Page;
use Illuminate\Support\Facades\Route;

Route::get('/', [Public\PageController::class, 'home'])->name('home');

// Webhook pembayaran SumoPod. Tanpa CSRF, keasliannya diperiksa lewat token atau tanda tangan.
Route::post('webhook/sumopod', SumoPodWebhookController::class)->middleware('throttle:60,1')->name('webhooks.sumopod');

Route::get('lapak', [Public\ShopController::class, 'index'])->name('shops.index');
Route::get('lapak/{shop:slug}', [Public\ShopController::class, 'show'])->name('shops.show');
Route::post('lapak/{shop:slug}/pesan/{product}', [Public\ShopController::class, 'order'])->name('shops.order');
Route::post('lapak/{shop:slug}/lapor', [Public\ShopController::class, 'report'])->middleware('throttle:5,10')->name('shops.report');

Route::get('kelas', [Public\ProgramController::class, 'index'])->name('programs.index');
Route::get('kelas/{program:slug}', [Public\ProgramController::class, 'show'])->name('programs.show');
Route::get('bos', [Public\PageController::class, 'bosses'])->name('bosses.index');
Route::get('siniar', [Public\PodcastController::class, 'index'])->name('podcasts.index');
Route::get('siniar/{podcast:slug}', [Public\PodcastController::class, 'show'])->name('podcasts.show');

Route::get('verifikasi', [Public\CertificateVerifyController::class, 'form'])->name('certificates.verify.form');
Route::post('verifikasi', [Public\CertificateVerifyController::class, 'lookup'])->middleware('throttle:30,1')->name('certificates.verify.lookup');
Route::get('verifikasi/{code}', [Public\CertificateVerifyController::class, 'show'])->middleware('throttle:60,1')->name('certificates.verify');

Route::get('jadi-bos', [Public\BecomeBossController::class, 'create'])->name('become-boss');
Route::post('jadi-bos', [Public\BecomeBossController::class, 'store'])->middleware('throttle:5,10')->name('become-boss.store');
Route::get('undangan/{invitation}', [InvitationAcceptController::class, 'show'])->middleware('signed')->name('invitations.accept');
Route::post('undangan/{invitation}', [InvitationAcceptController::class, 'store'])->middleware('signed')->name('invitations.accept.store');
foreach (array_keys(Page::SLUGS) as $slug) {
    Route::get($slug, [Public\PageController::class, 'show'])->defaults('slug', $slug)->name('pages.'.$slug);
}

require __DIR__.'/auth.php';

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::post('peran-aktif', [ActiveRoleController::class, 'update'])->name('active-role.update');
    Route::get('profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('profil', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('notifikasi', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifikasi', [NotificationController::class, 'read'])->name('notifications.read');

    Route::middleware('role:warga')->prefix('warga')->name('warga.')->group(function () {
        Route::get('kenali/{step?}', [Warga\OnboardingController::class, 'show'])->whereNumber('step')->name('onboarding.show');
        Route::post('kenali/{step}', [Warga\OnboardingController::class, 'store'])->whereNumber('step')->name('onboarding.store');

        Route::middleware('onboarded')->group(function () {
            Route::get('/', Warga\DashboardController::class)->name('dashboard');
            Route::get('jejak', [Warga\DashboardController::class, 'journey'])->name('journey');

            Route::get('peluang', [Warga\OpportunityController::class, 'index'])->name('opportunities.index');
            Route::post('peluang', [Warga\OpportunityController::class, 'generate'])->middleware('throttle:ai')->name('opportunities.generate');
            Route::post('peluang/{opportunity}/pilih', [Warga\OpportunityController::class, 'choose'])->name('opportunities.choose');

            Route::get('rencana', [Warga\PlanController::class, 'show'])->name('plan.show');
            Route::post('rencana', [Warga\PlanController::class, 'build'])->middleware('throttle:ai')->name('plan.build');
            Route::patch('rencana', [Warga\PlanController::class, 'update'])->name('plan.update');
            Route::get('rencana/pdf', [Warga\PlanController::class, 'pdf'])->name('plan.pdf');
            Route::get('rencana/hpp', [Warga\PlanController::class, 'cost'])->name('plan.cost');

            Route::get('langkah', [Warga\StepController::class, 'index'])->name('steps.index');
            Route::post('langkah', [Warga\StepController::class, 'store'])->name('steps.store');
            Route::patch('langkah/{step}', [Warga\StepController::class, 'update'])->name('steps.update');
            Route::delete('langkah/{step}', [Warga\StepController::class, 'destroy'])->name('steps.destroy');

            Route::get('cek-produk', [Warga\ProductCheckController::class, 'index'])->name('checks.index');
            Route::post('cek-produk', [Warga\ProductCheckController::class, 'store'])->middleware('throttle:ai')->name('checks.store');
            Route::get('cek-produk/{check}', [Warga\ProductCheckController::class, 'show'])->name('checks.show');
            Route::patch('cek-produk/{check}', [Warga\ProductCheckController::class, 'update'])->name('checks.update');

            Route::get('lapak-saya', [Warga\ShopController::class, 'edit'])->name('shop.edit');
            Route::post('lapak-saya', [Warga\ShopController::class, 'store'])->name('shop.store');
            Route::patch('lapak-saya', [Warga\ShopController::class, 'update'])->name('shop.update');
            Route::post('lapak-saya/ajukan', [Warga\ShopController::class, 'submit'])->name('shop.submit');
            Route::post('lapak-saya/tutup', [Warga\ShopController::class, 'toggle'])->name('shop.toggle');
            Route::get('lapak-saya/produk', [Warga\ProductController::class, 'create'])->name('products.create');
            Route::post('lapak-saya/produk', [Warga\ProductController::class, 'store'])->name('products.store');
            Route::get('lapak-saya/produk/{product}', [Warga\ProductController::class, 'edit'])->name('products.edit');
            Route::patch('lapak-saya/produk/{product}', [Warga\ProductController::class, 'update'])->name('products.update');
            Route::delete('lapak-saya/produk/{product}', [Warga\ProductController::class, 'destroy'])->name('products.destroy');

            Route::get('kelas', [Warga\ProgramController::class, 'index'])->name('programs.index');
            Route::get('kelas/{program}/konfirmasi', [Warga\ProgramController::class, 'confirm'])->name('programs.confirm');
            Route::post('kelas/{program}/daftar', [Warga\ProgramController::class, 'enroll'])->middleware('throttle:10,1')->name('programs.enroll');
            Route::get('pembayaran/{payment}', [Warga\PaymentController::class, 'show'])->name('payments.show');
            Route::post('kelas/{program}/batal', [Warga\ProgramController::class, 'cancel'])->name('programs.cancel');
            Route::post('kelas/sesi/{session}/hadir', [Warga\ProgramController::class, 'attend'])->middleware('throttle:10,1')->name('programs.attend');
            Route::get('kelas/{program}/materi/{index}', [Warga\ProgramController::class, 'material'])->whereNumber('index')->name('programs.material');

            Route::post('siniar/{podcast}/dengar', [Warga\PodcastController::class, 'listen'])->name('podcasts.listen');
            Route::post('siniar/{podcast}/catatan', [Warga\PodcastController::class, 'note'])->name('podcasts.note');

            Route::get('sertifikat', [Warga\CertificateController::class, 'index'])->name('certificates.index');
            Route::get('sertifikat/{certificate}', [Warga\CertificateController::class, 'show'])->name('certificates.show');
            Route::get('sertifikat/{certificate}/pdf', [Warga\CertificateController::class, 'pdf'])->name('certificates.pdf');
            Route::patch('sertifikat/{certificate}', [Warga\CertificateController::class, 'update'])->name('certificates.update');

            Route::get('temu-bos', [Warga\MentoringController::class, 'index'])->name('mentoring.index');
            Route::get('temu-bos/bos/{boss}', [Warga\MentoringController::class, 'boss'])->name('mentoring.boss');
            Route::post('temu-bos/slot/{slot}', [Warga\MentoringController::class, 'book'])->name('mentoring.book');
            Route::get('temu-bos/sesi/{session}', [Warga\MentoringController::class, 'show'])->name('mentoring.show');
            Route::post('temu-bos/sesi/{session}/batal', [Warga\MentoringController::class, 'cancel'])->name('mentoring.cancel');
            Route::post('temu-bos/sesi/{session}/nilai', [Warga\MentoringController::class, 'rate'])->name('mentoring.rate');
            Route::post('temu-bos/sesi/{session}/langkah', [Warga\MentoringController::class, 'steps'])->name('mentoring.steps');
        });
    });

    Route::middleware(['role:warga', 'onboarded'])->prefix('ruang-bos')->name('boss-room.')->controller(Warga\BossRoomController::class)->group(function () {
        Route::get('/', 'setup')->name('setup');
        Route::post('/', 'store')->name('store');
        Route::get('riwayat', 'history')->name('history');
        Route::get('{session}', 'show')->whereNumber('session')->name('show');
        Route::post('{session}/mulai', 'start')->whereNumber('session')->name('start');
        Route::post('{session}/kirim', 'send')->whereNumber('session')->middleware('throttle:ai')->name('send');
        Route::post('{session}/suara', 'voice')->whereNumber('session')->middleware('throttle:ai')->name('voice');
        Route::post('{session}/akhiri', 'finish')->whereNumber('session')->name('finish');
        Route::get('{session}/catatan', 'note')->whereNumber('session')->name('note');
        Route::get('{session}/catatan/pdf', 'pdf')->whereNumber('session')->name('note.pdf');
        Route::post('{session}/langkah', 'addSteps')->whereNumber('session')->name('steps');
    });

    Route::middleware(['role:warga', 'onboarded'])->prefix('warung')->name('game.')->controller(Warga\GameController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('{game}', 'play')->whereNumber('game')->name('play');
        Route::post('{game}/belanja', 'buy')->whereNumber('game')->name('buy');
        Route::post('{game}/harga', 'price')->whereNumber('game')->name('price');
        Route::post('{game}/buka', 'run')->whereNumber('game')->middleware('throttle:30,1')->name('run');
        Route::post('{game}/rak', 'stock')->whereNumber('game')->middleware('throttle:120,1')->name('stock');
        Route::post('{game}/buka-warung', 'open')->whereNumber('game')->middleware('throttle:30,1')->name('open');
        Route::post('{game}/layani', 'serve')->whereNumber('game')->middleware('throttle:120,1')->name('serve');
        Route::post('{game}/pergi', 'miss')->whereNumber('game')->middleware('throttle:120,1')->name('miss');
        Route::post('{game}/tutup', 'close')->whereNumber('game')->middleware('throttle:30,1')->name('close');
        Route::post('{game}/lanjut', 'next')->whereNumber('game')->middleware('throttle:30,1')->name('next');
        Route::get('{game}/rapor', 'report')->whereNumber('game')->name('report');
        Route::post('{game}/simpan', 'save')->whereNumber('game')->name('save');
    });

    Route::middleware('role:bos')->prefix('bos-area')->name('bos.')->group(function () {
        Route::get('/', Bos\DashboardController::class)->name('dashboard');
        Route::get('profil', [Bos\ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('profil', [Bos\ProfileController::class, 'update'])->name('profile.update');

        Route::middleware('boss.active')->group(function () {
            Route::get('slot', [Bos\SlotController::class, 'index'])->name('slots.index');
            Route::post('slot', [Bos\SlotController::class, 'store'])->name('slots.store');
            Route::delete('slot/{slot}', [Bos\SlotController::class, 'destroy'])->name('slots.destroy');
        });

        Route::get('sesi', [Bos\SessionController::class, 'index'])->name('sessions.index');
        Route::get('sesi/{session}', [Bos\SessionController::class, 'show'])->name('sessions.show');
        Route::post('sesi/{session}/setujui', [Bos\SessionController::class, 'approve'])->name('sessions.approve');
        Route::post('sesi/{session}/tolak', [Bos\SessionController::class, 'reject'])->name('sessions.reject');
        Route::post('sesi/{session}/selesai', [Bos\SessionController::class, 'complete'])->name('sessions.complete');
        Route::post('sesi/{session}/tidak-hadir', [Bos\SessionController::class, 'noShow'])->name('sessions.no-show');
        Route::post('sesi/{session}/batal', [Bos\SessionController::class, 'cancel'])->name('sessions.cancel');
    });

    Route::middleware('role:bos,pengelola,admin')->prefix('tanda-tangan')->name('signing.')->group(function () {
        Route::get('/', [Signing\SigningController::class, 'index'])->name('index');
        Route::get('spesimen', [Signing\SpecimenController::class, 'edit'])->name('specimen.edit');
        Route::post('spesimen', [Signing\SpecimenController::class, 'store'])->name('specimen.store');
        Route::get('kelas/{program}', [Signing\SigningController::class, 'show'])->name('show');
        Route::get('kelas/{program}/pratinjau/{certificate}', [Signing\SigningController::class, 'preview'])->name('preview');
        Route::post('kelas/{program}/keluarkan/{certificate}', [Signing\SigningController::class, 'exclude'])->name('exclude');
        Route::post('kelas/{program}', [Signing\SigningController::class, 'sign'])->middleware('throttle:10,1')->name('sign');
        Route::post('kelas/{program}/dompet', [Signing\SigningController::class, 'wallet'])->name('wallet');
    });

    Route::middleware('role:pengelola,admin')->prefix('pengelola')->name('pengelola.')->group(function () {
        Route::get('/', Pengelola\DashboardController::class)->name('dashboard');

        Route::resource('kelas', Pengelola\ProgramController::class)->parameters(['kelas' => 'program'])->names('programs');
        Route::post('kelas/{program}/terbitkan', [Pengelola\ProgramController::class, 'publish'])->name('programs.publish');
        Route::post('kelas/{program}/tutup', [Pengelola\ProgramController::class, 'close'])->name('programs.close');
        Route::get('kelas/{program}/peserta', [Pengelola\ProgramController::class, 'participants'])->name('programs.participants');
        Route::get('kelas/{program}/peserta/xlsx', [Pengelola\ProgramController::class, 'export'])->name('programs.export');
        Route::post('kelas/sesi/{session}/kode', [Pengelola\ProgramController::class, 'code'])->name('programs.code');
        Route::post('kelas/{program}/penanda-tangan', [Pengelola\ProgramController::class, 'signers'])->name('programs.signers');
        Route::resource('pembicara', Pengelola\SpeakerController::class)->parameters(['pembicara' => 'speaker'])->except('show')->names('speakers');

        Route::post('siniar/ambil', [Pengelola\PodcastController::class, 'fetch'])->name('podcasts.fetch');
        Route::post('siniar/{podcast}/ringkas', [Pengelola\PodcastController::class, 'summarize'])->middleware('throttle:ai')->name('podcasts.summarize');
        Route::post('siniar/{podcast}/terbitkan', [Pengelola\PodcastController::class, 'publish'])->name('podcasts.publish');
        Route::resource('siniar', Pengelola\PodcastController::class)->parameters(['siniar' => 'podcast'])->names('podcasts');

        Route::get('sertifikat', [Pengelola\CertificateController::class, 'index'])->name('certificates.index');
        Route::post('sertifikat/kelas/{program}/catat-ulang', [Pengelola\CertificateController::class, 'retry'])->name('certificates.retry');
        Route::post('sertifikat/{certificate}/cabut', [Pengelola\CertificateController::class, 'revoke'])->name('certificates.revoke');

        Route::get('bos', [Pengelola\BossController::class, 'index'])->name('bosses.index');
        Route::post('bos/undang', [Pengelola\BossController::class, 'invite'])->name('bosses.invite');
        Route::post('bos/{boss}/aktifkan', [Pengelola\BossController::class, 'activate'])->name('bosses.activate');
        Route::post('bos/{boss}/tolak', [Pengelola\BossController::class, 'reject'])->name('bosses.reject');
        Route::resource('pengumuman', Pengelola\AnnouncementController::class)->parameters(['pengumuman' => 'announcement'])->except('show')->names('announcements');
        Route::get('wawasan', Pengelola\InsightController::class)->name('insights');

        Route::get('lapak', [Pengelola\ShopModerationController::class, 'index'])->name('shops.index');
        Route::post('lapak/{shop}/setujui', [Pengelola\ShopModerationController::class, 'approve'])->name('shops.approve');
        Route::post('lapak/{shop}/tolak', [Pengelola\ShopModerationController::class, 'reject'])->name('shops.reject');
        Route::post('lapak/{shop}/turunkan', [Pengelola\ShopModerationController::class, 'takedown'])->name('shops.takedown');
    });

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', Admin\DashboardController::class)->name('dashboard');
        Route::resource('pengguna', Admin\UserController::class)->parameters(['pengguna' => 'user'])->only(['index', 'create', 'store', 'edit', 'update'])->names('users');
        Route::post('pengguna/{user}/reset-sandi', [Admin\UserController::class, 'sendReset'])->name('users.reset');
        Route::get('ai', [Admin\AiController::class, 'index'])->name('ai.index');
        Route::patch('ai/{feature}', [Admin\AiController::class, 'update'])->name('ai.update');
        Route::post('ai/prompt/{feature}', [Admin\AiController::class, 'storePrompt'])->name('ai.prompts.store');
        Route::post('ai/prompt/{feature}/uji', [Admin\AiController::class, 'testPrompt'])->middleware('throttle:ai')->name('ai.prompts.test');
        Route::resource('halaman', Admin\PageController::class)->parameters(['halaman' => 'page'])->only(['index', 'edit', 'update'])->names('pages');
        Route::resource('bank-kejadian', Admin\EventBankController::class)->parameters(['bank-kejadian' => 'event'])->except('show')->names('events');
        Route::get('pengaturan', [Admin\SettingController::class, 'edit'])->name('settings.edit');
        Route::patch('pengaturan', [Admin\SettingController::class, 'update'])->name('settings.update');
        Route::get('blockchain', [Admin\ChainController::class, 'index'])->name('chain');
        Route::get('audit', [Admin\SystemController::class, 'audit'])->name('audit');
    });
});
