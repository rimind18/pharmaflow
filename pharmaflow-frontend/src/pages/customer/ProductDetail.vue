<
<template>
    <div class="max-w-7xl mx-auto px-6 py-10">
        <!-- Loading -->

        <div v-if="loading" class="text-center py-24">Memuat produk...</div>

        <div v-else-if="medicine" class="grid lg:grid-cols-2 gap-10">
            <!-- IMAGE -->

            <div
                class="relative bg-gradient-to-br from-emerald-50 to-white rounded-3xl shadow-lg p-10 min-h-[520px] flex items-center justify-center"
            >
                <span
                    class="absolute top-5 left-5 px-4 py-2 rounded-full text-white font-semibold"
                    :class="
                        medicine.total_stock > 0 ? 'bg-green-500' : 'bg-red-500'
                    "
                >
                    {{ medicine.total_stock > 0 ? "Tersedia" : "Habis" }}
                </span>

                <img
                    v-if="medicine.photo"
                    :src="medicine.photo"
                    class="max-h-[420px] object-contain"
                />

                <div v-else class="text-[150px]">💊</div>
            </div>

            <!-- INFO -->

            <div class="sticky top-24 h-fit">
                <div
                    class="inline-flex px-4 py-2 rounded-full bg-emerald-100 text-emerald-700 font-semibold mb-4"
                >
                    {{ medicine.category?.name }}
                </div>

                <h1 class="text-5xl font-bold text-slate-800">
                    {{ medicine.name }}
                </h1>

                <!-- Rating -->

                <div class="flex items-center gap-2 mt-5">
                    ⭐⭐⭐⭐⭐
                    <span class="text-slate-500"> (4.9) </span>
                </div>

                <!-- PRICE -->

                <div class="text-6xl font-bold text-emerald-600 mt-6">
                    Rp{{ formatPrice(medicine.selling_price) }}
                </div>

                <!-- PRODUCT INFO -->

                <div class="mt-8 space-y-3 text-slate-600">
                    <div>
                        Stok :
                        <b class="text-emerald-600">
                            {{ medicine.total_stock }}
                        </b>
                    </div>

                    <div>
                        Supplier :
                        <b>
                            {{ medicine.supplier?.name }}
                        </b>
                    </div>

                    <div>
                        Kode :
                        <b>
                            {{ medicine.code }}
                        </b>
                    </div>

                    <div>
                        Satuan :
                        <b>
                            {{ medicine.unit }}
                        </b>
                    </div>
                </div>

                <div class="mt-8 grid grid-cols-2 gap-4">
                    <div class="bg-green-50 p-4 rounded-2xl">
                        <div class="text-2xl">🚚</div>
                        <p class="font-semibold mt-2">Pengiriman Cepat</p>
                    </div>

                    <div class="bg-blue-50 p-4 rounded-2xl">
                        <div class="text-2xl">💯</div>
                        <p class="font-semibold mt-2">Obat Asli</p>
                    </div>

                    <div class="bg-yellow-50 p-4 rounded-2xl">
                        <div class="text-2xl">🔒</div>
                        <p class="font-semibold mt-2">Pembayaran Aman</p>
                    </div>

                    <div class="bg-purple-50 p-4 rounded-2xl">
                        <div class="text-2xl">📞</div>
                        <p class="font-semibold mt-2">Support 24 Jam</p>
                    </div>
                </div>

                <!-- QTY -->

                <div class="flex items-center gap-4 mt-8">
                    <button
                        @click="decreaseQty"
                        class="w-12 h-12 rounded-xl border"
                    >
                        -
                    </button>

                    <div class="text-xl font-bold">
                        {{ quantity }}
                    </div>

                    <button
                        @click="increaseQty"
                        class="w-12 h-12 rounded-xl border"
                    >
                        +
                    </button>
                </div>

                <!-- SUBTOTAL -->

                <div class="mt-6">
                    <p class="text-slate-500">Subtotal</p>

                    <h3 class="text-3xl font-bold text-emerald-600">
                        Rp{{ formatPrice(quantity * medicine.selling_price) }}
                    </h3>
                </div>

                <!-- ACTION -->

                <div class="flex gap-4 mt-8">
                    <button
                        @click="addToCart"
                        class="flex-1 py-4 rounded-2xl bg-emerald-600 text-white font-bold hover:bg-emerald-700"
                    >
                        🛒 Tambah Ke Keranjang
                    </button>

                    <button class="w-16 rounded-2xl border text-2xl">❤️</button>
                </div>
            </div>
        </div>

        <div class="mt-8 bg-slate-50 rounded-2xl p-5">
            <h3 class="font-bold mb-3">Informasi Pengiriman</h3>

            <div class="space-y-2 text-slate-600">
                <p>🚚 Estimasi 1-2 Hari</p>
                <p>📍 Kota Jambi</p>
                <p>💳 COD & Midtrans</p>
            </div>
        </div>

        <div class="grid md:grid-cols-4 gap-6 mt-12">
            <div class="bg-white p-6 rounded-3xl shadow">
                <h3 class="text-3xl font-bold text-emerald-600">500+</h3>

                <p>Terjual</p>
            </div>

            <div class="bg-white p-6 rounded-3xl shadow">
                <h3 class="text-3xl font-bold text-emerald-600">4.9</h3>

                <p>Rating</p>
            </div>

            <div class="bg-white p-6 rounded-3xl shadow">
                <h3 class="text-3xl font-bold text-emerald-600">98%</h3>

                <p>Kepuasan</p>
            </div>

            <div class="bg-white p-6 rounded-3xl shadow">
                <h3 class="text-3xl font-bold text-emerald-600">24/7</h3>

                <p>Support</p>
            </div>
        </div>

        <!-- DESCRIPTION -->

        <div v-if="medicine" class="mt-16 bg-white rounded-3xl p-8 shadow">
            <h2 class="text-2xl font-bold mb-4">Deskripsi Produk</h2>

            <p class="text-slate-600 leading-relaxed">
                {{ medicine.description }}
            </p>
        </div>

        <div class="mt-12 bg-white rounded-3xl p-8 shadow">
            <h2 class="text-2xl font-bold mb-6">Ulasan Pelanggan</h2>

            <div class="space-y-6">
                <div>
                    <div class="font-semibold">Ahmad</div>

                    <div>⭐⭐⭐⭐⭐</div>

                    <p class="text-slate-600">
                        Produk asli dan pengiriman cepat.
                    </p>
                </div>

                <div>
                    <div class="font-semibold">Budi</div>

                    <div>⭐⭐⭐⭐⭐</div>

                    <p class="text-slate-600">
                        Harga lebih murah dibanding toko lain.
                    </p>
                </div>
            </div>
        </div>

        <div
            class="mt-12 bg-gradient-to-r from-emerald-600 to-green-700 rounded-3xl p-8 text-white"
        >
            <h2 class="text-3xl font-bold">Konsultasi Apoteker</h2>

            <p class="mt-3 text-emerald-100">
                Bingung memilih obat? Konsultasikan kebutuhan Anda dengan
                apoteker kami.
            </p>

            <button
                class="mt-5 bg-white text-emerald-700 px-6 py-3 rounded-xl font-bold"
            >
                Hubungi Apoteker
            </button>
        </div>

        <!-- RELATED PRODUCTS -->

        <section v-if="relatedMedicines.length" class="mt-16">
            <h2 class="text-3xl font-bold mb-8">Produk Terkait</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
                <ProductCard
                    v-for="item in relatedMedicines"
                    :key="item.id"
                    :medicine="item"
                />
            </div>
        </section>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRoute } from "vue-router";
