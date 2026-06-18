<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <div
            class="relative overflow-hidden bg-gradient-to-r from-emerald-600 via-green-600 to-teal-700"
        >
            <div class="max-w-7xl mx-auto px-6 py-8 md:py-10">
                <div class="flex items-center gap-4">
                    <div
                        class="w-16 h-16 rounded-2xl bg-white/20 backdrop-blur flex items-center justify-center"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-8 h-8 text-white"
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
                    </div>

                    <div>
                        <h1
                            class="text-white text-3xl md:text-4xl font-extrabold tracking-tight"
                        >
                            Pesanan Saya
                        </h1>

                        <p class="text-emerald-100 mt-2">
                            Kelola dan pantau seluruh pesanan Anda
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-6 py-8">
            <!-- Filter Tabs -->
            <div
                class="bg-white rounded-3xl shadow-xl border border-slate-100 p-4 mb-6 overflow-x-auto"
            >
                <button
                    @click="filterStatus = null"
                    :class="[
                        'px-8 py-3 rounded-2xl font-bold transition',
                        filterStatus === null
                            ? 'bg-emerald-600 text-white'
                            : 'bg-gray-100 text-gray-800 hover:bg-gray-200',
                    ]"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="w-5 h-5 inline mr-2"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"
                        />
                    </svg>

                    Semua ({{ totalOrders }})
                </button>

                <button
                    @click="filterStatus = 'pending'"
                    :class="[
                        'px-8 py-3 rounded-2xl font-bold transition',
                        filterStatus === 'pending'
                            ? 'bg-amber-500 text-white'
                            : 'bg-gray-100 text-gray-800 hover:bg-gray-200',
                    ]"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="w-5 h-5 inline mr-2"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <circle cx="12" cy="12" r="10" stroke-width="2" />
                        <path d="M12 6v6l4 2" stroke-width="2" />
                    </svg>
                    Menunggu ({{ statusCounts.pending }})
                </button>

                <button
                    @click="filterStatus = 'diproses'"
                    :class="[
                        'px-8 py-3 rounded-2xl font-bold transition',
                        filterStatus === 'diproses'
                            ? 'bg-emerald-600 text-white'
                            : 'bg-gray-100 text-gray-800 hover:bg-gray-200',
                    ]"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="w-5 h-5 inline mr-2"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-width="2"
                            d="M12 6V3m0 18v-3m6-6h3M3 12h3"
                        />
                    </svg>
                    Diproses ({{ statusCounts.diproses }})
                </button>

                <button
                    @click="filterStatus = 'dikirim'"
                    :class="[
                        'px-8 py-3 rounded-2xl font-bold transition',
                        filterStatus === 'dikirim'
                            ? 'bg-violet-600 text-white'
                            : 'bg-gray-100 text-gray-800 hover:bg-gray-200',
                    ]"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="w-5 h-5 inline mr-2"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-width="2"
                            d="M9 17h6m-9 0H5a2 2 0 01-2-2V9m15 8h1a2 2 0 002-2v-3"
                        />
                    </svg>
                    Dikirim ({{ statusCounts.dikirim }})
                </button>

                <button
                    @click="filterStatus = 'selesai'"
                    :class="[
                        'px-8 py-3 rounded-2xl font-bold transition',
                        filterStatus === 'selesai'
                            ? 'bg-green-600 text-white'
                            : 'bg-gray-100 text-gray-800 hover:bg-gray-200',
                    ]"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="w-5 h-5 inline mr-2"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Selesai ({{ statusCounts.selesai }})
                </button>

                <button
                    @click="filterStatus = 'dibatalkan'"
                    :class="[
                        'px-8 py-3 rounded-2xl font-bold transition',
                        filterStatus === 'dibatalkan'
                            ? 'bg-red-600 text-white'
                            : 'bg-gray-100 text-gray-800 hover:bg-gray-200',
                    ]"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="w-5 h-5 inline mr-2"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Dibatalkan ({{ statusCounts.dibatalkan }})
                </button>
            </div>

            <!-- Loading -->
            <div v-if="loading" class="text-center py-12">
                <p class="text-lg text-gray-600">⏳ Memuat pesanan...</p>
            </div>

            <!-- Empty State -->
            <div
                v-else-if="filteredOrders.length === 0"
                class="text-center py-12 bg-white rounded-lg shadow-md"
            >
                <div
                    class="w-24 h-24 mx-auto mb-6 rounded-full bg-slate-100 flex items-center justify-center"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="w-12 h-12 text-slate-400"
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
                </div>
                <p class="text-xl text-gray-600 mb-2">Belum ada pesanan</p>
                <p class="text-gray-500 mb-6">
                    {{
                        filterStatus
                            ? "Tidak ada pesanan dengan status ini"
                            : "Mulai belanja sekarang!"
                    }}
                </p>
                <router-link
                    to="/products"
                    class="inline-flex items-center gap-2 px-8 py-3 bg-emerald-600 text-white rounded-2xl hover:bg-emerald-700 transition font-bold"
                >
                    <svg
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
                            d="M3 3h2l.4 2M7 13h10l4-8H5.4"
                        />
                    </svg>

                    Belanja Sekarang
                </router-link>
            </div>

            <!-- Orders List -->
            <div v-else class="space-y-4">
                <div
                    v-for="order in filteredOrders"
                    :key="order.id"
                    class="bg-white rounded-3xl shadow-md hover:shadow-xl hover:-translate-y-1 transition-all duration-300"
                >
                    <!-- Order Header -->
                    <div
                        class="p-6 border-b hover:bg-slate-50 cursor-pointer"
                        @click="toggleOrderDetail(order.id)"
                    >
                        <!-- Left Info -->
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 flex-wrap">
                                    <h3
                                        class="text-xl md:text-2xl font-extrabold text-slate-800"
                                    >
                                        {{ order.order_number }}
                                    </h3>

                                    <span
                                        :class="[
                                            'px-3 py-1 rounded-full text-sm font-semibold',
                                            getStatusColor(order.status),
                                        ]"
                                    >
                                        {{ getStatusLabel(order.status) }}
                                    </span>
                                </div>

                                <div
                                    class="mt-3 flex flex-wrap gap-4 text-sm text-slate-500"
                                >
                                    <span>
                                        {{ formatDate(order.created_at) }}
                                    </span>

                                    <span>
                                        {{ order.items?.length || 0 }} Item
                                    </span>

                                    <span>
                                        {{ order.delivery_city }}
                                    </span>
                                </div>
                            </div>

                            <div class="text-right">
                                <div
                                    class="text-2xl md:text-3xl font-extrabold text-emerald-600"
                                >
                                    Rp{{ formatPrice(order.total_amount) }}
                                </div>

                                <div class="mt-2">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="w-5 h-5 text-slate-400 transition-transform duration-300"
                                        :class="{
                                            'rotate-180':
                                                expandedOrder === order.id,
                                        }"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M19 9l-7 7-7-7"
                                        />
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div class="px-6 pb-4">
                            <div class="h-2 bg-slate-200 rounded-full">
                                <div
                                    :class="[
                                        'h-2 rounded-full transition-all duration-500',
                                        getProgressColor(order.status),
                                    ]"
                                    :style="{
                                        width: getProgress(order.status),
                                    }"
                                ></div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Details (Expandable) -->
                    <div
                        v-if="expandedOrder === order.id"
                        class="bg-slate-50 p-6 space-y-4 border-t"
                    >
                        <!-- Items -->
                        <div>
                            <h4 class="font-bold mb-3 flex items-center gap-2">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="w-5 h-5 text-emerald-600"
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

                                Item Pesanan
                            </h4>
                            <div class="space-y-2">
                                <div
                                    v-for="item in order.items"
                                    :key="item.id"
                                    class="flex items-center justify-between p-4 bg-white rounded-xl border border-slate-200"
                                >
                                    <div>
                                        <p class="font-semibold text-slate-800">
                                            {{ item.medicine?.name }}
                                        </p>

                                        <p class="text-sm text-slate-500">
                                            Qty {{ item.quantity }}
                                        </p>
                                    </div>

                                    <div class="font-bold text-emerald-600">
                                        Rp{{ formatPrice(item.subtotal) }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Order Details -->
                        <div class="grid md:grid-cols-2 gap-4">
                            <div
                                class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition"
                            >
                                <p class="text-sm text-gray-600 font-bold">
                                    Tanggal Pemesanan
                                </p>
                                <p class="font-bold">
                                    {{ formatDatetime(order.created_at) }}
                                </p>
                            </div>

                            <div
                                class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition"
                            >
                                <p class="text-sm text-gray-600 font-bold">
                                    Metode Pengiriman
                                </p>
                                <p class="font-bold">
                                    {{
                                        order.shipping_method === "standard"
                                            ? " Standard"
                                            : order.shipping_method ===
                                                "express"
                                              ? " Express"
                                              : " Same Day"
                                    }}
                                </p>
                            </div>

                            <div
                                class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition"
                            >
                                <p class="text-sm text-gray-600 font-bold">
                                    Metode Pembayaran
                                </p>
                                <p class="font-bold">
                                    {{
                                        order.payment_method === "cod"
                                            ? " COD"
                                            : " Transfer Bank"
                                    }}
                                </p>
                            </div>

                            <div
                                class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition"
                            >
                                <p class="text-sm text-gray-600 font-bold">
                                    Status Pembayaran
                                </p>
                                <p
                                    :class="[
                                        'font-bold',
                                        order.payment_status === 'completed'
                                            ? 'text-green-600'
                                            : 'text-orange-600',
                                    ]"
                                >
                                    {{
                                        order.payment_status === "completed"
                                            ? " Lunas"
                                            : " Pending"
                                    }}
                                </p>
                            </div>
                        </div>

                        <!-- Shipping Address -->
                        <div
                            class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition"
                        >
                            <div class="flex items-center gap-2 mb-3">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="w-5 h-5 text-red-500"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 21s-6-4.35-6-10a6 6 0 1112 0c0 5.65-6 10-6 10z"
                                    />
                                </svg>

                                <span class="font-semibold text-slate-700">
                                    Alamat Pengiriman
                                </span>
                            </div>

                            <p class="font-bold">{{ order.customer_name }}</p>
                            <p class="text-sm">{{ order.customer_phone }}</p>
                            <p class="text-sm">
                                {{
                                    order.shipping_address ||
                                    order.delivery_address
                                }}
                            </p>
                            <p class="text-sm">
                                {{ order.delivery_city }},
                                {{ order.shipping_province }}
                                {{ order.shipping_postal_code }}
                            </p>
                        </div>

                        <!-- Tracking Info -->
                        <div class="text-sm text-gray-600 font-bold">
                            <div class="flex items-center gap-2">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="w-5 h-5 text-blue-600"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-width="2"
                                        d="M13 10V3L4 14h7v7l9-11h-7z"
                                    />
                                </svg>

                                <span>Nomor Resi</span>
                            </div>
                        </div>

                        <p class="font-bold text-lg">
                            {{ order.tracking_number }}
                        </p>

                        <!-- Notes -->
                        <div
                            v-if="order.notes"
                            class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition"
                        >
                            <div class="flex items-center gap-2 mb-2">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="w-5 h-5 text-amber-500"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l3.414 3.414A1 1 0 0117 7.414V19a2 2 0 01-2 2z"
                                    />
                                </svg>

                                <span class="font-semibold text-slate-700">
                                    Catatan
                                </span>
                            </div>
                            <p>{{ order.notes }}</p>
                        </div>

                        <!-- Order Summary -->
                        <div
                            class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition"
                        >
                            <div class="flex justify-between">
                                <span class="text-gray-600">Subtotal</span>
                                <span class="font-bold"
                                    >Rp{{ formatPrice(order.subtotal) }}</span
                                >
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Ongkir</span>
                                <span class="font-bold"
                                    >Rp{{
                                        formatPrice(order.shipping_cost)
                                    }}</span
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
                                class="flex justify-between text-lg font-bold border-t pt-2"
                            >
                                <span>Total</span>
                                <span class="text-green-600"
                                    >Rp{{
                                        formatPrice(order.total_amount)
                                    }}</span
                                >
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div
                            class="grid grid-cols-1 md:grid-cols-3 gap-3 pt-6 border-t"
                        >
                            <router-link
                                :to="`/orders/${order.order_number}`"
                                class="bg-blue-600 hover:bg-blue-700 text-white h-12 rounded-xl flex items-center justify-center gap-2"
                            >
                                <svg
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
                                        d="M15 12H9m12 0A9 9 0 1112 3a9 9 0 019 9z"
                                    />
                                </svg>

                                <span>Detail</span>
                            </router-link>

                            <router-link
                                :to="{
                                    name: 'ProductDetail',
                                    params: {
                                        id: order.items?.[0]?.medicine_id,
                                    },
                                }"
                                class="text-white bg-emerald-600 hover:bg-emerald-700 h-12 rounded-xl"
                            >
                                <div
                                    class="flex items-center justify-center gap-2"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="w-5 h-5"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-width="2"
                                            d="M3 3h2l.4 2M7 13h10l4-8H5.4"
                                        />
                                    </svg>

                                    <span>Pesan Lagi</span>
                                </div>
                            </router-link>

                            <button
                                v-if="
                                    order.status === 'pending' &&
                                    order.payment_status !== 'completed'
                                "
                                @click="cancelOrder(order.id)"
                                class="text-white bg-red-600 hover:bg-red-700 h-12 rounded-xl"
                            >
                                <div
                                    class="flex items-center justify-center gap-2"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="w-5 h-5"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"
                                        />
                                    </svg>

                                    <span>Batalkan</span>
                                </div>
                            </button>

                            <button
                                @click="contactSupport(order)"
                                class="text-white bg-slate-800 hover:bg-slate-900 h-12 rounded-xl"
                            >
                                <div
                                    class="flex items-center justify-center gap-2"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke-width="1.8"
                                        stroke="currentColor"
                                        class="w-5 h-5"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M8.625 9.75h6.75m-6.75 3h4.5M21 12c0 4.97-4.03 9-9 9a8.96 8.96 0 01-4.255-1.07L3 21l1.07-4.745A8.96 8.96 0 013 12c0-4.97 4.03-9 9-9s9 4.03 9 9z"
                                        />
                                    </svg>

                                    <span>Hubungi</span>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="totalPages > 1" class="flex justify-center gap-2 mt-8">
                <button
                    v-for="page in totalPages"
                    :key="page"
                    @click="
                        currentPage = page;
                        fetchOrders();
                    "
                    :class="[
                        'px-5 py-3 rounded-xl font-bold transition',
                        currentPage === page
                            ? 'bg-emerald-600 text-white'
                            : 'bg-white text-gray-800 hover:bg-gray-100 border',
                    ]"
                >
                    {{ page }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useAuthStore } from "@/stores/auth";
