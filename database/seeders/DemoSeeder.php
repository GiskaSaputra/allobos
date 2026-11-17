<?php

namespace Database\Seeders;

use App\Enums\BossStatus;
use App\Enums\CertificateStatus;
use App\Enums\CheckStatus;
use App\Enums\ContentStatus;
use App\Enums\EnrollmentStatus;
use App\Enums\MentoringStatus;
use App\Enums\ProgramKind;
use App\Enums\ProgramStatus;
use App\Enums\RoleName;
use App\Enums\SessionStatus;
use App\Enums\ShopStatus;
use App\Models\ActivityLog;
use App\Models\Announcement;
use App\Models\BossProfile;
use App\Models\BossSession;
use App\Models\BusinessField;
use App\Models\Certificate;
use App\Models\District;
use App\Models\MentoringSession;
use App\Models\Podcast;
use App\Models\ProductCheck;
use App\Models\Program;
use App\Models\ReadinessSnapshot;
use App\Models\Shop;
use App\Models\SignatureSpecimen;
use App\Models\Speaker;
use App\Models\User;
use App\Services\Certificates\AnchorService;
use App\Services\Chain\ChainException;
use App\Services\Game\GameEngine;
use App\Services\Game\GameRules;
use App\Services\Game\GameService;
use App\Services\Mentoring\MentoringService;
use App\Services\Plans\OpportunityFinder;
use App\Services\Plans\PlanBuilder;
use App\Services\Readiness\ReadinessCalculator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Data contoh untuk penjurian. Semua data di sini berlabel Demo dan tidak dihitung di statistik publik.
 * Skenario: Bu Sri, pembuat keripik singkong di Ponjong, dari profil sampai lapak dan sertifikat.
 */
class DemoSeeder extends Seeder
{
    private ReadinessCalculator $readiness;

