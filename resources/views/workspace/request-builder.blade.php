{{-- ---- REQUEST BUILDER ---- --}}

{{-- Request name row --}}
<div class="flex items-center gap-3 px-5 py-2.5 flex-shrink-0"
     style="border-bottom:1px solid var(--color-border-subtle); background:var(--color-bg-surface);">
    <input x-model="activeTab.request.name"
           @input="markDirty()"
           type="text"
           placeholder="Request name"
           class="flex-1 bg-transparent text-sm font-semibold text-white placeholder-gray-600 focus:outline-none"/>
    {{-- Refresh: reloads saved request data from server --}}
    <button x-show="activeTab?.requestId"
            x-cloak
            @click="refreshRequest()"
            class="px-3 py-1 rounded text-xs transition-colors flex-shrink-0"
            style="border:1px solid var(--color-border-input); color:var(--color-text-muted-1);"
            onmouseover="this.style.borderColor='var(--color-text-muted-3)'; this.style.color='#fff'"
            onmouseout="this.style.borderColor='var(--color-border-input)'; this.style.color='var(--color-text-muted-1)'"
            title="Reload from server">
        Refresh
    </button>
    <button @click="saveRequest()"
            class="px-3 py-1 rounded text-xs transition-colors flex-shrink-0"
            style="border:1px solid var(--color-border-input); color:var(--color-text-muted-1);"
            onmouseover="this.style.borderColor='var(--color-text-muted-3)'; this.style.color='#fff'"
            onmouseout="this.style.borderColor='var(--color-border-input)'; this.style.color='var(--color-text-muted-1)'">
        Save
    </button>
</div>

{{-- URL bar --}}
<div class="flex items-center gap-2 px-4 py-2.5 flex-shrink-0"
     style="border-bottom:1px solid var(--color-border-subtle); background:var(--color-bg-surface);">
    {{-- Method dropdown --}}
    <select x-model="activeTab.request.method"
            @change="markDirty()"
            :class="methodColor(activeTab.request.method)"
            class="rounded px-3 py-2 text-xs font-bold font-mono focus:outline-none cursor-pointer flex-shrink-0"
            style="background:var(--color-bg-base); border:1px solid var(--color-border-input); appearance:none; -webkit-appearance:none; min-width:72px; text-align:center;">
        <option class="text-green-400"  value="GET">GET</option>
        <option class="text-yellow-400" value="POST">POST</option>
        <option class="text-blue-400"   value="PUT">PUT</option>
        <option class="text-purple-400" value="PATCH">PATCH</option>
        <option class="text-red-400"    value="DELETE">DELETE</option>
    </select>

    {{-- URL input with {{variable}} backdrop highlighting --}}
    <div class="flex-1 rounded overflow-hidden url-field-wrap"
         :style="urlFocused ? 'border-color:var(--color-brand-tint-focus)' : 'border-color:var(--color-border-input)'"
         @mousemove="onVarHover($event)"
         @mouseleave="varTooltip.show = false">
        {{-- Backdrop (aria-hidden) --}}
        <div x-ref="urlBackdrop"
             aria-hidden="true"
             class="url-field-back"
             x-html="highlightUrl(activeTab.request.url)"></div>
        {{-- Real input --}}
        <input x-model="activeTab.request.url"
               x-ref="urlInput"
               @keydown="varAcKeydown($event)"
               @keydown.enter="if (!varAc.show) sendRequest()"
               @keydown.escape="varAc.show = false"
               @scroll="$refs.urlBackdrop.scrollLeft = $el.scrollLeft"
               @focus="urlFocused = true"
               @blur="varAc.show = false; urlFocused = false"
               @input="checkVarAc($event); markDirty()"
               type="text"
               placeholder="https://api.example.com/endpoint"
               class="url-field-real url-field-input"/>
    </div>

    {{-- Send button --}}
    <button @click="sendRequest()"
            :disabled="activeTab?.isLoading || !activeTab?.request.url.trim()"
            class="flex items-center gap-2 px-5 py-2 rounded text-sm font-medium text-white transition-colors flex-shrink-0 disabled:opacity-40 disabled:cursor-not-allowed"
            style="background:var(--color-brand);"
            onmouseover="if(!this.disabled) this.style.background='var(--color-brand-hover)'" onmouseout="if(!this.disabled) this.style.background='var(--color-brand)'">
        <svg x-show="activeTab?.isLoading" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
        </svg>
        <span x-text="activeTab?.isLoading ? 'Sending…' : 'Send'"></span>
    </button>
</div>

