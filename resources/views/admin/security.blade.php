@extends('layouts.admin')

@section('title', 'Безопасность')

@section('content')
    <section class="admin-card">
        @if(session('status'))
            <div class="alert alert-info">{{ session('status') }}</div>
        @endif

        <div class="panel__title">
            <div>
                <p class="eyebrow">Security</p>
                <h2>Двухфакторная аутентификация</h2>
            </div>
            <a class="btn-ghost" href="{{ route('admin.profile.edit') }}">Профиль администратора</a>
        </div>

        @include('admin.partials.two-factor-panel', [
            'twoFactorEnabled' => $twoFactorEnabled,
            'twoFactorPending' => $twoFactorPending,
            'twoFactorSetupSecret' => $twoFactorSetupSecret,
            'twoFactorSetupCodes' => $twoFactorSetupCodes,
            'twoFactorSetupUri' => $twoFactorSetupUri,
            'twoFactorQrCodeSvg' => $twoFactorQrCodeSvg,
            'twoFactorSetupRoute' => route('admin.security.two-factor.setup'),
            'twoFactorConfirmRoute' => route('admin.security.two-factor.confirm'),
            'twoFactorRecoveryCodesRoute' => route('admin.security.two-factor.recovery-codes'),
            'twoFactorDisableRoute' => route('admin.security.two-factor.disable'),
        ])
    </section>
@endsection
