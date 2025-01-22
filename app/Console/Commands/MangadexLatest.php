<?php

namespace App\Console\Commands;

use App\Models\Series;
use App\Models\Chapter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use GuzzleHttp\TransferStats;

class MangadexLatest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:mangadex-latest';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update or Create Series based on latest chapters on Mangadex';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $latestChapter = Chapter::latest('md_updated_at')->first();

        $lastestChapterUpdatedAt = $latestChapter?->md_updated_at ?? new \DateTime("2018-01-01");
        $lastestChapterUpdatedAt = $lastestChapterUpdatedAt->format('Y-m-d\TH:i:s');

        Log::debug($lastestChapterUpdatedAt);

        $response = Http::withOptions([
            'on_stats' => function (TransferStats $stats) use (&$url) {
                $url = $stats->getEffectiveUri();
            }
        ])->get('https://api.mangadex.org/chapter', [
            'updatedAtSince' => $lastestChapterUpdatedAt,
            'offset' => 0,
            'limit' => 10,
            'translatedLanguage' => ['vi'],
            'contentRating' => ['safe', 'suggestive', 'erotica', 'pornographic'],
            'includeFutureUpdates' => 0,
            'includeFuturePublishAt' => 0,
            'includeExternalUrl' => 0,
            'order[updatedAt]' => 'asc',
            'includes[]' => 'manga',
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

        $chapters = ($data['data']);

        foreach ($chapters as $mdChapter) {
            // find $mdChapter['relationships'] with type 'manga'
            $mdSeries = array_values(array_filter($mdChapter['relationships'], function ($relationship) {
                return $relationship['type'] === 'manga';
            }))[0];
            $seriesUUId = $mdSeries['id'];
            $seriesTitle = $this->getMangaTitle($mdSeries);

            \DB::transaction(function () use ($mdChapter, $mdSeries, $seriesUUId, $seriesTitle) {
                // get $chapter from following code
                Chapter::updateOrCreate(['uuid' => $mdChapter['id']], [
                    'uuid' => $mdChapter['id'],
                    'title' => $this->getChapterTitle($mdChapter),
                    'md_updated_at' => $this->toDateTime($mdChapter['attributes']['updatedAt']),
                    'series_uuid' => $seriesUUId,
                ]);

                Series::updateOrCreate(['uuid' => $seriesUUId], [
                    'uuid' => $seriesUUId,
                    'title' => $seriesTitle, // nullable
                    'last_chapter_updated_at' => $this->toDateTime($mdChapter['attributes']['updatedAt']),
                    'md_updated_at' => $this->toDateTime($mdSeries['attributes']['updatedAt']),
                    'latest_chapter_uuid' => $mdChapter['id'],
                ]);
            });
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
     * @param mixed $chapter
     * @return string
     */
    private function getChapterTitle($chapter)
    {
        if (!$chapter) {
            return "";
        }

        $attributes = $chapter['attributes'] ?? [];
        $title = $attributes['title'] ?? null;
        $volume = $attributes['volume'] ?? null;
        $chapterNumber = $attributes['chapter'] ?? null;

        if ($title) {
            return ($volume !== null ? "T{$volume} " : "") .
                ($chapterNumber !== null ? "C{$chapterNumber} " : "") .
                $title;
        }

        if ($volume) {
            return "Chương {$chapterNumber} Tập {$volume}";
        }

        if ($chapterNumber) {
            return "Chương {$chapterNumber}";
        }

        return "Oneshot";
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
