@extends('layouts.app')

@section('title', 'User Management')

@section('content')
<style>
    .form-input {
        background: var(--color-bg-base);
        border: 1px solid var(--color-border-btn);
        color: var(--color-text-input);
    }
    .form-input::placeholder { color: var(--color-text-muted-5); }
    .form-input:focus {
        outline: none;
        border-color: var(--color-brand);
        box-shadow: 0 0 0 2px var(--color-brand-tint-focus);
    }
    .form-input.input-error {
        border-color: var(--color-danger);
        background: var(--color-danger-tint-bg);
    }
    .btn-primary {
        background: var(--color-brand);
        color: #fff;
        transition: background 0.15s;
    }
    .btn-primary:hover:not(:disabled) { background: var(--color-brand-hover); }
    .btn-primary:focus { outline: none; box-shadow: 0 0 0 2px var(--color-brand-tint-focus); }
    .btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
    .btn-danger {
        background: var(--color-danger);
        color: #fff;
        transition: background 0.15s;
    }
    .btn-danger:hover { background: #dc2626; }
    .btn-danger:focus { outline: none; box-shadow: 0 0 0 2px var(--color-danger-tint-border); }
    .btn-secondary {
        background: var(--color-bg-btn);
        color: var(--color-text-muted-1);
        transition: background 0.15s;
    }
    .btn-secondary:hover { background: var(--color-bg-hover-row); }
    .table-row:hover td { background: var(--color-bg-hover-subtle); }
    .table-divider tr + tr td { border-top: 1px solid var(--color-border-subtle); }
</style>

<div class="min-h-screen flex flex-col" x-data="userManager()"
     style="background: var(--color-bg-base);">

    @include('workspace.topbar', ['standalone' => true])

    <main class="flex-1 max-w-3xl w-full mx-auto px-4 py-8">

        {{-- Page header --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-xl font-semibold" style="color: var(--color-text-primary);">Users</h1>
                <p class="text-sm mt-0.5" style="color: var(--color-text-muted-3);">Manage who can access Freeman.</p>
            </div>
            <button
                @click="showCreateForm = !showCreateForm"
                class="btn-primary inline-flex items-center gap-1.5 text-sm font-medium px-3 py-2 rounded-lg"
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
            class="rounded-xl p-6 mb-6 shadow-sm"
            style="background: var(--color-bg-elevated); border: 1px solid var(--color-border-subtle);"
        >
            <h2 class="text-sm font-semibold mb-4" style="color: var(--color-text-muted-1);">Create new user</h2>

            <form method="POST" action="{{ route('admin.users.store') }}" @submit="submitting = true">
                @csrf

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="username" class="block text-sm font-medium mb-1"
                               style="color: var(--color-text-muted-1);">Username</label>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            value="{{ old('username') }}"
                            required
                            autocomplete="off"
                            class="form-input w-full px-3 py-2 rounded-lg text-sm @error('username') input-error @enderror"
                            placeholder="e.g. jane"
                        >
                        @error('username')
                            <p class="mt-1 text-xs" style="color: var(--color-danger-light);">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium mb-1"
                               style="color: var(--color-text-muted-1);">Temporary password</label>
                        <input
                            type="text"
                            id="password"
                            name="password"
                            required
                            autocomplete="off"
                            class="form-input w-full px-3 py-2 rounded-lg text-sm @error('password') input-error @enderror"
                            placeholder="Min 8 characters"
                        >
                        @error('password')
                            <p class="mt-1 text-xs" style="color: var(--color-danger-light);">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <p class="mt-3 text-xs" style="color: var(--color-text-muted-4);">The user will be required to change this password on first login.</p>

                <div class="mt-4 flex gap-2">
                    <button
                        type="submit"
                        class="btn-primary text-sm font-medium px-4 py-2 rounded-lg"
                        :disabled="submitting"
                    >
                        <span x-show="!submitting">Create user</span>
                        <span x-show="submitting" x-cloak>Creating…</span>
                    </button>
                    <button
                        type="button"
                        @click="showCreateForm = false"
                        class="text-sm px-4 py-2 transition-colors"
                        style="color: var(--color-text-muted-3);"
                        onmouseover="this.style.color='var(--color-text-primary)'"
                        onmouseout="this.style.color='var(--color-text-muted-3)'"
                    >
                        Cancel
                    </button>
                </div>
            </form>
        </div>

        {{-- Users table --}}
        <div class="rounded-xl shadow-sm overflow-hidden"
             style="background: var(--color-bg-elevated); border: 1px solid var(--color-border-subtle);">
            @if ($users->isEmpty())
                <div class="px-6 py-12 text-center">
                    <p class="text-sm" style="color: var(--color-text-muted-4);">No users yet. Create one above.</p>
                </div>
            @else
                <table class="w-full text-sm">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--color-border-subtle); background: var(--color-bg-surface);">
                            <th class="text-left px-6 py-3 font-medium text-xs uppercase tracking-wide"
                                style="color: var(--color-text-muted-3);">Username</th>
                            <th class="text-left px-6 py-3 font-medium text-xs uppercase tracking-wide"
                                style="color: var(--color-text-muted-3);">Created</th>
                            <th class="text-left px-6 py-3 font-medium text-xs uppercase tracking-wide"
                                style="color: var(--color-text-muted-3);">Status</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="table-divider">
                        @foreach ($users as $user)
                            <tr class="table-row transition-colors">
                                <td class="px-6 py-4 font-medium" style="color: var(--color-text-primary);">{{ $user->username }}</td>
                                <td class="px-6 py-4" style="color: var(--color-text-muted-3);">{{ $user->created_at->format('M j, Y') }}</td>
                                <td class="px-6 py-4">
                                    @if ($user->must_change_password)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                              style="background: rgba(184,134,11,0.12); color: var(--color-folder); border: 1px solid rgba(184,134,11,0.35);">
                                            Password change required
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                              style="background: var(--color-success-tint-bg); color: var(--color-success); border: 1px solid var(--color-success-tint-border);">
                                            Active
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button
                                        @click="confirmDelete({{ $user->id }}, '{{ addslashes($user->username) }}')"
                                        class="text-xs font-medium transition-colors"
                                        style="color: var(--color-danger);"
                                        onmouseover="this.style.color='var(--color-danger-light)'"
                                        onmouseout="this.style.color='var(--color-danger)'"
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
            class="absolute inset-0"
            style="background: rgba(0,0,0,0.5);"
            @click="deleteModal.open = false"
        ></div>

        {{-- Dialog --}}
        <div
            class="relative rounded-xl shadow-xl p-6 w-full max-w-sm mx-4"
            style="background: var(--color-bg-elevated); border: 1px solid var(--color-border-menu);"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
        >
            <h3 class="text-base font-semibold mb-2" style="color: var(--color-text-primary);">Delete user?</h3>
            <p class="text-sm mb-6" style="color: var(--color-text-muted-3);">
                This will permanently delete
                <span class="font-medium" style="color: var(--color-text-primary);"
                      x-text="`&quot;${deleteModal.username}&quot;`"></span>
                and all their data. This cannot be undone.
            </p>

            <form method="POST" :action="`/admin/users/${deleteModal.userId}`">
                @csrf
                @method('DELETE')

                <div class="flex gap-3">
                    <button
                        type="submit"
                        class="btn-danger flex-1 text-sm font-medium py-2 rounded-lg"
                    >
                        Delete
                    </button>
                    <button
                        type="button"
                        @click="deleteModal.open = false"
                        class="btn-secondary flex-1 text-sm font-medium py-2 rounded-lg"
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