import api from "@/services/api";
import dayjs from "dayjs";
import { ElMessage, ElMessageBox } from "element-plus";

const authStore = useAuthStore();

const orders = ref([]);
const loading = ref(false);
const expandedOrder = ref(null);
const filterStatus = ref(null);
const currentPage = ref(1);
const totalPages = ref(1);

const formatPrice = (price) => {
    return new Intl.NumberFormat("id-ID").format(price);
};

const formatDate = (date) => {
    return dayjs(date).format("DD MMM YYYY");
};

const formatDatetime = (date) => {
    return dayjs(date).format("DD MMM YYYY HH:mm");
};

const getStatusColor = (status) => {
    return {
        pending: "bg-amber-100 text-amber-700",

        diproses: "bg-sky-100 text-sky-700",

        dikirim: "bg-violet-100 text-violet-700",

        selesai: "bg-emerald-100 text-emerald-700",

        dibatalkan: "bg-red-100 text-red-700",
    }[status];
};

const getProgress = (status) => {
    switch (status) {
        case "pending":
            return "25%";

        case "diproses":
            return "50%";

        case "dikirim":
            return "75%";

        case "selesai":
            return "100%";

        case "dibatalkan":
            return "100%";
    }
};

const getProgressColor = (status) => {
    return status === "dibatalkan" ? "bg-red-500" : "bg-emerald-500";
};

