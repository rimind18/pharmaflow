<script setup>
import { computed } from "vue";

import {
    Chart,
    CategoryScale,
    LinearScale,
    BarElement,
    Tooltip,
    Legend,
} from "chart.js";

import { Bar } from "vue-chartjs";

Chart.register(CategoryScale, LinearScale, BarElement, Tooltip, Legend);

const props = defineProps({
    products: {
        type: Array,
        default: () => [],
    },
});

const chartData = computed(() => ({
    labels: props.products.map((p) => p.medicine?.name),

    datasets: [
        {
            label: "Qty Sold",

            data: props.products.map((p) => Number(p.qty_sold)),

            backgroundColor: "#10B981",
        },
    ],
}));
</script>
<template>
    <div class="bg-white p-6 rounded-2xl shadow">
        <h2 class="font-bold text-lg mb-4">Produk Terlaris</h2>

        <Bar :data="chartData" />
    </div>
</template>
