<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import admin from '@/routes/filament/admin';
import { certificateDownload } from '@/routes/qz';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-[#FDFDFC] text-[#1b1b18] lg:justify-center lg:p-8 dark:bg-[#0a0a0a]"
    >
        <Head title="SVM" />

        <slot />

        <footer class="mb-6 w-full max-w-4xl">
            <div class="flex items-center justify-between">
                <h1 class="text-3xl font-bold">SVM</h1>
                <a
                    :href="certificateDownload().url"
                    class="text-sm text-gray-500 hover:underline"
                    ><svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        class="lucide lucide-shield-check-icon lucide-shield-check"
                    >
                        <path
                            d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"
                        />
                        <path d="m9 12 2 2 4-4" /></svg
                ></a>
                <a
                    :href="admin.resources.products.index().url"
                    class="text-sm text-gray-500 hover:underline"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        class="lucide lucide-settings-icon lucide-settings"
                    >
                        <path
                            d="M9.671 4.136a2.34 2.34 0 0 1 4.659 0 2.34 2.34 0 0 0 3.319 1.915 2.34 2.34 0 0 1 2.33 4.033 2.34 2.34 0 0 0 0 3.831 2.34 2.34 0 0 1-2.33 4.033 2.34 2.34 0 0 0-3.319 1.915 2.34 2.34 0 0 1-4.659 0 2.34 2.34 0 0 0-3.32-1.915 2.34 2.34 0 0 1-2.33-4.033 2.34 2.34 0 0 0 0-3.831A2.34 2.34 0 0 1 6.35 6.051a2.34 2.34 0 0 0 3.319-1.915"
                        />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                </a>
            </div>
        </footer>
    </div>
</template>
