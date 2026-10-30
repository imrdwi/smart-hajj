<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import DeviceController from '@/actions/App/Http/Controllers/Admin/DeviceController';
import PilgrimController from '@/actions/App/Http/Controllers/Admin/PilgrimController';
import AdminTable from '@/components/admin/AdminTable.vue';
import DeleteButton from '@/components/admin/DeleteButton.vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import Pagination from '@/components/admin/Pagination.vue';
import SearchFilter from '@/components/admin/SearchFilter.vue';
import { Badge } from '@/components/ui/badge';
import { formatDateTime } from '@/lib/format';
import type { Paginated } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Devices', href: DeviceController.index() }],
    },
});

type Device = {
    id: number;
    identifier: string;
    platform: string | null;
    last_seen_at: string | null;
    tokens_count: number;
    pilgrim: { id: number; name: string } | null;
};

defineProps<{
    devices: Paginated<Device>;
    filters: { search?: string };
}>();
</script>

<template>
    <Head title="Devices" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <PageHeader
            title="Devices"
            description="Phones paired with pilgrims. Pair new devices from a pilgrim's profile."
        />

        <SearchFilter
            :filters="filters"
            placeholder="Search by pilgrim or device ID"
        />

        <AdminTable :empty="devices.data.length === 0">
            <template #head>
                <tr>
                    <th>Pilgrim</th>
                    <th>Device ID</th>
                    <th>Platform</th>
                    <th>Last seen</th>
                    <th>Token</th>
                    <th />
                </tr>
            </template>
            <tr v-for="device in devices.data" :key="device.id">
                <td>
                    <Link
                        v-if="device.pilgrim"
                        :href="PilgrimController.show(device.pilgrim.id)"
                        class="font-medium hover:underline"
                    >
                        {{ device.pilgrim.name }}
                    </Link>
                </td>
                <td class="font-mono text-xs">{{ device.identifier }}</td>
                <td>{{ device.platform ?? '—' }}</td>
                <td>{{ formatDateTime(device.last_seen_at) }}</td>
                <td>
                    <Badge
                        :variant="device.tokens_count ? 'default' : 'secondary'"
                        >{{ device.tokens_count ? 'Active' : 'Revoked' }}</Badge
                    >
                </td>
                <td class="text-right">
                    <DeleteButton
                        :action="DeviceController.destroy.form(device.id)"
                        label="Unpair"
                        description="The device's token stops working immediately."
                    />
                </td>
            </tr>
        </AdminTable>

        <Pagination :page="devices" />
    </div>
</template>
