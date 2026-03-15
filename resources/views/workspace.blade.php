@extends('layouts.app')

@section('title', 'Workspace')

@section('content')
<div x-data="workspace()"
     class="h-screen flex flex-col overflow-hidden"
     style="background:#1e1e1e; color:#cccccc; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;">

    {{-- ================================================================
         TOP BAR
    ================================================================ --}}
    <header class="h-12 flex-shrink-0 flex items-center gap-3 px-4"
            style="background:#2c2c2c; border-bottom:1px solid #3a3a3a;">

        {{-- Logo --}}
        <div class="flex items-center gap-2 mr-2">
            <svg class="w-5 h-5" style="color:#e8602c" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
            </svg>
            <span class="text-white font-semibold text-sm tracking-wide select-none">Freeman</span>
        </div>

        <div class="flex-1"></div>

        {{-- Environment picker --}}
        <div class="relative">
            <button @click="envMenuOpen = !envMenuOpen"
                    class="flex items-center gap-2 px-3 py-1.5 rounded text-xs transition-colors select-none"
                    style="background:#383838; border:1px solid #505050; color:#cccccc;"
                    onmouseover="this.style.borderColor='#707070'" onmouseout="this.style.borderColor='#505050'">
                <span class="w-1.5 h-1.5 rounded-full flex-shrink-0"
                      :style="activeEnvironment ? 'background:#4ade80' : 'background:#555'"></span>
                <span x-text="activeEnvironment ? activeEnvironment.name : 'No Environment'"
                      class="max-w-[160px] truncate"></span>
                <svg class="w-3 h-3 flex-shrink-0" style="color:#888" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div x-show="envMenuOpen"
                 x-cloak
                 @click.outside="envMenuOpen = false"
                 class="absolute right-0 top-full mt-1 w-56 rounded shadow-2xl z-50 py-1"
                 style="background:#2c2c2c; border:1px solid #444;">
                <template x-for="env in environments" :key="env.id">
                    <button @click="activateEnvironment(env.id)"
                            class="w-full flex items-center justify-between px-3 py-2 text-xs text-left transition-colors"
                            style="color:#cccccc"
                            onmouseover="this.style.background='#383838'" onmouseout="this.style.background='transparent'">
                        <span class="flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full flex-shrink-0"
                                  :style="env.is_active ? 'background:#4ade80' : 'background:#555'"></span>
                            <span x-text="env.name" class="truncate max-w-[140px]"></span>
                        </span>
                        <span x-show="env.is_active" class="text-[10px] font-medium" style="color:#4ade80">ACTIVE</span>
                    </button>
                </template>
                <div x-show="environments.length === 0" class="px-3 py-2 text-xs" style="color:#666">
                    No environments created
                </div>
                <div style="border-top:1px solid #3a3a3a; margin:4px 0;"></div>
                <button @click="deactivateEnvironment()"
                        class="w-full px-3 py-2 text-xs text-left transition-colors"
                        style="color:#888"
                        onmouseover="this.style.color='#cccccc'; this.style.background='#383838'"
                        onmouseout="this.style.color='#888'; this.style.background='transparent'">
                    No Environment
                </button>
            </div>
        </div>

        {{-- User menu --}}
        <div class="relative">
            <button @click="userMenuOpen = !userMenuOpen"
                    class="flex items-center gap-2 px-3 py-1.5 rounded text-xs transition-colors select-none"
                    style="background:#383838; border:1px solid #505050; color:#cccccc;"
                    onmouseover="this.style.borderColor='#707070'" onmouseout="this.style.borderColor='#505050'">
                <svg class="w-3.5 h-3.5 flex-shrink-0" style="color:#888" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <span>{{ auth()->user()->username }}</span>
                <svg class="w-3 h-3 flex-shrink-0" style="color:#888" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div x-show="userMenuOpen"
                 x-cloak
                 @click.outside="userMenuOpen = false"
                 class="absolute right-0 top-full mt-1 w-48 rounded shadow-2xl z-50 py-1"
                 style="background:#2c2c2c; border:1px solid #444;">
                @if(auth()->user()->is_super_admin)
                <a href="{{ route('admin.users.index') }}"
                   class="flex items-center gap-2.5 px-3 py-2 text-xs transition-colors"
                   style="color:#cccccc; text-decoration:none;"
                   onmouseover="this.style.background='#383838'" onmouseout="this.style.background='transparent'">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" style="color:#888" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    Manage Users
                </a>
                <div style="border-top:1px solid #3a3a3a; margin:4px 0;"></div>
                @endif
                <a href="{{ route('password.change') }}"
                   class="flex items-center gap-2.5 px-3 py-2 text-xs transition-colors"
                   style="color:#cccccc; text-decoration:none;"
                   onmouseover="this.style.background='#383838'" onmouseout="this.style.background='transparent'">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" style="color:#888" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                    Change Password
                </a>
                <div style="border-top:1px solid #3a3a3a; margin:4px 0;"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="w-full flex items-center gap-2.5 px-3 py-2 text-xs text-left transition-colors"
                            style="color:#cccccc;"
                            onmouseover="this.style.background='#383838'" onmouseout="this.style.background='transparent'">
                        <svg class="w-3.5 h-3.5 flex-shrink-0" style="color:#888" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Sign Out
                    </button>
                </form>
            </div>
        </div>
    </header>

    {{-- ================================================================
         BODY: SIDEBAR + MAIN
    ================================================================ --}}
    <div class="flex flex-1 overflow-hidden" style="min-height:0">

        {{-- ============================================================
             SIDEBAR
        ============================================================ --}}
        <aside class="flex-shrink-0 flex flex-col"
               style="width:260px; background:#252525; border-right:1px solid #3a3a3a;">

            {{-- New Request button --}}
            <div class="p-3 flex-shrink-0">
                <button @click="newRequest()"
                        class="w-full flex items-center justify-center gap-2 py-2 rounded text-sm font-medium text-white transition-colors"
                        style="background:#e8602c;"
                        onmouseover="this.style.background='#d4541f'" onmouseout="this.style.background='#e8602c'">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    New Request
                </button>
            </div>

            {{-- Sidebar tab nav --}}
            <div class="flex flex-shrink-0" style="border-bottom:1px solid #3a3a3a;">
                <button @click="sidebarTab = 'collections'"
                        :style="sidebarTab === 'collections' ? 'color:#fff; border-bottom:2px solid #e8602c;' : 'color:#777; border-bottom:2px solid transparent;'"
                        class="flex-1 py-2.5 text-[10px] uppercase tracking-widest font-semibold transition-colors hover:text-gray-300">
                    Collections
                </button>
                <button @click="sidebarTab = 'environments'"
                        :style="sidebarTab === 'environments' ? 'color:#fff; border-bottom:2px solid #e8602c;' : 'color:#777; border-bottom:2px solid transparent;'"
                        class="flex-1 py-2.5 text-[10px] uppercase tracking-widest font-semibold transition-colors hover:text-gray-300">
                    Envs
                </button>
                <button @click="sidebarTab = 'history'"
                        :style="sidebarTab === 'history' ? 'color:#fff; border-bottom:2px solid #e8602c;' : 'color:#777; border-bottom:2px solid transparent;'"
                        class="flex-1 py-2.5 text-[10px] uppercase tracking-widest font-semibold transition-colors hover:text-gray-300">
                    History
                </button>
            </div>

            {{-- Sidebar scrollable content --}}
            <div class="flex-1 overflow-y-auto">

                {{-- ---- COLLECTIONS TAB ---- --}}
                <div x-show="sidebarTab === 'collections'">

                    {{-- Loading --}}
                    <div x-show="collectionsLoading" class="flex items-center justify-center py-10">
                        <svg class="w-5 h-5 animate-spin" style="color:#555" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                    </div>

                    {{-- Empty --}}
                    <div x-show="!collectionsLoading && collections.length === 0"
                         class="flex flex-col items-center justify-center py-10 px-4 text-center">
                        <svg class="w-9 h-9 mb-3" style="color:#444" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                        </svg>
                        <p class="text-xs" style="color:#666">No collections yet</p>
                        <p class="text-[10px] mt-1" style="color:#555">Save a request to create one</p>
                    </div>

                    {{-- Collections toolbar: Import button --}}
                    <div x-show="!collectionsLoading" class="flex items-center justify-between px-3 pt-2 pb-1">
                        <span class="text-[9px] uppercase tracking-widest font-semibold" style="color:#4a4a4a;">Collections</span>
                        <button @click="importCollection()"
                                class="text-[10px] transition-colors"
                                style="color:#666;"
                                onmouseover="this.style.color='#e8602c'" onmouseout="this.style.color='#666'">
                            + Import
                        </button>
                    </div>

                    {{-- Import notification toast --}}
                    <div x-show="importNotification"
                         x-cloak
                         x-transition.opacity
                         class="mx-3 mb-2 px-3 py-2 rounded text-[11px]"
                         :style="importNotification?.ok
                             ? 'background:rgba(74,222,128,0.1); border:1px solid rgba(74,222,128,0.25); color:#4ade80;'
                             : 'background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.25); color:#f87171;'"
                         x-text="importNotification?.msg">
                    </div>

                    {{-- Collections list --}}
                    <div x-show="!collectionsLoading">
                        <template x-for="col in collections" :key="col.id">
                            <div>
                                {{-- Collection header --}}
                                <div class="relative flex items-center gap-1.5 px-3 py-2 select-none transition-colors group"
                                     onmouseover="this.style.background='#2e2e2e'" onmouseout="this.style.background='transparent'">
                                    {{-- Clickable area: toggles expand --}}
                                    <div @click="toggleCollection(col.id)" class="flex items-center gap-1.5 flex-1 min-w-0 cursor-pointer">
                                        <svg class="w-2.5 h-2.5 flex-shrink-0 transition-transform duration-150"
                                             :style="isCollectionExpanded(col.id) ? 'transform:rotate(90deg); color:#888' : 'color:#555'"
                                             fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                        </svg>
                                        <svg class="w-3.5 h-3.5 flex-shrink-0" style="color:#e8602c" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
                                        </svg>
                                        <span x-text="col.name"
                                              class="text-xs font-semibold truncate flex-1"
                                              style="color:#d4d4d4"></span>
                                    </div>
                                    {{-- Count badge (hidden when menu visible) --}}
                                    <span x-show="(col.requests||[]).length + (col.folders||[]).length > 0 && collectionMenuOpen !== col.id"
                                          x-text="(col.requests||[]).length + (col.folders||[]).length"
                                          class="text-[9px] px-1.5 py-0.5 rounded-full flex-shrink-0 group-hover:hidden"
                                          style="background:#333; color:#777"></span>
                                    {{-- Three-dot context menu --}}
                                    <div class="relative flex-shrink-0" @click.stop>
                                        <button @click="toggleCollectionMenu(col.id)"
                                                class="p-1 rounded transition-opacity flex-shrink-0 opacity-0 group-hover:opacity-100"
                                                :class="collectionMenuOpen === col.id ? '!opacity-100' : ''"
                                                style="color:#777;"
                                                onmouseover="this.style.color='#ccc'" onmouseout="this.style.color='#777'">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                                            </svg>
                                        </button>
                                        <div x-show="collectionMenuOpen === col.id"
                                             x-cloak
                                             @click.outside="collectionMenuOpen = null"
                                             class="absolute right-0 top-full mt-1 w-40 rounded shadow-2xl z-50 py-1"
                                             style="background:#2c2c2c; border:1px solid #444;">
                                            <button @click="exportCollection(col.id); collectionMenuOpen = null"
                                                    class="w-full flex items-center gap-2.5 px-3 py-2 text-xs text-left transition-colors"
                                                    style="color:#cccccc"
                                                    onmouseover="this.style.background='#383838'" onmouseout="this.style.background='transparent'">
                                                <svg class="w-3.5 h-3.5 flex-shrink-0" style="color:#888" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                </svg>
                                                Export JSON
                                            </button>
                                            <div style="border-top:1px solid #3a3a3a; margin:4px 0;"></div>
                                            <button @click="deleteCollection(col.id); collectionMenuOpen = null"
                                                    class="w-full flex items-center gap-2.5 px-3 py-2 text-xs text-left transition-colors"
                                                    style="color:#ef4444"
                                                    onmouseover="this.style.background='#2a1515'" onmouseout="this.style.background='transparent'">
                                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                {{-- Expanded collection contents --}}
                                <div x-show="isCollectionExpanded(col.id)">

                                    {{-- Direct requests (no folder) --}}
                                    <template x-for="req in (col.requests || [])" :key="'req-' + req.id">
                                        <div @click="openRequest(req.id)"
                                             class="flex items-center gap-2 pl-8 pr-3 py-1.5 cursor-pointer transition-colors"
                                             :style="activeRequestId === req.id ? 'background:#37373d' : ''"
                                             onmouseover="if(this.getAttribute('data-active')!=='1') this.style.background='#2a2a2a'"
                                             onmouseout="if(this.getAttribute('data-active')!=='1') this.style.background=''"
                                             :data-active="activeRequestId === req.id ? '1' : '0'">
                                            <span :class="methodColor(req.method)"
                                                  class="text-[9px] font-bold font-mono flex-shrink-0"
                                                  style="width:36px; text-align:right"
                                                  x-text="req.method"></span>
                                            <span x-text="req.name" class="text-xs truncate" style="color:#c8c8c8"></span>
                                        </div>
                                    </template>

                                    {{-- Folders --}}
                                    <template x-for="folder in (col.folders || [])" :key="'fold-' + folder.id">
                                        <div>
                                            {{-- Folder header --}}
                                            <div @click="toggleFolder(folder.id)"
                                                 class="flex items-center gap-1.5 pl-6 pr-3 py-1.5 cursor-pointer select-none transition-colors"
                                                 onmouseover="this.style.background='#2a2a2a'" onmouseout="this.style.background='transparent'">
                                                <svg class="w-2.5 h-2.5 flex-shrink-0 transition-transform duration-150"
                                                     :style="isFolderExpanded(folder.id) ? 'transform:rotate(90deg); color:#888' : 'color:#555'"
                                                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                                </svg>
                                                <svg class="w-3.5 h-3.5 flex-shrink-0" style="color:#b8860b" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
                                                </svg>
                                                <span x-text="folder.name" class="text-xs truncate flex-1" style="color:#aaa"></span>
                                            </div>

                                            {{-- Folder requests --}}
                                            <div x-show="isFolderExpanded(folder.id)">
                                                <template x-for="req in (folder.requests || [])" :key="'fr-' + req.id">
                                                    <div @click="openRequest(req.id)"
                                                         class="flex items-center gap-2 pl-12 pr-3 py-1.5 cursor-pointer transition-colors"
                                                         :style="activeRequestId === req.id ? 'background:#37373d' : ''"
                                                         onmouseover="if(this.getAttribute('data-active')!=='1') this.style.background='#2a2a2a'"
                                                         onmouseout="if(this.getAttribute('data-active')!=='1') this.style.background=''"
                                                         :data-active="activeRequestId === req.id ? '1' : '0'">
                                                        <span :class="methodColor(req.method)"
                                                              class="text-[9px] font-bold font-mono flex-shrink-0"
                                                              style="width:36px; text-align:right"
                                                              x-text="req.method"></span>
                                                        <span x-text="req.name" class="text-xs truncate" style="color:#c8c8c8"></span>
                                                    </div>
                                                </template>
                                                <div x-show="!(folder.requests || []).length"
                                                     class="pl-12 pr-3 py-1.5 text-[10px]" style="color:#555">
                                                    Empty folder
                                                </div>
                                            </div>
                                        </div>
                                    </template>

                                    {{-- Truly empty collection --}}
                                    <div x-show="!(col.requests||[]).length && !(col.folders||[]).length"
                                         class="pl-8 pr-3 py-2 text-[10px]" style="color:#555">
                                        No requests
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- ---- ENVIRONMENTS TAB ---- --}}
                <div x-show="sidebarTab === 'environments'">
                    <template x-for="env in environments" :key="env.id">
                        <div class="flex items-center gap-3 px-4 py-2.5 transition-colors"
                             style="border-bottom:1px solid #2e2e2e;"
                             onmouseover="this.style.background='#2e2e2e'" onmouseout="this.style.background='transparent'">
                            <span class="w-2 h-2 rounded-full flex-shrink-0"
                                  :style="env.is_active ? 'background:#4ade80' : 'background:#444'"></span>
                            <span x-text="env.name" class="text-xs flex-1 truncate" style="color:#c8c8c8"></span>
                            <button x-show="!env.is_active"
                                    @click="activateEnvironment(env.id)"
                                    class="text-[10px] transition-colors flex-shrink-0"
                                    style="color:#777"
                                    onmouseover="this.style.color='#e8602c'" onmouseout="this.style.color='#777'">
                                Activate
                            </button>
                            <span x-show="env.is_active"
                                  class="text-[10px] font-semibold flex-shrink-0"
                                  style="color:#4ade80">
                                Active
                            </span>
                        </div>
                    </template>
                    <div x-show="environments.length === 0"
                         class="flex flex-col items-center justify-center py-10 px-4 text-center">
                        <svg class="w-9 h-9 mb-3" style="color:#444" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        <p class="text-xs" style="color:#666">No environments</p>
                    </div>
                </div>

                {{-- ---- HISTORY TAB ---- --}}
                <div x-show="sidebarTab === 'history'">
                    <div class="flex flex-col items-center justify-center py-10 px-4 text-center">
                        <svg class="w-9 h-9 mb-3" style="color:#444" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-xs" style="color:#666">History coming soon</p>
                    </div>
                </div>

            </div>{{-- end sidebar scroll --}}

        {{-- Hidden file input for collection import --}}
        <input type="file"
               x-ref="importFileInput"
               @change="handleImportFile($event.target.files)"
               accept=".json,application/json"
               class="hidden">

        </aside>

        {{-- ============================================================
             MAIN CONTENT AREA
        ============================================================ --}}
        <main class="flex-1 flex flex-col overflow-hidden" style="min-width:0; background:#1e1e1e;">

            {{-- ---- WELCOME STATE ---- --}}
            <div x-show="!requestOpen"
                 class="flex-1 flex items-center justify-center">
                <div class="text-center" style="max-width:400px;">
                    <div class="flex items-center justify-center w-16 h-16 rounded-2xl mx-auto mb-5"
                         style="background:rgba(232,96,44,0.1); border:1px solid rgba(232,96,44,0.2);">
                        <svg class="w-8 h-8" style="color:#e8602c" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <h2 class="text-lg font-semibold text-white mb-2">Ready to test APIs?</h2>
                    <p class="text-sm mb-6" style="color:#666;">
                        Select a saved request from the sidebar, or create a new one to get started.
                    </p>
                    <button @click="newRequest()"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded text-sm font-medium text-white transition-colors"
                            style="background:#e8602c;"
                            onmouseover="this.style.background='#d4541f'" onmouseout="this.style.background='#e8602c'">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        New Request
                    </button>
                </div>
            </div>

            {{-- ---- REQUEST BUILDER ---- --}}
            <div x-show="requestOpen" class="flex-1 flex flex-col overflow-hidden" style="min-height:0">

                {{-- Request name row --}}
                <div class="flex items-center gap-3 px-5 py-2.5 flex-shrink-0"
                     style="border-bottom:1px solid #3a3a3a; background:#252525;">
                    <input x-model="currentRequest.name"
                           type="text"
                           placeholder="Request name"
                           class="flex-1 bg-transparent text-sm font-semibold text-white placeholder-gray-600 focus:outline-none"/>
                    <button @click="saveRequest()"
                            class="px-3 py-1 rounded text-xs transition-colors flex-shrink-0"
                            style="border:1px solid #555; color:#aaa;"
                            onmouseover="this.style.borderColor='#888'; this.style.color='#fff'"
                            onmouseout="this.style.borderColor='#555'; this.style.color='#aaa'">
                        Save
                    </button>
                </div>

                {{-- URL bar --}}
                <div class="flex items-center gap-2 px-4 py-2.5 flex-shrink-0"
                     style="border-bottom:1px solid #3a3a3a; background:#252525;">
                    {{-- Method dropdown --}}
                    <select x-model="currentRequest.method"
                            :class="methodColor(currentRequest.method)"
                            class="rounded px-3 py-2 text-xs font-bold font-mono focus:outline-none cursor-pointer flex-shrink-0"
                            style="background:#1e1e1e; border:1px solid #555; appearance:none; -webkit-appearance:none; min-width:72px; text-align:center;">
                        <option class="text-green-400"  value="GET">GET</option>
                        <option class="text-yellow-400" value="POST">POST</option>
                        <option class="text-blue-400"   value="PUT">PUT</option>
                        <option class="text-purple-400" value="PATCH">PATCH</option>
                        <option class="text-red-400"    value="DELETE">DELETE</option>
                    </select>

                    {{-- URL input with {{variable}} backdrop highlighting.
                         CSS-grid stacking: backdrop + real input share the same grid cell
                         so they overlap perfectly without absolute positioning issues.  --}}
                    <div class="flex-1 rounded overflow-hidden url-field-wrap"
                         :style="urlFocused ? 'border-color:rgba(232,96,44,0.6)' : 'border-color:#555'">
                        {{-- Backdrop (aria-hidden): renders highlighted copy of the URL --}}
                        <div x-ref="urlBackdrop"
                             aria-hidden="true"
                             class="url-field-back"
                             x-html="highlightUrl(currentRequest.url)"></div>
                        {{-- Real input: transparent text → only the caret is visible --}}
                        <input x-model="currentRequest.url"
                               x-ref="urlInput"
                               @keydown.enter="sendRequest()"
                               @scroll="$refs.urlBackdrop.scrollLeft = $el.scrollLeft"
                               @focus="urlFocused = true"
                               @blur="urlFocused = false"
                               type="text"
                               placeholder="https://api.example.com/endpoint"
                               class="url-field-real url-field-input"/>
                    </div>

                    {{-- Send button --}}
                    <button @click="sendRequest()"
                            :disabled="isLoading || !currentRequest.url.trim()"
                            class="flex items-center gap-2 px-5 py-2 rounded text-sm font-medium text-white transition-colors flex-shrink-0 disabled:opacity-40 disabled:cursor-not-allowed"
                            style="background:#e8602c;"
                            onmouseover="if(!this.disabled) this.style.background='#d4541f'" onmouseout="if(!this.disabled) this.style.background='#e8602c'">
                        <svg x-show="isLoading" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <span x-text="isLoading ? 'Sending…' : 'Send'"></span>
                    </button>
                </div>

                {{-- Request config + Response (vertical split) --}}
                <div class="flex-1 flex flex-col overflow-hidden" style="min-height:0;">

                    {{-- === REQUEST CONFIG (top 42%) === --}}
                    <div class="flex flex-col overflow-hidden flex-shrink-0" style="height:42%; border-bottom:1px solid #3a3a3a;">

                        {{-- Request tab bar --}}
                        <div class="flex flex-shrink-0" style="background:#252525; border-bottom:1px solid #3a3a3a;">
                            <template x-for="tab in [{id:'params', label:'Params'}, {id:'headers', label:'Headers'}, {id:'body', label:'Body'}, {id:'auth', label:'Auth'}]" :key="tab.id">
                                <button @click="requestTab = tab.id"
                                        class="relative px-5 py-2.5 text-[10px] uppercase tracking-widest font-semibold transition-colors"
                                        :style="requestTab === tab.id ? 'color:#fff; border-bottom:2px solid #e8602c;' : 'color:#777; border-bottom:2px solid transparent;'"
                                        onmouseover="if(this.getAttribute('data-act')!=='1') this.style.color='#aaa'"
                                        onmouseout="if(this.getAttribute('data-act')!=='1') this.style.color='#777'"
                                        :data-act="requestTab === tab.id ? '1' : '0'">
                                    <span x-text="tab.label"></span>
                                    {{-- Badge for filled headers --}}
                                    <span x-show="tab.id === 'headers' && filledHeaderCount > 0"
                                          x-text="filledHeaderCount"
                                          class="ml-1.5 px-1.5 py-px rounded-full text-[8px] font-bold"
                                          style="background:rgba(232,96,44,0.25); color:#e8602c;"></span>
                                    <span x-show="tab.id === 'params' && filledParamCount > 0"
                                          x-text="filledParamCount"
                                          class="ml-1.5 px-1.5 py-px rounded-full text-[8px] font-bold"
                                          style="background:rgba(232,96,44,0.25); color:#e8602c;"></span>
                                </button>
                            </template>
                        </div>

                        {{-- Tab content (scrollable) --}}
                        <div class="flex-1 overflow-y-auto p-4">

                            {{-- PARAMS --}}
                            <div x-show="requestTab === 'params'">
                                <table class="w-full" style="border-collapse:collapse;">
                                    <thead>
                                        <tr class="text-[9px] uppercase tracking-widest" style="color:#555;">
                                            <th class="pb-2 w-5 text-left"></th>
                                            <th class="pb-2 pr-2 text-left">Key</th>
                                            <th class="pb-2 text-left">Value</th>
                                            <th class="pb-2 w-5"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(p, i) in currentRequest.params" :key="'p'+i">
                                            <tr class="kv-row">
                                                <td class="pr-2 py-0.5 w-5">
                                                    <input type="checkbox" x-model="p.enabled"
                                                           class="w-3 h-3 cursor-pointer" style="accent-color:#e8602c;"/>
                                                </td>
                                                <td class="pr-1.5 py-0.5">
                                                    <input x-model="p.key" type="text" placeholder="Key"
                                                           class="kv-input w-full rounded px-2.5 py-1.5 text-xs font-mono focus:outline-none"
                                                           style="background:#1e1e1e; border:1px solid #3a3a3a; color:#d4d4d4;"
                                                           onfocus="this.style.borderColor='#555'" onblur="this.style.borderColor='#3a3a3a'"/>
                                                </td>
                                                <td class="py-0.5">
                                                    <input x-model="p.value" type="text" placeholder="Value"
                                                           class="kv-input w-full rounded px-2.5 py-1.5 text-xs font-mono focus:outline-none"
                                                           style="background:#1e1e1e; border:1px solid #3a3a3a; color:#d4d4d4;"
                                                           onfocus="this.style.borderColor='#555'" onblur="this.style.borderColor='#3a3a3a'"/>
                                                </td>
                                                <td class="pl-1.5 py-0.5 w-5">
                                                    <button @click="removeParam(i)"
                                                            class="kv-del opacity-0 transition-opacity"
                                                            style="color:#555;"
                                                            onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='#555'">
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                                        </svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                                <button @click="addParam()"
                                        class="mt-2 flex items-center gap-1 text-[10px] transition-colors"
                                        style="color:#555;"
                                        onmouseover="this.style.color='#e8602c'" onmouseout="this.style.color='#555'">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Add row
                                </button>
                            </div>

                            {{-- HEADERS --}}
                            <div x-show="requestTab === 'headers'">
                                <table class="w-full" style="border-collapse:collapse;">
                                    <thead>
                                        <tr class="text-[9px] uppercase tracking-widest" style="color:#555;">
                                            <th class="pb-2 w-5 text-left"></th>
                                            <th class="pb-2 pr-2 text-left">Key</th>
                                            <th class="pb-2 text-left">Value</th>
                                            <th class="pb-2 w-5"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(h, i) in currentRequest.headers" :key="'h'+i">
                                            <tr class="kv-row">
                                                <td class="pr-2 py-0.5 w-5">
                                                    <input type="checkbox" x-model="h.enabled"
                                                           class="w-3 h-3 cursor-pointer" style="accent-color:#e8602c;"/>
                                                </td>
                                                <td class="pr-1.5 py-0.5">
                                                    <input x-model="h.key" type="text" placeholder="Header name"
                                                           class="kv-input w-full rounded px-2.5 py-1.5 text-xs font-mono focus:outline-none"
                                                           style="background:#1e1e1e; border:1px solid #3a3a3a; color:#d4d4d4;"
                                                           onfocus="this.style.borderColor='#555'" onblur="this.style.borderColor='#3a3a3a'"/>
                                                </td>
                                                <td class="py-0.5">
                                                    <input x-model="h.value" type="text" placeholder="Value"
                                                           class="kv-input w-full rounded px-2.5 py-1.5 text-xs font-mono focus:outline-none"
                                                           style="background:#1e1e1e; border:1px solid #3a3a3a; color:#d4d4d4;"
                                                           onfocus="this.style.borderColor='#555'" onblur="this.style.borderColor='#3a3a3a'"/>
                                                </td>
                                                <td class="pl-1.5 py-0.5 w-5">
                                                    <button @click="removeHeader(i)"
                                                            class="kv-del opacity-0 transition-opacity"
                                                            style="color:#555;"
                                                            onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='#555'">
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                                        </svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                                <button @click="addHeader()"
                                        class="mt-2 flex items-center gap-1 text-[10px] transition-colors"
                                        style="color:#555;"
                                        onmouseover="this.style.color='#e8602c'" onmouseout="this.style.color='#555'">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Add row
                                </button>
                            </div>

                            {{-- BODY --}}
                            <div x-show="requestTab === 'body'">
                                {{-- Body type selector --}}
                                <div class="flex gap-4 mb-4">
                                    <template x-for="btype in ['none', 'raw', 'form-data', 'x-www-form-urlencoded']" :key="btype">
                                        <label class="flex items-center gap-1.5 cursor-pointer select-none">
                                            <input type="radio" x-model="currentRequest.body_type" :value="btype"
                                                   class="w-3 h-3 cursor-pointer" style="accent-color:#e8602c;"/>
                                            <span x-text="btype" class="text-xs capitalize" style="color:#999;"></span>
                                        </label>
                                    </template>
                                </div>

                                {{-- Raw textarea --}}
                                <div x-show="currentRequest.body_type === 'raw'">
                                    <textarea x-model="currentRequest.body"
                                              rows="6"
                                              placeholder='{"key": "value"}'
                                              class="w-full rounded px-3 py-2.5 text-xs font-mono focus:outline-none resize-none response-body"
                                              style="background:#1a1a1a; border:1px solid #3a3a3a; color:#d4d4d4; line-height:1.6;"
                                              onfocus="this.style.borderColor='#555'" onblur="this.style.borderColor='#3a3a3a'"></textarea>
                                </div>

                                {{-- Form key-value body --}}
                                <div x-show="currentRequest.body_type === 'form-data' || currentRequest.body_type === 'x-www-form-urlencoded'">
                                    <table class="w-full" style="border-collapse:collapse;">
                                        <thead>
                                            <tr class="text-[9px] uppercase tracking-widest" style="color:#555;">
                                                <th class="pb-2 w-5 text-left"></th>
                                                <th class="pb-2 pr-2 text-left">Key</th>
                                                <th class="pb-2 text-left">Value</th>
                                                <th class="pb-2 w-5"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template x-for="(r, i) in currentRequest.body_form" :key="'bf'+i">
                                                <tr class="kv-row">
                                                    <td class="pr-2 py-0.5 w-5">
                                                        <input type="checkbox" x-model="r.enabled"
                                                               class="w-3 h-3 cursor-pointer" style="accent-color:#e8602c;"/>
                                                    </td>
                                                    <td class="pr-1.5 py-0.5">
                                                        <input x-model="r.key" type="text" placeholder="Key"
                                                               class="w-full rounded px-2.5 py-1.5 text-xs font-mono focus:outline-none"
                                                               style="background:#1e1e1e; border:1px solid #3a3a3a; color:#d4d4d4;"
                                                               onfocus="this.style.borderColor='#555'" onblur="this.style.borderColor='#3a3a3a'"/>
                                                    </td>
                                                    <td class="py-0.5">
                                                        <input x-model="r.value" type="text" placeholder="Value"
                                                               class="w-full rounded px-2.5 py-1.5 text-xs font-mono focus:outline-none"
                                                               style="background:#1e1e1e; border:1px solid #3a3a3a; color:#d4d4d4;"
                                                               onfocus="this.style.borderColor='#555'" onblur="this.style.borderColor='#3a3a3a'"/>
                                                    </td>
                                                    <td class="pl-1.5 py-0.5 w-5">
                                                        <button @click="removeFormRow(i)"
                                                                class="kv-del opacity-0 transition-opacity"
                                                                style="color:#555;"
                                                                onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='#555'">
                                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                                            </svg>
                                                        </button>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                    <button @click="addFormRow()"
                                            class="mt-2 flex items-center gap-1 text-[10px] transition-colors"
                                            style="color:#555;"
                                            onmouseover="this.style.color='#e8602c'" onmouseout="this.style.color='#555'">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        Add row
                                    </button>
                                </div>

                                {{-- None --}}
                                <div x-show="currentRequest.body_type === 'none'">
                                    <p class="text-xs" style="color:#555;">This request has no body.</p>
                                </div>
                            </div>

                            {{-- AUTH --}}
                            <div x-show="requestTab === 'auth'">
                                <div class="mb-4">
                                    <label class="block text-[9px] uppercase tracking-widest mb-2" style="color:#666;">Auth Type</label>
                                    <select x-model="currentRequest.auth_type"
                                            class="rounded px-3 py-2 text-xs focus:outline-none"
                                            style="background:#1e1e1e; border:1px solid #555; color:#d4d4d4;">
                                        <option value="none">No Auth</option>
                                        <option value="bearer">Bearer Token</option>
                                        <option value="basic">Basic Auth</option>
                                        <option value="api_key">API Key</option>
                                    </select>
                                </div>

                                {{-- Bearer --}}
                                <div x-show="currentRequest.auth_type === 'bearer'" class="space-y-3">
                                    <div>
                                        <label class="block text-[9px] uppercase tracking-widest mb-1.5" style="color:#666;">Token</label>
                                        <input x-model="currentRequest.auth_data.token"
                                               type="text" placeholder="Enter bearer token"
                                               class="w-full rounded px-3 py-2 text-xs font-mono focus:outline-none"
                                               style="background:#1e1e1e; border:1px solid #3a3a3a; color:#d4d4d4;"
                                               onfocus="this.style.borderColor='#555'" onblur="this.style.borderColor='#3a3a3a'"/>
                                    </div>
                                </div>

                                {{-- Basic --}}
                                <div x-show="currentRequest.auth_type === 'basic'" class="space-y-3">
                                    <div>
                                        <label class="block text-[9px] uppercase tracking-widest mb-1.5" style="color:#666;">Username</label>
                                        <input x-model="currentRequest.auth_data.username"
                                               type="text" placeholder="username"
                                               class="w-full rounded px-3 py-2 text-xs focus:outline-none"
                                               style="background:#1e1e1e; border:1px solid #3a3a3a; color:#d4d4d4;"
                                               onfocus="this.style.borderColor='#555'" onblur="this.style.borderColor='#3a3a3a'"/>
                                    </div>
                                    <div>
                                        <label class="block text-[9px] uppercase tracking-widest mb-1.5" style="color:#666;">Password</label>
                                        <input x-model="currentRequest.auth_data.password"
                                               type="password" placeholder="password"
                                               class="w-full rounded px-3 py-2 text-xs focus:outline-none"
                                               style="background:#1e1e1e; border:1px solid #3a3a3a; color:#d4d4d4;"
                                               onfocus="this.style.borderColor='#555'" onblur="this.style.borderColor='#3a3a3a'"/>
                                    </div>
                                </div>

                                {{-- API Key --}}
                                <div x-show="currentRequest.auth_type === 'api_key'" class="space-y-3">
                                    <div>
                                        <label class="block text-[9px] uppercase tracking-widest mb-1.5" style="color:#666;">Key Name</label>
                                        <input x-model="currentRequest.auth_data.key"
                                               type="text" placeholder="X-API-Key"
                                               class="w-full rounded px-3 py-2 text-xs font-mono focus:outline-none"
                                               style="background:#1e1e1e; border:1px solid #3a3a3a; color:#d4d4d4;"
                                               onfocus="this.style.borderColor='#555'" onblur="this.style.borderColor='#3a3a3a'"/>
                                    </div>
                                    <div>
                                        <label class="block text-[9px] uppercase tracking-widest mb-1.5" style="color:#666;">Value</label>
                                        <input x-model="currentRequest.auth_data.value"
                                               type="text" placeholder="API key value"
                                               class="w-full rounded px-3 py-2 text-xs font-mono focus:outline-none"
                                               style="background:#1e1e1e; border:1px solid #3a3a3a; color:#d4d4d4;"
                                               onfocus="this.style.borderColor='#555'" onblur="this.style.borderColor='#3a3a3a'"/>
                                    </div>
                                    <div>
                                        <label class="block text-[9px] uppercase tracking-widest mb-1.5" style="color:#666;">Add To</label>
                                        <select x-model="currentRequest.auth_data.in"
                                                class="rounded px-3 py-2 text-xs focus:outline-none"
                                                style="background:#1e1e1e; border:1px solid #555; color:#d4d4d4;">
                                            <option value="header">Header</option>
                                            <option value="query">Query Param</option>
                                        </select>
                                    </div>
                                </div>

                                {{-- None --}}
                                <div x-show="currentRequest.auth_type === 'none'">
                                    <p class="text-xs" style="color:#555;">No authentication for this request.</p>
                                </div>
                            </div>

                        </div>{{-- end tab content --}}
                    </div>{{-- end request config --}}

                    {{-- === RESPONSE PANEL (bottom 58%) === --}}
                    <div class="flex flex-col overflow-hidden" style="flex:1; min-height:0;">

                        {{-- Empty state: no request sent --}}
                        <div x-show="!response && !isLoading"
                             class="flex-1 flex items-center justify-center">
                            <div class="text-center">
                                <svg class="w-10 h-10 mx-auto mb-3" style="color:#333" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <p class="text-xs" style="color:#555;">Hit <strong style="color:#777;">Send</strong> to get a response</p>
                            </div>
                        </div>

                        {{-- Loading --}}
                        <div x-show="isLoading"
                             class="flex-1 flex items-center justify-center">
                            <div class="flex items-center gap-3" style="color:#666;">
                                <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                </svg>
                                <span class="text-sm">Sending request…</span>
                            </div>
                        </div>

                        {{-- Error state (network/connection failure) --}}
                        <div x-show="response && !response.success" class="flex flex-col overflow-hidden h-full">
                            <div class="flex items-center gap-4 px-5 py-2.5 flex-shrink-0"
                                 style="background:#252525; border-bottom:1px solid #3a3a3a;">
                                <span class="text-xs font-semibold" style="color:#ef4444;">Error</span>
                                <span class="text-xs" style="color:#555;"
                                      x-text="(response?.response_time_ms ?? 0) + ' ms'"></span>
                            </div>
                            <div class="flex-1 overflow-y-auto p-5">
                                <div class="flex items-start gap-3 p-4 rounded-lg"
                                     style="background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.25);">
                                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color:#ef4444;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                    <div>
                                        <p class="text-sm font-medium mb-1" style="color:#fca5a5;">Request Failed</p>
                                        <p x-text="response?.error" class="text-xs font-mono" style="color:#f87171; opacity:0.8;"></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Success response --}}
                        <div x-show="response && response.success" class="flex flex-col overflow-hidden h-full">

                            {{-- Status bar --}}
                            <div class="flex items-center gap-5 px-5 py-2.5 flex-shrink-0"
                                 style="background:#252525; border-bottom:1px solid #3a3a3a;">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-[9px] uppercase tracking-widest" style="color:#555;">Status</span>
                                    <span :class="statusColor(response?.status)"
                                          class="text-sm font-bold"
                                          x-text="response?.status"></span>
                                    <span class="text-[10px]" :class="statusLabel(response?.status)"
                                          x-text="statusText(response?.status)"></span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="text-[9px] uppercase tracking-widest" style="color:#555;">Time</span>
                                    <span class="text-xs" style="color:#c8c8c8;"
                                          x-text="(response?.response_time_ms ?? 0) + ' ms'"></span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="text-[9px] uppercase tracking-widest" style="color:#555;">Size</span>
                                    <span class="text-xs" style="color:#c8c8c8;"
                                          x-text="responseSize(response?.response_body)"></span>
                                </div>
                            </div>

                            {{-- Response tabs --}}
                            <div class="flex flex-shrink-0" style="background:#252525; border-bottom:1px solid #3a3a3a;">
                                <button @click="responseTab = 'body'"
                                        :style="responseTab === 'body' ? 'color:#fff; border-bottom:2px solid #e8602c;' : 'color:#777; border-bottom:2px solid transparent;'"
                                        class="px-5 py-2 text-[10px] uppercase tracking-widest font-semibold transition-colors">Body</button>
                                <button @click="responseTab = 'headers'"
                                        :style="responseTab === 'headers' ? 'color:#fff; border-bottom:2px solid #e8602c;' : 'color:#777; border-bottom:2px solid transparent;'"
                                        class="px-5 py-2 text-[10px] uppercase tracking-widest font-semibold transition-colors">Headers</button>
                            </div>

                            {{-- Response body --}}
                            <div class="flex-1 overflow-y-auto">
                                {{-- Body tab --}}
                                <div x-show="responseTab === 'body'">
                                    <pre class="p-4 text-xs font-mono whitespace-pre-wrap break-all response-body"
                                         style="tab-size:2; line-height:1.65; color:#d4d4d4;"
                                         x-html="renderResponseBody(response?.response_body, response?.response_headers)"></pre>
                                </div>

                                {{-- Headers tab --}}
                                <div x-show="responseTab === 'headers'" class="p-4">
                                    <table class="w-full" style="border-collapse:collapse;">
                                        <template x-for="[k, v] in Object.entries(response?.response_headers || {})" :key="k">
                                            <tr style="border-bottom:1px solid #2a2a2a;">
                                                <td class="py-2 pr-4 align-top w-2/5">
                                                    <span class="text-xs font-mono" style="color:#9cdcfe;" x-text="k"></span>
                                                </td>
                                                <td class="py-2 align-top">
                                                    <span class="text-xs font-mono" style="color:#ce9178;" x-text="v"></span>
                                                </td>
                                            </tr>
                                        </template>
                                        <tr x-show="Object.keys(response?.response_headers || {}).length === 0">
                                            <td colspan="2" class="py-4 text-xs text-center" style="color:#555;">No response headers</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>{{-- end response panel --}}
                </div>{{-- end split --}}
            </div>{{-- end request builder --}}
        </main>
    </div>

    {{-- ================================================================
         KV-ROW hover: show delete button
    ================================================================ --}}
    <style>
        .kv-row:hover .kv-del { opacity: 1 !important; }
        select option { background: #2c2c2c; }

        /* ---------- URL field backdrop (CSS-grid stack) ---------- */
        .url-field-wrap {
            display: grid;
            background: #1e1e1e;
            border: 1px solid #555;
            border-radius: 4px;
            transition: border-color .15s;
            overflow: hidden;
        }
        /* Shared typographic baseline — both layers MUST be identical */
        .url-field-back,
        .url-field-real {
            grid-area: 1 / 1;          /* stack in the same cell */
            padding: 8px 16px;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.875rem;
            line-height: 1.5;
            letter-spacing: 0;
            white-space: pre;
            overflow: hidden;
            box-sizing: border-box;
            width: 100%;
        }
        /* Backdrop: shows the highlighted text */
        .url-field-back {
            color: #d4d4d4;
            pointer-events: none;
            user-select: none;
        }
        /* Real input: transparent value text; only caret visible */
        .url-field-real {
            background: transparent;
            color: transparent;
            caret-color: #d4d4d4;
            border: none;
            outline: none;
        }
        .url-field-input { cursor: text; }
        .url-field-input::placeholder { color: #555; }

        /* ---------- Response body syntax tokens ---------- */
        .json-key    { color: #9cdcfe; }
        .json-str    { color: #ce9178; }
        .json-num    { color: #b5cea8; }
        .json-bool   { color: #569cd6; }
        .json-null   { color: #569cd6; }
        .json-punct  { color: #d4d4d4; }
        .xml-tag     { color: #4ec9b0; }
        .xml-bracket { color: #808080; }
        .xml-attr    { color: #9cdcfe; }
        .xml-val     { color: #ce9178; }
        .xml-comment { color: #6a9955; font-style: italic; }
    </style>

    {{-- ================================================================
         ALPINE.JS WORKSPACE COMPONENT
    ================================================================ --}}
    <script>
    function workspace() {
        return {
            // Layout
            sidebarTab: 'collections',
            requestOpen: false,
            requestTab: 'params',
            responseTab: 'body',
            userMenuOpen: false,
            envMenuOpen: false,

            // Data
            collections: [],
            environments: [],
            collectionsLoading: true,

            // Sidebar state
            expandedCollections: {},
            expandedFolders: {},
            activeRequestId: null,
            collectionMenuOpen: null,
            importNotification: null,

            // Request being built
            currentRequest: {
                name: 'New Request',
                method: 'GET',
                url: '',
                params:    [{ key: '', value: '', enabled: true }],
                headers:   [{ key: '', value: '', enabled: true }],
                body_type: 'none',
                body: '',
                body_form: [{ key: '', value: '', enabled: true }],
                auth_type: 'none',
                auth_data: { token: '', username: '', password: '', key: '', value: '', in: 'header' },
            },

            // URL field
            urlFocused: false,

            // Response
            response: null,
            isLoading: false,

            // ---- Init ----

            init() {
                this.loadCollections();
                this.loadEnvironments();
            },

            // ---- Computed ----

            get activeEnvironment() {
                return this.environments.find(e => e.is_active) || null;
            },

            get filledParamCount() {
                return (this.currentRequest.params || []).filter(p => p.key.trim()).length;
            },

            get filledHeaderCount() {
                return (this.currentRequest.headers || []).filter(h => h.key.trim()).length;
            },

            // ---- Data loading ----

            async loadCollections() {
                this.collectionsLoading = true;
                try {
                    const res  = await fetch('/collections', { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                    const json = await res.json();
                    this.collections = json.data || [];
                } catch (e) {
                    console.error('loadCollections:', e);
                    this.collections = [];
                } finally {
                    this.collectionsLoading = false;
                }
            },

            async loadEnvironments() {
                try {
                    const res  = await fetch('/environments', { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                    const json = await res.json();
                    this.environments = Array.isArray(json) ? json : [];
                } catch (e) {
                    console.error('loadEnvironments:', e);
                    this.environments = [];
                }
            },

            // ---- Sidebar expand/collapse ----

            toggleCollection(id) {
                this.expandedCollections = { ...this.expandedCollections, [id]: !this.expandedCollections[id] };
            },
            isCollectionExpanded(id) { return !!this.expandedCollections[id]; },

            toggleFolder(id) {
                this.expandedFolders = { ...this.expandedFolders, [id]: !this.expandedFolders[id] };
            },
            isFolderExpanded(id) { return !!this.expandedFolders[id]; },

            // ---- Request management ----

            blankRequest() {
                return {
                    name: 'New Request',
                    method: 'GET',
                    url: '',
                    params:    [{ key: '', value: '', enabled: true }],
                    headers:   [{ key: '', value: '', enabled: true }],
                    body_type: 'none',
                    body: '',
                    body_form: [{ key: '', value: '', enabled: true }],
                    auth_type: 'none',
                    auth_data: { token: '', username: '', password: '', key: '', value: '', in: 'header' },
                };
            },

            newRequest() {
                this.activeRequestId = null;
                this.currentRequest  = this.blankRequest();
                this.response        = null;
                this.requestTab      = 'params';
                this.requestOpen     = true;
            },

            async openRequest(requestId) {
                this.requestOpen     = true;
                this.response        = null;
                this.activeRequestId = requestId;

                try {
                    const res  = await fetch(`/requests/${requestId}`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                    const json = await res.json();
                    const d    = json.data;
                    const ad   = d.auth_data || {};

                    this.currentRequest = {
                        name:      d.name      || 'Untitled',
                        method:    d.method    || 'GET',
                        url:       d.url       || '',
                        params:    [{ key: '', value: '', enabled: true }],
                        headers:   Array.isArray(d.headers) && d.headers.length
                                       ? d.headers
                                       : [{ key: '', value: '', enabled: true }],
                        body_type: d.body_type || 'none',
                        body:      d.body      || '',
                        body_form: [{ key: '', value: '', enabled: true }],
                        auth_type: d.auth_type || 'none',
                        auth_data: {
                            token:    ad.token    || '',
                            username: ad.username || '',
                            password: ad.password || '',
                            key:      ad.key      || '',
                            value:    ad.value    || '',
                            in:       ad.in       || 'header',
                        },
                    };
                } catch (e) {
                    console.error('openRequest:', e);
                }
            },

            // ---- Send ----

            async sendRequest() {
                if (!this.currentRequest.url.trim() || this.isLoading) return;

                this.isLoading = true;
                this.response  = null;

                // Append query params to URL
                let url = this.currentRequest.url;
                const qp = this.currentRequest.params.filter(p => p.enabled && p.key.trim());
                if (qp.length) {
                    const qs = qp.map(p => encodeURIComponent(p.key) + '=' + encodeURIComponent(p.value)).join('&');
                    url += (url.includes('?') ? '&' : '?') + qs;
                }

                // Serialize form body if needed
                let body = this.currentRequest.body;
                if (['form-data', 'x-www-form-urlencoded'].includes(this.currentRequest.body_type)) {
                    body = JSON.stringify(this.currentRequest.body_form.filter(r => r.key.trim()));
                }

                const payload = {
                    method:     this.currentRequest.method,
                    url,
                    headers:    this.currentRequest.headers.filter(h => h.key.trim()),
                    body_type:  this.currentRequest.body_type,
                    body,
                    auth_type:  this.currentRequest.auth_type,
                    auth_data:  this.currentRequest.auth_data,
                    request_id: this.activeRequestId,
                };

                try {
                    const res     = await fetch('/run', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept':       'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify(payload),
                    });
                    this.response    = await res.json();
                    this.responseTab = 'body';
                } catch (e) {
                    this.response = { success: false, error: e.message, status: 0, response_time_ms: 0, response_body: '', response_headers: {} };
                } finally {
                    this.isLoading = false;
                }
            },

            // ---- Save (existing request only; full save modal is future work) ----

            async saveRequest() {
                if (!this.activeRequestId) {
                    // TODO: open "save to collection" modal
                    alert('Choose a collection to save to — save modal coming soon.');
                    return;
                }
                try {
                    await fetch(`/requests/${this.activeRequestId}`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept':       'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({
                            name:      this.currentRequest.name,
                            method:    this.currentRequest.method,
                            url:       this.currentRequest.url,
                            headers:   this.currentRequest.headers.filter(h => h.key.trim()),
                            body_type: this.currentRequest.body_type,
                            body:      this.currentRequest.body,
                            auth_type: this.currentRequest.auth_type,
                            auth_data: this.currentRequest.auth_data,
                        }),
                    });
                    await this.loadCollections(); // refresh sidebar
                } catch (e) {
                    console.error('saveRequest:', e);
                }
            },

            // ---- Environments ----

            async activateEnvironment(id) {
                try {
                    await fetch(`/environments/${id}/activate`, {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    });
                    this.envMenuOpen = false;
                    await this.loadEnvironments();
                } catch (e) { console.error('activateEnvironment:', e); }
            },

            async deactivateEnvironment() {
                try {
                    await fetch('/environments/deactivate', {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    });
                    this.envMenuOpen = false;
                    await this.loadEnvironments();
                } catch (e) { console.error('deactivateEnvironment:', e); }
            },

            // ---- Collection context menu ----

            toggleCollectionMenu(id) {
                this.collectionMenuOpen = this.collectionMenuOpen === id ? null : id;
            },

            // ---- Export ----

            exportCollection(id) {
                window.location.href = `/collections/${id}/export`;
            },

            // ---- Import ----

            importCollection() {
                this.$refs.importFileInput.value = '';
                this.$refs.importFileInput.click();
            },

            async handleImportFile(files) {
                if (!files || !files[0]) return;

                const formData = new FormData();
                formData.append('file', files[0]);

                try {
                    const res  = await fetch('/collections/import', {
                        method: 'POST',
                        headers: {
                            'Accept':       'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: formData,
                    });
                    const json = await res.json();

                    if (res.ok) {
                        this.importNotification = { ok: true, msg: `Imported "${json.data.name}" successfully.` };
                        await this.loadCollections();
                    } else {
                        this.importNotification = { ok: false, msg: json.message || 'Import failed.' };
                    }
                } catch (e) {
                    this.importNotification = { ok: false, msg: 'Network error during import.' };
                }

                // Auto-dismiss after 4 seconds
                setTimeout(() => { this.importNotification = null; }, 4000);
            },

            // ---- Delete collection ----

            async deleteCollection(id) {
                if (!confirm('Delete this collection and all its requests?')) return;
                try {
                    await fetch(`/collections/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'Accept':       'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });
                    if (this.activeRequestId !== null) {
                        // If a request from the deleted collection was open, clear it
                        this.requestOpen = false;
                        this.activeRequestId = null;
                    }
                    await this.loadCollections();
                } catch (e) {
                    console.error('deleteCollection:', e);
                }
            },

            // ---- Key-value row helpers ----

            addParam()      { this.currentRequest.params.push({ key: '', value: '', enabled: true }); },
            removeParam(i)  { this.currentRequest.params.splice(i, 1); },
            addHeader()     { this.currentRequest.headers.push({ key: '', value: '', enabled: true }); },
            removeHeader(i) { this.currentRequest.headers.splice(i, 1); },
            addFormRow()      { this.currentRequest.body_form.push({ key: '', value: '', enabled: true }); },
            removeFormRow(i)  { this.currentRequest.body_form.splice(i, 1); },

            // ---- Style helpers ----

            methodColor(method) {
                return { GET: 'text-green-400', POST: 'text-yellow-400', PUT: 'text-blue-400', PATCH: 'text-purple-400', DELETE: 'text-red-400' }[method] || 'text-gray-400';
            },

            statusColor(status) {
                if (!status) return 'text-gray-400';
                if (status < 300) return 'text-green-400';
                if (status < 400) return 'text-blue-400';
                if (status < 500) return 'text-yellow-400';
                return 'text-red-400';
            },

            statusLabel(status) {
                if (!status) return 'text-gray-600';
                if (status < 300) return 'text-green-700';
                if (status < 400) return 'text-blue-700';
                if (status < 500) return 'text-yellow-700';
                return 'text-red-700';
            },

            statusText(status) {
                const map = { 200:'OK', 201:'Created', 204:'No Content', 301:'Moved', 302:'Found', 304:'Not Modified', 400:'Bad Request', 401:'Unauthorized', 403:'Forbidden', 404:'Not Found', 405:'Method Not Allowed', 409:'Conflict', 422:'Unprocessable', 429:'Too Many Requests', 500:'Internal Server Error', 502:'Bad Gateway', 503:'Service Unavailable' };
                return map[status] ? map[status] : '';
            },

            // ---- URL {{variable}} highlight ----

            highlightUrl(url) {
                if (!url) return '';
                return this.escHtml(url).replace(
                    /\{\{([^}]*)\}\}/g,
                    '<mark style="background:rgba(232,96,44,0.18);color:#e8a07a;border-radius:2px;padding:0 1px;">{{$1}}</mark>'
                );
            },

            // ---- Response body rendering ----

            // Detect content type from response headers object
            detectContentType(headers) {
                if (!headers) return 'text';
                const entry = Object.entries(headers)
                    .find(([k]) => k.toLowerCase() === 'content-type');
                if (!entry) return 'text';
                const v = entry[1].toLowerCase();
                if (v.includes('json'))                       return 'json';
                if (v.includes('xml') || v.includes('html')) return 'xml';
                return 'text';
            },

            renderResponseBody(body, headers) {
                if (!body) return '<span style="color:#555;">— empty response —</span>';
                const type = this.detectContentType(headers);
                if (type === 'json') return this.highlightJson(body);
                if (type === 'xml')  return this.highlightXml(body);
                return this.escHtml(body);
            },

            // Escape HTML entities in a raw string
            escHtml(s) {
                return String(s)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;');
            },

            // JSON syntax highlight — character-level tokeniser (no double-wrap issues)
            highlightJson(body) {
                let fmt;
                try { fmt = JSON.stringify(JSON.parse(body), null, 2); }
                catch { return '<span class="json-punct">' + this.escHtml(body) + '</span>'; }

                let html = '';
                let i = 0;
                const len = fmt.length;

                while (i < len) {
                    const ch = fmt[i];

                    if (ch === '"') {
                        // Scan to end of JSON string (respects backslash escapes)
                        let j = i + 1;
                        while (j < len) {
                            if (fmt[j] === '\\') { j += 2; continue; }
                            if (fmt[j] === '"')  { j++; break; }
                            j++;
                        }
                        const token = fmt.slice(i, j);

                        // Peek ahead to decide: key (followed by colon) or string value
                        let k = j;
                        while (k < len && fmt[k] === ' ') k++;
                        const isKey = fmt[k] === ':';

                        html += isKey
                            ? `<span class="json-key">${this.escHtml(token)}</span>`
                            : `<span class="json-str">${this.escHtml(token)}</span>`;
                        i = j;

                    } else if (fmt.startsWith('true', i)) {
                        html += '<span class="json-bool">true</span>';  i += 4;
                    } else if (fmt.startsWith('false', i)) {
                        html += '<span class="json-bool">false</span>'; i += 5;
                    } else if (fmt.startsWith('null', i)) {
                        html += '<span class="json-null">null</span>';  i += 4;

                    } else if (ch === '-' || (ch >= '0' && ch <= '9')) {
                        // Number — scan digits, optional decimal, optional exponent
                        let j = i;
                        if (fmt[j] === '-') j++;
                        while (j < len && fmt[j] >= '0' && fmt[j] <= '9') j++;
                        if (j < len && fmt[j] === '.') {
                            j++;
                            while (j < len && fmt[j] >= '0' && fmt[j] <= '9') j++;
                        }
                        if (j < len && (fmt[j] === 'e' || fmt[j] === 'E')) {
                            j++;
                            if (j < len && (fmt[j] === '+' || fmt[j] === '-')) j++;
                            while (j < len && fmt[j] >= '0' && fmt[j] <= '9') j++;
                        }
                        html += `<span class="json-num">${fmt.slice(i, j)}</span>`;
                        i = j;

                    } else {
                        // Structural punctuation & whitespace
                        html += this.escHtml(ch);
                        i++;
                    }
                }
                return html;
            },

            // XML/HTML syntax highlight — indent then colorise each tag as a unit.
            // Processing each full tag (<name attrs>) in one regex callback avoids
            // subsequent regexes accidentally matching inside injected <span> tags.
            highlightXml(body) {
                // Step 1: basic indentation
                let fmt;
                try {
                    let depth = 0;
                    fmt = body
                        .replace(/>\s*</g, '>\n<')
                        .split('\n')
                        .map(raw => {
                            const line = raw.trim();
                            if (!line) return null;
                            if (/^<\//.test(line) || /^-->/.test(line))
                                depth = Math.max(0, depth - 1);
                            const out = '  '.repeat(depth) + line;
                            if (/^<[^/?!]/.test(line) && !line.endsWith('/>') && !/<\//.test(line))
                                depth++;
                            return out;
                        })
                        .filter(l => l !== null)
                        .join('\n');
                } catch { fmt = body; }

                // Step 2: HTML-escape the entire string
                const esc = this.escHtml(fmt);

                // Step 3: colorise in safe order
                return esc
                    // Comments (process before tags — may contain tag-like text inside)
                    .replace(
                        /(&lt;!--[\s\S]*?--&gt;)/g,
                        '<span class="xml-comment">$1</span>'
                    )
                    // Processing instructions  <?...?>
                    .replace(
                        /(&lt;\?[\s\S]*?\?&gt;)/g,
                        '<span class="xml-bracket">$1</span>'
                    )
                    // Every other tag — processed as ONE unit so attribute sub-patterns
                    // are only applied inside the captured tag content, never to our
                    // injected <span class="..."> markup.
                    // Captures: (1) open bracket+slash  (2) tag name  (3) attributes  (4) close bracket
                    // [^&]|&(?!gt;) matches any char that isn't the start of &gt;
                    .replace(
                        /(&lt;\/?)([\w][\w:.-]*)((?:[^&]|&(?!gt;))*?)(\/?&gt;)/g,
                        (_, open, tag, attrs, close) => {
                            // Colour attribute name=value pairs inside this tag only
                            const coloredAttrs = attrs
                                .replace(/([\w][\w:.-]*)=/g,
                                    '<span class="xml-attr">$1</span>=')
                                .replace(/=("(?:[^"])*")/g,
                                    '=<span class="xml-val">$1</span>');
                            return '<span class="xml-bracket">' + open + '</span>'
                                 + '<span class="xml-tag">'     + tag  + '</span>'
                                 + coloredAttrs
                                 + '<span class="xml-bracket">' + close + '</span>';
                        }
                    );
            },

            responseSize(body) {
                if (!body) return '0 B';
                const b = new Blob([body]).size;
                if (b < 1024)        return b + ' B';
                if (b < 1048576)     return (b / 1024).toFixed(1) + ' KB';
                return (b / 1048576).toFixed(1) + ' MB';
            },
        };
    }
    </script>

</div>
@endsection
