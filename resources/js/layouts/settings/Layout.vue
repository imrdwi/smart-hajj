<script setup lang="ts">
import PageHeader from '@/components/admin/PageHeader.vue';
</script>

<template>
    <!-- The portal's side menu already lists Profile and Security. -->
    <div class="flex flex-col gap-6 p-4">
        <PageHeader
            title="Settings"
            description="Manage your profile and account settings"
        />
        <section class="max-w-xl space-y-12">
            <slot />
        </section>
    </div>
</template>
