<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const props = defineProps<{
    open: boolean;
    totalPrice: number;
    canPrint?: boolean;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'paidAndPrint'): void;
}>();

const amountReceived = ref<number | string>('');

const formattedTotalPrice = computed(() => {
    return new Intl.NumberFormat('de-DE', {
        style: 'currency',
        currency: 'EUR',
    }).format(props.totalPrice);
});

const change = computed(() => {
    const received = Number(amountReceived.value);

    if (isNaN(received) || received < props.totalPrice) {
        return 0;
    }

    return received - props.totalPrice;
});

const formattedChange = computed(() => {
    return new Intl.NumberFormat('de-DE', {
        style: 'currency',
        currency: 'EUR',
    }).format(change.value);
});

const suggestedAmounts = computed(() => {
    const total = props.totalPrice;

    if (total <= 0) {
        return [];
    }

    const suggestions = new Set<number>();

    // Exact amount
    suggestions.add(total);

    // Rounded amount (ceil)
    suggestions.add(Math.ceil(total));

    // Common banknotes
    [10, 20, 50, 100].forEach(bill => {
        if (bill >= total) {
            suggestions.add(bill);
        }
    });

    return Array.from(suggestions).sort((a, b) => a - b);
});

const selectSuggestion = (amount: number) => {
    amountReceived.value = amount;
};

const handlePaidAndPrint = () => {
    emit('paidAndPrint');
};

const formatPriceShort = (price: number) => {
    return new Intl.NumberFormat('de-DE', {
        style: 'currency',
        currency: 'EUR',
    }).format(price);
};

// Reset amount received when opening
watch(() => props.open, (newVal) => {
    if (newVal) {
        amountReceived.value = '';
    }
});
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle>Rückgeldrechner</DialogTitle>
            </DialogHeader>

            <div class="grid gap-6 py-4">
                <div class="flex flex-col items-center justify-center space-y-2 rounded-lg bg-blue-50 p-6 text-blue-700">
                    <span class="text-sm font-medium uppercase tracking-wider">Gesamtbetrag</span>
                    <span class="text-4xl font-black">{{ formattedTotalPrice }}</span>
                </div>

                <div class="space-y-4">
                    <div class="grid gap-2">
                        <Label for="amount-received" class="text-base font-semibold">Erhaltener Betrag</Label>
                        <div class="relative">
                            <Input
                                id="amount-received"
                                v-model="amountReceived"
                                type="number"
                                step="0.01"
                                placeholder="0,00"
                                class="h-16 text-2xl font-bold pr-12"
                                autofocus
                            />
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 text-2xl font-bold text-gray-400">€</div>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <Button
                            v-for="amount in suggestedAmounts"
                            :key="amount"
                            variant="outline"
                            size="lg"
                            @click="selectSuggestion(amount)"
                            class="flex-1 min-w-[80px] h-12 text-lg font-bold"
                        >
                            {{ formatPriceShort(amount) }}
                        </Button>
                    </div>
                </div>

                <div
                    v-if="Number(amountReceived) >= totalPrice && Number(amountReceived) > 0"
                    class="flex flex-col items-center justify-center space-y-1 rounded-lg bg-green-50 p-6 text-green-700 transition-all"
                >
                    <span class="text-sm font-medium uppercase tracking-wider">Rückgeld</span>
                    <span class="text-4xl font-black">{{ formattedChange }}</span>
                </div>
            </div>

            <DialogFooter>
                <div class="grid w-full grid-cols-2 gap-4">
                    <Button
                        variant="ghost"
                        size="lg"
                        @click="emit('update:open', false)"
                        class="h-14 text-lg font-bold"
                    >
                        Abbrechen
                    </Button>
                    <Button
                        size="lg"
                        @click="handlePaidAndPrint"
                        :disabled="Number(amountReceived) < totalPrice || canPrint === false"
                        class="h-14 bg-green-600 text-lg font-bold hover:bg-green-700"
                    >
                        {{ canPrint === false ? 'Drucker offline' : 'Bezahlt & Drucken' }}
                    </Button>
                </div>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