const getStatusLabel = (status) => {
    const labels = {
        pending: "Menunggu",
        diproses: "Diproses",
        dikirim: "Dikirim",
        selesai: "Selesai",
        dibatalkan: "Dibatalkan",
    };
    return labels[status] || status;
};

// ================================
// COMPUTED
// ================================

const filteredOrders = computed(() => {
    if (!filterStatus.value) {
        return orders.value;
    }
    return orders.value.filter((order) => order.status === filterStatus.value);
});

const totalOrders = computed(() => orders.value.length);

const statusCounts = computed(() => {
    return {
        pending: orders.value.filter((o) => o.status === "pending").length,
        diproses: orders.value.filter((o) => o.status === "diproses").length,
        dikirim: orders.value.filter((o) => o.status === "dikirim").length,
        selesai: orders.value.filter((o) => o.status === "selesai").length,
        dibatalkan: orders.value.filter((o) => o.status === "dibatalkan")
            .length,
    };
});

// ================================
// METHODS
// ================================

const fetchOrders = async () => {
    loading.value = true;
    try {
        const response = await api.get("my-orders", {
            params: {
                page: currentPage.value,
                per_page: 10,
            },
        });

        console.log(response.data);

        orders.value = response.data.data.data || [];
        totalPages.value = response.data.data.last_page || 1;
        
    } catch (error) {
        ElMessage.error("Gagal memuat pesanan");
    } finally {
        loading.value = false;
    }
};

