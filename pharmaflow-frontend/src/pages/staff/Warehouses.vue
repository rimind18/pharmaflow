<template>
    <div class="space-y-6">
        <div
            class="relative overflow-hidden rounded-[32px] bg-gradient-to-r from-slate-900 via-slate-800 to-slate-700 p-8 text-white shadow-xl"
        >
            <div
                class="absolute -right-20 -top-20 w-72 h-72 rounded-full bg-white/5"
            />

            <div
                class="relative flex flex-col xl:flex-row xl:items-center xl:justify-between gap-6"
            >
                <div>
                    <span
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 text-sm"
                    >
                        <svg
                            class="w-4 h-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M3 7l9-4 9 4m-18 0v10l9 4m-9-14l9 4m9-4v10m-9-6v10"
                            />
                        </svg>

                        Inventory Warehouse Management
                    </span>

                    <h1 class="text-4xl font-bold mt-4">Manajemen Gudang</h1>

                    <p class="text-slate-300 mt-3 max-w-2xl">
                        Kelola lokasi penyimpanan, rak, kapasitas operasional,
                        dan distribusi stok obat Apotik RHD Farma.
                    </p>
                </div>

                <button
                    @click="openForm()"
                    class="h-14 px-6 rounded-2xl bg-emerald-500 hover:bg-emerald-600 transition font-semibold flex items-center gap-2"
                >
                    <svg
                        class="w-5 h-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 4v16m8-8H4"
                        />
                    </svg>

                    Tambah Gudang
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">
            <!-- TOTAL -->
            <div
                class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm"
            >
                <p class="text-slate-500 text-sm">Total Gudang</p>

                <h2 class="text-4xl font-bold text-slate-900 mt-2">
                    {{ warehouses.length }}
                </h2>
            </div>

            <!-- ACTIVE -->
            <div
                class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm"
            >
                <p class="text-slate-500 text-sm">Gudang Aktif</p>

                <h2 class="text-4xl font-bold text-emerald-600 mt-2">
                    {{ activeWarehouseCount }}
                </h2>
            </div>

            <!-- SHELVES -->
            <div
                class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm"
            >
                <p class="text-slate-500 text-sm">Total Rak</p>

                <h2 class="text-4xl font-bold text-blue-600 mt-2">
                    {{ totalShelves }}
                </h2>
            </div>

            <!-- SKU -->
            <div
                class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm"
            >
                <p class="text-slate-500 text-sm">Total SKU</p>

                <h2 class="text-4xl font-bold text-purple-600 mt-2">
                    {{ totalStocks }}
                </h2>
            </div>
        </div>

        <!-- Search & Filter -->
        <div class="bg-white rounded-3xl border border-slate-200 p-5 shadow-sm">
            <div class="relative">
                <svg
                    class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0
                   7 7 0 0114 0z"
                    />
                </svg>

                <input
                    v-model="searchQuery"
                    placeholder="Cari gudang..."
                    @keyup.enter="fetchWarehouses"
                    class="w-full h-14 pl-12 pr-4 rounded-2xl border border-slate-200 focus:ring-4 focus:ring-emerald-100 focus:border-emerald-500"
                />
            </div>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="text-center py-8">
            <p class="text-lg text-gray-600">⏳ Memuat gudang...</p>
        </div>

        <!-- Table -->
        <div
            v-else
            class="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-sm"
        >
            <table class="w-full">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left font-semibold">
                            Nama Gudang
                        </th>

                        <th class="px-6 py-3 text-left font-semibold">
                            Alamat
                        </th>

                        <th class="px-6 py-3 text-center font-semibold">Rak</th>

                        <th class="px-6 py-3 text-center font-semibold">
                            SKU/Stok
                        </th>

                        <th class="px-6 py-3 text-center font-semibold">
                            Status
                        </th>

                        <th class="px-6 py-3 text-center font-semibold">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <tr
                        v-for="warehouse in warehouses"
                        :key="warehouse.id"
                        class="hover:bg-slate-50 border-b"
                    >
                        <td class="px-6 py-4">
                            <div>
                                <p class="font-semibold text-slate-900">
                                    {{ warehouse.name }}
                                </p>

                                <p
                                    class="text-xs text-slate-500"
                                    v-if="warehouse.description"
                                >
                                    {{ warehouse.description }}
                                </p>
                            </div>
                        </td>

                        <td class="px-6 py-4">
                            {{ warehouse.address || "-" }}
                        </td>

                        <td class="px-6 py-4 text-center">
                            <span
                                class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-sm font-semibold"
                            >
                                {{ warehouse.shelves_count || 0 }}
                            </span>
                        </td>

                        <td class="px-6 py-4 text-center">
                            <span
                                class="px-3 py-1 rounded-full bg-indigo-100 text-indigo-700 text-sm font-semibold"
                            >
                                {{ warehouse.stocks_count || 0 }}
                            </span>
                        </td>

                        <td class="px-6 py-4 text-center">
                            <span
                                :class="[
                                    warehouse.is_active
                                        ? 'bg-green-100 text-green-700'
                                        : 'bg-red-100 text-red-700',
                                    'px-3 py-1 rounded-full text-sm font-semibold',
                                ]"
                            >
                                {{ warehouse.is_active ? "Aktif" : "Nonaktif" }}
                            </span>
                        </td>

                        <td class="px-6 py-4 text-center">
                            <div class="flex justify-center gap-2">
                                <div class="flex justify-center gap-2">
                                    <button
                                        @click="openDetail(warehouse.id)"
                                        class="w-10 h-10 rounded-xl bg-blue-50 hover:bg-blue-100 flex items-center justify-center"
                                    >
                                        <svg
                                            class="w-5 h-5 text-blue-600"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0
                3 3 0 016 0z"
                                            />
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M2.458 12
                C3.732 7.943
                7.523 5
                12 5
                c4.478 0
                8.268 2.943
                9.542 7
                -1.274 4.057
                -5.064 7
                -9.542 7
                -4.477 0
                -8.268-2.943
                -9.542-7z"
                                            />
                                        </svg>
                                    </button>

                                    <button
                                        @click="openForm(warehouse)"
                                        class="w-10 h-10 rounded-xl bg-orange-50 hover:bg-orange-100 flex items-center justify-center"
                                    >
                                        ✏️
                                    </button>
                                </div>

                                <button
                                    @click="deleteWarehouse(warehouse.id)"
                                    class="w-10 h-10 rounded-xl bg-red-50 hover:bg-red-100 flex items-center justify-center"
                                >
                                    <svg
                                        class="w-5 h-5 text-red-600"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M19 7L5 7M10 11v6M14 11v6M6 7l1 12a2 2 0 002 2h6a2 2 0 002-2l1-12M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"
                                        />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Form Modal -->
        <div
            v-if="showForm"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
        >
            <div
                class="bg-white rounded-lg shadow-lg p-8 max-w-2xl w-full max-h-96 overflow-y-auto"
            >
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold">
                        {{ editingId ? "✏️ Edit Gudang" : "➕ Tambah Gudang" }}
                    </h2>
                    <button
                        @click="showForm = false"
                        class="text-gray-600 hover:text-gray-800 font-bold text-2xl"
                    >
                        ×
                    </button>
                </div>

                <form @submit.prevent="saveWarehouse" class="space-y-4">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2"
                            >Nama Gudang *</label
                        >
                        <input
                            v-model="form.name"
                            type="text"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                            required
                        />
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">
                            Alamat
                        </label>

                        <textarea
                            v-model="form.address"
                            rows="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                        />
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">
                            Deskripsi
                        </label>

                        <textarea
                            v-model="form.description"
                            rows="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                        />
                    </div>

                    <div>
                        <label class="flex items-center gap-3">
                            <input type="checkbox" v-model="form.is_active" />

                            Gudang Aktif
                        </label>
                    </div>

                    <div class="flex gap-2 pt-4">
                        <button
                            type="submit"
                            :disabled="savingForm"
                            class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:bg-gray-400 transition font-semibold"
                        >
                            {{ savingForm ? "⏳ Saving..." : "✅ Simpan" }}
                        </button>
                        <button
                            type="button"
                            @click="showForm = false"
                            class="flex-1 px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400 transition font-semibold"
                        >
                            ❌ Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div>
            <div v-if="showDetail" class="fixed inset-0 z-50">
                <!-- Overlay -->

                <div
                    class="absolute inset-0 bg-black/40"
                    @click="showDetail = false"
                />

                <!-- Drawer -->

                <div
                    class="absolute right-0 top-0 h-full w-full md:w-[700px] bg-white shadow-2xl overflow-y-auto"
                >
                    <!-- HEADER -->

                    <div class="p-8 border-b flex justify-between">
                        <div>
                            <h2 class="text-3xl font-bold">
                                {{ selectedWarehouse?.name }}
                            </h2>

                            <p class="text-slate-500">Warehouse Detail</p>
                        </div>

                        <button @click="showDetail = false" class="text-3xl">
                            ×
                        </button>
                    </div>

                    <!-- TAB -->

                    <div class="flex gap-2 p-6">
                        <button
                            @click="activeTab = 'info'"
                            :class="[
                                activeTab === 'info'
                                    ? 'bg-emerald-600 text-white'
                                    : 'bg-slate-100',

                                'px-5 py-2 rounded-xl',
                            ]"
                        >
                            Informasi
                        </button>

                        <button
                            @click="activeTab = 'shelves'"
                            :class="[
                                activeTab === 'shelves'
                                    ? 'bg-emerald-600 text-white'
                                    : 'bg-slate-100',

                                'px-5 py-2 rounded-xl',
                            ]"
                        >
                            Rak
                        </button>

                        <button
                            @click="activeTab = 'stocks'"
                            :class="[
                                activeTab === 'stocks'
                                    ? 'bg-emerald-600 text-white'
                                    : 'bg-slate-100',

                                'px-5 py-2 rounded-xl',
                            ]"
                        >
                            Persediaan
                        </button>
                    </div>
                    <div v-if="activeTab === 'info'" class="p-6">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="grid grid-cols-2 gap-4 mb-5">
                                <div class="bg-emerald-50 rounded-2xl p-5">
                                    <p class="text-slate-500">Total Rak</p>

                                    <h3
                                        class="text-3xl font-bold text-emerald-600"
                                    >
                                        {{ selectedWarehouse?.shelves_count }}
                                    </h3>
                                </div>

                                <div class="bg-blue-50 rounded-2xl p-5">
                                    <p class="text-slate-500">Total SKU</p>

                                    <h3
                                        class="text-3xl font-bold text-blue-600"
                                    >
                                        {{ selectedWarehouse?.stocks_count }}
                                    </h3>
                                </div>
                            </div>
                            <div class="bg-slate-50 rounded-2xl p-5">
                                <p class="text-slate-500">Alamat</p>

                                <p class="font-semibold">
                                    {{ selectedWarehouse?.address || "-" }}
                                </p>
                            </div>

                            <div class="bg-slate-50 rounded-2xl p-5">
                                <p class="text-slate-500">Status</p>

                                <p class="font-semibold">
                                    {{
                                        selectedWarehouse?.is_active
                                            ? "Aktif"
                                            : "Nonaktif"
                                    }}
                                </p>
                            </div>

                            <div class="bg-slate-50 rounded-2xl p-5">
                                <p class="text-slate-500">Total Rak</p>

                                <h3 class="text-3xl font-bold">
                                    {{ selectedWarehouse?.shelves_count }}
                                </h3>
                            </div>

                            <div class="bg-slate-50 rounded-2xl p-5">
                                <p class="text-slate-500">Total SKU</p>

                                <h3 class="text-3xl font-bold">
                                    {{ selectedWarehouse?.stocks_count }}
                                </h3>
                            </div>
                        </div>
                    </div>
                    <div v-if="activeTab === 'shelves'" class="p-6">
                        <div
                            v-for="shelf in selectedWarehouse?.shelves"
                            :key="shelf.id"
                            class="border rounded-2xl p-4 mb-3"
                        >
                            <div class="flex justify-between">
                                <div>
                                    <h4 class="font-bold">
                                        {{ shelf.name }}
                                    </h4>

                                    <p class="text-slate-500">
                                        {{ shelf.code }}
                                    </p>
                                </div>

                                <span
                                    class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full"
                                >
                                    {{ shelf.capacity }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div v-if="activeTab === 'stocks'" class="p-6">
                        <div
                            v-for="stock in selectedWarehouse?.stocks"
                            :key="stock.id"
                            class="border rounded-2xl p-4 mb-3"
                        >
                            <div class="flex justify-between">
                                <div>
                                    <h4 class="font-semibold">
                                        Batch {{ stock.batch_number }}
                                    </h4>

                                    <p class="text-slate-500 text-sm">
                                        Exp:
                                        {{
                                            new Date(
                                                stock.expired_date,
                                            ).toLocaleDateString()
                                        }}
                                    </p>
                                </div>

                                <span
                                    class="px-4 py-2 rounded-full bg-emerald-100 text-emerald-700"
                                >
                                    {{ stock.quantity }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";

import api from "@/services/api";
import { ElMessage, ElMessageBox } from "element-plus";

const warehouses = ref([]);
const loading = ref(false);
const savingForm = ref(false);
const showForm = ref(false);
const editingId = ref(null);
const searchQuery = ref("");

const selectedWarehouse = ref(null);
const showDetail = ref(false);
const activeTab = ref("info");

const form = ref({
    name: "",
    address: "",
    description: "",
    is_active: true,
});

const openForm = (warehouse = null) => {
    if (warehouse) {
        form.value = {
            name: warehouse.name || "",
            address: warehouse.address || "",
            description: warehouse.description || "",
            is_active: warehouse.is_active ?? true,
        };

        editingId.value = warehouse.id;
    } else {
        form.value = {
            name: "",
            address: "",
            description: "",
            is_active: true,
        };

        editingId.value = null;
    }

    showForm.value = true;
};

const openDetail = async (warehouseId) => {
    try {
        const response = await api.get(`warehouses/${warehouseId}`);

        selectedWarehouse.value = response.data.data;

        activeTab.value = "info";

        showDetail.value = true;
    } catch (error) {
        ElMessage.error("Gagal memuat detail gudang");
    }
};

const activeWarehouseCount = computed(
    () => warehouses.value.filter((w) => w.is_active).length,
);

const totalShelves = computed(() =>
    warehouses.value.reduce((sum, w) => sum + Number(w.shelves_count || 0), 0),
);

const totalStocks = computed(() =>
    warehouses.value.reduce((sum, w) => sum + Number(w.stocks_count || 0), 0),
);

const fetchWarehouses = async () => {
    loading.value = true;

    try {
        const params = {
            per_page: 100,
        };

        if (searchQuery.value) {
            params.search = searchQuery.value;
        }

        const response = await api.get("warehouses", { params });

        warehouses.value =
            response.data?.data?.data || response.data?.data || [];
    } catch (error) {
        console.error(error);

        ElMessage.error("Gagal memuat gudang");
    } finally {
        loading.value = false;
    }
};

const saveWarehouse = async () => {
    savingForm.value = true;
    try {
        if (editingId.value) {
            await api.put(`warehouses/${editingId.value}`, form.value);
            ElMessage.success("Gudang berhasil diperbarui");
        } else {
            await api.post("warehouses", form.value);
            ElMessage.success("Gudang berhasil ditambahkan");
        }

        showForm.value = false;
        fetchWarehouses();
    } catch (error) {
        ElMessage.error(
            error.response?.data?.message || "Gagal menyimpan gudang",
        );
    } finally {
        savingForm.value = false;
    }
};

const deleteWarehouse = async (id) => {
    try {
        await ElMessageBox.confirm(
            "Apakah Anda yakin ingin menghapus gudang ini?",
            "Warning",
            {
                confirmButtonText: "Ya, Hapus",
                cancelButtonText: "Batal",
                type: "warning",
            },
        );

        await api.delete(`warehouses/${id}`);
        ElMessage.success("Gudang berhasil dihapus");
        fetchWarehouses();
    } catch (error) {
        if (error !== "cancel") {
            ElMessage.error("Gagal menghapus gudang");
        }
    }
};

onMounted(() => {
    fetchWarehouses();
});
</script>
