<?php

namespace App\Http\Controllers\Pub;

use App\Http\Controllers\Controller;
use App\Models\ReadList;
use App\Services\ReadListService;
use Http;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function info(Request $request)
    {
        $user = $request->user();
        $user->makeVisible(['email', 'email_verified_at']);

        return response()->json([
            'user' => $user,
        ]);
    }

    /**
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function readList(Request $request)
    {
        $list = ReadListService::userList($request->user());

        return $list;
    }

    /**
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function syncReadList(Request $request)
    {
        $validated = $request->validate([
            'source' => ['required', 'string'],
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'string'],
        ]);

        $response = Http::get('https://directus.truyendex.xyz/directus-endpoint-truyendex/map', [
            'source' => $validated['source'],
            'ids' => implode(',', $validated['ids']),
        ]);

        if ($response->failed()) {
            return response()->json([
                'message' => 'Failed to sync list',
            ], 500);
        }

        $data = $response->json(); // {"result":{"2101":["fee73e75-075d-423a-b597-5d96cd9cb554"],"2147":["ab541412-f0d0-4fcf-a7a3-084a835d9d4a"]}}

        $uuids = collect($data['result'])->flatten();

        $inserts = $uuids->map(function ($uuid) use ($request) {
            return ['user_id' => $request->user()->id, 'series_uuid' => $uuid];
        });

        $result = ReadList::upsert($inserts->toArray(), ['user_id', 'series_uuid']);

        return response()->json([
            'followed' => $result,
        ]);
    }
}
