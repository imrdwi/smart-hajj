<script setup lang="ts">
import { computed } from 'vue';
import CheckboxList from '@/components/admin/CheckboxList.vue';
import FormField from '@/components/admin/FormField.vue';

const props = defineProps<{
    flightGroups: { id: number; code: string }[];
    groups: {
        id: number;
        name: string;
        flight_group: { code: string } | null;
    }[];
    pilgrims: { id: number; name: string }[];
    assigned: {
        flight_group_ids: number[];
        group_ids: number[];
        pilgrim_ids: number[];
    };
    errors: Record<string, string>;
}>();

const tripOptions = computed(() =>
    props.flightGroups.map((trip) => ({ id: trip.id, label: trip.code })),
);
const groupOptions = computed(() =>
    props.groups.map((group) => ({
        id: group.id,
        label: group.flight_group
            ? `${group.name} (${group.flight_group.code})`
            : group.name,
    })),
);
const pilgrimOptions = computed(() =>
    props.pilgrims.map((pilgrim) => ({ id: pilgrim.id, label: pilgrim.name })),
);

const firstError = (prefix: string) =>
    Object.entries(props.errors).find(
        ([key]) => key === prefix || key.startsWith(`${prefix}.`),
    )?.[1];
</script>

<template>
    <fieldset class="space-y-3 rounded-lg border p-4">
        <legend class="px-1 text-sm font-medium">Assign to</legend>
        <p class="text-xs text-muted-foreground">
            Pilgrims receive anything assigned to their trip or group, so assign
            at the widest level that fits.
        </p>
        <div class="grid gap-4 md:grid-cols-3">
            <FormField
                label="Whole trips"
                for="flight_group_ids"
                :error="firstError('flight_group_ids')"
            >
                <CheckboxList
                    name="flight_group_ids"
                    :options="tripOptions"
                    :selected="assigned.flight_group_ids"
                />
            </FormField>
            <FormField
                label="Groups"
                for="group_ids"
                :error="firstError('group_ids')"
            >
                <CheckboxList
                    name="group_ids"
                    :options="groupOptions"
                    :selected="assigned.group_ids"
                />
            </FormField>
            <FormField
                label="Individual pilgrims"
                for="pilgrim_ids"
                :error="firstError('pilgrim_ids')"
            >
                <CheckboxList
                    name="pilgrim_ids"
                    :options="pilgrimOptions"
                    :selected="assigned.pilgrim_ids"
                />
            </FormField>
        </div>
    </fieldset>
</template>
