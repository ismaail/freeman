@extends('layouts.app')

@section('title', 'User Management')

@section('content')
<div class="min-h-screen flex flex-col" x-data="userManager()">

    {{-- Nav --}}
    <header class="bg-white border-b border-gray-200 px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('workspace') }}" class="font-semibold text-gray-900 hover:text-blue-600">Freeman</a>
            <span class="text-gray-300">/</span>
            <span class="text-sm text-gray-500">User Management</span>
        </div>

        <div class="flex items-center gap-4">
            @if (session('status'))
                <span class="text-sm text-green-600" x-data x-init="setTimeout(() => $el.remove(), 4000)">
                    {{ session('status') }}
                </span>
            @endif

            <span class="text-sm text-gray-500">{{ auth()->user()->username }}</span>

            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="text-sm text-gray-500 hover:text-gray-700">Sign out</button>
            </form>
        </div>
    </header>

    <main class="flex-1 max-w-3xl w-full mx-auto px-4 py-8">

        {{-- Page header --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Users</h1>
                <p class="text-sm text-gray-500 mt-0.5">Manage who can access Freeman.</p>
            </div>
            <button
                @click="showCreateForm = !showCreateForm"
                class="inline-flex items-center gap-1.5 bg-blue-600 text-white text-sm font-medium px-3 py-2 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors"
            >
                <svg x-show="!showCreateForm" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                <svg x-show="showCreateForm" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <span x-text="showCreateForm ? 'Cancel' : 'New User'"></span>
            </button>
        </div>

        {{-- Create user form (inline, collapsible) --}}
        <div
            x-show="showCreateForm"
            x-cloak
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 -translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            class="bg-white border border-gray-200 rounded-xl p-6 mb-6 shadow-sm"
        >
            <h2 class="text-sm font-semibold text-gray-700 mb-4">Create new user</h2>

            <form method="POST" action="{{ route('admin.users.store') }}" @submit="submitting = true">
                @csrf

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            value="{{ old('username') }}"
                            required
                            autocomplete="off"
                            class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('username') border-red-400 bg-red-50 @else border-gray-300 @enderror"
                            placeholder="e.g. jane"
                        >
                        @error('username')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Temporary password</label>
                        <input
                            type="text"
                            id="password"
                            name="password"
                            required
                            autocomplete="off"
                            class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('password') border-red-400 bg-red-50 @else border-gray-300 @enderror"
                            placeholder="Min 8 characters"
                        >
                        @error('password')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <p class="mt-3 text-xs text-gray-400">The user will be required to change this password on first login.</p>

                <div class="mt-4 flex gap-2">
                    <button
                        type="submit"
                        class="bg-blue-600 text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors disabled:opacity-60"
                        :disabled="submitting"
                    >
                        <span x-show="!submitting">Create user</span>
                        <span x-show="submitting" x-cloak>Creating…</span>
                    </button>
                    <button
                        type="button"
                        @click="showCreateForm = false"
                        class="text-sm text-gray-500 hover:text-gray-700 px-4 py-2"
                    >
                        Cancel
                    </button>
                </div>
            </form>
        </div>

        {{-- Users table --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            @if ($users->isEmpty())
                <div class="px-6 py-12 text-center text-gray-400">
                    <p class="text-sm">No users yet. Create one above.</p>
                </div>
            @else
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50">
                            <th class="text-left px-6 py-3 font-medium text-gray-500 text-xs uppercase tracking-wide">Username</th>
                            <th class="text-left px-6 py-3 font-medium text-gray-500 text-xs uppercase tracking-wide">Created</th>
                            <th class="text-left px-6 py-3 font-medium text-gray-500 text-xs uppercase tracking-wide">Status</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($users as $user)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $user->username }}</td>
                                <td class="px-6 py-4 text-gray-500">{{ $user->created_at->format('M j, Y') }}</td>
                                <td class="px-6 py-4">
                                    @if ($user->must_change_password)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200">
                                            Password change required
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-50 text-green-700 border border-green-200">
                                            Active
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button
                                        @click="confirmDelete({{ $user->id }}, '{{ addslashes($user->username) }}')"
                                        class="text-xs text-red-500 hover:text-red-700 font-medium"
                                    >
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

    </main>

    {{-- Delete confirmation modal --}}
    <div
        x-show="deleteModal.open"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center"
        @keydown.escape.window="deleteModal.open = false"
    >
        {{-- Backdrop --}}
        <div
            class="absolute inset-0 bg-black/40"
            @click="deleteModal.open = false"
        ></div>

        {{-- Dialog --}}
        <div
            class="relative bg-white rounded-xl shadow-xl p-6 w-full max-w-sm mx-4"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
        >
            <h3 class="text-base font-semibold text-gray-900 mb-2">Delete user?</h3>
            <p class="text-sm text-gray-500 mb-6">
                This will permanently delete <span class="font-medium text-gray-800" x-text="`&quot;${deleteModal.username}&quot;`"></span>
                and all their data. This cannot be undone.
            </p>

            <form method="POST" :action="`/admin/users/${deleteModal.userId}`">
                @csrf
                @method('DELETE')

                <div class="flex gap-3">
                    <button
                        type="submit"
                        class="flex-1 bg-red-600 text-white text-sm font-medium py-2 rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors"
                    >
                        Delete
                    </button>
                    <button
                        type="button"
                        @click="deleteModal.open = false"
                        class="flex-1 bg-gray-100 text-gray-700 text-sm font-medium py-2 rounded-lg hover:bg-gray-200 focus:outline-none transition-colors"
                    >
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function userManager() {
    return {
        showCreateForm: {{ $errors->any() ? 'true' : 'false' }},
        submitting: false,
        deleteModal: {
            open: false,
            userId: null,
            username: '',
        },
        confirmDelete(id, username) {
            this.deleteModal.userId = id;
            this.deleteModal.username = username;
            this.deleteModal.open = true;
        },
    };
}
</script>
@endsection
