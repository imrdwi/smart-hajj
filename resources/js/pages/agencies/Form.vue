<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import AgencyController from '@/actions/App/Http/Controllers/Admin/AgencyController';
import FormField from '@/components/admin/FormField.vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import Textarea from '@/components/admin/Textarea.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

const props = defineProps<{
    agency: {
        id: number;
        name: string;
        license_number: string | null;
        email: string | null;
        phone: string | null;
        address: string | null;
    } | null;
}>();

const title = props.agency ? `Edit ${props.agency.name}` : 'New agency';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Agencies', href: AgencyController.index() },
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
                agency
                    ? AgencyController.update.form(agency.id)
                    : AgencyController.store.form()
            "
            class="space-y-5"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-5 sm:grid-cols-2">
                <FormField
                    label="Name"
                    for="name"
                    :error="errors.name"
                    class="sm:col-span-2"
                >
                    <Input
                        id="name"
                        name="name"
                        :default-value="agency?.name"
                        required
                    />
                </FormField>
                <FormField
                    label="License number"
                    for="license_number"
                    :error="errors.license_number"
                >
                    <Input
                        id="license_number"
                        name="license_number"
                        :default-value="agency?.license_number ?? ''"
                    />
                </FormField>
                <FormField label="Phone" for="phone" :error="errors.phone">
                    <Input
                        id="phone"
                        name="phone"
                        type="tel"
                        :default-value="agency?.phone ?? ''"
                    />
                </FormField>
                <FormField
                    label="Email"
                    for="email"
                    :error="errors.email"
                    class="sm:col-span-2"
                >
                    <Input
                        id="email"
                        name="email"
                        type="email"
                        :default-value="agency?.email ?? ''"
                    />
                </FormField>
                <FormField
                    label="Address"
                    for="address"
                    :error="errors.address"
                    class="sm:col-span-2"
                >
                    <Textarea
                        id="address"
                        name="address"
                        :value="agency?.address ?? ''"
                    />
                </FormField>
            </div>
            <div class="flex gap-2">
                <Button :disabled="processing">Save</Button>
                <Button variant="ghost" as-child>
                    <Link :href="AgencyController.index()">Cancel</Link>
                </Button>
            </div>
        </Form>
    </div>
</template>
