<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <div
            class="bg-gradient-to-r from-emerald-700 via-green-600 to-teal-700 shadow-xl"
        >
            <div class="max-w-7xl mx-auto px-6 py-10">
                <div class="flex items-center gap-5">
                    <div
                        class="w-24 h-24 rounded-full bg-white/20 backdrop-blur flex items-center justify-center border-4 border-white/30"
                    >
                        <span class="text-4xl font-extrabold text-white">
                            {{ profile.name?.charAt(0)?.toUpperCase() }}
                        </span>
                    </div>

                    <div>
                        <h1 class="text-4xl font-extrabold text-white">
                            {{ profile.name }}
                        </h1>

                        <div
                            class="inline-flex items-center mt-2 px-3 py-1 rounded-full bg-white/20 text-white text-sm font-semibold"
                        >
                            Customer PharmaFlow
                        </div>

                        <p class="text-emerald-100 mt-2">
                            {{ profile.email }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-6 py-8">
            <!-- Statistik -->
            <div class="grid md:grid-cols-3 gap-4 mb-8">
                <div class="bg-white rounded-2xl shadow p-6">
                    <p class="text-gray-500">Total Order</p>
                    <h2 class="text-3xl font-bold">
                        {{ stats.total_orders }}
                    </h2>
                </div>

                <div class="bg-white rounded-2xl shadow p-6">
                    <p class="text-gray-500">Order Selesai</p>
                    <h2 class="text-3xl font-bold text-green-600">
                        {{ stats.completed_orders }}
                    </h2>
                </div>

                <div class="bg-white rounded-2xl shadow p-6">
                    <p class="text-gray-500">Total Belanja</p>
                    <h2 class="text-3xl font-bold text-emerald-600">
                        Rp{{ formatPrice(stats.total_spending) }}
                    </h2>
                </div>
            </div>

            <!-- Informasi Akun -->
            <div class="bg-white rounded-3xl shadow p-6 mb-6">
                <h3 class="text-xl font-bold mb-6">Informasi Akun</h3>

                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="text-gray-500"> Nama </label>

                        <p class="font-semibold">
                            {{ profile.name }}
                        </p>
                    </div>

                    <div>
                        <label class="text-gray-500"> Email </label>

                        <p class="font-semibold">
                            {{ profile.email }}
                        </p>
                    </div>

                    <div>
                        <label class="text-gray-500"> Nomor HP </label>

                        <p class="font-semibold">
                            {{ profile.phone || "-" }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Alamat -->
            <div class="bg-white rounded-3xl shadow p-6 mb-6">
                <h3 class="text-xl font-bold mb-6">Alamat Pengiriman</h3>

                <div class="space-y-2">
                    <p v-if="profile.city || profile.province">
                        {{ profile.city
                        }}{{ profile.city && profile.province ? ", " : "" }}
                        {{ profile.province }}
                    </p>

                    <p v-if="profile.postal_code">
                        {{ profile.postal_code }}
                    </p>
                </div>
            </div>

            <!-- Action -->
            <div class="grid md:grid-cols-3 gap-4">
                <button
                    @click="showEditDialog = true"
                    class="h-12 rounded-xl bg-emerald-600 text-white font-bold"
                >
                    Edit Profil
                </button>

                <button
                    @click="showPasswordDialog = true"
                    class="h-12 rounded-xl bg-blue-600 text-white font-bold"
                >
                    Ganti Password
                </button>

                <button
                    @click="logout"
                    class="h-12 rounded-xl bg-red-600 text-white font-bold"
                >
                    Logout
                </button>
            </div>
        </div>

        <el-dialog v-model="showEditDialog" title="Edit Profil" width="600px">
            <div class="space-y-4">
                <el-input v-model="editForm.name" placeholder="Nama" />

                <el-input v-model="editForm.phone" placeholder="Nomor HP" />

                <el-input
                    v-model="editForm.address"
                    type="textarea"
                    :rows="3"
                    placeholder="Alamat"
                />

                <el-input v-model="editForm.city" placeholder="Kota" />

                <el-input v-model="editForm.province" placeholder="Provinsi" />

                <el-input
                    v-model="editForm.postal_code"
                    placeholder="Kode Pos"
                />
            </div>

            <template #footer>
                <el-button @click="showEditDialog = false"> Batal </el-button>

                <el-button type="success" @click="updateProfile">
                    Simpan
                </el-button>
            </template>
        </el-dialog>
    </div>
    <el-dialog
        v-model="showPasswordDialog"
        title="Ganti Password"
        width="500px"
    >
        <div class="space-y-4">
            <el-input
                v-model="passwordForm.current_password"
                type="password"
                show-password
                placeholder="Password Lama"
            />

            <el-input
                v-model="passwordForm.password"
                type="password"
                show-password
                placeholder="Password Baru"
            />

            <el-input
                v-model="passwordForm.password_confirmation"
                type="password"
                show-password
                placeholder="Konfirmasi Password Baru"
            />
        </div>

        <template #footer>
            <el-button @click="showPasswordDialog = false"> Batal </el-button>

            <el-button type="primary" @click="changePassword">
                Simpan
            </el-button>
        </template>
    </el-dialog>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import api from "@/services/api";
import { ElMessage } from "element-plus";

const router = useRouter();
const authStore = useAuthStore();

const profile = ref({});
const stats = ref({});

const showEditDialog = ref(false);
const showPasswordDialog = ref(false);
const editForm = ref({
    name: "",
    phone: "",
    address: "",
    city: "",
    province: "",
    postal_code: "",
});

const formatPrice = (price) => {
    return new Intl.NumberFormat("id-ID").format(price || 0);
};

const fetchProfile = async () => {
    try {
        const response = await api.get("/auth/me");
        console.log("PROFILE RESPONSE");
        console.log(response.data);

        profile.value = response.data.user;

        stats.value = response.data.stats;

        editForm.value = {
            name: profile.value.name || "",
            phone: profile.value.phone || "",
            address: profile.value.address || "",
            city: profile.value.city || "",
            province: profile.value.province || "",
            postal_code: profile.value.postal_code || "",
        };
    } catch (error) {
        console.error(error);

        ElMessage.error("Gagal memuat profil");
    }
};

const updateProfile = async () => {
    try {
        await api.put("/profile", editForm.value);

        ElMessage.success("Profil berhasil diperbarui");

        showEditDialog.value = false;

        fetchProfile();
    } catch (error) {
        ElMessage.error(
            error?.response?.data?.message || "Gagal memperbarui profil",
        );
    }
};

const changePassword = async () => {
    try {
        await api.put("/change-password", passwordForm.value);

        ElMessage.success("Password berhasil diubah");

        showPasswordDialog.value = false;

        passwordForm.value = {
            current_password: "",
            password: "",
            password_confirmation: "",
        };
    } catch (error) {
        ElMessage.error(
            error?.response?.data?.message || "Gagal mengubah password",
        );
    }
};

const passwordForm = ref({
    current_password: "",
    password: "",
    password_confirmation: "",
});

const logout = () => {
    authStore.logout();

    router.push("/login");
};

onMounted(() => {
    fetchProfile();
});
</script>
