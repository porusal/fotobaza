<div class="security-card">
    <p class="eyebrow">Google Authenticator</p>
    <h2 class="h3 mb-2">Двухфакторная аутентификация</h2>
    <p class="page-copy">
        {{ $twoFactorEnabled ? '2FA уже включена и будет запрашиваться при каждом входе в админку.' : 'Включите 2FA, чтобы вход требовал код из приложения Google Authenticator.' }}
    </p>

    <div class="security-status">
        <span class="chip {{ $twoFactorEnabled ? 'is-active' : '' }}">
            {{ $twoFactorEnabled ? 'Включена' : 'Выключена' }}
        </span>
        @if($twoFactorPending)
            <span class="chip">Требует подтверждения</span>
        @endif
    </div>

    @if(! $twoFactorEnabled && ! $twoFactorSetupSecret)
        <form method="POST" action="{{ $twoFactorSetupRoute }}" class="mt-3">
            @csrf
            <button class="btn-soft" type="submit">Создать секрет 2FA</button>
        </form>
    @endif

    @if($twoFactorSetupSecret && ! $twoFactorEnabled)
        <div class="security-setup mt-4">
            <div class="security-qr">
                @if($twoFactorQrCodeSvg)
                    {!! $twoFactorQrCodeSvg !!}
                @endif
            </div>

            <p class="form-hint">
                Отсканируйте QR-код в приложении и введите 6-значный код, чтобы завершить включение.
            </p>

            @if(!empty($twoFactorSetupCodes))
                <div>
                    <h3 class="h5 mb-2">Recovery codes</h3>
                    <div class="security-code-list">
                        @foreach($twoFactorSetupCodes as $code)
                            <code class="security-code">{{ $code }}</code>
                        @endforeach
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ $twoFactorConfirmRoute }}" class="admin-form-grid mt-4">
                @csrf
                <div>
                    <label class="form-label" for="confirmation-code">Код подтверждения</label>
                    <input
                        id="confirmation-code"
                        name="code"
                        type="text"
                        class="form-control @error('code') is-invalid @enderror"
                        autocomplete="one-time-code"
                        inputmode="text"
                        required
                    >
                    @error('code')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <button class="btn-soft" type="submit">Подтвердить и включить</button>
            </form>

            <form method="POST" action="{{ $twoFactorDisableRoute }}" class="mt-2">
                @csrf
                @method('DELETE')
                <button class="btn-ghost" type="submit">Отменить настройку</button>
            </form>
        </div>
    @endif

    @if($twoFactorEnabled)
        <div class="security-setup mt-4">
            <div class="security-qr security-qr--compact">
                <strong>2FA активна</strong>
                <p class="form-hint mb-0">
                    При входе в админку потребуется код из приложения. Recovery codes можно использовать один раз.
                </p>
            </div>

            @if(session('recovery_codes'))
                <div>
                    <h3 class="h5 mb-2">Recovery codes</h3>
                    <div class="security-code-list">
                        @foreach(session('recovery_codes') as $code)
                            <code class="security-code">{{ $code }}</code>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="security-actions">
                <form method="POST" action="{{ $twoFactorRecoveryCodesRoute }}">
                    @csrf
                    <button class="btn-soft" type="submit">Новые recovery codes</button>
                </form>

                <form method="POST" action="{{ $twoFactorDisableRoute }}">
                    @csrf
                    @method('DELETE')
                    <button class="btn-ghost" type="submit">Отключить 2FA</button>
                </form>
            </div>
        </div>
    @endif
</div>
