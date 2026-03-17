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

    @include('workspace.styles')
    @include('workspace.script')

</div>
@endsection