{{-- Request config + Response (layout-aware split) --}}
<div x-ref="splitContainer"
     class="flex-1 flex overflow-hidden"
     :class="$store.workspace.layoutMode === 'side-by-side' ? 'flex-row' : 'flex-col'"
     style="min-height:0;">
    @include('workspace.request-config')
    {{-- Draggable split handle with layout menu --}}
    <div class="flex-shrink-0 select-none relative"
         :class="$store.workspace.layoutMode === 'side-by-side' ? 'cursor-col-resize' : 'cursor-row-resize'"
         :style="$store.workspace.layoutMode === 'side-by-side'
             ? 'width:12px; background:var(--color-border-subtle);'
             : 'height:12px; background:var(--color-border-subtle);'"
         @mousedown.prevent="startSplitDrag($event)"
         @click.outside="layoutMenuOpen = false">

        {{-- Three-dot menu button: right end (stacked) / bottom end (side-by-side), always visible --}}
        <div class="absolute"
             :style="$store.workspace.layoutMode === 'side-by-side'
                 ? 'bottom:6px; left:50%; transform:translateX(-50%);'
                 : 'right:6px; top:50%; transform:translateY(-50%);'"
             @mousedown.stop>

            <button @click.stop="layoutMenuOpen = !layoutMenuOpen"
                    class="flex items-center justify-center rounded"
                    style="width:20px; height:20px; color:var(--color-text-muted-4);"
                    onmouseover="this.style.color='var(--color-text-primary)'"
                    onmouseout="this.style.color='var(--color-text-muted-4)'">
                {{-- Horizontal three-dot icon --}}
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                    <circle cx="5" cy="12" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="19" cy="12" r="2"/>
                </svg>
            </button>

            {{-- Layout dropdown --}}
            <div x-show="layoutMenuOpen"
                 x-cloak
                 class="absolute z-50 w-44 rounded shadow-2xl py-1"
                 :style="$store.workspace.layoutMode === 'side-by-side'
                     ? 'bottom:100%; left:12px; margin-bottom:4px; background:var(--color-bg-elevated); border:1px solid var(--color-border-menu);'
                     : 'top:100%; right:0; margin-top:4px; background:var(--color-bg-elevated); border:1px solid var(--color-border-menu);'">
                <button @click.stop="$store.workspace.setLayout('stacked'); layoutMenuOpen = false"
                        class="w-full flex items-center gap-2.5 px-3 py-2 text-xs text-left transition-colors"
                        style="color:var(--color-text-primary)"
                        onmouseover="this.style.background='var(--color-bg-btn)'" onmouseout="this.style.background='transparent'">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" style="color:var(--color-text-muted-3)" viewBox="0 0 16 16" fill="currentColor">
                        <rect x="1" y="1" width="14" height="6" rx="1"/>
                        <rect x="1" y="9" width="14" height="6" rx="1"/>
                    </svg>
                    Stacked
                    <svg x-show="$store.workspace.layoutMode === 'stacked'" class="w-3 h-3 ml-auto flex-shrink-0" style="color:var(--color-brand)" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </button>
                <button @click.stop="$store.workspace.setLayout('side-by-side'); layoutMenuOpen = false"
                        class="w-full flex items-center gap-2.5 px-3 py-2 text-xs text-left transition-colors"
                        style="color:var(--color-text-primary)"
                        onmouseover="this.style.background='var(--color-bg-btn)'" onmouseout="this.style.background='transparent'">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" style="color:var(--color-text-muted-3)" viewBox="0 0 16 16" fill="currentColor">
                        <rect x="1" y="1" width="6" height="14" rx="1"/>
                        <rect x="9" y="1" width="6" height="14" rx="1"/>
                    </svg>
                    Side by side
                    <svg x-show="$store.workspace.layoutMode === 'side-by-side'" class="w-3 h-3 ml-auto flex-shrink-0" style="color:var(--color-brand)" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
    @include('workspace.response-panel')
</div>

{{-- Variable hover tooltip (fixed-position, lives here to stay in requestBuilderComponent scope) --}}
<div x-show="varTooltip.show"
     x-cloak
     :style="`position:fixed;
              left:${varTooltip.x}px; top:${varTooltip.y - 8}px;
              transform:translate(-50%, -100%);
              z-index:300; pointer-events:none;
              padding:5px 10px; border-radius:5px; font-size:11px; font-family:ui-monospace,monospace; white-space:nowrap;
              ${varTooltip.isUndef
                ? 'background:#2d1111; color:#f87171; border:1px solid #7f1d1d;'
                : 'background:#0f172a; color:#e2e8f0; border:1px solid #334155;'}`">
    <template x-if="!varTooltip.isUndef">
        <span>
            <span style="color:var(--color-text-muted-4);" x-text="varTooltip.name"></span>
            <span style="color:#475569; margin:0 4px;">→</span>
            <span x-text="varTooltip.text"></span>
        </span>
    </template>
    <template x-if="varTooltip.isUndef">
        <span x-text="varLabel(varTooltip.name) + ' is not defined'"></span>
    </template>
</div>

{{-- Variable autocomplete dropdown --}}
<div x-show="varAc.show"
     x-cloak
     @keydown.escape.window="varAc.show = false"
     :style="`position:fixed; left:${varAc.x}px; top:${varAc.y}px; z-index:300; min-width:220px; max-width:340px; background:var(--color-bg-elevated); border:1px solid var(--color-border-menu);`"
     class="rounded shadow-2xl py-1">
    <template x-for="(s, i) in varAc.suggestions" :key="s">
        <button @mousedown.prevent="selectVarAc(s)"
                @mouseenter="varAc.activeIdx = i"
                :style="varAc.activeIdx === i ? 'background:var(--color-bg-btn)' : ''"
                class="w-full flex items-center justify-between gap-3 px-3 py-1.5 text-xs text-left">
            <span class="font-mono" style="color:var(--color-brand);" x-text="varLabel(s)"></span>
            <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded flex-shrink-0"
                  style="background:rgba(249,115,22,0.15); color:#f97316; border:1px solid rgba(249,115,22,0.3);">C</span>
        </button>
    </template>
</div>
