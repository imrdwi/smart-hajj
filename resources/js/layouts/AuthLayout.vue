<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import BrandMark from '@/components/BrandMark.vue';
import { home } from '@/routes';

const { title = '', description = '' } = defineProps<{
    title?: string;
    description?: string;
}>();

const page = usePage();
const year = new Date().getFullYear();
</script>

<template>
    <!-- The Smart Umrah sign-in page: centred title, emblem, then the form. -->
    <div class="flex min-h-svh flex-col bg-white">
        <main
            class="mx-auto w-full max-w-lg flex-1 px-4 pt-20 pb-24 text-center"
        >
            <p class="text-4xl tracking-wide uppercase">
                {{ page.props.name }}
            </p>
            <p class="mt-2 text-lg">
                Pilgrim monitoring for Hajj and Umrah agencies
            </p>
            <Link :href="home()" class="mx-auto mt-6 block w-fit">
                <BrandMark class="size-[150px]" />
                <span class="sr-only">{{ page.props.name }}</span>
            </Link>
            <h1 class="mt-6 text-xl font-semibold">{{ title }}</h1>
            <p v-if="description" class="mt-1 text-sm text-muted-foreground">
                {{ description }}
            </p>
            <div class="mx-auto mt-6 max-w-sm text-left">
                <slot />
            </div>
        </main>
        <footer
            class="border-t border-[#5fc79d] bg-portal p-4 text-center text-sm font-bold text-white"
        >
            Copyright &copy; {{ page.props.name }} | {{ year }}
        </footer>
    </div>
</template>
