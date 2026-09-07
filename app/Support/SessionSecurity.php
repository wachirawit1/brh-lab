<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class SessionSecurity
{
    public static function expired(mixed $lastActivity): bool
    {
        if (! $lastActivity) {
            return true;
        }

        try {
            $last = Carbon::parse($lastActivity);
            return $last->isFuture() || $last->diffInSeconds(now()) >= 60 * max(1, (int) config('session.idle_timeout', 60));
        } catch (\Throwable) {
            return true;
        }
    }

    public static function invalidate(Request $request): void
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
