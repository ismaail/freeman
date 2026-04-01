{{-- ---- WELCOME STATE ---- --}}
<div x-show="!requestOpen"
     class="flex-1 flex items-center justify-center">
    <div class="text-center" style="max-width:400px;">
        <div class="flex items-center justify-center w-32 h-32 rounded-2xl mx-auto mb-5"
             style="background:var(--color-brand-tint-bg); border:1px solid var(--color-brand-tint-border);">
            <img src="{{ asset('images/Freeman-logo-transparent.png') }}" alt="Freeman" class="w-24 h-24 object-contain">
        </div>
        <h2 class="text-lg font-semibold text-white mb-2">Ready to test APIs?</h2>
        <p class="text-sm mb-6" style="color:var(--color-text-muted-5);">
            Select a saved request from the sidebar, or create a new one to get started.
        </p>
        <button @click="newRequest()"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded text-sm font-medium text-white transition-colors"
                style="background:var(--color-brand);"
                onmouseover="this.style.background='var(--color-brand-hover)'" onmouseout="this.style.background='var(--color-brand)'">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            New Request
        </button>
    </div>
</div>
