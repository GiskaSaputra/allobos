<?php

// Bobot bawaan Skor Siap Usaha (PRD JU-02). Admin dapat menimpanya lewat pengaturan.
return [
    'weights' => [
        'profil' => 10,
        'konsultasi' => 15,
        'belajar' => 25,
        'simulasi' => 15,
        'rencana' => 25,
        'lapak' => 10,
    ],

    'labels' => [
        'profil' => 'Profil Usaha',
        'konsultasi' => 'Konsultasi',
        'belajar' => 'Belajar',
        'simulasi' => 'Simulasi',
        'rencana' => 'Rencana dan langkah',
        'lapak' => 'Lapak',
    ],

    // Jumlah bukti yang dianggap penuh untuk tiap komponen.
    'targets' => [
        'konsultasi_sesi' => 2,
        'belajar_butir' => 4,
    ],

    'levels' => [
        0 => 'Baru mulai',
        30 => 'Mulai terarah',
        60 => 'Hampir siap',
        85 => 'Siap usaha',
    ],
];
