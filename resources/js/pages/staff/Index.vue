<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import StaffController from '@/actions/App/Http/Controllers/Admin/StaffController';
import AdminTable from '@/components/admin/AdminTable.vue';
import DeleteButton from '@/components/admin/DeleteButton.vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import Pagination from '@/components/admin/Pagination.vue';
import SearchFilter from '@/components/admin/SearchFilter.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { humanize } from '@/lib/format';
import type { Paginated } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Staff accounts', href: StaffController.index() },
        ],
    },
});

type Staff = {
    id: number;
    name: string;
    email: string;
    role: string;
    agency: { name: string } | null;
};

defineProps<{
    staff: Paginated<Staff>;
    filters: { search?: string };
}>();

const me = usePage().props.auth.user;
</script>

<template>
    <Head title="Staff accounts" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <PageHeader
            title="Staff accounts"
            description="Who can sign in to this panel, and what they can do."
        >
            <Button as-child>
                <Link :href="StaffController.create()">New account</Link>
            </Button>
        </PageHeader>

        <SearchFilter
            :filters="filters"
            placeholder="Search by name or email"
        />

        <AdminTable :empty="staff.data.length === 0">
            <template #head>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th v-if="me.role === 'system_admin'">Agency</th>
                    <th />
                </tr>
            </template>
            <tr v-for="member in staff.data" :key="member.id">
                <td class="font-medium">{{ member.name }}</td>
                <td>{{ member.email }}</td>
                <td>
                    <Badge variant="secondary">{{
                        humanize(member.role)
                    }}</Badge>
                </td>
                <td v-if="me.role === 'system_admin'">
                    {{ member.agency?.name ?? '—' }}
                </td>
                <td class="text-right whitespace-nowrap">
                    <Button variant="ghost" size="sm" as-child>
                        <Link :href="StaffController.edit(member.id)"
                            >Edit</Link
                        >
                    </Button>
                    <DeleteButton
                        v-if="member.id !== me.id"
                        :action="StaffController.destroy.form(member.id)"
                        :description="`Delete ${member.name}'s account?`"
                    />
                </td>
            </tr>
        </AdminTable>

        <Pagination :page="staff" />
    </div>
</template>
