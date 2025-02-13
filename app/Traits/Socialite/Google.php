<?php

namespace App\Traits\Socialite;

use App\Enums\RolesEnum;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Socialite;

trait Google
{
    /**
     *
     * @return true
     */
    public function googleCallback()
    {
        $socUser = Socialite::driver('google')->user();

        if (!$socUser || !$socUser->email) {
            abort(400);
        }

        $user = User::where('email', $socUser->email)
            // ->where('socialite_providers', '!=', '[]')
            ->first();

        if (!$user) {
            $newUser = User::create([
                'name' => $socUser->name . ' ' . \Str::random(5),
                'email' => $socUser->email,
                'password' => \Hash::make(\Str::random(16)),
                'socialite_providers' => ['google'],
                'email_verified_at' => now(),
            ]);

            $newUser->assignRole(RolesEnum::MEMBER);

            event(new Registered($user));

            \Auth::login($newUser, remember: true);
        } else {
            $user->socialite_providers = array_unique(array_merge($user->socialite_providers, ['google']));
            $user->save();

            \Auth::login($user, remember: true);
        }

        return true;
    }
}
