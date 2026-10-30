<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import PlaceController from '@/actions/App/Http/Controllers/Admin/PlaceController';
import AdminTable from '@/components/admin/AdminTable.vue';
import DeleteButton from '@/components/admin/DeleteButton.vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import Pagination from '@/components/admin/Pagination.vue';
import SearchFilter from '@/components/admin/SearchFilter.vue';
import { Button } from '@/components/ui/button';
import { humanize } from '@/lib/format';
import type { Paginated } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Holy sites', href: PlaceController.index() }],
    },
});

type Place = {
    id: number;
    name: string;
    city: string;
    latitude: number;
    longitude: number;
    radius_meters: number;
    prayers_count: number;
};

defineProps<{
    places: Paginated<Place>;
    filters: { search?: string };
}>();
</script>

<template>
    <Head title="Holy sites" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <PageHeader
            title="Holy sites"
            description="Geofenced sites. Pilgrims entering one receive its prayers in the app."
        >
            <Button as-child>
                <Link :href="PlaceController.create()">New site</Link>
            </Button>
        </PageHeader>

        <SearchFilter :filters="filters" placeholder="Search sites" />

        <AdminTable :empty="places.data.length === 0">
            <template #head>
                <tr>
                    <th>Name</th>
                    <th>City</th>
                    <th>Centre</th>
                    <th class="text-right">Radius</th>
                    <th class="text-right">Prayers</th>
                    <th />
                </tr>
            </template>
            <tr v-for="place in places.data" :key="place.id">
                <td class="font-medium">{{ place.name }}</td>
                <td>{{ humanize(place.city) }}</td>
                <td class="font-mono text-xs">
                    {{ place.latitude.toFixed(5) }},
                    {{ place.longitude.toFixed(5) }}
                </td>
                <td class="text-right tabular-nums">
                    {{ place.radius_meters }} m
                </td>
                <td class="text-right tabular-nums">
                    {{ place.prayers_count }}
                </td>
                <td class="text-right whitespace-nowrap">
                    <Button variant="ghost" size="sm" as-child>
                        <Link :href="PlaceController.edit(place.id)">Edit</Link>
                    </Button>
                    <DeleteButton
                        :action="PlaceController.destroy.form(place.id)"
                        :description="`Delete ${place.name} and its prayers?`"
                    />
                </td>
            </tr>
        </AdminTable>

        <Pagination :page="places" />
    </div>
</template>
