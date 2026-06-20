<template>
    <div class="space-y-6">
        <PosHeader
            cashierName="Staff 1"
            :transactions="transactionCount"
            :revenue="dailyRevenue"
        />

        <div class="grid grid-cols-12 gap-6">
            <div class="col-span-8 space-y-6">
                <SearchBar v-model="search" />

                <ProductGrid
                    ref="productGridRef"
                    :search="search"
                    @add-to-cart="addToCart"
                />
            </div>

            <div class="col-span-4">
                <CartPanel
                    :items="cartItems"
                    :processing="processing"
                    @increase="increaseQty"
                    @decrease="decreaseQty"
                    @remove="removeItem"
                    @checkout="checkout"
                />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";

import PosHeader from "@/components/pos/PosHeader.vue";
import SearchBar from "@/components/pos/SearchBar.vue";
import ProductGrid from "@/components/pos/ProductGrid.vue";
import CartPanel from "@/components/pos/CartPanel.vue";
import { checkoutPOS } from "@/services/posService";
import Swal from "sweetalert2";
import { getPosSummary } from "@/services/posService";

const processing = ref(false);

const transactionCount = ref(0);
const dailyRevenue = ref(0);
const productGridRef = ref(null);

const search = ref("");

const cartItems = ref([]);

const addToCart = (product) => {
    if (product.stock <= 0) {
        Swal.fire({
            icon: "warning",
            title: "Stock Habis",
        });

        return;
    }

    const existing = cartItems.value.find((item) => item.id === product.id);

    if (existing && existing.qty >= product.stock) {
        Swal.fire({
            icon: "warning",
            title: "Stock tidak cukup",
        });

        return;
    }

    if (existing) {
        existing.qty++;
        return;
    }

    cartItems.value.push({
        ...product,
        qty: 1,
    });
};

const decreaseQty = (id) => {
    const item = cartItems.value.find((i) => i.id === id);

    if (!item) return;

    if (item.qty > 1) {
        item.qty--;
    }
};

const checkout = async (payload) => {
    if (processing.value) return;

    processing.value = true;
    try {
        const rupiah = (value) =>
            new Intl.NumberFormat("id-ID").format(value || 0);
        const requestData = {
            payment_method: payload.payment_method,
            cash_received: payload.paid_amount,

            items: cartItems.value.map((item) => ({
                medicine_id: item.id,
                quantity: item.qty,
            })),
        };

        const response = await checkoutPOS(requestData);

        await Swal.fire({
            icon: "success",
            title: "Transaksi Berhasil",
            html: `
      No: ${response.data.reference_number}
      <br><br>
      Total: Rp${rupiah(response.data.grand_total)}
      <br><br>
      Kembalian: Rp${rupiah(response.data.change_amount)}
   `,
        });

        cartItems.value = [];

        await loadSummary();

        await productGridRef.value.fetchProducts();
    } catch (error) {
        console.error(error);

        alert(error.response?.data?.message || "Checkout gagal");
    } finally {
        processing.value = false;
    }
};

const loadSummary = async () => {
    try {
        const response = await getPosSummary();

        transactionCount.value = response.data.transactions_today;

        dailyRevenue.value = parseFloat(response.data.revenue_today || 0);
    } catch (error) {
        console.error(error);
    }
};

const increaseQty = (id) => {
    const item = cartItems.value.find((i) => i.id === id);

    if (!item) return;

    if (item.qty >= item.stock) {
        alert("Stock tidak cukup");

        return;
    }

    item.qty++;
};

const removeItem = (id) => {
    cartItems.value = cartItems.value.filter((item) => item.id !== id);
};

const grandTotal = computed(() =>
    cartItems.value.reduce((total, item) => total + item.price * item.qty, 0),
);

onMounted(() => {
    loadSummary();
});
</script>