    public function run(): void
    {
        $this->call(DatabaseSeeder::class);
        $this->readiness = app(ReadinessCalculator::class);
        // Data demo disusun tanpa AI supaya hasilnya sama setiap kali diisi ulang.
        config(['ai.keys' => []]);

        [$sri, $arif, $rani] = $this->accounts();
        $fieldKuliner = BusinessField::query()->where('slug', 'kuliner-dan-makanan-olahan')->value('id');
        $fieldOleh = BusinessField::query()->where('slug', 'oleh-oleh-dan-suvenir')->value('id');
        $fieldWisata = BusinessField::query()->where('slug', 'pariwisata-dan-penginapan')->value('id');
        $ponjong = District::query()->where('slug', 'ponjong')->value('id');

        $boss = $this->boss($arif, $fieldWisata);
        $speaker = Speaker::query()->updateOrCreate(['name' => $arif->name], ['business' => 'Pemilik homestay dan warung oleh-oleh di Tepus', 'bio' => 'Menjalankan usaha penginapan dan oleh-oleh sejak 2013.', 'user_id' => $arif->id, 'is_demo' => true]);

        // 1. Kenali
        $sri->profile()->updateOrCreate([], [
            'district_id' => $ponjong, 'business_status' => 'sudah', 'existing_business' => 'Keripik singkong titip warung',
            'interests' => [$fieldKuliner, $fieldOleh], 'capital_range' => 'lt1', 'free_time' => '2-4',
            'skills' => 'Menggoreng keripik, meracik bumbu balado, memakai WhatsApp', 'step' => 5, 'onboarded_at' => now()->subDays(20),
        ]);
        $this->evidence($sri, 'profil', 'Melengkapi Profil Usaha');

        // 2. Tanya
        $session = $sri->hasMany(BossSession::class)->create(['topic' => 'Menentukan harga jual', 'mode' => 'suara', 'status' => SessionStatus::Selesai, 'turns_count' => 2, 'started_at' => now()->subDays(18), 'ended_at' => now()->subDays(18)->addMinutes(12)]);
        foreach ([
            ['bos', 'Halo Sri, saya Bos Allo. Senang ngobrol dengan warga Ponjong. Hari ini kita bahas "Menentukan harga jual". Coba ceritakan dulu kondisi usahamu sekarang.'],
            ['warga', 'Keripik saya titip di tiga warung, sebungkus saya jual lima ribu. Tapi rasanya untungnya tipis sekali.'],
            ['bos', 'Harga jual sebaiknya dihitung dari biaya membuat satu bungkus, lalu ditambah untung. Coba catat biaya singkong, minyak, bumbu, dan plastik untuk sekali goreng.'],
            ['warga', 'Sekali goreng sekitar seratus ribu, jadinya lima puluh bungkus.'],
            ['bos', 'Berarti biaya bahan sekitar dua ribu per bungkus, belum termasuk gas dan tenaga. Dengan harga lima ribu, untungmu masih ada, tapi warung juga minta bagian. Hitung di Kalkulator HPP supaya jelas.'],
        ] as [$role, $text]) {
            $session->turns()->create(['role' => $role, 'content' => $text]);
        }
        $note = $session->note()->create([
            'user_id' => $sri->id,
            'problem' => 'Bu Sri menjual keripik singkong lima ribu per bungkus lewat titip warung, tetapi belum tahu biaya sebenarnya sehingga untungnya terasa tipis.',
            'advice' => ['Hitung HPP per bungkus termasuk gas dan bagian warung', 'Coba kemasan yang lebih menarik untuk pembeli oleh-oleh', 'Jual juga langsung lewat WhatsApp supaya tidak semua untung terbagi'],
            'next_steps' => ['Catat semua biaya sekali produksi', 'Hitung HPP di Kalkulator HPP', 'Tanya harga plastik klip di dua toko'],
            'modules' => ['rencana', 'kelas'],
            'is_ai' => true,
            'steps_added_at' => now()->subDays(18),
        ]);
        $this->evidence($sri, 'ruang_bos', 'Konsultasi dengan Bos Allo: Menentukan harga jual');

        $slotPast = $boss->slots()->create(['starts_at' => now()->subDays(12)->setTime(10, 0), 'duration_minutes' => 60, 'mode' => 'daring']);
        MentoringSession::query()->create([
            'boss_slot_id' => $slotPast->id, 'boss_profile_id' => $boss->id, 'user_id' => $sri->id, 'consult_note_id' => $note->id,
            'topic' => 'Cara menitipkan keripik ke toko oleh-oleh di jalur pantai', 'status' => MentoringStatus::Selesai,
            'meeting_link' => 'https://meet.google.com/', 'boss_notes' => 'Toko oleh-oleh butuh kemasan berlabel dan harga grosir yang jelas. Mulai dari dua toko di jalur Pantai Baron, bawa contoh dan daftar harga.',
            'boss_steps' => ['Siapkan label dengan nama dan nomor WhatsApp', 'Buat daftar harga grosir untuk toko'], 'rating' => 5, 'rating_comment' => 'Penjelasannya praktis.',
        ]);
        app(MentoringService::class)->refreshRating($boss);
        foreach ([2, 4, 6] as $days) {
            $boss->slots()->create(['starts_at' => now()->addDays($days)->setTime(13, 0), 'duration_minutes' => 60, 'mode' => $days === 4 ? 'tatap_muka' : 'daring']);
        }
        $this->evidence($sri, 'temu_bos', 'Sesi Temu Bos dengan '.$arif->name);

        // 3. Belajar: kelas yang sudah ditutup dengan sertifikat, dan dua kelas yang dibuka
        $closed = $this->program('Workshop Kemasan dan Harga Jual Camilan', ProgramKind::Workshop, $fieldKuliner, ProgramStatus::Selesai, -10, 2, $speaker, $rani);
        $closed->signers()->delete();
        $closed->signers()->create(['user_id' => $arif->id, 'title' => 'Pembicara kelas', 'sort' => 1, 'signed_at' => now()->subDays(8)]);
        $closed->signers()->create(['user_id' => $rani->id, 'title' => 'Pimpinan Institusi Pengusaha Indonesia', 'sort' => 2, 'signed_at' => now()->subDays(8)]);
        $closed->enrollments()->updateOrCreate(['user_id' => $sri->id], ['status' => EnrollmentStatus::LulusHadir, 'materials_opened' => [0]]);
        foreach ($closed->sessions as $s) {
            $s->attendances()->firstOrCreate(['user_id' => $sri->id]);
        }
        $this->evidence($sri, 'kelas', 'Hadir di '.$closed->title);
        $this->specimen($arif);
        $this->specimen($rani);
        $certificate = Certificate::query()->updateOrCreate(['program_id' => $closed->id, 'user_id' => $sri->id], [
            'code' => Certificate::query()->where('program_id', $closed->id)->where('user_id', $sri->id)->value('code') ?? Certificate::generateCode(),
            'status' => CertificateStatus::Ditandatangani, 'recipient_name' => $sri->name, 'issued_on' => now()->subDays(8)->toDateString(), 'is_demo' => true, 'show_on_shop' => true,
        ]);
        $anchors = app(AnchorService::class);
        $anchors->prepare($closed);
        try {
            $anchors->anchor($closed);
        } catch (ChainException|\Throwable $e) {
            $this->command?->warn('Sertifikat demo belum tercatat di blockchain: '.$e->getMessage());
        }

        $this->program('Bootcamp Pemasaran Lewat WhatsApp dan Lapak Online', ProgramKind::Bootcamp, $fieldOleh, ProgramStatus::Dibuka, 6, 3, $speaker, $rani, 50000);
        $this->program('Seminar Izin Usaha: NIB dan PIRT untuk Makanan Rumahan', ProgramKind::Seminar, $fieldKuliner, ProgramStatus::Dibuka, 9, 1, $speaker, $rani);

        foreach ($this->podcasts($fieldKuliner)->take(2) as $podcast) {
            $podcast->listens()->updateOrCreate(['user_id' => $sri->id], ['listened_at' => now()->subDays(9), 'note' => 'Coba kemasan berlabel seperti contoh di episode.']);
        }
        $this->evidence($sri, 'siniar', 'Mendengar siniar tentang usaha keripik');

        $this->game($sri);

        // 4. Rancang
        $finder = app(OpportunityFinder::class);
        $opportunity = $finder->generate($sri)->first();
        $finder->choose($opportunity);
        $plan = app(PlanBuilder::class)->build($sri, $opportunity);
        $plan->update(['batch_units' => 50, 'batches_per_month' => 12, 'selling_price' => 7000]);
        $plan->costItems()->delete();
        foreach ([['Singkong 10 kg', 'bahan', 40000], ['Minyak goreng 2 liter', 'bahan', 36000], ['Bumbu balado', 'bahan', 15000], ['Plastik klip dan label', 'bahan', 12500], ['Gas elpiji', 'tetap', 66000], ['Bagian titip warung', 'tetap', 60000]] as [$name, $kind, $amount]) {
            $plan->costItems()->create(['name' => $name, 'kind' => $kind, 'amount' => $amount]);
        }
        $sri->steps()->orderBy('sort')->limit(5)->get()->each(fn ($step) => $step->update(['done_at' => now()->subDays(4)]));
        $this->evidence($sri, 'rencana', 'Menyelesaikan lima langkah pertama');

        $photo = $this->image('demo/keripik-balado.webp', [214, 160, 70], [176, 100, 38]);
        $check = ProductCheck::query()->create([
            'user_id' => $sri->id, 'photo_path' => $photo, 'status' => CheckStatus::Selesai, 'name' => 'Keripik singkong balado',
            'result' => [
                'name' => 'Keripik singkong balado', 'category' => 'Camilan kemasan', 'photo_score' => 3, 'pack_score' => 2,
                'good' => ['Warna keripik merata dan terlihat renyah'], 'fixes' => ['Tambahkan label nama usaha dan nomor WhatsApp', 'Foto di tempat terang dengan latar polos'],
                'price_min' => 7000, 'price_max' => 10000, 'price_reason' => 'Camilan kemasan 250 gram di jalur wisata umumnya dijual pada kisaran ini.',
                'signal' => 'naik', 'reasons' => ['Musim liburan meningkatkan pembeli oleh-oleh'], 'sources' => [],
                'description' => 'Keripik singkong tipis dari singkong Ponjong, digoreng setiap pagi dan dibalut bumbu balado pedas manis. Renyah, gurih, cocok untuk oleh-oleh khas Gunungkidul.',
            ],
        ]);
        $this->evidence($sri, 'cek_produk', 'Memeriksa produk: Keripik singkong balado');

        // 5. Buka
        $shop = Shop::query()->updateOrCreate(['user_id' => $sri->id], [
            'name' => 'Keripik Mbok Sri', 'slug' => 'keripik-mbok-sri', 'district_id' => $ponjong, 'business_field_id' => $fieldKuliner,
            'description' => "Keripik singkong rumahan dari Ponjong. Digoreng setiap pagi tanpa pengawet.\nPesan lewat WhatsApp, bisa ambil di rumah atau titip ke warung terdekat.",
            'whatsapp' => '6281200000000', 'cover_path' => $this->image('demo/sampul-lapak.webp', [139, 161, 58], [199, 185, 46], 1600, 600),
            'status' => ShopStatus::Terbit, 'published_at' => now()->subDays(2), 'is_demo' => true,
        ]);
        $shop->products()->delete();
        foreach ([
            ['Keripik singkong balado', 8000, 'bungkus 250 g', $photo, $check->id, $check->result['description']],
            ['Keripik singkong original', 7000, 'bungkus 250 g', $this->image('demo/keripik-original.webp', [232, 196, 110], [205, 160, 80]), null, 'Rasa gurih asin sederhana, kesukaan anak-anak.'],
            ['Tiwul instan', 10000, 'bungkus 500 g', $this->image('demo/tiwul.webp', [150, 110, 80], [110, 80, 60]), null, 'Tiwul kering siap dikukus, tinggal tambah gula jawa dan kelapa.'],
        ] as $i => [$name, $price, $unit, $path, $checkId, $desc]) {
            $shop->products()->create(['name' => $name, 'price' => $price, 'unit' => $unit, 'photo_path' => $path, 'product_check_id' => $checkId, 'description' => $desc, 'sort' => $i]);
        }
        foreach (range(1, 23) as $i) {
            $shop->visits()->create(['kind' => $i % 4 === 0 ? 'pesan' : 'kunjungan']);
        }
        $this->evidence($sri, 'lapak', 'Lapak terbit: Keripik Mbok Sri');

        Announcement::query()->updateOrCreate(['title' => 'Pendaftaran Bootcamp Pemasaran dibuka'], ['body' => 'Contoh pengumuman: kuota terbatas, daftar lewat menu Belajar lalu Kelas.', 'audience' => 'warga', 'published_at' => now(), 'created_by' => $rani->id]);

        $this->catalog($rani);

        $score = $this->readiness->refresh($sri->fresh());
        $this->spreadHistory($sri);
        $this->command?->info('Data demo siap. Skor Siap Usaha Bu Sri: '.$score.'. Status sertifikat: '.$certificate->fresh()->status->label().'.');
    }

