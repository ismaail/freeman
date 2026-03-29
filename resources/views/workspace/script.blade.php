<script>
function workspace() {
    return {
        // Layout
        sidebarTab: 'collections',
        requestOpen: false,
        requestTab: 'params',
        responseTab: 'body',
        userMenuOpen: false,
        envMenuOpen: false,

        // Data
        collections: [],
        environments: [],
        collectionsLoading: true,

        // Resolved variables for the current request's collection (flat key→value)
        currentCollectionVars: {},

        // Floating tooltip shown when hovering a {variable} in the URL bar
        varTooltip: { show: false, text: '', x: 0, y: 0, isUndef: false },

        // Autocomplete dropdown for {variable} in any input
        varAc: { show: false, suggestions: [], x: 0, y: 0, anchor: null },

        // Collection variables modal
        collectionVarsModal: {
            open: false,
            collectionId: null,
            collectionName: '',
            variables: [],
            saving: false,
        },

        // Sidebar state
        expandedCollections: {},
        expandedFolders: {},
        activeRequestId: null,
        collectionMenuOpen: null,
        importNotification: null,
        addCollectionMenuOpen: false,
        newCollectionModal: false,
        newCollectionName: '',
        newCollectionLoading: false,
        newCollectionError: null,

        // Request being built
        currentRequest: {
            name: 'New Request',
            method: 'GET',
            url: '',
            params:    [{ key: '', value: '', enabled: true }],
            headers:   [{ key: '', value: '', enabled: true }],
            body_type: 'none',
            body: '',
            body_form: [{ key: '', value: '', enabled: true }],
            auth_type: 'none',
            auth_data: { token: '', username: '', password: '', key: '', value: '', in: 'header' },
        },

        // URL field
        urlFocused: false,

        // Response
        response: null,
        isLoading: false,

        // ---- Init ----

        init() {
            this.loadCollections();
            this.loadEnvironments();
        },

        // ---- Computed ----

        get activeEnvironment() {
            return this.environments.find(e => e.is_active) || null;
        },

        get filledParamCount() {
            return (this.currentRequest.params || []).filter(p => p.key.trim()).length;
        },

        get filledHeaderCount() {
            return (this.currentRequest.headers || []).filter(h => h.key.trim()).length;
        },

        // ---- Data loading ----

        async loadCollections() {
            this.collectionsLoading = true;
            try {
                const res  = await fetch('/collections', { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                const json = await res.json();
                this.collections = json.data || [];
            } catch (e) {
                console.error('loadCollections:', e);
                this.collections = [];
            } finally {
                this.collectionsLoading = false;
            }
        },

        async loadEnvironments() {
            try {
                const res  = await fetch('/environments', { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                const json = await res.json();
                this.environments = Array.isArray(json) ? json : [];
            } catch (e) {
                console.error('loadEnvironments:', e);
                this.environments = [];
            }
        },

        // ---- Sidebar expand/collapse ----

        toggleCollection(id) {
            this.expandedCollections = { ...this.expandedCollections, [id]: !this.expandedCollections[id] };
        },
        isCollectionExpanded(id) { return !!this.expandedCollections[id]; },

        toggleFolder(id) {
            this.expandedFolders = { ...this.expandedFolders, [id]: !this.expandedFolders[id] };
        },
        isFolderExpanded(id) { return !!this.expandedFolders[id]; },

        // ---- Request management ----

        blankRequest() {
            return {
                collection_id: null,
                name: 'New Request',
                method: 'GET',
                url: '',
                params:    [{ key: '', value: '', enabled: true }],
                headers:   [{ key: '', value: '', enabled: true }],
                body_type: 'none',
                body: '',
                body_form: [{ key: '', value: '', enabled: true }],
                auth_type: 'none',
                auth_data: { token: '', username: '', password: '', key: '', value: '', in: 'header' },
            };
        },

        newRequest() {
            this.activeRequestId     = null;
            this.currentRequest      = this.blankRequest();
            this.currentCollectionVars = {};
            this.response            = null;
            this.requestTab          = 'params';
            this.requestOpen         = true;
        },

        async openRequest(requestId) {
            this.requestOpen     = true;
            this.response        = null;
            this.activeRequestId = requestId;

            try {
                const res  = await fetch(`/requests/${requestId}`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                const json = await res.json();
                const d    = json.data;
                const ad   = d.auth_data || {};

                if (d.collection_id) {
                    this.loadCurrentCollectionVars(d.collection_id);
                } else {
                    this.currentCollectionVars = {};
                }

                this.currentRequest = {
                    collection_id: d.collection_id || null,
                    name:      d.name      || 'Untitled',
                    method:    d.method    || 'GET',
                    url:       d.url       || '',
                    params:    [{ key: '', value: '', enabled: true }],
                    headers:   Array.isArray(d.headers) && d.headers.length
                                   ? d.headers
                                   : [{ key: '', value: '', enabled: true }],
                    body_type: d.body_type || 'none',
                    body:      d.body      || '',
                    body_form: [{ key: '', value: '', enabled: true }],
                    auth_type: d.auth_type || 'none',
                    auth_data: {
                        token:    ad.token    || '',
                        username: ad.username || '',
                        password: ad.password || '',
                        key:      ad.key      || '',
                        value:    ad.value    || '',
                        in:       ad.in       || 'header',
                    },
                };
            } catch (e) {
                console.error('openRequest:', e);
            }
        },

        // ---- Send ----

        async sendRequest() {
            if (!this.currentRequest.url.trim() || this.isLoading) return;

            this.isLoading = true;
            this.response  = null;

            // Append query params to URL
            let url = this.currentRequest.url;
            const qp = this.currentRequest.params.filter(p => p.enabled && p.key.trim());
            if (qp.length) {
                const qs = qp.map(p => encodeURIComponent(p.key) + '=' + encodeURIComponent(p.value)).join('&');
                url += (url.includes('?') ? '&' : '?') + qs;
            }

            // Serialize form body if needed
            let body = this.currentRequest.body;
            if (['form-data', 'x-www-form-urlencoded'].includes(this.currentRequest.body_type)) {
                body = JSON.stringify(this.currentRequest.body_form.filter(r => r.key.trim()));
            }

            const payload = {
                method:        this.currentRequest.method,
                url,
                headers:       this.currentRequest.headers.filter(h => h.key.trim()),
                body_type:     this.currentRequest.body_type,
                body,
                auth_type:     this.currentRequest.auth_type,
                auth_data:     this.currentRequest.auth_data,
                request_id:    this.activeRequestId,
                collection_id: this.currentRequest.collection_id,
            };

            try {
                const res     = await fetch('/run', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept':       'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(payload),
                });
                this.response    = await res.json();
                this.responseTab = 'body';
            } catch (e) {
                this.response = { success: false, error: e.message, status: 0, response_time_ms: 0, response_body: '', response_headers: {} };
            } finally {
                this.isLoading = false;
            }
        },

        // ---- Save (existing request only; full save modal is future work) ----

        async saveRequest() {
            if (!this.activeRequestId) {
                // TODO: open "save to collection" modal
                alert('Choose a collection to save to — save modal coming soon.');
                return;
            }
            try {
                await fetch(`/requests/${this.activeRequestId}`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept':       'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        name:      this.currentRequest.name,
                        method:    this.currentRequest.method,
                        url:       this.currentRequest.url,
                        headers:   this.currentRequest.headers.filter(h => h.key.trim()),
                        body_type: this.currentRequest.body_type,
                        body:      this.currentRequest.body,
                        auth_type: this.currentRequest.auth_type,
                        auth_data: this.currentRequest.auth_data,
                    }),
                });
                await this.loadCollections(); // refresh sidebar
            } catch (e) {
                console.error('saveRequest:', e);
            }
        },

        // ---- Environments ----

        async activateEnvironment(id) {
            try {
                await fetch(`/environments/${id}/activate`, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                });
                this.envMenuOpen = false;
                await this.loadEnvironments();
            } catch (e) { console.error('activateEnvironment:', e); }
        },

        async deactivateEnvironment() {
            try {
                await fetch('/environments/deactivate', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                });
                this.envMenuOpen = false;
                await this.loadEnvironments();
            } catch (e) { console.error('deactivateEnvironment:', e); }
        },

        // ---- Collection context menu ----

        toggleCollectionMenu(id) {
            this.collectionMenuOpen = this.collectionMenuOpen === id ? null : id;
        },

        // ---- Export ----

        exportCollection(id) {
            window.location.href = `/collections/${id}/export`;
        },

        // ---- Create collection ----

        async createCollection() {
            const name = this.newCollectionName.trim();
            if (!name) return;
            this.newCollectionLoading = true;
            this.newCollectionError = null;
            try {
                const res  = await fetch('/collections', {
                    method: 'POST',
                    headers: {
                        'Content-Type':     'application/json',
                        'Accept':           'application/json',
                        'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ name }),
                });
                const json = await res.json();
                if (res.ok) {
                    this.newCollectionModal = false;
                    this.newCollectionName  = '';
                    await this.loadCollections();
                } else {
                    this.newCollectionError = json.message || 'Could not create collection.';
                }
            } catch (e) {
                this.newCollectionError = 'Network error.';
            } finally {
                this.newCollectionLoading = false;
            }
        },

        // ---- Import ----

        importCollection() {
            this.$refs.importFileInput.value = '';
            this.$refs.importFileInput.click();
        },

        async handleImportFile(files) {
            if (!files || !files[0]) return;

            const formData = new FormData();
            formData.append('file', files[0]);

            try {
                const res  = await fetch('/collections/import', {
                    method: 'POST',
                    headers: {
                        'Accept':       'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData,
                });
                const json = await res.json();

                if (res.ok) {
                    this.importNotification = { ok: true, msg: `Imported "${json.data.name}" successfully.` };
                    await this.loadCollections();
                } else {
                    this.importNotification = { ok: false, msg: json.message || 'Import failed.' };
                }
            } catch (e) {
                this.importNotification = { ok: false, msg: 'Network error during import.' };
            }

            // Auto-dismiss after 4 seconds
            setTimeout(() => { this.importNotification = null; }, 4000);
        },

        // ---- Delete collection ----

        async deleteCollection(id) {
            if (!confirm('Delete this collection and all its requests?')) return;
            try {
                await fetch(`/collections/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept':       'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                if (this.activeRequestId !== null) {
                    // If a request from the deleted collection was open, clear it
                    this.requestOpen = false;
                    this.activeRequestId = null;
                }
                await this.loadCollections();
            } catch (e) {
                console.error('deleteCollection:', e);
            }
        },

        // ---- Collection variable loading ----

        async loadCurrentCollectionVars(collectionId) {
            try {
                const res  = await fetch(`/collections/${collectionId}/variables`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                const json = await res.json();
                const map  = {};
                (json.data || []).forEach(v => { if (v.enabled && v.key) map[v.key] = v.value; });
                this.currentCollectionVars = map;
            } catch (e) {
                this.currentCollectionVars = {};
            }
        },

        // ---- URL variable hover tooltip ----
        // Uses elementFromPoint trick: briefly disable pointer-events on the real
        // input so document.elementFromPoint() can reach the backdrop marks.

        onVarHover(event) {
            // Works for URL bar (.url-field-wrap) and generic wrappers (.var-field-wrap).
            const wrap  = event.currentTarget;
            const input = wrap.querySelector('input, textarea');
            if (!input) return;

            // Hide the real input from hit-testing so elementFromPoint can reach
            // the <mark> elements in the backdrop behind it.
            input.style.pointerEvents = 'none';
            const el = document.elementFromPoint(event.clientX, event.clientY);
            input.style.pointerEvents = '';

            if (el && el.classList && el.classList.contains('url-var')) {
                const isUndef = el.classList.contains('url-var-err');
                const rect    = el.getBoundingClientRect();
                this.varTooltip = {
                    show:    true,
                    text:    isUndef ? '' : (el.dataset.val ?? ''),
                    name:    el.dataset.name ?? '',
                    x:       rect.left + rect.width / 2,
                    y:       rect.top,
                    isUndef,
                };
            } else {
                this.varTooltip.show = false;
            }
        },

        varLabel(name) {
            return '{' + '{' + name + '}' + '}';
        },

        highlightVars(text) {
            return this.highlightUrl(text);
        },

        // ---- variable autocomplete ----

        checkVarAc(event) {
            const el = event.target;
            if (!el || (el.tagName !== 'INPUT' && el.tagName !== 'TEXTAREA')) return;
            if (!Object.keys(this.currentCollectionVars).length) { this.varAc.show = false; return; }

            const val    = el.value;
            const pos    = el.selectionStart ?? val.length;
            const before = val.slice(0, pos);
            const openAt = before.lastIndexOf('{' + '{');

            if (openAt === -1 || before.slice(openAt + 2).includes('}' + '}')) {
                this.varAc.show = false;
                return;
            }

            const query       = before.slice(openAt + 2).toLowerCase();
            const suggestions = Object.keys(this.currentCollectionVars)
                .filter(k => k.toLowerCase().includes(query));

            if (!suggestions.length) { this.varAc.show = false; return; }

            const rect    = el.getBoundingClientRect();
            this.varAc.x  = rect.left;
            this.varAc.y  = rect.bottom + 4;
            this.varAc.suggestions = suggestions;
            this.varAc.anchor      = el;
            this.varAc.show        = true;
        },

        selectVarAc(name) {
            const el = this.varAc.anchor;
            if (!el) return;

            const val    = el.value;
            const pos    = el.selectionStart ?? val.length;
            const before = val.slice(0, pos);
            const openAt = before.lastIndexOf('{' + '{');
            const newVal = val.slice(0, openAt) + '{' + '{' + name + '}' + '}' + val.slice(pos);
            const newPos = openAt + name.length + 4; // open + name + close

            el.value = newVal;
            el.dispatchEvent(new Event('input', { bubbles: true }));
            setTimeout(() => { el.focus(); el.setSelectionRange(newPos, newPos); }, 0);
            this.varAc.show = false;
        },

        // ---- Collection variables modal ----

        async openCollectionVariables(collectionId, collectionName) {
            this.collectionVarsModal.collectionId   = collectionId;
            this.collectionVarsModal.collectionName = collectionName;
            this.collectionVarsModal.saving         = false;
            this.collectionVarsModal.variables      = [];
            this.collectionVarsModal.open           = true;

            try {
                const res  = await fetch(`/collections/${collectionId}/variables`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                const json = await res.json();
                this.collectionVarsModal.variables = (json.data && json.data.length)
                    ? json.data
                    : [{ key: '', value: '', enabled: true }];
            } catch (e) {
                console.error('openCollectionVariables:', e);
                this.collectionVarsModal.variables = [{ key: '', value: '', enabled: true }];
            }
        },

        addVariableRow() {
            this.collectionVarsModal.variables.push({ key: '', value: '', enabled: true });
        },

        removeVariableRow(i) {
            this.collectionVarsModal.variables.splice(i, 1);
            if (!this.collectionVarsModal.variables.length) {
                this.collectionVarsModal.variables.push({ key: '', value: '', enabled: true });
            }
        },

        async saveCollectionVariables() {
            this.collectionVarsModal.saving = true;
            try {
                await fetch(`/collections/${this.collectionVarsModal.collectionId}/variables`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type':     'application/json',
                        'Accept':           'application/json',
                        'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        variables: this.collectionVarsModal.variables.filter(v => v.key.trim()),
                    }),
                });
                // Reload vars if the saved collection is the active one
                if (this.currentRequest.collection_id === this.collectionVarsModal.collectionId) {
                    await this.loadCurrentCollectionVars(this.collectionVarsModal.collectionId);
                }
                this.collectionVarsModal.open = false;
            } catch (e) {
                console.error('saveCollectionVariables:', e);
            } finally {
                this.collectionVarsModal.saving = false;
            }
        },

        // ---- Key-value row helpers ----

        addParam()      { this.currentRequest.params.push({ key: '', value: '', enabled: true }); },
        removeParam(i)  { this.currentRequest.params.splice(i, 1); },
        addHeader()     { this.currentRequest.headers.push({ key: '', value: '', enabled: true }); },
        removeHeader(i) { this.currentRequest.headers.splice(i, 1); },
        addFormRow()      { this.currentRequest.body_form.push({ key: '', value: '', enabled: true }); },
        removeFormRow(i)  { this.currentRequest.body_form.splice(i, 1); },

        // ---- Style helpers ----

        methodColor(method) {
            return { GET: 'text-green-400', POST: 'text-yellow-400', PUT: 'text-blue-400', PATCH: 'text-purple-400', DELETE: 'text-red-400' }[method] || 'text-gray-400';
        },

        statusColor(status) {
            if (!status) return 'text-gray-400';
            if (status < 300) return 'text-green-400';
            if (status < 400) return 'text-blue-400';
            if (status < 500) return 'text-yellow-400';
            return 'text-red-400';
        },

        statusLabel(status) {
            if (!status) return 'text-gray-600';
            if (status < 300) return 'text-green-700';
            if (status < 400) return 'text-blue-700';
            if (status < 500) return 'text-yellow-700';
            return 'text-red-700';
        },

        statusText(status) {
            const map = { 200:'OK', 201:'Created', 204:'No Content', 301:'Moved', 302:'Found', 304:'Not Modified', 400:'Bad Request', 401:'Unauthorized', 403:'Forbidden', 404:'Not Found', 405:'Method Not Allowed', 409:'Conflict', 422:'Unprocessable', 429:'Too Many Requests', 500:'Internal Server Error', 502:'Bad Gateway', 503:'Service Unavailable' };
            return map[status] ? map[status] : '';
        },

        // ---- URL variable highlight ----

        highlightUrl(url) {
            if (!url) return '';
            const vars = this.currentCollectionVars;
            return this.escHtml(url).replace(/\{\{([^}]*)\}\}/g, (match, key) => {
                const k = key.trim();
                if (k in vars) {
                    return `<mark class="url-var url-var-ok" data-name="${this.escHtml(k)}" data-val="${this.escHtml(vars[k])}">@{{${this.escHtml(key)}}}</mark>`;
                }
                return `<mark class="url-var url-var-err" data-name="${this.escHtml(k)}">@{{${this.escHtml(key)}}}</mark>`;
            });
        },

        // ---- Response body rendering ----

        // Detect content type from response headers object
        detectContentType(headers) {
            if (!headers) return 'text';
            const entry = Object.entries(headers)
                .find(([k]) => k.toLowerCase() === 'content-type');
            if (!entry) return 'text';
            const v = entry[1].toLowerCase();
            if (v.includes('json'))                       return 'json';
            if (v.includes('xml') || v.includes('html')) return 'xml';
            return 'text';
        },

        renderResponseBody(body, headers) {
            if (!body) return '<span style="color:var(--color-border-input);">— empty response —</span>';
            const type = this.detectContentType(headers);
            if (type === 'json') return this.highlightJson(body);
            if (type === 'xml')  return this.highlightXml(body);
            return this.escHtml(body);
        },

        // Escape HTML entities in a raw string
        escHtml(s) {
            return String(s)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
        },

        // JSON syntax highlight — character-level tokeniser (no double-wrap issues)
        highlightJson(body) {
            let fmt;
            try { fmt = JSON.stringify(JSON.parse(body), null, 2); }
            catch { return '<span class="json-punct">' + this.escHtml(body) + '</span>'; }

            let html = '';
            let i = 0;
            const len = fmt.length;

            while (i < len) {
                const ch = fmt[i];

                if (ch === '"') {
                    // Scan to end of JSON string (respects backslash escapes)
                    let j = i + 1;
                    while (j < len) {
                        if (fmt[j] === '\\') { j += 2; continue; }
                        if (fmt[j] === '"')  { j++; break; }
                        j++;
                    }
                    const token = fmt.slice(i, j);

                    // Peek ahead to decide: key (followed by colon) or string value
                    let k = j;
                    while (k < len && fmt[k] === ' ') k++;
                    const isKey = fmt[k] === ':';

                    html += isKey
                        ? `<span class="json-key">${this.escHtml(token)}</span>`
                        : `<span class="json-str">${this.escHtml(token)}</span>`;
                    i = j;

                } else if (fmt.startsWith('true', i)) {
                    html += '<span class="json-bool">true</span>';  i += 4;
                } else if (fmt.startsWith('false', i)) {
                    html += '<span class="json-bool">false</span>'; i += 5;
                } else if (fmt.startsWith('null', i)) {
                    html += '<span class="json-null">null</span>';  i += 4;

                } else if (ch === '-' || (ch >= '0' && ch <= '9')) {
                    // Number — scan digits, optional decimal, optional exponent
                    let j = i;
                    if (fmt[j] === '-') j++;
                    while (j < len && fmt[j] >= '0' && fmt[j] <= '9') j++;
                    if (j < len && fmt[j] === '.') {
                        j++;
                        while (j < len && fmt[j] >= '0' && fmt[j] <= '9') j++;
                    }
                    if (j < len && (fmt[j] === 'e' || fmt[j] === 'E')) {
                        j++;
                        if (j < len && (fmt[j] === '+' || fmt[j] === '-')) j++;
                        while (j < len && fmt[j] >= '0' && fmt[j] <= '9') j++;
                    }
                    html += `<span class="json-num">${fmt.slice(i, j)}</span>`;
                    i = j;

                } else {
                    // Structural punctuation & whitespace
                    html += this.escHtml(ch);
                    i++;
                }
            }
            return html;
        },

        // XML/HTML syntax highlight — indent then colorise each tag as a unit.
        highlightXml(body) {
            // Step 1: basic indentation
            let fmt;
            try {
                let depth = 0;
                fmt = body
                    .replace(/>\s*</g, '>\n<')
                    .split('\n')
                    .map(raw => {
                        const line = raw.trim();
                        if (!line) return null;
                        if (/^<\//.test(line) || /^-->/.test(line))
                            depth = Math.max(0, depth - 1);
                        const out = '  '.repeat(depth) + line;
                        if (/^<[^/?!]/.test(line) && !line.endsWith('/>') && !/<\//.test(line))
                            depth++;
                        return out;
                    })
                    .filter(l => l !== null)
                    .join('\n');
            } catch { fmt = body; }

            // Step 2: HTML-escape the entire string
            const esc = this.escHtml(fmt);

            // Step 3: colorise in safe order
            return esc
                // Comments (process before tags — may contain tag-like text inside)
                .replace(
                    /(&lt;!--[\s\S]*?--&gt;)/g,
                    '<span class="xml-comment">$1</span>'
                )
                // Processing instructions  <?...?>
                .replace(
                    /(&lt;\?[\s\S]*?\?&gt;)/g,
                    '<span class="xml-bracket">$1</span>'
                )
                // Every other tag — processed as ONE unit
                .replace(
                    /(&lt;\/?)([\w][\w:.-]*)((?:[^&]|&(?!gt;))*?)(\/?&gt;)/g,
                    (_, open, tag, attrs, close) => {
                        const coloredAttrs = attrs
                            .replace(/([\w][\w:.-]*)=/g,
                                '<span class="xml-attr">$1</span>=')
                            .replace(/=("(?:[^"])*")/g,
                                '=<span class="xml-val">$1</span>');
                        return '<span class="xml-bracket">' + open + '</span>'
                             + '<span class="xml-tag">'     + tag  + '</span>'
                             + coloredAttrs
                             + '<span class="xml-bracket">' + close + '</span>';
                    }
                );
        },

        responseSize(body) {
            if (!body) return '0 B';
            const b = new Blob([body]).size;
            if (b < 1024)        return b + ' B';
            if (b < 1048576)     return (b / 1024).toFixed(1) + ' KB';
            return (b / 1048576).toFixed(1) + ' MB';
        },
    };
}
</script>
