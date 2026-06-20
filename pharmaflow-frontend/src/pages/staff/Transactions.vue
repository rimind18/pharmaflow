<template>
    <div class="space-y-6">
        <div
            class="flex items-center justify-between rounded-[32px] bg-gradient-to-r from-emerald-600 to-emerald-500 text-white p-6 shadow-xl"
        >
            <div>
                <h1 class="text-3xl font-bold text-white">Riwayat Transaksi</h1>

                <p class="text-white mt-1">
                    Monitoring seluruh transaksi Apotik RHD Farma
                </p>
            </div>

            <div class="flex gap-3">
                <button
                    @click="exportExcel"
                    class="bg-green-600 text-white px-5 py-3 rounded-xl"
                >
                    Export Excel
                </button>
               
                <button
                    @click="exportPdf"
                    class="flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-xl hover:bg-red-700"
                >
                    Export PDF
                </button>
            </div>
        </div>

        <div class="flex flex-wrap gap-2 mb-4">
            <button
                @click="filterToday"
                class="px-4 py-2 rounded-xl bg-green-100 text-green-700 font-semibold"
            >
                Hari Ini
            </button>

            <button
                @click="filterWeek"
                class="px-4 py-2 rounded-xl bg-blue-100 text-blue-700 font-semibold"
            >
                Minggu Ini
            </button>

            <button
                @click="filterMonth"
                class="px-4 py-2 rounded-xl bg-purple-100 text-purple-700 font-semibold"
            >
                Bulan Ini
            </button>

            <button
                @click="resetFilter"
                class="px-4 py-2 rounded-xl bg-slate-100 font-semibold"
            >
                Semua
            </button>
        </div>

        <!-- Filter -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-semibold mb-2"> Status </label>
                <select
                    v-model="filterStatus"
                    @change="fetchTransactions"
                    class="w-full rounded-xl border p-3"
                >
                    <option value="">Semua Status</option>
                    <option value="lunas">✅ Lunas</option>
                    <option value="kredit">📋 Kredit</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2"> Metode </label>

                <select
                    v-model="paymentMethod"
                    @change="fetchTransactions"
                    class="w-full rounded-xl border p-3"
                >
                    <option value="">Semua</option>

                    <option value="cash">Cash</option>

                    <option value="transfer">Transfer</option>

                    <option value="credit_card">Credit Card</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2"> Kasir </label>
                <select
                    v-model="cashierId"
                    @change="fetchTransactions"
                    class="w-full rounded-xl border p-3"
                >
                    <option value="">Semua Kasir</option>

                    <option
                        v-for="cashier in cashiers"
                        :key="cashier?.id || Math.random()"
                        :value="cashier?.id"
                    >
                        {{ cashier?.name || "-" }}
                    </option>
                </select>
            </div>

            <!-- Dari -->
            <div>
                <label class="block text-sm font-semibold mb-2"> Dari </label>

                <input
                    type="date"
                    v-model="startDate"
                    class="w-full rounded-xl border p-3"
                />
            </div>

            <!-- Sampai -->
            <div>
                <label class="block text-sm font-semibold mb-2"> Sampai </label>

                <input
                    type="date"
                    v-model="endDate"
                    class="w-full rounded-xl border p-3"
                />
            </div>

            <div class="flex gap-4 mt-4">
                <input
                    v-model="searchQuery"
                    @keyup.enter="fetchTransactions"
                    class="flex-1 rounded-xl border p-3"
                    placeholder="Cari transaksi..."
                />
            </div>

            <div class="relative flex items-end">
                <button
                    @click="fetchTransactions"
                    class="px-8 py-3 bg-blue-600 text-white rounded-xl"
                >
                    Cari
                </button>
            </div>
        </div>

        <!-- Summary Cards -->
        <div
            v-if="!loading"
            class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6"
        >
            <div
                class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm hover:-translate-y-1 hover:shadow-lg transition-all duration-300"
            >
                <div class="flex justify-between">
                    <div>
                        <p class="text-slate-500 text-sm">Total Transaksi</p>

                        <h3 class="text-3xl font-bold mt-2">
                            {{ summary.transaction_count }}
                        </h3>
                    </div>

                    <div
                        class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            class="w-8 h-8 text-blue-600"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75M3 13.5h18v4.5A2.25 2.25 0 0118.75 20.25H5.25A2.25 2.25 0 013 18V13.5z"
                            />
                        </svg>
                    </div>
                </div>
            </div>

            <div
                class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm hover:-translate-y-1 hover:shadow-lg transition-all duration-300"
            >
                <div class="flex justify-between">
                    <div>
                        <p class="text-slate-500 text-sm">Pendapatan</p>

                        <h3 class="text-3xl font-bold text-green-600 mt-2">
                            Rp{{ formatPrice(summary.total_sales) }}
                        </h3>
                    </div>

                    <div
                        class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            class="w-8 h-8 text-green-600"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 6v12m4-8H8m8 4H8"
                            />
                        </svg>
                    </div>
                </div>
            </div>

            <div
                class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm hover:-translate-y-1 hover:shadow-lg transition-all duration-300"
            >
                <div class="flex justify-between">
                    <div>
                        <p class="text-slate-500 text-sm">Total Diskon</p>

                        <h3 class="text-3xl font-bold text-red-600 mt-2">
                            Rp{{ formatPrice(summary.total_discount) }}
                        </h3>
                    </div>

                    <div
                        class="w-12 h-12 rounded-xl bg-red-100 flex items-center justify-center"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            class="w-8 h-8 text-purple-600"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M3 7.5L12 3l9 4.5M3 7.5v9L12 21l9-4.5v-9"
                            />
                        </svg>
                    </div>
                </div>
            </div>

            <div
                class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm hover:-translate-y-1 hover:shadow-lg transition-all duration-300"
            >
                <div class="flex justify-between">
                    <div>
                        <p class="text-slate-500 text-sm">Rata-rata</p>

                        <h3 class="text-3xl font-bold text-purple-600 mt-2">
                            Rp{{ formatPrice(summary.average_transaction) }}
                        </h3>
                    </div>

                    <div
                        class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            class="w-8 h-8 text-orange-600"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M3 17l6-6 4 4 8-8"
                            />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="text-center py-8">
            <div class="h-96 bg-slate-200 rounded-3xl animate-pulse"></div>
        </div>

        <div class="grid grid-cols-4 gap-4">
            <div
                class="bg-white p-4 rounded-2xl border hover:-translate-y-1 hover:shadow-lg transition-all duration-300"
            >
                <p class="text-sm text-slate-500">Tunai</p>

                <h3 class="text-2xl font-bold text-green-600">
                    Rp{{ formatPrice(summary.cash_sales || 0) }}
                </h3>
            </div>

            <div
                class="bg-white p-4 rounded-2xl border hover:-translate-y-1 hover:shadow-lg transition-all duration-300"
            >
                <p class="text-sm text-slate-500">Transfer Bank</p>

                <h3 class="text-2xl font-bold text-blue-600">
                    Rp{{ formatPrice(summary.transfer_sales || 0) }}
                </h3>
            </div>

            <div
                class="bg-white p-4 rounded-2xl border hover:-translate-y-1 hover:shadow-lg transition-all duration-300"
            >
                <p class="text-sm text-slate-500">Kartu Kredit</p>

                <h3 class="text-2xl font-bold text-purple-600">
                    Rp{{ formatPrice(summary.credit_card_sales || 0) }}
                </h3>
            </div>

            <div
                class="bg-white p-4 rounded-2xl border hover:-translate-y-1 hover:shadow-lg transition-all duration-300"
            >
                <p class="text-sm text-slate-500">Obat Terjual</p>

                <h3 class="text-2xl font-bold text-orange-600">
                    {{ summary.total_items_sold || 0 }}
                </h3>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-3xl shadow-sm border overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-200 sticky top-0 z-10">
                    <tr class="hover:bg-slate-50 transition">
                        <th class="px-6 py-3 text-left font-semibold">
                            No Ref
                        </th>
                        <th class="px-6 py-3 text-left font-semibold">
                            Tanggal
                        </th>
                        <th class="px-6 py-3 text-left font-semibold">Kasir</th>
                        <th class="px-6 py-3 text-left font-semibold">Item</th>
                        <th class="px-6 py-3 text-left font-semibold">
                            Pembayaran
                        </th>
                        <th class="px-6 py-3 text-left font-semibold">
                            Sub Total
                        </th>
                        <th class="px-6 py-3 text-left font-semibold">
                            Diskon
                        </th>
                        <th class="px-6 py-3 text-left font-semibold">
                            Grand Total
                        </th>
                        <th class="px-6 py-3 text-left font-semibold">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <tr
                        v-for="transaction in transactions"
                        :key="transaction.id"
                        :class="{
                            'bg-green-50': transaction.final_amount >= 500000,
                        }"
                        class="hover:bg-gray-50"
                    >
                        <td class="px-6 py-4 font-semibold">
                            {{ transaction.reference_number }}
                        </td>

                        <td class="px-6 py-4">
                            {{ formatDate(transaction.created_at) }}
                        </td>

                        <td class="px-6 py-4">
                            {{ transaction.kasir?.name }}
                        </td>

                        <td class="px-6 py-4">
                            {{ transaction.items?.length || 0 }}
                        </td>

                        <td class="px-6 py-4">
                            {{ paymentLabel(transaction.payment_method) }}
                        </td>

                        <td class="px-6 py-4">
                            Rp{{ formatPrice(transaction.total_amount) }}
                        </td>

                        <td class="px-6 py-4 text-red-600">
                            Rp{{ formatPrice(transaction.discount_amount) }}
                        </td>

                        <td class="px-6 py-4 font-bold text-green-600">
                            Rp{{ formatPrice(transaction.final_amount) }}
                        </td>
                        <td class="px-6 py-4">
                            <span
                                v-if="transaction.payment_status === 'lunas'"
                                class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold"
                            >
                                LUNAS
                            </span>

                            <span
                                v-else
                                class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-bold"
                            >
                                KREDIT
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <button
                                @click="viewDetail(transaction)"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.5"
                                    stroke="currentColor"
                                    class="w-4 h-4"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M2.25 12s3.75-7.5 9.75-7.5S21.75 12 21.75 12 18 19.5 12 19.5 2.25 12 2.25 12z"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                    />
                                </svg>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="flex justify-between items-center p-4">
                <p class="text-sm text-slate-500">
                    Menampilkan
                    {{ pagination.from }}
                    -
                    {{ pagination.to }}
                    dari
                    {{ pagination.total }}
                    transaksi
                </p>

                <div class="flex gap-2">
                    <button
                        @click="previousPage"
                        class="px-4 py-2 rounded-lg border"
                    >
                        Previous
                    </button>

                    <button
                        @click="nextPage"
                        class="px-4 py-2 rounded-lg border"
                    >
                        Next
                    </button>
                </div>
            </div>
        </div>

        <!-- Detail Modal -->
        <div
            v-if="selectedTransaction && selectedTransaction.id"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
        >
            <div
                class="bg-white rounded-lg shadow-lg p-8 max-w-2xl w-full max-h-[80vh] overflow-y-auto"
            >
                <div class="flex items-center justify-between border-b pb-4">
                    <div class="bg-slate-100 rounded-xl px-4 py-3">
                        <p class="font-bold text-center">APOTIK RHD FARMA</p>

                        <p class="text-center text-xs">Preview Struk</p>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-slate-800">
                            Detail Transaksi
                        </h2>

                        <p class="text-sm text-slate-500">
                            Informasi transaksi dan item penjualan
                        </p>
                    </div>

                    <button
                        @click="selectedTransaction = null"
                        class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200"
                    >
                        ✕
                    </button>
                </div>

                <!-- Header Info -->
                <div class="grid grid-cols-2 gap-4 mb-6 pb-4 border-b">
                    <div>
                        <p class="text-sm text-gray-600">No. Referensi</p>
                        <p class="font-bold">
                            {{ selectedTransaction.reference_number }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Tanggal</p>
                        <p class="font-bold">
                            {{ formatDate(selectedTransaction.created_at) }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Kasir</p>
                        <p class="font-bold">
                            {{ selectedTransaction.kasir?.name }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Status</p>
                        <p
                            :class="
                                selectedTransaction.payment_status === 'lunas'
                                    ? 'text-green-600'
                                    : 'text-yellow-600'
                            "
                            class="font-bold"
                        >
                            {{
                                selectedTransaction.payment_status === "lunas"
                                    ? "Lunas"
                                    : "Kredit"
                            }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Metode Pembayaran</p>

                        <span
                            :class="[
                                paymentBadge(
                                    selectedTransaction.payment_method,
                                ),
                                'px-3 py-1 rounded-full text-xs font-semibold',
                            ]"
                        >
                            {{ selectedTransaction.payment_method }}
                        </span>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Total Item</p>

                        <p class="font-bold">
                            {{ selectedTransaction.items?.length || 0 }}
                        </p>
                    </div>
                    <div class="grid grid-cols-3 gap-4 mt-4">
                        <div class="bg-slate-50 rounded-xl p-3">
                            <p class="text-xs text-slate-500">Modal</p>

                            <p class="font-bold">
                                Rp{{
                                    formatPrice(selectedTransaction.cost_amount)
                                }}
                            </p>
                        </div>

                        <div class="bg-green-50 rounded-xl p-3">
                            <p class="text-xs text-slate-500">Profit</p>

                            <p class="font-bold text-green-600">
                                Rp{{
                                    formatPrice(
                                        selectedTransaction.profit_amount,
                                    )
                                }}
                            </p>
                        </div>

                        <div class="bg-blue-50 rounded-xl p-3">
                            <p class="text-xs text-slate-500">Margin</p>

                            <p class="font-bold text-blue-600">
                                {{ selectedTransaction.margin_percent }}%
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Items -->
                <div
                    v-for="(item, index) in selectedTransaction.items"
                    :key="index"
                    class="flex justify-between items-center p-3 rounded-xl bg-slate-50"
                >
                    <div>
                        <p class="font-semibold">
                            {{ item.medicine ? item.medicine.name : "-" }}
                        </p>

                        <p class="text-sm text-slate-500">
                            {{ item.quantity }} x Rp{{
                                formatPrice(item.price)
                            }}
                        </p>
                    </div>

                    <div class="font-bold">
                        Rp{{ formatPrice(item.subtotal) }}
                    </div>
                </div>

                <!-- Totals -->
                <div class="border-t pt-4 space-y-2">
                    <div class="flex justify-between">
                        <span>Subtotal</span>
                        <span
                            >Rp{{
                                formatPrice(selectedTransaction.total_amount)
                            }}</span
                        >
                    </div>
                    <div class="flex justify-between text-red-600">
                        <span>Diskon</span>
                        <span
                            >-Rp{{
                                formatPrice(selectedTransaction.discount_amount)
                            }}</span
                        >
                    </div>
                    <div
                        class="flex justify-between text-2xl font-bold bg-green-50 p-3 rounded"
                    >
                        <span>Total</span>
                        <span class="text-green-600"
                            >Rp{{
                                formatPrice(selectedTransaction.final_amount)
                            }}</span
                        >
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Pembayaran</p>

                        <p class="font-bold">
                            Rp{{
                                formatPrice(selectedTransaction.cash_received)
                            }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-500">Kembalian</p>

                        <p class="font-bold">
                            Rp{{
                                formatPrice(selectedTransaction.change_amount)
                            }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 mt-6">
                    <button
                        @click="printReceipt(selectedTransaction)"
                        class="bg-green-600 text-white px-4 py-3 rounded-xl"
                    >
                        Print Struk
                    </button>

                    <button
                        @click="selectedTransaction = null"
                        class="py-3 rounded-xl bg-slate-200 font-semibold"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import api from "@/services/api";
import dayjs from "dayjs";
import { ElMessage } from "element-plus";

const transactions = ref([]);
const paymentMethod = ref("");
const loading = ref(false);
const selectedTransaction = ref(null);
const startDate = ref("");
const endDate = ref("");
const cashierId = ref("");
const cashiers = ref([]);
const currentPage = ref(1);
const filterStatus = ref("");
const filterDate = ref("");
const searchQuery = ref("");
const summary = ref({
    transaction_count: 0,
    total_sales: 0,
    total_discount: 0,
    average_transaction: 0,
});

const formatPrice = (price) => {
    return new Intl.NumberFormat("id-ID").format(
        isNaN(price) ? 0 : Number(price),
    );
};

const formatDate = (date) => {
    return dayjs(date).format("DD/MM/YYYY HH:mm");
};

const filterToday = () => {
    const today = new Date().toISOString().split("T")[0];

    startDate.value = today;
    endDate.value = today;

    fetchTransactions();
};

const filterWeek = () => {
    const today = new Date();

    const firstDay = new Date(today.setDate(today.getDate() - today.getDay()));

    startDate.value = firstDay.toISOString().split("T")[0];

    endDate.value = new Date().toISOString().split("T")[0];

    fetchTransactions();
};

const filterMonth = () => {
    const today = new Date();

    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);

    startDate.value = firstDay.toISOString().split("T")[0];

    endDate.value = new Date().toISOString().split("T")[0];

    fetchTransactions();
};

const resetFilter = () => {
    startDate.value = "";
    endDate.value = "";
    paymentMethod.value = "";
    cashierId.value = "";
    searchQuery.value = "";

    fetchTransactions();
};

const fetchTransactions = async () => {
    loading.value = true;
    try {
        const params = {
            per_page: 20,
            today: filterDate.value ? false : true,
        };
        if (paymentMethod.value) {
            params.payment_method = paymentMethod.value;
        }

        if (filterStatus.value) params.payment_status = filterStatus.value;
        if (filterDate.value) params.date = filterDate.value;
        if (searchQuery.value) params.search = searchQuery.value;

        if (cashierId.value) {
            params.cashier_id = cashierId.value;
        }

        if (startDate.value) {
            params.start_date = startDate.value;
        }

        if (endDate.value) {
            params.end_date = endDate.value;
        }

        const response = await api.get("transactions", { params });
        transactions.value = response.data.data.data || [];
        summary.value = response.data.summary || {};
        pagination.value = {
            current_page: response.data.data.current_page,
            from: response.data.data.from,
            to: response.data.data.to,
            total: response.data.data.total,
        };
    } catch (error) {
        ElMessage.error("Gagal memuat transaksi");
    } finally {
        loading.value = false;
    }
};

const fetchCashiers = async () => {
    try {
        const response = await api.get("/employees");

        cashiers.value = response.data.data;
    } catch (error) {
        console.log(error);
    }
};

const paymentLabel = (method) => {
    const labels = {
        cash: "Tunai",
        transfer: "Transfer Bank",
        credit_card: "Kartu Kredit",
    };

    return labels[method] || method;
};

const paymentBadge = (method) => {
    switch (method) {
        case "cash":
            return "bg-green-100 text-green-700";

        case "transfer":
            return "bg-blue-100 text-blue-700";

        case "credit_card":
            return "bg-purple-100 text-purple-700";

        default:
            return "bg-slate-100 text-slate-700";
    }
};

const exportExcel = () => {
    window.open(
        `${import.meta.env.VITE_API_URL}/reports/transactions/export`,
        "_blank"
    );
};

const exportPdf = () => {
    window.open(
        `${import.meta.env.VITE_API_URL}/reports/transactions/pdf`,
        "_blank",
    );
};

const viewDetail = async (transaction) => {
    try {
        const response = await api.get(`/transactions/${transaction.id}`);

        console.log("DETAIL", response.data.data);

        selectedTransaction.value = response.data.data;
    } catch (error) {
        console.log(error);
    }
};

const printReceipt = (transaction) => {
    window.open(`/receipt/${transaction.id}`, "_blank");
};

const pagination = ref({
    current_page: 1,
    from: 0,
    to: 0,
    total: 0,
});

const params = {
    page: currentPage.value,
    per_page: 20,
};

const nextPage = () => {
    if (
        pagination.value.current_page < Math.ceil(pagination.value.total / 20)
    ) {
        currentPage.value++;
        fetchTransactions();
    }
};

const previousPage = () => {
    if (currentPage.value > 1) {
        currentPage.value--;
        fetchTransactions();
    }
};

onMounted(() => {
    fetchTransactions();
    fetchCashiers();
});
</script>
