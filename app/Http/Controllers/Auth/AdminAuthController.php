<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\AdminSession;
use App\Support\SiteViewData;
use App\Support\TwoFactorAuthenticator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminAuthController extends Controller
{
    public function showLogin(Request $request): View|RedirectResponse
    {
        if (AdminSession::currentUser($request)) {
            return redirect()->route('admin.dashboard');
        }

        if (AdminSession::pendingUser($request)) {
            return redirect()->route('admin.2fa.challenge');
        }

        return view('auth.admin-login', SiteViewData::common());
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user = User::query()
            ->where('email', $credentials['email'])
            ->where('is_admin', true)
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'Неверный email или пароль.',
            ]);
        }

        if ($user->hasTwoFactorEnabled()) {
            AdminSession::startTwoFactorChallenge($user, $request);

            return redirect()->route('admin.2fa.challenge');
        }

        AdminSession::login($user, $request);

        return redirect()->to(AdminSession::intendedUrl($request));
    }

    public function showChallenge(Request $request): View|RedirectResponse
    {
        if (AdminSession::currentUser($request)) {
            return redirect()->route('admin.dashboard');
        }

        $pendingUser = AdminSession::pendingUser($request);

        if (! $pendingUser) {
            return redirect()->route('admin.login');
        }

        return view('auth.two-factor-challenge', array_merge(SiteViewData::common(), [
            'pendingUser' => $pendingUser,
        ]));
    }

    public function confirmChallenge(Request $request): RedirectResponse
    {
        $pendingUser = AdminSession::pendingUser($request);

        if (! $pendingUser) {
            return redirect()->route('admin.login');
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:64'],
        ]);

        $code = preg_replace('/[\s-]+/', '', $validated['code']) ?? '';
        $twoFactor = new TwoFactorAuthenticator();

        $recoveryCodes = (array) ($pendingUser->two_factor_recovery_codes ?? []);
        $recoveryIndex = $twoFactor->verifyRecoveryCode($code, $recoveryCodes);

        if ($recoveryIndex !== null) {
            unset($recoveryCodes[$recoveryIndex]);
            $pendingUser->forceFill([
                'two_factor_recovery_codes' => array_values($recoveryCodes),
            ])->save();

            AdminSession::login($pendingUser, $request);

            return redirect()->to(AdminSession::intendedUrl($request));
        }

        if ($pendingUser->two_factor_secret && $twoFactor->verifyCode($pendingUser->two_factor_secret, $code)) {
            AdminSession::login($pendingUser, $request);

            return redirect()->to(AdminSession::intendedUrl($request));
        }

        throw ValidationException::withMessages([
            'code' => 'Код не подошёл. Проверьте цифры или recovery code.',
        ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        AdminSession::logout($request);

        return redirect()->route('admin.login');
    }
}
