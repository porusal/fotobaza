@extends('layouts.admin')

@section('title', 'Профиль')

@section('content')
    <div class="profile-grid">
        <section class="admin-card profile-card">
            @if(session('status'))
                <div class="alert alert-info">{{ session('status') }}</div>
            @endif

            <div class="panel__title">
                <div>
                    <p class="eyebrow">Account</p>
                    <h2>Личные данные и пароль</h2>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.profile.update') }}" class="admin-form-grid">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="name">Имя</label>
                        <input
                            id="name"
                            name="name"
                            type="text"
                            value="{{ old('name', $adminUser->name) }}"
                            class="form-control @error('name') is-invalid @enderror"
                            required
                        >
                        @error('name')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="email">Email</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email', $adminUser->email) }}"
                            class="form-control @error('email') is-invalid @enderror"
                            required
                        >
                        @error('email')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="profile-note">
                    <strong>Текущий доступ</strong>
                    <span>Права администратора привязаны к этому аккаунту. После смены email сессия сохранится, но данные обновятся сразу.</span>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label" for="current_password">Текущий пароль</label>
                        <input
                            id="current_password"
                            name="current_password"
                            type="password"
                            class="form-control @error('current_password') is-invalid @enderror"
                            required
                        >
                        @error('current_password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label" for="new_password">Новый пароль</label>
                        <input
                            id="new_password"
                            name="new_password"
                            type="password"
                            class="form-control @error('new_password') is-invalid @enderror"
                            autocomplete="new-password"
                        >
                        @error('new_password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label" for="new_password_confirmation">Повторите пароль</label>
                        <input
                            id="new_password_confirmation"
                            name="new_password_confirmation"
                            type="password"
                            class="form-control"
                            autocomplete="new-password"
                        >
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <button class="btn-soft" type="submit">Сохранить профиль</button>
                    <a class="btn-ghost" href="{{ route('admin.security.show') }}">Отдельная страница 2FA</a>
                </div>
            </form>
        </section>

        <section class="admin-card">
            <div class="panel__title">
                <div>
                    <p class="eyebrow">Security</p>
                    <h2>2FA и recovery codes</h2>
                </div>
            </div>

            @include('admin.partials.two-factor-panel', [
                'twoFactorEnabled' => $twoFactor['twoFactorEnabled'],
                'twoFactorPending' => $twoFactor['twoFactorPending'],
                'twoFactorSetupSecret' => $twoFactor['twoFactorSetupSecret'],
                'twoFactorSetupCodes' => $twoFactor['twoFactorSetupCodes'],
                'twoFactorQrCodeSvg' => $twoFactor['twoFactorQrCodeSvg'],
                'twoFactorSetupRoute' => route('admin.profile.two-factor.setup'),
                'twoFactorConfirmRoute' => route('admin.profile.two-factor.confirm'),
                'twoFactorRecoveryCodesRoute' => route('admin.profile.two-factor.recovery-codes'),
                'twoFactorDisableRoute' => route('admin.profile.two-factor.disable'),
            ])
        </section>
    </div>
@endsection
