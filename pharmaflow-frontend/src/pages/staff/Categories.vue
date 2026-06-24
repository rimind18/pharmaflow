<template>
    <div class="space-y-6">
        <div
            class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-emerald-600 via-green-600 to-teal-600 p-8 text-white"
        >
            <div
                class="absolute -right-10 -top-10 w-48 h-48 rounded-full bg-white/10"
            />

            <div
                class="absolute right-16 bottom-0 w-24 h-24 rounded-full bg-white/10"
            />
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-bold">
                        Manajemen Kategori Produk
                    </h1>

                    <p class="text-indigo-100 mt-2">
                        Kelola klasifikasi produk farmasi untuk seluruh
                        operasional Apotik RHD Farma.
                    </p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">
            <div class="bg-white rounded-3xl p-6 border shadow-sm">
                <p class="text-slate-500 text-sm">Total Kategori</p>

                <h2 class="text-3xl font-bold text-indigo-600 mt-2">
                    {{ categories.length }}
                </h2>
            </div>

            <div class="bg-white rounded-3xl p-6 border shadow-sm">
                <p class="text-slate-500 text-sm">Total Produk</p>

                <h2 class="text-3xl font-bold text-blue-600 mt-2">
                    {{ totalProductsCount }}
                </h2>
            </div>

            <div class="bg-white rounded-3xl p-6 border shadow-sm">
                <p class="text-slate-500 text-sm">Kategori Aktif</p>

                <h2 class="text-3xl font-bold text-emerald-600 mt-2">
                    {{ categories.length }}
                </h2>
            </div>

            <div class="bg-white rounded-3xl p-6 border shadow-sm">
                <p class="text-slate-500 text-sm">Kategori Terbesar</p>

                <h2 class="text-xl font-bold text-purple-600 mt-2">
                    {{ largestCategory }}
                </h2>
            </div>
        </div>

        <div
            class="relative bg-white rounded-3xl border border-slate-200 p-5 shadow-sm"
        >
            <svg
                class="absolute left-9 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0a7 7 0 0114 0z"
                />
            </svg>

            <input
                v-model="searchQuery"
                placeholder="Cari kategori..."
                class="w-full h-14 pl-12 pr-4 rounded-2xl border border-slate-200 focus:ring-4 focus:ring-emerald-100 focus:border-emerald-500"
            />
        </div>

        <!-- Loading -->
        <div v-if="loading" class="text-center py-8">
            <p class="text-lg text-gray-600">⏳ Memuat kategori...</p>
        </div>

        <!-- Grid -->
        <div
            v-if="!loading"
            class="bg-white rounded-[32px] border border-slate-200 shadow-sm overflow-hidden"
        >
            <!-- HEADER -->
            <div
                class="px-7 py-6 border-b border-slate-100 flex items-center justify-between"
            >
                <div>
                    <h2
                        class="text-xl font-bold text-slate-900 flex items-center gap-2"
                    >
                        <svg
                            class="w-6 h-6"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M3 7h18M3 12h18M3 17h18"
                            />
                        </svg>

                        Daftar Kategori
                    </h2>

                    <p class="text-sm text-slate-500 mt-1">
                        {{ filteredCategories.length }}
                        kategori tersedia
                    </p>
                </div>

                <button
                    @click="openForm()"
                    class="h-12 px-5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold flex items-center gap-2"
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
                            d="M12 4v16m8-8H4"
                        />
                    </svg>
                    Tambah Kategori
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50">
                        <tr>
                            <th
                                class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500"
                            >
                                Kategori
                            </th>

                            <th
                                class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500"
                            >
                                Deskripsi
                            </th>

                            <th
                                class="px-6 py-4 text-center text-xs font-bold uppercase text-slate-500"
                            >
                                Produk
                            </th>

                            <th
                                class="px-6 py-4 text-center text-xs font-bold uppercase text-slate-500"
                            >
                                Dibuat
                            </th>

                            <th
                                class="px-6 py-4 text-center text-xs font-bold uppercase text-slate-500"
                            >
                                Aksi
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="category in filteredCategories"
                            :key="category.id"
                            class="border-t border-slate-100 hover:bg-slate-50 transition"
                        >
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-12 h-12 rounded-2xl bg-indigo-100 flex items-center justify-center"
                                    >
                                        <svg
                                            class="w-6 h-6 text-indigo-600"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M4 6h16v12H4z"
                                            />
                                        </svg>
                                    </div>

                                    <div>
                                        <h3
                                            class="font-semibold text-slate-900"
                                        >
                                            {{ category.name }}
                                        </h3>

                                        <p class="text-xs text-slate-400">
                                            ID #{{ category.id }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-5 text-slate-600">
                                {{
                                    category.description ||
                                    "Tidak ada deskripsi"
                                }}
                            </td>

                            <td class="px-6 py-5 text-center">
                                <span
                                    class="inline-flex px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-semibold"
                                >
                                    {{ category.medicines_count || 0 }}
                                </span>
                            </td>

                            <td class="px-6 py-5 text-center text-slate-500">
                                {{ formatDate(category.created_at) }}
                            </td>

                            <td class="px-6 py-5">
                                <div class="flex justify-center gap-2">
                                    <button
                                        @click="openForm(category)"
                                        class="w-10 h-10 rounded-xl bg-amber-50 hover:bg-amber-100 flex items-center justify-center"
                                    >
                                        <svg
                                            class="w-4 h-4 text-amber-600"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M11 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2v-5"
                                            />
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"
                                            />
                                        </svg>
                                    </button>

                                    <button
                                        @click="deleteCategory(category.id)"
                                        class="w-10 h-10 rounded-xl bg-red-50 hover:bg-red-100 flex items-center justify-center"
                                    >
                                        <svg
                                            class="w-4 h-4 text-red-600"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"
                                            />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Form Modal -->
        <!-- MODAL FORM -->
        <!-- DRAWER BACKDROP -->
        <Transition name="fade">
            <div
                v-if="showForm"
                class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm"
                @click="closeForm"
            />
        </Transition>

        <!-- DRAWER -->
        <Transition name="drawer">
            <aside
                v-if="showForm"
                class="fixed top-0 right-0 z-50 h-screen w-full max-w-2xl bg-white shadow-2xl flex flex-col"
            >
                <!-- HEADER -->
                <div
                    class="h-24 px-8 border-b border-slate-200 flex items-center justify-between bg-white"
                >
                    <div class="flex items-center gap-4">
                        <div
                            class="w-14 h-14 rounded-2xl bg-indigo-50 flex items-center justify-center"
                        >
                            <svg
                                class="w-7 h-7 text-indigo-600"
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
                        </div>

                        <div>
                            <h2 class="text-2xl font-bold text-slate-900">
                                {{
                                    editingId
                                        ? "Edit Kategori"
                                        : "Tambah Kategori"
                                }}
                            </h2>

                            <p class="text-sm text-slate-500 mt-1">
                                Kelola kategori produk farmasi
                            </p>
                        </div>
                    </div>

                    <button
                        @click="closeForm"
                        class="w-11 h-11 rounded-2xl bg-slate-100 hover:bg-slate-200 transition"
                    >
                        ✕
                    </button>
                </div>

                <!-- BODY -->
                <form
                    @submit.prevent="saveCategory"
                    class="flex-1 overflow-y-auto p-8 space-y-6"
                >
                    <!-- NAMA -->
                    <div
                        class="bg-slate-50 rounded-3xl border border-slate-100 p-6"
                    >
                        <label
                            class="block text-sm font-semibold text-slate-700 mb-3"
                        >
                            Nama Kategori
                        </label>

                        <input
                            v-model="form.name"
                            type="text"
                            required
                            placeholder="Masukkan nama kategori..."
                            class="w-full h-14 px-5 rounded-2xl border border-slate-200 focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition"
                        />
                    </div>

                    <!-- DESKRIPSI -->
                    <div
                        class="bg-slate-50 rounded-3xl border border-slate-100 p-6"
                    >
                        <label
                            class="block text-sm font-semibold text-slate-700 mb-3"
                        >
                            Deskripsi
                        </label>

                        <textarea
                            v-model="form.description"
                            rows="6"
                            placeholder="Masukkan deskripsi kategori..."
                            class="w-full rounded-2xl border border-slate-200 p-4 resize-none focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 outline-none"
                        />
                    </div>

                    <div
                        class="rounded-3xl bg-gradient-to-r from-emerald-600 via-green-600 to-teal-600 p-6 text-white"
                    >
                        <p class="text-indigo-100 text-sm">Preview Kategori</p>

                        <h2 class="text-2xl font-bold mt-2">
                            {{ form.name || "Nama Kategori" }}
                        </h2>

                        <p class="text-indigo-100 mt-3">
                            {{
                                form.description ||
                                "Deskripsi kategori akan tampil di sini."
                            }}
                        </p>
                    </div>
                    

                    <!-- FOOTER -->
                    <div
                        class="border-t border-slate-200 p-6 flex gap-3 bg-white"
                    >
                        <button
                            type="button"
                            @click="closeForm"
                            class="flex-1 h-14 rounded-2xl border border-slate-200 bg-slate-100 hover:bg-slate-200 font-semibold"
                        >
                            Batal
                        </button>

                        <button
                            type="submit"
                            :disabled="savingForm"
                            class="flex-1 h-14 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold shadow-lg shadow-indigo-200"
                        >
                            {{
                                savingForm
                                    ? "Menyimpan..."
                                    : editingId
                                      ? "Update Kategori"
                                      : "Simpan Kategori"
                            }}
                        </button>
                    </div>
                </form>
            </aside>
        </Transition>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import api from "@/services/api";
