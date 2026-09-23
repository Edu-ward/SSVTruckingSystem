const NominatimService = (function () {
    const cache = {};

    const GARAGE_COORDS = {
        name: "San Leonardo (SSV Quarry Garage)",
        lat: 15.359042,
        lng: 120.965016
    };

    
    const PH_BOUNDS = {
        minLat: 4.5,
        maxLat: 21.5,
        minLng: 116.0,
        maxLng: 127.0
    };

    
    const WATER_REGIONS = [
        
        { name: "Manila Bay", minLat: 14.42, maxLat: 14.78, minLng: 120.60, maxLng: 120.88 },
        
        { name: "Subic Bay", minLat: 14.74, maxLat: 14.85, minLng: 120.22, maxLng: 120.29 },
        
        { name: "Lingayen Gulf", minLat: 16.08, maxLat: 16.35, minLng: 120.08, maxLng: 120.35 },
        
        { name: "Laguna de Bay", minLat: 14.25, maxLat: 14.45, minLng: 121.14, maxLng: 121.35 },
        
        { name: "Taal Lake", minLat: 13.97, maxLat: 14.07, minLng: 120.95, maxLng: 121.05 },
        
        { name: "West Philippine Sea", minLat: 13.0, maxLat: 19.0, minLng: 116.0, maxLng: 119.70 },
        
        { name: "Pacific Ocean", minLat: 14.5, maxLat: 18.5, minLng: 122.4, maxLng: 127.0 },
        
        { name: "Pacific Ocean (Aurora Coast)", minLat: 15.2, maxLat: 16.3, minLng: 121.75, maxLng: 127.0 }
    ];

    function isWithinPhilippines(lat, lng) {
        if (lat == null || lng == null || isNaN(lat) || isNaN(lng)) return false;
        const nLat = parseFloat(lat);
        const nLng = parseFloat(lng);
        return nLat >= PH_BOUNDS.minLat && nLat <= PH_BOUNDS.maxLat &&
               nLng >= PH_BOUNDS.minLng && nLng <= PH_BOUNDS.maxLng;
    }

    function isKnownWaterBody(lat, lng) {
        if (lat == null || lng == null || isNaN(lat) || isNaN(lng)) return false;
        const nLat = parseFloat(lat);
        const nLng = parseFloat(lng);
        for (const b of WATER_REGIONS) {
            if (nLat >= b.minLat && nLat <= b.maxLat && nLng >= b.minLng && nLng <= b.maxLng) {
                return { isWater: true, name: b.name };
            }
        }
        return false;
    }

    async function checkIsWater(lat, lng) {
        if (lat == null || lng == null || isNaN(lat) || isNaN(lng)) return { isWater: false };
        const nLat = parseFloat(lat);
        const nLng = parseFloat(lng);

        
        if (!isWithinPhilippines(nLat, nLng)) {
            return { isWater: true, reason: 'Outside Philippine operational boundaries' };
        }

        
        const fast = isKnownWaterBody(nLat, nLng);
        if (fast) {
            return { isWater: true, reason: `Located in ${fast.name}` };
        }

        
        const cacheKey = `water:${nLat.toFixed(4)},${nLng.toFixed(4)}`;
        if (typeof cache[cacheKey] !== 'undefined') {
            return cache[cacheKey];
        }

        
        try {
            const controller = new AbortController();
            const timeout = setTimeout(() => controller.abort(), 2000);
            const osrmUrl = `https://router.project-osrm.org/nearest/v1/driving/${nLng},${nLat}?number=1`;
            const resp = await fetch(osrmUrl, { signal: controller.signal });
            clearTimeout(timeout);

            if (resp.ok) {
                const data = await resp.json();
                if (data && data.code === 'Ok' && data.waypoints && data.waypoints.length > 0) {
                    const distM = parseFloat(data.waypoints[0].distance);
                    
                    if (distM > 600) {
                        const result = { isWater: true, reason: `No road access (${Math.round(distM)}m from shore/road)` };
                        cache[cacheKey] = result;
                        return result;
                    }
                } else if (data && data.code !== 'Ok') {
                    const result = { isWater: true, reason: 'No road route available (open sea/ocean)' };
                    cache[cacheKey] = result;
                    return result;
                }
            }
        } catch (e) {
            console.warn('OSM Nearest road check skipped:', e);
        }

        
        try {
            const controller = new AbortController();
            const timeout = setTimeout(() => controller.abort(), 2000);
            const osmUrl = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${encodeURIComponent(nLat)}&lon=${encodeURIComponent(nLng)}&zoom=16`;
            const resp = await fetch(osmUrl, {
                headers: { 'Accept': 'application/json' },
                signal: controller.signal
            });
            clearTimeout(timeout);

            if (resp.ok) {
                const data = await resp.json();
                if (!data || data.error) {
                    const result = { isWater: true, reason: 'Unable to geocode (open sea)' };
                    cache[cacheKey] = result;
                    return result;
                }
                const cat = data.category || '';
                const type = data.type || '';
                if (cat === 'natural' && ['water', 'sea', 'ocean', 'bay', 'coastline', 'strait'].includes(type)) {
                    const result = { isWater: true, reason: `Natural water feature (${type})` };
                    cache[cacheKey] = result;
                    return result;
                }
            }
        } catch (e) {
            console.warn('OSM water fallback check skipped:', e);
        }

        const result = { isWater: false, reason: 'Valid land location' };
        cache[cacheKey] = result;
        return result;
    }

    function isWithinSanLeonardo(name) {
        if (!name || typeof name !== 'string') return false;
        const lower = name.toLowerCase();
        if (lower.includes('san leonardo')) return true;
        const slBarangays = [
            'bonifacio', 'burgos', 'castillejos', 'diversion', 'magpapalayoc',
            'mallorca', 'mambangnan', 'nieves', 'san anton', 'san bartolome',
            'san francisco', 'san roque', 'santa cruz', 'sta. cruz', 'tabuating', 'tagumpay'
        ];
        return slBarangays.some(b => new RegExp('\\b' + b + '\\b', 'i').test(lower));
    }

    function getSanLeonardoBoundaryDistance(name = '', destLat = null, destLng = null) {
        if (name) {
            const lower = name.toLowerCase();
            if (lower.includes('peñaranda') || lower.includes('penaranda') || lower.includes('general tinio') || lower.includes('gen. tinio') || lower.includes('papaya')) {
                return 6;
            }
        }
        if (destLat != null && destLng != null && !isNaN(destLat) && !isNaN(destLng)) {
            const dLng = destLng - 120.965016;
            const dLat = destLat - 15.359042;
            const angle = Math.atan2(dLng, dLat) * 180 / Math.PI;
            if (angle >= 45 && angle <= 135) {
                return 6;
            }
        }
        return 12; 
    }

    function calculateTripPay(km, name = '', destLat = null, destLng = null, customRate = null) {
        const rounded = Math.round(km);
        const globalBase = (typeof window !== 'undefined' && window.BASE_TRIP_RATE) ? Number(window.BASE_TRIP_RATE) : 300.00;
        const globalPerKm = (typeof window !== 'undefined' && window.RATE_PER_KM) ? Number(window.RATE_PER_KM) : 10.00;

        let basePay = globalBase;
        if (customRate != null && Number(customRate) > 0) {
            basePay = Number(customRate);
        } else if (typeof window !== 'undefined' && window.DESTINATION_DRIVER_RATES && name && window.DESTINATION_DRIVER_RATES[name] > 0) {
            basePay = Number(window.DESTINATION_DRIVER_RATES[name]);
        }

        if (isWithinSanLeonardo(name)) {
            return {
                pay: basePay,
                payAmount: basePay,
                outsideKm: 0,
                boundaryKm: 0,
                isWithin: true,
                breakdown: `Within San Leonardo (Flat Rate: ₱${basePay.toFixed(2)})`
            };
        }
        const boundaryKm = getSanLeonardoBoundaryDistance(name, destLat, destLng);
        const outsideKm = Math.max(0, rounded - boundaryKm);
        const pay = basePay + (outsideKm * globalPerKm);
        return {
            pay: pay,
            payAmount: pay,
            outsideKm: outsideKm,
            boundaryKm: boundaryKm,
            isWithin: false,
            breakdown: outsideKm > 0
                ? `₱${basePay.toFixed(2)} base + ${outsideKm} km outside × ₱${globalPerKm.toFixed(2)}/km`
                : `₱${basePay.toFixed(2)} base (within boundary)`
        };
    }

    function calculateMapDistance(destLat, destLng, origLat = GARAGE_COORDS.lat, origLng = GARAGE_COORDS.lng, destName = '') {
        if (destLat == null || destLng == null || isNaN(destLat) || isNaN(destLng)) return null;

        const R = 6371; 
        const dLat = (destLat - origLat) * Math.PI / 180;
        const dLon = (destLng - origLng) * Math.PI / 180;
        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                  Math.cos(origLat * Math.PI / 180) * Math.cos(destLat * Math.PI / 180) *
                  Math.sin(dLon / 2) * Math.sin(dLon / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        const straightLineKm = R * c;

        const roadOneWayKm = straightLineKm * 1.25;

        const roundTripKm = roadOneWayKm * 2;
        let roundedKm = Math.round(roundTripKm);
        if (roundedKm < 2 && straightLineKm > 0) roundedKm = 2;

        const payInfo = calculateTripPay(roundedKm, destName, destLat, destLng);

        return {
            km: roundedKm,
            roundedKm: roundedKm,
            pay: payInfo.payAmount,
            payAmount: payInfo.payAmount,
            oneWayKm: Math.round(roadOneWayKm),
            exactKm: parseFloat(roundTripKm.toFixed(1)),
            straightLineKm: parseFloat(straightLineKm.toFixed(1)),
            outsideKm: payInfo.outsideKm,
            payBreakdown: payInfo.breakdown,
            isWithinSanLeonardo: payInfo.isWithin,
            durationMins: Math.round((roundTripKm / 40) * 60),
            destLat: parseFloat(destLat),
            destLng: parseFloat(destLng)
        };
    }

    async function reverseGeocode(lat, lng) {
        if (lat == null || lng == null || isNaN(lat) || isNaN(lng)) return null;
        if (!isWithinPhilippines(lat, lng)) {
            console.warn(`Coordinates [${lat}, ${lng}] are outside the Philippines operational boundary.`);
            return null;
        }

        const cacheKey = `geo:${parseFloat(lat).toFixed(4)},${parseFloat(lng).toFixed(4)}`;
        if (cache[cacheKey]) {
            return cache[cacheKey];
        }

        const fastWater = isKnownWaterBody(lat, lng);
        if (fastWater) {
            const waterRes = {
                isWater: true,
                formatted: fastWater.name,
                displayName: fastWater.name,
                lat: parseFloat(lat),
                lng: parseFloat(lng)
            };
            cache[cacheKey] = waterRes;
            return waterRes;
        }

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

        return {
            formatted: `Point at ${parseFloat(lat).toFixed(4)}, ${parseFloat(lng).toFixed(4)}`,
            displayName: `Point at ${parseFloat(lat).toFixed(4)}, ${parseFloat(lng).toFixed(4)}`,
            lat: parseFloat(lat),
            lng: parseFloat(lng)
        };
    }


    // ++ Philippine Purok Address Pre-Processor ++
    // "Purok" is a sub-barangay subdivision in Philippine addresses.
    // OSM/Photon do not index purok-level data, so we resolve the barangay
    // to a geocodable query and prepend the purok label to results.

    const SL_BARANGAY_ALIASES = {
        'burgos':        'Barangay Burgos, San Leonardo, Nueva Ecija',
        'bonifacio':     'Barangay Bonifacio, San Leonardo, Nueva Ecija',
        'castillejos':   'Barangay Castillejos, San Leonardo, Nueva Ecija',
        'diversion':     'Barangay Diversion, San Leonardo, Nueva Ecija',
        'magpapalayoc':  'Barangay Magpapalayoc, San Leonardo, Nueva Ecija',
        'mallorca':      'Barangay Mallorca, San Leonardo, Nueva Ecija',
        'mambangnan':    'Barangay Mambangnan, San Leonardo, Nueva Ecija',
        'nieves':        'Barangay Nieves, San Leonardo, Nueva Ecija',
        'san anton':     'Barangay San Anton, San Leonardo, Nueva Ecija',
        'san bartolome': 'Barangay San Bartolome, San Leonardo, Nueva Ecija',
        'san francisco': 'Barangay San Francisco, San Leonardo, Nueva Ecija',
        'san roque':     'Barangay San Roque, San Leonardo, Nueva Ecija',
        'santa cruz':    'Barangay Santa Cruz, San Leonardo, Nueva Ecija',
        'sta cruz':      'Barangay Santa Cruz, San Leonardo, Nueva Ecija',
        'sta. cruz':     'Barangay Santa Cruz, San Leonardo, Nueva Ecija',
        'tabuating':     'Barangay Tabuating, San Leonardo, Nueva Ecija',
        'tagumpay':      'Barangay Tagumpay, San Leonardo, Nueva Ecija',
        'san leonardo':  'San Leonardo, Nueva Ecija',
    };

    function parsePurokQuery(query) {
        // Matches: "purok 5 brgy burgos", "purok 3 barangay san roque", "purok 2 san roque"
        const m = query.match(/^purok\s+(\S+)\s+(?:brgy\.?\s*|barangay\s+)?(.+)$/i);
        if (!m) return null;
        const purokNum = m[1];
        const brgyRaw  = m[2].trim().toLowerCase();
        let resolvedQuery = null;
        let displayBrgy   = null;
        const sortedKeys = Object.keys(SL_BARANGAY_ALIASES).sort((a, b) => b.length - a.length);
        for (const key of sortedKeys) {
            if (brgyRaw === key || brgyRaw.startsWith(key)) {
                resolvedQuery = SL_BARANGAY_ALIASES[key];
                displayBrgy   = 'Brgy. ' + key.split(' ').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
                break;
            }
        }
        if (!resolvedQuery) {
            resolvedQuery = brgyRaw + ', Nueva Ecija, Philippines';
            displayBrgy   = brgyRaw.split(' ').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
        }
        return {
            purokLabel:    'Purok ' + purokNum + ', ' + displayBrgy,
            resolvedQuery: resolvedQuery,
            purokNum:      purokNum
        };
    }
    // ++ End Purok Pre-Processor ++

    async function searchAddress(query) {
        if (!query || query.trim().length < 2) return [];
        const cleanQuery = query.trim();

        const cacheKey = `search:${cleanQuery.toLowerCase()}`;
        if (cache[cacheKey]) return cache[cacheKey];

        // Detect purok patterns and rewrite to a geocodable barangay query
        const purokInfo   = parsePurokQuery(cleanQuery);
        const searchQuery = purokInfo ? purokInfo.resolvedQuery : cleanQuery;

        function decorateResults(results) {
            if (!purokInfo || !results || results.length === 0) return results;
            return results.map((r, i) => ({
                ...r,
                shortName: i === 0 ? (purokInfo.purokLabel + (r.shortName ? ', ' + r.shortName : '')) : r.shortName,
                name:      i === 0 ? (purokInfo.purokLabel + ', ' + r.name) : r.name
            }));
        }

        try {
            const controller = new AbortController();
            const timeout = setTimeout(() => controller.abort(), 2000);
            
            const photonUrl = `https://photon.komoot.io/api/?q=${encodeURIComponent(searchQuery)}&limit=8&lat=15.359042&lon=120.965016&bbox=116.0,4.5,127.0,21.5`;
            const resp = await fetch(photonUrl, { signal: controller.signal });
            clearTimeout(timeout);

            if (resp.ok) {
                const data = await resp.json();
                if (data && data.features && data.features.length > 0) {
                    const results = data.features
                        .map(f => {
                            const p = f.properties || {};
                            const coords = f.geometry && f.geometry.coordinates ? f.geometry.coordinates : [0, 0];
                            const lat = coords[1];
                            const lng = coords[0];

                            
                            if (!isWithinPhilippines(lat, lng)) return null;
                            if (isKnownWaterBody(lat, lng)) return null;
                            if (p.osm_key === 'natural' && ['water', 'sea', 'ocean', 'bay', 'coastline', 'strait'].includes(p.osm_value)) return null;

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
                                lat: lat,
                                lng: lng
                            };
                        })
                        .filter(Boolean);

                    if (results.length > 0) {
                        const decorated = decorateResults(results);
                        cache[cacheKey] = decorated;
                        return decorated;
                    }
                }
            }
        } catch (photonErr) {
            console.warn('Photon search error, falling back to OSM Nominatim:', photonErr);
        }

        try {
            const controller = new AbortController();
            const timeout = setTimeout(() => controller.abort(), 2500);
            const url = `https://nominatim.openstreetmap.org/search?format=jsonv2&q=${encodeURIComponent(searchQuery)}&countrycodes=ph&viewbox=116.0,21.5,127.0,4.5&bounded=1&limit=6&addressdetails=1`;
            const response = await fetch(url, {
                headers: { 'Accept': 'application/json' },
                signal: controller.signal
            });
            clearTimeout(timeout);

            if (!response.ok) return [];
            const results = await response.json();

            const formattedResults = results
                .map(item => {
                    const lat = parseFloat(item.lat);
                    const lng = parseFloat(item.lon);
                    if (!isWithinPhilippines(lat, lng)) return null;
                    if (isKnownWaterBody(lat, lng)) return null;
                    if (item.category === 'natural' && ['water', 'sea', 'ocean', 'bay', 'coastline', 'strait'].includes(item.type)) return null;

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
                        lat: lat,
                        lng: lng
                    };
                })
                .filter(Boolean);

            const decorated = decorateResults(formattedResults);
            cache[cacheKey] = decorated;
            return decorated;
        } catch (err) {
            console.warn('OSM Search error:', err);
            return [];
        }
    }

    async function calculateDrivingDistance(destLat, destLng, origLat = GARAGE_COORDS.lat, origLng = GARAGE_COORDS.lng, destName = '') {
        const mapDist = calculateMapDistance(destLat, destLng, origLat, origLng, destName);
        if (!mapDist) return null;

        const cacheKey = `dist:${parseFloat(origLat).toFixed(4)},${parseFloat(origLng).toFixed(4)}->${parseFloat(destLat).toFixed(4)},${parseFloat(destLng).toFixed(4)}:${destName}`;
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
                    const payInfo = calculateTripPay(roundedKm, destName, destLat, destLng);
                    const result = {
                        km: roundedKm,
                        oneWayKm: Math.round(oneWayKm),
                        exactKm: parseFloat(roundTripKm.toFixed(1)),
                        payAmount: payInfo.payAmount,
                        outsideKm: payInfo.outsideKm,
                        boundaryKm: payInfo.boundaryKm,
                        payBreakdown: payInfo.breakdown,
                        isWithinSanLeonardo: payInfo.isWithin,
                        durationMins: Math.round((roundTripKm / 40) * 60),
                        destLat: destLat,
                        destLng: destLng
                    };
                    cache[cacheKey] = result;
                    return result;
                }
            }
        } catch (e) {
        }

        cache[cacheKey] = mapDist;
        return mapDist;
    }

    async function getDistanceForDestination(destName, origLat = GARAGE_COORDS.lat, origLng = GARAGE_COORDS.lng) {
        if (!destName || typeof destName !== 'string') return null;

        const searchResults = await searchAddress(destName);
        if (!searchResults || searchResults.length === 0) return null;

        const top = searchResults[0];
        const dist = await calculateDrivingDistance(top.lat, top.lng, origLat, origLng, destName);
        if (!dist) return null;

        return {
            ...dist,
            destination: destName,
            coords: { lat: top.lat, lng: top.lng }
        };
    }

    return {
        GARAGE_COORDS: GARAGE_COORDS,
        PH_BOUNDS: PH_BOUNDS,
        WATER_REGIONS: WATER_REGIONS,
        isWithinPhilippines: isWithinPhilippines,
        isKnownWaterBody: isKnownWaterBody,
        checkIsWater: checkIsWater,
        isWithinSanLeonardo: isWithinSanLeonardo,
        getSanLeonardoBoundaryDistance: getSanLeonardoBoundaryDistance,
        calculateTripPay: calculateTripPay,
        calculateMapDistance: calculateMapDistance,
        calculateDirectRoadDistance: calculateMapDistance, 
        calculateDrivingDistance: calculateDrivingDistance,
        reverseGeocode: reverseGeocode,
        searchAddress: searchAddress,
        getDistanceForDestination: getDistanceForDestination
    };
})();
