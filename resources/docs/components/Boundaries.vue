<script setup>
import { onMounted, ref, onUnmounted } from 'vue'

const mapContainer = ref(null)
const loading = ref(true)
const error = ref(null)
let map = null

const PROVINCE_CODE = '33'
const REGENCY_CODE = '75'
const DEMO_URL = `https://nusa.creasi.dev/static/${PROVINCE_CODE}/${REGENCY_CODE}.geojson`

// Dynamic loader for Leaflet since it's not in package.json dependencies
const loadLeaflet = () => {
    return new Promise((resolve, reject) => {
        if (typeof window === 'undefined') return reject(new Error('Client-side only'))

        // Return existing global if available
        if (window.L) return resolve(window.L)

        // Load CSS
        if (!document.getElementById('leaflet-css')) {
            const link = document.createElement('link')
            link.id = 'leaflet-css'
            link.rel = 'stylesheet'
            link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css'
            link.integrity = 'sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY='
            link.crossOrigin = ''
            document.head.appendChild(link)
        }

        // Load JS
        const script = document.createElement('script')
        script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js'
        script.integrity = 'sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo='
        script.crossOrigin = ''
        script.onload = () => resolve(window.L)
        script.onerror = () => reject(new Error('Failed to load Leaflet script'))
        document.head.appendChild(script)
    })
}

// Helper to determine array depth to detect malformed GeoJSON
const getDepth = (arr) => {
    let depth = 0;
    let children = arr;
    while (Array.isArray(children)) {
        depth++;
        children = children[0];
    }
    return depth;
}

// Normalize GeoJSON data to fix structural issues
const normalizeGeoJSON = (data) => {
    if (!data) return data;

    if (data.type === 'FeatureCollection' && Array.isArray(data.features)) {
        data.features = data.features.map(normalizeFeature);
    } else if (data.type === 'Feature') {
        normalizeFeature(data);
    }

    return data;
}

const normalizeFeature = (feature) => {
    if (!feature || !feature.geometry) return feature;

    const geom = feature.geometry;

    // Fix mismatch between type and coordinate depth
    // Polygon should be depth 3: [ [ [x,y], ... ] ]
    // MultiPolygon should be depth 4: [ [ [ [x,y], ... ] ] ]

    if (geom.coordinates && Array.isArray(geom.coordinates) && geom.coordinates.length > 0) {
        const depth = getDepth(geom.coordinates);

        if (geom.type === 'MultiPolygon' && depth === 3) {
            console.warn(`Fixing malformed GeoJSON: Type is MultiPolygon but coordinates depth is 3. Changing type to Polygon for feature: ${feature.properties?.name}`);
            geom.type = 'Polygon';
        } else if (geom.type === 'Polygon' && depth === 4) {
            console.warn(`Fixing malformed GeoJSON: Type is Polygon but coordinates depth is 4. Changing type to MultiPolygon for feature: ${feature.properties?.name}`);
            geom.type = 'MultiPolygon';
        }
    }

    return feature;
}

