<template>
    <div class="space-y-6">
        <div
            class="relative overflow-hidden rounded-[32px] bg-gradient-to-r from-slate-900 via-slate-800 to-slate-700 p-8 text-white shadow-xl"
        >
            <div
                class="absolute -right-20 -top-20 w-72 h-72 rounded-full bg-white/5"
            ></div>

            <div class="relative flex justify-between items-center">
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
                                d="M20 7L4 7M20 12L4 12M20 17L4 17"
                            />
                        </svg>

                        Rack Storage Management
                    </span>

                    <h1 class="text-4xl font-bold mt-4">Manajemen Rak</h1>

                    <p class="text-slate-300 mt-2">
                        Kelola lokasi penyimpanan obat, kapasitas rak, dan
                        distribusi stok.
                    </p>
                </div>

                <button
                    @click="openForm()"
                    class="h-14 px-6 rounded-2xl bg-emerald-500 hover:bg-emerald-600 font-semibold flex items-center gap-2"
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

                    Tambah Rak
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">
            <div
                class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm"
            >
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-slate-500 text-sm">Total Rak</p>
                        <h2 class="text-4xl font-bold text-slate-900 mt-2">
                            {{ shelves.length }}
                        </h2>
                    </div>

                    <div
                        class="w-12 h-12 rounded-2xl bg-blue-100 flex items-center justify-center"
                    >
                        <svg
                            class="w-6 h-6 text-blue-600"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M20 7H4M20 12H4M20 17H4"
                            />
                        </svg>
                    </div>
                </div>
            </div>
            <div
                class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm"
            >
                <p class="text-slate-500 text-sm">Rak Aktif</p>

                <h2 class="text-4xl font-bold text-emerald-600 mt-2">
                    {{ activeShelves }}
                </h2>
            </div>
            <div
                class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm"
            >
                <p class="text-slate-500 text-sm">Total SKU</p>

                <h2 class="text-4xl font-bold text-blue-600 mt-2">
                    {{ totalStocks }}
                </h2>
            </div>
            <div
                class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm"
            >
                <p class="text-slate-500 text-sm">Utilisasi</p>

                <h2 class="text-4xl font-bold text-purple-600 mt-2">
                    {{ avgUtilization }}%
                </h2>
            </div>
        </div>

        <!-- Filter -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <div>
                    <label
                        class="block text-sm font-semibold text-slate-700 mb-2"
                    >
                        Gudang
                    </label>

                    <select
                        v-model="filterWarehouse"
                        @change="fetchShelves"
                        class="w-full h-14 rounded-2xl border border-slate-200 px-4"
                    >
                        <option value="">Semua Gudang</option>

                        <option
                            v-for="wh in warehouses"
                            :key="wh.id"
                            :value="wh.id"
                        >
                            {{ wh.name }}
                        </option>
                    </select>
                </div>

                <div>
                    <label
                        class="block text-sm font-semibold text-slate-700 mb-2"
                    >
                        Pencarian
                    </label>

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
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                            />
                        </svg>

                        <input
                            v-model="searchQuery"
                            @keyup.enter="fetchShelves"
                            placeholder="Cari rak..."
                            class="w-full h-14 pl-12 rounded-2xl border border-slate-200"
                        />
                    </div>
                </div>

                <div class="flex items-end">
                    <button
                        @click="fetchShelves"
                        class="w-full h-14 rounded-2xl bg-emerald-500 hover:bg-emerald-600 text-white font-semibold"
                    >
                        Cari Data
                    </button>
                </div>
            </div>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="text-center py-8">
            <p class="text-lg text-gray-600">⏳ Memuat rak...</p>
        </div>

        <!-- Grid -->
        <div
            v-else
            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
        >
            <div
                v-for="shelf in shelves"
                :key="shelf.id"
                class="bg-white rounded-[28px] border border-slate-200 shadow-sm hover:shadow-xl transition-all duration-300 p-6"
            >
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <p class="text-sm text-gray-600">Kode</p>
                        <p class="text-2xl font-bold text-blue-600">
                            {{ shelf.code }}
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <button
                            @click="openForm(shelf)"
                            class="w-10 h-10 rounded-xl bg-orange-50 hover:bg-orange-100 flex items-center justify-center"
                        >
                            <svg
                                class="w-5 h-5 text-orange-600"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"
                                />
                            </svg>
                        </button>

                        <button
                            @click="deleteShelf(shelf.id)"
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
                                    d="M19 7L5 7M10 11V17M14 11V17M6 7L7 19A2 2 0 009 21H15A2 2 0 0017 19L18 7M9 7V4A1 1 0 0110 3H14A1 1 0 0115 4V7"
                                />
                            </svg>
                        </button>
                    </div>
                </div>

                <h3 class="text-xl font-bold mb-2">{{ shelf.name }}</h3>

                <div class="flex gap-2 mt-4">
                    <span
                        class="px-3 py-1 rounded-full bg-indigo-100 text-indigo-700 text-xs font-semibold"
                    >
                        {{ shelf.stocks_count || 0 }} SKU
                    </span>

                    <span
                        class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-semibold"
                    >
                        {{ shelf.stocks_count || 0 }}/{{ shelf.capacity || 0 }}
                    </span>
                </div>
                <p class="text-gray-600 text-sm mb-4">
                    {{ shelf.description }}
                </p>

                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Gudang:</span>
                        <span class="font-semibold">{{
                            shelf.warehouse?.name
                        }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Kapasitas:</span>
                        <span class="font-semibold">{{
                            shelf.capacity || "-"
                        }}</span>
                    </div>
                    <div class="mt-5">
                        <div class="flex justify-between text-xs mb-2">
                            <span>Utilisasi Rak</span>

                            <span>
                                {{
                                    Math.round(
                                        ((shelf.stocks_count || 0) /
                                            (shelf.capacity || 1)) *
                                            100,
                                    )
                                }}%
                            </span>
                        </div>

                        <div class="h-2 bg-slate-200 rounded-full">
                            <div
                                class="h-2 rounded-full bg-emerald-500"
                                :style="{
                                    width:
                                        Math.min(
                                            ((shelf.stocks_count || 0) /
                                                (shelf.capacity || 1)) *
                                                100,
                                            100,
                                        ) + '%',
                                }"
                            />
                        </div>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Status:</span>
                        <span
                            :class="[
                                shelf.status === 'aktif'
                                    ? 'bg-emerald-100 text-emerald-700'
                                    : 'bg-red-100 text-red-700',

                                'px-4 py-2 rounded-full text-xs font-bold',
                            ]"
                        >
                            {{ shelf.status }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Modal -->
        <div
            v-if="showForm"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
        >
            <div class="bg-white rounded-lg shadow-lg p-8 max-w-md w-full">
                <h2 class="text-2xl font-bold mb-6">
                    {{ editingId ? "✏️ Edit Rak" : "➕ Tambah Rak" }}
                </h2>

                <form @submit.prevent="saveShelf" class="space-y-4">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2"
                            >Gudang *</label
                        >
                        <select
                            v-model.number="form.warehouse_id"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                            required
                        >
                            <option value="">Pilih Gudang</option>
                            <option
                                v-for="wh in warehouses"
                                :key="wh.id"
                                :value="wh.id"
                            >
                                {{ wh.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2"
                            >Kode Rak *</label
                        >
                        <input
                            v-model="form.code"
                            type="text"
                            placeholder="A1, A2, B1, dll"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                            required
                        />
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2"
                            >Nama Rak *</label
                        >
                        <input
                            v-model="form.name"
                            type="text"
                            placeholder="Rak Antibiotik, dll"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                            required
                        />
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2"
                            >Deskripsi</label
                        >
                        <textarea
                            v-model="form.description"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 h-20"
                        ></textarea>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2"
                            >Kapasitas</label
                        >
                        <input
                            v-model.number="form.capacity"
                            type="number"
                            min="0"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                        />
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
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import api from "@/services/api";
import { ElMessage, ElMessageBox } from "element-plus";

const shelves = ref([]);
const warehouses = ref([]);
const loading = ref(false);
const savingForm = ref(false);
const showForm = ref(false);
const editingId = ref(null);
const filterWarehouse = ref("");
const searchQuery = ref("");


const form = ref({
    warehouse_id: "",
    code: "",
    name: "",
    description: "",
    capacity: "",
});

const fetchShelves = async () => {
    loading.value = true;
    try {
        const params = { per_page: 100 };
        if (filterWarehouse.value) params.warehouse_id = filterWarehouse.value;
        if (searchQuery.value) params.search = searchQuery.value;

        const response = await api.get("shelves", { params });
        shelves.value = response.data.data.data || [];
       console.log(response.data.data.data[0].stocks_count);
        console.log("SHELVES", shelves.value);
    } catch (error) {
        ElMessage.error("Gagal memuat rak");
    } finally {
        loading.value = false;
    }
};

const fetchWarehouses = async () => {
    try {
        const response = await api.get("warehouses?per_page=100");
        warehouses.value = response.data.data.data || [];
    } catch (error) {
        console.error("Failed to fetch warehouses:", error);
    }
};

const openForm = (shelf = null) => {
    if (shelf) {
        form.value = { ...shelf };
        editingId.value = shelf.id;
    } else {
        form.value = {
            warehouse_id: "",
            code: "",
            name: "",
            description: "",
            capacity: "",
        };
        editingId.value = null;
    }
    showForm.value = true;
};

const saveShelf = async () => {
    savingForm.value = true;
    try {
        if (editingId.value) {
            await api.put(`shelves/${editingId.value}`, form.value);
            ElMessage.success("Rak berhasil diperbarui");
        } else {
            await api.post("shelves", form.value);
            ElMessage.success("Rak berhasil ditambahkan");
        }

        showForm.value = false;
        fetchShelves();
    } catch (error) {
        ElMessage.error(error.response?.data?.message || "Gagal menyimpan rak");
    } finally {
        savingForm.value = false;
    }
};

const activeShelves = computed(
    () => shelves.value.filter((s) => s.status === "aktif").length,
);

const totalStocks = computed(() =>
    shelves.value.reduce(
        (sum, shelf) => sum + Number(shelf.stocks_count || 0),
        0,
    ),
);

const avgUtilization = computed(() => {
    const totalUsed = shelves.value.reduce(
        (sum, shelf) => sum + (shelf.stocks_count || 0),
        0,
    );

    const totalCapacity = shelves.value.reduce(
        (sum, shelf) => sum + (shelf.capacity || 0),
        0,
    );

    if (!totalCapacity) return 0;

    return Math.round((totalUsed / totalCapacity) * 100);
});

const deleteShelf = async (id) => {
    try {
        await ElMessageBox.confirm(
            "Apakah Anda yakin ingin menghapus rak ini?",
            "Warning",
            {
                confirmButtonText: "Ya, Hapus",
                cancelButtonText: "Batal",
                type: "warning",
            },
        );

        await api.delete(`shelves/${id}`);
        ElMessage.success("Rak berhasil dihapus");
        fetchShelves();
    } catch (error) {
        if (error !== "cancel") {
            ElMessage.error("Gagal menghapus rak");
        }
    }
};

onMounted(() => {
    fetchWarehouses();
    fetchShelves();
});
</script>
