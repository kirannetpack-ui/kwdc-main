(function () {
    const cache = new Map();
    let queue = Promise.resolve();
    let lastRequest = 0;

    async function request(path, payload) {
        const key = path + JSON.stringify(payload || null);
        if (cache.has(key)) return cache.get(key);
        const task = queue.catch(() => {}).then(async () => {
            const delay = Math.max(0, 80 - (Date.now() - lastRequest));
            if (delay) await new Promise(resolve => setTimeout(resolve, delay));
            lastRequest = Date.now();
            const response = await fetch(path, {
                method: payload ? 'POST' : 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                ...(payload ? { body: JSON.stringify(payload) } : {}),
                signal: AbortSignal.timeout(15000),
            });
            if (!response.ok) throw new Error('Map service is unavailable. Please retry.');
            return response.json();
        });
        queue = task;
        cache.set(key, task);
        task.catch(() => cache.delete(key));
        return task;
    }

    window.KwdcMaps = {
        async search(address) {
            if (!address || !address.trim()) return null;
            try {
                const matches = await request('/maps/search?q=' + encodeURIComponent(address.trim()));
                if (!matches || !matches.length) return null;
                return {lat: Number(matches[0].lat), lng: Number(matches[0].lon), label: matches[0].display_name};
            } catch (e) {
                console.warn('Map search error for:', address, e);
                return null;
            }
        },
        async roadDistance(addresses) {
            const points = [];
            for (const address of addresses) {
                let point = await this.search(address);
                if (!point) {
                    point = {lat: 27.7172, lng: 85.3240, label: address};
                }
                points.push({lat: point.lat, lng: point.lng});
            }
            try {
                const route = await request('/maps/route', {points});
                return route.distance_km;
            } catch (e) {
                let total = 0;
                for (let i = 0; i < points.length - 1; i++) {
                    const d = Math.hypot(points[i+1].lat - points[i].lat, points[i+1].lng - points[i].lng) * 111 * 1.35;
                    total += Math.max(2, d);
                }
                return Math.round(total * 10) / 10;
            }
        },
    };
})();
