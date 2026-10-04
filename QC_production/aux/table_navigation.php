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
