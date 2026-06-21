<template>
    <div class="space-y-6">
        <div
            class="relative overflow-hidden rounded-[32px] bg-gradient-to-r from-emerald-700 via-emerald-600 to-teal-500 p-8 shadow-xl"
        >
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-4xl font-bold text-white">
                        Laporan Operasional
                    </h1>

                    <p class="text-emerald-100 mt-2">
                        Monitoring transaksi, penjualan, stok, performa kasir
                        dan order online.
                    </p>
                </div>

                <div class="bg-white/20 backdrop-blur-md rounded-2xl px-6 py-4">
                    <p class="text-sm text-white">Tanggal Laporan</p>

                    <p class="text-2xl font-bold text-white">
                        {{ dayjs(selectedDate).format("DD MMMM YYYY") }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border p-5">
            <div class="flex flex-wrap gap-4 items-end">
                <div>
                    <label class="block text-sm font-medium mb-2">
                        Pilih Tanggal
                    </label>

                    <input
                        type="date"
                        v-model="selectedDate"
                        class="border rounded-xl px-4 py-2"
                    />
                </div>

                <button
                    @click="fetchReport"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-xl"
                >
                    Muat Data
                </button>

                <button
                    @click="exportExcel"
                    class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 rounded-xl"
                >
                    Export Excel
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            <div
                class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm hover:shadow-md transition"
            >
                <svg
                    class="w-10 h-10 text-blue-600"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5l5 5v11a2 2 0 01-2 2z"
                    />
                </svg>
                <p class="text-gray-500">Total Transaksi</p>

                <h2 class="text-3xl font-bold">
                    {{ report.summary.transaction_count }}
                </h2>
            </div>

            <div
                class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm hover:shadow-md transition"
            >
                <svg
                    class="w-10 h-10 text-green-600"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3
3-1.343 3-3-1.343-3-3-3z"
                    />
                </svg>
                <p class="text-gray-500">Total Penjualan</p>

                <h2 class="text-3xl font-bold text-green-600">
                    Rp{{ formatPrice(report.summary.total_sales) }}
                </h2>
            </div>

            <div
                class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm hover:shadow-md transition"
            >
                <svg
                    class="w-10 h-10 text-indigo-600"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M20 13V7a2 2 0 00-2-2h-3V3H9v2H6a2 2 0 00-2 2v6"
                    />
                </svg>
                <p class="text-gray-500">Item Terjual</p>

                <h2 class="text-3xl font-bold text-blue-600">
                    {{ report.summary.total_items_sold }}
                </h2>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow">
                <p class="text-slate-500">Total Profit</p>

                <h2 class="text-3xl font-bold text-emerald-600">
                    Rp{{ formatPrice(report.summary.total_profit) }}
                </h2>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow">
                <p class="text-slate-500">Margin</p>

                <h2 class="text-3xl font-bold text-blue-600">
                    {{ report.summary.profit_margin }}%
                </h2>
            </div>

            <div
                class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm hover:shadow-md transition"
            >
                <svg
                    class="w-10 h-10 text-purple-600"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M3 17l6-6 4 4 8-8"
                    />
                </svg>
                <p class="text-gray-500">Avg Transaksi</p>

                <h2 class="text-3xl font-bold text-purple-600">
                    Rp{{ formatPrice(report.summary.average_transaction) }}
                </h2>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow p-6">
            <h2 class="text-xl font-bold mb-4">💳 Metode Pembayaran</h2>

            <div
                v-for="item in report.payment_methods"
                :key="item.payment_method"
                class="bg-slate-50 rounded-xl p-5"
            >
                <p class="text-slate-500">
                    {{ item.payment_method }}
                </p>

                <h3 class="text-2xl font-bold text-emerald-600">
                    Rp{{ formatPrice(item.amount) }}
                </h3>

                <p class="text-sm text-slate-400">{{ item.count }} transaksi</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow p-6">
            <h2 class="text-xl font-bold mb-6">📦 Online Orders</h2>

            <div class="grid md:grid-cols-5 gap-4">
                <div class="bg-yellow-50 rounded-xl p-4">
                    <p>Pending</p>
                    <h3 class="text-2xl font-bold">
                        {{ report.online_orders.pending }}
                    </h3>
                </div>

                <div class="bg-blue-50 rounded-xl p-4">
                    <p>Diproses</p>
                    <h3 class="text-2xl font-bold">
                        {{ report.online_orders.diproses }}
                    </h3>
                </div>

                <div class="bg-indigo-50 rounded-xl p-4">
                    <p>Dikirim</p>
                    <h3 class="text-2xl font-bold">
                        {{ report.online_orders.dikirim }}
                    </h3>
                </div>

                <div class="bg-green-50 rounded-xl p-4">
                    <p>Selesai</p>
                    <h3 class="text-2xl font-bold">
                        {{ report.online_orders.selesai }}
                    </h3>
                </div>

                <div class="bg-red-50 rounded-xl p-4">
                    <p>Dibatalkan</p>
                    <h3 class="text-2xl font-bold">
                        {{ report.online_orders.dibatalkan }}
                    </h3>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow p-6">
            <div
                v-if="report.top_products.length"
                class="bg-gradient-to-r from-orange-500 to-amber-500 text-white rounded-3xl p-6"
            >
                <p class="text-sm opacity-80">Produk Terlaris Hari Ini</p>

                <h2 class="text-2xl font-bold mt-2">
                    {{ report.top_products[0].medicine?.name }}
                </h2>

                <p>
                    {{ report.top_products[0].qty_sold }}
                    pcs terjual
                </p>
            </div>

            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-3">#</th>
                        <th class="text-left py-3">Produk</th>
                        <th class="text-left py-3">Kategori</th>
                        <th class="text-left py-3">Qty</th>
                        <th class="text-left py-3">Progress</th>
                        <th class="text-left py-3">Revenue</th>
                    </tr>
                </thead>

                <tbody>
                    <tr
                        v-for="(item, index) in report.top_products"
                        :key="item.medicine_id"
                        class="border-b"
                    >
                        <td>{{ index + 1 }}</td>

                        <td>
                            {{ item.medicine?.name }}
                        </td>

                        <td>
                            {{ item.medicine?.category?.name }}
                        </td>

                        <td>
                            {{ item.qty_sold }}
                        </td>

                        <td class="w-52">
                            <div class="bg-slate-200 h-2 rounded-full">
                                <div
                                    class="bg-emerald-500 h-2 rounded-full"
                                    :style="{
                                        width: report.top_products.length
                                            ? (item.qty_sold /
                                                  report.top_products[0]
                                                      .qty_sold) *
                                                  100 +
                                              '%'
                                            : '0%',
                                    }"
                                ></div>
                            </div>
                        </td>

                        <td>Rp{{ formatPrice(item.revenue) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div
            v-for="(cashier, index) in report.cashier_performance"
            :key="cashier.kasir_id"
            class="flex justify-between items-center border-b py-4"
        >
            <div class="flex items-center gap-3">
                <span
                    class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center"
                >
                    {{ index + 1 }}
                </span>

                <div>
                    <p class="font-semibold">
                        {{ cashier.kasir?.name }}
                    </p>

                    <p class="text-sm text-slate-500">
                        {{ cashier.transaction_count }} transaksi
                    </p>
                </div>
            </div>

            <div class="font-bold text-emerald-600">
                Rp{{ formatPrice(cashier.total_sales) }}
            </div>
        </div>

        <div class="grid grid-cols-2 gap-6">
            <!-- LOW STOCK -->

            <div class="bg-white rounded-xl shadow p-6">
                <span
                    class="px-2 py-1 rounded-full bg-red-100 text-red-700 text-xs"
                >
                    LOW STOCK
                </span>

                <div
                    v-for="item in report.low_stock"
                    :key="item.medicine_id"
                    class="border-b py-2"
                >
                    <div>
                        {{ item.medicine?.name }}
                    </div>

                    <div class="text-red-600 font-bold">
                        Stock : {{ item.stock }}
                    </div>
                </div>

                <div
                    v-if="report.low_stock.length === 0"
                    class="text-green-600"
                >
                    Tidak ada stok menipis
                </div>
            </div>

            <!-- EXPIRING -->

            <div class="bg-white rounded-xl shadow p-6">
                <span
                    class="px-2 py-1 rounded-full bg-yellow-100 text-yellow-700 text-xs"
                >
                    EXPIRING
                </span>

                <div
                    v-for="item in report.expiring_medicines"
                    :key="item.id"
                    class="border-b py-2"
                >
                    <div>
                        {{ item.medicine?.name }}
                    </div>

                    <div class="text-yellow-600 font-bold">
                        {{ item.expired_date }}
                    </div>
                </div>

                <div
                    v-if="report.expiring_medicines.length === 0"
                    class="text-green-600"
                >
                    Tidak ada obat mendekati expired
                </div>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-6">
            <PaymentMethodChart :payment-methods="report.payment_methods" />

            <TopProductChart :products="report.top_products" />
        </div>
        <div v-if="loading" class="space-y-4">
            <div class="h-40 bg-slate-200 animate-pulse rounded-3xl"></div>

            <div class="grid grid-cols-4 gap-4">
                <div
                    v-for="n in 4"
                    :key="n"
                    class="h-32 bg-slate-200 animate-pulse rounded-2xl"
                ></div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import api from "@/services/api";
import PaymentMethodChart from "@/components/reports/PaymentMethodChart.vue";
import TopProductChart from "@/components/reports/TopProductChart.vue";
import dayjs from "dayjs";
import "dayjs/locale/id";

dayjs.locale("id");

const loading = ref(false);

const report = ref({
    summary: {
        transaction_count: 0,
        total_sales: 0,
        total_discount: 0,
        total_items_sold: 0,
        average_transaction: 0,
    },
    payment_methods: [],
    cashier_performance: [],
    top_products: [],
    low_stock: [],
    expiring_medicines: [],
    online_orders: {
        pending: 0,
        diproses: 0,
        dikirim: 0,
        selesai: 0,
        dibatalkan: 0,
    },
});

const selectedDate = ref(dayjs().format("YYYY-MM-DD"));

const fetchReport = async () => {
    loading.value = true;

    try {
        const response = await api.get("/reports/daily", {
            params: {
                date: selectedDate.value,
            },
        });
        console.log("REPORT API", response.data);

        console.log(response.data);

        report.value = response.data.data;
    } catch (error) {
        console.error(error);
    } finally {
        loading.value = false;
    }
};

const formatPrice = (value) => {
    return new Intl.NumberFormat("id-ID").format(value || 0);
};

const exportExcel = () => {
    window.open(
        `http://127.0.0.1:8000/api/v1/reports/daily/export?date=${selectedDate.value}`,
        "_blank",
    );
};

onMounted(() => {
    fetchReport();
});
</script>
