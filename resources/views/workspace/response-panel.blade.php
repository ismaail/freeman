{{-- === RESPONSE PANEL (bottom 58%) === --}}
<div class="flex flex-col overflow-hidden" style="flex:1; min-height:0;">

    {{-- Empty state: no request sent --}}
    <div x-show="!activeTab?.response && !activeTab?.isLoading"
         class="flex-1 flex items-center justify-center">
        <div class="text-center">
            <svg class="w-10 h-10 mx-auto mb-3" style="color:var(--color-bg-badge)" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <p class="text-xs" style="color:var(--color-border-input);">Hit <strong style="color:var(--color-text-muted-4);">Send</strong> to get a response</p>
        </div>
    </div>

    {{-- Loading --}}
    <div x-show="activeTab?.isLoading"
         class="flex-1 flex items-center justify-center">
        <div class="flex items-center gap-3" style="color:var(--color-text-muted-5);">
            <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            <span class="text-sm">Sending request…</span>
        </div>
    </div>

    {{-- Error state (network/connection failure) --}}
    <div x-show="activeTab?.response && !activeTab?.response.success" class="flex flex-col overflow-hidden h-full">
        <div class="flex items-center gap-4 px-5 py-2.5 flex-shrink-0"
             style="background:var(--color-bg-surface); border-bottom:1px solid var(--color-border-subtle);">
            <span class="text-xs font-semibold" style="color:var(--color-danger);">Error</span>
            <span class="text-xs" style="color:var(--color-border-input);"
                  x-text="(activeTab?.response?.response_time_ms ?? 0) + ' ms'"></span>
        </div>
        <div class="flex-1 overflow-y-auto p-5">
            <div class="flex items-start gap-3 p-4 rounded-lg"
                 style="background:var(--color-danger-tint-bg); border:1px solid var(--color-danger-tint-border);">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color:var(--color-danger);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div>
                    <p class="text-sm font-medium mb-1" style="color:var(--color-danger-pale);">Request Failed</p>
                    <p x-text="activeTab?.response?.error" class="text-xs font-mono" style="color:var(--color-danger-light); opacity:0.8;"></p>
                </div>
            </div>
        </div>
    </div>

    {{-- Success response --}}
    <div x-show="activeTab?.response && activeTab?.response.success" class="flex flex-col overflow-hidden h-full">

        {{-- Status bar --}}
        <div class="flex items-center gap-5 px-5 py-2.5 flex-shrink-0"
             style="background:var(--color-bg-surface); border-bottom:1px solid var(--color-border-subtle);">
            <div class="flex items-center gap-1.5">
                <span class="text-[9px] uppercase tracking-widest" style="color:var(--color-border-input);">Status</span>
                <span :class="statusColor(activeTab?.response?.status)"
                      class="text-sm font-bold"
                      x-text="activeTab?.response?.status"></span>
                <span class="text-[10px]" :class="statusLabel(activeTab?.response?.status)"
                      x-text="statusText(activeTab?.response?.status)"></span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="text-[9px] uppercase tracking-widest" style="color:var(--color-border-input);">Time</span>
                <span class="text-xs" style="color:var(--color-text-secondary);"
                      x-text="(activeTab?.response?.response_time_ms ?? 0) + ' ms'"></span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="text-[9px] uppercase tracking-widest" style="color:var(--color-border-input);">Size</span>
                <span class="text-xs" style="color:var(--color-text-secondary);"
                      x-text="responseSize(activeTab?.response?.response_body)"></span>
            </div>
        </div>

        {{-- Tab bar + inline body controls --}}
        <div class="flex items-center flex-shrink-0"
             style="background:var(--color-bg-surface); border-bottom:1px solid var(--color-border-subtle);">

            {{-- Body / Headers tabs --}}
            <button @click="activeTab.responseTab = 'body'"
                    :style="activeTab?.responseTab === 'body' ? 'color:#fff; border-bottom:2px solid var(--color-brand);' : 'color:var(--color-text-muted-4); border-bottom:2px solid transparent;'"
                    class="px-5 py-2 text-[10px] uppercase tracking-widest font-semibold transition-colors flex-shrink-0">Body</button>
            <button @click="activeTab.responseTab = 'headers'"
                    :style="activeTab?.responseTab === 'headers' ? 'color:#fff; border-bottom:2px solid var(--color-brand);' : 'color:var(--color-text-muted-4); border-bottom:2px solid transparent;'"
                    class="px-5 py-2 text-[10px] uppercase tracking-widest font-semibold transition-colors flex-shrink-0">Headers</button>

            {{-- Body view controls --}}
            <div class="ml-auto flex items-center gap-2 pr-3"
                 :style="activeTab?.responseTab === 'body' ? 'opacity:1; pointer-events:auto;' : 'opacity:0; pointer-events:none;'"
                 x-data="{
                     get isBinary() { const t = detectContentType(activeTab?.response?.response_headers); return t === 'image' || t === 'audio'; },
                     get isHtml()   { return detectContentType(activeTab?.response?.response_headers) === 'html'; }
                 }">

                {{-- Pretty / Raw pill (hidden for binary content) --}}
                <div class="flex rounded overflow-hidden"
                     x-show="!isBinary"
                     style="border:1px solid var(--color-border-subtle);">
                    <button @click="activeTab.responseViewMode = 'pretty'"
                            class="px-2.5 py-1 text-[10px] font-medium transition-colors"
                            :style="activeTab?.responseViewMode === 'pretty'
                                ? 'background:var(--color-bg-elevated); color:var(--color-text-muted-1);'
                                : 'color:var(--color-text-muted-5);'">Pretty</button>
                    <button @click="activeTab.responseViewMode = 'raw'"
                            class="px-2.5 py-1 text-[10px] font-medium transition-colors"
                            style="border-left:1px solid var(--color-border-subtle);"
                            :style="activeTab?.responseViewMode === 'raw'
                                ? 'background:var(--color-bg-elevated); color:var(--color-text-muted-1);'
                                : 'color:var(--color-text-muted-5);'">Raw</button>
                    <button x-show="isHtml"
                            @click="activeTab.responseViewMode = 'preview'"
                            class="px-2.5 py-1 text-[10px] font-medium transition-colors"
                            style="border-left:1px solid var(--color-border-subtle);"
                            :style="activeTab?.responseViewMode === 'preview'
                                ? 'background:var(--color-bg-elevated); color:var(--color-text-muted-1);'
                                : 'color:var(--color-text-muted-5);'">Preview</button>
                </div>

                {{-- Format dropdown (hidden for binary content) --}}
                <div class="relative" x-show="!isBinary" x-data="{ fmtOpen: false }">
                    <button @click="fmtOpen = !fmtOpen"
                            class="flex items-center gap-1.5 rounded px-2.5 py-1 text-[10px] font-medium transition-colors"
                            style="border:1px solid var(--color-border-subtle); color:var(--color-text-muted-2); background:var(--color-bg-base);"
                            onmouseover="this.style.borderColor='var(--color-border-input)';this.style.color='var(--color-text-muted-1)'"
                            onmouseout="this.style.borderColor='var(--color-border-subtle)';this.style.color='var(--color-text-muted-2)'">
                        <span class="font-mono text-[9px] font-bold leading-none"
                              style="color:var(--color-brand);"
                              x-text="({'json':'{ }','xml':'< >','html':'< >','javascript':'JS','text':'Tx','auto':({'json':'{ }','xml':'< >','html':'< >','javascript':'JS','text':'Tx'})[responseDetectedType] ?? '{ }'})[activeTab?.responseForceType]"></span>
                        <span x-text="activeTab?.responseForceType === 'auto' ? responseDetectedType.toUpperCase() : activeTab?.responseForceType.toUpperCase()"></span>
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div x-show="fmtOpen"
                         x-cloak
                         @click.outside="fmtOpen = false"
                         class="absolute right-0 z-50 rounded-md py-1 min-w-[130px]"
                         style="top:calc(100% + 4px); background:var(--color-bg-elevated); border:1px solid var(--color-border-menu); box-shadow:0 8px 24px rgba(0,0,0,.45);">

                        <p class="px-3 pt-1 pb-1.5 text-[9px] uppercase tracking-widest"
                           style="color:var(--color-text-muted-5);">View as</p>

                        <template x-for="opt in [
                            { value:'auto',       icon:'◎',   label:'Auto detect' },
                            { value:'json',       icon:'{ }', label:'JSON' },
                            { value:'xml',        icon:'< >', label:'XML' },
                            { value:'html',       icon:'< >', label:'HTML' },
                            { value:'javascript', icon:'JS',  label:'JavaScript' },
                            { value:'text',       icon:'Tx',  label:'Text' }
                        ]" :key="opt.value">
                            <button @click="activeTab.responseForceType = opt.value; fmtOpen = false"
                                    class="w-full flex items-center gap-2.5 px-3 py-1.5 text-xs transition-colors text-left"
                                    :style="activeTab?.responseForceType === opt.value
                                        ? 'color:var(--color-brand); background:var(--color-brand-tint-bg);'
                                        : 'color:var(--color-text-muted-2);'"
                                    onmouseover="if(this.dataset.active!=='1') this.style.background='var(--color-bg-hover-row)'"
                                    onmouseout="if(this.dataset.active!=='1') this.style.background=''"
                                    :data-active="activeTab?.responseForceType === opt.value ? '1' : '0'">
                                <span class="font-mono text-[9px] font-bold w-5 text-center leading-none"
                                      style="color:var(--color-brand); opacity:0.7;" x-text="opt.icon"></span>
                                <span x-text="opt.label"></span>
                                <svg x-show="activeTab?.responseForceType === opt.value"
                                     class="w-3 h-3 ml-auto" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- JSON filter toggle --}}
                <button x-show="!isBinary && responseDetectedType === 'json' && activeTab?.responseViewMode === 'pretty'"
                        @click="toggleJsonFilter()"
                        class="flex items-center gap-1.5 text-[10px] font-medium transition-colors"
                        :style="jsonFilterOpen ? 'color:var(--color-brand)' : 'color:var(--color-text-muted-4)'"
                        title="Filter JSON (Ctrl+F)">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                    </svg>
                    <span>Filter</span>
                </button>

                {{-- Copy button --}}
                <button @click="copyResponseBody()"
                        class="flex items-center gap-1.5 text-[10px] font-medium transition-colors"
                        :style="responseCopied ? 'color:var(--color-success)' : 'color:var(--color-text-muted-4)'"
                        onmouseover="if(!this.dataset.copied) this.style.color='var(--color-text-muted-1)'"
                        onmouseout="if(!this.dataset.copied) this.style.color='var(--color-text-muted-4)'"
                        :data-copied="responseCopied ? '1' : ''">
                    <template x-if="!responseCopied">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                    </template>
                    <template x-if="responseCopied">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </template>
                    <span x-text="responseCopied ? 'Copied!' : 'Copy'"></span>
                </button>
            </div>
        </div>

        {{-- JSON filter bar --}}
        <div x-show="jsonFilterOpen && activeTab?.responseTab === 'body' && responseDetectedType === 'json' && activeTab?.responseViewMode === 'pretty'"
             x-cloak
             x-data="{ histOpen: false }"
             class="flex-shrink-0 relative"
             style="background:var(--color-bg-surface); border-bottom:1px solid var(--color-border-subtle);">
            <div class="flex items-center gap-2 px-4 py-2">
                <svg class="w-3.5 h-3.5 flex-shrink-0" style="color:var(--color-text-muted-4);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                </svg>
                <div class="relative flex-1">
                    <input id="jf-filter-input"
                           type="text"
                           x-model="jsonFilter"
                           placeholder="Filter JSON keys and values…"
                           @keydown.enter="saveFilterToHistory(jsonFilter)"
                           @keydown.escape="toggleJsonFilter()"
                           @focus="histOpen = jsonFilterHistory.length > 0"
                           @blur="setTimeout(() => histOpen = false, 150)"
                           class="w-full text-xs font-mono bg-transparent outline-none"
                           style="color:var(--color-text-input); caret-color:var(--color-brand);"
                           autocomplete="off" spellcheck="false">
                    <span x-show="jsonFilter.trim()"
                          x-text="jsonMatchCount + (jsonMatchCount === 1 ? ' match' : ' matches')"
                          class="absolute right-0 top-0 text-[10px] pointer-events-none"
                          style="color:var(--color-text-muted-5); line-height:1.5rem;"></span>
                </div>
                <button x-show="jsonFilter.trim()"
                        @click="clearJsonFilter()"
                        class="flex-shrink-0 text-xs leading-none transition-colors"
                        style="color:var(--color-text-muted-4);"
                        onmouseover="this.style.color='var(--color-text-muted-1)'"
                        onmouseout="this.style.color='var(--color-text-muted-4)'">✕</button>
                <button @click="jsonFilterHide = !jsonFilterHide"
                        class="flex-shrink-0 flex items-center gap-1 text-[10px] font-medium transition-colors whitespace-nowrap"
                        :style="jsonFilterHide ? 'color:var(--color-brand)' : 'color:var(--color-text-muted-4)'"
                        title="Hide non-matching items">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h18M7 9h10M11 14h2"/>
                    </svg>
                    <span>Hide</span>
                </button>
                <span class="flex-shrink-0 text-[10px]" style="color:var(--color-text-muted-5);">ESC to close</span>
            </div>
            {{-- History dropdown --}}
            <div x-show="histOpen && jsonFilterHistory.length > 0"
                 x-cloak
                 class="absolute left-0 right-0 z-50 py-1"
                 style="top:100%; background:var(--color-bg-elevated); border:1px solid var(--color-border-menu); border-top:none; box-shadow:0 8px 24px rgba(0,0,0,.45);">
                <p class="px-4 pt-1 pb-1 text-[9px] uppercase tracking-widest" style="color:var(--color-text-muted-5);">Recent</p>
                <template x-for="h in jsonFilterHistory" :key="h">
                    <button @click="jsonFilter = h; histOpen = false; $nextTick(() => document.getElementById('jf-filter-input')?.focus())"
                            class="w-full text-left px-4 py-1.5 text-xs font-mono transition-colors"
                            style="color:var(--color-text-muted-2);"
                            onmouseover="this.style.background='var(--color-bg-hover-row)'"
                            onmouseout="this.style.background=''">
                        <span x-text="h"></span>
                    </button>
                </template>
            </div>
        </div>

        {{-- Response body / headers content --}}
        <div class="flex-1 overflow-y-auto">
            {{-- Body tab --}}
            <div x-show="activeTab?.responseTab === 'body'">
                <pre x-show="!(activeTab?.responseViewMode === 'preview' && detectContentType(activeTab?.response?.response_headers) === 'html')"
                     :class="detectContentType(activeTab?.response?.response_headers) === 'image' || detectContentType(activeTab?.response?.response_headers) === 'audio'
                          ? 'p-4'
                          : 'p-4 text-xs font-mono whitespace-pre-wrap break-all response-body'"
                     style="tab-size:2; line-height:1.65; color:var(--color-text-input);"
                     x-html="renderResponseBody(activeTab?.response?.response_body, activeTab?.response?.response_headers)"></pre>
                <iframe x-show="activeTab?.responseViewMode === 'preview' && detectContentType(activeTab?.response?.response_headers) === 'html'"
                        :srcdoc="activeTab?.response?.response_body"
                        sandbox="allow-scripts allow-same-origin allow-forms"
                        class="w-full border-0 block"
                        style="min-height:500px;"></iframe>
            </div>

            {{-- Headers tab --}}
            <div x-show="activeTab?.responseTab === 'headers'" class="p-4">
                <table class="w-full" style="border-collapse:collapse;">
                    <template x-for="[k, v] in Object.entries(activeTab?.response?.response_headers || {})" :key="k">
                        <tr style="border-bottom:1px solid var(--color-bg-hover-subtle);">
                            <td class="py-2 pr-4 align-top w-2/5">
                                <span class="text-xs font-mono" style="color:var(--color-syntax-key);" x-text="k"></span>
                            </td>
                            <td class="py-2 align-top">
                                <span class="text-xs font-mono" style="color:var(--color-syntax-str);" x-text="v"></span>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="Object.keys(activeTab?.response?.response_headers || {}).length === 0">
                        <td colspan="2" class="py-4 text-xs text-center" style="color:var(--color-border-input);">No response headers</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

</div>{{-- end response panel --}}
