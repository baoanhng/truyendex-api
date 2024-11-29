<?php

namespace App\Services;

use App\Models\ReadList;
use Illuminate\Http\Request;

class ReadListService
{
    /**
     *
     * @param string $seriesUUID
     * @param int $userId
     * @return bool
     */
    public static function createOrDelete(string $seriesUUId, int $userId)
    {
        $read = self::getFollow($seriesUUId, $userId);

        if ($read) {
            $read->delete();

            return false;
        } else {
            ReadList::create([
                'user_id' => $userId,
                'series_uuid' => $seriesUUId,
            ]);

            return true;
        }
    }

    /**
     *
     * @param string $seriesUUId
     * @param int $userId
     * @return ReadList|null
     */
    public static function getFollow(string $seriesUUId, int $userId)
    {
        $read = ReadList::where('user_id', $userId)
            ->where('series_uuid', $seriesUUId)
            ->first();

        return $read;
    }
}
