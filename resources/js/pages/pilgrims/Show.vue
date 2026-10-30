<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { Copy } from '@lucide/vue';
import { ref } from 'vue';
import DeviceController from '@/actions/App/Http/Controllers/Admin/DeviceController';
import PilgrimController from '@/actions/App/Http/Controllers/Admin/PilgrimController';
import DeleteButton from '@/components/admin/DeleteButton.vue';
import NativeSelect from '@/components/admin/NativeSelect.vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { formatDate, formatDateTime, humanize } from '@/lib/format';

type Device = {
    id: number;
    identifier: string;
    platform: string | null;
    last_seen_at: string | null;
};

const props = defineProps<{
    pilgrim: {
        id: number;
        name: string;
        gender: string;
        birth_date: string | null;
        phone: string | null;
        occupation: string | null;
        address: string | null;
        photo_url: string | null;
        devices: Device[];
        group: {
            name: string;
            leader: { name: string } | null;
            flight_group: {
                code: string;
                departs_at: string;
                returns_at: string | null;
            } | null;
        } | null;
    };
    schedules: {
        id: number;
        title: string;
        starts_at: string;
        ends_at: string | null;
        place: { name: string } | null;
    }[];
    meals: {
        id: number;
        type: string;
        menu: string | null;
        served_at: string;
    }[];
    lastLocation: {
        latitude: number;
        longitude: number;
        recorded_at: string;
    } | null;
    canManage: boolean;
    pairedToken: string | null;
}>();

const copied = ref(false);

async function copyToken() {
    if (props.pairedToken) {
        await navigator.clipboard.writeText(props.pairedToken);
        copied.value = true;
    }
}

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Pilgrims', href: PilgrimController.index() },
            { title: 'Profile', href: '#' },
        ],
    },
});
</script>

