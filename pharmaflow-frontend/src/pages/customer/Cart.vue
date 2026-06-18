<template>
    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Header -->
        <div
            class="mb-8 rounded-3xl bg-gradient-to-r from-emerald-600 to-green-700 px-10 py-8"
        >
            <div class="max-w-6xl text-white mx-auto">
                <h1 class="text-4xl font-bold">Keranjang Belanja</h1>

                <p class="mt-3 text-emerald-100 text-lg">
                    Kelola produk yang akan Anda checkout
                </p>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-6 py-12">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                <!-- Cart Items (Left - 2 cols) -->
                <div class="lg:col-span-2">
                    <!-- Empty State -->
                    <div
                        v-if="cartStore.items.length === 0"
                        class="bg-white rounded-3xl shadow-md py-16 px-10 text-center min-h-[380px] flex flex-col justify-center"
                    >
                        <div
                            class="w-32 h-32 mx-auto mb-8 rounded-full bg-emerald-50 flex items-center justify-center"
                        >
                            <span class="text-6xl">🛒</span>
                        </div>

                        <h2 class="text-4xl font-bold mb-4">
                            Keranjang Masih Kosong
                        </h2>

                        <p class="text-slate-500 mb-8">
                            Temukan berbagai kebutuhan kesehatan terbaik di
                            PharmaFlow
                        </p>

                        <router-link
                            to="/products"
                            class="inline-flex items-center justify-center px-8 py-4 rounded-2xl bg-emerald-600 text-white font-bold"
                        >
                            Jelajahi Produk
                        </router-link>
                    </div>

                    <!-- Items List -->
                    <div v-else class="space-y-4">
                        <div
                            v-for="item in cartStore.items"
                            :key="item.id"
                            class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 flex items-center gap-6 hover:-translate-y-1 hover:shadow-xl transition-all duration-300"
                        >
                            <!-- Image -->
                            <div
                                class="w-20 h-20 rounded-2xl bg-emerald-50 flex items-center justify-center"
                            >
                                <img
                                    v-if="item.photo"
                                    :src="item.photo"
                                    class="w-full h-full object-cover rounded-2xl"
                                />

                                <span v-else class="text-4xl"> 💊 </span>
                            </div>

                            <!-- Item Info -->
                            <div class="flex-1">
                                <router-link
                                    :to="`/products/${item.id}`"
                                    class="text-lg font-bold text-gray-800 hover:text-blue-600 block mb-1"
                                >
                                    {{ item.name }}
                                </router-link>
                                <p class="text-green-600 font-bold">
                                    Rp{{
                                        formatPrice(
                                            item.selling_price || item.price,
                                        )
                                    }}
                                </p>
                            </div>

                            <!-- Quantity Control -->
                            <div
                                class="flex items-center rounded-xl border border-slate-200 overflow-hidden"
                            >
                                <button
                                    @click="decreaseQty(item)"
                                    class="w-10 h-10 hover:bg-emerald-50 flex items-center justify-center"
                                >
                                    −
                                </button>

                                <input
                                    :value="item.quantity"
                                    @change="updateQuantity($event, item.id)"
                                    type="number"
                                    min="1"
                                    class="w-12 text-center border-0 focus:outline-none font-bold"
                                />

                                <button
                                    @click="increaseQty(item)"
                                    class="w-10 h-10 hover:bg-emerald-50 flex items-center justify-center"
                                >
                                    +
                                </button>
                            </div>

                            <!-- Subtotal -->
                            <div class="text-right">
                                <p class="text-lg font-bold text-green-600">
                                    Rp{{
                                        formatPrice(
                                            (item.selling_price || item.price) *
                                                item.quantity,
                                        )
                                    }}
                                </p>
                                <button
                                    @click="removeItem(item.id)"
                                    class="text-red-600 hover:text-red-800 text-sm font-bold mt-1"
                                >
                                    🗑️ Hapus
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary (Right - 1 col) -->
                <div class="col-span-1">
                    <div class="max-w-sm mx-auto mb-8">
                        <div class="flex items-center justify-between">
                            <div class="text-center">
                                <div
                                    class="w-10 h-10 rounded-full bg-emerald-600 text-white flex items-center justify-center"
                                >
                                    1
                                </div>
                                <p class="text-xs mt-2">Keranjang</p>
                            </div>

                            <div class="flex-1 h-1 bg-slate-200 mx-2"></div>

                            <div class="text-center opacity-50">
                                <div
                                    class="w-10 h-10 rounded-full bg-slate-300 flex items-center justify-center"
                                >
                                    2
                                </div>
                                <p class="text-xs mt-2">Checkout</p>
                            </div>

                            <div class="flex-1 h-1 bg-slate-200 mx-2"></div>

                            <div class="text-center opacity-50">
                                <div
                                    class="w-10 h-10 rounded-full bg-slate-300 flex items-center justify-center"
                                >
                                    3
                                </div>
                                <p class="text-xs mt-2">Bayar</p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-white rounded-3xl shadow-lg border border-slate-100 p-8 sticky top-28"
                    >
                        <!-- Order Summary -->
                        <h2 class="text-2xl font-bold mb-4">📋 Ringkasan</h2>

                        <div class="space-y-4 pb-5 border-b border-slate-100">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Subtotal</span>
                                <span class="font-bold"
                                    >Rp{{
                                        formatPrice(cartStore.subtotal)
                                    }}</span
                                >
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Ongkir</span>
                                <span class="font-bold"
                                    >Rp{{
                                        formatPrice(cartStore.shippingCost)
                                    }}</span
                                >
                            </div>
                            <div
                                v-if="cartStore.discountAmount > 0"
                                class="flex justify-between text-red-600"
                            >
                                <span>Diskon</span>
                                <span class="font-bold"
                                    >-Rp{{
                                        formatPrice(cartStore.discountAmount)
                                    }}</span
                                >
                            </div>
                        </div>

                        <div
                            class="flex justify-between items-center text-2xl font-bold bg-gradient-to-r from-emerald-50 to-green-100 border border-emerald-200 p-5 rounded-2xl"
                        >
                            <span>Total</span>
                            <span class="text-green-600"
                                >Rp{{ formatPrice(cartStore.total) }}</span
                            >
                        </div>

                        <div
                            class="flex justify-between items-center bg-emerald-50 p-4 rounded-3xl"
                        >
                            <span class="font-medium"> Total Produk </span>

                            <span class="font-bold text-emerald-600">
                                {{ totalItems }}
                            </span>
                        </div>

                        <div
                            class="inline-flex px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 font-semibold mb-4"
                        >
                            {{ cartStore.items.length }} Produk
                        </div>

                        <!-- Action Buttons -->
                        <div
                            v-if="cartStore.items.length > 0"
                            class="space-y-2"
                        >
                            <button
                                @click="proceedCheckout"
                                class="w-full h-14 rounded-2xl font-bold text-white bg-gradient-to-r from-emerald-600 to-green-700 hover:from-emerald-700 hover:to-green-800 shadow-lg transition-all duration-300"
                            >
                                🛒 Checkout Sekarang
                            </button>

                            <router-link
                                to="/products"
                                class="block text-center py-4 rounded-2xl bg-slate-100 hover:bg-slate-200 font-semibold transition"
                            >
                                🛍️ Lanjut Belanja
                            </router-link>

                            <button
                                :disabled="cartStore.items.length === 0"
                                @click="clearCart"
                                class="w-full px-4 py-2 bg-red-100 text-red-800 rounded-lg hover:bg-red-200 transition font-bold text-sm"
                            >
                                🗑️ Kosongkan Keranjang
                            </button>
                        </div>

                        <!-- Login CTA -->
                        <div
                            v-if="!authStore.isAuthenticated"
                            class="mt-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded"
                        >
                            <p class="text-sm text-gray-700 mb-2">
                                ✨ Daftarkan akun untuk menyimpan riwayat
                                pesanan dan alamat
                            </p>
                            <div class="flex gap-2">
                                <router-link
                                    to="/login"
                                    class="flex-1 text-center px-2 py-2 bg-emerald-600 text-white rounded text-sm hover:bg-blue-700 transition font-bold"
                                >
                                    Masuk
                                </router-link>
                                <router-link
                                    to="/register"
                                    class="flex-1 text-center px-2 py-2 bg-emerald-100 text-emerald-800 rounded text-sm hover:bg-blue-200 transition font-bold"
                                >
                                    Daftar
                                </router-link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted } from "vue";
