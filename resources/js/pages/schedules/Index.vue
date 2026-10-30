<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ScheduleController from '@/actions/App/Http/Controllers/Admin/ScheduleController';
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
        breadcrumbs: [{ title: 'Schedules', href: ScheduleController.index() }],
    },
});

type Schedule = {
    id: number;
    title: string;
    starts_at: string;
    ends_at: string | null;
    place: { name: string } | null;
    flight_groups_count: number;
    groups_count: number;
    pilgrims_count: number;
};

defineProps<{
    schedules: Paginated<Schedule>;
    filters: { search?: string };
    canManage: boolean;
}>();

function audience(schedule: Schedule): string {
    const parts = [
        schedule.flight_groups_count &&
            `${schedule.flight_groups_count} trip(s)`,
        schedule.groups_count && `${schedule.groups_count} group(s)`,
        schedule.pilgrims_count && `${schedule.pilgrims_count} pilgrim(s)`,
    ].filter(Boolean);

    return parts.length ? parts.join(', ') : 'Unassigned';
}
</script>

<template>
    <Head title="Schedules" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <PageHeader
            title="Schedules"
            description="Activities and rituals, optionally at a holy site."
        >
            <Button v-if="canManage" as-child>
                <Link :href="ScheduleController.create()">New schedule</Link>
            </Button>
        </PageHeader>

        <SearchFilter :filters="filters" placeholder="Search schedules" />

        <AdminTable :empty="schedules.data.length === 0">
            <template #head>
                <tr>
                    <th>Title</th>
                    <th>Place</th>
                    <th>Starts</th>
                    <th>Ends</th>
                    <th>Assigned to</th>
                    <th />
                </tr>
            </template>
            <tr v-for="schedule in schedules.data" :key="schedule.id">
                <td class="font-medium">{{ schedule.title }}</td>
                <td>{{ schedule.place?.name ?? '—' }}</td>
                <td>{{ formatDateTime(schedule.starts_at) }}</td>
                <td>{{ formatDateTime(schedule.ends_at) }}</td>
                <td class="text-muted-foreground">{{ audience(schedule) }}</td>
                <td class="text-right whitespace-nowrap">
                    <template v-if="canManage">
                        <Button variant="ghost" size="sm" as-child>
                            <Link :href="ScheduleController.edit(schedule.id)"
                                >Edit</Link
                            >
                        </Button>
                        <DeleteButton
                            :action="
                                ScheduleController.destroy.form(schedule.id)
                            "
                            :description="`Delete ${schedule.title}?`"
                        />
                    </template>
                </td>
            </tr>
        </AdminTable>

        <Pagination :page="schedules" />
    </div>
</template>
