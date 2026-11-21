<?php

// Sidebar dashboard dan navigasi warga. Butir tanpa rute terdaftar tidak ditampilkan.
$pengelola = [
    ['Ringkasan', 'pengelola.dashboard', 'phosphor-squares-four'],
    ['Kelas', 'pengelola.programs.index', 'phosphor-chalkboard-teacher'],
    ['Pembicara', 'pengelola.speakers.index', 'phosphor-microphone-stage'],
    ['Sertifikat', 'pengelola.certificates.index', 'phosphor-certificate'],
    ['Tanda Tangan Sertifikat', 'signing.index', 'phosphor-signature'],
    ['Siniar', 'pengelola.podcasts.index', 'phosphor-headphones'],
    ['Kurasi Bos', 'pengelola.bosses.index', 'phosphor-user-check'],
    ['Moderasi Lapak', 'pengelola.shops.index', 'phosphor-storefront'],
    ['Pengumuman', 'pengelola.announcements.index', 'phosphor-megaphone'],
    ['Wawasan', 'pengelola.insights', 'phosphor-chart-bar'],
];

return [
    'bos' => [
        ['Ringkasan', 'bos.dashboard', 'phosphor-squares-four'],
        ['Permintaan Sesi', 'bos.sessions.index', 'phosphor-chats'],
        ['Jadwal dan Slot', 'bos.slots.index', 'phosphor-calendar'],
        ['Tanda Tangan Sertifikat', 'signing.index', 'phosphor-signature'],
        ['Profil Bos', 'bos.profile.edit', 'phosphor-identification-card'],
    ],

    'pengelola' => $pengelola,

    'admin' => [
        ['Ringkasan Admin', 'admin.dashboard', 'phosphor-gauge'],
        ...$pengelola,
        ['Pengguna', 'admin.users.index', 'phosphor-users'],
        ['Pengaturan AI', 'admin.ai.index', 'phosphor-robot'],
        ['Blockchain', 'admin.chain', 'phosphor-link'],
        ['Halaman', 'admin.pages.index', 'phosphor-files'],
        ['Bank Kejadian', 'admin.events.index', 'phosphor-dice-five'],
        ['Pengaturan', 'admin.settings.edit', 'phosphor-sliders'],
        ['Audit', 'admin.audit', 'phosphor-list-magnifying-glass'],
    ],

    'warga' => [
        ['Beranda', 'warga.dashboard', 'phosphor-house', []],
        ['Ruang Bos', 'boss-room.setup', 'phosphor-chat-circle-dots', []],
        ['Belajar', 'warga.programs.index', 'phosphor-book-open', [
            ['Kelas Saya', 'warga.programs.index'],
            ['Jelajahi Kelas', 'programs.index'],
            ['Sertifikat Saya', 'warga.certificates.index'],
            ['Verifikasi Sertifikat', 'certificates.verify.form'],
            ['Siniar', 'podcasts.index'],
            ['Warung 3D', 'game.index'],
            ['Temu Bos', 'warga.mentoring.index'],
        ]],
        ['Rancang', 'warga.opportunities.index', 'phosphor-compass', [
            ['Peluang', 'warga.opportunities.index'],
            ['Rencana', 'warga.plan.show'],
            ['Langkah', 'warga.steps.index'],
            ['Cek Produk', 'warga.checks.index'],
        ]],
        ['Lapak', 'warga.shop.edit', 'phosphor-storefront', [
            ['Lapak Saya', 'warga.shop.edit'],
            ['Jelajahi Lapak Warga', 'shops.index'],
        ]],
    ],
];
