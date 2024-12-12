<?php

namespace App\Http\Controllers\Pub;

use App\Http\Controllers\Controller;
use App\Http\Requests\CommentStoreRequest;
use App\Models\Comment;
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
        $comments = Comment::where('commentable_type', $request->type)
            ->where('commentable_id', $request->type_id)
            ->with('user')->latest('id')
            ->paginate(20);

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

        $comment = Comment::create([
            'commentable_type' => $validated['type'],
            'commentable_id' => $validated['type_id'],
            'content' => Purifier::clean($validated['content']),
        ]);

        return response()->json([
            'comment' => $comment,
        ]);
    }
}
