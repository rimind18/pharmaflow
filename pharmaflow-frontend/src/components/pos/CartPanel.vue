<template>
    <div class="bg-white rounded-3xl p-6 shadow-sm border">
        <p class="text-red-500">Total Item: {{ items.length }}</p>
        <h2 class="font-bold text-2xl mb-6">Keranjang</h2>

        <div v-if="items.length" class="space-y-4">
            <div v-for="item in items" :key="item.id" class="border-b pb-4">
                <h3 class="font-semibold">
                    {{ item.name }}
                </h3>

                <p class="text-sm">Rp{{ formatPrice(item.price) }}</p>

                <div class="flex items-center gap-2 mt-3">
                    <button
                        @click="$emit('decrease', item.id)"
                        class="px-3 py-1 border rounded"
                    >
                        -
                    </button>

                    <span>
                        {{ item.qty }}
                    </span>

                    <button
                        @click="$emit('increase', item.id)"
                        class="px-3 py-1 border rounded"
                    >
                        +
                    </button>

                    <button
                        @click="$emit('remove', item.id)"
                        class="ml-auto text-red-500"
                    >
                        Hapus
                    </button>
                </div>
            </div>

            <div class="border-t pt-4 mt-4">
                <div class="flex justify-between font-bold text-lg">
                    <span>Total</span>

                    <span> Rp{{ formatPrice(total) }} </span>
                </div>

                <div class="mt-6 border-t pt-4">
                    <label class="font-medium"> Metode Pembayaran </label>

                    <select
                        v-model="paymentMethod"
                        class="w-full mt-2 border rounded-xl p-3"
                    >
                        <option value="cash">Cash</option>

                        <option value="transfer">Transfer</option>

                        <option value="credit_card">Credit Card</option>
                    </select>
                </div>

                <div v-if="paymentMethod === 'cash'">
                    <div class="mt-4">
                        <label class="font-medium"> Uang Diterima </label>

                        <input
                            v-model.number="paidAmount"
                            type="number"
                            class="w-full mt-2 border rounded-xl p-3"
                        />
                    </div>

                    <div class="grid grid-cols-3 gap-2 mt-3">
                        <button
                            @click="paidAmount = total"
                            class="border rounded-xl py-2"
                        >
                            Pas
                        </button>

                        <button
                            @click="paidAmount = 50000"
                            class="border rounded-xl py-2"
                        >
                            50rb
                        </button>

                        <button
                            @click="paidAmount = 100000"
                            class="border rounded-xl py-2"
                        >
                            100rb
                        </button>
                    </div>

                    <div
                        class="flex justify-between mt-4 text-lg font-semibold"
                    >
                        <span>Kembalian</span>

                        <span
                            :class="
                                change >= 0 ? 'text-green-600' : 'text-red-500'
                            "
                        >
                            Rp{{ formatPrice(change) }}
                        </span>
                    </div>
                </div>

                <button
                    @click="handleCheckout"
                    :disabled="
                        (paymentMethod === 'cash' && paidAmount < total) ||
                        !items.length ||
                        props.processing
                    "
                    class="w-full mt-6 bg-emerald-500 text-white py-4 rounded-2xl font-bold disabled:bg-slate-300"
                >
                    {{ props.processing ? "Memproses..." : "Bayar Sekarang" }}
                </button>
            </div>
        </div>

        <div v-else class="text-center text-gray-400">Keranjang kosong</div>
    </div>
</template>

<script setup>
import { ref, computed } from "vue";

const props = defineProps({
    items: {
        type: Array,
        default: () => [],
    },

    processing: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["increase", "decrease", "remove", "checkout"]);

const paymentMethod = ref("cash");
const paidAmount = ref(0);

const total = computed(() => {
    return props.items.reduce((sum, item) => sum + item.price * item.qty, 0);
});

const change = computed(() => {
    return paidAmount.value - total.value;
});

const formatPrice = (value) => {
    return new Intl.NumberFormat("id-ID").format(value || 0);
};

const handleCheckout = () => {
    emit("checkout", {
        payment_method: paymentMethod.value,
        paid_amount: paidAmount.value,
        total: total.value,
        change: change.value,
    });
};
</script>
