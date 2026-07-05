@extends('layouts.auth')

@section('title', 'Вход в админку')

@section('content')
    <div class="auth-form">
        @if(session('status'))
            <div class="alert alert-info mb-0">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('admin.login.store') }}" class="admin-form-grid">
            @csrf

            <div>
                <label class="form-label" for="email">Email</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    class="form-control @error('email') is-invalid @enderror"
                    autocomplete="email"
                    required
                >
                @error('email')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label class="form-label" for="password">Пароль</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    class="form-control @error('password') is-invalid @enderror"
                    autocomplete="current-password"
                    required
                >
                @error('password')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <button class="btn-soft w-100" type="submit">Войти</button>
        </form>

        <p class="form-hint mb-0">
            После входа вы попадёте в дашборд. Если для аккаунта включена 2FA, система попросит код из Google Authenticator.
        </p>
    </div>
@endsection
