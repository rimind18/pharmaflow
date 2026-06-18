<template>
    <div
        class="relative bg-white rounded-3xl overflow-hidden border border-slate-200 hover:shadow-2xl hover:-translate-y-1 transition-all duration-300"
    >
        <!-- BADGE STOCK -->

        <span
            v-if="medicine.total_stock > 0"
            class="absolute top-3 left-3 z-10 bg-green-500 text-white text-xs px-3 py-1 rounded-full"
        >
            Tersedia
        </span>

        <span
            v-else
            class="absolute top-3 left-3 z-10 bg-red-500 text-white text-xs px-3 py-1 rounded-full"
        >
            Habis
        </span>

        <!-- WISHLIST BUTTON -->

        <button
            @click="toggleWishlist"
            class="absolute top-3 right-3 z-10 w-10 h-10 rounded-full bg-white shadow-md hover:shadow-lg"
        >
            ❤️
        </button>

        <!-- PRODUCT IMAGE -->

        <div class="h-56 bg-slate-100 flex items-center justify-center">
            <img
                v-if="medicine.photo"
                :src="medicine.photo"
                class="w-full h-full object-cover"
            />

            <div v-else class="text-7xl">💊</div>
        </div>

        <!-- CONTENT -->

        <div class="p-5">
            <!-- CATEGORY -->

            <div
                class="inline-flex px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-semibold mb-3"
            >
                {{ medicine.category?.name || "Obat" }}
            </div>

            <!-- PRODUCT NAME -->

            <h3
                class="font-bold text-lg text-slate-800 line-clamp-2 min-h-[56px]"
            >
                {{ medicine.name }}
            </h3>

            <div class="flex items-center gap-2 mt-2">
                ⭐⭐⭐⭐⭐

                <span class="text-sm text-slate-500"> (4.9) </span>
            </div>

            <!-- PRICE -->

            <p class="text-emerald-600 text-2xl font-bold mt-3">
                Rp{{ formatPrice(medicine.selling_price) }}
            </p>

            <!-- STOCK INFO -->

            <p class="text-sm text-slate-500 mt-2">
                Stok:
                {{ medicine.total_stock || 0 }}
            </p>

            <!-- ACTION -->

            <div class="flex gap-2 mt-5">
                <router-link
                    :to="`/products/${medicine.id}`"
                    class="flex-1 text-center py-3 rounded-xl border border-emerald-600 text-emerald-600 font-semibold hover:bg-emerald-50"
                >
                    Detail
                </router-link>

                <button
                    @click="addToCart"
                    :disabled="medicine.total_stock <= 0"
                    class="flex-1 py-3 rounded-xl bg-emerald-600 text-white font-semibold hover:bg-emerald-700 disabled:bg-gray-300 disabled:cursor-not-allowed"
                >
                    + Keranjang
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useCartStore } from "@/stores/cart";
import { ElMessage } from "element-plus";

const props = defineProps({
    medicine: {
        type: Object,
        required: true,
    },
});

const cartStore = useCartStore();

const formatPrice = (price) => {
    return new Intl.NumberFormat("id-ID").format(price);
};

const addToCart = () => {
    if ((props.medicine.total_stock || 0) <= 0) {
        ElMessage.warning("Stok produk habis");

        return;
    }

    cartStore.addToCart(props.medicine);

    ElMessage.success(`${props.medicine.name} ditambahkan ke keranjang`);
};

const toggleWishlist = () => {
    ElMessage.info(
        "Wishlist akan segera hadir"
    );
};
</script>
