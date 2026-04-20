@extends('layouts.app')

@section('title', 'Workspace')

@section('content')
<div x-data="workspace()"
     class="h-screen flex flex-col overflow-hidden"
     style="background:var(--color-bg-base); color:var(--color-text-primary); font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;">

    @include('workspace.topbar')

    <div class="flex flex-1 overflow-hidden" style="min-height:0">

        @include('workspace.sidebar')

        <main class="flex-1 flex flex-col overflow-hidden" style="min-width:0; background:var(--color-bg-base);">

            {{-- Tab bar (always visible when there are tabs) --}}
            <div x-show="tabs.length > 0"
                 class="flex items-center flex-shrink-0 overflow-x-auto"
                 style="background:var(--color-bg-surface); border-bottom:1px solid var(--color-border-subtle); scrollbar-width:none; min-height:38px;">

                <template x-for="tab in tabs" :key="tab.id">
                    <div @click="switchTab(tab.id)"
                         class="flex items-center gap-2 px-3 py-2 flex-shrink-0 cursor-pointer select-none group relative"
                         :style="activeTabId === tab.id
                             ? 'background:var(--color-bg-base); border-bottom:2px solid var(--color-brand); color:#fff;'
                             : 'border-bottom:2px solid transparent; color:var(--color-text-muted-4);'"
                         style="border-right:1px solid var(--color-border-subtle); max-width:200px; min-width:130px;">

                        {{-- Method badge --}}
                        <span class="text-[9px] font-bold font-mono flex-shrink-0"
                              :class="methodColor(tab.request.method)"
                              x-text="tab.request.method"></span>

                        {{-- Request name --}}
                        <span class="text-xs truncate flex-1 min-w-0" x-text="tab.request.name"></span>

                        {{-- Dirty dot --}}
                        <span x-show="tab.isDirty"
                              class="w-1.5 h-1.5 rounded-full flex-shrink-0"
                              style="background:var(--color-brand);"></span>

                        {{-- Close button --}}
                        <button @click.stop="closeTab(tab.id)"
                                class="flex-shrink-0 w-4 h-4 flex items-center justify-center rounded opacity-0 group-hover:opacity-100 transition-opacity"
                                style="color:var(--color-text-muted-4);"
                                onmouseover="this.style.color='#fff'; this.style.background='rgba(255,255,255,0.1)'"
                                onmouseout="this.style.color='var(--color-text-muted-4)'; this.style.background='transparent'">
                            <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </template>

                {{-- New tab button --}}
                <button @click="newTab()"
                        class="flex items-center justify-center w-8 h-8 flex-shrink-0 ml-1 rounded transition-colors"
                        style="color:var(--color-text-muted-4);"
                        title="New request tab"
                        onmouseover="this.style.color='var(--color-text-muted-1)'" onmouseout="this.style.color='var(--color-text-muted-4)'">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                </button>
            </div>

            @include('workspace.welcome')

            <div x-show="activeTab !== null" class="flex-1 flex flex-col overflow-hidden" style="min-height:0">
                @include('workspace.request-builder')
            </div>

        </main>
    </div>

    @include('workspace.collection-variables-modal')
    @include('workspace.save-request-modal')

    {{-- Variable hover tooltip --}}
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

    @include('workspace.styles')
    @include('workspace.script')

</div>
@endsection
