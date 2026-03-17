@extends('layouts.app')

@section('title', 'Change Password')

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

<div class="min-h-screen flex flex-col"
     style="background: var(--color-bg-base);">

    @include('workspace.topbar', ['standalone' => true])

    <div class="flex-1 flex items-center justify-center px-4">
    <div class="w-full max-w-sm">

        <div class="rounded-xl shadow-sm p-8"
             style="background: var(--color-bg-elevated); border: 1px solid var(--color-border-subtle);">

            @if (auth()->user()->must_change_password)
                <div class="mb-6 p-3 rounded-lg"
                     style="background: rgba(184,134,11,0.12); border: 1px solid rgba(184,134,11,0.35);">
                    <p class="text-sm" style="color: var(--color-folder);">You must change your password before continuing.</p>
                </div>
            @endif

            <h2 class="text-lg font-semibold mb-6" style="color: var(--color-text-primary);">Change password</h2>

            <form method="POST" action="{{ route('password.change.update') }}" x-data="{ loading: false }" @submit="loading = true">
                @csrf

                <div class="space-y-4">
                    <div>
                        <label for="current_password" class="block text-sm font-medium mb-1"
                               style="color: var(--color-text-muted-1);">Current password</label>
                        <input
                            type="password"
                            id="current_password"
                            name="current_password"
                            required
                            autocomplete="current-password"
                            class="auth-input w-full px-3 py-2 rounded-lg text-sm @error('current_password') input-error @enderror"
                        >
                        @error('current_password')
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
                            placeholder="Minimum 8 characters"
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
                        >
                    </div>
                </div>

                <button
                    type="submit"
                    class="auth-btn mt-6 w-full py-2 px-4 rounded-lg text-sm font-medium"
                    :disabled="loading"
                >
                    <span x-show="!loading">Update password</span>
                    <span x-show="loading" x-cloak>Updating…</span>
                </button>
            </form>

            @if (! auth()->user()->must_change_password)
                <div class="mt-4 text-center">
                    <a href="{{ route('workspace') }}"
                       class="text-sm transition-colors"
                       style="color: var(--color-text-muted-3);"
                       onmouseover="this.style.color='var(--color-text-primary)'"
                       onmouseout="this.style.color='var(--color-text-muted-3)'"
                    >Cancel</a>
                </div>
            @endif

        </div>

    </div>
    </div>
</div>
@endsection
