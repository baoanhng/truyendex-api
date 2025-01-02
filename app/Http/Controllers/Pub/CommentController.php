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

        $validated = $request->validated();

        $type = CommentService::resolveType($validated['type']);

        $comment = Comment::create([
            'user_id' => $request->user()->id,
            'parent_id' => $validated['parent_id'],
            'commentable_type' => $type,
            'commentable_id' => $validated['type_id'],
            'content' => Purifier::clean($validated['content']),
        ]);

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

        $comment = Comment::first('id', $validated['id']);

        if (!$request->user()->can('update', $comment)) {
            abort(403);
        }

        $comment->update([
                'content' => Purifier::clean($validated['content']),
            ]);

        return response()->json([
            'comment' => $comment,
        ]);
    }
}
