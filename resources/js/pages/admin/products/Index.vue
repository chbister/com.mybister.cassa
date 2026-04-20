<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Category {
    id: number;
    name: string;
}

interface Product {
    id: number;
    name: string;
    amount_info: string;
    price: string | number;
    category_id: number;
    category?: Category;
}

defineProps<{
    products: Product[];
    categories: Category[];
}>();

const form = useForm({
    name: '',
    amount_info: '',
    price: '',
    category_id: '',
});

const importForm = useForm({
    file: null as File | null,
});

const editingProduct = ref<Product | null>(null);
const editForm = useForm({
    name: '',
    amount_info: '',
    price: '',
    category_id: '',
});

const submit = () => {
    form.post(route('admin.products.store'), {
        onSuccess: () => form.reset(),
    });
};

const startEdit = (product: Product) => {
    editingProduct.value = product;
    editForm.name = product.name;
    editForm.amount_info = product.amount_info;
    editForm.price = String(product.price);
    editForm.category_id = String(product.category_id);
};

const update = () => {
    if (!editingProduct.value) {
        return;
    }

    editForm.put(route('admin.products.update', editingProduct.value.id), {
        onSuccess: () => editingProduct.value = null,
    });
};

const destroy = (id: number) => {
    if (confirm('Sicher?')) {
        useForm({}).delete(route('admin.products.destroy', id));
    }
};

const handleImport = (e: Event) => {
    const target = e.target as HTMLInputElement;

    if (target.files && target.files.length > 0) {
        importForm.file = target.files[0];
        importForm.post(route('admin.products.import'), {
            onSuccess: () => {
                target.value = '';
                importForm.reset();
            },
        });
    }
};

const formatPrice = (price: string | number) => {
    return new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' }).format(Number(price));
};
</script>

<template>
    <Head title="Produkte verwalten" />

    <div class="min-h-screen bg-gray-100 p-8">
        <div class="max-w-6xl mx-auto">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-bold">Produkte verwalten</h1>
                <div class="flex gap-4">
                    <Link :href="route('admin.categories.index')" class="bg-white px-4 py-2 rounded shadow hover:bg-gray-50">Kategorien</Link>
                    <Link :href="route('home')" class="bg-gray-200 px-4 py-2 rounded shadow hover:bg-gray-300">Zur Kasse</Link>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Add Form -->
                <div class="bg-white p-6 rounded-xl shadow-sm h-fit">
                    <h2 class="text-xl font-bold mb-4">Neues Produkt</h2>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Kategorie</label>
                            <select v-model="form.category_id" class="w-full rounded-lg border-gray-300" required>
                                <option value="" disabled>Wählen...</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Name</label>
                            <input v-model="form.name" type="text" class="w-full rounded-lg border-gray-300" required />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Mengen-Info (z.B. 0,5l)</label>
                            <input v-model="form.amount_info" type="text" class="w-full rounded-lg border-gray-300" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Preis (€)</label>
                            <input v-model="form.price" type="number" step="0.01" class="w-full rounded-lg border-gray-300" required />
                        </div>
                        <button type="submit" :disabled="form.processing" class="w-full bg-blue-600 text-white py-2 rounded-lg font-bold hover:bg-blue-700 disabled:opacity-50">
                            Hinzufügen
                        </button>
                    </form>

                    <div class="mt-8 pt-8 border-t">
                        <h3 class="font-bold mb-4">Import / Export</h3>
                        <div class="flex flex-col gap-4">
                            <a :href="route('admin.products.export')" class="text-center bg-gray-100 py-2 rounded hover:bg-gray-200">CSV Export</a>
                            <div class="relative">
                                <input type="file" @change="handleImport" class="hidden" id="csv-import" accept=".csv" />
                                <label for="csv-import" class="block text-center bg-gray-100 py-2 rounded hover:bg-gray-200 cursor-pointer">
                                    CSV Import
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Products Table -->
                <div class="lg:col-span-2 bg-white rounded-xl shadow-sm overflow-hidden">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-4 py-4 font-bold">Kategorie</th>
                                <th class="px-4 py-4 font-bold">Name</th>
                                <th class="px-4 py-4 font-bold text-right">Preis</th>
                                <th class="px-4 py-4 font-bold text-right">Aktionen</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y text-sm">
                            <tr v-for="product in products" :key="product.id">
                                <template v-if="editingProduct?.id === product.id">
                                    <td class="px-4 py-2">
                                        <select v-model="editForm.category_id" class="w-full rounded border-gray-300 py-1">
                                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                        </select>
                                    </td>
                                    <td class="px-4 py-2">
                                        <input v-model="editForm.name" type="text" class="w-full rounded border-gray-300 py-1" />
                                        <input v-model="editForm.amount_info" type="text" placeholder="Menge" class="w-full rounded border-gray-300 py-1 mt-1 text-xs" />
                                    </td>
                                    <td class="px-4 py-2 text-right">
                                        <input v-model="editForm.price" type="number" step="0.01" class="w-20 rounded border-gray-300 py-1 text-right" />
                                    </td>
                                    <td class="px-4 py-2 text-right space-x-2">
                                        <button @click="update" class="text-green-600 font-bold hover:underline">Speichern</button>
                                        <button @click="editingProduct = null" class="text-gray-500 hover:underline">Abbr.</button>
                                    </td>
                                </template>
                                <template v-else>
                                    <td class="px-4 py-4">{{ product.category?.name }}</td>
                                    <td class="px-4 py-4">
                                        <div class="font-bold">{{ product.name }}</div>
                                        <div class="text-xs text-gray-500">{{ product.amount_info }}</div>
                                    </td>
                                    <td class="px-4 py-4 text-right font-bold">{{ formatPrice(product.price) }}</td>
                                    <td class="px-4 py-4 text-right space-x-3">
                                        <button @click="startEdit(product)" class="text-blue-600 hover:underline">Edit</button>
                                        <button @click="destroy(product.id)" class="text-red-600 hover:underline">Delete</button>
                                    </td>
                                </template>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>