import dayjs from "dayjs";
import { ElMessage, ElMessageBox } from "element-plus";

const categories = ref([]);
const loading = ref(false);
const savingForm = ref(false);
const showForm = ref(false);
const editingId = ref(null);
const searchQuery = ref("");

const form = ref({
    name: "",
    description: "",
});

const formatDate = (date) => {
    return dayjs(date).format("DD/MM/YYYY");
};

const filteredCategories = computed(() => {
    if (!searchQuery.value) return categories.value;

    return categories.value.filter((item) =>
        item.name.toLowerCase().includes(searchQuery.value.toLowerCase()),
    );
});

const emptyCategories = computed(
    () =>
        categories.value.filter((c) => Number(c.medicines_count || 0) === 0)
            .length,
);
const largestCategory = computed(() => {
    if (!categories.value.length) return "-";

    return [...categories.value].sort(
        (a, b) => (b.medicines_count || 0) - (a.medicines_count || 0),
    )[0]?.name;
});
const closeForm = () => {
    showForm.value = false;

    form.value = {
        name: "",
        description: "",
    };

    editingId.value = null;
};

const fetchCategories = async () => {
    loading.value = true;

    try {
        const response = await api.get("categories?per_page=100");

        console.log("FULL RESPONSE:", response.data);
        console.log("DATA:", response.data.data);
        console.log("ITEM PERTAMA:", response.data.data.data?.[0]);

        categories.value = response.data.data.data || [];
    } catch (error) {
        console.error(error);
        ElMessage.error("Gagal memuat kategori");
    } finally {
        loading.value = false;
    }
};

