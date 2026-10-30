<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import PlaceController from '@/actions/App/Http/Controllers/Admin/PlaceController';
import PrayerController from '@/actions/App/Http/Controllers/Admin/PrayerController';
import DeleteButton from '@/components/admin/DeleteButton.vue';
import FormField from '@/components/admin/FormField.vue';
import NativeSelect from '@/components/admin/NativeSelect.vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import Textarea from '@/components/admin/Textarea.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { humanize } from '@/lib/format';

type Prayer = {
    id: number;
    title: string;
    arabic: string;
    transliteration: string | null;
    translation: string;
};

const props = defineProps<{
    place: {
        id: number;
        name: string;
        city: string;
        latitude: number;
        longitude: number;
        radius_meters: number;
        description: string | null;
        prayers: Prayer[];
    } | null;
    cities: string[];
}>();

const title = props.place ? `Edit ${props.place.name}` : 'New holy site';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Holy sites', href: PlaceController.index() },
            { title: 'Form', href: '#' },
        ],
    },
});
</script>

<template>
    <Head :title="title" />

    <div class="flex max-w-3xl flex-1 flex-col gap-8 p-4">
        <PageHeader :title="title" />

        <Form
            v-bind="
                place
                    ? PlaceController.update.form(place.id)
                    : PlaceController.store.form()
            "
            class="space-y-5"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-5 sm:grid-cols-2">
                <FormField label="Name" for="name" :error="errors.name">
                    <Input
                        id="name"
                        name="name"
                        :default-value="place?.name"
                        required
                    />
                </FormField>
                <FormField label="City" for="city" :error="errors.city">
                    <NativeSelect id="city" name="city" required>
                        <option
                            v-for="city in cities"
                            :key="city"
                            :value="city"
                            :selected="city === place?.city"
                        >
                            {{ humanize(city) }}
                        </option>
                    </NativeSelect>
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
                        :default-value="place?.latitude"
                        required
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
                        :default-value="place?.longitude"
                        required
                    />
                </FormField>
                <FormField
                    label="Geofence radius (m)"
                    for="radius_meters"
                    :error="errors.radius_meters"
                    hint="Pilgrims inside this distance of the centre count as arrived."
                >
                    <Input
                        id="radius_meters"
                        name="radius_meters"
                        type="number"
                        min="10"
                        max="10000"
                        :default-value="place?.radius_meters ?? 200"
                        required
                    />
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
                        :value="place?.description ?? ''"
                    />
                </FormField>
            </div>
            <div class="flex gap-2">
                <Button :disabled="processing">Save</Button>
                <Button variant="ghost" as-child>
                    <Link :href="PlaceController.index()">Back</Link>
                </Button>
            </div>
        </Form>

        <section v-if="place" class="space-y-4">
            <h2 class="text-lg font-semibold">Prayers</h2>
            <p class="text-sm text-muted-foreground">
                Shown to pilgrims when they arrive at {{ place.name }}.
            </p>

            <details
                v-for="prayer in place.prayers"
                :key="prayer.id"
                class="rounded-lg border p-4"
            >
                <summary class="cursor-pointer font-medium">
                    {{ prayer.title }}
                </summary>
                <Form
                    v-bind="PrayerController.update.form(prayer.id)"
                    class="mt-4 space-y-4"
                    v-slot="{ errors, processing }"
                >
                    <FormField
                        label="Title"
                        :for="`title-${prayer.id}`"
                        :error="errors.title"
                    >
                        <Input
                            :id="`title-${prayer.id}`"
                            name="title"
                            :default-value="prayer.title"
                            required
                        />
                    </FormField>
                    <FormField
                        label="Arabic"
                        :for="`arabic-${prayer.id}`"
                        :error="errors.arabic"
                    >
                        <Textarea
                            :id="`arabic-${prayer.id}`"
                            name="arabic"
                            dir="rtl"
                            lang="ar"
                            class="text-lg"
                            :value="prayer.arabic"
                            required
                        />
                    </FormField>
                    <FormField
                        label="Transliteration"
                        :for="`transliteration-${prayer.id}`"
                        :error="errors.transliteration"
                    >
                        <Textarea
                            :id="`transliteration-${prayer.id}`"
                            name="transliteration"
                            :value="prayer.transliteration ?? ''"
                        />
                    </FormField>
                    <FormField
                        label="Translation"
                        :for="`translation-${prayer.id}`"
                        :error="errors.translation"
                    >
                        <Textarea
                            :id="`translation-${prayer.id}`"
                            name="translation"
                            :value="prayer.translation"
                            required
                        />
                    </FormField>
                    <div class="flex gap-2">
                        <Button :disabled="processing" size="sm"
                            >Save prayer</Button
                        >
                        <DeleteButton
                            :action="PrayerController.destroy.form(prayer.id)"
                            label="Remove"
                            :description="`Remove ${prayer.title}?`"
                        />
                    </div>
                </Form>
            </details>

            <Form
                v-bind="PrayerController.store.form(place.id)"
                class="space-y-4 rounded-lg border border-dashed p-4"
                reset-on-success
                v-slot="{ errors, processing }"
            >
                <h3 class="font-medium">Add a prayer</h3>
                <FormField label="Title" for="new-title" :error="errors.title">
                    <Input id="new-title" name="title" required />
                </FormField>
                <FormField
                    label="Arabic"
                    for="new-arabic"
                    :error="errors.arabic"
                >
                    <Textarea
                        id="new-arabic"
                        name="arabic"
                        dir="rtl"
                        lang="ar"
                        class="text-lg"
                        required
                    />
                </FormField>
                <FormField
                    label="Transliteration"
                    for="new-transliteration"
                    :error="errors.transliteration"
                >
                    <Textarea id="new-transliteration" name="transliteration" />
                </FormField>
                <FormField
                    label="Translation"
                    for="new-translation"
                    :error="errors.translation"
                >
                    <Textarea
                        id="new-translation"
                        name="translation"
                        required
                    />
                </FormField>
                <Button :disabled="processing" size="sm">Add prayer</Button>
            </Form>
        </section>
    </div>
</template>
