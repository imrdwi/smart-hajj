<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import LocationController from '@/actions/App/Http/Controllers/Admin/LocationController';
import PilgrimController from '@/actions/App/Http/Controllers/Admin/PilgrimController';
import NativeSelect from '@/components/admin/NativeSelect.vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import PilgrimMap from '@/components/admin/PilgrimMap.vue';
import { Button } from '@/components/ui/button';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Pilgrim locations', href: LocationController.index() },
        ],
    },
});

const props = defineProps<{
    positions: {
        pilgrim_id: number;
        name: string;
        group: string | null;
        latitude: number;
        longitude: number;
        recorded_at: string;
    }[];
    places: {
        id: number;
        name: string;
        latitude: number;
        longitude: number;
        radius_meters: number;
    }[];
    groups: { id: number; name: string }[];
    filters: { group_id?: string };
}>();

function filterGroup(event: Event) {
    const value = (event.target as HTMLSelectElement).value;
    router.get(
        LocationController.index().url,
        { group_id: value || undefined },
        { preserveState: true },
    );
}

const pilgrimUrl = (id: number) => PilgrimController.show(id).url;
</script>

<template>
    <Head title="Pilgrim locations" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <PageHeader
            title="Pilgrim locations"
            :description="`Last reported position of ${positions.length} pilgrim(s). Shaded circles are holy-site geofences.`"
        >
            <NativeSelect
                class="w-48"
                aria-label="Filter by group"
                @change="filterGroup"
            >
                <option value="">All groups</option>
                <option
                    v-for="group in groups"
                    :key="group.id"
                    :value="group.id"
                    :selected="
                        String(group.id) ===
                        String(props.filters.group_id ?? '')
                    "
                >
                    {{ group.name }}
                </option>
            </NativeSelect>
            <Button
                variant="outline"
                @click="router.reload({ only: ['positions'] })"
                >Refresh</Button
            >
        </PageHeader>

        <PilgrimMap
            :positions="positions"
            :places="places"
            :pilgrim-url="pilgrimUrl"
        />
    </div>
</template>
