<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import GroupController from '@/actions/App/Http/Controllers/Admin/GroupController';
import PilgrimController from '@/actions/App/Http/Controllers/Admin/PilgrimController';
import AdminTable from '@/components/admin/AdminTable.vue';
import DeleteButton from '@/components/admin/DeleteButton.vue';
import NativeSelect from '@/components/admin/NativeSelect.vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import Pagination from '@/components/admin/Pagination.vue';
import SearchFilter from '@/components/admin/SearchFilter.vue';
import { Button } from '@/components/ui/button';
import type { Paginated } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Groups', href: GroupController.index() }],
    },
});

type Group = {
    id: number;
    name: string;
    pilgrims_count: number;
    flight_group: { id: number; code: string } | null;
    leader: { id: number; name: string } | null;
};

const props = defineProps<{
    groups: Paginated<Group>;
    flightGroups: { id: number; code: string }[];
    filters: { search?: string; flight_group_id?: string };
    canManage: boolean;
}>();

function filterTrip(event: Event) {
    const value = (event.target as HTMLSelectElement).value;
    router.get(
        GroupController.index().url,
        { ...props.filters, flight_group_id: value || undefined },
        { preserveState: true },
    );
}
</script>

<template>
    <Head title="Groups" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <PageHeader
            title="Groups"
            description="Pilgrims travel in groups, each with a leader."
        >
            <Button v-if="canManage" as-child>
                <Link :href="GroupController.create()">New group</Link>
            </Button>
        </PageHeader>

        <div class="flex flex-wrap gap-2">
            <SearchFilter :filters="filters" placeholder="Search groups" />
            <NativeSelect
                class="w-48"
                aria-label="Filter by trip"
                @change="filterTrip"
            >
                <option value="">All trips</option>
                <option
                    v-for="trip in flightGroups"
                    :key="trip.id"
                    :value="trip.id"
                    :selected="
                        String(trip.id) ===
                        String(filters.flight_group_id ?? '')
                    "
                >
                    {{ trip.code }}
                </option>
            </NativeSelect>
        </div>

        <AdminTable :empty="groups.data.length === 0">
            <template #head>
                <tr>
                    <th>Name</th>
                    <th>Trip</th>
                    <th>Leader</th>
                    <th class="text-right">Pilgrims</th>
                    <th />
                </tr>
            </template>
            <tr v-for="group in groups.data" :key="group.id">
                <td class="font-medium">{{ group.name }}</td>
                <td>{{ group.flight_group?.code ?? '—' }}</td>
                <td>{{ group.leader?.name ?? '—' }}</td>
                <td class="text-right tabular-nums">
                    <Link
                        :href="
                            PilgrimController.index({
                                query: { group_id: group.id },
                            })
                        "
                        class="hover:underline"
                    >
                        {{ group.pilgrims_count }}
                    </Link>
                </td>
                <td class="text-right whitespace-nowrap">
                    <template v-if="canManage">
                        <Button variant="ghost" size="sm" as-child>
                            <Link :href="GroupController.edit(group.id)"
                                >Edit</Link
                            >
                        </Button>
                        <DeleteButton
                            :action="GroupController.destroy.form(group.id)"
                            label="Archive"
                            :description="`Archive ${group.name}? Its pilgrims stop appearing in lists.`"
                        />
                    </template>
                </td>
            </tr>
        </AdminTable>

        <Pagination :page="groups" />
    </div>
</template>
