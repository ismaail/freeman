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

        // Request/response split (percent for top panel, persisted in localStorage)
        splitPct: parseFloat(localStorage.getItem('freeman_split_pct') || '42'),

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

        // Folder modals
        folderModal: { open: false, collectionId: null, parentFolderId: null, parentFolderName: null, name: '', loading: false, error: null },
        renameFolderModal: { open: false, folderId: null, collectionId: null, name: '', loading: false, error: null },
        folderMenuOpen: null,

        // Save-to-collection modal
        saveModal: {
            open: false,
            name: 'New Request',
            collectionId: null,
            folderId: null,
            saving: false,
            error: null,
            path: [],   // [{id, name, type:'collection'|'folder'}] — browser nav stack
        },

        // Request being built
        currentRequest: {
            name: 'New Request',
            method: 'GET',
            url: '',
            params:    [{ key: '', value: '', enabled: true }],
            headers:   [{ key: '', value: '', enabled: true }],
            body_type: 'none',
            raw_body_type: 'json',
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
        responseViewMode: 'pretty',   // 'pretty' | 'raw'
        responseForceType: 'auto',    // 'auto' | 'json' | 'xml' | 'html' | 'text'
        responseCopied: false,

        // ---- Init ----

        init() {
            this.loadCollections();
            this.loadEnvironments();
        },

        // ---- Computed ----

        get activeEnvironment() {
            return this.environments.find(e => e.is_active) || null;
        },

        get responseDetectedType() {
            if (!this.response?.response_headers) return 'text';
            return this.detectContentType(this.response.response_headers);
        },

        // Items to show in the save-modal folder browser at the current path level.
        get saveModalBrowserItems() {
            if (!this.saveModal.path.length) {
                // Root: show all collections
                return this.collections.map(c => ({
                    id: c.id, name: c.name, type: 'collection',
                    hasChildren: (c.folders || []).some(f => (f.parent_folder_id ?? null) === null),
                }));
            }
            const col = this.collections.find(c => c.id == this.saveModal.collectionId);
            if (!col) return [];
            const last = this.saveModal.path[this.saveModal.path.length - 1];
            const parentId = last.type === 'folder' ? last.id : null;
            return (col.folders || [])
                .filter(f => (f.parent_folder_id ?? null) == parentId)
                .sort((a, b) => a.name.localeCompare(b.name))
                .map(f => ({
                    id: f.id, name: f.name, type: 'folder',
                    hasChildren: (col.folders || []).some(cf => cf.parent_folder_id == f.id),
                }));
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
                raw_body_type: 'json',
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
                    body_type:     d.body_type     || 'none',
                    raw_body_type: d.raw_body_type || 'json',
                    body:          d.body          || '',
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

            // Build effective headers — inject Content-Type for raw body if not already set
            let effectiveHeaders = this.currentRequest.headers.filter(h => h.key.trim());
            if (this.currentRequest.body_type === 'raw') {
                const hasContentType = effectiveHeaders.some(h => h.key.toLowerCase() === 'content-type');
                if (!hasContentType) {
                    const ctMap = { text: 'text/plain', json: 'application/json', javascript: 'application/javascript', xml: 'application/xml', html: 'text/html' };
                    const ct = ctMap[this.currentRequest.raw_body_type] ?? 'application/json';
                    effectiveHeaders = [{ key: 'Content-Type', value: ct, enabled: true }, ...effectiveHeaders];
                }
            }

            const payload = {
                method:        this.currentRequest.method,
                url,
                headers:       effectiveHeaders,
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

        openSaveModal() {
            this.saveModal = {
                open: true,
                name: this.currentRequest.name || 'New Request',
                collectionId: null,
                folderId: null,
                saving: false,
                error: null,
                path: [],
            };
        },

        // Navigate into a collection or folder in the save-modal browser
        saveModalNavigateInto(item) {
            this.saveModal.path = [...this.saveModal.path, { id: item.id, name: item.name, type: item.type }];
            if (item.type === 'collection') {
                this.saveModal.collectionId = item.id;
                this.saveModal.folderId = null;
            } else {
                this.saveModal.folderId = item.id;
            }
        },

        // Navigate back to a specific breadcrumb index (-1 = all collections root)
        saveModalNavigateTo(index) {
            if (index < 0) {
                this.saveModal.path = [];
                this.saveModal.collectionId = null;
                this.saveModal.folderId = null;
            } else {
                this.saveModal.path = this.saveModal.path.slice(0, index + 1);
                const item = this.saveModal.path[index];
                if (item.type === 'collection') {
                    this.saveModal.collectionId = item.id;
                    this.saveModal.folderId = null;
                } else {
                    this.saveModal.folderId = item.id;
                }
            }
        },

        async confirmSaveRequest() {
            if (!this.saveModal.collectionId) {
                this.saveModal.error = 'Please select a collection.';
                return;
            }
            this.saveModal.saving = true;
            this.saveModal.error = null;
            try {
                const res = await fetch('/requests', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept':       'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        name:          this.saveModal.name,
                        method:        this.currentRequest.method,
                        url:           this.currentRequest.url,
                        collection_id: this.saveModal.collectionId,
                        folder_id:     this.saveModal.folderId || null,
                        headers:       this.currentRequest.headers.filter(h => h.key.trim()),
                        body_type:     this.currentRequest.body_type,
                        raw_body_type: this.currentRequest.raw_body_type,
                        body:          this.currentRequest.body,
                        auth_type:     this.currentRequest.auth_type,
                        auth_data:     this.currentRequest.auth_data,
                    }),
                });
                const json = await res.json();
                if (!res.ok) {
                    this.saveModal.error = json.message || 'Failed to save.';
                    return;
                }
                this.activeRequestId = json.data.id;
                this.currentRequest.name = this.saveModal.name;
                this.currentRequest.collection_id = this.saveModal.collectionId;
                this.saveModal.open = false;
                await this.loadCollections();
            } catch (e) {
                this.saveModal.error = 'An error occurred.';
                console.error('confirmSaveRequest:', e);
            } finally {
                this.saveModal.saving = false;
            }
        },

        async saveRequest() {
            if (!this.activeRequestId) {
                this.openSaveModal();
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
                        headers:       this.currentRequest.headers.filter(h => h.key.trim()),
                        body_type:     this.currentRequest.body_type,
                        raw_body_type: this.currentRequest.raw_body_type,
                        body:          this.currentRequest.body,
                        auth_type:     this.currentRequest.auth_type,
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

        // ---- Folder tree (flat depth-first for sidebar rendering) ----
        // The API returns all folders flat (with parent_folder_id). We build the
        // display list here via a depth-first walk so Alpine can iterate a simple array.

        flatCollectionTree(col) {
            const rows      = [];
            const allFolders = col.folders || [];
            // Build a map: parentId → [child folders]
            const childMap  = {};
            for (const f of allFolders) {
                const pid = f.parent_folder_id ?? null;
                if (!childMap[pid]) childMap[pid] = [];
                childMap[pid].push(f);
            }
            const walk = (parentId, depth) => {
                const children = childMap[parentId] || [];
                for (const folder of children) {
                    rows.push({ type: 'folder', folder, depth, collectionId: col.id });
                    if (this.isFolderExpanded(folder.id)) {
                        for (const req of (folder.requests || [])) {
                            rows.push({ type: 'request', req, depth: depth + 1 });
                        }
                        walk(folder.id, depth + 1);
                    }
                }
            };
            // Root-level requests first
            for (const req of (col.requests || [])) {
                rows.push({ type: 'request', req, depth: 0 });
            }
            // Then folders starting from root (parent_folder_id = null)
            walk(null, 0);
            return rows;
        },

        // ---- Folder context menu ----

        toggleFolderMenu(id) {
            this.folderMenuOpen = this.folderMenuOpen === id ? null : id;
        },

        // ---- Create folder ----

        openNewFolderModal(collectionId, parentFolderId = null, parentFolderName = null) {
            this.folderModal = { open: true, collectionId, parentFolderId, parentFolderName, name: '', loading: false, error: null };
            this.collectionMenuOpen = null;
            this.folderMenuOpen = null;
        },

        async createFolder() {
            const name = this.folderModal.name.trim();
            if (!name) return;
            this.folderModal.loading = true;
            this.folderModal.error = null;
            const payload = { name };
            if (this.folderModal.parentFolderId) payload.parent_folder_id = this.folderModal.parentFolderId;
            try {
                const res  = await fetch(`/collections/${this.folderModal.collectionId}/folders`, {
                    method: 'POST',
                    headers: {
                        'Content-Type':     'application/json',
                        'Accept':           'application/json',
                        'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(payload),
                });
                const json = await res.json();
                if (res.ok) {
                    this.folderModal.open = false;
                    await this.loadCollections();
                    this.expandedCollections = { ...this.expandedCollections, [this.folderModal.collectionId]: true };
                    if (this.folderModal.parentFolderId) {
                        this.expandedFolders = { ...this.expandedFolders, [this.folderModal.parentFolderId]: true };
                    }
                } else {
                    this.folderModal.error = json.message || 'Could not create folder.';
                }
            } catch (e) {
                this.folderModal.error = 'Network error.';
            } finally {
                this.folderModal.loading = false;
            }
        },

        // ---- Rename folder ----

        openRenameFolderModal(folderId, collectionId, currentName) {
            this.renameFolderModal = { open: true, folderId, collectionId, name: currentName, loading: false, error: null };
            this.folderMenuOpen = null;
        },

        async saveRenameFolder() {
            const name = this.renameFolderModal.name.trim();
            if (!name) return;
            this.renameFolderModal.loading = true;
            this.renameFolderModal.error = null;
            try {
                const res  = await fetch(`/collections/${this.renameFolderModal.collectionId}/folders/${this.renameFolderModal.folderId}`, {
                    method: 'PATCH',
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
                    this.renameFolderModal.open = false;
                    await this.loadCollections();
                } else {
                    this.renameFolderModal.error = json.message || 'Could not rename folder.';
                }
            } catch (e) {
                this.renameFolderModal.error = 'Network error.';
            } finally {
                this.renameFolderModal.loading = false;
            }
        },

        // ---- Delete folder ----

        async deleteFolder(folderId, collectionId) {
            if (!confirm('Delete this folder and all its contents?')) return;
            try {
                await fetch(`/collections/${collectionId}/folders/${folderId}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept':           'application/json',
                        'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                this.folderMenuOpen = null;
                await this.loadCollections();
            } catch (e) {
                console.error('deleteFolder:', e);
            }
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

        startSplitDrag(e) {
            const container = this.$refs.splitContainer;
            const onMove = (mv) => {
                const rect = container.getBoundingClientRect();
                const pct = ((mv.clientY - rect.top) / rect.height) * 100;
                this.splitPct = Math.min(Math.max(pct, 15), 80);
            };
            const onUp = () => {
                document.removeEventListener('mousemove', onMove);
                document.removeEventListener('mouseup', onUp);
                localStorage.setItem('freeman_split_pct', this.splitPct.toFixed(1));
            };
            document.addEventListener('mousemove', onMove);
            document.addEventListener('mouseup', onUp);
        },

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
            if (v.includes('json'))        return 'json';
            if (v.includes('html'))        return 'html';
            if (v.includes('xml'))         return 'xml';
            if (v.includes('javascript'))  return 'javascript';
            return 'text';
        },

        renderResponseBody(body, headers) {
            if (!body) return '<span style="color:var(--color-border-input);">— empty response —</span>';
            const type = this.responseForceType !== 'auto'
                ? this.responseForceType
                : this.detectContentType(headers);
            if (this.responseViewMode === 'raw') return this.escHtml(body);
            if (type === 'json')                    return this.highlightJson(body);
            if (type === 'xml' || type === 'html')  return this.highlightXml(body);
            if (type === 'javascript')              return this.highlightJsEditor(body);
            return this.escHtml(body);
        },

        async copyResponseBody() {
            if (!this.response?.response_body) return;
            try {
                await navigator.clipboard.writeText(this.response.response_body);
                this.responseCopied = true;
                setTimeout(() => { this.responseCopied = false; }, 2000);
            } catch {}
        },

        // Escape HTML entities in a raw string
        escHtml(s) {
            return String(s)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
        },

        // Dispatcher for body editor syntax highlighting (no reformatting — preserves alignment)
        highlightBodyContent(text) {
            if (!text) return '';
            const type = this.currentRequest.raw_body_type;
            if (type === 'json')                    return this.highlightJsonEditor(text);
            if (type === 'xml' || type === 'html')  return this.highlightXmlEditor(text);
            if (type === 'javascript')              return this.highlightJsEditor(text);
            return this.highlightVars(text);  // plain text — still show var marks
        },

        // JSON coloriser for the body editor — character-level tokeniser, NO reformatting
        highlightJsonEditor(body) {
            if (!body) return '';
            const len = body.length;
            let html = '';
            let i = 0;
            while (i < len) {
                const ch = body[i];
                if (ch === '"') {
                    let j = i + 1;
                    while (j < len) {
                        if (body[j] === '\\') { j += 2; continue; }
                        if (body[j] === '"')  { j++; break; }
                        j++;
                    }
                    const token = body.slice(i, j);
                    let k = j;
                    while (k < len && (body[k] === ' ' || body[k] === '\t' || body[k] === '\n' || body[k] === '\r')) k++;
                    const isKey = body[k] === ':';
                    html += isKey
                        ? `<span class="json-key">${this.escHtml(token)}</span>`
                        : `<span class="json-str">${this.escHtml(token)}</span>`;
                    i = j;
                } else if (body.startsWith('true', i)) {
                    html += '<span class="json-bool">true</span>';  i += 4;
                } else if (body.startsWith('false', i)) {
                    html += '<span class="json-bool">false</span>'; i += 5;
                } else if (body.startsWith('null', i)) {
                    html += '<span class="json-null">null</span>';  i += 4;
                } else if (ch === '-' || (ch >= '0' && ch <= '9')) {
                    let j = i;
                    if (body[j] === '-') j++;
                    while (j < len && body[j] >= '0' && body[j] <= '9') j++;
                    if (j < len && body[j] === '.') {
                        j++;
                        while (j < len && body[j] >= '0' && body[j] <= '9') j++;
                    }
                    if (j < len && (body[j] === 'e' || body[j] === 'E')) {
                        j++;
                        if (j < len && (body[j] === '+' || body[j] === '-')) j++;
                        while (j < len && body[j] >= '0' && body[j] <= '9') j++;
                    }
                    html += `<span class="json-num">${body.slice(i, j)}</span>`;
                    i = j;
                } else {
                    html += this.escHtml(ch);
                    i++;
                }
            }
            return html;
        },

        // XML/HTML coloriser for the body editor — colorise only, NO indentation reformatting
        highlightXmlEditor(body) {
            if (!body) return '';
            const esc = this.escHtml(body);
            return esc
                .replace(/(&lt;!--[\s\S]*?--&gt;)/g,
                    '<span class="xml-comment">$1</span>')
                .replace(/(&lt;\?[\s\S]*?\?&gt;)/g,
                    '<span class="xml-bracket">$1</span>')
                .replace(
                    /(&lt;\/?)([\w][\w:.-]*)((?:[^&]|&(?!gt;))*?)(\/?&gt;)/g,
                    (_, open, tag, attrs, close) => {
                        const coloredAttrs = attrs
                            .replace(/([\w][\w:.-]*)=/g, '<span class="xml-attr">$1</span>=')
                            .replace(/=("(?:[^"])*")/g, '=<span class="xml-val">$1</span>');
                        return '<span class="xml-bracket">' + open + '</span>'
                             + '<span class="xml-tag">'     + tag  + '</span>'
                             + coloredAttrs
                             + '<span class="xml-bracket">' + close + '</span>';
                    }
                );
        },

        // JavaScript coloriser for the body editor — highlights comments only (safe, no false positives)
        highlightJsEditor(body) {
            if (!body) return '';
            const esc = this.escHtml(body);
            return esc
                .replace(/(\/\*[\s\S]*?\*\/)/g, '<span class="xml-comment">$1</span>')
                .replace(/(\/\/[^\n]*)/g,        '<span class="xml-comment">$1</span>');
        },

        // Pretty-print the raw body in-place (updates currentRequest.body)
        formatBody() {
            const type = this.currentRequest.raw_body_type;
            const src  = this.currentRequest.body ?? '';
            if (type === 'json') {
                try { this.currentRequest.body = JSON.stringify(JSON.parse(src), null, 2); } catch {}
            } else if (type === 'xml' || type === 'html') {
                try {
                    let depth = 0;
                    this.currentRequest.body = src
                        .replace(/>\s*</g, '>\n<')
                        .split('\n')
                        .map(raw => {
                            const line = raw.trim();
                            if (!line) return null;
                            if (/^<\//.test(line) || /^-->/.test(line)) depth = Math.max(0, depth - 1);
                            const out = '  '.repeat(depth) + line;
                            if (/^<[^/?!]/.test(line) && !line.endsWith('/>') && !/<\//.test(line)) depth++;
                            return out;
                        })
                        .filter(l => l !== null)
                        .join('\n');
                } catch {}
            }
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
                // Processing instructions (XML PI nodes)
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
