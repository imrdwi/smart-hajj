<script setup lang="ts">
import 'leaflet/dist/leaflet.css';
import L from 'leaflet';
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { formatDateTime } from '@/lib/format';

type Position = {
    pilgrim_id: number;
    name: string;
    group: string | null;
    latitude: number;
    longitude: number;
    recorded_at: string;
};
type Place = {
    id: number;
    name: string;
    latitude: number;
    longitude: number;
    radius_meters: number;
};

const props = defineProps<{
    positions: Position[];
    places: Place[];
    pilgrimUrl: (id: number) => string;
}>();

const container = ref<HTMLDivElement>();
let map: L.Map | undefined;
let layer: L.LayerGroup | undefined;

function escape(text: string): string {
    const element = document.createElement('span');
    element.textContent = text;

    return element.innerHTML;
}

function render() {
    if (!map || !layer) {
        return;
    }

    layer.clearLayers();

    for (const place of props.places) {
        L.circle([place.latitude, place.longitude], {
            radius: place.radius_meters,
            color: '#0d9488',
            weight: 1,
            fillOpacity: 0.08,
        })
            .bindTooltip(escape(place.name))
            .addTo(layer);
    }

    const points: L.LatLngExpression[] = [];

    for (const position of props.positions) {
        const point: L.LatLngExpression = [
            position.latitude,
            position.longitude,
        ];
        points.push(point);
        L.circleMarker(point, { radius: 6, color: '#1d4ed8', fillOpacity: 0.9 })
            .bindPopup(
                `<a href="${props.pilgrimUrl(position.pilgrim_id)}"><strong>${escape(position.name)}</strong></a><br>` +
                    `${escape(position.group ?? '')}<br><small>${escape(formatDateTime(position.recorded_at))}</small>`,
            )
            .addTo(layer);
    }

    const bounds = points.length
        ? points
        : props.places.map(
              (place) =>
                  [place.latitude, place.longitude] as L.LatLngExpression,
          );

    if (bounds.length) {
        map.fitBounds(L.latLngBounds(bounds), {
            padding: [32, 32],
            maxZoom: 16,
        });
    }
}

onMounted(() => {
    if (!container.value) {
        return;
    }

    map = L.map(container.value).setView([21.4225, 39.8262], 13);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors',
    }).addTo(map);
    layer = L.layerGroup().addTo(map);
    render();
});

watch(() => [props.positions, props.places], render);

onBeforeUnmount(() => map?.remove());
</script>

<template>
    <div
        ref="container"
        class="z-0 h-[32rem] w-full rounded-xl border"
        role="region"
        aria-label="Map of pilgrim locations"
    />
</template>
