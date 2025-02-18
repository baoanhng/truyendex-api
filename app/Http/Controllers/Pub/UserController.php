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
use Spatie\Activitylog\Models\Activity;

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
                'message' => 'Đã có lỗi xảy ra khi lấy thông tin đồng bộ',
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
                'message' => 'Mật khẩu cũ không chính xác',
            ], 400);
        }

        if ($validated['current_password'] === $validated['password']) {
            return response()->json([
                'message' => 'Mật khẩu mới phải khác mật khẩu cũ',
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

    /**
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function changeName(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:6', 'max:25', 'unique:' . User::class],
        ]);

        $lastChange = Activity::causedBy($request->user())
            ->where('description', 'Change name')->latest('id')
            ->first();

        if ($lastChange && ($lastChange->created_at->diffInDays() < 60)) {
            return response()->json([
                'message' => 'Bạn chỉ có thể đổi tên mỗi 60 ngày',
            ], 400);
        }

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

    /**
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function changeAvatar(Request $request)
    {
        $validated = $request->validate([
            'avatar' => ['required', 'image', 'max:1024'],
        ]);

        try {
            $user = $request->user();

            if ($user->avatar_path) {
                \Storage::delete($user->avatar_path);
            }

            $path = $validated['avatar']->store(app()->environment() === 'production' ? 'avatars' : 'temp');

            $user->avatar_path = $path;
            $user->save();

            return response()->json([
                'avatar_url' => \Storage::url($path),
            ]);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());

            return response()->json([
                'message' => 'Đã có lỗi xảy ra khi cập nhật ảnh đại diện',
            ], 500);
        }
    }
}
