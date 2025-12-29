<script setup>
import Boundaries from '../../components/Boundaries.vue';
</script>

# Boundaries Demo

Rendering GeoJSON for `75` (Kota Pekalongan) in `33` (Jawa Tengah)  
Source: <a href="https://nusa.creasi.dev/api/33/75.geojson" target="_blank">https://nusa.creasi.dev/api/33/75.geojson</a>

```js
import L from 'leaflet'

const DEMO_URL = 'https://nusa.creasi.dev/api/33/75.geojson'

try {
    // Initialize Map
    // We set center and zoom initially to avoid "undefined" errors if fitBounds fails
    map = L.map(mapContainer).setView([-2.5, 118], 5)

    // Add OpenStreetMap Tile Layer
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map)

    // Fetch GeoJSON Data
    const response = await fetch(DEMO_URL)
    
    if (!response.ok) throw new Error(`Failed to fetch data: ${response.statusText}`)

    // Add GeoJSON Layer
    const geoJsonLayer = L.geoJSON(await response.json(), {
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
} catch (e) {
    console.error('Map initialization error:', e)
}
```

<Boundaries />

## Next Steps

- **[HTTP Endpoints](/en/api/http-endpoint)** - Learn to use the static API