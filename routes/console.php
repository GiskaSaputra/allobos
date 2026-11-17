<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('allobos:ruang-bos-rapikan')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('allobos:pengingat')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('allobos:pembayaran-kedaluwarsa')->everyFiveMinutes()->withoutOverlapping();
