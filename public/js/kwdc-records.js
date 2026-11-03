document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('table tbody tr').forEach(row => {
        // Find detail link inside the row
        const link = Array.from(row.querySelectorAll('a[href]')).find(anchor => {
            try {
                const url = new URL(anchor.href, location.origin);
                return url.origin === location.origin && (
                    /\/(?:\d+)(?:\/show)?$/.test(url.pathname) ||
                    /\/(?:warehouse-requests|equipment-requests|invoices|pickup|dispatch|warehouses|drivers|clients)\/\d+/.test(url.pathname)
                );
            } catch (e) {
                return false;
            }
        });

        if (!link) return;

        row.dataset.recordUrl = link.href;
        row.classList.add('kwdc-clickable-row');
        if (!row.getAttribute('title')) {
            row.setAttribute('title', 'Click to view details');
        }

        row.addEventListener('click', event => {
            // Do not navigate if user clicked on another link, button, or form control, or selected text
            if (event.target.closest('a, button, input, select, textarea, label, form, .no-row-click') || window.getSelection()?.toString()) {
                return;
            }

            if (event.ctrlKey || event.metaKey) {
                window.open(link.href, '_blank', 'noopener');
            } else {
                location.assign(link.href);
            }
        });
    });
});
