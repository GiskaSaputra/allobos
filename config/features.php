<?php

return [
    // Lencana Demo di samping logo dan data contoh.
    'demo' => (bool) env('APP_DEMO', false),

    // Bila false, akun baru langsung terverifikasi. Dipakai saat server belum punya SMTP.
    'verify_email' => (bool) env('REQUIRE_EMAIL_VERIFICATION', true),

    // Kata sandi empat akun demo. Dibaca lewat config supaya tetap jalan setelah config:cache.
    'demo_password' => env('DEMO_PASSWORD') ?: 'allobos-demo',

    'boss_sessions_per_day' => (int) env('BOSS_SESSIONS_PER_DAY', 5),
    'transcript_days' => 180,
    'idle_session_minutes' => 30,
];
