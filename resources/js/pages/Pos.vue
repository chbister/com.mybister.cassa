<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, computed, onMounted, watch } from 'vue';
import logoUrl from '@/assets/printlogo.png';
import {
    connectQz,
    printRaw,
    isConnected,
    printerStatus,
    checkPrinterReady,
} from '@/composables/qzTray';
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
    deposit_product_id?: number | null;
}

interface Category {
    id: number;
    name: string;
    is_deposit: boolean;
    products: Product[];
}

const props = defineProps<{
    categories: Category[];
}>();

const orderItems = ref<{ product: Product; quantity: number }[]>([]);

// State for auto-deposit per category
const autoDepositCategories = ref<Record<number, boolean>>({});

// Initialize auto-deposit as true for categories that have products with deposits
props.categories.forEach((category) => {
    if (category.products.some((p) => p.deposit_product_id)) {
        autoDepositCategories.value[category.id] = true;
    }
});

const isAutoDepositEnabled = (categoryId: number) => {
    return autoDepositCategories.value[categoryId] ?? false;
};

const toggleAutoDeposit = (categoryId: number) => {
    autoDepositCategories.value[categoryId] = !isAutoDepositEnabled(categoryId);
};

const addToOrder = (product: Product) => {
    const existing = orderItems.value.find(
        (item) => item.product.id === product.id,
    );

    if (existing) {
        existing.quantity++;
    } else {
        orderItems.value.push({ product, quantity: 1 });
    }

    // Add deposit if enabled and available
    if (
        isAutoDepositEnabled(product.category_id) &&
        product.deposit_product_id
    ) {
        // Find the deposit product in the loaded categories
        let depositProduct: Product | undefined;

        for (const cat of props.categories) {
            depositProduct = cat.products.find(
                (p) => p.id === product.deposit_product_id,
            );

            if (depositProduct) {
                break;
            }
        }

        if (depositProduct) {
            addToOrder(depositProduct);
        }
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

const totalExpectedDeposits = computed(() => {
    return orderItems.value.reduce((total, item) => {
        if (item.product.deposit_product_id) {
            return total + item.quantity;
        }

        return total;
    }, 0);
});

const totalActualDeposits = computed(() => {
    // Find all unique deposit product IDs from the categories
    const depositProductIds = new Set<number>();
    props.categories.forEach((cat) => {
        cat.products.forEach((p) => {
            if (p.deposit_product_id) {
                depositProductIds.add(p.deposit_product_id);
            }
        });
    });

    return orderItems.value.reduce((total, item) => {
        if (depositProductIds.has(item.product.id)) {
            return total + item.quantity;
        }

        return total;
    }, 0);
});

const isDepositMismatch = computed(() => {
    return (
        totalExpectedDeposits.value !== totalActualDeposits.value &&
        totalExpectedDeposits.value > 0
    );
});

const getDepositProduct = (product: Product) => {
    if (!product.deposit_product_id) {
        return null;
    }

    for (const cat of props.categories) {
        const deposit = cat.products.find(
            (p) => p.id === product.deposit_product_id,
        );

        if (deposit) {
            return deposit;
        }
    }

    return null;
};

const printerName = 'TM-T20III';
const isPrinting = ref(false);
const printError = ref<string | null>(null);

async function imageUrlToDataUrl(imageUrl) {
    const response = await fetch(imageUrl);
    const blob = await response.blob();

    return await new Promise((resolve, reject) => {
        const reader = new FileReader();

        reader.onloadend = () => resolve(reader.result);
        reader.onerror = reject;

        reader.readAsDataURL(blob);
    });
}
watch(printerStatus, (newStatus) => {
    console.log(
        'Printer changed:',
        newStatus.printerName,
        printerStatus.value.statusCode,
        printerStatus.value,
    );

    if (printerStatus.value.statusCode != 'idle') {
        isConnected.value = false;
    }
});

const getCategoryName = (categoryId: number) => {
    return props.categories.find((c) => c.id === categoryId)?.name || '';
};

async function printOrder(): Promise<void> {
    if (orderItems.value.length === 0 || isPrinting.value) {
        return;
    }

    isPrinting.value = true;
    printError.value = null;
    const logoDataUrl = await imageUrlToDataUrl(logoUrl);

    try {
        const data: string[] = ['\x1B\x40\x1B\x74\x13']; // Initialize
        const tickets: string[][] = [];

        orderItems.value.forEach((item) => {
            const categoryName = getCategoryName(item.product.category_id);
            const now = new Date();

            const dateTimeText = new Intl.DateTimeFormat('de-DE', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
            }).format(now);

            for (let i = 0; i < item.quantity; i++) {
                const isDepositCategory = props.categories.find(
                    (c) => c.id === item.product.category_id,
                )?.is_deposit;
                const infoText = isDepositCategory
                    ? formatPrice(item.product.price)
                    : item.product.amount_info;

                const ticket: string[] = [
                    '\x1B\x61\x01', // CENTER ALIGN
                    '\x1B\x4D\x00', // Font A
                    '\x1D\x21\x00',
                    '*** ABHOLMARKE ***\n',
                    '\x1D\x21\x00',
                    `*** ${categoryName} ***\n`,
                    '\x1D\x21\x00',
                    '\n',
                    '\x1B\x4D\x00', // Font A für Produkt etwas kräftiger
                    '\x1D\x21\x11', // Double height, double width
                    `${item.product.name} ${infoText}\n`,
                    '\x1D\x21\x00',
                    '\n',
                    '\x1B\x4D\x01', // Font B
                    '\x1D\x21\x00',
                    `${dateTimeText}\n`,
                    '\x1D\x21\x00', // Reset size
                    '\x1B\x61\x00', // Left align
                    '\x1B\x4D\x00', // Font A zurück
                ];
                tickets.push(ticket);
            }
        });

        tickets.forEach((ticket, index) => {
            data.push(...ticket);

            if (index < tickets.length - 1) {
                // Partial cut
                data.push('\x1D\x56\x42\x00');
            } else {
                // Full cut for the last one
                data.push('\x1D\x56\x41\x00');
            }
        });

        await printRaw(printerName, data);
        resetOrder();
    } catch (error) {
        console.error('Print failed:', error);
        printError.value =
            'Drucken fehlgeschlagen. Bitte QZ Tray und Drucker prüfen.';
    } finally {
        isPrinting.value = false;
    }
}

const resetOrder = () => {
    orderItems.value = [];
};

onMounted(async () => {
    try {
        await connectQz();
        await checkPrinterReady(printerName);
    } catch (error) {
        console.error('QZ connection failed:', error);
        printError.value = 'QZ Tray konnte nicht verbunden werden.';
    }
});
</script>

<template>
    <Head title="Kasse" />

    <div class="min-h-screen bg-gray-100 p-4 md:p-8">
        <div class="mx-auto flex max-w-7xl flex-col gap-8 lg:flex-row">
            <!-- Left Side: Product Selection -->
            <div class="flex-1">
                <!--div>
                    Drucker: {{ printerStatus.printerName }}
                    Status:
                    {{
                        printerStatus.statusText ||
                        printerStatus.message ||
                        'unbekannt'
                    }}
                </div-->
                <div
                    v-for="category in categories"
                    :key="category.id"
                    class="mb-8"
                >
                    <div
                        class="mb-4 flex items-center justify-between border-b pb-2"
                    >
                        <h2 class="text-xl font-semibold text-gray-700">
                            {{ category.name }}
                        </h2>
                        <button
                            v-if="
                                category.products.some(
                                    (p) => p.deposit_product_id,
                                )
                            "
                            @click="toggleAutoDeposit(category.id)"
                            :class="[
                                'flex items-center gap-2 rounded-lg px-3 py-1 text-sm font-medium transition-colors',
                                isAutoDepositEnabled(category.id)
                                    ? 'bg-blue-600 text-white'
                                    : 'bg-gray-200 text-gray-700 hover:bg-gray-300',
                            ]"
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
                                class="lucide lucide-coins-icon lucide-coins"
                            >
                                <path d="M13.744 17.736a6 6 0 1 1-7.48-7.48" />
                                <path d="M15 6h1v4" />
                                <path d="m6.134 14.768.866-.5 2 3.464" />
                                <circle cx="16" cy="8" r="6" />
                            </svg>
                            Pfand:
                            {{
                                isAutoDepositEnabled(category.id) ? 'An' : 'Aus'
                            }}
                        </button>
                    </div>
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
                            <div class="flex items-end justify-between">
                                <div class="text-xl font-bold text-blue-600">
                                    {{ formatPrice(product.price) }}
                                </div>
                                <div
                                    v-if="
                                        isAutoDepositEnabled(category.id) &&
                                        getDepositProduct(product)
                                    "
                                    class="mb-0.5 text-xs font-medium text-gray-400"
                                >
                                    +
                                    {{
                                        formatPrice(
                                            getDepositProduct(product)?.price ||
                                                0,
                                        )
                                    }}
                                    Pfand
                                </div>
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
                        <div class="flex items-center justify-between">
                            <h2 class="text-xl font-bold">
                                Aktuelle Bestellung
                            </h2>
                            <div
                                v-if="isDepositMismatch"
                                class="flex animate-pulse items-center gap-1.5 rounded-full bg-yellow-400 px-3 py-1 text-xs font-bold text-blue-900 shadow-sm"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-3.5 w-3.5"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                Pfand prüfen
                            </div>
                            <div
                                v-if="!isConnected"
                                class="flex items-center gap-1.5 rounded-full bg-red-500 px-3 py-1 text-xs font-bold text-white shadow-sm"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-3.5 w-3.5"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                Drucker nicht bereit
                            </div>
                        </div>
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
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="24"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="lucide lucide-trash2-icon lucide-trash-2"
                                    >
                                        <path d="M10 11v6" />
                                        <path d="M14 11v6" />
                                        <path
                                            d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"
                                        />
                                        <path d="M3 6h18" />
                                        <path
                                            d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"
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
                                :disabled="
                                    orderItems.length === 0 ||
                                    isPrinting ||
                                    !isConnected
                                "
                                :class="[
                                    'w-full rounded-xl py-4 text-lg font-bold text-white shadow-lg transition-colors disabled:cursor-not-allowed disabled:opacity-50',
                                    isConnected
                                        ? 'bg-blue-600 shadow-blue-200 hover:bg-blue-700'
                                        : 'bg-gray-400 shadow-none',
                                ]"
                            >
                                {{
                                    isPrinting
                                        ? 'Druck läuft...'
                                        : isConnected
                                          ? 'Drucken'
                                          : 'Drucker offline'
                                }}
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
