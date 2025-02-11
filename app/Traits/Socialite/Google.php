<?php

namespace App\Traits\Socialite;

use App\Models\User;
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

        $user = User::where('email', $socUser->email)->first();

        if (!$user) {
            $newUser = User::create([
                'name' => $socUser->name . ' ' . \Str::random(5),
                'email' => $socUser->email,
                'password' => \Hash::make(\Str::random(16)),
                'socialite_providers' => ['google'],
            ]);

            \Auth::login($newUser, remember: true);
        } else {
            \Auth::login($user, remember: true);
        }

        return true;
    }
}
