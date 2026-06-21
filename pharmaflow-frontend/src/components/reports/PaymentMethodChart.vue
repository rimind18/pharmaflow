<script setup>
import { computed } from "vue";

import { Chart, ArcElement, Tooltip, Legend } from "chart.js";

import { Pie } from "vue-chartjs";

Chart.register(ArcElement, Tooltip, Legend);

const props = defineProps({
    paymentMethods: {
        type: Array,
        default: () => [],
    },
});

const chartData = computed(() => ({
    labels: props.paymentMethods.map((item) => item.payment_method),

    datasets: [
        {
            data: props.paymentMethods.map((item) => Number(item.amount)),

            backgroundColor: [
                "#10B981",
                "#3B82F6",
                "#F59E0B",
                "#EF4444",
                "#8B5CF6",
            ],
        },
    ],
}));
</script>

<template>
    <div class="bg-white p-6 rounded-2xl shadow">
        <h2 class="font-bold text-lg mb-4">
            Metode Pembayaran
        </h2>

        <Pie :data="chartData" />
    </div>
</template>
