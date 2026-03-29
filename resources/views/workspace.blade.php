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

            @include('workspace.welcome')

            <div x-show="requestOpen" class="flex-1 flex flex-col overflow-hidden" style="min-height:0">
                @include('workspace.request-builder')
            </div>

        </main>
    </div>

    @include('workspace.collection-variables-modal')

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
