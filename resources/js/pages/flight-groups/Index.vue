<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import FlightGroupController from '@/actions/App/Http/Controllers/Admin/FlightGroupController';
import GroupController from '@/actions/App/Http/Controllers/Admin/GroupController';
import AdminTable from '@/components/admin/AdminTable.vue';
import DeleteButton from '@/components/admin/DeleteButton.vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import Pagination from '@/components/admin/Pagination.vue';
import SearchFilter from '@/components/admin/SearchFilter.vue';
import { Button } from '@/components/ui/button';
import { formatDateTime } from '@/lib/format';
import type { Paginated } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Trips', href: FlightGroupController.index() }],
    },
});

type FlightGroup = {
    id: number;
    code: string;
    airline: string | null;
    flight_number: string | null;
    departs_at: string;
    returns_at: string | null;
    groups_count: number;
    pilgrims_count: number;
};

defineProps<{
    flightGroups: Paginated<FlightGroup>;
    filters: { search?: string };
    canManage: boolean;
}>();
</script>

<template>
    <Head title="Trips" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <PageHeader
            title="Trips"
            description="Each trip is one departure: its flight and travel dates."
        >
            <Button v-if="canManage" as-child>
                <Link :href="FlightGroupController.create()">New trip</Link>
            </Button>
        </PageHeader>

        <SearchFilter :filters="filters" placeholder="Search by trip code" />

        <AdminTable :empty="flightGroups.data.length === 0">
            <template #head>
                <tr>
                    <th>Code</th>
                    <th>Flight</th>
                    <th>Departs</th>
                    <th>Returns</th>
                    <th class="text-right">Groups</th>
                    <th class="text-right">Pilgrims</th>
                    <th />
                </tr>
            </template>
            <tr v-for="trip in flightGroups.data" :key="trip.id">
                <td class="font-medium">{{ trip.code }}</td>
                <td>
                    {{
                        [trip.airline, trip.flight_number]
                            .filter(Boolean)
                            .join(' ') || '—'
                    }}
                </td>
                <td>{{ formatDateTime(trip.departs_at) }}</td>
                <td>{{ formatDateTime(trip.returns_at) }}</td>
                <td class="text-right tabular-nums">
                    <Link
                        :href="
                            GroupController.index({
                                query: { flight_group_id: trip.id },
                            })
                        "
                        class="hover:underline"
                    >
                        {{ trip.groups_count }}
                    </Link>
                </td>
                <td class="text-right tabular-nums">
                    {{ trip.pilgrims_count }}
                </td>
                <td class="text-right whitespace-nowrap">
                    <template v-if="canManage">
                        <Button variant="ghost" size="sm" as-child>
                            <Link :href="FlightGroupController.edit(trip.id)"
                                >Edit</Link
                            >
                        </Button>
                        <DeleteButton
                            :action="
                                FlightGroupController.destroy.form(trip.id)
                            "
                            label="Archive"
                            :description="`Archive trip ${trip.code}? Its groups and pilgrims are archived with it.`"
                        />
                    </template>
                </td>
            </tr>
        </AdminTable>

        <Pagination :page="flightGroups" />
    </div>
</template>
