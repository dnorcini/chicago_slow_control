<script>
    //enable navigating the table and switch the input cells with arrow keys
    document.addEventListener('DOMContentLoaded', function() {
        const tables = Array.from(document.querySelectorAll('table'));

        function clamp(n, min, max) {
            return Math.max(min, Math.min(max, n));
        }

        tables.forEach((table) => {
            // Collect rows with inputs/selects/textareas
            const rows = Array.from(table.querySelectorAll('tr'))
                .filter(r => r.querySelector('input, select, textarea'));

            // Build grid (2D array of focusable fields)
            const grid = rows.map(r =>
                Array.from(r.querySelectorAll('input, select, textarea'))
                .filter(el => !el.disabled && el.tabIndex !== -1)
            ).filter(cols => cols.length > 0);

            function moveFocus(rowIdx, colIdx) {
                rowIdx = clamp(rowIdx, 0, grid.length - 1);
                colIdx = clamp(colIdx, 0, grid[rowIdx].length - 1);
                const el = grid[rowIdx][colIdx];
                if (el) {
                    el.focus({
                        preventScroll: true
                    });
                    if (el.tagName === 'INPUT' && el.type === 'text' && el.setSelectionRange) {
                        const len = el.value.length;
                        el.setSelectionRange(len, len);
                    }
                }
            }

            function findPos(el) {
                for (let i = 0; i < grid.length; i++) {
                    const j = grid[i].indexOf(el);
                    if (j !== -1) return {
                        i,
                        j
                    };
                }
                return null;
            }

            table.addEventListener('keydown', function(e) {
                const t = e.target;
                if (!(/INPUT|SELECT|TEXTAREA/.test(t.tagName))) return;

                const pos = findPos(t);
                if (!pos) return;

                const isSelect = t.tagName === 'SELECT';
                const isText = t.tagName === 'INPUT' && t.type === 'text';
                const atStart = isText && t.selectionStart === 0 && t.selectionEnd === 0;
                const atEnd = isText && t.selectionStart === t.value.length && t.selectionEnd === t.value.length;

                switch (e.key) {
                    case 'ArrowRight':
                        if (isSelect || atEnd || t.readOnly) {
                            e.preventDefault();
                            moveFocus(pos.i, pos.j + 1);
                        }
                        break;
                    case 'ArrowLeft':
                        if (isSelect || atStart || t.readOnly) {
                            e.preventDefault();
                            moveFocus(pos.i, pos.j - 1);
                        }
                        break;
                    case 'ArrowDown':
                        if (!isSelect) {
                            e.preventDefault();
                            moveFocus(pos.i + 1, pos.j);
                        }
                        break;
                    case 'ArrowUp':
                        if (!isSelect) {
                            e.preventDefault();
                            moveFocus(pos.i - 1, pos.j);
                        }
                        break;
                }
            });
        });
    });
</script>


<script>
    // Keyboard navigation: Cmd (macOS) / Ctrl (Win/Linux) + ArrowLeft/ArrowRight
    (function() {
        function isEditingField(el) {
            if (!el) return false;
            const tag = el.tagName;
            if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT' || el.isContentEditable) return true;
            // Treat number/text/email/search/url/tel/password inputs as editing fields
            if (tag === 'INPUT' && el.type) return true;
            return false;
        }

        document.addEventListener('keydown', function(e) {
            const isMac = navigator.platform && navigator.platform.toUpperCase().includes('MAC');
            const modifierHeld = isMac ? e.metaKey : e.ctrlKey; // Cmd on Mac, Ctrl otherwise
            if (!modifierHeld) return;

            // Don’t steal shortcuts while the user is typing into a field
            if (isEditingField(document.activeElement)) return;

            // ← = prev, → = next
            if (e.key === 'ArrowLeft') {
                e.preventDefault();
                const btn = document.querySelector('#btn-prev') || document.querySelector('input[name="prev"]');
                if (btn) btn.click();
            } else if (e.key === 'ArrowRight') {
                e.preventDefault();
                const btn = document.querySelector('#btn-next') || document.querySelector('input[name="next"]');
                if (btn) btn.click();
            }

            // (Optional) add Shift for First/Last: Cmd/Ctrl+Shift+←/→
            if (e.shiftKey && e.key === 'ArrowLeft') {
                e.preventDefault();
                const btn = document.querySelector('#btn-first') || document.querySelector('input[name="first"]');
                if (btn) btn.click();
            } else if (e.shiftKey && e.key === 'ArrowRight') {
                e.preventDefault();
                const btn = document.querySelector('#btn-last') || document.querySelector('input[name="last"]');
                if (btn) btn.click();
            }
        });
    })();
