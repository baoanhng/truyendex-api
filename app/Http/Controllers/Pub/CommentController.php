<?php

namespace App\Http\Controllers\Pub;

use App\Http\Controllers\Controller;
use App\Http\Requests\CommentStoreRequest;
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
        $validated = $request->validated();

        $type = CommentService::resolveType($validated['type']);

        $comment = Comment::create([
            'commentable_type' => $type,
            'commentable_id' => $validated['type_id'],
            'content' => Purifier::clean($validated['content']),
        ]);

        return response()->json([
            'comment' => $comment,
        ]);
    }
}
