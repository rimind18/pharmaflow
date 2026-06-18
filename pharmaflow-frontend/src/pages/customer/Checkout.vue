<template>
    <div
        class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-emerald-50"
    >
        <!-- Header -->
        <div
            class="relative overflow-hidden bg-gradient-to-r from-emerald-600 via-green-600 to-teal-700"
        >
            <div class="absolute inset-0 bg-black/10"></div>

            <div class="relative max-w-7xl mx-auto px-6 py-12">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-5xl font-bold text-white mb-3">
                            Checkout Pesanan
                        </h1>

                        <p class="text-green-100 text-lg">
                            Apotek RHD Farma • Aman • Cepat • Terpercaya
                        </p>
                    </div>

                    <div
                        class="hidden md:flex items-center gap-3 bg-white/20 backdrop-blur-md px-5 py-3 rounded-2xl"
                    >
                        <span class="text-2xl">🔒</span>

                        <div>
                            <p class="font-semibold text-white">
                                Pembayaran Aman
                            </p>

                            <p class="text-sm text-green-100">
                                Midtrans Secure Payment
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-white border-b">
            <div class="max-w-6xl mx-auto px-6 py-6">
                <div class="flex justify-center">
                    <div
                        class="flex items-center justify-center gap-3 md:gap-8 flex-wrap"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold"
                            >
                                1
                            </div>

                            <span class="font-medium"> Data Pelanggan </span>
                        </div>

                        <div
                            class="hidden md:block w-20 h-1 bg-emerald-300"
                        ></div>

                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold"
                            >
                                2
                            </div>

                            <span class="font-medium"> Alamat </span>
                        </div>

                        <div
                            class="hidden md:block w-20 h-1 bg-emerald-300"
                        ></div>

                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold"
                            >
                                3
                            </div>

                            <span class="font-medium"> Pembayaran </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div
            v-if="loading"
            class="fixed inset-0 z-[9999] bg-black/60 backdrop-blur-md flex items-center justify-center"
        >
            <div class="bg-white rounded-3xl p-10 text-center shadow-2xl">
                <div
                    class="animate-spin w-16 h-16 border-4 border-green-500 border-t-transparent rounded-full mx-auto mb-5"
                ></div>

                <h3 class="font-bold text-xl">Memproses Pesanan</h3>

                <p class="text-gray-500 mt-2">Mohon tunggu sebentar...</p>
            </div>
        </div>

        <!-- Main Content -->
        <div v-else class="max-w-6xl mx-auto px-6 py-8">
            <!-- Empty Cart -->
            <div
                v-if="cartStore.items.length === 0"
                class="text-center py-12 bg-white/90 backdrop-blur-xl border border-white/30 rounded-3xl shadow-xl"
            >
                <p class="text-4xl mb-4">📭</p>
                <p class="text-xl text-gray-600 mb-6">Keranjang Anda kosong</p>
                <router-link
                    to="/products"
                    class="inline-block px-8 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-bold"
                >
                    🛍️ Belanja Sekarang
                </router-link>
            </div>

            <!-- Checkout Form -->
            <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Form Section (Left - 2 cols) -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- STEP 1: Customer Info -->
                    <div
                        class="bg-white/90 backdrop-blur-xl border border-white/30 rounded-3xl shadow-xl p-6"
                    >
                        <div class="flex items-center gap-3 mb-6">
                            <div
                                class="w-8 h-8 rounded-full bg-green-600 text-white flex items-center justify-center font-bold"
                            >
                                1
                            </div>
                            <h2 class="text-2xl font-bold">
                                👤 Informasi Pelanggan
                            </h2>
                        </div>

                        <div
                            class="bg-green-50 border border-green-200 rounded-2xl p-4"
                        >
                            <div
                                v-if="
                                    cartStore.guestInfo.latitude &&
                                    cartStore.guestInfo.longitude
                                "
                                class="flex items-center gap-3 text-green-600 font-bold"
                            >
                                <p class="font-semibold text-green-700">
                                    📍 Lokasi Berhasil Diverifikasi
                                </p>

                                <p class="text-sm text-gray-600 mt-1">
                                    Pengiriman tersedia untuk wilayah Kota Jambi
                                </p>
                            </div>

                            <div
                                v-else
                                class="flex items-center gap-3 text-red-500 font-bold"
                            >
                                🔴 Lokasi Belum Aktif
                            </div>
                        </div>

                        <form class="space-y-4" @submit.prevent="validateStep1">
                            <!-- Name -->
                            <div>
                                <label
                                    class="block text-sm font-bold text-gray-700 mb-2"
                                    >Nama Lengkap *</label
                                >
                                <input
                                    v-model="cartStore.guestInfo.customer_name"
                                    type="text"
                                    placeholder="Contoh: Budi Santoso"
                                    class="w-full px-5 py-4 rounded-2xl border-0 bg-slate-100 focus:ring-2 focus:ring-emerald-500 transition-all"
                                    required
                                />
                            </div>

                            <!-- Phone -->
                            <div>
                                <label
                                    class="block text-sm font-bold text-gray-700 mb-2"
                                    >No. WhatsApp *</label
                                >
                                <input
                                    v-model="cartStore.guestInfo.phone"
                                    type="tel"
                                    placeholder="Contoh: 081234567890 atau +6281234567890"
                                    class="w-full px-5 py-4 rounded-2xl border-0 bg-slate-100 focus:ring-2 focus:ring-emerald-500 transition-all"
                                    required
                                />
                                <p class="text-xs text-gray-600 mt-1">
                                    Format: gunakan 0 atau +62 di awal nomor
                                </p>
                            </div>

                            <!-- Email (Optional, for authenticated users) -->
                            <div v-if="authStore.isAuthenticated">
                                <label
                                    class="block text-sm font-bold text-gray-700 mb-2"
                                    >Email</label
                                >
                                <input
                                    :value="authStore.user?.email"
                                    type="email"
                                    disabled
                                    class="w-full px-5 py-4 rounded-2xl border-0 bg-slate-100 focus:ring-2 focus:ring-emerald-500 transition-all"
                                />
                            </div>
                        </form>
                    </div>

                    <!-- STEP 2: Shipping Address -->
                    <div
                        class="bg-white/90 backdrop-blur-xl border border-white/30 rounded-3xl shadow-xl p-6"
                    >
                        <div class="flex items-center gap-3 mb-6">
                            <div
                                class="w-8 h-8 rounded-full bg-green-600 text-white flex items-center justify-center font-bold"
                            >
                                2
                            </div>
                            <h2 class="text-2xl font-bold">
                                📍 Alamat Pengiriman
                            </h2>
                        </div>

                        <div
                            class="mt-4 p-4 rounded-2xl bg-green-50 border border-green-200"
                        >
                            <div class="flex items-center gap-2">
                                <span class="text-green-600"> 📍 </span>

                                <span class="font-medium text-green-700">
                                    Lokasi GPS berhasil terdeteksi
                                </span>
                            </div>
                        </div>

                        <form class="space-y-4" @submit.prevent="validateStep2">
                            <!-- Address -->
                            <div>
                                <label
                                    class="block text-sm font-bold text-gray-700 mb-2"
                                    >Alamat Lengkap *</label
                                >
                                <textarea
                                    v-model="cartStore.guestInfo.address"
                                    placeholder="Contoh: Jl. Merdeka No. 123, Blok A, RT 04/RW 05"
                                    rows="3"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                                    required
                                ></textarea>
                                <p class="text-xs text-gray-600 mt-1">
                                    Minimal 10 karakter (termasuk nama jalan,
                                    nomor rumah, RT/RW)
                                </p>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <!-- City -->
                                <div>
                                    <label
                                        class="block text-sm font-bold text-gray-700 mb-2"
                                        >Kota/Kabupaten *</label
                                    >
                                    <input
                                        v-model="cartStore.guestInfo.city"
                                        type="text"
                                        placeholder="Contoh: Kota Jambi"
                                        class="w-full px-5 py-4 rounded-2xl border-0 bg-slate-100 focus:ring-2 focus:ring-emerald-500 transition-all"
                                        required
                                    />
                                </div>

                                <!-- Province -->
                                <div>
                                    <label
                                        class="block text-sm font-bold text-gray-700 mb-2"
                                        >Provinsi *</label
                                    >
                                    <input
                                        v-model="cartStore.guestInfo.province"
                                        type="text"
                                        placeholder="Contoh: Jambi"
                                        class="w-full px-5 py-4 rounded-2xl border-0 bg-slate-100 focus:ring-2 focus:ring-emerald-500 transition-all"
                                        required
                                    />
                                </div>
                            </div>

                            <!-- Postal Code -->
                            <div>
                                <label
                                    class="block text-sm font-bold text-gray-700 mb-2"
                                    >Kode Pos *</label
                                >
                                <input
                                    v-model="cartStore.guestInfo.postal_code"
                                    type="text"
                                    placeholder="Contoh: 12210"
                                    maxlength="5"
                                    pattern="[0-9]{5}"
                                    class="w-full px-5 py-4 rounded-2xl border-0 bg-slate-100 focus:ring-2 focus:ring-emerald-500 transition-all"
                                    required
                                />
                                <p class="text-xs text-gray-600 mt-1">
                                    Kode pos harus 5 digit angka
                                </p>
                            </div>

                            <!-- Notes (Optional) -->
                            <div>
                                <label
                                    class="block text-sm font-bold text-gray-700 mb-2"
                                    >Catatan Pengiriman (Opsional)</label
                                >
                                <textarea
                                    v-model="cartStore.guestInfo.notes"
                                    placeholder="Contoh: Rumah di sebelah toko kelontong, pintu kuning"
                                    rows="2"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                                ></textarea>
                            </div>
                        </form>
                    </div>

                    <!-- STEP 3: Shipping & Payment -->
                    <div
                        class="bg-white/90 backdrop-blur-xl border border-white/30 rounded-3xl shadow-xl p-6"
                    >
                        <div class="flex items-center gap-3 mb-6">
                            <div
                                class="w-8 h-8 rounded-full bg-green-600 text-white flex items-center justify-center font-bold"
                            >
                                3
                            </div>
                            <h2 class="text-2xl font-bold">
                                💳 Metode Pembayaran
                            </h2>
                        </div>

                        <form class="space-y-6" @submit.prevent="submitOrder">
                            <div class="space-y-4">
                                <label class="block cursor-pointer">
                                    <input
                                        v-model="
                                            cartStore.guestInfo.payment_method
                                        "
                                        type="radio"
                                        value="cod"
                                        class="hidden"
                                    />

                                    <div
                                        class="p-5 rounded-2xl border-2 transition-all"
                                        :class="
                                            cartStore.guestInfo
                                                .payment_method === 'cod'
                                                ? 'border-emerald-500 bg-emerald-50'
                                                : 'border-gray-200'
                                        "
                                    >
                                        <div class="flex items-center gap-4">
                                            <div
                                                class="w-14 h-14 rounded-2xl bg-emerald-100 flex items-center justify-center text-2xl"
                                            >
                                                💵
                                            </div>

                                            <div>
                                                <h4 class="font-bold text-lg">
                                                    Bayar di Tempat
                                                </h4>

                                                <p class="text-gray-500">
                                                    Bayar ketika pesanan
                                                    diterima
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </label>

                                <label class="block cursor-pointer">
                                    <input
                                        v-model="
                                            cartStore.guestInfo.payment_method
                                        "
                                        type="radio"
                                        value="midtrans"
                                        class="hidden"
                                    />

                                    <div
                                        class="p-5 rounded-2xl border-2 transition-all"
                                        :class="
                                            cartStore.guestInfo
                                                .payment_method === 'midtrans'
                                                ? 'border-blue-500 bg-blue-50'
                                                : 'border-gray-200'
                                        "
                                    >
                                        <div class="flex items-center gap-4">
                                            <div
                                                class="w-14 h-14 rounded-2xl bg-blue-100 flex items-center justify-center text-2xl"
                                            >
                                                💳
                                            </div>

                                            <div>
                                                <h4 class="font-bold text-lg">
                                                    Pembayaran Online
                                                </h4>

                                                <p class="text-gray-500">
                                                    QRIS, Transfer Bank,
                                                    E-Wallet, Kartu Kredit
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            <!-- Terms & Conditions -->
                            <div class="border-t pt-6">
                                <label
                                    class="flex items-start gap-3 cursor-pointer"
                                >
                                    <input
                                        v-model="agreeTerms"
                                        type="checkbox"
                                        class="w-5 h-5 mt-1"
                                    />
                                    <span class="text-sm text-gray-700">
                                        Saya setuju dengan
                                        <router-link
                                            to="/terms"
                                            class="text-blue-600 hover:underline"
                                        >
                                            syarat dan ketentuan
                                        </router-link>
                                        serta
                                        <router-link
                                            to="/privacy"
                                            class="text-blue-600 hover:underline"
                                        >
                                            kebijakan privasi
                                        </router-link>
                                    </span>
                                </label>
                            </div>

                            <!-- Submit Button -->
                            <button
                                :disabled="loading || submitting || !agreeTerms"
                                :class="{
                                    'cursor-not-allowed opacity-50':
                                        loading || submitting || !agreeTerms,
                                }"
                                class="w-full py-5 rounded-2xl bg-gradient-to-r from-emerald-600 to-green-700 text-white font-bold text-lg shadow-xl hover:scale-[1.02] transition-all disabled:opacity-50"
                            >
                                {{
                                    loading
                                        ? "Sedang Memproses..."
                                        : "Buat Pesanan Sekarang"
                                }}
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Order Summary (Right - 1 col) -->
                <div
                    class="sticky top-24 self-start bg-white rounded-3xl shadow-xl p-6"
                >
                    <div class="mb-4 pb-4 border-b">
                        <p class="text-sm text-slate-500">
                            {{ cartStore.items.length }} produk dipilih
                        </p>
                    </div>
                    <h2 class="text-2xl font-bold mb-6">
                        📦 Ringkasan Pesanan
                    </h2>

                    <div class="space-y-3 mb-6">
                        <div
                            v-for="item in cartStore.items"
                            :key="item.id"
                            class="flex justify-between items-center py-2 border-b border-slate-100"
                        >
                            <div>
                                <p class="font-medium text-slate-800">
                                    {{ item.name }}
                                </p>

                                <p class="text-xs text-slate-500">
                                    Qty {{ item.quantity }}
                                </p>
                            </div>
                            <img
                                :src="item.image || '/images/no-image.png'"
                                class="w-12 h-12 rounded-lg object-cover"
                                alt="Produk"
                            />

                            <span>
                                Rp{{
                                    formatPrice(
                                        item.quantity *
                                            (item.selling_price || item.price),
                                    )
                                }}
                            </span>
                        </div>
                    </div>
                    <div class="border-t pt-4 mt-4">
                        <div class="flex justify-between text-sm">
                            <span>Total Produk</span>
                            <span class="font-semibold">
                                {{ cartStore.items.length }}
                            </span>
                        </div>
                    </div>

                    <!-- Totals -->
                    <div class="space-y-2 text-sm mb-4">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Subtotal</span>
                            <span class="font-bold"
                                >Rp{{ formatPrice(cartStore.subtotal) }}</span
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

                    <!-- Grand Total -->
                    <div
                        class="flex justify-between items-center p-4 bg-gradient-to-r from-green-50 to-green-100 rounded-lg border-2 border-green-300"
                    >
                        <span class="font-bold text-lg">Total</span>
                        <span class="text-2xl font-bold text-green-600"
                            >Rp{{ formatPrice(cartStore.total) }}</span
                        >
                    </div>

                    <div
                        class="mt-5 bg-emerald-50 border border-emerald-200 rounded-2xl p-4"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center"
                            >
                                🔒
                            </div>

                            <div>
                                <p class="font-semibold text-emerald-700">
                                    Transaksi Aman
                                </p>

                                <p class="text-xs text-gray-600">
                                    Dilindungi Midtrans Secure Payment
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Info Box -->
                    <div
                        class="mt-6 p-4 bg-emerald-50 border border-emerald-200 rounded-xl"
                    >
                        <p class="font-semibold text-emerald-700 mb-2">
                            ✓ Apotek RHD Farma
                        </p>

                        <p class="text-sm text-gray-600">
                            Pesanan akan diproses setelah pembayaran berhasil
                            diverifikasi atau setelah pesanan COD diterima
                            sistem.
                        </p>
                    </div>

                    <div
                        class="mt-4 rounded-2xl bg-blue-50 border border-blue-200 p-4"
                    >
                        <h3 class="font-bold text-blue-700">
                            🏥 Apotek RHD Farma
                        </h3>

                        <p class="text-sm text-gray-600 mt-2">
                            Kota Jambi Jam Operasional: 08.00 - 22.00 WIB
                        </p>
                    </div>

                    <div
                        class="mt-5 bg-yellow-50 border border-yellow-200 rounded-2xl p-4"
                    >
                        <p class="font-semibold">🚚 Estimasi Pengiriman</p>

                        <p class="text-sm text-gray-600 mt-1">
                            1 - 2 jam untuk wilayah Kota Jambi
                        </p>
                    </div>

                    <!-- Back to Cart -->
                    <router-link
                        to="/cart"
                        class="block text-center text-blue-600 hover:text-blue-800 text-sm font-bold mt-4"
                    >
                        ← Kembali ke Keranjang
                    </router-link>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import { useCartStore } from "@/stores/cart";