import { useCartStore } from "@/stores/cart";
import { useAuthStore } from "@/stores/auth";
import { ElMessage, ElMessageBox } from "element-plus";
import { useRouter } from "vue-router";

const cartStore = useCartStore();
const authStore = useAuthStore();
const router = useRouter();

/*
|--------------------------------------------------------------------------
| FORMAT RUPIAH
|--------------------------------------------------------------------------
*/
const formatPrice = (price) => {
    return new Intl.NumberFormat("id-ID").format(Number(price || 0));
};

/*
|--------------------------------------------------------------------------
| TOTAL ITEM
|--------------------------------------------------------------------------
*/
const totalItems = computed(() => {
    return cartStore.items.reduce((sum, item) => sum + item.quantity, 0);
});

/*
|--------------------------------------------------------------------------
| UPDATE QTY
|--------------------------------------------------------------------------
*/
const updateQuantity = (event, id) => {
    const qty = Number(event.target.value);

    if (qty < 1) {
        cartStore.updateQuantity(id, 1);
        return;
    }

    cartStore.updateQuantity(id, qty);
};

/*
|--------------------------------------------------------------------------
| PLUS
|--------------------------------------------------------------------------
*/
const increaseQty = (item) => {
    cartStore.updateQuantity(item.id, item.quantity + 1);
};

