<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Analytics\Facades\Analytics;
use Spatie\Analytics\Period;

class UpdateUserViewCount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-user-view-count';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $data = Analytics::fetchVisitorsAndPageViews(Period::days(7));
    }
}
