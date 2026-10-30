<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import FlightGroupController from '@/actions/App/Http/Controllers/Admin/FlightGroupController';
import FormField from '@/components/admin/FormField.vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { toDateTimeLocal } from '@/lib/format';

const props = defineProps<{
    flightGroup: {
        id: number;
        code: string;
        airline: string | null;
        flight_number: string | null;
        departs_at: string;
        returns_at: string | null;
    } | null;
}>();

const title = props.flightGroup
    ? `Edit trip ${props.flightGroup.code}`
    : 'New trip';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Trips', href: FlightGroupController.index() },
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
                flightGroup
                    ? FlightGroupController.update.form(flightGroup.id)
                    : FlightGroupController.store.form()
            "
            class="space-y-5"
            v-slot="{ errors, processing }"
        >
            <FormField
                label="Trip code"
                for="code"
                :error="errors.code"
                hint="Unique within your agency, e.g. HAJJ-2027-A."
            >
                <Input
                    id="code"
                    name="code"
                    :default-value="flightGroup?.code"
                    required
                />
            </FormField>
            <div class="grid gap-5 sm:grid-cols-2">
                <FormField
                    label="Airline"
                    for="airline"
                    :error="errors.airline"
                >
                    <Input
                        id="airline"
                        name="airline"
                        :default-value="flightGroup?.airline ?? ''"
                    />
                </FormField>
                <FormField
                    label="Flight number"
                    for="flight_number"
                    :error="errors.flight_number"
                >
                    <Input
                        id="flight_number"
                        name="flight_number"
                        :default-value="flightGroup?.flight_number ?? ''"
                    />
                </FormField>
                <FormField
                    label="Departs"
                    for="departs_at"
                    :error="errors.departs_at"
                >
                    <Input
                        id="departs_at"
                        name="departs_at"
                        type="datetime-local"
                        :default-value="
                            toDateTimeLocal(flightGroup?.departs_at)
                        "
                        required
                    />
                </FormField>
                <FormField
                    label="Returns"
                    for="returns_at"
                    :error="errors.returns_at"
                >
                    <Input
                        id="returns_at"
                        name="returns_at"
                        type="datetime-local"
                        :default-value="
                            toDateTimeLocal(flightGroup?.returns_at)
                        "
                    />
                </FormField>
            </div>
            <div class="flex gap-2">
                <Button :disabled="processing">Save</Button>
                <Button variant="ghost" as-child>
                    <Link :href="FlightGroupController.index()">Cancel</Link>
                </Button>
            </div>
        </Form>
    </div>
</template>
