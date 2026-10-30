<script setup lang="ts">
defineProps<{
    name: string;
    options: { id: number; label: string }[];
    selected: number[];
}>();
</script>

<template>
    <div class="max-h-48 space-y-1 overflow-y-auto rounded-md border p-2">
        <p
            v-if="options.length === 0"
            class="px-1 text-sm text-muted-foreground"
        >
            None available.
        </p>
        <label
            v-for="option in options"
            :key="option.id"
            class="flex items-center gap-2 rounded px-1 py-0.5 text-sm hover:bg-muted/50"
        >
            <input
                type="checkbox"
                :name="`${name}[]`"
                :value="option.id"
                :checked="selected.includes(option.id)"
                class="size-4 rounded border-input accent-primary"
            />
            {{ option.label }}
        </label>
    </div>
</template>
