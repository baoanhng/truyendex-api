<?php

namespace App\Services;

use App\Http\Requests\CommentStoreRequest;
use App\Models\Chapter;
use App\Models\Comment;
use App\Models\Page;
use App\Models\Series;
use Illuminate\Database\Eloquent\Collection;
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
            'page' => Page::class,
            'chapter' => Chapter::class,
            'series' => Series::class,
            default => Series::class,
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
        $commentable = $type::where('uuid', $request->type_id)->first();

        return Comment::where('commentable_type', $type)
            ->where('commentable_id', $commentable->id)->where('parent_id', 0)
            ->with(['user', 'replies' => fn($query) => $query->limit(2), 'replies.user'])
            ->latest('id')
            ->paginate(15);
    }

    /**
     *
     * @return Collection<int, Comment>
     */
    public static function recent($limit = 10)
    {
        return Comment::with(['user', 'commentable'])
            ->latest('id')
            ->limit($limit)
            ->get();
    }

    /**
     *
     * @param Comment $tailComment
     * @param int $limit
     * @return void
     */
    public static function fetchReply(Comment $tailComment, int $limit)
    {
        return Comment::with('user')->where('parent_id', $tailComment->parent_id)
            ->where('id', '>', $tailComment->id)
            ->oldest('id')->limit($limit)
            ->get();
    }

    /**
     *
     * @param CommentStoreRequest $request
     * @return mixed
     */
    public static function store(CommentStoreRequest $request)
    {
        $validated = $request->validated();

        $type = self::resolveType($validated['type']);

        $result = \DB::transaction(function () use ($validated, $type, $request) {
            $commentable = $type::where('uuid', $validated['type_id'])->first();

            if ($validated['parent_id'] > 0) {
                $parent = Comment::find($validated['parent_id']);

                if ($parent->parent_id > 0) {
                    return false;
                }
            }

            $comment = Comment::create([
                'user_id' => $request->user()->id,
                'parent_id' => $validated['parent_id'],
                'commentable_type' => $type,
                'commentable_id' => $commentable->id,
                'content' => Purifier::clean($validated['content']),
            ]);

            $comment->user->timestamps = false;
            $comment->user->comment_count += 1;
            $comment->user->save();

            if (isset($parent)) {
                $parent->timestamps = false;
                $parent->reply_count += 1;
                $parent->save();
            }

            $commentable->timestamps = false;
            $commentable->comment_count += 1;
            $commentable->save();

            if ($commentable instanceof Chapter) {
                $commentable->series->timestamps = false;
                $commentable->series->comment_count += 1;
                $commentable->series->save();
            }

            return $comment;
        });

        return $result;
    }

    /**
     *
     * @param array $validated
     * @param Comment $comment
     * @return true
     */
    public static function update(array $validated, Comment $comment)
    {
        $updated = $comment->update([
            'content' => Purifier::clean($validated['content']),
        ]);

        return $updated;
    }

    /**
     *
     * @param Comment $comment
     * @return mixed
     */
    public static function delete(Comment $comment)
    {
        $result = \DB::transaction(function () use ($comment) {
            $comment->user->timestamps = false;
            $comment->user->comment_count -= 1;
            $comment->user->save();

            $commentable = $comment->commentable;

            $commentable->timestamps = false;
            $commentable->comment_count -= 1;
            $commentable->save();

            if ($commentable instanceof Chapter) {
                $commentable->series->timestamps = false;
                $commentable->series->comment_count -= 1;
                $commentable->series->save();
            }

            $comment->delete();

            return true;
        });

        return $result;
    }
}
