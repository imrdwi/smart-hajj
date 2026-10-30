<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AgencyController from '@/actions/App/Http/Controllers/Admin/AgencyController';
import AdminTable from '@/components/admin/AdminTable.vue';
import DeleteButton from '@/components/admin/DeleteButton.vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import Pagination from '@/components/admin/Pagination.vue';
import SearchFilter from '@/components/admin/SearchFilter.vue';
import { Button } from '@/components/ui/button';
import type { Paginated } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Agencies', href: AgencyController.index() }],
    },
});

type Agency = {
    id: number;
    name: string;
    license_number: string | null;
    phone: string | null;
    flight_groups_count: number;
    users_count: number;
};

defineProps<{
    agencies: Paginated<Agency>;
    filters: { search?: string };
}>();
</script>

<template>
    <Head title="Agencies" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <PageHeader
            title="Agencies"
            description="Travel agencies using the platform."
        >
            <Button as-child>
                <Link :href="AgencyController.create()">New agency</Link>
            </Button>
        </PageHeader>

        <SearchFilter :filters="filters" placeholder="Search agencies" />

        <AdminTable :empty="agencies.data.length === 0">
            <template #head>
                <tr>
                    <th>Name</th>
                    <th>License</th>
                    <th>Phone</th>
                    <th class="text-right">Trips</th>
                    <th class="text-right">Staff</th>
                    <th />
                </tr>
            </template>
            <tr v-for="agency in agencies.data" :key="agency.id">
                <td class="font-medium">{{ agency.name }}</td>
                <td>{{ agency.license_number ?? '—' }}</td>
                <td>{{ agency.phone ?? '—' }}</td>
                <td class="text-right tabular-nums">
                    {{ agency.flight_groups_count }}
                </td>
                <td class="text-right tabular-nums">
                    {{ agency.users_count }}
                </td>
                <td class="text-right whitespace-nowrap">
                    <Button variant="ghost" size="sm" as-child>
                        <Link :href="AgencyController.edit(agency.id)"
                            >Edit</Link
                        >
                    </Button>
                    <DeleteButton
                        :action="AgencyController.destroy.form(agency.id)"
                        label="Archive"
                        :description="`Archive ${agency.name}?`"
                    />
                </td>
            </tr>
        </AdminTable>

        <Pagination :page="agencies" />
    </div>
</template>