    /**
     * Isi tambahan supaya katalog publik terasa hidup: bos pendamping, kelas, lapak warga, dan episode siniar.
     * Semua berlabel data contoh (is_demo). Episode siniar memakai video publik YouTube yang sudah dicek lewat oEmbed.
     */
    private function catalog(User $rani): void
    {
        $field = fn (string $slug) => BusinessField::query()->where('slug', $slug)->value('id');
        $district = fn (string $slug) => District::query()->where('slug', $slug)->value('id');
        $password = config('features.demo_password');
        $person = function (string $email, string $name, RoleName $role) use ($password): User {
            $user = User::query()->updateOrCreate(['email' => $email], [
                'name' => $name, 'whatsapp' => '6281200000000', 'password' => $password, 'email_verified_at' => now(),
                'active_role' => $role, 'is_active' => true, 'is_demo' => true,
            ]);
            $user->syncRoles([$role->value]);

            return $user;
        };

        // Bos pendamping tambahan
        $speakers = [];
        foreach ([
            ['bos.ratna@allobos.com', 'Ratna Kusuma', 'Batik Tulis Sekar Gedangsari', 'fesyen-dan-batik', 'gedangsari', 9, 'Membina dua puluh perajin batik tulis rumahan dan memasok butik di Yogyakarta.'],
            ['bos.hendra@allobos.com', 'Hendra Saputro', 'Kerajinan Topeng Kayu Bobung', 'kerajinan-tangan', 'patuk', 14, 'Menjual topeng dan suvenir kayu ke toko oleh-oleh dan pembeli luar negeri.'],
            ['bos.wulan@allobos.com', 'Wulandari', 'Olahan Rumput Laut Pantai Selatan', 'kuliner-dan-makanan-olahan', 'tanjungsari', 7, 'Mengolah rumput laut menjadi keripik dan dodol untuk pasar wisata pantai.'],
            ['bos.bayu@allobos.com', 'Bayu Prasetyo', 'Toko Kelontong dan Agen Sembako', 'perdagangan-dan-kelontong', 'wonosari', 12, 'Mengelola toko kelontong dan agen sembako yang memasok warung di tiga kalurahan.'],
        ] as [$email, $name, $business, $fieldSlug, $districtSlug, $years, $desc]) {
            $user = $person($email, $name, RoleName::Bos);
            BossProfile::query()->updateOrCreate(['user_id' => $user->id], [
                'business_name' => $business, 'business_field_id' => $field($fieldSlug), 'district_id' => $district($districtSlug), 'years_running' => $years,
                'business_desc' => 'Data contoh. '.$desc, 'track_record' => 'Data contoh. Aktif berbagi pengalaman di kelas dan pendampingan IPI.',
                'status' => BossStatus::Aktif, 'checklist' => array_fill_keys(array_keys(BossProfile::STANDARDS), true), 'is_demo' => true,
            ]);
            $speakers[$fieldSlug] = Speaker::query()->updateOrCreate(['name' => $name], ['business' => $business, 'bio' => $desc, 'user_id' => $user->id, 'is_demo' => true]);
        }

        // Kelas tambahan yang sedang dibuka
        foreach ([
            ['Kelas Batik Tulis untuk Pemula', ProgramKind::Workshop, 'fesyen-dan-batik', 12, 2, 75000],
            ['Membuat Suvenir Kayu yang Laku di Toko Oleh-oleh', ProgramKind::Workshop, 'kerajinan-tangan', 15, 2, 0],
            ['Olahan Hasil Laut untuk Pasar Wisata', ProgramKind::Bootcamp, 'kuliner-dan-makanan-olahan', 18, 3, 60000],
            ['Mengelola Stok dan Kas Warung Kelontong', ProgramKind::Seminar, 'perdagangan-dan-kelontong', 21, 1, 0],
        ] as [$title, $kind, $fieldSlug, $days, $sessions, $price]) {
            $this->program($title, $kind, $field($fieldSlug), ProgramStatus::Dibuka, $days, $sessions, $speakers[$fieldSlug], $rani, $price);
        }

        // Lapak warga tambahan
        $palettes = [[[139, 161, 58], [199, 185, 46]], [[116, 184, 140], [190, 222, 140]], [[150, 110, 70], [214, 170, 96]], [[62, 77, 30], [139, 161, 58]], [[180, 120, 80], [232, 196, 110]]];
        foreach ([
            ['lapak.batik@allobos.com', 'Siti Aminah', 'Batik Tulis Bu Siti', 'fesyen-dan-batik', 'gedangsari', 'Batik tulis motif alam Gedangsari, diwarnai dengan pewarna alami.', [['Kain batik tulis motif daun', 350000, 'lembar'], ['Selendang batik', 125000, 'helai'], ['Masker kain batik', 15000, 'buah']]],
            ['lapak.topeng@allobos.com', 'Sugeng Riyadi', 'Topeng Kayu Pak Sugeng', 'kerajinan-tangan', 'patuk', 'Topeng kayu pule dan gantungan kunci ukir, dibuat tangan di Bobung.', [['Topeng panji ukuran sedang', 85000, 'buah'], ['Gantungan kunci topeng', 10000, 'buah'], ['Hiasan dinding topeng', 150000, 'buah']]],
            ['lapak.rumputlaut@allobos.com', 'Rina Wati', 'Rumput Laut Mbak Rina', 'kuliner-dan-makanan-olahan', 'tanjungsari', 'Olahan rumput laut dari pesisir Tanjungsari, renyah dan tanpa pengawet.', [['Keripik rumput laut', 12000, 'bungkus 150 g'], ['Dodol rumput laut', 15000, 'kotak'], ['Peyek rumput laut', 10000, 'bungkus']]],
            ['lapak.gaplek@allobos.com', 'Painem', 'Gaplek dan Tiwul Semanu', 'kuliner-dan-makanan-olahan', 'semanu', 'Gaplek, tiwul, dan gatot dari singkong petani Semanu.', [['Tiwul instan gula jawa', 12000, 'bungkus 500 g'], ['Gatot kering', 9000, 'bungkus 250 g'], ['Gaplek iris', 8000, 'kg']]],
            ['lapak.madu@allobos.com', 'Tukiman', 'Madu Klanceng Playen', 'peternakan-dan-perikanan', 'playen', 'Madu lebah klanceng dari kebun karst Playen, dipanen sendiri.', [['Madu klanceng botol kecil', 65000, 'botol 100 ml'], ['Madu klanceng botol besar', 175000, 'botol 300 ml'], ['Propolis mentah', 50000, 'wadah']]],
        ] as $i => [$email, $name, $shopName, $fieldSlug, $districtSlug, $desc, $products]) {
            $owner = $person($email, $name, RoleName::Warga);
            [$from, $to] = $palettes[$i];
            $slug = Str::slug($shopName);
            $shop = Shop::query()->updateOrCreate(['user_id' => $owner->id], [
                'name' => $shopName, 'slug' => $slug, 'district_id' => $district($districtSlug), 'business_field_id' => $field($fieldSlug),
                'description' => $desc."\nPesan lewat WhatsApp untuk ambil di tempat atau kirim.", 'whatsapp' => '6281200000000',
                'cover_path' => $this->image("demo/sampul-{$slug}.webp", $from, $to, 1600, 600),
                'status' => ShopStatus::Terbit, 'published_at' => now()->subDays(3 + $i), 'is_demo' => true,
            ]);
            $shop->products()->delete();
            foreach ($products as $n => [$productName, $price, $unit]) {
                $path = $this->image('demo/'.Str::slug($productName).'.webp', $to, $from);
                $shop->products()->create(['name' => $productName, 'price' => $price, 'unit' => $unit, 'photo_path' => $path, 'description' => $desc, 'sort' => $n]);
            }
        }
    }

