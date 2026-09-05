/**
 * Nominatim (OpenStreetMap) Geocoding & Reverse Geocoding Service
 * SSV Trucking System
 */
const NominatimService = (function() {
    const cache = {};
    let lastRequestTime = 0;
    const MIN_REQ_INTERVAL = 1100; // 1.1s between live requests to obey OSM policy

    function formatAddress(data) {
        if (!data || !data.address) return data ? (data.display_name || '') : '';
        const addr = data.address;
        const parts = [];
        
        if (addr.road || addr.street || addr.highway) {
            parts.push(addr.road || addr.street || addr.highway);
        }
        if (addr.suburb || addr.neighbourhood || addr.village || addr.quarter || addr.barangay) {
            parts.push(addr.suburb || addr.neighbourhood || addr.village || addr.quarter || addr.barangay);
        }
        if (addr.city || addr.town || addr.municipality) {
            parts.push(addr.city || addr.town || addr.municipality);
        }
        if (addr.province || addr.state) {
            parts.push(addr.province || addr.state);
        }
        return parts.length > 0 ? parts.join(', ') : (data.display_name || '');
    }

    return {
        /**
         * Reverse Geocode (lat, lng -> address string)
         */
        reverseGeocode: async function(lat, lng) {
            if (lat === null || lng === null || isNaN(lat) || isNaN(lng)) return null;
            
            // Round coordinates to ~10m precision for caching
            const cacheKey = `${parseFloat(lat).toFixed(4)},${parseFloat(lng).toFixed(4)}`;
            if (cache[cacheKey]) {
                return cache[cacheKey];
            }

            // Enforce minimum request interval
            const now = Date.now();
            const elapsed = now - lastRequestTime;
            if (elapsed < MIN_REQ_INTERVAL) {
                await new Promise(res => setTimeout(res, MIN_REQ_INTERVAL - elapsed));
            }
            lastRequestTime = Date.now();

            try {
                const url = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${encodeURIComponent(lat)}&lon=${encodeURIComponent(lng)}&zoom=16&addressdetails=1`;
                const response = await fetch(url, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) return null;
                const data = await response.json();
                const addressStr = formatAddress(data);

                const result = {
                    raw: data,
                    displayName: data.display_name,
                    formatted: addressStr,
                    lat: lat,
                    lng: lng
                };
                cache[cacheKey] = result;
                return result;
            } catch (err) {
                console.warn('Nominatim Reverse Geocode Error:', err);
                return null;
            }
        },

        /**
         * Search Address / Place Name (Forward Geocoding)
         */
        searchAddress: async function(query) {
            if (!query || query.trim().length < 3) return [];
            const cleanQuery = query.trim();

            const cacheKey = `search:${cleanQuery.toLowerCase()}`;
            if (cache[cacheKey]) {
                return cache[cacheKey];
            }

            // Enforce rate limit
            const now = Date.now();
            const elapsed = now - lastRequestTime;
            if (elapsed < MIN_REQ_INTERVAL) {
                await new Promise(res => setTimeout(res, MIN_REQ_INTERVAL - elapsed));
            }
            lastRequestTime = Date.now();

            try {
                // Focus search on Philippines (countrycodes=ph)
                const url = `https://nominatim.openstreetmap.org/search?format=jsonv2&q=${encodeURIComponent(cleanQuery)}&countrycodes=ph&limit=5&addressdetails=1`;
                const response = await fetch(url, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) return [];
                const results = await response.json();

                const formattedResults = results.map(item => ({
                    id: item.place_id,
                    name: item.display_name,
                    shortName: formatAddress(item),
                    lat: parseFloat(item.lat),
                    lng: parseFloat(item.lon),
                    raw: item
                }));

                cache[cacheKey] = formattedResults;
                return formattedResults;
            } catch (err) {
                console.warn('Nominatim Search Error:', err);
                return [];
            }
        }
    };
})();
