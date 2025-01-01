<?php

use App\Console\Commands\MangadexLatest;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command(MangadexLatest::class)
    ->everyTenSeconds()
    ->runInBackground();
