<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('allobos:ruang-bos-rapikan')->everyFiveMinutes()->withoutOverlapping();