    /** @return array{User, User, User} */
    private function accounts(): array
    {
        $password = config('features.demo_password');
        $users = [];
        foreach ([[RoleName::Warga, 'Sri Lestari', 'warga@allobos.com'], [RoleName::Bos, 'Arif Wibowo', 'bos@allobos.com'], [RoleName::Pengelola, 'Rani Pratiwi', 'pengelola@allobos.com'], [RoleName::Admin, 'Admin alloBos', 'admin@allobos.com']] as [$role, $name, $email]) {
            $user = User::query()->updateOrCreate(['email' => $email], [
                'name' => $name, 'whatsapp' => '6281200000000', 'password' => $password, 'email_verified_at' => now(),
                'active_role' => $role, 'is_active' => true, 'is_demo' => true,
            ]);
            $user->syncRoles([$role->value]);
            $users[$role->value] = $user;
        }

        return [$users['warga'], $users['bos'], $users['pengelola']];
    }

    private function boss(User $arif, int $field): BossProfile
    {
        $boss = BossProfile::query()->updateOrCreate(['user_id' => $arif->id], [
            'business_name' => 'Homestay dan Warung Oleh-oleh Pantai', 'business_field_id' => $field,
            'district_id' => District::query()->where('slug', 'tepus')->value('id'), 'years_running' => 11,
            'business_desc' => 'Data contoh. Menjalankan homestay enam kamar dan warung oleh-oleh di jalur pantai selatan. Bisa berbagi soal menjual ke wisatawan dan menitipkan produk ke toko.',
            'track_record' => 'Data contoh. Melayani rombongan wisata sekolah dan keluarga setiap musim liburan.',
            'status' => BossStatus::Aktif, 'checklist' => array_fill_keys(array_keys(BossProfile::STANDARDS), true), 'is_demo' => true,
        ]);
        $boss->slots()->delete();

        return $boss;
    }