onMounted(async () => {
    // Ensure we are in a browser environment
    if (typeof window === 'undefined') return

    try {
        const L = await loadLeaflet()

        if (!mapContainer.value) return

        // Initialize Map
        // We set center and zoom initially to avoid "undefined" errors if fitBounds fails
        map = L.map(mapContainer.value).setView([-2.5, 118], 5)

        // Add OpenStreetMap Tile Layer
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map)

        // Fetch GeoJSON Data
        const response = await fetch(DEMO_URL)
        if (!response.ok) throw new Error(`Failed to fetch data: ${response.statusText}`)

        let geoJsonData = await response.json()

        // Validate Data
        if (!geoJsonData || !geoJsonData.type) {
            throw new Error('Invalid GeoJSON data format')
        }

        // Normalize Data (Fix malformed MultiPolygon/Polygon issues)
        geoJsonData = normalizeGeoJSON(geoJsonData);

        // Add GeoJSON Layer
        const geoJsonLayer = L.geoJSON(geoJsonData, {
            style: {
                color: '#3b82f6', // Tailwind blue-500
                weight: 2,
                opacity: 0.8,
                fillOpacity: 0.2
            },
            pointToLayer: (feature, latlng) => {
                // Handle Point geometries (like the capital city marker)
                return L.circleMarker(latlng, {
                    radius: 6,
                    fillColor: '#ef4444',
                    color: '#fff',
                    weight: 1,
                    opacity: 1,
                    fillOpacity: 0.8
                })
            },
            onEachFeature: (feature, layer) => {
                if (feature.properties) {
                    const name = feature.properties.name || 'Unknown'
                    const code = feature.properties.code || '-'
                    layer.bindPopup(`<strong>${name}</strong><br>Code: ${code}`)
                }
            }
        }).addTo(map)

        // Fit map to bounds safely
        try {
            const bounds = geoJsonLayer.getBounds()
            if (bounds.isValid()) {
                map.fitBounds(bounds, { padding: [50, 50] })
            } else {
                console.warn('Invalid bounds from GeoJSON, keeping default view')
            }
        } catch (err) {
            console.warn('Error fitting bounds:', err)
        }

        loading.value = false
    } catch (e) {
        console.error('Map initialization error:', e)
        error.value = e.message
        loading.value = false
    }
})

onUnmounted(() => {
    if (map) {
        map.remove()
        map = null
    }
})
</script>

<template>
    <div class="boundaries-card">
        <div class="header">
            <h3>Geographic Boundaries Demo</h3>
            <p>Rendering GeoJSON for <code>{{ REGENCY_CODE }}</code> (Kota Pekalongan) in <code>{{ PROVINCE_CODE }}</code> (Jawa Tengah)</p>
            <p class="source-link">
                Source: <a :href="DEMO_URL" target="_blank">{{ DEMO_URL }}</a>
            </p>
        </div>

        <div class="map-wrapper">
            <div ref="mapContainer" class="map-container"></div>

            <div v-if="loading" class="overlay">
                <div class="spinner"></div>
                <span>Loading map data...</span>
            </div>

            <div v-if="error" class="overlay error">
                <p><strong>Error loading map</strong></p>
                <span>{{ error }}</span>
            </div>
        </div>
    </div>
</template>

<style scoped>
.boundaries-card {
    border: 1px solid var(--vp-c-divider);
    border-radius: 8px;
    background-color: var(--vp-c-bg-soft);
    margin: 1rem 0;
    overflow: hidden;
}

.header {
    padding: 1rem;
    border-bottom: 1px solid var(--vp-c-divider);
}

.header h3 {
    margin: 0 0 0.5rem 0;
    font-size: 1.1rem;
    font-weight: 600;
}

.header p {
    margin: 0;
    font-size: 0.9rem;
    color: var(--vp-c-text-2);
}

.source-link {
    display: block;
    margin-top: 0.5rem !important;
    font-size: 0.8rem !important;
    font-family: var(--vp-font-family-mono);
    word-break: break-all;
}

.map-wrapper {
    position: relative;
    height: 400px;
    width: 100%;
    background-color: var(--vp-c-bg-alt);
}

.map-container {
    height: 100%;
    width: 100%;
    z-index: 1;
}

.overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.9);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    gap: 10px;
    color: var(--vp-c-text-1);
    text-align: center;
    padding: 20px;
}

.dark .overlay {
    background: rgba(30, 30, 30, 0.9);
}

.error {
    color: var(--vp-c-danger-1);
}

.spinner {
    width: 30px;
    height: 30px;
    border: 3px solid var(--vp-c-divider);
    border-top-color: var(--vp-c-brand-1);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Fix for Leaflet in dark mode contexts */
:deep(.leaflet-popup-content-wrapper),
:deep(.leaflet-popup-tip) {
    background: var(--vp-c-bg);
    color: var(--vp-c-text-1);
}

:deep(.leaflet-container) {
    font-family: var(--vp-font-family-base);
}
</style>
