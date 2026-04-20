<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import PosLayout from '@/layouts/PosLayout.vue';

defineOptions({
    layout: PosLayout,
});

interface Product {
    id: number;
    name: string;
    amount_info: string;
    price: string | number;
    category_id: number;
}

interface Category {
    id: number;
    name: string;
    products: Product[];
}

defineProps<{
    categories: Category[];
}>();

const orderItems = ref<{ product: Product; quantity: number }[]>([]);

const addToOrder = (product: Product) => {
    const existing = orderItems.value.find(
        (item) => item.product.id === product.id,
    );

    if (existing) {
        existing.quantity++;
    } else {
        orderItems.value.push({ product, quantity: 1 });
    }
};

const removeFromOrder = (productId: number) => {
    const index = orderItems.value.findIndex(
        (item) => item.product.id === productId,
    );

    if (index !== -1) {
        if (orderItems.value[index].quantity > 1) {
            orderItems.value[index].quantity--;
        } else {
            orderItems.value.splice(index, 1);
        }
    }
};

const totalPrice = computed(() => {
    return orderItems.value.reduce((total, item) => {
        return total + Number(item.product.price) * item.quantity;
    }, 0);
});

const formatPrice = (price: string | number) => {
    return new Intl.NumberFormat('de-DE', {
        style: 'currency',
        currency: 'EUR',
    }).format(Number(price));
};

const printOrder = () => {
    window.print();
    resetOrder();
};

const resetOrder = () => {
    orderItems.value = [];
};
</script>

<template>
    <Head title="Kasse" />

    <div class="min-h-screen bg-gray-100 p-4 md:p-8">
        <div class="mx-auto flex max-w-7xl flex-col gap-8 lg:flex-row">
            <!-- Left Side: Product Selection -->
            <div class="flex-1">
                <div class="mb-6 flex items-center justify-between">
                    <h1 class="text-3xl font-bold text-gray-900">
                        Taschenrechner
                    </h1>
                </div>

                <div
                    v-for="category in categories"
                    :key="category.id"
                    class="mb-8"
                >
                    <h2
                        class="mb-4 border-b pb-2 text-xl font-semibold text-gray-700"
                    >
                        {{ category.name }}
                    </h2>
                    <div
                        class="grid grid-cols-2 gap-4 sm:grid-cols-3 xl:grid-cols-4"
                    >
                        <button
                            v-for="product in category.products"
                            :key="product.id"
                            @click="addToOrder(product)"
                            class="flex h-32 flex-col justify-between rounded-xl border border-transparent bg-white p-4 text-left shadow-sm transition-all hover:border-blue-500 hover:shadow-md active:scale-95"
                        >
                            <div>
                                <div class="text-lg leading-tight font-bold">
                                    {{ product.name }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ product.amount_info }}
                                </div>
                            </div>
                            <div class="text-xl font-bold text-blue-600">
                                {{ formatPrice(product.price) }}
                            </div>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right Side: Current Order -->
            <div
                class="flex h-[calc(100vh-4rem)] w-full flex-col lg:sticky lg:top-8 lg:w-96"
            >
                <div
                    class="flex h-full flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-lg"
                >
                    <div class="bg-blue-600 p-4 text-white">
                        <h2 class="text-xl font-bold">Aktuelle Bestellung</h2>
                    </div>

                    <div class="flex-1 space-y-4 overflow-y-auto p-4">
                        <div
                            v-if="orderItems.length === 0"
                            class="flex h-full items-center justify-center text-gray-400 italic"
                        >
                            Keine Artikel ausgewählt
                        </div>

                        <div
                            v-for="item in orderItems"
                            :key="item.product.id"
                            class="flex items-center justify-between rounded-lg bg-gray-50 p-3"
                        >
                            <div class="flex-1">
                                <div class="font-bold">
                                    {{ item.product.name }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ item.quantity }} x
                                    {{ formatPrice(item.product.price) }}
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="text-lg font-bold">
                                    {{
                                        formatPrice(
                                            Number(item.product.price) *
                                                item.quantity,
                                        )
                                    }}
                                </div>
                                <button
                                    @click="removeFromOrder(item.product.id)"
                                    class="rounded-full p-2 text-red-600 transition-colors hover:bg-red-50 hover:text-red-700"
                                >
                                    <svg
                                        id="Layer_1"
                                        xmlns="http://www.w3.org/2000/svg"
                                        xmlns:xlink="http://www.w3.org/1999/xlink"
                                        class="h-6 w-6"
                                        viewBox="0 0 36 36"
                                        fill="none"
                                        stroke="currentColor"
                                    >
                                        <path
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-miterlimit="10"
                                            d="M23,27H11c-1.1,0-2-0.9-2-2V8h16v17
	C25,26.1,24.1,27,23,27z"
                                        />
                                        <line
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-miterlimit="10"
                                            x1="27"
                                            y1="8"
                                            x2="7"
                                            y2="8"
                                        />
                                        <path
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-miterlimit="10"
                                            d="M14,8V6c0-0.6,0.4-1,1-1h4c0.6,0,1,0.4,1,1v2"
                                        />
                                        <line
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-miterlimit="10"
                                            x1="17"
                                            y1="23"
                                            x2="17"
                                            y2="12"
                                        />
                                        <line
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-miterlimit="10"
                                            x1="21"
                                            y1="23"
                                            x2="21"
                                            y2="12"
                                        />
                                        <line
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-miterlimit="10"
                                            x1="13"
                                            y1="23"
                                            x2="13"
                                            y2="12"
                                        />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="border-t bg-gray-50 p-4">
                        <div class="mb-4 flex items-center justify-between">
                            <span class="font-medium text-gray-600"
                                >Gesamtbetrag:</span
                            >
                            <span class="text-3xl font-black text-blue-700">{{
                                formatPrice(totalPrice)
                            }}</span>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <button
                                @click="resetOrder"
                                :disabled="orderItems.length === 0"
                                class="w-full rounded-xl bg-gray-200 py-4 text-lg font-bold text-gray-700 transition-colors hover:bg-gray-300 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                Abbruch
                            </button>
                            <button
                                @click="printOrder"
                                :disabled="orderItems.length === 0"
                                class="w-full rounded-xl bg-blue-600 py-4 text-lg font-bold text-white shadow-lg shadow-blue-200 transition-colors hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                Drucken
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@media print {
    /* Hide everything else */
    body * {
        visibility: hidden;
    }
    /* Only show the current order */
    .lg\:w-96,
    .lg\:w-96 * {
        visibility: visible;
    }
    .lg\:w-96 {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        height: auto;
    }
    /* Hide the buttons while printing */
    .grid-cols-2,
    button {
        display: none !important;
    }
}
</style>