    private function program(string $title, ProgramKind $kind, int $field, ProgramStatus $status, int $startInDays, int $sessions, Speaker $speaker, User $creator, int $price = 0): Program
    {
        $program = Program::query()->updateOrCreate(['slug' => Str::slug($title)], [
            'title' => $title, 'kind' => $kind, 'business_field_id' => $field, 'status' => $status, 'quota' => 30, 'price' => $price,
            'description' => "Data contoh untuk demonstrasi alloBos.\n\nPeserta belajar langsung dari pengusaha yang menjalankan usahanya sendiri, dengan praktik dan tanya jawab.",
            'location' => 'Balai Kalurahan Ponjong, Gunungkidul', 'materials' => [['title' => 'Lembar kerja hitung HPP', 'url' => 'https://example.com/lembar-kerja']],
            'is_official' => false, 'is_demo' => true, 'created_by' => $creator->id, 'closed_at' => $status === ProgramStatus::Selesai ? now()->subDays(9) : null,
        ]);
        $program->sessions()->delete();
        for ($i = 0; $i < $sessions; $i++) {
            $start = now()->addDays($startInDays + $i)->setTime(9, 0);
            $program->sessions()->create(['title' => 'Sesi '.($i + 1), 'starts_at' => $start, 'ends_at' => $start->copy()->addHours(3)]);
        }
        $program->speakers()->sync([$speaker->id]);

        return $program->load('sessions');
    }

