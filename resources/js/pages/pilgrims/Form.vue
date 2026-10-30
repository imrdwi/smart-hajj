<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import PilgrimController from '@/actions/App/Http/Controllers/Admin/PilgrimController';
import FormField from '@/components/admin/FormField.vue';
import NativeSelect from '@/components/admin/NativeSelect.vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import Textarea from '@/components/admin/Textarea.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { humanize, toDateInput } from '@/lib/format';

type Pilgrim = {
    id: number;
    group_id: number;
    name: string;
    passport_number: string;
    gender: string;
    birth_date: string | null;
    phone: string | null;
    occupation: string | null;
    address: string | null;
};

const props = defineProps<{
    pilgrim: Pilgrim | null;
    groups: {
        id: number;
        name: string;
        flight_group: { code: string } | null;
    }[];
    genders: string[];
}>();

const title = props.pilgrim ? `Edit ${props.pilgrim.name}` : 'Register pilgrim';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Pilgrims', href: PilgrimController.index() },
            { title: 'Form', href: '#' },
        ],
    },
});
</script>

<template>
    <Head :title="title" />

    <div class="flex max-w-3xl flex-1 flex-col gap-6 p-4">
        <PageHeader :title="title" />

        <Form
            v-bind="
                pilgrim
                    ? PilgrimController.update.form(pilgrim.id)
                    : PilgrimController.store.form()
            "
            class="space-y-5"
            v-slot="{ errors, processing, progress }"
        >
            <div class="grid gap-5 sm:grid-cols-2">
                <FormField
                    label="Full name"
                    for="name"
                    :error="errors.name"
                    class="sm:col-span-2"
                >
                    <Input
                        id="name"
                        name="name"
                        :default-value="pilgrim?.name"
                        required
                    />
                </FormField>
                <FormField
                    label="Passport number"
                    for="passport_number"
                    :error="errors.passport_number"
                >
                    <Input
                        id="passport_number"
                        name="passport_number"
                        :default-value="pilgrim?.passport_number"
                        required
                    />
                </FormField>
                <FormField
                    label="Group"
                    for="group_id"
                    :error="errors.group_id"
                >
                    <NativeSelect id="group_id" name="group_id" required>
                        <option value="" disabled :selected="!pilgrim">
                            Choose a group
                        </option>
                        <option
                            v-for="group in groups"
                            :key="group.id"
                            :value="group.id"
                            :selected="group.id === pilgrim?.group_id"
                        >
                            {{ group.name
                            }}{{
                                group.flight_group
                                    ? ` (${group.flight_group.code})`
                                    : ''
                            }}
                        </option>
                    </NativeSelect>
                </FormField>
                <FormField label="Gender" for="gender" :error="errors.gender">
                    <NativeSelect id="gender" name="gender" required>
                        <option
                            v-for="gender in genders"
                            :key="gender"
                            :value="gender"
                            :selected="gender === pilgrim?.gender"
                        >
                            {{ humanize(gender) }}
                        </option>
                    </NativeSelect>
                </FormField>
                <FormField
                    label="Date of birth"
                    for="birth_date"
                    :error="errors.birth_date"
                >
                    <Input
                        id="birth_date"
                        name="birth_date"
                        type="date"
                        :default-value="toDateInput(pilgrim?.birth_date)"
                    />
                </FormField>
                <FormField label="Phone" for="phone" :error="errors.phone">
                    <Input
                        id="phone"
                        name="phone"
                        type="tel"
                        :default-value="pilgrim?.phone ?? ''"
                    />
                </FormField>
                <FormField
                    label="Occupation"
                    for="occupation"
                    :error="errors.occupation"
                >
                    <Input
                        id="occupation"
                        name="occupation"
                        :default-value="pilgrim?.occupation ?? ''"
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
                        :value="pilgrim?.address ?? ''"
                    />
                </FormField>
                <FormField
                    label="Photo"
                    for="photo"
                    :error="errors.photo"
                    hint="JPEG or PNG, up to 2 MB."
                    class="sm:col-span-2"
                >
                    <Input
                        id="photo"
                        name="photo"
                        type="file"
                        accept="image/*"
                    />
                    <progress
                        v-if="progress"
                        :value="progress.percentage"
                        max="100"
                        class="w-full"
                    />
                </FormField>
            </div>
            <div class="flex gap-2">
                <Button :disabled="processing">Save</Button>
                <Button variant="ghost" as-child>
                    <Link
                        :href="
                            pilgrim
                                ? PilgrimController.show(pilgrim.id)
                                : PilgrimController.index()
                        "
                        >Cancel</Link
                    >
                </Button>
            </div>
        </Form>
    </div>
</template>
