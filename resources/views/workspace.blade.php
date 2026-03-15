@extends('layouts.app')

@section('title', 'Workspace')

@section('content')
<div class="min-h-screen flex flex-col">
    {{-- Top nav --}}
    <header class="bg-white border-b border-gray-200 px-4 py-3 flex items-center justify-between">
        <span class="font-semibold text-gray-900">Freeman</span>

        <div class="flex items-center gap-4">
            @if (session('status'))
                <span class="text-sm text-green-600">{{ session('status') }}</span>
            @endif

            <span class="text-sm text-gray-500">{{ auth()->user()->username }}</span>

            <a href="{{ route('password.change') }}" class="text-sm text-gray-500 hover:text-gray-700">Change password</a>

            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="text-sm text-gray-500 hover:text-gray-700">Sign out</button>
            </form>
        </div>
    </header>

    {{-- Workspace placeholder --}}
    <main class="flex-1 flex items-center justify-center">
        <div class="text-center text-gray-400">
            <p class="text-lg font-medium">Workspace coming soon</p>
            <p class="text-sm mt-1">Request builder will appear here.</p>
        </div>
    </main>
</div>
@endsection