    /**
     * Episode publik dari YouTube, dicek lewat oEmbed. Dua episode pertama diringkas dari keterangan video.
     * Episode lainnya diberi ringkasan pendek dari judul, lalu di server bisa diringkas ulang oleh Gemini yang menonton videonya.
     */
    private function podcasts(int $field)
    {
        $slug = fn (string $s) => BusinessField::query()->where('slug', $s)->value('id') ?? $field;
        $items = [
            ['O9T5PZyfBIU', 'Bongkar kisah keripik pedas dari nol', 'Pendiri merek keripik singkong pedas bercerita tentang membangun usaha dari nol: memasarkan lewat jaringan agen, menghadapi persaingan, mengikuti selera pembeli, dan menjaga rasa tetap sama dari waktu ke waktu.', ['Cari cara jualan yang membuat pembeli penasaran, bukan hanya harga murah', 'Jaga rasa dan mutu tetap sama di setiap bungkus', 'Siapkan diri menghadapi pesaing yang meniru produkmu'], 38, 'kuliner-dan-makanan-olahan'],
            ['rPyxzXfWoDI', 'Usaha keripik singkong rumahan sampai satu ton sebulan', 'Kunjungan ke usaha keripik singkong rumahan yang dirintis sejak 2018. Pemilik memilih singkong dari daerah tertentu supaya hasil gorengan renyah, lalu menambah produksi sedikit demi sedikit.', ['Mutu bahan baku menentukan rasa dan kerenyahan', 'Naikkan jumlah produksi bertahap mengikuti pesanan', 'Catat asal bahan supaya hasilnya bisa diulang'], 12, 'kuliner-dan-makanan-olahan'],
            ['4hetkoW8MPw', 'Opak singkong rumahan yang tembus pasar luar daerah', 'Liputan Tribunnews tentang usaha opak singkong rumahan milik Onih di Tangerang yang produknya kini dijual sampai ke luar daerah.', ['Olahan singkong sederhana bisa dijual lebih jauh bila kemasannya rapi', 'Pelanggan pertama biasanya datang dari lingkungan terdekat', 'Jaga pasokan bahan supaya pesanan besar bisa dipenuhi'], null, 'kuliner-dan-makanan-olahan'],
            ['1QSZR5GRJkI', 'Cara keripik masuk ratusan swalayan', 'Episode TVBisnis tentang langkah yang perlu disiapkan supaya produk keripik bisa dijual di swalayan.', ['Siapkan izin edar dan label yang lengkap sebelum menawarkan ke toko', 'Hitung harga grosir yang masih menyisakan untung', 'Pastikan bisa memasok dengan jumlah dan mutu yang tetap'], null, 'kuliner-dan-makanan-olahan'],
            ['-KEXKkyoadY', 'Bisnis kuliner keluarga dari resep nenek', 'Obrolan di kanal Gue Mau Tau tentang awal mula bisnis kuliner keluarga yang dimulai dari resep turun-temurun.', ['Resep keluarga bisa menjadi pembeda produk', 'Catat takaran resep supaya rasanya bisa dijaga', 'Libatkan keluarga dengan pembagian tugas yang jelas'], null, 'kuliner-dan-makanan-olahan'],
            ['177VhJBHHdk', 'Bisnis kuliner bukan hanya soal masak enak', 'Episode PecahTelur tentang pola pikir yang perlu dimiliki pengusaha kuliner selain kemampuan memasak.', ['Rasa enak perlu didukung hitungan biaya yang benar', 'Pelayanan dan kebersihan ikut menentukan pembeli kembali', 'Belajar dari angka penjualan, bukan dari perasaan'], null, 'kuliner-dan-makanan-olahan'],
            ['2guEvk_aNRg', 'Kenapa banyak UMKM gagal saat mulai besar', 'Episode Grego Julius Podcast yang membahas penyebab usaha kecil kesulitan ketika mulai tumbuh.', ['Pisahkan uang usaha dan uang pribadi sejak awal', 'Tambah karyawan dan cabang setelah sistemnya rapi', 'Pantau arus kas setiap minggu'], null, 'kepemimpinan-dan-tim'],
            ['1GXTw4HyUA8', 'Kerajinan topeng batik Gunungkidul', 'Video Pariwisata Gunungkidul tentang kerajinan topeng batik sebagai salah satu potensi subsektor kriya di Gunungkidul.', ['Kerajinan khas daerah punya nilai cerita yang bisa dijual', 'Wisatawan adalah pasar utama suvenir kriya', 'Jaga ciri khas motif supaya mudah dikenali'], null, 'kerajinan-tangan'],
            ['6V_eVpHslYg', 'Usaha beras kecil yang menjadi pemasok andal', 'Obrolan UMKM di RRI Jogja tentang usaha beras yang berawal kecil lalu menjadi pemasok tetap.', ['Kepercayaan pembeli tumbuh dari pasokan yang tepat waktu', 'Mutu yang konsisten membuka pelanggan grosir', 'Kembangkan usaha sedikit demi sedikit sesuai kemampuan modal'], null, 'pertanian-dan-perkebunan'],
        ];

        return collect($items)->map(fn ($p, $i) => Podcast::query()->updateOrCreate(['youtube_id' => $p[0]], [
            'title' => $p[1], 'slug' => Str::slug($p[1]), 'business_field_id' => $slug($p[5]), 'duration_seconds' => $p[4] ? $p[4] * 60 : null,
            'summary' => $p[2], 'lessons' => $p[3], 'summary_is_ai' => false, 'summary_state' => 'disetujui',
            'status' => ContentStatus::Terbit, 'published_at' => now()->subDays(10 + $i), 'is_demo' => true,
        ]));
    }

