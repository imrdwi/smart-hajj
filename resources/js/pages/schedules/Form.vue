<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import ScheduleController from '@/actions/App/Http/Controllers/Admin/ScheduleController';
import AssignmentFields from '@/components/admin/AssignmentFields.vue';
import FormField from '@/components/admin/FormField.vue';
import NativeSelect from '@/components/admin/NativeSelect.vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import Textarea from '@/components/admin/Textarea.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { toDateTimeLocal } from '@/lib/format';

const props = defineProps<{
    schedule: {
        id: number;
        title: string;
        description: string | null;
        place_id: number | null;
        starts_at: string;
        ends_at: string | null;
    } | null;
    assigned: {
        flight_group_ids: number[];
        group_ids: number[];
        pilgrim_ids: number[];
    };
    places: { id: number; name: string }[];
    flightGroups: { id: number; code: string }[];
    groups: {
        id: number;
        name: string;
        flight_group: { code: string } | null;
    }[];
    pilgrims: { id: number; name: string }[];
}>();

const title = props.schedule ? `Edit ${props.schedule.title}` : 'New schedule';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Schedules', href: ScheduleController.index() },
            { title: 'Form', href: '#' },
        ],
    },
});
</script>

<template>
    <Head :title="title" />

    <div class="flex max-w-4xl flex-1 flex-col gap-6 p-4">
        <PageHeader :title="title" />

        <Form
            v-bind="
                schedule
                    ? ScheduleController.update.form(schedule.id)
                    : ScheduleController.store.form()
            "
            class="space-y-5"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-5 sm:grid-cols-2">
                <FormField
                    label="Title"
                    for="title"
                    :error="errors.title"
                    class="sm:col-span-2"
                >
                    <Input
                        id="title"
                        name="title"
                        :default-value="schedule?.title"
                        required
                    />
                </FormField>
                <FormField
                    label="Starts"
                    for="starts_at"
                    :error="errors.starts_at"
                >
                    <Input
                        id="starts_at"
                        name="starts_at"
                        type="datetime-local"
                        :default-value="toDateTimeLocal(schedule?.starts_at)"
                        required
                    />
                </FormField>
                <FormField label="Ends" for="ends_at" :error="errors.ends_at">
                    <Input
                        id="ends_at"
                        name="ends_at"
                        type="datetime-local"
                        :default-value="toDateTimeLocal(schedule?.ends_at)"
                    />
                </FormField>
                <FormField
                    label="Holy site"
                    for="place_id"
                    :error="errors.place_id"
                    class="sm:col-span-2"
                >
                    <NativeSelect id="place_id" name="place_id">
                        <option value="">No specific place</option>
                        <option
                            v-for="place in places"
                            :key="place.id"
                            :value="place.id"
                            :selected="place.id === schedule?.place_id"
                        >
                            {{ place.name }}
                        </option>
                    </NativeSelect>
                </FormField>
                <FormField
                    label="Description"
                    for="description"
                    :error="errors.description"
                    class="sm:col-span-2"
                >
                    <Textarea
                        id="description"
                        name="description"
                        :value="schedule?.description ?? ''"
                    />
                </FormField>
            </div>

            <AssignmentFields
                :flight-groups="flightGroups"
                :groups="groups"
                :pilgrims="pilgrims"
                :assigned="assigned"
                :errors="errors"
            />

            <div class="flex gap-2">
                <Button :disabled="processing">Save</Button>
                <Button variant="ghost" as-child>
                    <Link :href="ScheduleController.index()">Cancel</Link>
                </Button>
            </div>
        </Form>
    </div>
</template>
