<template>
    <div class="max-w-6xl mx-auto px-6 py-10">
        <div v-if="loading">Memuat detail pesanan...</div>

        <div v-else-if="order">
            <!-- Header -->

            <div class="bg-white rounded-3xl shadow p-8 mb-6">
                <h1 class="text-3xl font-bold">
                    {{ order.order_number }}
                </h1>

                <div class="mt-3">
                    <span
                        class="px-4 py-2 rounded-full text-sm font-semibold"
                        :class="getStatusColor(order.status)"
                    >
                        {{ getStatusLabel(order.status) }}
                    </span>
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow p-8 mb-6">
                <h2 class="font-bold text-xl mb-6">Status Pesanan</h2>

                <div class="flex justify-between">
                    <div>
                        <div
                            :class="['w-10 h-10 rounded-full', getStepClass(1)]"
                        ></div>
                        <p>Order Dibuat</p>
                    </div>

                    <div>
                        <div
                            :class="['w-10 h-10 rounded-full', getStepClass(2)]"
                        ></div>
                        <p>Diproses</p>
                    </div>

                    <div>
                        <div
                            :class="['w-10 h-10 rounded-full', getStepClass(3)]"
                        ></div>
                        <p>Dikirim</p>
                    </div>

                    <div>
                        <div
                            :class="['w-10 h-10 rounded-full', getStepClass(4)]"
                        ></div>
                        <p>Selesai</p>
                    </div>
                </div>
            </div>

            <!-- Customer -->

            <div class="bg-white rounded-3xl shadow p-8 mb-6">
                <h2 class="font-bold text-xl mb-4">Informasi Pengiriman</h2>

                <p>
                    {{ order.customer_name }}
                </p>

                <p>
                    {{ order.customer_phone }}
                </p>

                <p>
                    {{ order.shipping_address }}
                </p>
                <p class="mt-2 text-gray-600">
                    {{ order.delivery_address }}
                </p>
            </div>

            <!-- Items -->

            <div class="bg-white rounded-3xl shadow p-8 mb-6">
                <h2 class="font-bold text-xl mb-6">Produk Pesanan</h2>

                <div
                    v-for="item in order.items"
                    :key="item.id"
                    class="flex justify-between py-4 border-b"
                >
                    <div>
                        <div class="font-semibold">
                            {{ item.medicine_name }}
                        </div>

                        <div class="text-gray-500">Qty {{ item.quantity }}</div>
                    </div>

                    <div class="font-bold">
                        Rp{{ formatPrice(item.subtotal) }}
                    </div>
                </div>
            </div>

            <!-- Total -->

            <div class="bg-white rounded-3xl shadow p-8">
                <div class="flex justify-between mb-3">
                    <span>Subtotal</span>

                    <span> Rp{{ formatPrice(order.subtotal) }} </span>
                </div>

                <div class="flex justify-between mb-3">
                    <span>Ongkir</span>

                    <span> Rp{{ formatPrice(order.shipping_cost) }} </span>
                </div>

                <div
                    class="flex justify-between text-2xl font-bold text-emerald-600"
                >
                    <span>Total</span>

                    <span> Rp{{ formatPrice(order.total_amount) }} </span>
                </div>
            </div>
            <div class="flex gap-4 mt-6">
                <button
                    @click="contactAdmin"
                    class="flex-1 bg-blue-600 text-white py-3 rounded-xl"
                >
                    Hubungi Admin
                </button>

                <button
                    @click="reorder"
                    class="flex-1 bg-emerald-600 text-white py-3 rounded-xl"
                >
                    Pesan Lagi
                </button>
            </div>
            <div class="bg-white rounded-3xl shadow p-8 mb-6">
                <h2 class="font-bold text-xl mb-4">Informasi Pembayaran</h2>

                <div class="flex justify-between">
                    <span>Metode</span>

                    <b>
                        {{ order.payment_method }}
                    </b>
                </div>

                <div class="flex justify-between mt-2">
                    <span>Status</span>

                    <b>
                        {{ getPaymentStatusLabel(order.payment_status) }}
                    </b>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import api from "@/services/api";
import { ElMessage } from "element-plus";
import { useCartStore } from "@/stores/cart";

const router = useRouter();

const cartStore = useCartStore();

const route = useRoute();

const loading = ref(false);
const order = ref(null);

const formatPrice = (price) => {
    return new Intl.NumberFormat("id-ID").format(price || 0);
};

const fetchOrder = async () => {
    loading.value = true;

    try {
        const orderNumber = route.params.order_number;

        console.log("ORDER NUMBER:", orderNumber);

        const response = await api.get(`/orders/${orderNumber}`);

        console.log("DETAIL RESPONSE", response.data);

        order.value = response.data.order;

        console.log("ORDER", order.value);
    } catch (error) {
        console.error(error);

        ElMessage.error("Gagal memuat detail pesanan");
    } finally {
        loading.value = false;
    }
};

const getStatusColor = (status) => {
    return {
        pending: "bg-yellow-100 text-yellow-700",

        diproses: "bg-blue-100 text-blue-700",

        dikirim: "bg-purple-100 text-purple-700",

        selesai: "bg-green-100 text-green-700",

        dibatalkan: "bg-red-100 text-red-700",
    }[status];
};

const getStatusLabel = (status) => {
    return {
        pending: "Menunggu",
        diproses: "Diproses",
        dikirim: "Dikirim",
        selesai: "Selesai",
        dibatalkan: "Dibatalkan",
    }[status];
};

const getStepClass = (step) => {
    const statusMap = {
        pending: 1,
        diproses: 2,
        dikirim: 3,
        selesai: 4,
    };

    if (order.value.status === "dibatalkan") {
        return "bg-red-500";
    }

    return statusMap[order.value.status] >= step
        ? "bg-green-500"
        : "bg-gray-300";
};

const getPaymentStatusLabel = (status) => {
    return (
        {
            pending: "Menunggu",
            paid: "Lunas",
            cancelled: "Dibatalkan",
        }[status] || status
    );
};

const contactAdmin = () => {
    const message = `Halo Admin, saya ingin menanyakan pesanan ${order.value.order_number}`;

    window.open(
        `https://wa.me/6281234567890?text=${encodeURIComponent(message)}`,
        "_blank",
    );
};

const reorder = () => {
    order.value.items.forEach((item) => {
        for (let i = 0; i < item.quantity; i++) {
            cartStore.addToCart({
                id: item.medicine_id,
                name: item.medicine_name,
                selling_price: item.unit_price,
            });
        }
    });

    router.push("/cart");

    ElMessage.success("Produk berhasil dimasukkan ke keranjang");
};

onMounted(() => {
    fetchOrder();
});
</script>
