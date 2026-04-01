{{-- ================================================================
     TOP BAR
     $standalone (optional, default false): when true, wraps in its own
     x-data and hides the environment picker (used on non-workspace pages)
================================================================ --}}
@php $standalone ??= false; @endphp
@if($standalone)<div x-data="{ userMenuOpen: false }">@endif
<header class="h-12 flex-shrink-0 flex items-center gap-3 px-4"
        style="background:var(--color-bg-elevated); border-bottom:1px solid var(--color-border-subtle);">

    {{-- Logo --}}
    <a href="{{ route('workspace') }}" class="flex items-center gap-2 mr-2" style="text-decoration:none;">
        <img src="{{ asset('images/Freeman-logo-transparent.png') }}" alt="Freeman" class="h-7 w-auto">
        <span class="text-white font-semibold text-sm tracking-wide select-none">Freeman</span>
    </a>

    <div class="flex-1"></div>

    {{-- Environment picker (workspace only) --}}
    @unless($standalone)
    <div class="relative">
        <button @click="envMenuOpen = !envMenuOpen"
                class="flex items-center gap-2 px-3 py-1.5 rounded text-xs transition-colors select-none"
                style="background:var(--color-bg-btn); border:1px solid var(--color-border-btn); color:var(--color-text-primary);"
                onmouseover="this.style.borderColor='var(--color-border-strong)'" onmouseout="this.style.borderColor='var(--color-border-btn)'">
            <span class="w-1.5 h-1.5 rounded-full flex-shrink-0"
                  :style="activeEnvironment ? 'background:var(--color-success)' : 'background:var(--color-border-input)'"></span>
            <span x-text="activeEnvironment ? activeEnvironment.name : 'No Environment'"
                  class="max-w-[160px] truncate"></span>
            <svg class="w-3 h-3 flex-shrink-0" style="color:var(--color-text-muted-3)" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div x-show="envMenuOpen"
             x-cloak
             @click.outside="envMenuOpen = false"
             class="absolute right-0 top-full mt-1 w-56 rounded shadow-2xl z-50 py-1"
             style="background:var(--color-bg-elevated); border:1px solid var(--color-border-menu);">
            <template x-for="env in environments" :key="env.id">
                <button @click="activateEnvironment(env.id)"
                        class="w-full flex items-center justify-between px-3 py-2 text-xs text-left transition-colors"
                        style="color:var(--color-text-primary)"
                        onmouseover="this.style.background='var(--color-bg-btn)'" onmouseout="this.style.background='transparent'">
                    <span class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full flex-shrink-0"
                              :style="env.is_active ? 'background:var(--color-success)' : 'background:var(--color-border-input)'"></span>
                        <span x-text="env.name" class="truncate max-w-[140px]"></span>
                    </span>
                    <span x-show="env.is_active" class="text-[10px] font-medium" style="color:var(--color-success)">ACTIVE</span>
                </button>
            </template>
            <div x-show="environments.length === 0" class="px-3 py-2 text-xs" style="color:var(--color-text-muted-5)">
                No environments created
            </div>
            <div style="border-top:1px solid var(--color-border-subtle); margin:4px 0;"></div>
            <button @click="deactivateEnvironment()"
                    class="w-full px-3 py-2 text-xs text-left transition-colors"
                    style="color:var(--color-text-muted-3)"
                    onmouseover="this.style.color='var(--color-text-primary)'; this.style.background='var(--color-bg-btn)'"
                    onmouseout="this.style.color='var(--color-text-muted-3)'; this.style.background='transparent'">
                No Environment
            </button>
        </div>
    </div>
    @endunless

    {{-- User menu --}}
    <div class="relative">
        <button @click="userMenuOpen = !userMenuOpen"
                class="flex items-center gap-2 px-3 py-1.5 rounded text-xs transition-colors select-none"
                style="background:var(--color-bg-btn); border:1px solid var(--color-border-btn); color:var(--color-text-primary);"
                onmouseover="this.style.borderColor='var(--color-border-strong)'" onmouseout="this.style.borderColor='var(--color-border-btn)'">
            <svg class="w-3.5 h-3.5 flex-shrink-0" style="color:var(--color-text-muted-3)" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            <span>{{ auth()->user()->username }}</span>
            <svg class="w-3 h-3 flex-shrink-0" style="color:var(--color-text-muted-3)" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div x-show="userMenuOpen"
             x-cloak
             @click.outside="userMenuOpen = false"
             class="absolute right-0 top-full mt-1 w-48 rounded shadow-2xl z-50 py-1"
             style="background:var(--color-bg-elevated); border:1px solid var(--color-border-menu);">
            @if(auth()->user()->is_super_admin)
            <a href="{{ route('admin.users.index') }}"
               class="flex items-center gap-2.5 px-3 py-2 text-xs transition-colors"
               style="color:var(--color-text-primary); text-decoration:none;"
               onmouseover="this.style.background='var(--color-bg-btn)'" onmouseout="this.style.background='transparent'">
                <svg class="w-3.5 h-3.5 flex-shrink-0" style="color:var(--color-text-muted-3)" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                Manage Users
            </a>
            <div style="border-top:1px solid var(--color-border-subtle); margin:4px 0;"></div>
            @endif
            <a href="{{ route('password.change') }}"
               class="flex items-center gap-2.5 px-3 py-2 text-xs transition-colors"
               style="color:var(--color-text-primary); text-decoration:none;"
               onmouseover="this.style.background='var(--color-bg-btn)'" onmouseout="this.style.background='transparent'">
                <svg class="w-3.5 h-3.5 flex-shrink-0" style="color:var(--color-text-muted-3)" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
                Change Password
            </a>
            <div style="border-top:1px solid var(--color-border-subtle); margin:4px 0;"></div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="w-full flex items-center gap-2.5 px-3 py-2 text-xs text-left transition-colors"
                        style="color:var(--color-text-primary);"
                        onmouseover="this.style.background='var(--color-bg-btn)'" onmouseout="this.style.background='transparent'">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" style="color:var(--color-text-muted-3)" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Sign Out
                </button>
            </form>
        </div>
    </div>
</header>
@if($standalone)</div>@endif
