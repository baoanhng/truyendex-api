<?php

namespace App\Services;

use App\Models\ReadList;
use Illuminate\Http\Request;

class ReadListService
{
    public static function createOrDelete(Request $request)
    {
        $read = ReadList::where('user_id', $request->user()->id)
                        ->where('series_id', $request['series_id'])
                        ->first();

        if ($read) {
            $read->delete();

            return false;
        } else {
            ReadList::create([
                'user_id' => $request->user()->id,
                'series_id' => $request['series_id'],
            ]);

            return true;
        }
    }
}
