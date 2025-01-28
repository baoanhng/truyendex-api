<?php

namespace App\Console\Commands;

use App\Models\Series;
use App\Models\Chapter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use GuzzleHttp\TransferStats;

class UpdateMangadexSeries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:mangadex-series';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update series on Mangadex';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $lastUpdatedSeries = Series::latest('md_updated_at')->first();

        $lastestMangaUpdatedAt = $lastUpdatedSeries?->md_updated_at->addSeconds(1) ?? new \DateTime("2018-01-01");
        $lastestMangaUpdatedAt = $lastestMangaUpdatedAt->format('Y-m-d\TH:i:s');

        Log::debug($lastestMangaUpdatedAt);

        $response = Http::withOptions([
            'on_stats' => function (TransferStats $stats) use (&$url) {
                $url = $stats->getEffectiveUri();
            }
        ])->get('https://api.mangadex.org/manga', [
            'updatedAtSince' => $lastestMangaUpdatedAt,
            'offset' => 0,
            'limit' => 100,
            'contentRating' => ['safe', 'suggestive', 'erotica', 'pornographic'],
            'order[updatedAt]' => 'asc',
        ]);

        // For debugging
        // $this->info($url);

        if (!$response->ok()) {
            // log response data
            Log::debug('Failed to fetch data from Mangadex', [
                'response' => $response->json(),
            ]);
            $this->error('Http code not ok');
            return;
        }

        $data = $response->json();

        if ($data['result'] !== 'ok') {
            $this->error('Returned result code is not OK');
            return;
        }

        if (empty($data['data'])) {
            $this->error('No data');
            return;
        }

        $series = ($data['data']);

        foreach ($series as $mdSeries) {
            $seriesUUId = $mdSeries['id'];
            $seriesTitle = $this->getMangaTitle($mdSeries);

            Series::updateOrCreate(['uuid' => $seriesUUId], [
                'uuid' => $seriesUUId,
                'title' => $seriesTitle, // nullable
                'md_updated_at' => $this->toDateTime($mdSeries['attributes']['updatedAt']),
            ]);
        }
    }

    /**
     *
     * @param mixed $manga
     * @return mixed
     */
    private function getMangaTitle($manga)
    {
        if (!$manga) {
            return "";
        }

        $altTitles = $manga['attributes']['altTitles'] ?? [];
        $title = $manga['attributes']['title'] ?? [];

        foreach ($altTitles as $altTitle) {
            if (isset($altTitle['vi'])) {
                return $altTitle['vi'];
            }
        }

        if (isset($title['en'])) {
            return $title['en'];
        }

        $firstTitle = reset($title);
        return $firstTitle ?: "No title";
    }

    /**
     *
     * @param mixed $dateString
     * @return Carbon
     */
    private function toDateTime($dateString)
    {
        return Carbon::parse($dateString);
    }
}
