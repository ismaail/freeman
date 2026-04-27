// freeman-shell.js
// workspaceShell — root Alpine component for workspace.blade.php.
// Owns layout state (sidebarTab, env menus) and proxies shared store data to templates.

document.addEventListener('alpine:init', () => {
    Alpine.data('workspaceShell', () => ({

        // ── Layout state ───────────────────────────────────────────────────
        sidebarTab:  'collections',
        userMenuOpen: false,
        envMenuOpen:  false,

        // ── Store proxies (keep templates in workspace.blade.php unchanged) ─
        get tabs()              { return Alpine.store('workspace').tabs; },
        get activeTabId()       { return Alpine.store('workspace').activeTabId; },
        get activeTab()         { return Alpine.store('workspace').activeTab; },
        get collections()       { return Alpine.store('workspace').collections; },
        get collectionsLoading(){ return Alpine.store('workspace').collectionsLoading; },
        get environments()      { return Alpine.store('workspace').environments; },
        get activeEnvironment() { return Alpine.store('workspace').activeEnvironment; },

        // ── Tab method proxies ─────────────────────────────────────────────
        newTab()         { Alpine.store('workspace').newTab(); },
        newRequest()     { Alpine.store('workspace').newTab(); },
        switchTab(id)    { Alpine.store('workspace').switchTab(id); },
        methodColor(m)   { return methodColor(m); },

        closeTab(tabId) {
            const tab = Alpine.store('workspace').tabs.find(t => t.id === tabId);
            if (!tab) return;
            if (tab.isDirty && !confirm('This tab has unsaved changes. Close anyway?')) return;

            // Clean up file input map
            if (window.__fileInputMap) {
                Object.keys(window.__fileInputMap)
                    .filter(k => k.startsWith(tabId + '_'))
                    .forEach(k => delete window.__fileInputMap[k]);
            }

            // Notify requestBuilderComponent to clean its reactive fileSelectedMap
            window.dispatchEvent(new CustomEvent('freeman:tab-closed', { detail: { tabId } }));

            Alpine.store('workspace').removeTab(tabId);
        },

        // ── Init ──────────────────────────────────────────────────────────
        init() {
            Alpine.store('workspace').loadCollections();
            Alpine.store('workspace').loadEnvironments();
            Alpine.store('workspace').restoreTabs();

            // Ctrl+S → dispatch to saveModalComponent
            window.addEventListener('keydown', (e) => {
                if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                    e.preventDefault();
                    window.dispatchEvent(new CustomEvent('freeman:save-request'));
                }
            });
        },

        // ── Environment actions ────────────────────────────────────────────
        async activateEnvironment(id) {
            try {
                await fetch(`/environments/${id}/activate`, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                });
                this.envMenuOpen = false;
                await Alpine.store('workspace').loadEnvironments();
            } catch (e) { console.error('activateEnvironment:', e); }
        },

        async deactivateEnvironment() {
            try {
                await fetch('/environments/deactivate', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                });
                this.envMenuOpen = false;
                await Alpine.store('workspace').loadEnvironments();
            } catch (e) { console.error('deactivateEnvironment:', e); }
        },
    }));
});
