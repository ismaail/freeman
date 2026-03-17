{{-- ============================================================
     SIDEBAR
============================================================ --}}
<aside class="flex-shrink-0 flex flex-col"
       style="width:260px; background:var(--color-bg-surface); border-right:1px solid var(--color-border-subtle);">

    {{-- New Request button --}}
    <div class="p-3 flex-shrink-0">
        <button @click="newRequest()"
                class="w-full flex items-center justify-center gap-2 py-2 rounded text-sm font-medium text-white transition-colors"
                style="background:var(--color-brand);"
                onmouseover="this.style.background='var(--color-brand-hover)'" onmouseout="this.style.background='var(--color-brand)'">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            New Request
        </button>
    </div>

    {{-- Sidebar tab nav --}}
    <div class="flex flex-shrink-0" style="border-bottom:1px solid var(--color-border-subtle);">
        <button @click="sidebarTab = 'collections'"
                :style="sidebarTab === 'collections' ? 'color:#fff; border-bottom:2px solid var(--color-brand);' : 'color:var(--color-text-muted-4); border-bottom:2px solid transparent;'"
                class="flex-1 py-2.5 text-[10px] uppercase tracking-widest font-semibold transition-colors hover:text-gray-300">
            Collections
        </button>
        <button @click="sidebarTab = 'environments'"
                :style="sidebarTab === 'environments' ? 'color:#fff; border-bottom:2px solid var(--color-brand);' : 'color:var(--color-text-muted-4); border-bottom:2px solid transparent;'"
                class="flex-1 py-2.5 text-[10px] uppercase tracking-widest font-semibold transition-colors hover:text-gray-300">
            Envs
        </button>
        <button @click="sidebarTab = 'history'"
                :style="sidebarTab === 'history' ? 'color:#fff; border-bottom:2px solid var(--color-brand);' : 'color:var(--color-text-muted-4); border-bottom:2px solid transparent;'"
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
                <svg class="w-5 h-5 animate-spin" style="color:var(--color-border-input)" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
            </div>

            {{-- Empty --}}
            <div x-show="!collectionsLoading && collections.length === 0"
                 class="flex flex-col items-center justify-center py-10 px-4 text-center">
                <svg class="w-9 h-9 mb-3" style="color:var(--color-border-menu)" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                </svg>
                <p class="text-xs" style="color:var(--color-text-muted-5)">No collections yet</p>
                <p class="text-[10px] mt-1" style="color:var(--color-border-input)">Save a request to create one</p>
            </div>

            {{-- Collections toolbar: Import button --}}
            <div x-show="!collectionsLoading" class="flex items-center justify-between px-3 pt-2 pb-1">
                <span class="text-[9px] uppercase tracking-widest font-semibold" style="color:var(--color-text-muted-7);">Collections</span>
                <button @click="importCollection()"
                        class="text-[10px] transition-colors"
                        style="color:var(--color-text-muted-5);"
                        onmouseover="this.style.color='var(--color-brand)'" onmouseout="this.style.color='var(--color-text-muted-5)'">
                    + Import
                </button>
            </div>

            {{-- Import notification toast --}}
            <div x-show="importNotification"
                 x-cloak
                 x-transition.opacity
                 class="mx-3 mb-2 px-3 py-2 rounded text-[11px]"
                 :style="importNotification?.ok
                     ? 'background:var(--color-success-tint-bg); border:1px solid var(--color-success-tint-border); color:var(--color-success);'
                     : 'background:var(--color-danger-tint-bg2); border:1px solid var(--color-danger-tint-border); color:var(--color-danger-light);'"
                 x-text="importNotification?.msg">
            </div>

            {{-- Collections list --}}
            <div x-show="!collectionsLoading">
                <template x-for="col in collections" :key="col.id">
                    <div>
                        {{-- Collection header --}}
                        <div class="relative flex items-center gap-1.5 px-3 py-2 select-none transition-colors group"
                             onmouseover="this.style.background='var(--color-bg-hover-row)'" onmouseout="this.style.background='transparent'">
                            {{-- Clickable area: toggles expand --}}
                            <div @click="toggleCollection(col.id)" class="flex items-center gap-1.5 flex-1 min-w-0 cursor-pointer">
                                <svg class="w-2.5 h-2.5 flex-shrink-0 transition-transform duration-150"
                                     :style="isCollectionExpanded(col.id) ? 'transform:rotate(90deg); color:var(--color-text-muted-3)' : 'color:var(--color-border-input)'"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                </svg>
                                <svg class="w-3.5 h-3.5 flex-shrink-0" style="color:var(--color-brand)" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
                                </svg>
                                <span x-text="col.name"
                                      class="text-xs font-semibold truncate flex-1"
                                      style="color:var(--color-text-input)"></span>
                            </div>
                            {{-- Count badge (hidden when menu visible) --}}
                            <span x-show="(col.requests||[]).length + (col.folders||[]).length > 0 && collectionMenuOpen !== col.id"
                                  x-text="(col.requests||[]).length + (col.folders||[]).length"
                                  class="text-[9px] px-1.5 py-0.5 rounded-full flex-shrink-0 group-hover:hidden"
                                  style="background:var(--color-bg-badge); color:var(--color-text-muted-4)"></span>
                            {{-- Three-dot context menu --}}
                            <div class="relative flex-shrink-0" @click.stop>
                                <button @click="toggleCollectionMenu(col.id)"
                                        class="p-1 rounded transition-opacity flex-shrink-0 opacity-0 group-hover:opacity-100"
                                        :class="collectionMenuOpen === col.id ? '!opacity-100' : ''"
                                        style="color:var(--color-text-muted-4);"
                                        onmouseover="this.style.color='var(--color-text-primary)'" onmouseout="this.style.color='var(--color-text-muted-4)'">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                                    </svg>
                                </button>
                                <div x-show="collectionMenuOpen === col.id"
                                     x-cloak
                                     @click.outside="collectionMenuOpen = null"
                                     class="absolute right-0 top-full mt-1 w-40 rounded shadow-2xl z-50 py-1"
                                     style="background:var(--color-bg-elevated); border:1px solid var(--color-border-menu);">
                                    <button @click="exportCollection(col.id); collectionMenuOpen = null"
                                            class="w-full flex items-center gap-2.5 px-3 py-2 text-xs text-left transition-colors"
                                            style="color:var(--color-text-primary)"
                                            onmouseover="this.style.background='var(--color-bg-btn)'" onmouseout="this.style.background='transparent'">
                                        <svg class="w-3.5 h-3.5 flex-shrink-0" style="color:var(--color-text-muted-3)" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                        Export JSON
                                    </button>
                                    <div style="border-top:1px solid var(--color-border-subtle); margin:4px 0;"></div>
                                    <button @click="deleteCollection(col.id); collectionMenuOpen = null"
                                            class="w-full flex items-center gap-2.5 px-3 py-2 text-xs text-left transition-colors"
                                            style="color:var(--color-danger)"
                                            onmouseover="this.style.background='var(--color-bg-danger-hover)'" onmouseout="this.style.background='transparent'">
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
                                     :style="activeRequestId === req.id ? 'background:var(--color-bg-active-item)' : ''"
                                     onmouseover="if(this.getAttribute('data-active')!=='1') this.style.background='var(--color-bg-hover-subtle)'"
                                     onmouseout="if(this.getAttribute('data-active')!=='1') this.style.background=''"
                                     :data-active="activeRequestId === req.id ? '1' : '0'">
                                    <span :class="methodColor(req.method)"
                                          class="text-[9px] font-bold font-mono flex-shrink-0"
                                          style="width:36px; text-align:right"
                                          x-text="req.method"></span>
                                    <span x-text="req.name" class="text-xs truncate" style="color:var(--color-text-secondary)"></span>
                                </div>
                            </template>

                            {{-- Folders --}}
                            <template x-for="folder in (col.folders || [])" :key="'fold-' + folder.id">
                                <div>
                                    {{-- Folder header --}}
                                    <div @click="toggleFolder(folder.id)"
                                         class="flex items-center gap-1.5 pl-6 pr-3 py-1.5 cursor-pointer select-none transition-colors"
                                         onmouseover="this.style.background='var(--color-bg-hover-subtle)'" onmouseout="this.style.background='transparent'">
                                        <svg class="w-2.5 h-2.5 flex-shrink-0 transition-transform duration-150"
                                             :style="isFolderExpanded(folder.id) ? 'transform:rotate(90deg); color:var(--color-text-muted-3)' : 'color:var(--color-border-input)'"
                                             fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                        </svg>
                                        <svg class="w-3.5 h-3.5 flex-shrink-0" style="color:var(--color-folder)" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
                                        </svg>
                                        <span x-text="folder.name" class="text-xs truncate flex-1" style="color:var(--color-text-muted-1)"></span>
                                    </div>

                                    {{-- Folder requests --}}
                                    <div x-show="isFolderExpanded(folder.id)">
                                        <template x-for="req in (folder.requests || [])" :key="'fr-' + req.id">
                                            <div @click="openRequest(req.id)"
                                                 class="flex items-center gap-2 pl-12 pr-3 py-1.5 cursor-pointer transition-colors"
                                                 :style="activeRequestId === req.id ? 'background:var(--color-bg-active-item)' : ''"
                                                 onmouseover="if(this.getAttribute('data-active')!=='1') this.style.background='var(--color-bg-hover-subtle)'"
                                                 onmouseout="if(this.getAttribute('data-active')!=='1') this.style.background=''"
                                                 :data-active="activeRequestId === req.id ? '1' : '0'">
                                                <span :class="methodColor(req.method)"
                                                      class="text-[9px] font-bold font-mono flex-shrink-0"
                                                      style="width:36px; text-align:right"
                                                      x-text="req.method"></span>
                                                <span x-text="req.name" class="text-xs truncate" style="color:var(--color-text-secondary)"></span>
                                            </div>
                                        </template>
                                        <div x-show="!(folder.requests || []).length"
                                             class="pl-12 pr-3 py-1.5 text-[10px]" style="color:var(--color-border-input)">
                                            Empty folder
                                        </div>
                                    </div>
                                </div>
                            </template>

                            {{-- Truly empty collection --}}
                            <div x-show="!(col.requests||[]).length && !(col.folders||[]).length"
                                 class="pl-8 pr-3 py-2 text-[10px]" style="color:var(--color-border-input)">
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
                     style="border-bottom:1px solid var(--color-bg-hover-row);"
                     onmouseover="this.style.background='var(--color-bg-hover-row)'" onmouseout="this.style.background='transparent'">
                    <span class="w-2 h-2 rounded-full flex-shrink-0"
                          :style="env.is_active ? 'background:var(--color-success)' : 'background:var(--color-border-menu)'"></span>
                    <span x-text="env.name" class="text-xs flex-1 truncate" style="color:var(--color-text-secondary)"></span>
                    <button x-show="!env.is_active"
                            @click="activateEnvironment(env.id)"
                            class="text-[10px] transition-colors flex-shrink-0"
                            style="color:var(--color-text-muted-4)"
                            onmouseover="this.style.color='var(--color-brand)'" onmouseout="this.style.color='var(--color-text-muted-4)'">
                        Activate
                    </button>
                    <span x-show="env.is_active"
                          class="text-[10px] font-semibold flex-shrink-0"
                          style="color:var(--color-success)">
                        Active
                    </span>
                </div>
            </template>
            <div x-show="environments.length === 0"
                 class="flex flex-col items-center justify-center py-10 px-4 text-center">
                <svg class="w-9 h-9 mb-3" style="color:var(--color-border-menu)" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                <p class="text-xs" style="color:var(--color-text-muted-5)">No environments</p>
            </div>
        </div>

        {{-- ---- HISTORY TAB ---- --}}
        <div x-show="sidebarTab === 'history'">
            <div class="flex flex-col items-center justify-center py-10 px-4 text-center">
                <svg class="w-9 h-9 mb-3" style="color:var(--color-border-menu)" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-xs" style="color:var(--color-text-muted-5)">History coming soon</p>
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
