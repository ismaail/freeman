{{-- ================================================================
     SAVE REQUEST TO COLLECTION MODAL
================================================================ --}}
<div x-show="saveModal.open"
     x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center"
     style="background:rgba(0,0,0,0.6);"
     @keydown.escape.window="saveModal.open = false">

    <div class="flex flex-col rounded-lg shadow-2xl w-full max-w-sm mx-4"
         style="background:var(--color-bg-elevated); border:1px solid var(--color-border-menu);"
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
        <div class="px-5 py-4 flex flex-col gap-4">

            {{-- Request name --}}
            <div>
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

            {{-- Collection --}}
            <div>
                <label class="block text-[11px] font-medium mb-1.5" style="color:var(--color-text-muted-3);">Collection</label>
                <select x-model="saveModal.collectionId"
                        @change="saveModal.folderId = null"
                        class="w-full rounded px-3 py-2 text-xs focus:outline-none"
                        style="background:var(--color-bg-base); border:1px solid var(--color-border-subtle); color:var(--color-text-input);">
                    <option value="" disabled selected style="color:var(--color-text-muted-4);">— Select a collection —</option>
                    <template x-for="col in collections" :key="col.id">
                        <option :value="col.id" x-text="col.name"></option>
                    </template>
                </select>
            </div>

            {{-- Folder (shown only when a collection with folders is selected) --}}
            <div x-show="saveModalFolders.length > 0">
                <label class="block text-[11px] font-medium mb-1.5" style="color:var(--color-text-muted-3);">Folder <span style="color:var(--color-text-muted-5);">(optional)</span></label>
                <select x-model="saveModal.folderId"
                        class="w-full rounded px-3 py-2 text-xs focus:outline-none"
                        style="background:var(--color-bg-base); border:1px solid var(--color-border-subtle); color:var(--color-text-input);">
                    <option value="" style="color:var(--color-text-muted-4);">— No folder —</option>
                    <template x-for="f in saveModalFolders" :key="f.id">
                        <option :value="f.id" x-text="f.name"></option>
                    </template>
                </select>
            </div>

            {{-- Error --}}
            <p x-show="saveModal.error" x-text="saveModal.error"
               class="text-[11px]" style="color:var(--color-danger);"></p>

        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-end gap-2 px-5 py-3 flex-shrink-0"
             style="border-top:1px solid var(--color-border-subtle);">
            <button @click="saveModal.open = false"
                    class="px-3 py-1.5 rounded text-xs transition-colors"
                    style="color:var(--color-text-muted-3); background:transparent;"
                    onmouseover="this.style.background='var(--color-bg-btn)'" onmouseout="this.style.background='transparent'">
                Cancel
            </button>
            <button @click="confirmSaveRequest()"
                    :disabled="saveModal.saving"
                    class="px-4 py-1.5 rounded text-xs font-medium text-white transition-colors"
                    style="background:var(--color-brand);"
                    onmouseover="this.style.background='var(--color-brand-hover)'" onmouseout="this.style.background='var(--color-brand)'">
                <span x-text="saveModal.saving ? 'Saving…' : 'Save'"></span>
            </button>
        </div>

    </div>
</div>
