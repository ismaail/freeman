<script>
function workspace() {
    return {
        // Layout
        sidebarTab: 'collections',
        userMenuOpen: false,
        envMenuOpen: false,

        // Request/response split (percent for top panel, persisted in localStorage)
        splitPct: parseFloat(localStorage.getItem('freeman_split_pct') || '42'),

        // Data
        collections: [],
        environments: [],
        collectionsLoading: true,

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
        renameCollectionModal: { open: false, collectionId: null, name: '', loading: false, error: null },
        folderMenuOpen: null,

        // Save-to-collection modal
        saveModal: {
            open: false,
            name: 'New Request',
            collectionId: null,
            folderId: null,
            saving: false,
            error: null,
            path: [],
        },

        // URL field focus state
        urlFocused: false,

        // Copy button flash state (top-level — transient UI, not tab-specific)
        responseCopied: false,

        // ---- TABS ----
        tabs: [],
        activeTabId: null,

        // ---- Init ----

        init() {
            this.loadCollections();
            this.loadEnvironments();
            this.restoreTabs();

            // Ctrl+S → Save request (prevent browser "Save page" dialog)
            window.addEventListener('keydown', (e) => {
                if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                    e.preventDefault();
                    this.saveRequest();
                }
            });
        },

        // ---- Computed ----

        get activeTab() {
            return this.tabs.find(t => t.id === this.activeTabId) ?? null;
        },

        get activeEnvironment() {
            return this.environments.find(e => e.is_active) || null;
        },

        get responseDetectedType() {
            if (!this.activeTab?.response?.response_headers) return 'text';
            return this.detectContentType(this.activeTab.response.response_headers);
        },

        get saveModalBrowserItems() {
            if (!this.saveModal.path.length) {
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
            return (this.activeTab?.request.params || []).filter(p => p.key.trim()).length;
        },

        get filledHeaderCount() {
            return (this.activeTab?.request.headers || []).filter(h => h.key.trim()).length;
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

        // ---- Tab management ----

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

        blankTab() {
            return {
                id: 'tab_' + Date.now() + '_' + Math.random().toString(36).slice(2),
                requestId: null,
                isDirty: false,
                savedSnapshot: null,
                request: this.blankRequest(),
                response: null,
                isLoading: false,
                requestTab: 'params',
                responseTab: 'body',
                responseViewMode: 'pretty',
                responseForceType: 'auto',
                collectionVars: {},
            };
        },

        newTab() {
            const tab = this.blankTab();
            this.tabs.push(tab);
            this.activeTabId = tab.id;
        },

        // Backward-compat alias (welcome.blade.php calls newRequest())
        newRequest() {
            this.newTab();
        },

        switchTab(tabId) {
            this.activeTabId = tabId;
        },

        closeTab(tabId) {
            const tab = this.tabs.find(t => t.id === tabId);
            if (!tab) return;
            if (tab.isDirty && !confirm('This tab has unsaved changes. Close anyway?')) return;

            const idx = this.tabs.indexOf(tab);
            this.tabs.splice(idx, 1);

            if (this.activeTabId === tabId) {
                const next = this.tabs[idx] ?? this.tabs[idx - 1] ?? null;
                this.activeTabId = next?.id ?? null;
            }

            this.persistTabs();
        },

        markDirty() {
            const tab = this.activeTab;
            if (!tab || tab.savedSnapshot === null) return;
            tab.isDirty = JSON.stringify(tab.request) !== tab.savedSnapshot;
        },

        persistTabs() {
            const ids = this.tabs.filter(t => t.requestId).map(t => t.requestId);
            localStorage.setItem('freeman_open_tabs', JSON.stringify(ids));
            localStorage.setItem('freeman_active_tab', String(this.activeTab?.requestId ?? ''));
        },

        async restoreTabs() {
            try {
                const saved      = JSON.parse(localStorage.getItem('freeman_open_tabs') || '[]');
                const activeReqId = localStorage.getItem('freeman_active_tab') || '';
                if (!saved.length) return;

                for (const requestId of saved) {
                    await this.openRequest(requestId);
                }

                if (activeReqId) {
                    const tab = this.tabs.find(t => String(t.requestId) === activeReqId);
                    if (tab) this.activeTabId = tab.id;
                }
            } catch (e) {
                console.error('restoreTabs:', e);
                localStorage.removeItem('freeman_open_tabs');
                localStorage.removeItem('freeman_active_tab');
            }
        },

        // ---- Request open / load ----

        async openRequest(requestId) {
            // Focus existing tab if already open
            const existing = this.tabs.find(t => t.requestId === requestId);
            if (existing) {
                this.activeTabId = existing.id;
                return;
            }

            // Create placeholder tab immediately so the chip appears while loading
            const tab = this.blankTab();
            const tabId = tab.id;
            tab.requestId = requestId;
            this.tabs.push(tab);
            this.activeTabId = tabId;

            try {
                const res  = await fetch(`/requests/${requestId}`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                const json = await res.json();
                const d    = json.data;
                const ad   = d.auth_data || {};

                // Re-acquire the reactive proxy of this tab (the local `tab` var is the raw object,
                // not Alpine's proxy — mutations to it won't trigger re-renders).
                const liveTab = this.tabs.find(t => t.id === tabId);
                if (!liveTab) return; // Tab was closed while loading

                if (d.collection_id) {
                    await this.loadCollectionVarsForTab(liveTab, d.collection_id);
                }

                liveTab.request = {
                    collection_id: d.collection_id || null,
                    name:          d.name          || 'Untitled',
                    method:        d.method        || 'GET',
                    url:           d.url           || '',
                    params:        [{ key: '', value: '', enabled: true }],
                    headers:       Array.isArray(d.headers) && d.headers.length
                                       ? d.headers
                                       : [{ key: '', value: '', enabled: true }],
                    body_type:     d.body_type     || 'none',
                    raw_body_type: d.raw_body_type || 'json',
                    body:          d.body          || '',
                    body_form:     [{ key: '', value: '', enabled: true }],
                    auth_type:     d.auth_type     || 'none',
                    auth_data: {
                        token:    ad.token    || '',
                        username: ad.username || '',
                        password: ad.password || '',
                        key:      ad.key      || '',
                        value:    ad.value    || '',
                        in:       ad.in       || 'header',
                    },
                };
                liveTab.savedSnapshot = JSON.stringify(liveTab.request);
                liveTab.isDirty       = false;
                this.persistTabs();
            } catch (e) {
                console.error('openRequest:', e);
                this.tabs = this.tabs.filter(t => t.id !== tabId);
                if (this.activeTabId === tabId) {
                    this.activeTabId = this.tabs[this.tabs.length - 1]?.id ?? null;
                }
            }
        },

        async loadCollectionVarsForTab(tab, collectionId) {
            try {
                const res  = await fetch(`/collections/${collectionId}/variables`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                const json = await res.json();
                const map  = {};
                (json.data || []).forEach(v => { if (v.enabled && v.key) map[v.key] = v.value; });
                tab.collectionVars = map;
            } catch (e) {
                tab.collectionVars = {};
            }
        },

        async loadCurrentCollectionVars(collectionId) {
            if (this.activeTab) {
                await this.loadCollectionVarsForTab(this.activeTab, collectionId);
            }
        },

        // ---- Send ----

        async sendRequest() {
            const tab = this.activeTab;
            if (!tab || !tab.request.url.trim() || tab.isLoading) return;

            tab.isLoading = true;
            tab.response  = null;

            // Append query params to URL
            let url = tab.request.url;
            const qp = tab.request.params.filter(p => p.enabled && p.key.trim());
            if (qp.length) {
                const qs = qp.map(p => encodeURIComponent(p.key) + '=' + encodeURIComponent(p.value)).join('&');
                url += (url.includes('?') ? '&' : '?') + qs;
            }

            // Serialize form body if needed
            let body = tab.request.body;
            if (['form-data', 'x-www-form-urlencoded'].includes(tab.request.body_type)) {
                body = JSON.stringify(tab.request.body_form.filter(r => r.key.trim()));
            }

            // Build effective headers — inject Content-Type for raw body if not already set
            let effectiveHeaders = tab.request.headers.filter(h => h.key.trim());
            if (tab.request.body_type === 'raw') {
                const hasContentType = effectiveHeaders.some(h => h.key.toLowerCase() === 'content-type');
                if (!hasContentType) {
                    const ctMap = { text: 'text/plain', json: 'application/json', javascript: 'application/javascript', xml: 'application/xml', html: 'text/html' };
                    const ct = ctMap[tab.request.raw_body_type] ?? 'application/json';
                    effectiveHeaders = [{ key: 'Content-Type', value: ct, enabled: true }, ...effectiveHeaders];
                }
            }

            const payload = {
                method:        tab.request.method,
                url,
                headers:       effectiveHeaders,
                body_type:     tab.request.body_type,
                body,
                auth_type:     tab.request.auth_type,
                auth_data:     tab.request.auth_data,
                request_id:    tab.requestId,
                collection_id: tab.request.collection_id,
            };

            try {
                const res  = await fetch('/run', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept':       'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(payload),
                });
                tab.response    = await res.json();
                tab.responseTab = 'body';
            } catch (e) {
                tab.response = { success: false, error: e.message, status: 0, response_time_ms: 0, response_body: '', response_headers: {} };
            } finally {
                tab.isLoading = false;
            }
        },

        // ---- Save ----

        openSaveModal() {
            this.saveModal = {
                open: true,
                name: this.activeTab?.request.name || 'New Request',
                collectionId: null,
                folderId: null,
                saving: false,
                error: null,
                path: [],
            };
        },

        saveModalNavigateInto(item) {
            this.saveModal.path = [...this.saveModal.path, { id: item.id, name: item.name, type: item.type }];
            if (item.type === 'collection') {
                this.saveModal.collectionId = item.id;
                this.saveModal.folderId = null;
            } else {
                this.saveModal.folderId = item.id;
            }
        },

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
            const tab = this.activeTab;
            if (!tab) return;
            if (!this.saveModal.collectionId) {
                this.saveModal.error = 'Please select a collection.';
                return;
            }
            this.saveModal.saving = true;
            this.saveModal.error  = null;
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
                        method:        tab.request.method,
                        url:           tab.request.url,
                        collection_id: this.saveModal.collectionId,
                        folder_id:     this.saveModal.folderId || null,
                        headers:       tab.request.headers.filter(h => h.key.trim()),
                        body_type:     tab.request.body_type,
                        raw_body_type: tab.request.raw_body_type,
                        body:          tab.request.body,
                        auth_type:     tab.request.auth_type,
                        auth_data:     tab.request.auth_data,
                    }),
                });
                const json = await res.json();
                if (!res.ok) {
                    this.saveModal.error = json.message || 'Failed to save.';
                    return;
                }
                tab.requestId             = json.data.id;
                tab.request.name          = this.saveModal.name;
                tab.request.collection_id = this.saveModal.collectionId;
                tab.savedSnapshot         = JSON.stringify(tab.request);
                tab.isDirty               = false;
                this.saveModal.open       = false;
                this.persistTabs();
                await this.loadCollections();
            } catch (e) {
                this.saveModal.error = 'An error occurred.';
                console.error('confirmSaveRequest:', e);
            } finally {
                this.saveModal.saving = false;
            }
        },

        async saveRequest() {
            const tab = this.activeTab;
            if (!tab) return;
            if (!tab.requestId) {
                this.openSaveModal();
                return;
            }
            try {
                await fetch(`/requests/${tab.requestId}`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept':       'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        name:          tab.request.name,
                        method:        tab.request.method,
                        url:           tab.request.url,
                        headers:       tab.request.headers.filter(h => h.key.trim()),
                        body_type:     tab.request.body_type,
                        raw_body_type: tab.request.raw_body_type,
                        body:          tab.request.body,
                        auth_type:     tab.request.auth_type,
                        auth_data:     tab.request.auth_data,
                    }),
                });
                tab.savedSnapshot = JSON.stringify(tab.request);
                tab.isDirty       = false;
                await this.loadCollections();
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

        flatCollectionTree(col) {
            const rows      = [];
            const allFolders = col.folders || [];
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
            for (const req of (col.requests || [])) {
                rows.push({ type: 'request', req, depth: 0 });
            }
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
            this.folderModal.error   = null;
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
            this.renameFolderModal.error   = null;
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
            this.newCollectionError   = null;
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

        // ---- Rename collection ----

        openRenameCollectionModal(collectionId, currentName) {
            this.renameCollectionModal = { open: true, collectionId, name: currentName, loading: false, error: null };
            this.collectionMenuOpen = null;
        },

        async saveRenameCollection() {
            const name = this.renameCollectionModal.name.trim();
            if (!name) return;
            this.renameCollectionModal.loading = true;
            this.renameCollectionModal.error   = null;
            try {
                const res  = await fetch(`/collections/${this.renameCollectionModal.collectionId}`, {
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
                    this.renameCollectionModal.open = false;
                    await this.loadCollections();
                } else {
                    this.renameCollectionModal.error = json.message || 'Could not rename collection.';
                }
            } catch (e) {
                this.renameCollectionModal.error = 'Network error.';
            } finally {
                this.renameCollectionModal.loading = false;
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
                        'Accept':           'application/json',
                        'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]').content,
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

            setTimeout(() => { this.importNotification = null; }, 4000);
        },

        // ---- Delete collection ----

        async deleteCollection(id) {
            if (!confirm('Delete this collection and all its requests?')) return;
            try {
                await fetch(`/collections/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept':           'application/json',
                        'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                // Close any tabs for requests that belonged to this collection
                const before = this.activeTabId;
                this.tabs = this.tabs.filter(t => t.request.collection_id !== id);
                if (!this.tabs.find(t => t.id === before)) {
                    this.activeTabId = this.tabs[this.tabs.length - 1]?.id ?? null;
                }
                this.persistTabs();
                await this.loadCollections();
            } catch (e) {
                console.error('deleteCollection:', e);
            }
        },

        // ---- Collection variable loading ----

        async loadCollectionVarsForTab(tab, collectionId) {
            try {
                const res  = await fetch(`/collections/${collectionId}/variables`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                const json = await res.json();
                const map  = {};
                (json.data || []).forEach(v => { if (v.enabled && v.key) map[v.key] = v.value; });
                tab.collectionVars = map;
            } catch (e) {
                tab.collectionVars = {};
            }
        },

        async loadCurrentCollectionVars(collectionId) {
            if (this.activeTab) {
                await this.loadCollectionVarsForTab(this.activeTab, collectionId);
            }
        },

        // ---- URL variable hover tooltip ----

        onVarHover(event) {
            const wrap  = event.currentTarget;
            const input = wrap.querySelector('input, textarea');
            if (!input) return;

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

        // ---- Variable autocomplete ----

        checkVarAc(event) {
            const el = event.target;
            if (!el || (el.tagName !== 'INPUT' && el.tagName !== 'TEXTAREA')) return;
            const vars = this.activeTab?.collectionVars ?? {};
            if (!Object.keys(vars).length) { this.varAc.show = false; return; }

            const val    = el.value;
            const pos    = el.selectionStart ?? val.length;
            const before = val.slice(0, pos);
            const openAt = before.lastIndexOf('{' + '{');

            if (openAt === -1 || before.slice(openAt + 2).includes('}' + '}')) {
                this.varAc.show = false;
                return;
            }

            const query       = before.slice(openAt + 2).toLowerCase();
            const suggestions = Object.keys(vars).filter(k => k.toLowerCase().includes(query));

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
            const newPos = openAt + name.length + 4;

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
                // Reload vars if the active tab uses this collection
                if (this.activeTab?.request.collection_id === this.collectionVarsModal.collectionId) {
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

        addParam()       { this.activeTab?.request.params.push({ key: '', value: '', enabled: true }); },
        removeParam(i)   { this.activeTab?.request.params.splice(i, 1); },
        addHeader()      { this.activeTab?.request.headers.push({ key: '', value: '', enabled: true }); },
        removeHeader(i)  { this.activeTab?.request.headers.splice(i, 1); },
        addFormRow()     { this.activeTab?.request.body_form.push({ key: '', value: '', enabled: true }); },
        removeFormRow(i) { this.activeTab?.request.body_form.splice(i, 1); },

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
            const vars = this.activeTab?.collectionVars ?? {};
            return this.escHtml(url).replace(/\{\{([^}]*)\}\}/g, (match, key) => {
                const k = key.trim();
                if (k in vars) {
                    return `<mark class="url-var url-var-ok" data-name="${this.escHtml(k)}" data-val="${this.escHtml(vars[k])}">@{{${this.escHtml(key)}}}</mark>`;
                }
                return `<mark class="url-var url-var-err" data-name="${this.escHtml(k)}">@{{${this.escHtml(key)}}}</mark>`;
            });
        },

        // ---- Response body rendering ----

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
            const tab  = this.activeTab;
            const type = (tab?.responseForceType !== 'auto' ? tab?.responseForceType : null)
                ?? this.detectContentType(headers);
            if (tab?.responseViewMode === 'raw')            return this.escHtml(body);
            if (type === 'json')                            return this.highlightJson(body);
            if (type === 'xml' || type === 'html')          return this.highlightXml(body);
            if (type === 'javascript')                      return this.highlightJsEditor(body);
            return this.escHtml(body);
        },

        async copyResponseBody() {
            const body = this.activeTab?.response?.response_body;
            if (!body) return;
            try {
                await navigator.clipboard.writeText(body);
                this.responseCopied = true;
                setTimeout(() => { this.responseCopied = false; }, 2000);
            } catch {}
        },

        escHtml(s) {
            return String(s)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
        },

        highlightBodyContent(text) {
            if (!text) return '';
            const type = this.activeTab?.request.raw_body_type;
            if (type === 'json')                    return this.highlightJsonEditor(text);
            if (type === 'xml' || type === 'html')  return this.highlightXmlEditor(text);
            if (type === 'javascript')              return this.highlightJsEditor(text);
            return this.highlightVars(text);
        },

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

        highlightJsEditor(body) {
            if (!body) return '';
            const esc = this.escHtml(body);
            return esc
                .replace(/(\/\*[\s\S]*?\*\/)/g, '<span class="xml-comment">$1</span>')
                .replace(/(\/\/[^\n]*)/g,        '<span class="xml-comment">$1</span>');
        },

        formatBody() {
            const tab = this.activeTab;
            if (!tab) return;
            const type = tab.request.raw_body_type;
            const src  = tab.request.body ?? '';
            if (type === 'json') {
                try { tab.request.body = JSON.stringify(JSON.parse(src), null, 2); } catch {}
            } else if (type === 'xml' || type === 'html') {
                try {
                    let depth = 0;
                    tab.request.body = src
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
                    let j = i + 1;
                    while (j < len) {
                        if (fmt[j] === '\\') { j += 2; continue; }
                        if (fmt[j] === '"')  { j++; break; }
                        j++;
                    }
                    const token = fmt.slice(i, j);
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
                    html += this.escHtml(ch);
                    i++;
                }
            }
            return html;
        },

        highlightXml(body) {
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

            const esc = this.escHtml(fmt);

            return esc
                .replace(
                    /(&lt;!--[\s\S]*?--&gt;)/g,
                    '<span class="xml-comment">$1</span>'
                )
                .replace(
                    /(&lt;\?[\s\S]*?\?&gt;)/g,
                    '<span class="xml-bracket">$1</span>'
                )
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
