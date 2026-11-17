<?php

return [
    // Lencana Demo di samping logo dan data contoh.
    'demo' => (bool) env('APP_DEMO', false),

    // Bila false, akun baru langsung terverifikasi. Dipakai saat server belum punya SMTP.
    'verify_email' => (bool) env('REQUIRE_EMAIL_VERIFICATION', true),

    'transcript_days' => 180,
    'idle_session_minutes' => 30,
];
