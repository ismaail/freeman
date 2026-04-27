{{-- ================================================================
     COLLECTION VARIABLES MODAL
================================================================ --}}
<div x-data="collectionVarsModalComponent()"
     x-show="collectionVarsModal.open"
     x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center"
     style="background:rgba(0,0,0,0.6);"
     @keydown.escape.window="collectionVarsModal.open = false">

    <div class="flex flex-col rounded-lg shadow-2xl w-full max-w-xl mx-4"
         style="background:var(--color-bg-elevated); border:1px solid var(--color-border-menu); max-height:80vh;"
         @click.stop>

        {{-- Header --}}
        <div class="flex items-center justify-between px-5 py-4 flex-shrink-0"
             style="border-bottom:1px solid var(--color-border-subtle);">
            <div>
                <h2 class="text-sm font-semibold" style="color:var(--color-text-primary);">Collection Variables</h2>
                <p class="text-[11px] mt-0.5 truncate max-w-xs" style="color:var(--color-text-muted-4);"
                   x-text="collectionVarsModal.collectionName"></p>
            </div>
            <button @click="collectionVarsModal.open = false"
                    class="p-1.5 rounded transition-colors"
                    style="color:var(--color-text-muted-4);"
                    onmouseover="this.style.color='var(--color-text-primary)'; this.style.background='var(--color-bg-btn)'"
                    onmouseout="this.style.color='var(--color-text-muted-4)'; this.style.background='transparent'">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Variable rows --}}
        <div class="flex-1 overflow-y-auto p-4">
            <table class="w-full" style="border-collapse:collapse;">
                <thead>
                    <tr class="text-[9px] uppercase tracking-widest" style="color:var(--color-border-input);">
                        <th class="pb-2 w-5 text-left"></th>
                        <th class="pb-2 pr-2 text-left">Variable</th>
                        <th class="pb-2 text-left">Value</th>
                        <th class="pb-2 w-5"></th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(v, i) in collectionVarsModal.variables" :key="'cv'+i">
                        <tr class="kv-row">
                            <td class="pr-2 py-0.5 w-5">
                                <input type="checkbox" x-model="v.enabled"
                                       class="w-3 h-3 cursor-pointer" style="accent-color:var(--color-brand);"/>
                            </td>
                            <td class="pr-1.5 py-0.5">
                                <input x-model="v.key" type="text" placeholder="VARIABLE_NAME"
                                       class="kv-input w-full rounded px-2.5 py-1.5 text-xs font-mono focus:outline-none"
                                       style="background:var(--color-bg-base); border:1px solid var(--color-border-subtle); color:var(--color-text-input);"
                                       onfocus="this.style.borderColor='var(--color-border-input)'" onblur="this.style.borderColor='var(--color-border-subtle)'"/>
                            </td>
                            <td class="py-0.5">
                                <input x-model="v.value" type="text" placeholder="value"
                                       class="kv-input w-full rounded px-2.5 py-1.5 text-xs font-mono focus:outline-none"
                                       style="background:var(--color-bg-base); border:1px solid var(--color-border-subtle); color:var(--color-text-input);"
                                       onfocus="this.style.borderColor='var(--color-border-input)'" onblur="this.style.borderColor='var(--color-border-subtle)'"/>
                            </td>
                            <td class="pl-1.5 py-0.5 w-5">
                                <button @click="removeVariableRow(i)"
                                        class="p-0.5 rounded transition-colors"
                                        style="color:var(--color-border-input);"
                                        onmouseover="this.style.color='var(--color-danger)'" onmouseout="this.style.color='var(--color-border-input)'">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>

            <button @click="addVariableRow()"
                    class="mt-3 text-[11px] transition-colors"
                    style="color:var(--color-text-muted-4);"
                    onmouseover="this.style.color='var(--color-brand)'" onmouseout="this.style.color='var(--color-text-muted-4)'">
                + Add Variable
            </button>
        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-between px-5 py-3 flex-shrink-0"
             style="border-top:1px solid var(--color-border-subtle);">
            <p class="text-[10px]" style="color:var(--color-text-muted-5);">
                Use <code style="color:var(--color-brand);">@{{VARIABLE_NAME}}</code> anywhere in your requests.
            </p>
            <div class="flex items-center gap-2">
                <button @click="collectionVarsModal.open = false"
                        class="px-3 py-1.5 rounded text-xs transition-colors"
                        style="color:var(--color-text-muted-3); background:transparent;"
                        onmouseover="this.style.background='var(--color-bg-btn)'" onmouseout="this.style.background='transparent'">
                    Cancel
                </button>
                <button @click="saveCollectionVariables()"
                        :disabled="collectionVarsModal.saving"
                        class="px-4 py-1.5 rounded text-xs font-medium text-white transition-colors"
                        style="background:var(--color-brand);"
                        onmouseover="this.style.background='var(--color-brand-hover)'" onmouseout="this.style.background='var(--color-brand)'">
                    <span x-text="collectionVarsModal.saving ? 'Saving…' : 'Save'"></span>
                </button>
            </div>
        </div>

    </div>
</div>
