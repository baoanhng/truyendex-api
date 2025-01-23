<?php

namespace App\Http\Controllers\Pub;

use App\Http\Controllers\Controller;
use App\Http\Requests\FollowSeriesRequest;
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
        ]);

        foreach ($validated['series_uuids'] as $series_uuid) {
            ReadListService::createOnly($series_uuid, $request->user()->id);
        }

        return response()->json([
            'followed' => true,
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
