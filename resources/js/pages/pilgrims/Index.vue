<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import PilgrimController from '@/actions/App/Http/Controllers/Admin/PilgrimController';
import AdminTable from '@/components/admin/AdminTable.vue';
import NativeSelect from '@/components/admin/NativeSelect.vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import Pagination from '@/components/admin/Pagination.vue';
import SearchFilter from '@/components/admin/SearchFilter.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { humanize } from '@/lib/format';
import type { Paginated } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Pilgrims', href: PilgrimController.index() }],
    },
});

type Pilgrim = {
    id: number;
    name: string;
    gender: string;
    phone: string | null;
    devices_count: number;
    group: {
        id: number;
        name: string;
        flight_group: { code: string } | null;
    } | null;
};

const props = defineProps<{
    pilgrims: Paginated<Pilgrim>;
    groups: { id: number; name: string }[];
    filters: { search?: string; group_id?: string };
    canManage: boolean;
}>();

function filterGroup(event: Event) {
    const value = (event.target as HTMLSelectElement).value;
    router.get(
        PilgrimController.index().url,
        { ...props.filters, group_id: value || undefined },
        { preserveState: true },
    );
}
</script>

<template>
    <Head title="Pilgrims" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <PageHeader
            title="Pilgrims"
            description="Personal details, devices and itinerary for each pilgrim."
        >
            <Button v-if="canManage" as-child>
                <Link :href="PilgrimController.create()">Register pilgrim</Link>
            </Button>
        </PageHeader>

        <div class="flex flex-wrap gap-2">
            <SearchFilter :filters="filters" placeholder="Search by name" />
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
                        String(group.id) === String(filters.group_id ?? '')
                    "
                >
                    {{ group.name }}
                </option>
            </NativeSelect>
        </div>

        <AdminTable :empty="pilgrims.data.length === 0">
            <template #head>
                <tr>
                    <th>Name</th>
                    <th>Gender</th>
                    <th>Group</th>
                    <th>Trip</th>
                    <th>Phone</th>
                    <th>Device</th>
                </tr>
            </template>
            <tr v-for="pilgrim in pilgrims.data" :key="pilgrim.id">
                <td>
                    <Link
                        :href="PilgrimController.show(pilgrim.id)"
                        class="font-medium hover:underline"
                        >{{ pilgrim.name }}</Link
                    >
                </td>
                <td>{{ humanize(pilgrim.gender) }}</td>
                <td>{{ pilgrim.group?.name ?? '—' }}</td>
                <td>{{ pilgrim.group?.flight_group?.code ?? '—' }}</td>
                <td>{{ pilgrim.phone ?? '—' }}</td>
                <td>
                    <Badge
                        :variant="
                            pilgrim.devices_count ? 'default' : 'secondary'
                        "
                    >
                        {{ pilgrim.devices_count ? 'Paired' : 'None' }}
                    </Badge>
                </td>
            </tr>
        </AdminTable>

        <Pagination :page="pilgrims" />
    </div>
</template>
