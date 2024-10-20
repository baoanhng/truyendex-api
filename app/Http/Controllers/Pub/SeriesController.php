<?php

namespace App\Http\Controllers\Pub;

use App\Http\Controllers\Controller;
use App\Http\Requests\FollowSeriesRequest;
use App\Services\ReadListService;
use Illuminate\Http\Request;

class SeriesController extends Controller
{
    public function follow(FollowSeriesRequest $request)
    {
        $validated = $request->validated();

        $followed = ReadListService::createOrDelete($validated['series_id'], $request->user()->id);

        return response()->json([
            'message' => $followed ? 'Series is followed!' : 'Series is unfollowed!',
        ]);
    }

    public function checkFollow(FollowSeriesRequest $request)
    {
        $validated = $request->validated();

        $read = ReadListService::getFollow($validated['series_id'], $request->user()->id);

        return response()->json([
            'followed' => $read ? true : false,
        ]);
    }
}
