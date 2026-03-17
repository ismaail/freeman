{{-- === RESPONSE PANEL (bottom 58%) === --}}
<div class="flex flex-col overflow-hidden" style="flex:1; min-height:0;">

    {{-- Empty state: no request sent --}}
    <div x-show="!response && !isLoading"
         class="flex-1 flex items-center justify-center">
        <div class="text-center">
            <svg class="w-10 h-10 mx-auto mb-3" style="color:var(--color-bg-badge)" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <p class="text-xs" style="color:var(--color-border-input);">Hit <strong style="color:var(--color-text-muted-4);">Send</strong> to get a response</p>
        </div>
    </div>

    {{-- Loading --}}
    <div x-show="isLoading"
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
    <div x-show="response && !response.success" class="flex flex-col overflow-hidden h-full">
        <div class="flex items-center gap-4 px-5 py-2.5 flex-shrink-0"
             style="background:var(--color-bg-surface); border-bottom:1px solid var(--color-border-subtle);">
            <span class="text-xs font-semibold" style="color:var(--color-danger);">Error</span>
            <span class="text-xs" style="color:var(--color-border-input);"
                  x-text="(response?.response_time_ms ?? 0) + ' ms'"></span>
        </div>
        <div class="flex-1 overflow-y-auto p-5">
            <div class="flex items-start gap-3 p-4 rounded-lg"
                 style="background:var(--color-danger-tint-bg); border:1px solid var(--color-danger-tint-border);">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color:var(--color-danger);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div>
                    <p class="text-sm font-medium mb-1" style="color:var(--color-danger-pale);">Request Failed</p>
                    <p x-text="response?.error" class="text-xs font-mono" style="color:var(--color-danger-light); opacity:0.8;"></p>
                </div>
            </div>
        </div>
    </div>

    {{-- Success response --}}
    <div x-show="response && response.success" class="flex flex-col overflow-hidden h-full">

        {{-- Status bar --}}
        <div class="flex items-center gap-5 px-5 py-2.5 flex-shrink-0"
             style="background:var(--color-bg-surface); border-bottom:1px solid var(--color-border-subtle);">
            <div class="flex items-center gap-1.5">
                <span class="text-[9px] uppercase tracking-widest" style="color:var(--color-border-input);">Status</span>
                <span :class="statusColor(response?.status)"
                      class="text-sm font-bold"
                      x-text="response?.status"></span>
                <span class="text-[10px]" :class="statusLabel(response?.status)"
                      x-text="statusText(response?.status)"></span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="text-[9px] uppercase tracking-widest" style="color:var(--color-border-input);">Time</span>
                <span class="text-xs" style="color:var(--color-text-secondary);"
                      x-text="(response?.response_time_ms ?? 0) + ' ms'"></span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="text-[9px] uppercase tracking-widest" style="color:var(--color-border-input);">Size</span>
                <span class="text-xs" style="color:var(--color-text-secondary);"
                      x-text="responseSize(response?.response_body)"></span>
            </div>
        </div>

        {{-- Response tabs --}}
        <div class="flex flex-shrink-0" style="background:var(--color-bg-surface); border-bottom:1px solid var(--color-border-subtle);">
            <button @click="responseTab = 'body'"
                    :style="responseTab === 'body' ? 'color:#fff; border-bottom:2px solid var(--color-brand);' : 'color:var(--color-text-muted-4); border-bottom:2px solid transparent;'"
                    class="px-5 py-2 text-[10px] uppercase tracking-widest font-semibold transition-colors">Body</button>
            <button @click="responseTab = 'headers'"
                    :style="responseTab === 'headers' ? 'color:#fff; border-bottom:2px solid var(--color-brand);' : 'color:var(--color-text-muted-4); border-bottom:2px solid transparent;'"
                    class="px-5 py-2 text-[10px] uppercase tracking-widest font-semibold transition-colors">Headers</button>
        </div>

        {{-- Response body --}}
        <div class="flex-1 overflow-y-auto">
            {{-- Body tab --}}
            <div x-show="responseTab === 'body'">
                <pre class="p-4 text-xs font-mono whitespace-pre-wrap break-all response-body"
                     style="tab-size:2; line-height:1.65; color:var(--color-text-input);"
                     x-html="renderResponseBody(response?.response_body, response?.response_headers)"></pre>
            </div>

            {{-- Headers tab --}}
            <div x-show="responseTab === 'headers'" class="p-4">
                <table class="w-full" style="border-collapse:collapse;">
                    <template x-for="[k, v] in Object.entries(response?.response_headers || {})" :key="k">
                        <tr style="border-bottom:1px solid var(--color-bg-hover-subtle);">
                            <td class="py-2 pr-4 align-top w-2/5">
                                <span class="text-xs font-mono" style="color:var(--color-syntax-key);" x-text="k"></span>
                            </td>
                            <td class="py-2 align-top">
                                <span class="text-xs font-mono" style="color:var(--color-syntax-str);" x-text="v"></span>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="Object.keys(response?.response_headers || {}).length === 0">
                        <td colspan="2" class="py-4 text-xs text-center" style="color:var(--color-border-input);">No response headers</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

</div>{{-- end response panel --}}
