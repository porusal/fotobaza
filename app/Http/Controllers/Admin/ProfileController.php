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
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $this->currentUser($request);

        return view('admin.profile', array_merge(SiteViewData::common(), [
            'adminUser' => $user,
            'twoFactor' => $this->twoFactorViewData($request, $user),
        ]));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $this->currentUser($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'current_password' => ['required', 'string'],
            'new_password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if (! Hash::check($validated['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Текущий пароль указан неверно.',
            ]);
        }

        $payload = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if (! empty($validated['new_password'])) {
            $payload['password'] = Hash::make($validated['new_password']);
        }

        $user->forceFill($payload)->save();

        $request->session()->regenerate();

        return redirect()
            ->route('admin.profile.edit')
            ->with('status', 'Профиль сохранён.');
    }

    public function setupTwoFactor(Request $request): RedirectResponse
    {
        $user = $this->currentUser($request);
        $twoFactor = new TwoFactorAuthenticator();

        if ($user->hasTwoFactorEnabled()) {
            return redirect()
                ->route('admin.profile.edit')
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
            ->route('admin.profile.edit')
            ->with('status', 'Сканируйте QR-код в Google Authenticator и подтвердите код ниже.');
    }

    public function confirmTwoFactor(Request $request): RedirectResponse
    {
        $user = $this->currentUser($request);

        if (! $user->two_factor_secret) {
            return redirect()
                ->route('admin.profile.edit')
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
            ->route('admin.profile.edit')
            ->with('status', 'Двухфакторная аутентификация включена.')
            ->with('recovery_codes', $plainRecoveryCodes);
    }

    public function regenerateTwoFactorRecoveryCodes(Request $request): RedirectResponse
    {
        $user = $this->currentUser($request);

        if (! $user->hasTwoFactorEnabled()) {
            return redirect()
                ->route('admin.profile.edit')
                ->with('status', 'Сначала включите 2FA.');
        }

        $twoFactor = new TwoFactorAuthenticator();
        $plainRecoveryCodes = $twoFactor->generateRecoveryCodes();

        $user->forceFill([
            'two_factor_recovery_codes' => $twoFactor->hashRecoveryCodes($plainRecoveryCodes),
        ])->save();

        return redirect()
            ->route('admin.profile.edit')
            ->with('status', 'Recovery codes обновлены.')
            ->with('recovery_codes', $plainRecoveryCodes);
    }

    public function disableTwoFactor(Request $request): RedirectResponse
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
            ->route('admin.profile.edit')
            ->with('status', 'Двухфакторная аутентификация отключена.');
    }

    private function currentUser(Request $request): User
    {
        $user = AdminSession::currentUser($request);

        abort_unless($user instanceof User, 403);

        return $user;
    }

    private function twoFactorViewData(Request $request, User $user): array
    {
        $setupSecret = AdminSession::twoFactorSetupSecret($request);
        $setupCodes = AdminSession::twoFactorSetupCodes($request);

        if (! $setupSecret && $user->hasPendingTwoFactorSetup()) {
            $setupSecret = $user->two_factor_secret;
        }

        return [
            'twoFactorEnabled' => $user->hasTwoFactorEnabled(),
            'twoFactorPending' => $user->hasPendingTwoFactorSetup(),
            'twoFactorSetupSecret' => $setupSecret,
            'twoFactorSetupCodes' => $setupCodes,
            'twoFactorQrCodeSvg' => $setupSecret
                ? $this->qrCodeSvg($user->email, $setupSecret)
                : null,
        ];
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
