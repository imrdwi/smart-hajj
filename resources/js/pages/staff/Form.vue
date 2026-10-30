<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import StaffController from '@/actions/App/Http/Controllers/Admin/StaffController';
import FormField from '@/components/admin/FormField.vue';
import NativeSelect from '@/components/admin/NativeSelect.vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { humanize } from '@/lib/format';

const props = defineProps<{
    staffMember: {
        id: number;
        name: string;
        email: string;
        role: string;
        agency_id: number | null;
    } | null;
    roles: string[];
    agencies: { id: number; name: string }[];
}>();

const me = usePage().props.auth.user;
const role = ref(
    props.staffMember?.role ?? props.roles[props.roles.length - 1],
);
const title = props.staffMember
    ? `Edit ${props.staffMember.name}`
    : 'New account';
const isSelf = props.staffMember?.id === me.id;

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Staff accounts', href: StaffController.index() },
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
                staffMember
                    ? StaffController.update.form(staffMember.id)
                    : StaffController.store.form()
            "
            class="space-y-5"
            v-slot="{ errors, processing }"
        >
            <FormField label="Name" for="name" :error="errors.name">
                <Input
                    id="name"
                    name="name"
                    :default-value="staffMember?.name"
                    required
                />
            </FormField>
            <FormField label="Email" for="email" :error="errors.email">
                <Input
                    id="email"
                    name="email"
                    type="email"
                    :default-value="staffMember?.email"
                    required
                />
            </FormField>
            <FormField
                label="Role"
                for="role"
                :error="errors.role"
                :hint="isSelf ? 'You cannot change your own role.' : undefined"
            >
                <NativeSelect
                    id="role"
                    name="role"
                    :disabled="isSelf"
                    @change="role = ($event.target as HTMLSelectElement).value"
                >
                    <option
                        v-for="option in roles"
                        :key="option"
                        :value="option"
                        :selected="option === role"
                    >
                        {{ humanize(option) }}
                    </option>
                </NativeSelect>
                <input v-if="isSelf" type="hidden" name="role" :value="role" />
            </FormField>
            <FormField
                v-if="agencies.length && role !== 'system_admin'"
                label="Agency"
                for="agency_id"
                :error="errors.agency_id"
            >
                <NativeSelect id="agency_id" name="agency_id" required>
                    <option
                        value=""
                        disabled
                        :selected="!staffMember?.agency_id"
                    >
                        Choose an agency
                    </option>
                    <option
                        v-for="agency in agencies"
                        :key="agency.id"
                        :value="agency.id"
                        :selected="agency.id === staffMember?.agency_id"
                    >
                        {{ agency.name }}
                    </option>
                </NativeSelect>
            </FormField>
            <FormField
                label="Password"
                for="password"
                :error="errors.password"
                :hint="
                    staffMember
                        ? 'Leave blank to keep the current password.'
                        : undefined
                "
            >
                <PasswordInput
                    id="password"
                    name="password"
                    autocomplete="new-password"
                    :required="!staffMember"
                />
            </FormField>
            <div class="flex gap-2">
                <Button :disabled="processing">Save</Button>
                <Button variant="ghost" as-child>
                    <Link :href="StaffController.index()">Cancel</Link>
                </Button>
            </div>
        </Form>
    </div>
</template>
