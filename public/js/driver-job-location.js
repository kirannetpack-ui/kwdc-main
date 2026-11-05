(() => {
    const rows = [...document.querySelectorAll('[data-location-url]:not([data-location-ready])')];

    for (const row of rows) {
        row.dataset.locationReady = 'true';
        row.querySelector('[data-location-status]').textContent = 'Allow location access and keep this page open to share your location.';
    }

    if (!rows.length || !navigator.geolocation) return;

    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    let lastSent = 0;
    const watch = navigator.geolocation.watchPosition(async ({ coords }) => {
        if (Date.now() - lastSent < 15000) return;
        lastSent = Date.now();

        for (const row of [...rows]) {
            const status = row.querySelector('[data-location-status]');
            try {
                const response = await fetch(row.dataset.locationUrl, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: JSON.stringify({ latitude: coords.latitude, longitude: coords.longitude }),
                });

                if ([401, 403, 404, 409, 419].includes(response.status)) {
                    rows.splice(rows.indexOf(row), 1);
                    status.textContent = 'Location sharing stopped. Refresh to check your job status.';
                } else {
                    status.textContent = response.ok ? 'Location shared' : 'Location update failed. Retrying when available.';
                }
            } catch {
                status.textContent = 'Location update failed. Retrying when available.';
            }
        }

        if (!rows.length) navigator.geolocation.clearWatch(watch);
    }, () => {
        for (const row of rows) {
            row.querySelector('[data-location-status]').textContent = 'Location unavailable. Check your browser permissions and GPS.';
        }
    });

    window.addEventListener('pagehide', () => navigator.geolocation.clearWatch(watch), { once: true });
})();