    private function game(User $sri): void
    {
        $games = app(GameService::class);
        $game = $games->start($sri);
        $choices = [1 => 'rapi', 2 => 'tenda', 3 => 'beda', 4 => 'normal', 5 => 'naik_sedikit', 6 => 'balas', 7 => 'pagi'];
        $plan = [1 => 1.0, 2 => 0.6, 3 => 0.85, 4 => 1.7, 5 => 1.0, 6 => 1.05, 7 => 1.6];

        for ($day = 1; $day <= 7; $day++) {
            $game->refresh();
            $state = $game->state;
            $qty = [];
            $budget = $state['cash'] - 40000;
            foreach (GameRules::KINDS[$game->kind]['items'] as $key => $item) {
                $need = (int) ceil($item['demand'] * $plan[$day] * 1.05) - $state['stock'][$key]['qty'];
                $cost = app(GameEngine::class)->unitCost($state, $key, $state['event']);
                $qty[$key] = max(0, min($need, intdiv(max(0, $budget), max(1, $cost))));
                $budget -= $qty[$key] * $cost;
            }
            $games->buy($game, $qty);
            $games->price($game->refresh(), array_map(fn ($i) => $i['fair'], GameRules::KINDS[$game->kind]['items']));
            $games->run($game->refresh(), 'tanpa', $choices[$day]);
            $games->next($game->refresh());
        }
        $games->saveLessons($game->refresh());
    }

