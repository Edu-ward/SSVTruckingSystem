/**
 * Map Geocoding & Distance Service
 * SSV Trucking System
 * Relies directly on map coordinates, Leaflet map geometry, and BigDataCloud / OSM APIs.
 * NO hardcoded latitude/longitude dictionary.
 */
const NominatimService = (function () {
    const cache = {};

    // SSV Quarry Garage coordinates (Brgy. Burgos, San Leonardo, Nueva Ecija)
    const GARAGE_COORDS = {
        name: "San Leonardo (SSV Quarry Garage)",
        lat: 15.359042,
        lng: 120.965016
    };

    /**
     * Calculate geodesic distance directly between two map coordinate points
     * Uses the Haversine formula (same as Leaflet's L.latLng.distanceTo).
     * Multiplies by 1.25 driving road curvature factor.
     * Back and Forth (Round Trip): 2x distance, minimum 2 km.
     * Pay rate: ₱10 per km (e.g., 2 km one-way = 4 km round-trip = ₱40.00 pay).
     */
    function calculateMapDistance(destLat, destLng, origLat = GARAGE_COORDS.lat, origLng = GARAGE_COORDS.lng) {
        if (destLat == null || destLng == null || isNaN(destLat) || isNaN(destLng)) return null;

        const R = 6371; // Earth radius in km
        const dLat = (destLat - origLat) * Math.PI / 180;
        const dLon = (destLng - origLng) * Math.PI / 180;
        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                  Math.cos(origLat * Math.PI / 180) * Math.cos(destLat * Math.PI / 180) *
                  Math.sin(dLon / 2) * Math.sin(dLon / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        const straightLineKm = R * c;

        // 1.25 road curvature multiplier to convert straight-line to actual driving distance
        const roadOneWayKm = straightLineKm * 1.25;

        // Back and forth (round trip): 2x distance
        const roundTripKm = roadOneWayKm * 2;
        let roundedKm = Math.round(roundTripKm);
        if (roundedKm < 2 && straightLineKm > 0) roundedKm = 2;

        const pay = roundedKm * 10;

        return {
            km: roundedKm,
            oneWayKm: Math.round(roadOneWayKm),
            exactKm: parseFloat(roundTripKm.toFixed(1)),
            straightLineKm: parseFloat(straightLineKm.toFixed(1)),
            payAmount: pay,
            durationMins: Math.round((roundTripKm / 40) * 60),
            destLat: parseFloat(destLat),
            destLng: parseFloat(destLng)
        };
    }

    /**
     * Reverse geocode coordinates to human-readable address:
     * Primary: BigDataCloud Reverse Geocoding API (Fast, Free, Client-side browser friendly, no CORS issues)
     * Fallback: OpenStreetMap Nominatim
     */
    async function reverseGeocode(lat, lng) {
        if (lat == null || lng == null || isNaN(lat) || isNaN(lng)) return null;

        const cacheKey = `geo:${parseFloat(lat).toFixed(4)},${parseFloat(lng).toFixed(4)}`;
        if (cache[cacheKey]) {
            return cache[cacheKey];
        }

        // 1. Try BigDataCloud API
        try {
            const controller = new AbortController();
            const timeout = setTimeout(() => controller.abort(), 2000);
            const bdcUrl = `https://api.bigdatacloud.net/data/reverse-geocode-client?latitude=${encodeURIComponent(lat)}&longitude=${encodeURIComponent(lng)}&localityLanguage=en`;
            const resp = await fetch(bdcUrl, { signal: controller.signal });
            clearTimeout(timeout);

            if (resp.ok) {
                const data = await resp.json();
                const parts = [];

                if (data.locality && data.locality !== data.city) parts.push(data.locality);
                if (data.city) parts.push(data.city);

                // Province from administrative list
                if (data.localityInfo && data.localityInfo.administrative) {
                    for (const adm of data.localityInfo.administrative) {
                        if (adm.description && adm.description.toLowerCase().includes('province') && !parts.includes(adm.name)) {
                            parts.push(adm.name);
                        }
                    }
                }
                if (parts.length === 0 && data.principalSubdivision) {
                    parts.push(data.principalSubdivision);
                }

                const formatted = parts.length > 0 ? parts.join(', ') : `${data.locality || data.city || ''}, ${data.countryName || 'Philippines'}`;
                if (formatted.trim().length > 1) {
                    const result = {
                        formatted: formatted,
                        displayName: formatted,
                        lat: parseFloat(lat),
                        lng: parseFloat(lng)
                    };
                    cache[cacheKey] = result;
                    return result;
                }
            }
        } catch (e) {
            console.warn('BigDataCloud geocode failed, falling back to OSM:', e);
        }

        // 2. Fallback: OSM Nominatim
        try {
            const controller = new AbortController();
            const timeout = setTimeout(() => controller.abort(), 2500);
            const osmUrl = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${encodeURIComponent(lat)}&lon=${encodeURIComponent(lng)}&zoom=16&addressdetails=1`;
            const resp = await fetch(osmUrl, {
                headers: { 'Accept': 'application/json' },
                signal: controller.signal
            });
            clearTimeout(timeout);

            if (resp.ok) {
                const data = await resp.json();
                const addr = data.address || {};
                const parts = [];
                if (addr.road || addr.street) parts.push(addr.road || addr.street);
                if (addr.village || addr.quarter || addr.suburb || addr.barangay) parts.push(addr.village || addr.quarter || addr.suburb || addr.barangay);
                if (addr.city || addr.town || addr.municipality) parts.push(addr.city || addr.town || addr.municipality);
                if (addr.province) parts.push(addr.province);

                const formatted = parts.length > 0 ? parts.join(', ') : (data.display_name || `${lat.toFixed(4)}, ${lng.toFixed(4)}`);
                const result = {
                    formatted: formatted,
                    displayName: formatted,
                    lat: parseFloat(lat),
                    lng: parseFloat(lng)
                };
                cache[cacheKey] = result;
                return result;
            }
        } catch (err) {
            console.warn('OSM reverse geocode error:', err);
        }

        // Fallback default coordinates label
        return {
            formatted: `Point at ${parseFloat(lat).toFixed(4)}, ${parseFloat(lng).toFixed(4)}`,
            displayName: `Point at ${parseFloat(lat).toFixed(4)}, ${parseFloat(lng).toFixed(4)}`,
            lat: parseFloat(lat),
            lng: parseFloat(lng)
        };
    }

    /**
     * Search address or place name
     * Primary: Komoot Photon API (OSM-backed, ultra fast <200ms, no rate limiting, biased around Nueva Ecija)
     * Fallback: OpenStreetMap Nominatim
     */
    async function searchAddress(query) {
        if (!query || query.trim().length < 2) return [];
        const cleanQuery = query.trim();

        const cacheKey = `search:${cleanQuery.toLowerCase()}`;
        if (cache[cacheKey]) return cache[cacheKey];

        // 1. Try Komoot Photon API (Ultra fast & CORS friendly)
        try {
            const controller = new AbortController();
            const timeout = setTimeout(() => controller.abort(), 2000);
            const photonUrl = `https://photon.komoot.io/api/?q=${encodeURIComponent(cleanQuery)}&limit=8&lat=15.359042&lon=120.965016`;
            const resp = await fetch(photonUrl, { signal: controller.signal });
            clearTimeout(timeout);

            if (resp.ok) {
                const data = await resp.json();
                if (data && data.features && data.features.length > 0) {
                    const results = data.features.map(f => {
                        const p = f.properties || {};
                        const coords = f.geometry && f.geometry.coordinates ? f.geometry.coordinates : [0, 0];
                        const parts = [];
                        if (p.name) parts.push(p.name);
                        if (p.district && p.district !== p.name) parts.push(p.district);
                        if (p.city && p.city !== p.name && !parts.includes(p.city)) parts.push(p.city);
                        if (p.state && !parts.includes(p.state)) parts.push(p.state);

                        const short = parts.slice(0, 2).join(', ') || p.name || 'Location';
                        const full = parts.join(', ') || short;

                        return {
                            id: p.osm_id || Math.random(),
                            name: full,
                            shortName: short,
                            lat: coords[1],
                            lng: coords[0]
                        };
                    });
                    if (results.length > 0) {
                        cache[cacheKey] = results;
                        return results;
                    }
                }
            }
        } catch (photonErr) {
            console.warn('Photon search error, falling back to OSM Nominatim:', photonErr);
        }

        // 2. Fallback: OpenStreetMap Nominatim
        try {
            const controller = new AbortController();
            const timeout = setTimeout(() => controller.abort(), 2500);
            const url = `https://nominatim.openstreetmap.org/search?format=jsonv2&q=${encodeURIComponent(cleanQuery)}&countrycodes=ph&limit=6&addressdetails=1`;
            const response = await fetch(url, {
                headers: { 'Accept': 'application/json' },
                signal: controller.signal
            });
            clearTimeout(timeout);

            if (!response.ok) return [];
            const results = await response.json();

            const formattedResults = results.map(item => {
                const addr = item.address || {};
                const parts = [];
                if (addr.road || addr.street) parts.push(addr.road || addr.street);
                if (addr.village || addr.quarter || addr.suburb || addr.barangay) parts.push(addr.village || addr.quarter || addr.suburb || addr.barangay);
                if (addr.city || addr.town || addr.municipality) parts.push(addr.city || addr.town || addr.municipality);
                if (addr.province) parts.push(addr.province);

                const short = parts.length > 0 ? parts.join(', ') : item.display_name.split(',').slice(0, 3).join(',');
                return {
                    id: item.place_id,
                    name: item.display_name,
                    shortName: short,
                    lat: parseFloat(item.lat),
                    lng: parseFloat(item.lon)
                };
            });

            cache[cacheKey] = formattedResults;
            return formattedResults;
        } catch (err) {
            console.warn('OSM Search error:', err);
            return [];
        }
    }

    /**
     * Query road driving route distance:
     * First computes map distance immediately (0ms).
     * Attempts OSRM routing with a short 1.2s timeout to refine if network allows.
     */
    async function calculateDrivingDistance(destLat, destLng, origLat = GARAGE_COORDS.lat, origLng = GARAGE_COORDS.lng) {
        // Immediate baseline from the map (0ms, 100% reliable)
        const mapDist = calculateMapDistance(destLat, destLng, origLat, origLng);
        if (!mapDist) return null;

        const cacheKey = `dist:${parseFloat(origLat).toFixed(4)},${parseFloat(origLng).toFixed(4)}->${parseFloat(destLat).toFixed(4)},${parseFloat(destLng).toFixed(4)}`;
        if (cache[cacheKey]) return cache[cacheKey];

        try {
            const controller = new AbortController();
            const timeout = setTimeout(() => controller.abort(), 1200);
            const osrmUrl = `https://router.project-osrm.org/route/v1/driving/${origLng},${origLat};${destLng},${destLat}?overview=false`;
            const res = await fetch(osrmUrl, { signal: controller.signal });
            clearTimeout(timeout);

            if (res.ok) {
                const data = await res.json();
                if (data.code === 'Ok' && data.routes && data.routes.length > 0) {
                    const oneWayKm = data.routes[0].distance / 1000;
                    const roundTripKm = oneWayKm * 2;
                    let roundedKm = Math.round(roundTripKm);
                    if (roundedKm < 2 && oneWayKm > 0) roundedKm = 2;
                    const result = {
                        km: roundedKm,
                        oneWayKm: Math.round(oneWayKm),
                        exactKm: parseFloat(roundTripKm.toFixed(1)),
                        payAmount: roundedKm * 10,
                        durationMins: Math.round((roundTripKm / 40) * 60),
                        destLat: destLat,
                        destLng: destLng
                    };
                    cache[cacheKey] = result;
                    return result;
                }
            }
        } catch (e) {
            // OSRM failed or timed out — return map distance
        }

        cache[cacheKey] = mapDist;
        return mapDist;
    }

    /**
     * Resolve distance for destination name by searching map
     */
    async function getDistanceForDestination(destName, origLat = GARAGE_COORDS.lat, origLng = GARAGE_COORDS.lng) {
        if (!destName || typeof destName !== 'string') return null;

        const searchResults = await searchAddress(destName);
        if (!searchResults || searchResults.length === 0) return null;

        const top = searchResults[0];
        const dist = await calculateDrivingDistance(top.lat, top.lng, origLat, origLng);
        if (!dist) return null;

        return {
            ...dist,
            destination: destName,
            coords: { lat: top.lat, lng: top.lng }
        };
    }

    return {
        GARAGE_COORDS: GARAGE_COORDS,
        calculateMapDistance: calculateMapDistance,
        calculateDirectRoadDistance: calculateMapDistance, // alias
        calculateDrivingDistance: calculateDrivingDistance,
        reverseGeocode: reverseGeocode,
        searchAddress: searchAddress,
        getDistanceForDestination: getDistanceForDestination
    };
})();
