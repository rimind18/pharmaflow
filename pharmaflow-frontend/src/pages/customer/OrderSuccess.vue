<template>
    <div class="min-h-screen bg-slate-50">
        <!-- HERO -->
        <div class="bg-gradient-to-r from-emerald-600 to-green-500 text-white">
            <div class="max-w-4xl mx-auto px-6 py-12 text-center">
                <div
                    class="w-24 h-24 mx-auto rounded-full bg-white text-emerald-600 flex items-center justify-center text-5xl font-bold shadow-lg"
                >
                    ✓
                </div>

                <h1 class="text-4xl font-bold mt-6">Pesanan Berhasil Dibuat</h1>

                <p class="mt-3 text-green-100">
                    Terima kasih telah berbelanja di PharmaFlow
                </p>
            </div>
        </div>

        <!-- CONTENT -->
        <div class="max-w-4xl mx-auto px-6 py-8">
            <!-- ORDER CARD -->
            <div class="bg-white rounded-2xl shadow-sm border p-8">
                <h2 class="text-xl font-bold mb-6">Detail Pesanan</h2>

                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-gray-500">Nomor Pesanan</p>

                        <p class="font-bold text-lg text-emerald-600">
                            {{ orderNumber }}
                        </p>

                        <button
                            @click="copyOrderNumber"
                            class="text-xs text-blue-600 hover:underline"
                        >
                            Salin Nomor Pesanan
                        </button>
                    </div>

                    <div>
                        <p class="text-gray-500">Nama Pelanggan</p>

                        <p class="font-semibold">
                            {{ customerName }}
                        </p>
                    </div>

                    <div>
                        <p class="text-gray-500">Nomor WhatsApp</p>

                        <p class="font-semibold">
                            {{ customerPhone }}
                        </p>
                    </div>

                    <div>
                        <p class="text-gray-500">Total Pembayaran</p>

                        <p class="font-bold text-2xl text-emerald-600">
                            Rp{{ formatPrice(totalAmount) }}
                        </p>
                    </div>
                </div>
            </div>

            <div>
                <p class="text-gray-500">Status Pembayaran</p>

                <span
                    class="inline-flex px-3 py-1 rounded-full text-sm font-semibold"
                    :class="{
                        'bg-yellow-100 text-yellow-700':
                            paymentStatus === 'pending',

                        'bg-green-100 text-green-700':
                            paymentStatus === 'completed',

                        'bg-red-100 text-red-700': paymentStatus === 'failed',
                    }"
                >
                    {{
                        paymentStatus === "completed"
                            ? "Lunas"
                            : paymentStatus === "failed"
                              ? "Gagal"
                              : "Menunggu Pembayaran"
                    }}
                </span>
            </div>

            <!-- TIMELINE -->
            <div class="bg-white rounded-2xl shadow-sm border p-8 mt-6">
                <h2 class="text-xl font-bold mb-6">Status Pesanan</h2>

                <div class="space-y-6">
                    <div class="flex gap-4">
                        <div
                            class="w-10 h-10 rounded-full bg-green-500 text-white flex items-center justify-center"
                        >
                            ✓
                        </div>

                        <div>
                            <h3 class="font-bold">Pesanan Diterima</h3>

                            <p class="text-gray-500 text-sm">
                                Pesanan berhasil dibuat
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <div
                            class="w-10 h-10 rounded-full bg-slate-300 text-white flex items-center justify-center"
                        >
                            2
                        </div>

                        <div>
                            <h3 class="font-bold text-slate-600">Diproses</h3>

                            <p class="text-gray-500 text-sm">
                                Tim apotek sedang menyiapkan pesanan
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <div
                            class="w-10 h-10 rounded-full bg-slate-300 text-white flex items-center justify-center"
                        >
                            3
                        </div>

                        <div>
                            <h3 class="font-bold text-slate-600">Dikirim</h3>

                            <p class="text-gray-500 text-sm">
                                Pesanan sedang dikirim
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <div
                            class="w-10 h-10 rounded-full bg-slate-300 text-white flex items-center justify-center"
                        >
                            4
                        </div>

                        <div>
                            <h3 class="font-bold text-slate-600">Selesai</h3>

                            <p class="text-gray-500 text-sm">
                                Pesanan diterima
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ACTION BUTTON -->
            <div class="grid md:grid-cols-2 gap-4 mt-6">
                <router-link
                    to="/"
                    class="bg-white border rounded-xl p-4 text-center font-semibold hover:bg-slate-50"
                >
                    Lanjut Belanja
                </router-link>

                <router-link
                    :to="{
                        name: 'OrderTracking',
                        query: {
                            order_number: orderNumber,
                            phone: customerPhone,
                        },
                    }"
                    class="bg-blue-600 text-white rounded-xl p-4 text-center font-semibold hover:bg-blue-700"
                >
                    Lacak Pesanan
                </router-link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRoute } from "vue-router";
import api from "@/services/api";
import { ElMessage } from "element-plus";

const route = useRoute();

const orderNumber = ref("");
const customerPhone = ref("");
const customerName = ref("");
const totalAmount = ref(0);
const orderId = ref(route.query.id || null);
const paymentStatus = ref("pending");

const formatPrice = (price) => {
    return new Intl.NumberFormat("id-ID").format(price);
};

const loadOrder = async () => {
    try {
        if (!orderNumber.value) return;

        const response = await api.get(`orders/${orderNumber.value}`);

        const order = response.data.data || response.data;

        customerName.value = order.customer_name;

        customerPhone.value = order.customer_phone;

        totalAmount.value = order.total_amount;

        paymentStatus.value = order.payment_status || "pending";
    } catch (error) {
        console.error("Load order failed", error);
    }
};
const copyOrderNumber = async () => {
    await navigator.clipboard.writeText(orderNumber.value);

    ElMessage.success("Nomor pesanan berhasil disalin");
};

onMounted(async () => {
    orderNumber.value = route.query.order_number || "";

    customerPhone.value = route.query.phone || "";

    await loadOrder();

    const lastOrder = localStorage.getItem("last_order");

    if (!lastOrder) return;

    try {
        const order = JSON.parse(lastOrder);

        customerName.value =
            customerName.value || order.customer_name || "Pelanggan";

        totalAmount.value =
            totalAmount.value || Number(order.total_amount) || 0;
    } catch (error) {
        console.error(error);
    }
});
</script>
