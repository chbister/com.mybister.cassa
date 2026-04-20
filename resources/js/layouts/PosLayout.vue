<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { login } from '@/routes';
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
                <Link
                    :href="login()"
                    class="text-sm text-gray-500 hover:underline"
                >
                    Admin Login
                </Link>
            </div>
        </footer>
    </div>
</template>
