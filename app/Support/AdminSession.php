<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;

class AdminSession
{
    private const AUTH_USER_KEY = 'admin.user_id';
    private const PENDING_USER_KEY = 'admin.pending_user_id';
    private const INTENDED_URL_KEY = 'admin.intended';
    private const TWO_FACTOR_SETUP_SECRET_KEY = 'admin.two_factor.setup_secret';
    private const TWO_FACTOR_SETUP_CODES_KEY = 'admin.two_factor.setup_codes';

    public static function currentUser(?Request $request = null): ?User
    {
        if (app()->runningInConsole()) {
            return null;
        }

        $request ??= request();

        if (! $request instanceof Request || ! $request->hasSession()) {
            return null;
        }

        $userId = $request->session()->get(self::AUTH_USER_KEY);

        if (! $userId) {
            return null;
        }

        return User::query()
            ->whereKey($userId)
            ->where('is_admin', true)
            ->first();
    }

    public static function pendingUser(?Request $request = null): ?User
    {
        if (app()->runningInConsole()) {
            return null;
        }

        $request ??= request();

        if (! $request instanceof Request || ! $request->hasSession()) {
            return null;
        }

        $userId = $request->session()->get(self::PENDING_USER_KEY);

        if (! $userId) {
            return null;
        }

        return User::query()
            ->whereKey($userId)
            ->where('is_admin', true)
            ->first();
    }

    public static function login(User $user, Request $request): void
    {
        $request->session()->regenerate();
        $request->session()->put(self::AUTH_USER_KEY, $user->id);
        $request->session()->forget([
            self::PENDING_USER_KEY,
            self::TWO_FACTOR_SETUP_SECRET_KEY,
            self::TWO_FACTOR_SETUP_CODES_KEY,
        ]);
    }

    public static function startTwoFactorChallenge(User $user, Request $request): void
    {
        $request->session()->regenerate();
        $request->session()->put(self::PENDING_USER_KEY, $user->id);
        $request->session()->forget(self::AUTH_USER_KEY);
    }

    public static function storeIntendedUrl(Request $request, ?string $url = null): void
    {
        $request->session()->put(self::INTENDED_URL_KEY, $url ?? $request->fullUrl());
    }

    public static function intendedUrl(Request $request, string $fallbackRoute = 'admin.dashboard'): string
    {
        return (string) $request->session()->pull(self::INTENDED_URL_KEY, route($fallbackRoute));
    }

    public static function putTwoFactorSetup(Request $request, string $secret, array $recoveryCodes): void
    {
        $request->session()->put(self::TWO_FACTOR_SETUP_SECRET_KEY, $secret);
        $request->session()->put(self::TWO_FACTOR_SETUP_CODES_KEY, array_values($recoveryCodes));
    }

    public static function twoFactorSetupSecret(?Request $request = null): ?string
    {
        if (app()->runningInConsole()) {
            return null;
        }

        $request ??= request();

        if (! $request instanceof Request || ! $request->hasSession()) {
            return null;
        }

        return $request->session()->get(self::TWO_FACTOR_SETUP_SECRET_KEY);
    }

    public static function twoFactorSetupCodes(?Request $request = null): array
    {
        if (app()->runningInConsole()) {
            return [];
        }

        $request ??= request();

        if (! $request instanceof Request || ! $request->hasSession()) {
            return [];
        }

        return (array) $request->session()->get(self::TWO_FACTOR_SETUP_CODES_KEY, []);
    }

    public static function clearTwoFactorSetup(Request $request): void
    {
        $request->session()->forget([
            self::TWO_FACTOR_SETUP_SECRET_KEY,
            self::TWO_FACTOR_SETUP_CODES_KEY,
        ]);
    }

    public static function clearAuth(Request $request): void
    {
        $request->session()->forget([
            self::AUTH_USER_KEY,
            self::PENDING_USER_KEY,
            self::INTENDED_URL_KEY,
            self::TWO_FACTOR_SETUP_SECRET_KEY,
            self::TWO_FACTOR_SETUP_CODES_KEY,
        ]);
    }

    public static function logout(Request $request): void
    {
        self::clearAuth($request);
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
