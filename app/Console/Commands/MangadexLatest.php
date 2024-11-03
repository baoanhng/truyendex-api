<?php

namespace App\Console\Commands;

use App\Models\Series;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

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
        $response = Http::get('https://api.mangadex.org/chapter?limit=10&translatedLanguage%5B%5D=vi&contentRating%5B%5D=safe&contentRating%5B%5D=suggestive&contentRating%5B%5D=erotica&includeFutureUpdates=0&includeFuturePublishAt=0&includeExternalUrl=0&order%5BreadableAt%5D=desc&includes%5B%5D=manga');

        if (!$response->ok()) {
            $this->error('Http code not ok');
            return;
        }

        $data = $response->json();

        if ($data['result'] !== 'ok') {
            $this->error('Returned result code is not OK');
            return;
        }

        $chapters = array_reverse($data['data']);

        foreach ($chapters as $chapter) {
            $seriesId = $chapter['relationships'][1]['id'];
            $seriesTitle = $this->getSeriesTitle($chapter['relationships'][1]);

            Series::updateOrCreate(['uuid' => $seriesId], [
                'uuid' => $seriesId,
                'title' => $seriesTitle, // nullable
                'latest_chapter_uuid' => $chapter['id'],
                'latest_chapter_title' => $chapter['attributes']['title'], // nullable
            ]);
        }
    }

    /**
     *
     * @param array $seriesData
     * @return string
     */
    private function getSeriesTitle(array $seriesData)
    {
        $title = $seriesData['attributes']['title']['en'];

        if (isset($seriesData['attributes']['altTitles']['vi'])) {
            $title = $seriesData['attributes']['altTitles']['vi'];
        }

        return $title;
    }
}