const openForm = (category = null) => {
    if (category) {
        form.value = { ...category };
        editingId.value = category.id;
    } else {
        form.value = { name: "", description: "" };
        editingId.value = null;
    }
    showForm.value = true;
};

const totalProductsCount = computed(() =>
    categories.value.reduce(
        (total, category) => total + Number(category.medicines_count || 0),
        0,
    ),
);

const saveCategory = async () => {
    savingForm.value = true;
    try {
        if (editingId.value) {
            await api.put(`categories/${editingId.value}`, form.value);
            ElMessage.success("Kategori berhasil diperbarui");
        } else {
            await api.post("categories", form.value);
            ElMessage.success("Kategori berhasil ditambahkan");
        }

        showForm.value = false;
        fetchCategories();
    } catch (error) {
        ElMessage.error(
            error.response?.data?.message || "Gagal menyimpan kategori",
        );
    } finally {
        savingForm.value = false;
    }
};

const deleteCategory = async (id) => {
    try {
        await api.delete(`categories/${id}`);
        ElMessage.success("Kategori berhasil dihapus");
        fetchCategories();
    } catch (error) {
        console.error(error.response?.data);

        ElMessage.error(
            error.response?.data?.message || "Gagal menghapus kategori",
        );
    }
};

onMounted(() => {
    fetchCategories();
});
</script>

<style scoped>
.drawer-enter-active,
.drawer-leave-active {
    transition: all 0.3s ease;
}

.drawer-enter-from,
.drawer-leave-to {
    transform: translateX(100%);
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.25s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
