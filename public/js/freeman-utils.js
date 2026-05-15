// freeman-utils.js
// Pure utility functions — no Alpine dependency. Available as globals to all components.

function methodColor(method) {
    return { GET: 'text-green-400', POST: 'text-yellow-400', PUT: 'text-blue-400', PATCH: 'text-purple-400', DELETE: 'text-red-400' }[method] || 'text-gray-400';
}

function statusColor(status) {
    if (!status) return 'text-gray-400';
    if (status < 300) return 'text-green-400';
    if (status < 400) return 'text-blue-400';
    if (status < 500) return 'text-yellow-400';
    return 'text-red-400';
}

function statusLabel(status) {
    if (!status) return 'text-gray-600';
    if (status < 300) return 'text-green-700';
    if (status < 400) return 'text-blue-700';
    if (status < 500) return 'text-yellow-700';
    return 'text-red-700';
}

function statusText(status) {
    const map = { 200:'OK', 201:'Created', 204:'No Content', 301:'Moved', 302:'Found', 304:'Not Modified', 400:'Bad Request', 401:'Unauthorized', 403:'Forbidden', 404:'Not Found', 405:'Method Not Allowed', 409:'Conflict', 422:'Unprocessable', 429:'Too Many Requests', 500:'Internal Server Error', 502:'Bad Gateway', 503:'Service Unavailable' };
    return map[status] ? map[status] : '';
}

function responseSize(body) {
    if (!body) return '0 B';
    const b = new Blob([body]).size;
    if (b < 1024)    return b + ' B';
    if (b < 1048576) return (b / 1024).toFixed(1) + ' KB';
    return (b / 1048576).toFixed(1) + ' MB';
}

function escHtml(s) {
    return String(s)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
}

function detectContentType(headers) {
    if (!headers) return 'text';
    const entry = Object.entries(headers).find(([k]) => k.toLowerCase() === 'content-type');
    if (!entry) return 'text';
    const v = entry[1].toLowerCase();
    if (v.includes('json'))       return 'json';
    if (v.includes('html'))       return 'html';
    if (v.includes('xml'))        return 'xml';
    if (v.includes('javascript')) return 'javascript';
    if (v.startsWith('image/'))   return 'image';
    if (v.startsWith('audio/'))   return 'audio';
    return 'text';
}

function varLabel(name) {
    return '{{' + name + '}}';
}

// ── Foldable JSON renderer ───────────────────────────────────────────────────
(function () {
    const s = document.createElement('style');
    s.textContent = [
        '.jf-toggle{cursor:pointer;user-select:none;color:#6b7280;font-size:1em;padding:0 3px;vertical-align:middle}',
        '.jf-toggle:hover{color:#d1d5db}',
        '.jf-sum{color:#6b7280;font-style:italic}',
    ].join('');
    document.head.appendChild(s);
})();

let _jfId = 0;

function renderFoldableJson(value) {
    _jfId = 0;
    return _jfNode(value, 0);
}

function _jfNode(val, indent) {
    const pad  = ' '.repeat(indent);
    const pad2 = ' '.repeat(indent + 2);

    if (val === null)             return '<span class="json-null">null</span>';
    if (typeof val === 'boolean') return `<span class="json-bool">${val}</span>`;
    if (typeof val === 'number')  return `<span class="json-num">${val}</span>`;
    if (typeof val === 'string')  return `<span class="json-str">${escHtml(JSON.stringify(val))}</span>`;

    if (Array.isArray(val)) {
        if (val.length === 0) return '<span class="json-punct">[]</span>';
        const id = ++_jfId;
        const n  = val.length;
        const rows = val.map((v, i) =>
            pad2 + _jfNode(v, indent + 2) + (i < n - 1 ? '<span class="json-punct">,</span>' : '')
        ).join('\n');
        return `<span class="jf-group">`
             + `<span class="jf-toggle" id="jf-t-${id}" onclick="jfToggle(${id})">▾</span>`
             + `<span class="json-punct">[</span>`
             + `<span class="jf-sum" id="jf-s-${id}" style="display:none"> ${n} item${n !== 1 ? 's' : ''} <span class="json-punct">]</span></span>`
             + `<span id="jf-b-${id}">\n${rows}\n${pad}</span>`
             + `<span class="json-punct" id="jf-c-${id}">]</span>`
             + `</span>`;
    }

    if (typeof val === 'object') {
        const keys = Object.keys(val);
        if (keys.length === 0) return '<span class="json-punct">{}</span>';
        const id = ++_jfId;
        const n  = keys.length;
        const rows = keys.map((k, i) =>
            pad2 + `<span class="json-key">${escHtml(JSON.stringify(k))}</span><span class="json-punct">:</span> `
                 + _jfNode(val[k], indent + 2)
                 + (i < n - 1 ? '<span class="json-punct">,</span>' : '')
        ).join('\n');
        return `<span class="jf-group">`
             + `<span class="jf-toggle" id="jf-t-${id}" onclick="jfToggle(${id})">▾</span>`
             + `<span class="json-punct">{</span>`
             + `<span class="jf-sum" id="jf-s-${id}" style="display:none"> ${n} key${n !== 1 ? 's' : ''} <span class="json-punct">}</span></span>`
             + `<span id="jf-b-${id}">\n${rows}\n${pad}</span>`
             + `<span class="json-punct" id="jf-c-${id}">}</span>`
             + `</span>`;
    }

    return escHtml(String(val));
}

function jfToggle(id) {
    const body   = document.getElementById(`jf-b-${id}`);
    const sum    = document.getElementById(`jf-s-${id}`);
    const close  = document.getElementById(`jf-c-${id}`);
    const toggle = document.getElementById(`jf-t-${id}`);
    if (!body) return;
    const open = body.style.display !== 'none';
    body.style.display  = open ? 'none' : '';
    sum.style.display   = open ? '' : 'none';
    close.style.display = open ? 'none' : '';
    if (toggle) toggle.textContent = open ? '▸' : '▾';
}
