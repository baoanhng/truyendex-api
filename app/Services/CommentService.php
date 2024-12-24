<?php

namespace App\Services;

use App\Models\Comment;
use Illuminate\Http\Request;

class CommentService
{
    /**
     *
     * @param string $type
     * @return string
     */
    public static function resolveType(string $type)
    {
        return match ($type) {
            'page' => 'App\Models\Page',
            'chapter' => 'App\Models\Chapter',
            'series' => 'App\Models\Series',
            default => 'App\Models\Series',
        };
    }

    /**
     *
     * @param Request $request
     * @return mixed
     */
    public static function list(Request $request)
    {
        $type = self::resolveType($request->type);

        return Comment::where('commentable_type', $type)
            ->where('commentable_id', $request->type_id)
            ->with('user')->latest('id')
            ->paginate(20);
    }
}
