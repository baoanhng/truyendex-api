<?php

namespace App\Http\Controllers\Pub;

use App\Http\Controllers\Controller;
use App\Http\Requests\CommentStoreRequest;
use App\Http\Requests\CommentUpdateRequest;
use App\Models\Comment;
use App\Services\CommentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
     * @param CommentStoreRequest $request
     * @return JsonResponse
     */
    public function store(CommentStoreRequest $request)
    {
        if (!$request->user()->can('create', Comment::class)) {
            abort(403);
        }

        $comment = CommentService::store($request);

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
     * @return void
     */
    public function delete(Request $request)
    {
        $validated = $request->validated([
            'id' => 'required', 'exists:comments,id',
        ]);

        $comment = Comment::find($validated['id']);

        if (!$request->user()->can('delete', $comment)) {
            abort(403);
        }

        \DB::transaction(function () use ($comment) {
            $comment->user->comment_count -= 1;
            $comment->user->save();

            $comment->series->comment_count -= 1;
            $comment->series->save();

            $comment->delete();
        });

        return response()->json([
            'status' => 'comment-deleted',
        ]);
    }
}
