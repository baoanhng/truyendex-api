<?php

namespace App\Services;

use App\Models\ReadList;
use App\Models\User;
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

    /**
     *
     * @param User $user
     * @return mixed
     */
    public static function userList(User $user)
    {
        $list = ReadList::leftJoin('series', 'read_lists.series_uuid', '=', 'series.uuid')
            ->select(
                'read_lists.*',
                'series.title',
                'series.latest_chapter_uuid',
                'series.latest_chapter_title',
                'series.updated_at as series_updated_at'
            )
            ->where('read_lists.user_id', $user->id)
            ->orderByRaw('series_updated_at IS NULL, series_updated_at DESC, read_lists.updated_at DESC')
            ->paginate(20);

        return $list;
    }
}
