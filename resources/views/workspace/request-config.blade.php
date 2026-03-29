{{-- === REQUEST CONFIG (top 42%) === --}}
<div class="flex flex-col overflow-hidden flex-shrink-0" style="height:42%; border-bottom:1px solid var(--color-border-subtle);">

    {{-- Request tab bar --}}
    <div class="flex flex-shrink-0" style="background:var(--color-bg-surface); border-bottom:1px solid var(--color-border-subtle);">
        <template x-for="tab in [{id:'params', label:'Params'}, {id:'headers', label:'Headers'}, {id:'body', label:'Body'}, {id:'auth', label:'Auth'}]" :key="tab.id">
            <button @click="requestTab = tab.id"
                    class="relative px-5 py-2.5 text-[10px] uppercase tracking-widest font-semibold transition-colors"
                    :style="requestTab === tab.id ? 'color:#fff; border-bottom:2px solid var(--color-brand);' : 'color:var(--color-text-muted-4); border-bottom:2px solid transparent;'"
                    onmouseover="if(this.getAttribute('data-act')!=='1') this.style.color='var(--color-text-muted-1)'"
                    onmouseout="if(this.getAttribute('data-act')!=='1') this.style.color='var(--color-text-muted-4)'"
                    :data-act="requestTab === tab.id ? '1' : '0'">
                <span x-text="tab.label"></span>
                {{-- Badge for filled headers --}}
                <span x-show="tab.id === 'headers' && filledHeaderCount > 0"
                      x-text="filledHeaderCount"
                      class="ml-1.5 px-1.5 py-px rounded-full text-[8px] font-bold"
                      style="background:var(--color-brand-tint-badge); color:var(--color-brand);"></span>
                <span x-show="tab.id === 'params' && filledParamCount > 0"
                      x-text="filledParamCount"
                      class="ml-1.5 px-1.5 py-px rounded-full text-[8px] font-bold"
                      style="background:var(--color-brand-tint-badge); color:var(--color-brand);"></span>
            </button>
        </template>
    </div>

    {{-- Tab content (scrollable) --}}
    <div class="flex-1 overflow-y-auto p-4">

        {{-- PARAMS --}}
        <div x-show="requestTab === 'params'">
            <table class="w-full" style="border-collapse:collapse;">
                <thead>
                    <tr class="text-[9px] uppercase tracking-widest" style="color:var(--color-border-input);">
                        <th class="pb-2 w-5 text-left"></th>
                        <th class="pb-2 pr-2 text-left">Key</th>
                        <th class="pb-2 text-left">Value</th>
                        <th class="pb-2 w-5"></th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(p, i) in currentRequest.params" :key="'p'+i">
                        <tr class="kv-row">
                            <td class="pr-2 py-0.5 w-5">
                                <input type="checkbox" x-model="p.enabled"
                                       class="w-3 h-3 cursor-pointer" style="accent-color:var(--color-brand);"/>
                            </td>
                            <td class="pr-1.5 py-0.5">
                                <input x-model="p.key" type="text" placeholder="Key"
                                       class="kv-input w-full rounded px-2.5 py-1.5 text-xs font-mono focus:outline-none"
                                       style="background:var(--color-bg-base); border:1px solid var(--color-border-subtle); color:var(--color-text-input);"
                                       onfocus="this.style.borderColor='var(--color-border-input)'" onblur="this.style.borderColor='var(--color-border-subtle)'"/>
                            </td>
                            <td class="py-0.5">
                                <div class="var-field-wrap w-full kv-input"
                                     @mousemove="onVarHover($event)" @mouseleave="varTooltip.show=false">
                                    <div class="vf-back" x-html="highlightVars(p.value)"></div>
                                    <input x-model="p.value" type="text" placeholder="Value"
                                           @input="checkVarAc($event)" @blur="varAc.show = false" @keydown.escape="varAc.show = false"
                                           class="vf-real focus:outline-none"
                                           onfocus="this.closest('.var-field-wrap').style.borderColor='var(--color-border-input)'"
                                           onblur="this.closest('.var-field-wrap').style.borderColor='var(--color-border-subtle)'"/>
                                </div>
                            </td>
                            <td class="pl-1.5 py-0.5 w-5">
                                <button @click="removeParam(i)"
                                        class="kv-del opacity-0 transition-opacity"
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
            <button @click="addParam()"
                    class="mt-2 flex items-center gap-1 text-[10px] transition-colors"
                    style="color:var(--color-border-input);"
                    onmouseover="this.style.color='var(--color-brand)'" onmouseout="this.style.color='var(--color-border-input)'">
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Add row
            </button>
        </div>

        {{-- HEADERS --}}
        <div x-show="requestTab === 'headers'">
            <table class="w-full" style="border-collapse:collapse;">
                <thead>
                    <tr class="text-[9px] uppercase tracking-widest" style="color:var(--color-border-input);">
                        <th class="pb-2 w-5 text-left"></th>
                        <th class="pb-2 pr-2 text-left">Key</th>
                        <th class="pb-2 text-left">Value</th>
                        <th class="pb-2 w-5"></th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(h, i) in currentRequest.headers" :key="'h'+i">
                        <tr class="kv-row">
                            <td class="pr-2 py-0.5 w-5">
                                <input type="checkbox" x-model="h.enabled"
                                       class="w-3 h-3 cursor-pointer" style="accent-color:var(--color-brand);"/>
                            </td>
                            <td class="pr-1.5 py-0.5">
                                <input x-model="h.key" type="text" placeholder="Header name"
                                       class="kv-input w-full rounded px-2.5 py-1.5 text-xs font-mono focus:outline-none"
                                       style="background:var(--color-bg-base); border:1px solid var(--color-border-subtle); color:var(--color-text-input);"
                                       onfocus="this.style.borderColor='var(--color-border-input)'" onblur="this.style.borderColor='var(--color-border-subtle)'"/>
                            </td>
                            <td class="py-0.5">
                                <div class="var-field-wrap w-full kv-input"
                                     @mousemove="onVarHover($event)" @mouseleave="varTooltip.show=false">
                                    <div class="vf-back" x-html="highlightVars(h.value)"></div>
                                    <input x-model="h.value" type="text" placeholder="Value"
                                           @input="checkVarAc($event)" @blur="varAc.show = false" @keydown.escape="varAc.show = false"
                                           class="vf-real focus:outline-none"
                                           onfocus="this.closest('.var-field-wrap').style.borderColor='var(--color-border-input)'"
                                           onblur="this.closest('.var-field-wrap').style.borderColor='var(--color-border-subtle)'"/>
                                </div>
                            </td>
                            <td class="pl-1.5 py-0.5 w-5">
                                <button @click="removeHeader(i)"
                                        class="kv-del opacity-0 transition-opacity"
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
            <button @click="addHeader()"
                    class="mt-2 flex items-center gap-1 text-[10px] transition-colors"
                    style="color:var(--color-border-input);"
                    onmouseover="this.style.color='var(--color-brand)'" onmouseout="this.style.color='var(--color-border-input)'">
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Add row
            </button>
        </div>

        {{-- BODY --}}
        <div x-show="requestTab === 'body'">
            {{-- Body type selector --}}
            <div class="flex gap-4 mb-4">
                <template x-for="btype in ['none', 'raw', 'form-data', 'x-www-form-urlencoded']" :key="btype">
                    <label class="flex items-center gap-1.5 cursor-pointer select-none">
                        <input type="radio" x-model="currentRequest.body_type" :value="btype"
                               class="w-3 h-3 cursor-pointer" style="accent-color:var(--color-brand);"/>
                        <span x-text="btype" class="text-xs capitalize" style="color:var(--color-text-muted-2);"></span>
                    </label>
                </template>
            </div>

            {{-- Raw textarea --}}
            <div x-show="currentRequest.body_type === 'raw'">
                <div class="var-field-wrap vf-textarea w-full"
                     style="background:var(--color-bg-body-input);"
                     @mousemove="onVarHover($event)" @mouseleave="varTooltip.show=false">
                    <div class="vf-back response-body" x-html="highlightVars(currentRequest.body ?? '')"></div>
                    <textarea x-model="currentRequest.body"
                              placeholder='{"key": "value"}'
                              @input="checkVarAc($event)" @blur="varAc.show = false" @keydown.escape="varAc.show = false"
                              class="vf-real focus:outline-none"
                              onfocus="this.closest('.var-field-wrap').style.borderColor='var(--color-border-input)'"
                              onblur="this.closest('.var-field-wrap').style.borderColor='var(--color-border-subtle)'"
                              @scroll="$el.closest('.var-field-wrap').querySelector('.vf-back').scrollTop = $el.scrollTop"></textarea>
                </div>
            </div>

            {{-- Form key-value body --}}
            <div x-show="currentRequest.body_type === 'form-data' || currentRequest.body_type === 'x-www-form-urlencoded'">
                <table class="w-full" style="border-collapse:collapse;">
                    <thead>
                        <tr class="text-[9px] uppercase tracking-widest" style="color:var(--color-border-input);">
                            <th class="pb-2 w-5 text-left"></th>
                            <th class="pb-2 pr-2 text-left">Key</th>
                            <th class="pb-2 text-left">Value</th>
                            <th class="pb-2 w-5"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(r, i) in currentRequest.body_form" :key="'bf'+i">
                            <tr class="kv-row">
                                <td class="pr-2 py-0.5 w-5">
                                    <input type="checkbox" x-model="r.enabled"
                                           class="w-3 h-3 cursor-pointer" style="accent-color:var(--color-brand);"/>
                                </td>
                                <td class="pr-1.5 py-0.5">
                                    <input x-model="r.key" type="text" placeholder="Key"
                                           class="w-full rounded px-2.5 py-1.5 text-xs font-mono focus:outline-none"
                                           style="background:var(--color-bg-base); border:1px solid var(--color-border-subtle); color:var(--color-text-input);"
                                           onfocus="this.style.borderColor='var(--color-border-input)'" onblur="this.style.borderColor='var(--color-border-subtle)'"/>
                                </td>
                                <td class="py-0.5">
                                    <div class="var-field-wrap w-full"
                                         @mousemove="onVarHover($event)" @mouseleave="varTooltip.show=false">
                                        <div class="vf-back" x-html="highlightVars(r.value)"></div>
                                        <input x-model="r.value" type="text" placeholder="Value"
                                               @input="checkVarAc($event)" @blur="varAc.show = false" @keydown.escape="varAc.show = false"
                                               class="vf-real focus:outline-none"
                                               onfocus="this.closest('.var-field-wrap').style.borderColor='var(--color-border-input)'"
                                               onblur="this.closest('.var-field-wrap').style.borderColor='var(--color-border-subtle)'"/>
                                    </div>
                                </td>
                                <td class="pl-1.5 py-0.5 w-5">
                                    <button @click="removeFormRow(i)"
                                            class="kv-del opacity-0 transition-opacity"
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
                <button @click="addFormRow()"
                        class="mt-2 flex items-center gap-1 text-[10px] transition-colors"
                        style="color:var(--color-border-input);"
                        onmouseover="this.style.color='var(--color-brand)'" onmouseout="this.style.color='var(--color-border-input)'">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add row
                </button>
            </div>

            {{-- None --}}
            <div x-show="currentRequest.body_type === 'none'">
                <p class="text-xs" style="color:var(--color-border-input);">This request has no body.</p>
            </div>
        </div>

        {{-- AUTH --}}
        <div x-show="requestTab === 'auth'">
            <div class="mb-4">
                <label class="block text-[9px] uppercase tracking-widest mb-2" style="color:var(--color-text-muted-5);">Auth Type</label>
                <select x-model="currentRequest.auth_type"
                        class="rounded px-3 py-2 text-xs focus:outline-none"
                        style="background:var(--color-bg-base); border:1px solid var(--color-border-input); color:var(--color-text-input);">
                    <option value="none">No Auth</option>
                    <option value="bearer">Bearer Token</option>
                    <option value="basic">Basic Auth</option>
                    <option value="api_key">API Key</option>
                </select>
            </div>

            {{-- Bearer --}}
            <div x-show="currentRequest.auth_type === 'bearer'" class="space-y-3">
                <div>
                    <label class="block text-[9px] uppercase tracking-widest mb-1.5" style="color:var(--color-text-muted-5);">Token</label>
                    <div class="var-field-wrap vf-md w-full"
                         @mousemove="onVarHover($event)" @mouseleave="varTooltip.show=false">
                        <div class="vf-back" x-html="highlightVars(currentRequest.auth_data.token ?? '')"></div>
                        <input x-model="currentRequest.auth_data.token"
                               type="text" placeholder="Enter bearer token"
                               @input="checkVarAc($event)" @blur="varAc.show = false" @keydown.escape="varAc.show = false"
                               class="vf-real focus:outline-none"
                               onfocus="this.closest('.var-field-wrap').style.borderColor='var(--color-border-input)'"
                               onblur="this.closest('.var-field-wrap').style.borderColor='var(--color-border-subtle)'"/>
                    </div>
                </div>
            </div>

            {{-- Basic --}}
            <div x-show="currentRequest.auth_type === 'basic'" class="space-y-3">
                <div>
                    <label class="block text-[9px] uppercase tracking-widest mb-1.5" style="color:var(--color-text-muted-5);">Username</label>
                    <input x-model="currentRequest.auth_data.username"
                           type="text" placeholder="username"
                           class="w-full rounded px-3 py-2 text-xs focus:outline-none"
                           style="background:var(--color-bg-base); border:1px solid var(--color-border-subtle); color:var(--color-text-input);"
                           onfocus="this.style.borderColor='var(--color-border-input)'" onblur="this.style.borderColor='var(--color-border-subtle)'"/>
                </div>
                <div>
                    <label class="block text-[9px] uppercase tracking-widest mb-1.5" style="color:var(--color-text-muted-5);">Password</label>
                    <input x-model="currentRequest.auth_data.password"
                           type="password" placeholder="password"
                           class="w-full rounded px-3 py-2 text-xs focus:outline-none"
                           style="background:var(--color-bg-base); border:1px solid var(--color-border-subtle); color:var(--color-text-input);"
                           onfocus="this.style.borderColor='var(--color-border-input)'" onblur="this.style.borderColor='var(--color-border-subtle)'"/>
                </div>
            </div>

            {{-- API Key --}}
            <div x-show="currentRequest.auth_type === 'api_key'" class="space-y-3">
                <div>
                    <label class="block text-[9px] uppercase tracking-widest mb-1.5" style="color:var(--color-text-muted-5);">Key Name</label>
                    <input x-model="currentRequest.auth_data.key"
                           type="text" placeholder="X-API-Key"
                           class="w-full rounded px-3 py-2 text-xs font-mono focus:outline-none"
                           style="background:var(--color-bg-base); border:1px solid var(--color-border-subtle); color:var(--color-text-input);"
                           onfocus="this.style.borderColor='var(--color-border-input)'" onblur="this.style.borderColor='var(--color-border-subtle)'"/>
                </div>
                <div>
                    <label class="block text-[9px] uppercase tracking-widest mb-1.5" style="color:var(--color-text-muted-5);">Value</label>
                    <div class="var-field-wrap vf-md w-full"
                         @mousemove="onVarHover($event)" @mouseleave="varTooltip.show=false">
                        <div class="vf-back" x-html="highlightVars(currentRequest.auth_data.value ?? '')"></div>
                        <input x-model="currentRequest.auth_data.value"
                               type="text" placeholder="API key value"
                               @input="checkVarAc($event)" @blur="varAc.show = false" @keydown.escape="varAc.show = false"
                               class="vf-real focus:outline-none"
                               onfocus="this.closest('.var-field-wrap').style.borderColor='var(--color-border-input)'"
                               onblur="this.closest('.var-field-wrap').style.borderColor='var(--color-border-subtle)'"/>
                    </div>
                </div>
                <div>
                    <label class="block text-[9px] uppercase tracking-widest mb-1.5" style="color:var(--color-text-muted-5);">Add To</label>
                    <select x-model="currentRequest.auth_data.in"
                            class="rounded px-3 py-2 text-xs focus:outline-none"
                            style="background:var(--color-bg-base); border:1px solid var(--color-border-input); color:var(--color-text-input);">
                        <option value="header">Header</option>
                        <option value="query">Query Param</option>
                    </select>
                </div>
            </div>

            {{-- None --}}
            <div x-show="currentRequest.auth_type === 'none'">
                <p class="text-xs" style="color:var(--color-border-input);">No authentication for this request.</p>
            </div>
        </div>

    </div>{{-- end tab content --}}
</div>{{-- end request config --}}
