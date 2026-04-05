<?php

use Illuminate\Support\Facades\Schedule;

// Fetch news every 3 hours
Schedule::command('news:fetch')
    ->everyThreeHours()
    ->withoutOverlapping()
    ->runInBackground();

// Summarize new articles every 30 minutes
Schedule::command('news:summarize')
    ->everyThirtyMinutes()
    ->withoutOverlapping();
