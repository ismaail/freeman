@extends('layouts.app')

@section('title', 'Change Password')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-sm">

        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Freeman</h1>
            <p class="text-sm text-gray-500 mt-1">REST API Client</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">

            @if (auth()->user()->must_change_password)
                <div class="mb-6 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                    <p class="text-sm text-amber-800">You must change your password before continuing.</p>
                </div>
            @endif

            <h2 class="text-lg font-semibold text-gray-900 mb-6">Change password</h2>

            <form method="POST" action="{{ route('password.change.update') }}" x-data="{ loading: false }" @submit="loading = true">
                @csrf

                <div class="space-y-4">
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Current password</label>
                        <input
                            type="password"
                            id="current_password"
                            name="current_password"
                            required
                            autocomplete="current-password"
                            class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('current_password') border-red-400 bg-red-50 @else border-gray-300 @enderror"
                        >
                        @error('current_password')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">New password</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            autocomplete="new-password"
                            class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('password') border-red-400 bg-red-50 @else border-gray-300 @enderror"
                            placeholder="Minimum 8 characters"
                        >
                        @error('password')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm new password</label>
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            required
                            autocomplete="new-password"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                    </div>
                </div>

                <button
                    type="submit"
                    class="mt-6 w-full bg-blue-600 text-white py-2 px-4 rounded-lg text-sm font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors disabled:opacity-60"
                    :disabled="loading"
                >
                    <span x-show="!loading">Update password</span>
                    <span x-show="loading" x-cloak>Updating…</span>
                </button>
            </form>

            @if (! auth()->user()->must_change_password)
                <div class="mt-4 text-center">
                    <a href="{{ route('workspace') }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
                </div>
            @endif

        </div>

    </div>
</div>
@endsection
