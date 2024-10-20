<?php

namespace App\Services;

use App\Models\ReadList;
use Illuminate\Http\Request;

class ReadListService
{
    public static function createOrDelete(string $seriesId, int $userId)
    {
        $read = self::getFollow($seriesId, $userId);

        if ($read) {
            $read->delete();

            return false;
        } else {
            ReadList::create([
                'user_id' => $userId,
                'series_id' => $seriesId,
            ]);

            return true;
        }
    }

    public static function getFollow(string $seriesId, int $userId)
    {
        $read = ReadList::where('user_id', $userId)
            ->where('series_id', $seriesId)
            ->first();

        return $read;
    }
}