    private function evidence(User $user, string $module, string $description): void
    {
        $this->readiness->record($user->fresh(), $module, $description);
    }

    /** Cuplikan skor dan aktivitas disebar ke 20 hari terakhir sesuai urutan kejadian, supaya grafik Jejak Usaha menggambarkan perjalanan. */
    private function spreadHistory(User $user): void
    {
        foreach ([ReadinessSnapshot::class, ActivityLog::class] as $model) {
            $rows = $model::query()->where('user_id', $user->id)->orderBy('id')->get();
            $rows->values()->each(fn ($row, $i) => $row->forceFill(['created_at' => now()->subDays(20)->addMinutes((int) round($i * 20 * 1440 / max(1, $rows->count())))])->save());
        }
    }

    private function specimen(User $user): void
    {
        $img = imagecreatetruecolor(600, 200);
        imagesavealpha($img, true);
        imagefill($img, 0, 0, imagecolorallocatealpha($img, 0, 0, 0, 127));
        $ink = imagecolorallocate($img, 20, 26, 60);
        imagesetthickness($img, 5);
        $seed = crc32($user->email);
        $x = 40;
        $y = 120;
        for ($i = 0; $i < 9; $i++) {
            $nx = $x + 50 + ($seed >> $i) % 25;
            $ny = 60 + (($seed >> ($i * 2)) % 90);
            imageline($img, $x, $y, $nx, $ny, $ink);
            [$x, $y] = [$nx, $ny];
        }
        imageline($img, 60, 165, 520, 150, $ink);
        ob_start();
        imagepng($img);
        $path = 'spesimen/'.$user->id.'.png';
        Storage::disk('local')->put($path, (string) ob_get_clean());
        SignatureSpecimen::query()->updateOrCreate(['user_id' => $user->id], ['path' => $path]);
    }

    /** Gambar contoh sederhana untuk produk dan sampul, dibuat dengan GD. */
    private function image(string $path, array $from, array $to, int $w = 900, int $h = 900): string
    {
        $img = imagecreatetruecolor($w, $h);
        for ($y = 0; $y < $h; $y++) {
            $t = $y / $h;
            $c = imagecolorallocate($img, ...array_map(fn ($a, $b) => (int) ($a + ($b - $a) * $t), $from, $to));
            imageline($img, 0, $y, $w, $y, $c);
        }
        // Lingkaran cahaya lembut dan ikon alloBos di tengah, supaya gambar contoh terlihat rapi dan bermerek.
        imagealphablending($img, true);
        $glow = imagecolorallocatealpha($img, 255, 250, 225, 96);
        imagefilledellipse($img, (int) ($w * 0.5), (int) ($h * 0.5), (int) (min($w, $h) * 0.95), (int) (min($w, $h) * 0.95), $glow);
        imagefilledellipse($img, (int) ($w * 0.5), (int) ($h * 0.5), (int) (min($w, $h) * 0.7), (int) (min($w, $h) * 0.7), $glow);
        $icon = imagecreatefrompng(public_path('image/ikon-allobos-512.png'));
        $size = (int) (min($w, $h) * 0.46);
        imagecopyresampled($img, $icon, (int) (($w - $size) / 2), (int) (($h - $size) / 2), 0, 0, $size, $size, 512, 512);
        ob_start();
        imagewebp($img, null, 78);
        Storage::disk('public')->put($path, (string) ob_get_clean());

        return $path;
    }
}
