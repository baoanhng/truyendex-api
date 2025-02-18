<?php

namespace App\Http\Controllers\Pub;

use App\Http\Controllers\Controller;
use App\Http\Requests\CommentStoreRequest;
use App\Http\Requests\CommentUpdateRequest;
use App\Models\Comment;
use App\Services\CommentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Mews\Purifier\Facades\Purifier;

class CommentController extends Controller
{
    /**
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request)
    {
        $comments = CommentService::list($request);

        return response()->json([
            'comments' => $comments,
        ]);
    }

    /**
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function recent(Request $request)
    {
        $comments = CommentService::recent(15);

        return response()->json([
            'comments' => $comments,
        ]);
    }

    /**
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function fetchReply(Request $request)
    {
        $validated = $request->validate([
            'last_id' => ['required', 'integer', 'min:1'],
        ]);

        $tailComment = Comment::find($validated['last_id']);

        if (!$tailComment || $tailComment->parent_id == 0) {
            abort(400);
        }

        $replies = CommentService::fetchReply($tailComment, 10);

        return response()->json([
            'replies' => $replies,
        ]);
    }

    /**
     *
     * @param CommentStoreRequest $request
     * @return JsonResponse
     */
    public function store(CommentStoreRequest $request)
    {
        if (!$request->user()->can('create', Comment::class)) {
            abort(403);
        }

        if (RateLimiter::tooManyAttempts('send-comment:'.request()->user()->id, $perMinute = 5)) {
            return response()->json([
                'message' => 'Bạn đã gửi quá nhiều bình luận, vui lòng thử lại sau',
            ], 429);
        }

        $comment = CommentService::store($request);

        RateLimiter::increment('send-comment:'.request()->user()->id);

        return response()->json([
            'comment' => $comment,
        ]);
    }

    /**
     *
     * @param CommentUpdateRequest $request
     * @return JsonResponse
     */
    public function update(CommentUpdateRequest $request)
    {
        $validated = $request->validated();

        $comment = Comment::find($validated['id']);

        if (!$request->user()->can('update', $comment)) {
            abort(403);
        }

        CommentService::update($validated, $comment);

        return response()->json([
            'comment' => $comment,
        ]);
    }

    /**
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request)
    {
        $validated = $request->validate([
            'id' => ['required', 'exists:comments,id'],
        ]);

        $comment = Comment::find($validated['id']);

        if (!$request->user()->can('delete', $comment)) {
            abort(403);
        }

        $result = CommentService::delete($comment);

        return response()->json([
            'status' => $result,
        ]);
    }
}
