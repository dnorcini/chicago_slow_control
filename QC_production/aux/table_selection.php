<style>
    .section-panel {
        position: sticky;
        top: 0;
        z-index: 100;
        background: #fff;
        border-bottom: 1px solid #ccc;
        padding: 4px 6px;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;
        font-size: 14px;
    }

    .section-panel .tiny-btn {
        font-size: 14px;
        padding: 2px 5px;
    }

    .checkbox-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-left: auto;
        /* push checkboxes to the right side */
    }

    .hidden-section {
        display: none !important;
    }
</style>
<!-- Section Visibility Controls -->
<div id="section-visibility-panel" class="section-panel">
    <!-- Check/Uncheck buttons -->
    <button type="button" id="check-all" class="tiny-btn">Check all</button>
    <button type="button" id="uncheck-all" class="tiny-btn">Uncheck all</button>

    <!-- section checkboxes will be populated here -->
    <div id="section-checkboxes" class="checkbox-grid" aria-live="polite"></div>
</div>


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