<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import MealController from '@/actions/App/Http/Controllers/Admin/MealController';
import AssignmentFields from '@/components/admin/AssignmentFields.vue';
import FormField from '@/components/admin/FormField.vue';
import NativeSelect from '@/components/admin/NativeSelect.vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import Textarea from '@/components/admin/Textarea.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { humanize, toDateTimeLocal } from '@/lib/format';

const props = defineProps<{
    meal: {
        id: number;
        type: string;
        menu: string | null;
        served_at: string;
    } | null;
    assigned: {
        flight_group_ids: number[];
        group_ids: number[];
        pilgrim_ids: number[];
    };
    types: string[];
    flightGroups: { id: number; code: string }[];
    groups: {
        id: number;
        name: string;
        flight_group: { code: string } | null;
    }[];
    pilgrims: { id: number; name: string }[];
}>();

const title = props.meal ? 'Edit meal' : 'New meal';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Meals', href: MealController.index() },
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
                meal
                    ? MealController.update.form(meal.id)
                    : MealController.store.form()
            "
            class="space-y-5"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-5 sm:grid-cols-2">
                <FormField label="Type" for="type" :error="errors.type">
                    <NativeSelect id="type" name="type" required>
                        <option
                            v-for="type in types"
                            :key="type"
                            :value="type"
                            :selected="type === meal?.type"
                        >
                            {{ humanize(type) }}
                        </option>
                    </NativeSelect>
                </FormField>
                <FormField
                    label="Served at"
                    for="served_at"
                    :error="errors.served_at"
                >
                    <Input
                        id="served_at"
                        name="served_at"
                        type="datetime-local"
                        :default-value="toDateTimeLocal(meal?.served_at)"
                        required
                    />
                </FormField>
                <FormField
                    label="Menu"
                    for="menu"
                    :error="errors.menu"
                    class="sm:col-span-2"
                >
                    <Textarea id="menu" name="menu" :value="meal?.menu ?? ''" />
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
                    <Link :href="MealController.index()">Cancel</Link>
                </Button>
            </div>
        </Form>
    </div>
</template>
