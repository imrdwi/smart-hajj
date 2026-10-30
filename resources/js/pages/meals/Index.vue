<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import MealController from '@/actions/App/Http/Controllers/Admin/MealController';
import AdminTable from '@/components/admin/AdminTable.vue';
import DeleteButton from '@/components/admin/DeleteButton.vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import Pagination from '@/components/admin/Pagination.vue';
import SearchFilter from '@/components/admin/SearchFilter.vue';
import { Button } from '@/components/ui/button';
import { formatDateTime, humanize } from '@/lib/format';
import type { Paginated } from '@/types';

defineOptions({
    layout: { breadcrumbs: [{ title: 'Meals', href: MealController.index() }] },
});

type Meal = {
    id: number;
    type: string;
    menu: string | null;
    served_at: string;
    flight_groups_count: number;
    groups_count: number;
    pilgrims_count: number;
};

defineProps<{
    meals: Paginated<Meal>;
    filters: { search?: string };
    canManage: boolean;
}>();
</script>

<template>
    <Head title="Meals" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <PageHeader
            title="Meals"
            description="Catering for each trip, group or pilgrim."
        >
            <Button v-if="canManage" as-child>
                <Link :href="MealController.create()">New meal</Link>
            </Button>
        </PageHeader>

        <SearchFilter :filters="filters" placeholder="Search menus" />

        <AdminTable :empty="meals.data.length === 0">
            <template #head>
                <tr>
                    <th>Served</th>
                    <th>Type</th>
                    <th>Menu</th>
                    <th class="text-right">Trips / groups / pilgrims</th>
                    <th />
                </tr>
            </template>
            <tr v-for="meal in meals.data" :key="meal.id">
                <td>{{ formatDateTime(meal.served_at) }}</td>
                <td class="font-medium">{{ humanize(meal.type) }}</td>
                <td class="max-w-sm truncate">{{ meal.menu ?? '—' }}</td>
                <td class="text-right tabular-nums">
                    {{ meal.flight_groups_count }} / {{ meal.groups_count }} /
                    {{ meal.pilgrims_count }}
                </td>
                <td class="text-right whitespace-nowrap">
                    <template v-if="canManage">
                        <Button variant="ghost" size="sm" as-child>
                            <Link :href="MealController.edit(meal.id)"
                                >Edit</Link
                            >
                        </Button>
                        <DeleteButton
                            :action="MealController.destroy.form(meal.id)"
                            description="Delete this meal?"
                        />
                    </template>
                </td>
            </tr>
        </AdminTable>

        <Pagination :page="meals" />
    </div>
</template>
