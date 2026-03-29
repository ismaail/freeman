<style>
    .kv-row:hover .kv-del { opacity: 1 !important; }
    select option { background: var(--color-bg-elevated); }

    /* ---------- URL field backdrop (CSS-grid stack) ---------- */
    .url-field-wrap {
        display: grid;
        background: var(--color-bg-base);
        border: 1px solid var(--color-border-input);
        border-radius: 4px;
        transition: border-color .15s;
        overflow: hidden;
    }
    /* Shared typographic baseline — both layers MUST be identical */
    .url-field-back,
    .url-field-real {
        grid-area: 1 / 1;          /* stack in the same cell */
        padding: 8px 16px;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 0.875rem;
        line-height: 1.5;
        letter-spacing: 0;
        white-space: pre;
        overflow: hidden;
        box-sizing: border-box;
        width: 100%;
    }
    /* Backdrop: shows the highlighted text */
    .url-field-back {
        color: var(--color-text-input);
        pointer-events: none;
        user-select: none;
    }
    /* Real input: transparent value text; only caret is visible */
    .url-field-real {
        background: transparent;
        color: transparent;
        caret-color: var(--color-text-input);
        border: none;
        outline: none;
    }
    .url-field-input { cursor: text; }
    .url-field-input::placeholder { color: var(--color-border-input); }

    /* ---------- Generic variable-highlight field wrapper ---------- */
    /* Mirrors the URL bar technique: CSS-grid stacking of backdrop + real input */
    .var-field-wrap {
        display: grid;
        border-radius: 4px;
        background: var(--color-bg-base);
        border: 1px solid var(--color-border-subtle);
        overflow: hidden;
        transition: border-color .15s;
    }
    .vf-back, .vf-real {
        grid-area: 1/1;
        padding: 6px 10px;       /* px-2.5 py-1.5 */
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 0.75rem;
        line-height: 1.5;
        white-space: pre;
        overflow: hidden;
        box-sizing: border-box;
        width: 100%;
        min-width: 0;
        letter-spacing: 0;
    }
    .vf-back {
        color: var(--color-text-input);
        pointer-events: none;
        user-select: none;
    }
    .vf-real {
        background: transparent;
        color: transparent;
        caret-color: var(--color-text-input);
        border: none;
        outline: none;
    }
    /* Modifier: px-3 py-2 padding (auth / wider inputs) */
    .var-field-wrap.vf-md .vf-back,
    .var-field-wrap.vf-md .vf-real { padding: 8px 12px; }
    /* Modifier: textarea (multi-line, px-3 py-2.5, line-height 1.6) */
    .var-field-wrap.vf-textarea { min-height: 135px; }
    .var-field-wrap.vf-textarea .vf-back,
    .var-field-wrap.vf-textarea .vf-real {
        padding: 10px 12px;
        line-height: 1.6;
        white-space: pre-wrap;
        overflow-wrap: break-word;
        overflow-y: auto;
        resize: none;
        height: 135px;
    }

    /* ---------- URL variable marks ---------- */
    .url-var     { border-radius: 2px; padding: 0 2px; margin: 0 -2px; pointer-events: auto; cursor: default; }
    .url-var-ok  { background: var(--color-brand-tint-url-bg); color: var(--color-brand-tint-url-text); }
    .url-var-err { background: rgba(239,68,68,0.18); color: #f87171; }

    /* ---------- Response body syntax tokens ---------- */
    .json-key    { color: var(--color-syntax-key); }
    .json-str    { color: var(--color-syntax-str); }
    .json-num    { color: var(--color-syntax-num); }
    .json-bool   { color: var(--color-syntax-bool); }
    .json-null   { color: var(--color-syntax-bool); }
    .json-punct  { color: var(--color-text-input); }
    .xml-tag     { color: var(--color-syntax-xml-tag); }
    .xml-bracket { color: var(--color-syntax-xml-bracket); }
    .xml-attr    { color: var(--color-syntax-key); }
    .xml-val     { color: var(--color-syntax-str); }
    .xml-comment { color: var(--color-syntax-comment); font-style: italic; }
</style>
