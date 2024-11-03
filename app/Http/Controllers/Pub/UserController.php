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
        $list = ReadList::where('user_id', $request->user()->id)
            ->with('series')->latest('id')
            ->paginate(20);

        return $list;
    }
}