const toggleOrderDetail = (orderId) => {
    if (expandedOrder.value === orderId) {
        expandedOrder.value = null;
    } else {
        expandedOrder.value = orderId;
    }
};

const cancelOrder = async (orderId) => {
    try {
        await ElMessageBox.confirm(
            "Apakah Anda yakin ingin membatalkan pesanan ini?",
            "Konfirmasi",
            {
                confirmButtonText: "Ya, Batalkan",
                cancelButtonText: "Batal",
                type: "warning",
            },
        );

        await api.post(`orders/${orderId}/cancel`);
        ElMessage.success("Pesanan berhasil dibatalkan");
        fetchOrders();
    } catch (error) {
        if (!authStore.token) {
            ElMessage.error("Session expired");
            return;
        }
        if (error !== "cancel") {
            ElMessage.error("Gagal membatalkan pesanan");
        }
    }
};

const contactSupport = (order) => {
    const message = `Halo, saya ingin menanyakan tentang pesanan saya ${order.order_number}`;
    const whatsappUrl = `https://wa.me/6281234567890?text=${encodeURIComponent(message)}`;
    window.open(whatsappUrl, "_blank");
};

onMounted(() => {
    console.log("USER", authStore.user);

    console.log("ROLE", authStore.user?.role);

    fetchOrders();
});
</script>
