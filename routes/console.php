<?php

use App\Console\Commands\MangadexLatest;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command(MangadexLatest::class)
    ->everyMinute()
    ->runInBackground();
