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
