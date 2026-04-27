{{-- ---- REQUEST BUILDER ---- --}}

{{-- Request name row --}}
<div class="flex items-center gap-3 px-5 py-2.5 flex-shrink-0"
     style="border-bottom:1px solid var(--color-border-subtle); background:var(--color-bg-surface);">
    <input x-model="activeTab.request.name"
           @input="markDirty()"
           type="text"
           placeholder="Request name"
           class="flex-1 bg-transparent text-sm font-semibold text-white placeholder-gray-600 focus:outline-none"/>
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
               @keydown.enter="sendRequest()"
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

{{-- Request config + Response (vertical split) --}}
<div x-ref="splitContainer" class="flex-1 flex flex-col overflow-hidden" style="min-height:0;">
    @include('workspace.request-config')
    {{-- Draggable split handle --}}
    <div class="flex-shrink-0 flex items-center justify-center cursor-row-resize select-none group"
         style="height:6px; background:var(--color-border-subtle);"
         @mousedown.prevent="startSplitDrag($event)">
        <div class="rounded-full opacity-40 group-hover:opacity-80 transition-opacity"
             style="width:32px; height:2px; background:var(--color-text-muted-4);"></div>
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
     :style="`position:fixed; left:${varAc.x}px; top:${varAc.y}px; z-index:300; min-width:220px; max-width:340px;`"
     class="rounded shadow-2xl py-1"
     style="background:var(--color-bg-elevated); border:1px solid var(--color-border-menu);">
    <template x-for="s in varAc.suggestions" :key="s">
        <button @mousedown.prevent="selectVarAc(s)"
                class="w-full flex items-center justify-between gap-3 px-3 py-1.5 text-xs text-left"
                onmouseover="this.style.background='var(--color-bg-btn)'" onmouseout="this.style.background='transparent'">
            <span class="font-mono" style="color:var(--color-brand);" x-text="varLabel(s)"></span>
            <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded flex-shrink-0"
                  style="background:rgba(249,115,22,0.15); color:#f97316; border:1px solid rgba(249,115,22,0.3);">C</span>
        </button>
    </template>
</div>
