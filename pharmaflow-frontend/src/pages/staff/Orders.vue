<template>
    <div class="space-y-6">
        <!-- Header -->

        <div
            class="bg-gradient-to-r from-emerald-600 to-teal-700 rounded-3xl shadow-xl p-8 text-white"
        >
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-4xl font-black">Manajemen Pesanan</h1>

                    <p class="text-emerald-100 mt-2">
                        Kelola seluruh pesanan customer
                    </p>
                </div>

                <div class="bg-white/20 px-5 py-3 rounded-2xl">
                    <p class="text-sm">Total Pesanan</p>

                    <p class="text-3xl font-bold">
                        {{ orders.length }}
                    </p>
                </div>
            </div>
        </div>

        <div class="grid md:grid-cols-5 gap-4">
            <div class="bg-white rounded-2xl p-5 shadow">
                <p class="text-slate-500">Total</p>

                <h2 class="text-3xl font-bold">
                    {{ orders.length }}
                </h2>
            </div>

            <div class="bg-yellow-50 rounded-2xl p-5 shadow">
                <p class="text-yellow-700">Pending</p>

                <h2 class="text-3xl font-bold">
                    {{ orders.filter((o) => o.status === "pending").length }}
                </h2>
            </div>

            <div class="bg-blue-50 rounded-2xl p-5 shadow">
                <p class="text-blue-700">Diproses</p>

                <h2 class="text-3xl font-bold">
                    {{ orders.filter((o) => o.status === "diproses").length }}
                </h2>
            </div>

            <div class="bg-purple-50 rounded-2xl p-5 shadow">
                <p class="text-purple-700">Dikirim</p>

                <h2 class="text-3xl font-bold">
                    {{ orders.filter((o) => o.status === "dikirim").length }}
                </h2>
            </div>

            <div class="bg-green-50 rounded-2xl p-5 shadow">
                <p class="text-green-700">Selesai</p>

                <h2 class="text-3xl font-bold">
                    {{ orders.filter((o) => o.status === "selesai").length }}
                </h2>
            </div>
        </div>

        <!-- Filter & Search -->
        <div
            class="bg-white rounded-3xl shadow-lg p-6 grid md:grid-cols-5 gap-4"
        >
            <div>
                <label class="block text-sm font-semibold mb-2">Status</label>
                <select
                    v-model="filterStatus"
                    @change="fetchOrders"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option value="">Semua Status</option>
                    <option value="pending">⏳ Pending</option>
                    <option value="diproses">📦 Diproses</option>
                    <option value="dikirim">🚚 Dikirim</option>
                    <option value="selesai">✅ Selesai</option>
                    <option value="dibatalkan">❌ Dibatalkan</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2"
                    >Metode Pembayaran</label
                >
                <select
                    v-model="filterPayment"
                    @change="fetchOrders"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option value="">Semua Metode</option>
                    <option value="cod">💵 COD</option>
                    <option value="midtrans">💳 Midtrans</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2">Periode</label>
                <input
                    v-model="filterDate"
                    type="month"
                    @change="fetchOrders"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2">Cari</label>
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="No. Pesanan / Customer"
                    @keyup.enter="fetchOrders"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
            </div>

            <div class="flex items-end">
                <button
                    @click="fetchOrders"
                    class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold"
                >
                    🔍 Filter
                </button>
            </div>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="text-center py-8">
            <p class="text-lg text-gray-600">⏳ Memuat pesanan...</p>
        </div>

        <!-- Table -->
        <div v-else class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="space-y-4">
                <div
                    v-for="order in orders"
                    :key="order.id"
                    class="bg-white rounded-3xl shadow-lg border border-slate-100 p-6"
                >
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-xl font-bold">
                                {{ order.order_number }}
                            </h3>

                            <p class="text-slate-500 mt-1">
                                {{ order.customer_name }}
                            </p>

                            <p class="text-sm text-slate-400">
                                {{ formatDate(order.created_at) }}
                            </p>
                        </div>

                        <span
                            :class="[
                                'px-4 py-2 rounded-full text-white text-sm font-semibold',
                                getStatusColor(order.status),
                            ]"
                        >
                            {{ getStatusLabel(order.status) }}
                        </span>
                    </div>

                    <div class="grid md:grid-cols-4 gap-4 mt-6">
                        <div>
                            <p class="text-slate-500 text-sm">Total Item</p>

                            <p class="font-bold text-lg">
                                {{ order.items?.length || 0 }}
                            </p>
                        </div>

                        <div>
                            <p class="text-slate-500 text-sm">Pembayaran</p>

                            <p class="font-bold">
                                {{ order.payment_method }}
                            </p>
                        </div>

                        <div>
                            <p class="text-slate-500 text-sm">Total Belanja</p>

                            <p class="font-bold text-emerald-600">
                                Rp{{ formatPrice(order.total_amount) }}
                            </p>
                        </div>

                        <div>
                            <p class="text-slate-500 text-sm">Kota</p>

                            <p class="font-bold">
                                {{ order.delivery_city }}
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-3 mt-6">
                        <button
                            @click="viewDetail(order)"
                            class="px-4 py-2 bg-sky-600 text-white rounded-xl"
                        >
                            Detail
                        </button>

                        <button
                            v-if="order.status === 'pending'"
                            @click="updateOrderStatus(order, 'diproses')"
                            class="px-4 py-2 bg-amber-600 text-white rounded-xl"
                        >
                            Proses
                        </button>

                        <button
                            v-if="order.status === 'diproses'"
                            @click="updateOrderStatus(order, 'dikirim')"
                            class="px-4 py-2 bg-purple-600 text-white rounded-xl"
                        >
                            Kirim
                        </button>

                        <button
                            v-if="order.status === 'dikirim'"
                            @click="updateOrderStatus(order, 'selesai')"
                            class="px-4 py-2 bg-green-600 text-white rounded-xl"
                        >
                            Selesai
                        </button>

                        <button
                            v-if="
                                order.status !== 'selesai' &&
                                order.status !== 'dibatalkan'
                            "
                            @click="updateOrderStatus(order, 'dibatalkan')"
                            class="px-4 py-2 bg-red-600 text-white rounded-xl"
                        >
                            Batalkan
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detail Modal -->
        <div
            v-if="selectedOrder"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
        >
            <div
                class="bg-white rounded-lg shadow-lg p-8 max-w-2xl w-full max-h-96 overflow-y-auto"
            >
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold">Detail Pesanan</h2>
                    <button
                        @click="selectedOrder = null"
                        class="text-gray-600 hover:text-gray-800 font-bold text-2xl"
                    >
                        ×
                    </button>
                </div>

                <!-- Order Header -->
                <div class="grid grid-cols-2 gap-4 mb-6 pb-4 border-b">
                    <div>
                        <p class="text-sm text-gray-600">No. Pesanan</p>
                        <p class="font-bold">
                            {{ selectedOrder.order_number }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Tanggal</p>
                        <p class="font-bold">
                            {{ formatDate(selectedOrder.created_at) }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Customer</p>
                        <p class="font-bold">{{ selectedOrder.user?.name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Status</p>
                        <p
                            :class="
                                'font-bold ' +
                                (selectedOrder.status === 'selesai'
                                    ? 'text-green-600'
                                    : 'text-yellow-600')
                            "
                        >
                            {{ getStatusLabel(selectedOrder.status) }}
                        </p>
                    </div>
                </div>

                <!-- Shipping Info -->
                <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                    <h3 class="font-bold mb-2">📍 Alamat Pengiriman</h3>
                    <p class="text-sm">{{ selectedOrder.delivery_address }}</p>
                    <p class="text-sm">{{ selectedOrder.delivery_city }}</p>
                </div>

                <!-- Items -->
                <div class="mb-6">
                    <h3 class="font-bold mb-3">📦 Items:</h3>
                    <div class="space-y-2">
                        <div
                            v-for="item in selectedOrder.items"
                            :key="item.id"
                            class="flex justify-between p-2 bg-gray-50 rounded"
                        >
                            <div>
                                <p class="font-semibold text-sm">
                                    {{ item.medicine.name }}
                                </p>
                                <p class="text-xs text-gray-600">
                                    {{ item.quantity }} x Rp{{
                                        formatPrice(item.unit_price)
                                    }}
                                </p>
                            </div>
                            <p class="font-semibold text-green-600">
                                Rp{{ formatPrice(item.subtotal) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Total -->
                <div class="border-t pt-4 mb-6">
                    <div class="flex justify-between text-lg font-bold">
                        <span>Total</span>
                        <span class="text-green-600"
                            >Rp{{
                                formatPrice(selectedOrder.total_amount)
                            }}</span
                        >
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-2">
                    <button
                        v-if="selectedOrder.status === 'pending'"
                        @click="confirmUpdateStatus('diproses')"
                        class="flex-1 px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition font-semibold"
                    >
                        📦 Proses Pesanan
                    </button>
                    <button
                        @click="selectedOrder = null"
                        class="flex-1 px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400 transition font-semibold"
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
import { ElMessage, ElMessageBox } from "element-plus";

const orders = ref([]);
const loading = ref(false);
const selectedOrder = ref(null);
const filterStatus = ref("");
const filterPayment = ref("");
const filterDate = ref(dayjs().format("YYYY-MM"));
const searchQuery = ref("");

const formatPrice = (price) => {
    return new Intl.NumberFormat("id-ID").format(price);
};

const formatDate = (date) => {
    return dayjs(date).format("DD/MM/YYYY HH:mm");
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
        pending: "⏳ Pending",
        diproses: "📦 Diproses",
        dikirim: "🚚 Dikirim",
        selesai: "✅ Selesai",
        dibatalkan: "❌ Dibatalkan",
    };
    return labels[status] || status;
};

const fetchOrders = async () => {
    loading.value = true;
    try {
        const params = { per_page: 100 };
        if (filterStatus.value) params.status = filterStatus.value;
        if (filterPayment.value) params.payment_method = filterPayment.value;
        if (filterDate.value) {
            const [year, month] = filterDate.value.split("-");
            params.start_date = `${year}-${month}-01`;
            params.end_date = dayjs(`${year}-${month}-01`)
                .endOf("month")
                .format("YYYY-MM-DD");
        }
        if (searchQuery.value) params.search = searchQuery.value;

        const response = await api.get("staff/orders", {
            params,
        });
        console.log(response.data);
        orders.value = response.data.data.data;
        console.log("ORDERS =", orders.value);
    } catch (error) {
        ElMessage.error("Gagal memuat pesanan");
    } finally {
        loading.value = false;
    }
};

const viewDetail = (order) => {
    selectedOrder.value = order;
};

const updateOrderStatus = async (order, newStatus) => {
    try {
        await ElMessageBox.confirm(
            `Ubah status pesanan menjadi ${getStatusLabel(newStatus)}?`,
            "Konfirmasi",
            {
                confirmButtonText: "Ya, Ubah",
                cancelButtonText: "Batal",
                type: "warning",
            },
        );

        await api.put(`orders/${order.id}`, { status: newStatus });
        ElMessage.success("Status pesanan berhasil diubah");
        fetchOrders();
        selectedOrder.value = null;
    } catch (error) {
        console.log(error.response?.data);

        ElMessage.error(
            error.response?.data?.message || "Gagal mengubah status",
        );
    }
};

const confirmUpdateStatus = (status) => {
    updateOrderStatus(selectedOrder.value, status);
};

onMounted(() => {
    fetchOrders();
});
</script>
