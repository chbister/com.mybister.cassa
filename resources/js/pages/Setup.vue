<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import PosLayout from '@/layouts/PosLayout.vue';
import setup from '@/routes/setup';

defineOptions({
    layout: PosLayout,
});

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(setup.store.url(), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <Head title="Initial Setup" />

    <div class="flex min-h-screen items-center justify-center bg-gray-100 p-6">
        <div class="w-full max-w-md overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl">
            <div class="bg-blue-600 p-6 text-white">
                <h1 class="text-2xl font-bold italic tracking-tight">Cassa <span class="font-normal opacity-75">Setup</span></h1>
                <p class="mt-1 text-sm text-blue-100">Create the initial administrator account to get started.</p>
            </div>

            <form @submit.prevent="submit" class="space-y-6 p-8">
                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-700">Full Name</label>
                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        required
                        autofocus
                        autocomplete="name"
                        class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                        placeholder="John Doe"
                    />
                    <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</div>
                </div>

                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700">Email Address</label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        autocomplete="username"
                        class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                        placeholder="admin@example.com"
                    />
                    <div v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700">Password</label>
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        required
                        autocomplete="new-password"
                        class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                        placeholder="••••••••"
                    />
                    <div v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</div>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-gray-700">Confirm Password</label>
                    <input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        required
                        autocomplete="new-password"
                        class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                        placeholder="••••••••"
                    />
                    <div v-if="form.errors.password_confirmation" class="mt-1 text-sm text-red-600">{{ form.errors.password_confirmation }}</div>
                </div>

                <div class="pt-2">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="flex w-full justify-center rounded-xl bg-blue-600 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-blue-200 transition-all hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50"
                    >
                        <span v-if="form.processing">Setting up...</span>
                        <span v-else>Complete Setup</span>
                    </button>
                </div>
            </form>

            <div class="border-t bg-gray-50 p-6 text-center">
                <p class="text-xs text-gray-500 italic">This page will be disabled after the first user is created.</p>
            </div>
        </div>
    </div>
</template>
