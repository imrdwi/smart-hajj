<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { ChevronDown, LogOut } from '@lucide/vue';
import BrandMark from '@/components/BrandMark.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { Toaster } from '@/components/ui/sonner';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { useNavigation } from '@/composables/useNavigation';
import { cn } from '@/lib/utils';
import { dashboard, logout } from '@/routes';
import type { BreadcrumbItem } from '@/types';

withDefaults(defineProps<{ breadcrumbs?: BreadcrumbItem[] }>(), {
    breadcrumbs: () => [],
});

const page = usePage();
const { sections, current } = useNavigation();
const { isCurrentOrParentUrl } = useCurrentUrl();
const year = new Date().getFullYear();
</script>

<template>
    <div class="flex min-h-svh flex-col bg-white">
        <div class="mx-auto w-full max-w-[1170px] flex-1 px-4">
            <header class="flex items-start gap-4 pt-7">
                <Link :href="dashboard()" class="hidden shrink-0 sm:block">
                    <BrandMark class="size-[113px]" />
                    <span class="sr-only">{{ page.props.name }}</span>
                </Link>
                <div class="min-w-0 flex-1 pt-2">
                    <p class="text-2xl leading-tight tracking-wide uppercase">
                        {{ page.props.name }}
                    </p>
                    <p class="mt-1 mb-2.5 text-sm">
                        Pilgrim monitoring for Hajj and Umrah agencies
                    </p>

                    <nav
                        class="flex flex-wrap items-center rounded-sm border border-[#5fc79d] bg-portal px-2"
                        aria-label="Main"
                    >
                        <Link
                            v-for="section in sections"
                            :key="section.title"
                            :href="section.items[0].href"
                            :class="
                                cn(
                                    'px-3.5 py-3.5 text-[15px] text-portal-ink transition-colors hover:text-white focus-visible:text-white',
                                    current?.title === section.title &&
                                        'text-white',
                                )
                            "
                            :aria-current="
                                current?.title === section.title
                                    ? 'page'
                                    : undefined
                            "
                        >
                            {{ section.title }}
                        </Link>
                        <Link
                            :href="logout()"
                            as="button"
                            class="ml-auto inline-flex items-center gap-1.5 px-3.5 py-3.5 text-[15px] text-portal-ink hover:text-white"
                            data-test="logout-button"
                            @click="router.flushAll()"
                        >
                            <LogOut class="size-4" /> Logout
                        </Link>
                    </nav>
                </div>
            </header>

            <div
                class="mt-4 flex min-h-[450px] flex-col gap-4 bg-[#f7f7f7] px-4 pt-4 pb-6 md:flex-row md:gap-6"
            >
                <aside v-if="current" class="w-full shrink-0 md:w-60">
                    <p
                        class="flex items-center justify-between border-b border-[#e7e7e7] px-4 py-2.5 font-semibold text-[#337ab7]"
                    >
                        {{ current.title }}
                        <ChevronDown class="size-3.5" />
                    </p>
                    <ul>
                        <li
                            v-for="item in current.items"
                            :key="item.title"
                            class="border-b border-[#e7e7e7]"
                        >
                            <Link
                                :href="item.href"
                                :class="
                                    cn(
                                        'flex items-center gap-2 py-2.5 pr-4 pl-9 text-[#337ab7] hover:bg-[#eee] hover:text-[#23527c]',
                                        isCurrentOrParentUrl(item.href) &&
                                            'bg-[#eee] font-semibold',
                                    )
                                "
                            >
                                <component :is="item.icon" class="size-4" />
                                {{ item.title }}
                            </Link>
                        </li>
                    </ul>
                </aside>

                <main class="min-w-0 flex-1">
                    <Breadcrumbs
                        v-if="breadcrumbs.length > 1"
                        :breadcrumbs="breadcrumbs"
                        class="px-4 pt-3"
                    />
                    <slot />
                </main>
            </div>
        </div>

        <footer
            class="mt-8 border-t border-[#5fc79d] bg-portal p-4 text-center text-sm font-bold text-white"
        >
            Copyright &copy; {{ page.props.name }} | {{ year }}
        </footer>
        <Toaster />
    </div>
</template>
