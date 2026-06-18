<template>
    <div>
        <!-- Hero Section -->
        <section
            class="relative overflow-hidden bg-gradient-to-br from-emerald-600 via-green-600 to-teal-700"
        >
            <div class="absolute inset-0 bg-black/10"></div>

            <div
                class="max-w-7xl mx-auto px-6 py-24 relative grid lg:grid-cols-2 gap-10 items-center"
            >
                <h1 class="text-6xl font-extrabold text-white">
                    Pesan Obat Online Lebih Cepat & Lebih Aman
                </h1>

                <p class="mt-6 text-xl text-green-100 max-w-2xl">
                    Apotek RHD Farma menyediakan layanan pemesanan obat online
                    dengan pengiriman cepat di Kota Jambi.
                </p>
            </div>
        </section>

        <div class="max-w-4xl mx-auto -mt-10 relative z-20">
            <div class="bg-white rounded-3xl shadow-2xl ">
                <input
                    v-model="searchQuery"
                    @keyup.enter="searchMedicine"
                    type="text"
                    placeholder="Cari obat, vitamin, suplemen..."
                    class="w-full h-14 rounded-2xl bg-slate-100 px-6 pr-32 outline-none"
                />

                <button
                    @click="searchMedicine"
                    class="absolute right-2 top-2 h-10 px-6 bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 transition"
                >
                    Cari
                </button>
            </div>
        </div>
        <div class="max-w-7xl mx-auto px-6 mt-12">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-3xl p-6 shadow-lg text-center">
                    <h3 class="text-4xl font-bold text-emerald-600">10.000+</h3>

                    <p class="text-slate-500">Pelanggan</p>
                </div>

                <div class="bg-white rounded-3xl p-6 shadow-lg text-center">
                    <h3 class="text-4xl font-bold text-emerald-600">5.000+</h3>

                    <p class="text-slate-500">Pesanan</p>
                </div>

                <div class="bg-white rounded-3xl p-6 shadow-lg text-center">
                    <h3 class="text-4xl font-bold text-emerald-600">98%</h3>

                    <p class="text-slate-500">Kepuasan</p>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <section class="py-20 bg-slate-50">
            <div class="max-w-7xl mx-auto px-6">
                <div class="text-center mb-14">
                    <h2 class="text-4xl font-bold">
                        Kenapa Memilih PharmaFlow?
                    </h2>

                    <p class="text-slate-500 mt-3">
                        Belanja obat online dengan aman dan nyaman
                    </p>
                </div>

                <div class="grid md:grid-cols-3 gap-8">
                    <div
                        class="bg-white rounded-3xl p-8 shadow-sm hover:shadow-xl transition"
                    >
                        <div class="text-5xl mb-4">🚚</div>

                        <h3 class="font-bold text-xl mb-3">Pengiriman Cepat</h3>

                        <p class="text-slate-500">
                            Pengiriman cepat untuk wilayah Kota Jambi.
                        </p>
                    </div>

                    <div
                        class="bg-white rounded-3xl p-8 shadow-sm hover:shadow-xl transition"
                    >
                        <div class="text-5xl mb-4">💊</div>

                        <h3 class="font-bold text-xl mb-3">Obat Asli</h3>

                        <p class="text-slate-500">
                            Produk resmi dan terjamin kualitasnya.
                        </p>
                    </div>

                    <div
                        class="bg-white rounded-3xl p-8 shadow-sm hover:shadow-xl transition"
                    >
                        <div class="text-5xl mb-4">🔒</div>

                        <h3 class="font-bold text-xl mb-3">Pembayaran Aman</h3>

                        <p class="text-slate-500">
                            Mendukung Midtrans, QRIS, Transfer dan COD.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Featured Products -->
        <section class="py-20">
            <div class="max-w-7xl mx-auto px-4">
                <div class="flex justify-between items-center mb-12">
                    <h2 class="text-4xl font-bold">Produk Pilihan</h2>

                    <router-link
                        to="/products"
                        class="text-emerald-600 font-semibold"
                    >
                        Lihat Semua →
                    </router-link>
                </div>
                <div v-if="loading" class="text-center">
                    <p class="text-xl text-gray-600">Memuat produk...</p>
                </div>
                <div
                    v-else
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6"
                >
                    <ProductCard
                        v-for="medicine in recentMedicines"
                        :key="medicine.id"
                        :medicine="medicine"
                    />
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section
            class="bg-gradient-to-r from-emerald-600 to-green-700 text-white py-20"
        >
            <div class="max-w-7xl mx-auto px-6 text-center">
                <h2 class="text-4xl font-bold mb-5">
                    Dapatkan Diskon 10% Untuk Pembelian Pertama
                </h2>

                <p class="text-xl text-emerald-100 mb-8">
                    Gunakan kode promo
                    <span
                        class="bg-white text-emerald-700 px-4 py-2 rounded-xl font-bold"
                    >
                        WELCOME10
                    </span>
                </p>

                <router-link
                    to="/products"
                    class="inline-flex items-center px-8 py-4 rounded-2xl bg-white text-emerald-700 font-bold shadow-xl hover:scale-105 transition"
                >
                    Mulai Belanja Sekarang →
                </router-link>
            </div>
        </section>
    </div>
</template>
<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import api from "@/services/api";
import ProductCard from "@/components/ProductCard.vue";

const router = useRouter();

const recentMedicines = ref([]);
const loading = ref(false);
const searchQuery = ref("");

const fetchRecentMedicines = async () => {
    loading.value = true;

    try {
        const response = await api.get(
            "medicines?per_page=8&order_by=created_at&order_dir=desc"
        );

        recentMedicines.value =
            response.data.data.data || [];
    } catch (error) {
        console.error(
            "Failed to fetch medicines:",
            error
        );
    } finally {
        loading.value = false;
    }
};

const searchMedicine = () => {
    if (!searchQuery.value.trim()) return;

    router.push({
        path: "/products",
        query: {
            search: searchQuery.value,
        },
    });
};

onMounted(() => {
    fetchRecentMedicines();
});
</script>