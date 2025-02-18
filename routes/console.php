<?php

use App\Console\Commands\MangadexLatest;
use App\Console\Commands\UpdateMangadexSeries;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command(MangadexLatest::class)
    ->everyMinute()
    ->runInBackground();

Schedule::command(UpdateMangadexSeries::class)
    ->everyThreeMinutes()
    ->runInBackground();

Schedule::command('telescope:prune')->daily();