</script>


<script>
    (function() {
        const STORAGE_KEY = "visibleSections:v1"; // bump version if you change keys
        const sectionBlocks = Array.from(document.querySelectorAll('.section-block[data-section]'));
        const panel = document.getElementById('section-visibility-panel');
        const grid = document.getElementById('section-checkboxes');
        const toggleAll = document.getElementById('toggle-all');
        const btnCheckAll = document.getElementById('check-all');
        const btnUncheckAll = document.getElementById('uncheck-all');

        if (!panel || sectionBlocks.length === 0) return; // nothing to do

        // Build a list of sections
        const sections = sectionBlocks.map(el => ({
            key: String(el.getAttribute('data-section')),
            label: el.getAttribute('data-label') || String(el.getAttribute('data-section')),
            el
        }));

        // Load saved visibility from localStorage, default to all visible
        let visible = {};
        try {
            const raw = localStorage.getItem(STORAGE_KEY);
            if (raw) visible = JSON.parse(raw) || {};
        } catch (e) {}

        // If nothing saved yet, mark everything visible by default
        if (Object.keys(visible).length === 0) {
            for (const s of sections) visible[s.key] = true;
        } else {
            // If new sections appeared since last save, default them to visible
            for (const s of sections) {
                if (typeof visible[s.key] === 'undefined') visible[s.key] = true;
            }
        }

        // Render checkboxes
        grid.innerHTML = "";
        for (const s of sections) {
            const id = `sec-${s.key}`;
            const wrapper = document.createElement('label');
            wrapper.setAttribute('for', id);
            wrapper.style.display = 'flex';
            wrapper.style.alignItems = 'center';
            wrapper.style.gap = '.35rem';

            const cb = document.createElement('input');
            cb.type = 'checkbox';
            cb.id = id;
            cb.dataset.key = s.key;
            cb.checked = !!visible[s.key];

            const span = document.createElement('span');
            span.textContent = s.label;

            wrapper.prepend(cb);
            wrapper.appendChild(span);
            grid.appendChild(wrapper);
        }

        // Apply visibility to DOM
        function applyVisibility() {
            for (const s of sections) {
                const show = !!visible[s.key];
                s.el.classList.toggle('hidden-section', !show);
            }
            updateMasterCheckbox();
        }

        function updateMasterCheckbox() {
            const states = sections.map(s => !!visible[s.key]);
            const all = states.every(Boolean);
            const none = states.every(v => !v);
            // Use the "indeterminate" visual state when mixed
            toggleAll.indeterminate = !all && !none;
            toggleAll.checked = all && !toggleAll.indeterminate;
        }

        function saveVisibility() {
            try {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(visible));
            } catch (e) {}
        }

        // Listen to individual checkbox changes
        grid.addEventListener('change', function(e) {
            if (e.target && e.target.matches('input[type="checkbox"][data-key]')) {
                const key = e.target.dataset.key;
                visible[key] = e.target.checked;
                applyVisibility();
                saveVisibility();
            }
        });

        // Master toggle: tri-state aware
        toggleAll.addEventListener('change', function() {
            const desired = toggleAll.checked; // when user clicks, checked means "show all"
            for (const s of sections) visible[s.key] = desired;
            // Reflect in individual cbs
            grid.querySelectorAll('input[type="checkbox"][data-key]').forEach(cb => cb.checked = desired);
            applyVisibility();
            saveVisibility();
        });

        // Optional: explicit buttons
        btnCheckAll?.addEventListener('click', () => {
            for (const s of sections) visible[s.key] = true;
            grid.querySelectorAll('input[type="checkbox"][data-key]').forEach(cb => cb.checked = true);
            applyVisibility();
            saveVisibility();
        });

        btnUncheckAll?.addEventListener('click', () => {
            for (const s of sections) visible[s.key] = false;
            grid.querySelectorAll('input[type="checkbox"][data-key]').forEach(cb => cb.checked = false);
            applyVisibility();
            saveVisibility();
        });

        // Initial paint
        applyVisibility();

        // (Nice to have) Keep scroll position stable if a section collapses/expands near the top
        // The previous “remember scrollY across reloads” you added will still work fine with this.
    })();
</script>