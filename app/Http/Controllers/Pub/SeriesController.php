<?php

namespace App\Http\Controllers\Pub;

use App\Http\Controllers\Controller;
use App\Http\Requests\SeriesInfoRequest;
use App\Models\ReadList;
use App\Models\Series;
use App\Services\ReadListService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SeriesController extends Controller
{
    /**
     * Get series for homepage
     * @return JsonResponse
     */
    public function homepage()
    {
        $series = Series::latest('last_chapter_updated_at')
            ->with(['chapters' => fn($query) => $query->limit(3)->latest('id')])
            ->paginate(20);

        return response()->json($series);
    }

    /**
     *
     * @param SeriesInfoRequest $request
     * @return JsonResponse
     */
    public function follow(SeriesInfoRequest $request)
    {
        $validated = $request->validated();

        $followed = ReadListService::createOrDelete($validated['series_uuid'], $request->user()->id);

        return response()->json([
            'followed' => $followed,
        ]);
    }

    /**
     * Follow multiple series
     *
     * @param Request $request
     * @return void
     */
    public function follows(Request $request)
    {
        $validated = $request->validate([
            'series_uuids' => ['required', 'array'],
            'series_uuids.*' => ['required', 'uuid'],
        ]);

        $inserts = Series::where('uuid', $validated['series_uuids'])->pluck('uuid')->map(function ($uuid) use ($request) {
            return ['user_id', $request->user()->id, 'series_uuid' => $uuid];
        });

        $result = ReadList::upsert($inserts->toArray(), ['user_id', 'series_uuid']);

        return response()->json([
            'followed' => $result,
        ]);
    }

    /**
     *
     * @param SeriesInfoRequest $request
     * @return JsonResponse
     */
    public function checkInfo(SeriesInfoRequest $request)
    {
        $validated = $request->validated();

        $series = Series::where('uuid', $validated['series_uuid'])->firstOrFail();

        if (\Auth::check()) {
            $read = ReadListService::getFollow($validated['series_uuid'], $request->user()->id);
        } else {
            $read = null;
        }

        return response()->json([
            'followed' => \Auth::check() ? ($read ? true : false) : null,
            'comment_count' => $series->comment_count,
        ]);
    }
}
