<?php

namespace App\Http\Controllers\Pub;

use App\Http\Controllers\Controller;
use App\Models\ReadList;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function readList(Request $request)
    {
        $list = ReadList::leftJoin('series', 'read_lists.series_uuid', '=', 'series.uuid')
            ->select(
                'read_lists.*',
                'series.title',
                'series.latest_chapter_uuid',
                'series.latest_chapter_title',
                'series.updated_at as series_updated_at'
            )
            ->where('read_lists.user_id', $request->user()->id)
            ->orderByRaw('series_updated_at IS NULL, series_updated_at DESC, read_lists.updated_at DESC')
            ->paginate(20);

        return $list;
    }
}
