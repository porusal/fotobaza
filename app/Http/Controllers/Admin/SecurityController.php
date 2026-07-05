<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\AdminSession;
use App\Support\SiteViewData;
use App\Support\TwoFactorAuthenticator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class SecurityController extends Controller
{
    public function show(Request $request): View
    {
        $user = $this->currentUser($request);
        $setupSecret = AdminSession::twoFactorSetupSecret($request);
        $setupCodes = AdminSession::twoFactorSetupCodes($request);

        if (! $setupSecret && $user?->hasPendingTwoFactorSetup()) {
            $setupSecret = $user->two_factor_secret;
        }

        return view('admin.security', array_merge(SiteViewData::common(), [
            'adminUser' => $user,
            'twoFactorEnabled' => $user?->hasTwoFactorEnabled() ?? false,
            'twoFactorPending' => $user?->hasPendingTwoFactorSetup() ?? false,
            'twoFactorSetupSecret' => $setupSecret,
            'twoFactorSetupCodes' => $setupCodes,
            'twoFactorQrCodeSvg' => $setupSecret && $user
                ? $this->qrCodeSvg($user->email, $setupSecret)
                : null,
        ]));
    }

    public function setup(Request $request): RedirectResponse
    {
        $user = $this->currentUser($request);
        $twoFactor = new TwoFactorAuthenticator();

        if ($user->hasTwoFactorEnabled()) {
            return redirect()
                ->route('admin.security.show')
                ->with('status', 'Двухфакторная аутентификация уже включена.');
        }

        $secret = $twoFactor->generateSecretKey();
        $recoveryCodes = $twoFactor->generateRecoveryCodes();

        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => null,
            'two_factor_recovery_codes' => null,
        ])->save();

        AdminSession::putTwoFactorSetup($request, $secret, $recoveryCodes);
        $request->session()->forget('recovery_codes');

        return redirect()
            ->route('admin.security.show')
            ->with('status', 'Сканируйте QR-код в Google Authenticator и подтвердите код ниже.');
    }

    public function confirm(Request $request): RedirectResponse
    {
        $user = $this->currentUser($request);

        if (! $user->two_factor_secret) {
            return redirect()
                ->route('admin.security.show')
                ->with('status', 'Сначала создайте секрет 2FA.');
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:64'],
        ]);

        $code = preg_replace('/[\s-]+/', '', $validated['code']) ?? '';
        $twoFactor = new TwoFactorAuthenticator();

        if (! $twoFactor->verifyCode($user->two_factor_secret, $code)) {
            throw ValidationException::withMessages([
                'code' => 'Код из приложения не подошёл.',
            ]);
        }

        $plainRecoveryCodes = AdminSession::twoFactorSetupCodes($request);

        if (! count($plainRecoveryCodes)) {
            $plainRecoveryCodes = $twoFactor->generateRecoveryCodes();
        }

        $user->forceFill([
            'two_factor_confirmed_at' => Carbon::now(),
            'two_factor_recovery_codes' => $twoFactor->hashRecoveryCodes($plainRecoveryCodes),
        ])->save();

        AdminSession::clearTwoFactorSetup($request);

        return redirect()
            ->route('admin.security.show')
            ->with('status', 'Двухфакторная аутентификация включена.')
            ->with('recovery_codes', $plainRecoveryCodes);
    }

    public function regenerateRecoveryCodes(Request $request): RedirectResponse
    {
        $user = $this->currentUser($request);

        if (! $user->hasTwoFactorEnabled()) {
            return redirect()
                ->route('admin.security.show')
                ->with('status', 'Сначала включите 2FA.');
        }

        $twoFactor = new TwoFactorAuthenticator();
        $plainRecoveryCodes = $twoFactor->generateRecoveryCodes();

        $user->forceFill([
            'two_factor_recovery_codes' => $twoFactor->hashRecoveryCodes($plainRecoveryCodes),
        ])->save();

        return redirect()
            ->route('admin.security.show')
            ->with('status', 'Recovery codes обновлены.')
            ->with('recovery_codes', $plainRecoveryCodes);
    }

    public function disable(Request $request): RedirectResponse
    {
        $user = $this->currentUser($request);

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
            'two_factor_recovery_codes' => null,
        ])->save();

        AdminSession::clearTwoFactorSetup($request);
        $request->session()->forget('recovery_codes');

        return redirect()
            ->route('admin.security.show')
            ->with('status', 'Двухфакторная аутентификация отключена.');
    }

    private function currentUser(Request $request): User
    {
        $user = AdminSession::currentUser($request);

        abort_unless($user instanceof User, 403);

        return $user;
    }

    private function qrCodeSvg(string $email, string $secret): string
    {
        $uri = (new TwoFactorAuthenticator())->otpauthUri($email, $secret);

        return QrCode::format('svg')
            ->margin(1)
            ->size(210)
            ->generate($uri);
    }
}
