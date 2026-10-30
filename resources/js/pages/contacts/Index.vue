<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ContactController from '@/actions/App/Http/Controllers/Admin/ContactController';
import AdminTable from '@/components/admin/AdminTable.vue';
import DeleteButton from '@/components/admin/DeleteButton.vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import Pagination from '@/components/admin/Pagination.vue';
import SearchFilter from '@/components/admin/SearchFilter.vue';
import { Button } from '@/components/ui/button';
import type { Paginated } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Contacts', href: ContactController.index() }],
    },
});

type Contact = {
    id: number;
    name: string;
    role: string | null;
    phone: string;
    address: string | null;
    owner: string;
};

defineProps<{
    contacts: Paginated<Contact>;
    filters: { search?: string };
    canManage: boolean;
}>();
</script>

<template>
    <Head title="Contacts" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <PageHeader
            title="Contacts"
            description="Numbers pilgrims can call: leaders, doctors, hotels, the agency office."
        >
            <Button v-if="canManage" as-child>
                <Link :href="ContactController.create()">New contact</Link>
            </Button>
        </PageHeader>

        <SearchFilter :filters="filters" placeholder="Search contacts" />

        <AdminTable :empty="contacts.data.length === 0">
            <template #head>
                <tr>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Phone</th>
                    <th>Shown to</th>
                    <th />
                </tr>
            </template>
            <tr v-for="contact in contacts.data" :key="contact.id">
                <td class="font-medium">
                    {{ contact.name }}
                    <p
                        v-if="contact.address"
                        class="text-xs font-normal text-muted-foreground"
                    >
                        {{ contact.address }}
                    </p>
                </td>
                <td>{{ contact.role ?? '—' }}</td>
                <td>
                    <a :href="`tel:${contact.phone}`" class="hover:underline">{{
                        contact.phone
                    }}</a>
                </td>
                <td>{{ contact.owner }}</td>
                <td class="text-right whitespace-nowrap">
                    <template v-if="canManage">
                        <Button variant="ghost" size="sm" as-child>
                            <Link :href="ContactController.edit(contact.id)"
                                >Edit</Link
                            >
                        </Button>
                        <DeleteButton
                            :action="ContactController.destroy.form(contact.id)"
                            :description="`Delete ${contact.name}?`"
                        />
                    </template>
                </td>
            </tr>
        </AdminTable>

        <Pagination :page="contacts" />
    </div>
</template>