<template>
    <Head :title="pilgrim.name" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <PageHeader
            :title="pilgrim.name"
            :description="
                pilgrim.group
                    ? `${pilgrim.group.name} · ${pilgrim.group.flight_group?.code ?? ''}`
                    : undefined
            "
        >
            <template v-if="canManage">
                <Button variant="outline" as-child>
                    <Link :href="PilgrimController.edit(pilgrim.id)">Edit</Link>
                </Button>
                <DeleteButton
                    :action="PilgrimController.destroy.form(pilgrim.id)"
                    label="Archive"
                    :description="`Archive ${pilgrim.name}? Their paired devices are signed out.`"
                />
            </template>
        </PageHeader>

        <Alert v-if="pairedToken">
            <AlertTitle>Device token</AlertTitle>
            <AlertDescription class="space-y-2">
                <p>
                    Enter this token in the pilgrim app. It is shown only once.
                </p>
                <div class="flex items-center gap-2">
                    <code
                        class="rounded bg-muted px-2 py-1 font-mono text-xs break-all"
                        >{{ pairedToken }}</code
                    >
                    <Button size="sm" variant="outline" @click="copyToken">
                        <Copy class="size-4" /> {{ copied ? 'Copied' : 'Copy' }}
                    </Button>
                </div>
            </AlertDescription>
        </Alert>

        <div class="grid gap-4 lg:grid-cols-3">
            <section
                class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
            >
                <img
                    v-if="pilgrim.photo_url"
                    :src="pilgrim.photo_url"
                    :alt="`Photo of ${pilgrim.name}`"
                    class="mb-4 aspect-square w-32 rounded-lg object-cover"
                />
                <dl class="grid grid-cols-[auto_1fr] gap-x-4 gap-y-2 text-sm">
                    <dt class="text-muted-foreground">Gender</dt>
                    <dd>{{ humanize(pilgrim.gender) }}</dd>
                    <dt class="text-muted-foreground">Born</dt>
                    <dd>{{ formatDate(pilgrim.birth_date) }}</dd>
                    <dt class="text-muted-foreground">Phone</dt>
                    <dd>{{ pilgrim.phone ?? '—' }}</dd>
                    <dt class="text-muted-foreground">Occupation</dt>
                    <dd>{{ pilgrim.occupation ?? '—' }}</dd>
                    <dt class="text-muted-foreground">Address</dt>
                    <dd class="whitespace-pre-line">
                        {{ pilgrim.address ?? '—' }}
                    </dd>
                    <dt class="text-muted-foreground">Leader</dt>
                    <dd>{{ pilgrim.group?.leader?.name ?? '—' }}</dd>
                    <dt class="text-muted-foreground">Last seen</dt>
                    <dd>
                        <template v-if="lastLocation">
                            {{ formatDateTime(lastLocation.recorded_at) }}
                            <a
                                :href="`https://www.openstreetmap.org/?mlat=${lastLocation.latitude}&mlon=${lastLocation.longitude}#map=17/${lastLocation.latitude}/${lastLocation.longitude}`"
                                target="_blank"
                                rel="noopener"
                                class="block text-xs underline"
                                >{{ lastLocation.latitude.toFixed(5) }},
                                {{ lastLocation.longitude.toFixed(5) }}</a
                            >
                        </template>
                        <template v-else>No location yet</template>
                    </dd>
                </dl>
            </section>

            <section
                class="rounded-xl border border-sidebar-border/70 p-4 lg:col-span-2 dark:border-sidebar-border"
            >
                <h2 class="mb-3 font-medium">Devices</h2>
                <p
                    v-if="!pilgrim.devices.length"
                    class="mb-3 text-sm text-muted-foreground"
                >
                    No device paired.
                </p>
                <ul class="mb-4 divide-y text-sm">
                    <li
                        v-for="device in pilgrim.devices"
                        :key="device.id"
                        class="flex items-center justify-between gap-4 py-2"
                    >
                        <span>
                            <span class="font-mono text-xs">{{
                                device.identifier
                            }}</span>
                            <span class="text-muted-foreground">
                                · {{ device.platform ?? 'unknown' }} · last seen
                                {{ formatDateTime(device.last_seen_at) }}</span
                            >
                        </span>
                        <DeleteButton
                            v-if="canManage"
                            :action="DeviceController.destroy.form(device.id)"
                            label="Unpair"
                            description="The device's token stops working immediately."
                        />
                    </li>
                </ul>
                <Form
                    v-if="canManage"
                    v-bind="DeviceController.store.form(pilgrim.id)"
                    class="flex flex-wrap items-end gap-2"
                    v-slot="{ processing }"
                >
                    <NativeSelect
                        name="platform"
                        class="max-w-40"
                        aria-label="Platform"
                    >
                        <option value="android">Android</option>
                        <option value="ios">iOS</option>
                    </NativeSelect>
                    <Button :disabled="processing" variant="outline"
                        >Pair a device</Button
                    >
                </Form>
            </section>

            <section
                class="rounded-xl border border-sidebar-border/70 p-4 lg:col-span-2 dark:border-sidebar-border"
            >
                <h2 class="mb-1 font-medium">Itinerary</h2>
                <p class="mb-3 text-xs text-muted-foreground">
                    Includes schedules assigned to the pilgrim's group and trip.
                </p>
                <p
                    v-if="!schedules.length"
                    class="text-sm text-muted-foreground"
                >
                    Nothing scheduled.
                </p>
                <ul class="divide-y text-sm">
                    <li
                        v-for="schedule in schedules"
                        :key="schedule.id"
                        class="flex justify-between gap-4 py-2"
                    >
                        <span
                            >{{ schedule.title
                            }}<span
                                v-if="schedule.place"
                                class="text-muted-foreground"
                            >
                                · {{ schedule.place.name }}</span
                            ></span
                        >
                        <span class="shrink-0 text-muted-foreground">{{
                            formatDateTime(schedule.starts_at)
                        }}</span>
                    </li>
                </ul>
            </section>

            <section
                class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
            >
                <h2 class="mb-3 font-medium">Meals</h2>
                <p v-if="!meals.length" class="text-sm text-muted-foreground">
                    No meals assigned.
                </p>
                <ul class="divide-y text-sm">
                    <li v-for="meal in meals" :key="meal.id" class="py-2">
                        <span class="font-medium">{{
                            humanize(meal.type)
                        }}</span>
                        <span class="text-muted-foreground">
                            · {{ formatDateTime(meal.served_at) }}</span
                        >
                        <p v-if="meal.menu" class="text-muted-foreground">
                            {{ meal.menu }}
                        </p>
                    </li>
                </ul>
            </section>
        </div>
    </div>
</template>
