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

        // Add GeoJSON Layer
        const geoJsonLayer = L.geoJSON(geoJsonData, {
            style: {
                color: '#3b82f6', // Tailwind blue-500
                weight: 2,
                opacity: 0.8,
                fillOpacity: 0.2
            },
            pointToLayer: (feature, latlng) => L.marker(latlng, {
                radius: 6,
                fillColor: '#ef4444',
                color: '#fff',
                weight: 1,
                opacity: 1,
                fillOpacity: 0.8
            }),
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
</template>

<style scoped>
.map-wrapper {
    position: relative;
    height: 400px;
    width: 100%;
    border-radius: 8px;
    overflow: hidden;
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