import { useAuthStore } from "@/stores/auth";
import api from "@/services/api";
import { ElMessage, ElMessageBox } from "element-plus";

const router = useRouter();
const cartStore = useCartStore();
const authStore = useAuthStore();

const loading = ref(false);
const submitting = ref(false);
const agreeTerms = ref(false);

const formatPrice = (price) => {
    return new Intl.NumberFormat("id-ID").format(price || 0);
};

// ====================================
// VALIDASI DATA PELANGGAN
// ====================================
const validateStep1 = () => {
    if (authStore.isAuthenticated) {
        return true;
    }

    const { customer_name, phone } = cartStore.guestInfo;

    if (!customer_name?.trim()) {
        ElMessage.warning("Nama lengkap wajib diisi");
        return false;
    }

    if (customer_name.trim().length < 3) {
        ElMessage.warning("Nama minimal 3 karakter");
        return false;
    }

    const nameRegex = /^[a-zA-Z\s.,'-]+$/;

    if (!nameRegex.test(customer_name)) {
        ElMessage.warning("Nama hanya boleh berisi huruf");
        return false;
    }

    const phoneRegex = /^(\+62|0)[0-9]{9,12}$/;

    if (!phoneRegex.test(phone)) {
        ElMessage.warning("Format nomor WhatsApp tidak valid");
        return false;
    }

    return true;
};

// ====================================
// VALIDASI ALAMAT
// ====================================
const validateStep2 = () => {
    const { address, city, province, postal_code } = cartStore.guestInfo;

    if (!address?.trim() || address.trim().length < 10) {
        ElMessage.warning("Alamat harus diisi minimal 10 karakter");
        return false;
    }

    if (!city?.trim()) {
        ElMessage.warning("Kota/Kabupaten wajib diisi");
        return false;
    }

    if (!province?.trim()) {
        ElMessage.warning("Provinsi wajib diisi");
        return false;
    }

    const cityRegex = /^[a-zA-Z\s.]+$/;

    if (!cityRegex.test(city)) {
        ElMessage.warning("Kota/Kabupaten tidak valid");
        return false;
    }

    if (!cityRegex.test(province)) {
        ElMessage.warning("Provinsi tidak valid");
        return false;
    }

    const postalRegex = /^[0-9]{5}$/;

    if (!postalRegex.test(postal_code)) {
        ElMessage.warning("Kode pos harus 5 digit angka");
        return false;
    }

    return true;
};

// ====================================
// SUBMIT ORDER
// ====================================
const submitOrder = async () => {
    if (!validateStep1()) return;
    if (!validateStep2()) return;

    if (!cartStore.guestInfo.latitude || !cartStore.guestInfo.longitude) {
        ElMessage.warning("Aktifkan GPS terlebih dahulu");
        return;
    }

    if (!agreeTerms.value) {
        ElMessage.warning("Anda harus menyetujui syarat dan ketentuan");
        return;
    }

    if (!cartStore.items.length) {
        ElMessage.warning("Keranjang Anda kosong");
        return;
    }

    if (submitting.value) return;

    const customerName = cartStore.guestInfo.customer_name || "-";

    const customerPhone = cartStore.guestInfo.phone || "-";

    const paymentMethod = cartStore.guestInfo.payment_method || "cod";

    try {
        await ElMessageBox.confirm(
            `
Nama : ${customerName}
No WA : ${customerPhone}

Metode :
${paymentMethod === "cod" ? "Bayar di Tempat" : "Pembayaran Online"}

Total :
Rp${formatPrice(cartStore.total)}

Pastikan data pesanan sudah benar.
            `,
            "Konfirmasi Pesanan",
            {
                confirmButtonText: "Ya, Buat Pesanan",
                cancelButtonText: "Periksa Lagi",
                type: "info",
            },
        );
    } catch {
        return;
    }

    loading.value = true;
    submitting.value = true;

    try {
        const orderData = cartStore.getOrderData();

        const response = await api.post("orders", orderData);

        const order = response.data.order;

        const orderNumber = order.order_number;

        localStorage.setItem(
            "last_order",
            JSON.stringify({
                order_id: order.id,
                order_number: orderNumber,
                customer_phone: customerPhone,
                customer_name: customerName,
                total_amount: response.data.total,
            }),
        );

        // =========================
        // COD
        // =========================
        if (paymentMethod === "cod") {
            cartStore.resetAfterOrder();

            router.push({
                name: "OrderSuccess",
                query: {
                    id: order.id,
                    order_number: orderNumber,
                    phone: customerPhone,
                },
            });

            return;
        }

        // =========================
        // MIDTRANS
        // =========================
        const paymentResponse = await api.post("payments/snap-token", {
            order_id: order.id,
        });

        if (typeof window.snap === "undefined") {
            ElMessage.error("Midtrans belum berhasil dimuat");
            return;
        }

        if (paymentResponse.data.snap_token) {
            window.snap.pay(paymentResponse.data.snap_token, {
                onSuccess() {
                    cartStore.resetAfterOrder();

                    router.push({
                        name: "OrderSuccess",
                        query: {
                            id: order.id,
                            order_number: orderNumber,
                            phone: customerPhone,
                        },
                    });
                },

                onPending() {
                    router.push({
                        name: "OrderTracking",
                        query: {
                            order_number: orderNumber,
                            phone: customerPhone,
                        },
                    });
                },

                onError() {
                    ElMessage.error("Pembayaran gagal");

                    router.push("/checkout");
                },

                onClose() {
                    ElMessage.warning("Pembayaran belum diselesaikan");

                    router.push({
                        name: "OrderTracking",
                        query: {
                            order_number: orderNumber,
                            phone: customerPhone,
                        },
                    });
                },
            });
        }
    } catch (error) {
        console.error("Order error:", error);

        ElMessage.error(
            error?.response?.data?.message || "Gagal membuat pesanan",
        );
    } finally {
        loading.value = false;
        submitting.value = false;
    }
};

// ====================================
// ON MOUNTED
// ====================================
onMounted(() => {
    cartStore.loadFromLocalStorage();

    if ("geolocation" in navigator) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                cartStore.guestInfo.latitude = position.coords.latitude;

                cartStore.guestInfo.longitude = position.coords.longitude;
            },

            () => {
                ElMessage.warning("Aktifkan GPS untuk pengiriman");
            },

            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0,
            },
        );
    }

    if (authStore.isAuthenticated) {
        cartStore.guestInfo.customer_name = authStore.user?.name || "";

        cartStore.guestInfo.phone = authStore.user?.phone || "";
    }

    if (!cartStore.guestInfo.payment_method) {
        cartStore.guestInfo.payment_method = "cod";
    }

    if (!window.snap) {
        const script = document.createElement("script");

       script.src = "https://app.sandbox.midtrans.com/snap/snap.js";

        script.async = true;

        script.setAttribute(
            "data-client-key",
            import.meta.env.VITE_MIDTRANS_CLIENT_KEY,
        );

        script.onload = () => {
            console.log("Midtrans loaded");
        };

        document.body.appendChild(script);
    }
});
</script>
