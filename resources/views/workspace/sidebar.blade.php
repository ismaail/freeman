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

    {{-- Sidebar scrollable content --}}
    <div class="flex-1 overflow-y-auto">

        <div>

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

            {{-- Collections toolbar: Add button (dropdown) --}}
            <div x-show="!collectionsLoading" class="flex items-center justify-between px-3 pt-2 pb-1">
                <span class="text-[9px] uppercase tracking-widest font-semibold" style="color:var(--color-text-muted-7);">Collections</span>
                <div class="relative" @click.outside="addCollectionMenuOpen = false">
                    <button @click="addCollectionMenuOpen = !addCollectionMenuOpen"
                            class="text-[10px] transition-colors"
                            style="color:var(--color-text-muted-5);"
                            onmouseover="this.style.color='var(--color-brand)'" onmouseout="this.style.color='var(--color-text-muted-5)'">
                        + Add
                    </button>
                    <div x-show="addCollectionMenuOpen"
                         x-cloak
                         class="absolute right-0 top-full mt-1 w-40 rounded shadow-2xl z-50 py-1"
                         style="background:var(--color-bg-elevated); border:1px solid var(--color-border-menu);">
                        <button @click="addCollectionMenuOpen = false; newCollectionModal = true; newCollectionName = ''"
                                class="w-full flex items-center gap-2.5 px-3 py-2 text-xs text-left transition-colors"
                                style="color:var(--color-text-primary)"
                                onmouseover="this.style.background='var(--color-bg-btn)'" onmouseout="this.style.background='transparent'">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" style="color:var(--color-text-muted-3)" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                            </svg>
                            New Collection
                        </button>
                        <button @click="addCollectionMenuOpen = false; importCollection()"
                                class="w-full flex items-center gap-2.5 px-3 py-2 text-xs text-left transition-colors"
                                style="color:var(--color-text-primary)"
                                onmouseover="this.style.background='var(--color-bg-btn)'" onmouseout="this.style.background='transparent'">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" style="color:var(--color-text-muted-3)" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l4 4m0 0l4-4m-4 4V4"/>
                            </svg>
                            Import
                        </button>
                    </div>
                </div>
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
                                    <button @click="openNewFolderModal(col.id); toggleCollection(col.id); collectionMenuOpen = null"
                                            class="w-full flex items-center gap-2.5 px-3 py-2 text-xs text-left transition-colors"
                                            style="color:var(--color-text-primary)"
                                            onmouseover="this.style.background='var(--color-bg-btn)'" onmouseout="this.style.background='transparent'">
                                        <svg class="w-3.5 h-3.5 flex-shrink-0" style="color:var(--color-text-muted-3)" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                                        </svg>
                                        Add Folder
                                    </button>
                                    <button @click="openRenameCollectionModal(col.id, col.name)"
                                            class="w-full flex items-center gap-2.5 px-3 py-2 text-xs text-left transition-colors"
                                            style="color:var(--color-text-primary)"
                                            onmouseover="this.style.background='var(--color-bg-btn)'" onmouseout="this.style.background='transparent'">
                                        <svg class="w-3.5 h-3.5 flex-shrink-0" style="color:var(--color-text-muted-3)" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Rename
                                    </button>
                                    <button @click="openCollectionVariables(col.id, col.name); collectionMenuOpen = null"
                                            class="w-full flex items-center gap-2.5 px-3 py-2 text-xs text-left transition-colors"
                                            style="color:var(--color-text-primary)"
                                            onmouseover="this.style.background='var(--color-bg-btn)'" onmouseout="this.style.background='transparent'">
                                        <svg class="w-3.5 h-3.5 flex-shrink-0" style="color:var(--color-text-muted-3)" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                        </svg>
                                        Variables
                                    </button>
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

                            {{-- Flat depth-first tree: requests + folders at all nesting levels --}}
                            <template x-for="item in flatCollectionTree(col)" :key="item.type + '-' + (item.type === 'folder' ? item.folder.id : item.req.id) + '-d' + item.depth">
                                <div>

                                {{-- Request row --}}
                                <template x-if="item.type === 'request'">
                                    <div @click="openRequest(item.req.id)"
                                         class="flex items-center gap-2 pr-3 py-1.5 cursor-pointer transition-colors"
                                         :style="'padding-left:' + (32 + item.depth * 16) + 'px;' + (activeTab?.requestId === item.req.id ? 'background:var(--color-bg-active-item)' : '')"
                                         onmouseover="if(this.getAttribute('data-active')!=='1') this.style.background='var(--color-bg-hover-subtle)'"
                                         onmouseout="if(this.getAttribute('data-active')!=='1') this.style.background=''"
                                         :data-active="activeTab?.requestId === item.req.id ? '1' : '0'">
                                        <span :class="methodColor(item.req.method)"
                                              class="text-[9px] font-bold font-mono flex-shrink-0"
                                              style="width:36px; text-align:right"
                                              x-text="item.req.method"></span>
                                        <span x-text="item.req.name" class="text-xs truncate" style="color:var(--color-text-secondary)"></span>
                                    </div>
                                </template>

                                {{-- Folder row --}}
                                <template x-if="item.type === 'folder'">
                                    <div class="relative group select-none"
                                         @click.outside="folderMenuOpen === item.folder.id && (folderMenuOpen = null)">
                                        <div class="flex items-center gap-1.5 pr-3 py-1.5 cursor-pointer transition-colors"
                                             :style="'padding-left:' + (24 + item.depth * 16) + 'px'"
                                             onmouseover="this.parentElement.style.background='var(--color-bg-hover-subtle)'"
                                             onmouseout="this.parentElement.style.background='transparent'">
                                            {{-- Toggle arrow --}}
                                            <div @click="toggleFolder(item.folder.id)" class="flex items-center gap-1.5 flex-1 min-w-0">
                                                <svg class="w-2.5 h-2.5 flex-shrink-0 transition-transform duration-150"
                                                     :style="isFolderExpanded(item.folder.id) ? 'transform:rotate(90deg); color:var(--color-text-muted-3)' : 'color:var(--color-border-input)'"
                                                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                                </svg>
                                                <svg class="w-3.5 h-3.5 flex-shrink-0" style="color:var(--color-folder)" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
                                                </svg>
                                                <span x-text="item.folder.name" class="text-xs truncate flex-1" style="color:var(--color-text-muted-1)"></span>
                                            </div>
                                            {{-- Folder 3-dot menu button --}}
                                            <div class="relative flex-shrink-0" @click.stop>
                                                <button @click="toggleFolderMenu(item.folder.id)"
                                                        class="p-1 rounded transition-opacity opacity-0 group-hover:opacity-100"
                                                        :class="folderMenuOpen === item.folder.id ? '!opacity-100' : ''"
                                                        style="color:var(--color-text-muted-4);"
                                                        onmouseover="this.style.color='var(--color-text-primary)'" onmouseout="this.style.color='var(--color-text-muted-4)'">
                                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                                                    </svg>
                                                </button>
                                                {{-- Folder context menu --}}
                                                <div x-show="folderMenuOpen === item.folder.id"
                                                     x-cloak
                                                     class="absolute right-0 top-full mt-1 w-44 rounded shadow-2xl z-50 py-1"
                                                     style="background:var(--color-bg-elevated); border:1px solid var(--color-border-menu);">
                                                    <button @click="openNewFolderModal(item.collectionId, item.folder.id, item.folder.name)"
                                                            class="w-full flex items-center gap-2.5 px-3 py-2 text-xs text-left transition-colors"
                                                            style="color:var(--color-text-primary)"
                                                            onmouseover="this.style.background='var(--color-bg-btn)'" onmouseout="this.style.background='transparent'">
                                                        <svg class="w-3.5 h-3.5 flex-shrink-0" style="color:var(--color-text-muted-3)" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                                                        </svg>
                                                        Add Subfolder
                                                    </button>
                                                    <button @click="openRenameFolderModal(item.folder.id, item.collectionId, item.folder.name)"
                                                            class="w-full flex items-center gap-2.5 px-3 py-2 text-xs text-left transition-colors"
                                                            style="color:var(--color-text-primary)"
                                                            onmouseover="this.style.background='var(--color-bg-btn)'" onmouseout="this.style.background='transparent'">
                                                        <svg class="w-3.5 h-3.5 flex-shrink-0" style="color:var(--color-text-muted-3)" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                        </svg>
                                                        Rename
                                                    </button>
                                                    <div style="border-top:1px solid var(--color-border-subtle); margin:4px 0;"></div>
                                                    <button @click="deleteFolder(item.folder.id, item.collectionId)"
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
                                    </div>
                                </template>

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


    </div>{{-- end sidebar scroll --}}

    {{-- Hidden file input for collection import --}}
    <input type="file"
           x-ref="importFileInput"
           @change="handleImportFile($event.target.files)"
           accept=".json,application/json"
           class="hidden">

    {{-- New Folder Modal --}}
    <div x-show="folderModal.open"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center"
         style="background:rgba(0,0,0,0.6);"
         @keydown.escape.window="folderModal.open = false">
        <div class="w-80 rounded-lg shadow-2xl p-5"
             style="background:var(--color-bg-elevated); border:1px solid var(--color-border-menu);"
             @click.stop>
            <h3 class="text-sm font-semibold mb-1" style="color:var(--color-text-input)"
                x-text="folderModal.parentFolderId ? 'New Subfolder' : 'New Folder'"></h3>
            <p x-show="folderModal.parentFolderName"
               class="text-[11px] mb-3 truncate"
               style="color:var(--color-text-muted-4)"
               x-text="'in ' + folderModal.parentFolderName"></p>
            <div x-show="!folderModal.parentFolderName" class="mb-3"></div>
            <input x-ref="newFolderNameInput"
                   x-model="folderModal.name"
                   @keydown.enter="createFolder()"
                   type="text"
                   placeholder="Folder name"
                   class="w-full px-3 py-2 rounded text-sm outline-none"
                   style="background:var(--color-bg-input); border:1px solid var(--color-border-input); color:var(--color-text-input);"
                   x-init="$watch('folderModal.open', v => { if (v) $nextTick(() => $refs.newFolderNameInput && $refs.newFolderNameInput.focus()) })">
            <div x-show="folderModal.error" x-cloak class="mt-2 text-[11px]" style="color:var(--color-danger)" x-text="folderModal.error"></div>
            <div class="flex justify-end gap-2 mt-4">
                <button @click="folderModal.open = false"
                        class="px-3 py-1.5 rounded text-xs transition-colors"
                        style="color:var(--color-text-muted-4); background:transparent;"
                        onmouseover="this.style.background='var(--color-bg-btn)'" onmouseout="this.style.background='transparent'">
                    Cancel
                </button>
                <button @click="createFolder()"
                        :disabled="folderModal.loading || !folderModal.name.trim()"
                        class="px-3 py-1.5 rounded text-xs font-medium text-white transition-colors disabled:opacity-50"
                        style="background:var(--color-brand);"
                        onmouseover="this.style.background='var(--color-brand-hover)'" onmouseout="this.style.background='var(--color-brand)'">
                    <span x-show="!folderModal.loading">Create</span>
                    <span x-show="folderModal.loading">Creating…</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Rename Folder Modal --}}
    <div x-show="renameFolderModal.open"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center"
         style="background:rgba(0,0,0,0.6);"
         @keydown.escape.window="renameFolderModal.open = false">
        <div class="w-80 rounded-lg shadow-2xl p-5"
             style="background:var(--color-bg-elevated); border:1px solid var(--color-border-menu);"
             @click.stop>
            <h3 class="text-sm font-semibold mb-4" style="color:var(--color-text-input)">Rename Folder</h3>
            <input x-ref="renameFolderNameInput"
                   x-model="renameFolderModal.name"
                   @keydown.enter="saveRenameFolder()"
                   type="text"
                   placeholder="Folder name"
                   class="w-full px-3 py-2 rounded text-sm outline-none"
                   style="background:var(--color-bg-input); border:1px solid var(--color-border-input); color:var(--color-text-input);"
                   x-init="$watch('renameFolderModal.open', v => { if (v) $nextTick(() => $refs.renameFolderNameInput && $refs.renameFolderNameInput.focus()) })">
            <div x-show="renameFolderModal.error" x-cloak class="mt-2 text-[11px]" style="color:var(--color-danger)" x-text="renameFolderModal.error"></div>
            <div class="flex justify-end gap-2 mt-4">
                <button @click="renameFolderModal.open = false"
                        class="px-3 py-1.5 rounded text-xs transition-colors"
                        style="color:var(--color-text-muted-4); background:transparent;"
                        onmouseover="this.style.background='var(--color-bg-btn)'" onmouseout="this.style.background='transparent'">
                    Cancel
                </button>
                <button @click="saveRenameFolder()"
                        :disabled="renameFolderModal.loading || !renameFolderModal.name.trim()"
                        class="px-3 py-1.5 rounded text-xs font-medium text-white transition-colors disabled:opacity-50"
                        style="background:var(--color-brand);"
                        onmouseover="this.style.background='var(--color-brand-hover)'" onmouseout="this.style.background='var(--color-brand)'">
                    <span x-show="!renameFolderModal.loading">Save</span>
                    <span x-show="renameFolderModal.loading">Saving…</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Rename Collection Modal --}}
    <div x-show="renameCollectionModal.open"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center"
         style="background:rgba(0,0,0,0.6);"
         @keydown.escape.window="renameCollectionModal.open = false">
        <div class="w-80 rounded-lg shadow-2xl p-5"
             style="background:var(--color-bg-elevated); border:1px solid var(--color-border-menu);"
             @click.stop>
            <h3 class="text-sm font-semibold mb-4" style="color:var(--color-text-input)">Rename Collection</h3>
            <input x-ref="renameCollectionNameInput"
                   x-model="renameCollectionModal.name"
                   @keydown.enter="saveRenameCollection()"
                   type="text"
                   placeholder="Collection name"
                   class="w-full px-3 py-2 rounded text-sm outline-none"
                   style="background:var(--color-bg-input); border:1px solid var(--color-border-input); color:var(--color-text-input);"
                   x-init="$watch('renameCollectionModal.open', v => { if (v) $nextTick(() => $refs.renameCollectionNameInput && $refs.renameCollectionNameInput.focus()) })">
            <div x-show="renameCollectionModal.error" x-cloak class="mt-2 text-[11px]" style="color:var(--color-danger)" x-text="renameCollectionModal.error"></div>
            <div class="flex justify-end gap-2 mt-4">
                <button @click="renameCollectionModal.open = false"
                        class="px-3 py-1.5 rounded text-xs transition-colors"
                        style="color:var(--color-text-muted-4); background:transparent;"
                        onmouseover="this.style.background='var(--color-bg-btn)'" onmouseout="this.style.background='transparent'">
                    Cancel
                </button>
                <button @click="saveRenameCollection()"
                        :disabled="renameCollectionModal.loading || !renameCollectionModal.name.trim()"
                        class="px-3 py-1.5 rounded text-xs font-medium text-white transition-colors disabled:opacity-50"
                        style="background:var(--color-brand);"
                        onmouseover="this.style.background='var(--color-brand-hover)'" onmouseout="this.style.background='var(--color-brand)'">
                    <span x-show="!renameCollectionModal.loading">Save</span>
                    <span x-show="renameCollectionModal.loading">Saving…</span>
                </button>
            </div>
        </div>
    </div>

    {{-- New Collection Modal --}}
    <div x-show="newCollectionModal"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center"
         style="background:rgba(0,0,0,0.6);"
         @keydown.escape.window="newCollectionModal = false">
        <div class="w-80 rounded-lg shadow-2xl p-5"
             style="background:var(--color-bg-elevated); border:1px solid var(--color-border-menu);"
             @click.stop>
            <h3 class="text-sm font-semibold mb-4" style="color:var(--color-text-input)">New Collection</h3>
            <input x-ref="newCollectionNameInput"
                   x-model="newCollectionName"
                   @keydown.enter="createCollection()"
                   type="text"
                   placeholder="Collection name"
                   class="w-full px-3 py-2 rounded text-sm outline-none"
                   style="background:var(--color-bg-input); border:1px solid var(--color-border-input); color:var(--color-text-input);"
                   x-init="$watch('newCollectionModal', v => { if (v) $nextTick(() => $refs.newCollectionNameInput.focus()) })">
            <div x-show="newCollectionError" x-cloak class="mt-2 text-[11px]" style="color:var(--color-danger)" x-text="newCollectionError"></div>
            <div class="flex justify-end gap-2 mt-4">
                <button @click="newCollectionModal = false; newCollectionError = null"
                        class="px-3 py-1.5 rounded text-xs transition-colors"
                        style="color:var(--color-text-muted-4); background:transparent;"
                        onmouseover="this.style.background='var(--color-bg-btn)'" onmouseout="this.style.background='transparent'">
                    Cancel
                </button>
                <button @click="createCollection()"
                        :disabled="newCollectionLoading || !newCollectionName.trim()"
                        class="px-3 py-1.5 rounded text-xs font-medium text-white transition-colors disabled:opacity-50"
                        style="background:var(--color-brand);"
                        onmouseover="this.style.background='var(--color-brand-hover)'" onmouseout="this.style.background='var(--color-brand)'">
                    <span x-show="!newCollectionLoading">Create</span>
                    <span x-show="newCollectionLoading">Creating…</span>
                </button>
            </div>
        </div>
    </div>

</aside>