import api from "@/services/api";
import { useCartStore } from "@/stores/cart";
import { ElMessage } from "element-plus";
import ProductCard from "@/components/ProductCard.vue";

const route = useRoute();

const cartStore = useCartStore();

const medicine = ref(null);

const loading = ref(false);

const quantity = ref(1);

const relatedMedicines = ref([]);

const fetchMedicine = async () => {
    loading.value = true;

    try {
        const response = await api.get(`medicines/${route.params.id}`);

        medicine.value = response.data.data;
    } catch (error) {
        console.error(error);
    } finally {
        loading.value = false;
    }
};

const increaseQty = () => {
    if (quantity.value < medicine.value.total_stock) {
        quantity.value++;
    }
};

const decreaseQty = () => {
    if (quantity.value > 1) {
        quantity.value--;
    }
};

const addToCart = () => {
    for (let i = 0; i < quantity.value; i++) {
        cartStore.addToCart(medicine.value);
    }

    ElMessage.success("Produk berhasil ditambahkan");
};

const formatPrice = (price) => {
    return new Intl.NumberFormat("id-ID").format(price);
};

const fetchRelated = async () => {
    try {
        let response = await api.get("medicines", {
            params: {
                category_id: medicine.value.category_id,
                per_page: 8,
            },
        });

        let products = response.data.data.data.filter(
            (item) => item.id !== medicine.value.id,
        );

        if (products.length < 4) {
            response = await api.get("medicines", {
                params: {
                    per_page: 8,
                },
            });

            products = response.data.data.data.filter(
                (item) => item.id !== medicine.value.id,
            );
        }

        relatedMedicines.value = products.slice(0, 4);
    } catch (error) {
        console.error(error);
    }
};

onMounted(async () => {
    await fetchMedicine();

    if (medicine.value) {
        await fetchRelated();
    }
});
</script>
