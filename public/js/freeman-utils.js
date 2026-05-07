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
