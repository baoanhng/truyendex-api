<?php

namespace App\Http\Controllers\Pub;

use App\Http\Controllers\Controller;
use App\Models\ReadList;
use App\Models\User;
use App\Services\ReadListService;
use Http;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;

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

    /**
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = $request->user();

        if (!\Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect',
            ], 400);
        }

        if ($validated['current_password'] === $validated['password']) {
            return response()->json([
                'message' => 'New password must be different from current password',
            ], 400);
        }

        $user->password = \Hash::make($validated['password']);
        $user->save();

        \Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'changed' => true,
        ]);
    }

    public function changeName(Request $request)
    {
        $validated = $request->validate([
            'password' => ['required', 'current_password', 'string'],
            'name' => ['required', 'string', 'min:6', 'max:25', 'unique:'.User::class],
        ]);

        $result = \DB::transaction(function () use ($request, $validated) {
            $user = $request->user();
            $user->name = $validated['name'];
            $user->save();

            activity()
                ->performedOn($user)
                ->causedBy($user)
                ->log('Change name');

            return true;
        });

        return response()->json([
            'changed' => $result,
        ]);
    }
}
