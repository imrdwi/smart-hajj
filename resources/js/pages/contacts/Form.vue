<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import ContactController from '@/actions/App/Http/Controllers/Admin/ContactController';
import FormField from '@/components/admin/FormField.vue';
import NativeSelect from '@/components/admin/NativeSelect.vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import Textarea from '@/components/admin/Textarea.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

const props = defineProps<{
    contact: {
        id: number;
        name: string;
        role: string | null;
        phone: string;
        address: string | null;
        latitude: number | null;
        longitude: number | null;
        group_id: number | null;
    } | null;
    groups: { id: number; name: string }[];
}>();

const title = props.contact ? `Edit ${props.contact.name}` : 'New contact';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Contacts', href: ContactController.index() },
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
                contact
                    ? ContactController.update.form(contact.id)
                    : ContactController.store.form()
            "
            class="space-y-5"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-5 sm:grid-cols-2">
                <FormField label="Name" for="name" :error="errors.name">
                    <Input
                        id="name"
                        name="name"
                        :default-value="contact?.name"
                        required
                    />
                </FormField>
                <FormField label="Role" for="role" :error="errors.role">
                    <Input
                        id="role"
                        name="role"
                        :default-value="contact?.role ?? ''"
                        placeholder="Doctor, hotel, tour leader…"
                    />
                </FormField>
                <FormField label="Phone" for="phone" :error="errors.phone">
                    <Input
                        id="phone"
                        name="phone"
                        type="tel"
                        :default-value="contact?.phone"
                        required
                    />
                </FormField>
                <FormField
                    label="Shown to"
                    for="group_id"
                    :error="errors.group_id"
                >
                    <NativeSelect id="group_id" name="group_id">
                        <option value="">Every pilgrim in the agency</option>
                        <option
                            v-for="group in groups"
                            :key="group.id"
                            :value="group.id"
                            :selected="group.id === contact?.group_id"
                        >
                            {{ group.name }} only
                        </option>
                    </NativeSelect>
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
                        :value="contact?.address ?? ''"
                    />
                </FormField>
                <FormField
                    label="Latitude"
                    for="latitude"
                    :error="errors.latitude"
                >
                    <Input
                        id="latitude"
                        class="font-mono"
                        name="latitude"
                        inputmode="decimal"
                        :default-value="contact?.latitude ?? ''"
                    />
                </FormField>
                <FormField
                    label="Longitude"
                    for="longitude"
                    :error="errors.longitude"
                >
                    <Input
                        id="longitude"
                        class="font-mono"
                        name="longitude"
                        inputmode="decimal"
                        :default-value="contact?.longitude ?? ''"
                    />
                </FormField>
            </div>
            <div class="flex gap-2">
                <Button :disabled="processing">Save</Button>
                <Button variant="ghost" as-child>
                    <Link :href="ContactController.index()">Cancel</Link>
                </Button>
            </div>
        </Form>
    </div>
</template>
