<template>
    <div class="space-y-6">
        <!-- HEADER -->

        <div
            class="bg-gradient-to-r from-emerald-600 to-teal-700 rounded-3xl p-8 text-white shadow-xl"
        >
            <h1 class="text-4xl font-black">Stock Opname</h1>

            <p class="mt-2 text-emerald-100">
                Monitoring & pengecekan stok fisik apotek
            </p>
        </div>

        <!-- KPI -->

        <div class="grid md:grid-cols-5 gap-5">
            <div class="bg-white rounded-2xl shadow p-5">
                <p>Total Opname</p>

                <h2 class="text-3xl font-bold">
                    {{ opnames.length }}
                </h2>
            </div>

            <div class="bg-yellow-50 rounded-2xl shadow p-5">
                <p class="text-yellow-700">Pending</p>

                <h2 class="text-3xl font-bold">
                    {{ opnames.filter((o) => o.status === "pending").length }}
                </h2>
            </div>

            <div class="bg-green-50 rounded-2xl shadow p-5">
                <p class="text-green-700">Approved</p>

                <h2 class="text-3xl font-bold">
                    {{ opnames.filter((o) => o.status === "approved").length }}
                </h2>
            </div>

            <div class="bg-red-50 rounded-2xl shadow p-5">
                <p class="text-red-700">Rejected</p>

                <h2 class="text-3xl font-bold">
                    {{ opnames.filter((o) => o.status === "rejected").length }}
                </h2>
            </div>
        </div>

        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold">Daftar Stock Opname</h2>

                <p class="text-slate-500">Riwayat pengecekan stok fisik</p>
            </div>

            <button
                @click="openCreateModal"
                class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-3 rounded-2xl font-semibold"
            >
                + Buat Opname
            </button>
        </div>

        <input
            v-model="search"
            placeholder="Cari nomor opname..."
            class="border rounded-xl px-4 py-3"
        />

        <!-- LIST -->

        <div class="space-y-4">
            <div
                v-for="item in filteredOpnames"
                :key="item.id"
                class="bg-white rounded-3xl shadow p-6"
            >
                <div class="flex justify-between">
                    <div>
                        <h2 class="font-bold text-xl">
                            {{ item.reference_number }}
                        </h2>

                        <p class="text-gray-500">
                            {{ item.warehouse?.name }}
                        </p>
                    </div>

                    <span
                        class="px-4 py-2 rounded-full text-white font-bold"
                        :class="getStatusColor(item.status)"
                    >
                        {{ item.status }}
                    </span>
                </div>

                <div class="mt-5">
                    Jumlah item :

                    <b>
                        {{ item.items.length }}
                    </b>
                </div>

                <button
                    @click="viewDetail(item)"
                    class="mt-5 bg-blue-600 text-white px-5 py-2 rounded-xl"
                >
                    Detail
                </button>
            </div>
        </div>
    </div>
    <div
        v-if="showCreateModal"
        class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
    >
        <div
            class="bg-white rounded-3xl p-6 w-full max-w-4xl max-h-[90vh] overflow-auto"
        >
            <div class="flex justify-between mb-6">
                <h2 class="text-2xl font-bold">Stock Opname Baru</h2>

                <button @click="showCreateModal = false">✖</button>
            </div>

            <div class="mb-4">
                <label>Gudang</label>

                <select
                    v-model="selectedWarehouse"
                    class="w-full border rounded-xl p-3"
                >
                    <option value="">Pilih Gudang</option>

                    <option
                        v-for="warehouse in warehouses"
                        :key="warehouse.id"
                        :value="warehouse.id"
                    >
                        {{ warehouse.name }}
                    </option>
                </select>
            </div>

            <button
                @click="addItem"
                class="bg-blue-600 text-white px-4 py-2 rounded-xl mb-4"
            >
                + Tambah Obat
            </button>

            <div
                v-for="(item, index) in opnameItems"
                :key="index"
                class="border rounded-xl p-4 mb-3"
            >
                <div class="grid md:grid-cols-3 gap-4">
                    <select
                        v-model="item.medicine_id"
                        class="border rounded-xl p-3"
                    >
                        <option value="">Pilih Obat</option>

                        <option
                            v-for="medicine in medicines"
                            :key="medicine.id"
                            :value="medicine.id"
                        >
                            {{ medicine.name }}
                        </option>
                    </select>

                    <input
                        type="number"
                        v-model="item.physical_quantity"
                        class="border rounded-xl p-3"
                        placeholder="Stok Fisik"
                    />

                    <button
                        @click="removeItem(index)"
                        class="bg-red-600 text-white rounded-xl"
                    >
                        Hapus
                    </button>
                </div>
            </div>

            <button
                @click="submitStockOpname"
                class="bg-emerald-600 text-white px-6 py-3 rounded-xl"
            >
                Simpan Stock Opname
            </button>
        </div>
    </div>
    <div
        v-if="showDetailModal"
        class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
    >
        <div
            class="bg-white rounded-3xl w-full max-w-5xl p-8 max-h-[90vh] overflow-auto"
        >
            <div class="flex justify-between mb-6">
                <h2 class="text-2xl font-bold">Detail Stock Opname</h2>

                <button @click="showDetailModal = false">✖</button>
            </div>

            <div class="grid md:grid-cols-4 gap-4 mb-6">
                <div>
                    <p class="text-slate-500">Nomor</p>

                    <b>
                        {{ selectedOpname.reference_number }}
                    </b>
                </div>

                <div>
                    <p class="text-slate-500">Gudang</p>

                    <b>
                        {{ selectedOpname.warehouse?.name }}
                    </b>
                </div>

                <div>
                    <p class="text-slate-500">Status</p>

                    <b>
                        {{ selectedOpname.status }}
                    </b>
                </div>

                <div>
                    <p class="text-slate-500">Tanggal</p>

                    <b>
                        {{
                            dayjs(selectedOpname.created_at).format(
                                "DD MMM YYYY HH:mm",
                            )
                        }}
                    </b>
                </div>
            </div>

            <table class="w-full">
                <thead>
                    <tr class="border-b bg-slate-50">
                        <th class="p-3 text-left">Obat</th>

                        <th class="p-3">Sistem</th>

                        <th class="p-3">Fisik</th>

                        <th class="p-3">Selisih</th>
                    </tr>
                </thead>

                <tbody>
                    <tr
                        v-for="item in selectedOpname.items"
                        :key="item.id"
                        class="border-b"
                    >
                        <td class="p-3">
                            {{ item.medicine?.name }}
                        </td>

                        <td class="text-center">
                            {{ item.system_quantity }}
                        </td>

                        <td class="text-center">
                            {{ item.physical_quantity }}
                        </td>

                        <td class="text-center font-bold">
                            {{ item.difference }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import dayjs from "dayjs";
import api from "@/services/api";
import { ElMessage } from "element-plus";


const opnames = ref([]);
const showCreateModal = ref(false);

const warehouses = ref([]);
const medicines = ref([]);

const selectedWarehouse = ref("");

const opnameItems = ref([]);
const search = ref("");
const selectedOpname = ref(null);
const showDetailModal = ref(false);

const filteredOpnames = computed(() => {
    return opnames.value.filter((o) =>
        o.reference_number?.toLowerCase().includes(search.value.toLowerCase()),
    );
});

const fetchOpname = async () => {
    const res = await api.get("stock-opname");

    opnames.value = res.data.data;

    console.log("OPNAME", opnames.value);
};

const getStatusColor = (status) => {
    return {
        pending: "bg-yellow-500",

        approved: "bg-green-500",

        rejected: "bg-red-500",
    }[status];
};

const viewDetail = async (item) => {
    try {
        console.log("ID =", item.id);

        const response = await api.get(`stock-opname/${item.id}`);

        console.log("DETAIL RESPONSE", response.data);

        selectedOpname.value = response.data;

        showDetailModal.value = true;
    } catch (error) {
        console.log("DETAIL ERROR", error);

        console.log(error.response);

        ElMessage.error("Gagal memuat detail opname");
    }
};

const fetchWarehouses = async () => {
    try {
        const response = await api.get("warehouses");

        warehouses.value = response.data.data || response.data;
        console.log(response.data);
    } catch (error) {
        console.log(error);
    }
};

const fetchMedicines = async () => {
    try {
        const response = await api.get("medicines");

        medicines.value = response.data.data?.data || response.data.data || [];
        console.log(response.data);
    } catch (error) {
        console.log(error);
    }
};

const addItem = () => {
    opnameItems.value.push({
        medicine_id: null,
        physical_quantity: null,
        notes: "",
    });
};

const removeItem = (index) => {
    opnameItems.value.splice(index, 1);
};

const submitStockOpname = async () => {
    try {
        if (!selectedWarehouse.value) {
            ElMessage.error("Pilih gudang terlebih dahulu");
            return;
        }

        const validItems = opnameItems.value.filter((item) => item.medicine_id);

        if (validItems.length === 0) {
            ElMessage.error("Minimal 1 obat harus dipilih");
            return;
        }

        await api.post("stock-opname", {
            warehouse_id: selectedWarehouse.value,
            items: validItems,
        });

        ElMessage.success("Stock Opname berhasil dibuat");

        showCreateModal.value = false;

        await fetchOpname();

        opnameItems.value = [];

        selectedWarehouse.value = "";
    } catch (error) {
        console.log(error);

        ElMessage.error(
            error.response?.data?.message || "Gagal membuat opname",
        );
    }
};

const openCreateModal = () => {
    showCreateModal.value = true;

    if (opnameItems.value.length === 0) {
        addItem();
    }
};

onMounted(() => {
    fetchOpname();

    fetchWarehouses();

    fetchMedicines();
});
</script>
