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

        $followed = ReadListService::createOrDelete($validated);

        return response()->json([
            'message' => $followed ? 'Series is followed!' : 'Series is unfollowed!',
        ]);
    }
}
