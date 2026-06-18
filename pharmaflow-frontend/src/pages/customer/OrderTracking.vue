<template>
    <div
        class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-emerald-50"
    >
        <!-- Header -->
        <div
            class="relative overflow-hidden bg-gradient-to-r from-emerald-600 via-green-600 to-teal-700"
        >
            <div class="max-w-5xl mx-auto px-6 py-14 md:py-20">
                <div
                    class="relative overflow-hidden bg-gradient-to-r from-emerald-600 via-green-600 to-teal-700"
                >
                    <div
                        class="absolute top-0 right-0 w-96 h-96 bg-white/10 rounded-full blur-3xl"
                    ></div>

                    <div
                        class="absolute -bottom-20 -left-20 w-80 h-80 bg-white/10 rounded-full blur-3xl"
                    ></div>
                    <div
                        class="w-16 h-16 bg-white/20 backdrop-blur rounded-2xl flex items-center justify-center mb-5"
                    >
                        <svg
                            class="w-8 h-8 text-white"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 01.553-.894L9 2m0 18l6-2m-6 2V2m6 16l5.447-2.724A1 1 0 0021 14.382V3.618a1 1 0 00-.553-.894L15 1m0 17V1m-6 1l6-1"
                            />
                        </svg>
                    </div>
                </div>

                <h1 class="text-4xl md:text-5xl font-bold text-white">
                    Lacak Pesanan Anda
                </h1>

                <p class="text-emerald-100 mt-3 text-lg">
                    Pantau status pesanan dan pengiriman secara realtime
                </p>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-5xl mx-auto px-6 py-8">
            <!-- Search Form -->
            <div
                class="bg-white rounded-3xl shadow-xl border border-slate-100 p-6 mb-8"
            >
                <div class="flex items-center gap-3 mb-6">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="w-6 h-6 text-emerald-600"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 105.5 5.5a7.5 7.5 0 0011.15 11.15z"
                        />
                    </svg>

                    <h2 class="text-2xl font-bold">Cari Pesanan</h2>
                </div>

                <form @submit.prevent="searchOrder" class="space-y-4">
                    <div>
                        <label
                            class="block text-sm font-bold text-gray-700 mb-2"
                            >Nomor Pesanan</label
                        >
                        <input
                            v-model="searchOrderNumber"
                            type="text"
                            placeholder="Contoh: ORD-20240115-XXXXX"
                            class="w-full px-5 py-4 rounded-2xl border border-slate-200 bg-slate-50 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition"
                        />
                    </div>

                    <div>
                        <label
                            class="block text-sm font-bold text-gray-700 mb-2"
                            >No. WhatsApp</label
                        >
                        <input
                            v-model="searchPhone"
                            type="tel"
                            placeholder="Contoh: 081234567890"
                            class="w-full px-5 py-4 rounded-2xl border border-slate-200 bg-slate-50 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition"
                        />
                    </div>

                    <div class="flex items-center justify-center gap-2">
                        <svg
                            v-if="!searching"
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-5 h-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M21 21l-4.35-4.35"
                            />
                        </svg>

                        <span>
                            {{ searching ? "Mencari..." : "Cari Pesanan" }}
                        </span>
                    </div>
                </form>
            </div>

            <!-- Order Found -->
            <div
                v-if="orderFound && order"
                class="bg-white rounded-[32px] shadow-[0_10px_40px_rgba(0,0,0,0.08)] border border-slate-100 p-6 md:p-8 mb-8"
            >
                <!-- Order Header -->
                <div class="mb-8 pb-8 border-b">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h2
                                class="text-2xl md:text-4xl font-black text-emerald-600 tracking-wide"
                            >
                                {{ order.order_number }}
                            </h2>

                            <button
                                @click="copyOrderNumber"
                                class="text-blue-600 text-sm hover:underline"
                            >
                                Salin Nomor Pesanan
                            </button>

                            <p class="text-gray-600">
                                {{ formatDate(order.created_at) }}
                            </p>
                        </div>
                        <span
                            :class="[
                                'px-5 py-2 rounded-2xl shadow-md font-bold text-white',
                                getStatusColor(order.status),
                            ]"
                        >
                            {{ getStatusLabel(order.status) }}
                        </span>
                    </div>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Penerima</p>
                            <p class="font-bold">{{ order.customer_name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Alamat</p>
                            <p class="font-bold">{{ order.delivery_city }}</p>
                        </div>
                    </div>
                </div>

                <!-- Status Timeline -->
                <div class="mb-8">
                    <div class="flex items-center gap-3 mb-6">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-6 h-6 text-blue-600"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10m-13 9h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v11a2 2 0 002 2z"
                            />
                        </svg>

                        <h3 class="text-xl font-bold">Status Pengiriman</h3>
                    </div>

                    <div class="space-y-4">
                        <!-- Pending -->
                        <div class="flex gap-4">
                            <div class="flex flex-col items-center">
                                <div
                                    :class="[
                                        'w-8 h-8 rounded-full flex items-center justify-center font-bold text-white',
                                        [
                                            'pending',
                                            'diproses',
                                            'dikirim',
                                            'selesai',
                                        ].indexOf(order.status) >= 0
                                            ? 'bg-green-500'
                                            : 'bg-gray-300',
                                    ]"
                                >
                                    ✓
                                </div>
                                <div
                                    v-if="order.status !== 'selesai'"
                                    class="w-1 h-16 bg-gray-300 my-2"
                                ></div>
                            </div>
                            <div>
                                <p class="font-bold">Pesanan Diterima</p>
                                <p class="text-sm text-gray-600">
                                    {{ formatDate(order.created_at) }}
                                </p>
                            </div>
                        </div>

                        <!-- Processing -->
                        <div class="flex gap-4">
                            <div class="flex flex-col items-center">
                                <div
                                    :class="[
                                        'w-8 h-8 rounded-full flex items-center justify-center font-bold text-white',
                                        [
                                            'diproses',
                                            'dikirim',
                                            'selesai',
                                        ].indexOf(order.status) >= 0
                                            ? 'bg-green-500'
                                            : 'bg-gray-300',
                                    ]"
                                >
                                    {{
                                        [
                                            "diproses",
                                            "dikirim",
                                            "selesai",
                                        ].indexOf(order.status) >= 0
                                            ? "✓"
                                            : "2"
                                    }}
                                </div>
                                <div
                                    v-if="
                                        !['dikirim', 'selesai'].includes(
                                            order.status,
                                        )
                                    "
                                    class="w-1 h-16 bg-gray-300 my-2"
                                ></div>
                            </div>
                            <div>
                                <p class="font-bold">Diproses</p>
                                <p class="text-sm text-gray-600">
                                    Tim kami sedang menyiapkan pesanan
                                </p>
                            </div>
                        </div>

                        <div class="mb-8 bg-slate-50 border rounded-2xl p-5">
                            <div class="flex items-center gap-3 mb-4">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="w-6 h-6 text-emerald-600"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M17 9V7a5 5 0 00-10 0v2m-2 0h14a2 2 0 012 2v7a2 2 0 01-2 2H5a2 2 0 01-2-2v-7a2 2 0 012-2z"
                                    />
                                </svg>

                                <h3 class="font-bold text-lg">
                                    Status Pembayaran
                                </h3>
                            </div>

                            <span
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-full font-semibold shadow-sm"
                            >
                                {{
                                    order.payment_status === "completed"
                                        ? "Lunas"
                                        : order.payment_status === "failed"
                                          ? "Gagal"
                                          : "Menunggu Pembayaran"
                                }}
                            </span>
                        </div>

                        <!-- Shipped -->
                        <div class="flex gap-4">
                            <div class="flex flex-col items-center">
                                <div
                                    :class="[
                                        'w-12 h-12 rounded-full flex items-center justify-center font-bold text-white',
                                        ['dikirim', 'selesai'].indexOf(
                                            order.status,
                                        ) >= 0
                                            ? 'bg-green-500'
                                            : 'bg-gray-300',
                                    ]"
                                >
                                    {{
                                        ["dikirim", "selesai"].indexOf(
                                            order.status,
                                        ) >= 0
                                            ? "✓"
                                            : "3"
                                    }}
                                </div>
                                <div
                                    v-if="order.status !== 'selesai'"
                                    class="w-1 h-20 bg-gray-300 my-2"
                                ></div>
                            </div>
                            <div>
                                <p class="text-lg font-bold">Dikirim</p>
                                <p class="text-sm text-gray-600">
                                    {{
                                        order.shipping_method === "standard"
                                            ? "Estimasi 1-2 hari kerja"
                                            : order.shipping_method ===
                                                "express"
                                              ? "Estimasi besok pagi"
                                              : "Hari yang sama"
                                    }}
                                </p>
                                <p
                                    v-if="order.tracking_number"
                                    class="text-sm text-blue-600 font-bold mt-1"
                                >
                                    Nomor Resi: {{ order.tracking_number }}
                                </p>
                            </div>
                        </div>

                        <div
                            v-if="order.status === 'dibatalkan'"
                            class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6"
                        >
                            <h3 class="font-bold text-red-700">
                                Pesanan Dibatalkan
                            </h3>

                            <p class="text-red-600 text-sm">
                                Pesanan ini sudah dibatalkan.
                            </p>
                        </div>

                        <!-- Completed -->
                        <div class="flex gap-4">
                            <div class="flex flex-col items-center">
                                <div
                                    :class="[
                                        'w-8 h-8 rounded-full flex items-center justify-center font-bold text-white',
                                        order.status === 'selesai'
                                            ? 'bg-green-500'
                                            : 'bg-gray-300',
                                    ]"
                                >
                                    {{ order.status === "selesai" ? "✓" : "4" }}
                                </div>
                            </div>
                            <div>
                                <p class="font-bold">Selesai</p>
                                <p
                                    v-if="order.delivered_at"
                                    class="text-sm text-gray-600"
                                >
                                    Diterima pada
                                    {{ formatDate(order.delivered_at) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Details -->
                <div class="mt-8 bg-slate-50 border rounded-2xl p-6">
                    <div class="flex items-center gap-3 mb-5">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-6 h-6 text-orange-500"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M20 13V7a2 2 0 00-1-1.73l-6-3.46a2 2 0 00-2 0L5 5.27A2 2 0 004 7v6m16 0l-8 5-8-5"
                            />
                        </svg>

                        <h3 class="text-xl font-bold">Detail Pesanan</h3>
                    </div>

                    <div class="space-y-3">
                        <div
                            v-for="item in order.items"
                            :key="item.id"
                            class="flex justify-between items-center bg-white rounded-xl border border-slate-100 px-4 py-3"
                        >
                            <span
                                >{{ item.medicine?.name }} x{{
                                    item.quantity
                                }}</span
                            >
                            <span class="font-bold"
                                >Rp{{ formatPrice(item.subtotal) }}</span
                            >
                        </div>
                    </div>

                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Subtotal</span>
                            <span class="font-bold"
                                >Rp{{ formatPrice(order.subtotal) }}</span
                            >
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Ongkir</span>
                            <span class="font-bold"
                                >Rp{{ formatPrice(order.shipping_cost) }}</span
                            >
                        </div>
                        <div
                            v-if="order.discount_amount > 0"
                            class="flex justify-between text-red-600"
                        >
                            <span>Diskon</span>
                            <span class="font-bold"
                                >-Rp{{
                                    formatPrice(order.discount_amount)
                                }}</span
                            >
                        </div>
                        <div
                            class="flex justify-between text-lg font-bold bg-gradient-to-r from-emerald-50 to-green-50 p-2 rounded"
                        >
                            <span>Total</span>
                            <span class="text-green-600"
                                >Rp{{ formatPrice(order.total_amount) }}</span
                            >
                        </div>
                    </div>
                </div>

                <div
                    class="bg-gradient-to-r from-emerald-50 to-green-50 border border-green-200 rounded-xl p-4 mb-6"
                >
                    <div class="flex items-center gap-2">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-5 h-5 text-green-600"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 17h6m-6 0a2 2 0 11-4 0m4 0a2 2 0 104 0m6 0a2 2 0 11-4 0m4 0H19V9h-4V5H3v12h2"
                            />
                        </svg>

                        <span class="font-bold text-green-700">
                            Estimasi Pengiriman
                        </span>
                    </div>

                    <p class="text-sm text-gray-600">
                        1 - 2 jam untuk wilayah Kota Jambi
                    </p>
                </div>

                <!-- Shipping Address -->
                <div class="mb-8 pb-8 border-t pt-8">
                    <div class="flex items-center gap-3 mb-5">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-6 h-6 text-red-500"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0L6.343 16.657a8 8 0 1111.314 0z"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                            />
                        </svg>

                        <h3 class="font-bold text-xl">Alamat Pengiriman</h3>
                    </div>

                    <div class="bg-slate-50 border rounded-2xl p-5">
                        <p class="font-bold mb-1">{{ order.customer_name }}</p>
                        <p class="font-bold mb-2">{{ order.customer_phone }}</p>
                        <p class="text-gray-700">
                            {{
                                order.shipping_address || order.delivery_address
                            }}
                        </p>
                        <p class="text-gray-700">
                            {{ order.delivery_city }},
                            {{ order.shipping_province }}
                            {{ order.shipping_postal_code }}
                        </p>
                        <p
                            v-if="order.notes"
                            class="text-gray-600 text-sm mt-2"
                        >
                            <strong>Catatan:</strong> {{ order.notes }}
                        </p>
                    </div>
                </div>

                <!-- Contact -->
                <div
                    class="bg-gradient-to-r from-emerald-50 to-green-50 border border-emerald-200 rounded-lg p-6"
                >
                    <div class="flex items-center gap-3 mb-4">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-6 h-6 text-emerald-600"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M8 10h.01M12 10h.01M16 10h.01M9 16h6M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4-.8L3 20l1.3-3.9A7.66 7.66 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
                            />
                        </svg>

                        <h3 class="text-xl font-bold">Hubungi Kami</h3>
                    </div>
                    <p class="text-gray-700 mb-3">
                        Hubungi kami melalui WhatsApp atau telepon
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <a
                            :href="`https://wa.me/${whatsappNumber}`"
                            target="_blank"
                            class="flex-1 text-center px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-green-700 transition font-bold"
                        >
                            WhatsApp
                        </a>
                        <a
                            href="tel:+6281234567890"
                            class="flex-1 text-center px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-blue-700 transition font-bold"
                        >
                            Telepon
                        </a>
                    </div>
                </div>
            </div>

            <!-- Not Found -->
            <div
                v-else-if="searched && !orderFound"
                class="bg-red-50 border-l-4 border-red-500 rounded-lg p-6"
            >
                <div class="flex items-center gap-3 mb-3">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="w-8 h-8 text-red-600"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>

                    <h3 class="font-bold text-xl text-red-700">
                        Pesanan Tidak Ditemukan
                    </h3>
                </div>
                <p class="text-red-700 mb-3">
                    Pastikan nomor pesanan dan nomor WhatsApp sudah benar
                </p>
                <button
                    @click="reset"
                    class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-bold"
                >
                    Coba Lagi
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import api from "@/services/api";
import dayjs from "dayjs";
import { ElMessage } from "element-plus";

const searchOrderNumber = ref("");
const searchPhone = ref("");
const searching = ref(false);
const searched = ref(false);
const orderFound = ref(false);
const order = ref(null);
const whatsappNumber = "6282310185517";

const formatPrice = (price) => {
    return new Intl.NumberFormat("id-ID").format(price);
};

const formatDate = (date) => {
    return dayjs(date).format("DD MMMM YYYY HH:mm");
};

const getStatusColor = (status) => {
    const colors = {
        pending: "bg-yellow-500",
        diproses: "bg-blue-500",
        dikirim: "bg-purple-500",
        selesai: "bg-green-500",
        dibatalkan: "bg-red-500",
    };
    return colors[status] || "bg-gray-500";
};

const getStatusLabel = (status) => {
    const labels = {
        pending: "⏳ Menunggu",
        diproses: "⚙️ Diproses",
        dikirim: "🚚 Dikirim",
        selesai: "✅ Selesai",
        dibatalkan: "❌ Dibatalkan",
    };
    return labels[status] || status;
};

const searchOrder = async () => {
    if (!searchOrderNumber.value || !searchPhone.value) {
        ElMessage.warning("Nomor pesanan dan nomor WhatsApp wajib diisi");
        return;
    }

    searching.value = true;
    searched.value = true;

    try {
        const response = await api.post("orders/track", {
            order_number: searchOrderNumber.value,

            phone: searchPhone.value,
        });

        order.value = response.data.order || null;

        orderFound.value = !!response.data.order;
    } catch (error) {
        console.error(error);

        orderFound.value = false;

        ElMessage.error(
            error.response?.data?.message || "Pesanan tidak ditemukan",
        );
    } finally {
        searching.value = false;
    }
};

const copyOrderNumber = async () => {
    try {
        await navigator.clipboard.writeText(order.value.order_number);

        ElMessage.success("Nomor pesanan berhasil disalin");
    } catch {
        ElMessage.error("Gagal menyalin nomor pesanan");
    }
};

onMounted(async () => {
    const lastOrder = localStorage.getItem("last_order");

    if (!lastOrder) return;

    const data = JSON.parse(lastOrder);

    searchOrderNumber.value = data.order_number;

    searchPhone.value = data.customer_phone;

    await searchOrder();
});

const reset = () => {
    searchOrderNumber.value = "";
    searchPhone.value = "";
    searched.value = false;
    orderFound.value = false;
    order.value = null;
};
</script>
