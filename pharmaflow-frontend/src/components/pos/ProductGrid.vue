<template>
    <div class="bg-white rounded-3xl p-6 border shadow-sm">
        <h2 class="text-xl font-bold mb-4">Produk</h2>

        <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
            <div
                v-for="product in filteredProducts"
                :key="product.id"
                class="border rounded-2xl p-4 hover:border-emerald-500 transition"
            >
                <h3 class="font-semibold">
                    {{ product.name }}
                </h3>

                <p class="mt-2">Rp{{ formatPrice(product.price) }}</p>

                <p class="text-sm text-gray-500">Stock {{ product.stock }}</p>

                <button
                    @click="addProduct(product)"
                    :disabled="product.stock <= 0"
                    class="mt-4 w-full bg-emerald-500 text-white py-2 rounded-xl disabled:bg-gray-300"
                >
                    {{ product.stock <= 0 ? "Habis" : "Tambah" }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import api from "@/services/api";
import { ref, onMounted, computed } from "vue";

const products = ref([]);

const fetchProducts = async () => {
    try {
        const response = await api.get("/medicines");

        console.log("FULL RESPONSE:", response.data);
        console.log("DATA:", response.data.data);

        const medicines = response.data?.data?.data || [];

        products.value = medicines.map((item) => ({
            id: item.id,
            name: item.name,
            price: Number(item.selling_price),
            stock: Number(item.total_stock ?? 0),
        }));
    } catch (error) {
        console.error(error);
    }
};

const props = defineProps({
    search: String,
});

const emit = defineEmits(["add-to-cart"]);

const addProduct = (product) => {
    console.log("PRODUCT CLICKED:", product);
    emit("add-to-cart", product);
};

const filteredProducts = computed(() => {
    if (!props.search) return products.value;

    return products.value.filter((product) =>
        product.name.toLowerCase().includes(props.search.toLowerCase()),
    );
});

const formatPrice = (value) => new Intl.NumberFormat("id-ID").format(value);

onMounted(() => {
    fetchProducts();
});
defineExpose({
    fetchProducts,
});
</script>
