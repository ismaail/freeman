@extends('layouts.app')

@section('title', 'Reset Password')

@section('content')
<style>
    .auth-input {
        background: var(--color-bg-base);
        border: 1px solid var(--color-border-btn);
        color: var(--color-text-input);
    }
    .auth-input::placeholder { color: var(--color-text-muted-5); }
    .auth-input:focus {
        outline: none;
        border-color: var(--color-brand);
        box-shadow: 0 0 0 2px var(--color-brand-tint-focus);
    }
    .auth-input.input-error {
        border-color: var(--color-danger);
        background: var(--color-danger-tint-bg);
    }
    .auth-btn {
        background: var(--color-brand);
        color: #fff;
        transition: background 0.15s;
    }
    .auth-btn:hover:not(:disabled) { background: var(--color-brand-hover); }
    .auth-btn:focus { outline: none; box-shadow: 0 0 0 2px var(--color-brand-tint-focus); }
    .auth-btn:disabled { opacity: 0.6; cursor: not-allowed; }
</style>

<div class="min-h-screen flex items-center justify-center px-4"
     style="background: var(--color-bg-base);">
    <div class="w-full max-w-sm">

        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold" style="color: var(--color-text-primary);">Freeman</h1>
            <p class="text-sm mt-1" style="color: var(--color-text-muted-3);">REST API Client</p>
        </div>

        <div class="rounded-xl shadow-sm p-8"
             style="background: var(--color-bg-elevated); border: 1px solid var(--color-border-subtle);">
            <h2 class="text-lg font-semibold mb-6" style="color: var(--color-text-primary);">Set a new password</h2>

            <form method="POST" action="{{ route('password.update') }}" x-data="{ loading: false }" @submit="loading = true">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">

                <div class="space-y-4">
                    <div>
                        <label for="email" class="block text-sm font-medium mb-1"
                               style="color: var(--color-text-muted-1);">Email address</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email', $email) }}"
                            required
                            autofocus
                            autocomplete="email"
                            class="auth-input w-full px-3 py-2 rounded-lg text-sm @error('email') input-error @enderror"
                            placeholder="you@example.com"
                        >
                        @error('email')
                            <p class="mt-1 text-xs" style="color: var(--color-danger-light);">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium mb-1"
                               style="color: var(--color-text-muted-1);">New password</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            autocomplete="new-password"
                            class="auth-input w-full px-3 py-2 rounded-lg text-sm @error('password') input-error @enderror"
                            placeholder="Min 8 characters"
                        >
                        @error('password')
                            <p class="mt-1 text-xs" style="color: var(--color-danger-light);">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium mb-1"
                               style="color: var(--color-text-muted-1);">Confirm new password</label>
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            required
                            autocomplete="new-password"
                            class="auth-input w-full px-3 py-2 rounded-lg text-sm"
                            placeholder="Repeat your password"
                        >
                    </div>
                </div>

                <button
                    type="submit"
                    class="auth-btn mt-6 w-full py-2 px-4 rounded-lg text-sm font-medium"
                    :disabled="loading"
                >
                    <span x-show="!loading">Reset password</span>
                    <span x-show="loading" x-cloak>Resetting…</span>
                </button>
            </form>
        </div>

    </div>
</div>
@endsection
