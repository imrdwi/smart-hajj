<script setup lang="ts">
defineProps<{
    empty?: boolean;
    emptyText?: string;
}>();
</script>

<template>
    <div class="overflow-x-auto bg-white">
        <table
            class="w-full border border-border text-left text-sm [&_tbody_tr:hover]:bg-[#f5f5f5] [&_td]:border [&_td]:border-border [&_td]:px-2 [&_td]:py-2 [&_th]:border [&_th]:border-b-2 [&_th]:border-border [&_th]:px-2 [&_th]:py-2 [&_th]:font-semibold"
        >
            <thead>
                <slot name="head" />
            </thead>
            <tbody>
                <slot />
                <tr v-if="empty">
                    <td colspan="100" class="text-center text-muted-foreground">
                        {{ emptyText ?? 'Nothing here yet.' }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
