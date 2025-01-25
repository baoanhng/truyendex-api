<?php

namespace App\Http\Controllers\Pub;

use App\Http\Controllers\Controller;
use App\Http\Requests\FollowSeriesRequest;
use App\Models\ReadList;
use App\Models\Series;
use App\Services\ReadListService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SeriesController extends Controller
{
    /**
     *
     * @param FollowSeriesRequest $request
     * @return JsonResponse
     */
    public function follow(FollowSeriesRequest $request)
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
     * @param FollowSeriesRequest $request
     * @return JsonResponse
     */
    public function checkFollow(FollowSeriesRequest $request)
    {
        $validated = $request->validated();

        $read = ReadListService::getFollow($validated['series_uuid'], $request->user()->id);

        return response()->json([
            'followed' => $read ? true : false,
        ]);
    }
}
