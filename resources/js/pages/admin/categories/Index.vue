<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Category {
    id: number;
    name: string;
}

const props = defineProps<{
    categories: Category[];
}>();

const form = useForm({
    name: '',
});

const editingCategory = ref<Category | null>(null);
const editForm = useForm({
    name: '',
});

const submit = () => {
    form.post(route('admin.categories.store'), {
        onSuccess: () => form.reset(),
    });
};

const startEdit = (category: Category) => {
    editingCategory.value = category;
    editForm.name = category.name;
};

const update = () => {
    if (!editingCategory.value) return;
    editForm.put(route('admin.categories.update', editingCategory.value.id), {
        onSuccess: () => editingCategory.value = null,
    });
};

const destroy = (id: number) => {
    if (confirm('Sicher?')) {
        useForm({}).delete(route('admin.categories.destroy', id));
    }
};
</script>

<template>
    <Head title="Kategorien verwalten" />

    <div class="min-h-screen bg-gray-100 p-8">
        <div class="max-w-4xl mx-auto">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-bold">Kategorien verwalten</h1>
                <div class="flex gap-4">
                    <Link :href="route('admin.products.index')" class="bg-white px-4 py-2 rounded shadow hover:bg-gray-50">Produkte</Link>
                    <Link :href="route('home')" class="bg-gray-200 px-4 py-2 rounded shadow hover:bg-gray-300">Zur Kasse</Link>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-sm mb-8">
                <h2 class="text-xl font-bold mb-4">Neue Kategorie</h2>
                <form @submit.prevent="submit" class="flex gap-4">
                    <input
                        v-model="form.name"
                        type="text"
                        placeholder="Name der Kategorie"
                        class="flex-1 rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                        required
                    />
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="bg-blue-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-blue-700 disabled:opacity-50"
                    >
                        Hinzufügen
                    </button>
                </form>
            </div>

            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="px-6 py-4 font-bold">Name</th>
                            <th class="px-6 py-4 font-bold text-right">Aktionen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="category in categories" :key="category.id">
                            <td class="px-6 py-4">
                                <template v-if="editingCategory?.id === category.id">
                                    <input
                                        v-model="editForm.name"
                                        type="text"
                                        class="w-full rounded border-gray-300"
                                        @keyup.enter="update"
                                    />
                                </template>
                                <template v-else>
                                    {{ category.name }}
                                </template>
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <template v-if="editingCategory?.id === category.id">
                                    <button @click="update" class="text-green-600 font-bold hover:underline">Speichern</button>
                                    <button @click="editingCategory = null" class="text-gray-500 hover:underline">Abbrechen</button>
                                </template>
                                <template v-else>
                                    <button @click="startEdit(category)" class="text-blue-600 hover:underline">Bearbeiten</button>
                                    <button @click="destroy(category.id)" class="text-red-600 hover:underline">Löschen</button>
                                </template>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
