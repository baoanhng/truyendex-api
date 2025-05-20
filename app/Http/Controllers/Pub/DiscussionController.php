<?php

namespace App\Http\Controllers\Pub;

use App\Http\Controllers\Controller;
use App\Http\Requests\DiscussionStoreRequest;
use App\Models\Discussion;
use Illuminate\Http\Request;

class DiscussionController extends Controller
{
    public function list(Request $request)
    {
        $discussions = Discussion::latest('updated_at')->paginate(10);

        return response()->json([
            'discussions' => $discussions,
        ]);
    }

    public function show(int $id, Request $request)
    {
        $discussion = Discussion::findOrFail($id);

        return response()->json([
            'discussion' => $discussion,
        ]);
    }

    public function store(DiscussionStoreRequest $request)
    {
        if ($request->user()->can('create discussions')) {
            abort(403);
        }

        $validated = $request->validated();

        $discussion = new Discussion();
        $discussion->uuid = \Str::uuid();
        $discussion->user_id = $request->user()->id();
        $discussion->title = \Purifier::clean($validated['title']);
        $discussion->slug = \Str::slug($validated['title']);
        $discussion->content = \Purifier::clean($validated['content']);
        $discussion->save();

        return response()->json([
            'status' => 'success',
            'discussion' => $discussion,
        ]);
    }
}
