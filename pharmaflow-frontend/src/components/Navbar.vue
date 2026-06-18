<template>
    <nav
        class="sticky top-0 z-50 bg-white/80 backdrop-blur-xl border-b border-gray-100 shadow-sm"
    >
        <div class="max-w-7xl mx-auto px-6">
            <div class="h-20 flex items-center justify-between">
                <!-- Logo -->
                <router-link to="/" class="flex items-center gap-3">
                    <div
                        class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-500 to-green-700 flex items-center justify-center text-white text-xl shadow-lg"
                    >
                        🏥
                    </div>

                    <div>
                        <h1 class="text-xl font-extrabold text-slate-800">
                            PharmaFlow
                        </h1>

                        <p class="text-xs text-slate-500">
                            Apotek Online Modern
                        </p>
                    </div>
                </router-link>

                <!-- Menu -->
                <div class="hidden md:flex items-center gap-8">
                    <router-link
                        to="/"
                        class="font-medium text-slate-600 hover:text-emerald-600"
                    >
                        Beranda
                    </router-link>

                    <router-link
                        to="/products"
                        class="font-medium text-slate-600 hover:text-emerald-600"
                    >
                        Produk
                    </router-link>

                    <router-link
                        to="/orders"
                        class="font-medium text-slate-600 hover:text-emerald-600"
                    >
                        Pesanan
                    </router-link>
                </div>

                <!-- Right -->
                <div class="flex items-center gap-4">
                    <!-- Cart -->
                    <router-link
                        to="/cart"
                        class="relative text-2xl hover:scale-110 transition"
                    >
                        🛒

                        <span
                            v-if="cartStore.cartCount > 0"
                            class="absolute -top-2 -right-2 w-5 h-5 rounded-full bg-red-500 text-white text-xs flex items-center justify-center"
                        >
                            {{ cartStore.cartCount }}
                        </span>
                    </router-link>

                    <!-- Guest -->
                    <template v-if="!authStore.isAuthenticated">
                        <router-link
                            to="/login"
                            class="px-5 py-2 rounded-xl border border-gray-300 hover:bg-gray-50"
                        >
                            Login
                        </router-link>

                        <router-link
                            to="/register"
                            class="px-5 py-2 rounded-xl bg-gradient-to-r from-emerald-500 to-green-700 text-white shadow-lg"
                        >
                            Register
                        </router-link>
                    </template>

                    <!-- User -->
                    <template v-else>
                        <el-dropdown>
                            <span
                                class="cursor-pointer flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100"
                            >
                                👤 {{ authStore.user?.name }}
                            </span>

                            <template #dropdown>
                                <el-dropdown-item
                                    @click="router.push('/orders')"
                                >
                                    Pesanan Saya
                                </el-dropdown-item>

                                <el-dropdown-item @click="logout">
                                    Logout
                                </el-dropdown-item>
                            </template>
                        </el-dropdown>
                    </template>
                </div>
            </div>
        </div>
    </nav>
</template>

<script setup>
import { useAuthStore } from "@/stores/auth";

import { useCartStore } from "@/stores/cart";

import api from "@/services/api";

import { useRouter } from "vue-router";

import { ElMessage } from "element-plus";

const authStore = useAuthStore();

const cartStore = useCartStore();

const router = useRouter();

const logout = async () => {
    try {
        // call backend logout
        await api.post("/auth/logout");
    } catch (error) {
        console.warn("Logout API gagal:", error.response?.data);
    } finally {
        // paksa clear local
        await authStore.logout();

        ElMessage.success("Logout berhasil");

        router.push("/login");
    }
};
</script>
