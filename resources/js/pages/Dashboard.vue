<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import PilgrimController from '@/actions/App/Http/Controllers/Admin/PilgrimController';
import PageHeader from '@/components/admin/PageHeader.vue';
import { formatDateTime } from '@/lib/format';
import { dashboard } from '@/routes';

defineOptions({
    layout: { breadcrumbs: [{ title: 'Dashboard', href: dashboard() }] },
});

defineProps<{
    stats: { label: string; value: number }[];
    upcoming: {
        id: number;
        title: string;
        starts_at: string;
        place: { name: string } | null;
    }[];
    quiet: {
        id: number;
        identifier: string;
        last_seen_at: string | null;
        pilgrim: { id: number; name: string } | null;
    }[];
}>();
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <PageHeader
            title="Dashboard"
            description="Today's picture of your pilgrims."
        />

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div
                v-for="stat in stats"
                :key="stat.label"
                class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
            >
                <p class="text-sm text-muted-foreground">{{ stat.label }}</p>
                <p class="mt-1 text-3xl font-semibold tabular-nums">
                    {{ stat.value }}
                </p>
            </div>
        </div>

        <div
            v-if="upcoming.length || quiet.length"
            class="grid gap-4 lg:grid-cols-2"
        >
            <section
                class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
            >
                <h2 class="mb-3 font-medium">Upcoming schedules</h2>
                <p
                    v-if="!upcoming.length"
                    class="text-sm text-muted-foreground"
                >
                    Nothing scheduled.
                </p>
                <ul class="divide-y text-sm">
                    <li
                        v-for="item in upcoming"
                        :key="item.id"
                        class="flex justify-between gap-4 py-2"
                    >
                        <span>
                            {{ item.title }}
                            <span
                                v-if="item.place"
                                class="text-muted-foreground"
                                >· {{ item.place.name }}</span
                            >
                        </span>
                        <span class="shrink-0 text-muted-foreground">{{
                            formatDateTime(item.starts_at)
                        }}</span>
                    </li>
                </ul>
            </section>

            <section
                class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
            >
                <h2 class="mb-1 font-medium">Devices not heard from</h2>
                <p class="mb-3 text-xs text-muted-foreground">
                    No location in the last 30 minutes.
                </p>
                <p v-if="!quiet.length" class="text-sm text-muted-foreground">
                    Every device has checked in.
                </p>
                <ul class="divide-y text-sm">
                    <li
                        v-for="device in quiet"
                        :key="device.id"
                        class="flex justify-between gap-4 py-2"
                    >
                        <Link
                            v-if="device.pilgrim"
                            :href="PilgrimController.show(device.pilgrim.id)"
                            class="hover:underline"
                            >{{ device.pilgrim.name }}</Link
                        >
                        <span class="shrink-0 text-muted-foreground">{{
                            device.last_seen_at
                                ? formatDateTime(device.last_seen_at)
                                : 'Never'
                        }}</span>
                    </li>
                </ul>
            </section>
        </div>
    </div>
</template>
