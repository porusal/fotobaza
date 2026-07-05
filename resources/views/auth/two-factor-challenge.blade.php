@extends('layouts.auth')

@section('title', 'Подтверждение 2FA')

@section('content')
    <div class="auth-form">
        <div class="panel panel--soft mb-0">
            <p class="eyebrow">Two-factor challenge</p>
            <h2 class="h3 mb-2">Подтвердите вход</h2>
            <p class="page-copy mb-0">
                Аккаунт: <strong>{{ $pendingUser->email }}</strong>
            </p>
        </div>

        @if(session('status'))
            <div class="alert alert-info mb-0">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('admin.2fa.verify') }}" class="admin-form-grid">
            @csrf

            <div>
                <label class="form-label" for="code">Код из приложения или recovery code</label>
                <input
                    id="code"
                    name="code"
                    type="text"
                    value="{{ old('code') }}"
                    class="form-control @error('code') is-invalid @enderror"
                    autocomplete="one-time-code"
                    inputmode="text"
                    placeholder="123456"
                    required
                >
                @error('code')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <button class="btn-soft w-100" type="submit">Подтвердить</button>
        </form>

        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button class="btn-ghost w-100" type="submit">Отменить вход</button>
        </form>
    </div>
@endsection