/*
|--------------------------------------------------------------------------
| MINUS
|--------------------------------------------------------------------------
*/
const decreaseQty = (item) => {
    if (item.quantity <= 1) return;

    cartStore.updateQuantity(item.id, item.quantity - 1);
};

/*
|--------------------------------------------------------------------------
| REMOVE ITEM
|--------------------------------------------------------------------------
*/
const removeItem = async (id) => {
    try {
        await ElMessageBox.confirm(
            "Hapus produk dari keranjang?",
            "Konfirmasi",
            {
                confirmButtonText: "Hapus",
                cancelButtonText: "Batal",
                type: "warning",
            },
        );

        cartStore.removeFromCart(id);

        ElMessage.success("Produk berhasil dihapus");
    } catch {}
};

/*
|--------------------------------------------------------------------------
| CLEAR CART
|--------------------------------------------------------------------------
*/
const clearCart = async () => {
    try {
        await ElMessageBox.confirm(
            "Kosongkan seluruh keranjang?",
            "Konfirmasi",
            {
                confirmButtonText: "Ya",
                cancelButtonText: "Batal",
                type: "warning",
            },
        );

        cartStore.clearCart();

        ElMessage.success("Keranjang berhasil dikosongkan");
    } catch {}
};

/*
|--------------------------------------------------------------------------
| CHECKOUT
|--------------------------------------------------------------------------
*/
const proceedCheckout = () => {
    if (cartStore.items.length === 0) {
        ElMessage.warning("Keranjang masih kosong");
        return;
    }

    router.push("/checkout");
};

/*
|--------------------------------------------------------------------------
| LOAD CART
|--------------------------------------------------------------------------
*/
onMounted(() => {
    cartStore.loadFromLocalStorage();

    console.log("Cart loaded:", cartStore.items);
});
</script>
