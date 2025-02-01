<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'cf-turnstile-response' => ['required', Rule::turnstile()],
        ]);

        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended('/');
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json(['status' => 'verification-link-sent']);
    }
}
