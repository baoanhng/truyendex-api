<?php

namespace App\Services;

use App\Http\Requests\CommentStoreRequest;
use App\Http\Requests\CommentUpdateRequest;
use App\Models\Comment;
use Illuminate\Contracts\Support\ValidatedData;
use Illuminate\Http\Request;
use Purifier;

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
            ->with(['user', 'replies' => fn ($query) => $query->limit(3), 'replies.user'])
            ->latest('id')
            ->paginate(20);
    }

    /**
     *
     * @param CommentStoreRequest $request
     * @return mixed
     */
    public static function store(CommentStoreRequest $request)
    {
        $validated = $request->validated();

        $type = CommentService::resolveType($validated['type']);

        $comment = Comment::create([
            'user_id' => $request->user()->id,
            'parent_id' => $validated['parent_id'],
            'commentable_type' => $type,
            'commentable_id' => $validated['type_id'],
            'content' => Purifier::clean($validated['content']),
        ]);

        return $comment;
    }

    /**
     *
     * @param CommentUpdateRequest $request
     * @param Comment $comment
     * @return true
     */
    public static function update(CommentUpdateRequest $request, Comment $comment)
    {
        $validated = $request->validated();

        $comment->update([
            'content' => Purifier::clean($validated['content']),
        ]);

        return true;
    }
}
