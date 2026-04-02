{{-- ================================================================
     SAVE REQUEST TO COLLECTION MODAL
================================================================ --}}
<div x-show="saveModal.open"
     x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center"
     style="background:rgba(0,0,0,0.6);"
     @keydown.escape.window="saveModal.open = false">

    <div class="flex flex-col rounded-lg shadow-2xl w-full mx-4"
         style="background:var(--color-bg-elevated); border:1px solid var(--color-border-menu); max-width:420px; max-height:90vh;"
         @click.stop>

        {{-- Header --}}
        <div class="flex items-center justify-between px-5 py-4 flex-shrink-0"
             style="border-bottom:1px solid var(--color-border-subtle);">
            <h2 class="text-sm font-semibold" style="color:var(--color-text-primary);">Save Request</h2>
            <button @click="saveModal.open = false"
                    class="p-1.5 rounded transition-colors"
                    style="color:var(--color-text-muted-4);"
                    onmouseover="this.style.color='var(--color-text-primary)'; this.style.background='var(--color-bg-btn)'"
                    onmouseout="this.style.color='var(--color-text-muted-4)'; this.style.background='transparent'">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Body --}}
        <div class="flex flex-col min-h-0 flex-1">

            {{-- Request name --}}
            <div class="px-5 pt-4 pb-3 flex-shrink-0">
                <label class="block text-[11px] font-medium mb-1.5" style="color:var(--color-text-muted-3);">Request Name</label>
                <input x-model="saveModal.name"
                       type="text"
                       placeholder="My Request"
                       class="w-full rounded px-3 py-2 text-xs focus:outline-none"
                       style="background:var(--color-bg-base); border:1px solid var(--color-border-subtle); color:var(--color-text-input);"
                       onfocus="this.style.borderColor='var(--color-border-input)'"
                       onblur="this.style.borderColor='var(--color-border-subtle)'"
                       @keydown.enter.prevent="confirmSaveRequest()"/>
            </div>

            {{-- Folder browser --}}
            <div class="flex flex-col min-h-0 flex-1 mx-5 mb-4 rounded overflow-hidden"
                 style="border:1px solid var(--color-border-subtle);">

                {{-- Breadcrumb --}}
                <div class="flex items-center gap-1 px-3 py-2 flex-shrink-0 flex-wrap"
                     style="background:var(--color-bg-base); border-bottom:1px solid var(--color-border-subtle); min-height:36px;">
                    <button @click="saveModalNavigateTo(-1)"
                            class="text-[11px] transition-colors flex-shrink-0"
                            :style="!saveModal.path.length ? 'color:var(--color-text-primary); font-weight:600;' : 'color:var(--color-text-muted-4);'"
                            onmouseover="this.style.color='var(--color-brand)'"
                            @mouseleave="$el.style.color = ''">
                        Collections
                    </button>
                    <template x-for="(crumb, i) in saveModal.path" :key="'crumb-' + i">
                        <span class="flex items-center gap-1 flex-shrink-0">
                            <svg class="w-2.5 h-2.5 flex-shrink-0" style="color:var(--color-border-input)" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                            </svg>
                            <button @click="saveModalNavigateTo(i)"
                                    x-text="crumb.name"
                                    class="text-[11px] transition-colors max-w-[120px] truncate"
                                    :style="i === saveModal.path.length - 1 ? 'color:var(--color-text-primary); font-weight:600;' : 'color:var(--color-text-muted-4);'"
                                    onmouseover="this.style.color='var(--color-brand)'"
                                    onmouseout="this.style.color=''">
                            </button>
                        </span>
                    </template>
                </div>

                {{-- Item list --}}
                <div class="overflow-y-auto flex-1" style="min-height:180px; max-height:260px;">

                    {{-- Empty state --}}
                    <div x-show="saveModalBrowserItems.length === 0"
                         class="flex flex-col items-center justify-center h-full py-8 px-4 text-center">
                        <svg class="w-7 h-7 mb-2" style="color:var(--color-border-menu)" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                        </svg>
                        <p class="text-[11px]" style="color:var(--color-text-muted-5)"
                           x-text="saveModal.path.length ? 'No subfolders here' : 'No collections yet'"></p>
                    </div>

                    {{-- Items --}}
                    <template x-for="item in saveModalBrowserItems" :key="item.type + '-' + item.id">
                        <div @click="saveModalNavigateInto(item)"
                             class="flex items-center gap-2.5 px-4 py-2.5 cursor-pointer transition-colors select-none"
                             onmouseover="this.style.background='var(--color-bg-hover-row)'" onmouseout="this.style.background='transparent'">

                            {{-- Collection icon --}}
                            <template x-if="item.type === 'collection'">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" style="color:var(--color-brand)" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
                                </svg>
                            </template>

                            {{-- Folder icon --}}
                            <template x-if="item.type === 'folder'">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" style="color:var(--color-folder)" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
                                </svg>
                            </template>

                            <span x-text="item.name" class="text-xs flex-1 truncate" style="color:var(--color-text-secondary)"></span>

                            {{-- Chevron if has children --}}
                            <svg x-show="item.hasChildren"
                                 class="w-3 h-3 flex-shrink-0" style="color:var(--color-text-muted-5)"
                                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </template>

                </div>
            </div>

            {{-- Error --}}
            <p x-show="saveModal.error" x-cloak x-text="saveModal.error"
               class="px-5 pb-3 text-[11px] flex-shrink-0" style="color:var(--color-danger);"></p>

        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-between gap-2 px-5 py-3 flex-shrink-0"
             style="border-top:1px solid var(--color-border-subtle);">

            {{-- Current save location hint --}}
            <p class="text-[11px] truncate flex-1 mr-2" style="color:var(--color-text-muted-5)">
                <template x-if="saveModal.path.length">
                    <span>Saving in: <span x-text="saveModal.path[saveModal.path.length - 1].name"
                                           style="color:var(--color-text-muted-3); font-weight:500;"></span></span>
                </template>
                <template x-if="!saveModal.path.length">
                    <span>Select a location</span>
                </template>
            </p>

            <div class="flex items-center gap-2 flex-shrink-0">
                <button @click="saveModal.open = false"
                        class="px-3 py-1.5 rounded text-xs transition-colors"
                        style="color:var(--color-text-muted-3); background:transparent;"
                        onmouseover="this.style.background='var(--color-bg-btn)'" onmouseout="this.style.background='transparent'">
                    Cancel
                </button>
                <button @click="confirmSaveRequest()"
                        :disabled="saveModal.saving || !saveModal.collectionId"
                        class="px-4 py-1.5 rounded text-xs font-medium text-white transition-colors disabled:opacity-40"
                        style="background:var(--color-brand);"
                        onmouseover="this.style.background='var(--color-brand-hover)'" onmouseout="this.style.background='var(--color-brand)'">
                    <span x-text="saveModal.saving ? 'Saving…' : 'Save here'"></span>
                </button>
            </div>
        </div>

    </div>
</div>
