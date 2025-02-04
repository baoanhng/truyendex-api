<?php

namespace App\Http\Controllers\Auth;

use App\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Socialite;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): Response
    {
        $request->authenticate();

        $request->session()->regenerate();

        return response()->noContent();
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): Response
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->noContent();
    }

    /**
     *
     * @param string $provider
     * @return Response
     */
    public function socialiteRedirect(string $provider): Response
    {
        Helper::verifySocialiteProvider($provider);

        return Socialite::driver($provider)->redirect();
    }

    /**
     *
     * @param string $provider
     * @return Response
     */
    public function socialiteCallback(string $provider): Response
    {
        Helper::verifySocialiteProvider($provider);

        $socUser = Socialite::driver($provider)->user();

        if (!$socUser || !$socUser->email) {
            abort(400);
        }

        $user = User::where('email', $socUser->email)->first();

        return response()->noContent();
    }
}
