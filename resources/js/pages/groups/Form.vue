<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import GroupController from '@/actions/App/Http/Controllers/Admin/GroupController';
import FormField from '@/components/admin/FormField.vue';
import NativeSelect from '@/components/admin/NativeSelect.vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

const props = defineProps<{
    group: {
        id: number;
        name: string;
        flight_group_id: number;
        leader_id: number | null;
    } | null;
    flightGroups: { id: number; code: string }[];
    leaders: { id: number; name: string }[];
}>();

const title = props.group ? `Edit ${props.group.name}` : 'New group';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Groups', href: GroupController.index() },
            { title: 'Form', href: '#' },
        ],
    },
});
</script>

<template>
    <Head :title="title" />

    <div class="flex max-w-2xl flex-1 flex-col gap-6 p-4">
        <PageHeader :title="title" />

        <Form
            v-bind="
                group
                    ? GroupController.update.form(group.id)
                    : GroupController.store.form()
            "
            class="space-y-5"
            v-slot="{ errors, processing }"
        >
            <FormField label="Name" for="name" :error="errors.name">
                <Input
                    id="name"
                    name="name"
                    :default-value="group?.name"
                    required
                />
            </FormField>
            <FormField
                label="Trip"
                for="flight_group_id"
                :error="errors.flight_group_id"
            >
                <NativeSelect
                    id="flight_group_id"
                    name="flight_group_id"
                    required
                >
                    <option value="" disabled :selected="!group">
                        Choose a trip
                    </option>
                    <option
                        v-for="trip in flightGroups"
                        :key="trip.id"
                        :value="trip.id"
                        :selected="trip.id === group?.flight_group_id"
                    >
                        {{ trip.code }}
                    </option>
                </NativeSelect>
            </FormField>
            <FormField
                label="Leader"
                for="leader_id"
                :error="errors.leader_id"
                hint="Group leader accounts are created under Staff accounts."
            >
                <NativeSelect id="leader_id" name="leader_id">
                    <option value="">No leader yet</option>
                    <option
                        v-for="leader in leaders"
                        :key="leader.id"
                        :value="leader.id"
                        :selected="leader.id === group?.leader_id"
                    >
                        {{ leader.name }}
                    </option>
                </NativeSelect>
            </FormField>
            <div class="flex gap-2">
                <Button :disabled="processing">Save</Button>
                <Button variant="ghost" as-child>
                    <Link :href="GroupController.index()">Cancel</Link>
                </Button>
            </div>
        </Form>
    </div>
</template>
