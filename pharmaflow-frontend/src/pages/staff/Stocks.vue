<template>
    <div class="w-full min-h-screen bg-slate-50 p-6">
        <div class="space-y-6">
            <!-- HEADER -->
            <!-- HEADER PROFESIONAL -->
            <div
                class="relative overflow-hidden rounded-[32px] bg-gradient-to-r from-emerald-700 via-green-600 to-teal-600 p-8 text-white shadow-lg"
            >
                <div
                    class="absolute -right-16 -top-16 w-60 h-60 rounded-full bg-white/10"
                />

                <div
                    class="relative flex flex-col xl:flex-row xl:items-center xl:justify-between gap-6"
                >
                    <div>
                        <div class="flex items-center gap-3 mb-3">
                            <span
                                class="px-4 py-2 rounded-full bg-white/20 text-sm font-semibold"
                            >
                                Sistem Persediaan Apotek
                            </span>
                        </div>

                        <h1 class="text-4xl font-bold">
                            Manajemen Persediaan Obat
                        </h1>

                        <p class="mt-3 text-emerald-50 max-w-2xl">
                            Pantau stok obat, gudang, rak, tanggal kedaluwarsa,
                            FIFO, serta nilai aset persediaan Apotik RHD Farma
                            secara real-time.
                        </p>

                        <div class="flex gap-4 mt-6">
                            <div>
                                <p class="text-sm text-emerald-100">
                                    Total Jenis Obat
                                </p>

                                <h2 class="text-3xl font-bold">
                                    {{ stocks.length }}
                                </h2>
                            </div>

                            <div>
                                <p class="text-sm text-emerald-100">
                                    Stok Bermasalah
                                </p>

                                <h2 class="text-3xl font-bold">
                                    {{ totalAlerts }}
                                </h2>
                            </div>

                            <div>
                                <p class="text-sm text-emerald-100">
                                    Kesehatan
                                </p>

                                <h2 class="text-3xl font-bold">
                                    {{ inventoryHealth }}%
                                </h2>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3">
                        <button
                            @click="openStockOpname"
                            class="flex items-center gap-2 px-6 py-3 rounded-2xl bg-white/20 hover:bg-white/30 font-bold transition"
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
                                    d="M9 17v-6h13M9 5v6h13M5 19V5"
                                />
                            </svg>

                            Pemeriksaan Stok
                        </button>

                        <button
                            @click="openAdjustment(null)"
                            class="flex items-center gap-2 px-6 py-3 rounded-2xl bg-white text-emerald-700 hover:bg-emerald-50 font-bold transition"
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
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581"
                                />
                            </svg>

                            Penyesuaian Stok
                        </button>
                    </div>
                </div>
            </div>

            <div
                class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm"
            >
                <div class="flex items-center justify-between mb-5">
                    <h2 class="font-bold text-xl text-slate-900">
                        Peringatan Persediaan
                    </h2>

                    <span
                        class="px-3 py-1 rounded-full bg-red-100 text-red-600 text-xs font-semibold"
                    >
                        {{ totalAlerts }} Alert
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="rounded-2xl bg-red-50 p-4">
                        <p class="text-red-500 text-sm">Stok Menipis</p>

                        <h3 class="text-3xl font-bold text-red-600">
                            {{ lowStockCount }}
                        </h3>
                    </div>

                    <div class="rounded-2xl bg-orange-50 p-4">
                        <p class="text-orange-500 text-sm">
                            Mendekati Kedaluwarsa
                        </p>

                        <h3 class="text-3xl font-bold text-orange-600">
                            {{ expiringSoonCount }}
                        </h3>
                    </div>

                    <div class="rounded-2xl bg-rose-50 p-4">
                        <p class="text-rose-500 text-sm">Kedaluwarsa</p>

                        <h3 class="text-3xl font-bold text-rose-600">
                            {{ expiredCount }}
                        </h3>
                    </div>

                    <div class="rounded-2xl bg-blue-50 p-4">
                        <p class="text-blue-500 text-sm">Perlu Pemesanan</p>

                        <h3 class="text-3xl font-bold text-blue-600">
                            {{ lowStockCount }}
                        </h3>
                    </div>
                </div>
            </div>

            <!-- FILTER -->
            <div
                class="bg-white rounded-[32px] border border-slate-200 shadow-sm p-8"
            >
                <div
                    class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-5"
                >
                    <div>
                        <h3 class="text-2xl font-bold text-slate-900">
                            🔍 Filter Stok
                        </h3>

                        <p class="text-slate-500">Cari dan filter stok</p>
                    </div>

                    <button
                        @click="fetchStocks"
                        class="px-5 py-3 rounded-2xl bg-blue-600 text-white font-semibold hover:bg-blue-700 transition shadow-md"
                    >
                        🔄 Refresh
                    </button>
                </div>

                <!-- GRID FILTER -->
                <div
                    class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5"
                >
                    <!-- Gudang -->
                    <div>
                        <label
                            class="block text-sm font-semibold text-slate-700 mb-2"
                        >
                            Gudang
                        </label>

                        <select
                            v-model="filterWarehouse"
                            class="w-full h-[56px] rounded-2xl border border-slate-300 px-4 outline-none focus:ring-4 focus:ring-blue-100"
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

                    <!-- Filter -->
                    <div>
                        <label
                            class="block text-sm font-semibold text-slate-700 mb-2"
                        >
                            Filter
                        </label>

                        <select
                            v-model="filterType"
                            class="w-full h-[56px] rounded-2xl border border-slate-300 px-4 outline-none focus:ring-4 focus:ring-blue-100"
                        >
                            <option value="">Semua</option>

                            <option value="low_stock">Stok Menipis</option>

                            <option value="expired">Expired</option>

                            <option value="expiring_soon">
                                Exp Soon (30d)
                            </option>
                        </select>
                    </div>

                    <!-- Search -->
                    <div
                        class="relative bg-white rounded-3xl border border-slate-200 p-4 shadow-sm xl:col-span-2"
                    >
                        <svg
                            class="absolute left-8 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400"
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
                            placeholder="Cari obat, kategori, kode obat..."
                            class="w-full h-14 pl-12 pr-4 rounded-2xl border border-slate-200 focus:ring-4 focus:ring-emerald-100 focus:border-emerald-500"
                        />
                    </div>
                </div>
            </div>
            <!-- PREMIUM TABLE -->
            <div
                class="bg-white rounded-[32px] border border-slate-200 shadow-sm overflow-hidden"
            >
                <!-- HEADER -->
                <div
                    class="px-7 py-6 border-b border-slate-100 flex items-center justify-between"
                >
                    <div>
                        <h2 class="text-2xl font-bold text-slate-800">
                            📦 Data Persediaan Obat
                        </h2>

                        <p class="text-slate-500 text-sm mt-1">
                            Total
                            <span class="font-bold text-blue-600">
                                {{ stocks.length }}
                            </span>
                            data persediaan ditemukan
                        </p>
                    </div>

                    <button
                        @click="fetchStocks"
                        class="px-5 py-3 rounded-2xl bg-blue-600 text-white font-semibold hover:bg-blue-700 transition"
                    >
                        🔄 Perbarui Data
                    </button>
                </div>

                <!-- LOADING -->
                <div
                    v-if="loading"
                    class="py-24 flex flex-col items-center justify-center"
                >
                    <div
                        class="w-14 h-14 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"
                    />

                    <p class="text-slate-500 mt-4 font-medium">
                        Memuat data stok...
                    </p>
                </div>

                <!-- EMPTY -->
                <div
                    v-else-if="stocks.length === 0"
                    class="py-24 flex flex-col items-center justify-center"
                >
                    <div class="text-7xl mb-4">📦</div>

                    <h3 class="text-2xl font-bold text-slate-700">
                        Tidak ada stok
                    </h3>

                    <p class="text-slate-500 mt-2">Data stok belum tersedia</p>
                </div>

                <!-- TABLE -->
                <div v-else class="overflow-x-auto">
                    <table class="w-max min-w-full">
                        <thead class="bg-slate-50 border-b">
                            <tr>
                                <th class="px-6 py-5 text-left">
                                    Informasi Obat
                                </th>

                                <th class="px-6 py-5 text-left">Lokasi</th>

                                <th class="px-6 py-5 text-center">Stok</th>

                                <th class="px-6 py-5 text-center">
                                    Status Stok
                                </th>

                                <th class="px-6 py-5 text-center">Minimum</th>

                                <th class="px-6 py-5 text-center">
                                    Kedaluwarsa
                                </th>

                                <th class="px-6 py-5 text-center">Harga</th>

                                <th class="px-6 py-5 text-center">
                                    Nilai Persediaan
                                </th>
                                <th class="px-6 py-5 text-center">Risiko</th>
                                <th class="px-6 py-5 text-center">Prioritas</th>
                                <th class="px-6 py-5 text-center">Kondisi</th>

                                <th class="px-6 py-5 text-center">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="stock in stocks"
                                :key="stock.stock_id || stock.id"
                                class="border-b hover:bg-emerald-50 transition"
                            >
                                <!-- OBAT -->

                                <td class="px-6 py-5">
                                    <div class="flex gap-4 items-center">
                                        <div
                                            class="w-14 h-14 rounded-2xl bg-emerald-100 flex items-center justify-center"
                                        >
                                            <svg
                                                class="w-7 h-7 text-emerald-600"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M10.5 6.5l7 7m-9-5l5 5m-7-1a3 3 0 114.24-4.24l4.24 4.24a3 3 0 11-4.24 4.24L6.5 12.74a3 3 0 010-4.24z"
                                                />
                                            </svg>
                                        </div>

                                        <div>
                                            <h3
                                                class="font-bold text-slate-900"
                                            >
                                                {{ stock.medicine?.name }}
                                            </h3>

                                            <p class="text-sm text-slate-500">
                                                Kode :
                                                {{
                                                    stock.medicine?.code || "-"
                                                }}
                                            </p>

                                            <p
                                                class="text-xs text-indigo-600 mt-1"
                                            >
                                                {{
                                                    stock.medicine?.category
                                                        ?.name ||
                                                    "Tanpa kategori"
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <!-- LOKASI -->

                                <td class="px-6 py-5">
                                    <p class="font-semibold">
                                        🏥 {{ stock.warehouse?.name || "-" }}
                                    </p>

                                    <p class="text-sm text-slate-500">
                                        Rak :
                                        {{ stock.shelf?.code || "-" }}
                                    </p>
                                </td>

                                <!-- QTY -->

                                <td class="px-6 py-5 text-center">
                                    <span
                                        :class="[
                                            'px-5 py-2 rounded-full text-white font-bold',

                                            stock.quantity <= 0
                                                ? 'bg-red-500'
                                                : stock.quantity <=
                                                    (stock.medicine
                                                        ?.stock_minimum || 0)
                                                  ? 'bg-orange-500'
                                                  : 'bg-emerald-500',
                                        ]"
                                    >
                                        {{ stock.quantity }}
                                    </span>
                                </td>

                                <!-- STATUS STOK -->
                                <td class="px-6 py-5 text-center">
                                    <div class="w-24 mx-auto">
                                        <div
                                            class="w-full bg-slate-200 h-2 rounded-full"
                                        >
                                            <div
                                                class="h-2 rounded-full"
                                                :class="
                                                    stock.quantity <=
                                                    (stock.medicine
                                                        ?.stock_minimum || 0)
                                                        ? 'bg-orange-500'
                                                        : 'bg-emerald-500'
                                                "
                                                :style="{
                                                    width:
                                                        Math.min(
                                                            (stock.quantity /
                                                                ((stock.medicine
                                                                    ?.stock_minimum ||
                                                                    1) *
                                                                    2)) *
                                                                100,
                                                            100,
                                                        ) + '%',
                                                }"
                                            />
                                        </div>
                                    </div>
                                </td>

                                <!-- MINIMUM -->
                                <td class="px-6 py-5 text-center">
                                    <span
                                        class="px-4 py-2 rounded-full bg-slate-100 text-slate-700 font-semibold"
                                    >
                                        {{ stock.medicine?.stock_minimum || 0 }}
                                    </span>
                                </td>

                                <!-- EXPIRED -->

                                <td class="px-6 py-5 text-center">
                                    <span
                                        :class="[
                                            'px-4 py-2 rounded-xl font-semibold',

                                            isExpired(stock.expired_date)
                                                ? 'bg-red-100 text-red-700'
                                                : isExpiringSoon(
                                                        stock.expired_date,
                                                    )
                                                  ? 'bg-orange-100 text-orange-700'
                                                  : 'bg-green-100 text-green-700',
                                        ]"
                                    >
                                        {{ formatDate(stock.expired_date) }}
                                    </span>
                                </td>

                                <!-- HARGA -->

                                <td class="px-6 py-5">
                                    <p>
                                        Beli :

                                        <b>
                                            Rp
                                            {{
                                                formatCurrency(
                                                    stock.medicine?.base_price,
                                                )
                                            }}
                                        </b>
                                    </p>

                                    <p>
                                        Jual :

                                        <b class="text-emerald-600">
                                            Rp
                                            {{
                                                formatCurrency(
                                                    stock.medicine
                                                        ?.selling_price,
                                                )
                                            }}
                                        </b>
                                    </p>
                                </td>

                                <!-- NILAI -->

                                <td class="px-6 py-5 text-center">
                                    <p class="font-bold text-emerald-600">
                                        Rp
                                        {{
                                            formatCurrency(
                                                stock.quantity *
                                                    (stock.medicine
                                                        ?.base_price || 0),
                                            )
                                        }}
                                    </p>
                                </td>

                                <td class="px-6 py-5 text-center">
                                    <span
                                        v-if="stock.risk_level === 'HIGH'"
                                        class="px-3 py-1 rounded-full bg-red-100 text-red-700 text-xs font-bold"
                                    >
                                        Tinggi
                                    </span>

                                    <span
                                        v-else-if="
                                            stock.risk_level === 'MEDIUM'
                                        "
                                        class="px-3 py-1 rounded-full bg-orange-100 text-orange-700 text-xs font-bold"
                                    >
                                        Sedang
                                    </span>

                                    <span
                                        v-else
                                        class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs font-bold"
                                    >
                                        Rendah
                                    </span>
                                </td>

                                <td class="px-6 py-5 text-center">
                                    <span
                                        v-if="stock.risk_level === 'HIGH'"
                                        class="px-3 py-1 rounded-full bg-red-100 text-red-700 text-xs font-bold"
                                    >
                                        Segera
                                    </span>

                                    <span
                                        v-else-if="
                                            stock.risk_level === 'MEDIUM'
                                        "
                                        class="px-3 py-1 rounded-full bg-yellow-100 text-yellow-700 text-xs font-bold"
                                    >
                                        Pantau
                                    </span>

                                    <span
                                        v-else
                                        class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs font-bold"
                                    >
                                        Normal
                                    </span>
                                </td>

                                <!-- STATUS -->

                                <td class="px-6 py-5 text-center">
                                    <span
                                        v-if="isExpired(stock.expired_date)"
                                        class="badge-danger"
                                    >
                                        Kedaluwarsa
                                    </span>

                                    <span
                                        v-else-if="
                                            isExpiringSoon(stock.expired_date)
                                        "
                                        class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-red-50 text-red-700 text-xs font-semibold"
                                    >
                                        Hampir Expired
                                    </span>

                                    <span
                                        v-else-if="
                                            stock.quantity <=
                                            (stock.medicine?.stock_minimum || 0)
                                        "
                                        class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-amber-50 text-amber-700 text-xs font-semibold"
                                    >
                                        Stok Menipis
                                    </span>

                                    <span
                                        v-else
                                        class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-emerald-50 text-emerald-700 text-xs font-semibold"
                                    >
                                        Stok Aman
                                    </span>
                                </td>

                                <!-- ACTION -->

                                <td class="px-6 py-5 text-center">
                                    <div class="flex justify-center gap-2">
                                        <!-- DETAIL -->
                                        <button
                                            @click="openStockDetail(stock)"
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
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                                />
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5
                c4.478 0 8.268 2.943 9.542 7
                -1.274 4.057-5.064 7-9.542 7
                -4.477 0-8.268-2.943-9.542-7z"
                                                />
                                            </svg>
                                        </button>

                                        <!-- ADJUST -->
                                        <button
                                            @click="openAdjustment(stock)"
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
                                                    d="M11 4H6a2 2 0 00-2 2v12
                a2 2 0 002 2h12a2 2 0 002-2v-5"
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

            <!-- =========================
     ADJUSTMENT MODAL
========================= -->
            <div
                v-if="showAdjustmentForm"
                class="fixed inset-0 z-[1000] flex items-center justify-center p-4"
            >
                <!-- BACKDROP -->
                <div
                    @click="closeAdjustment"
                    class="absolute inset-0 bg-black/50"
                />

                <!-- CONTENT -->
                <div
                    class="relative bg-white rounded-[32px] shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto z-10"
                >
                    <!-- HEADER -->
                    <div
                        class="bg-gradient-to-r from-blue-600 to-cyan-600 text-white px-8 py-6 flex items-center justify-between rounded-t-[32px]"
                    >
                        <div>
                            <h2 class="text-3xl font-bold">
                                🔄 Stock Adjustment
                            </h2>

                            <p class="text-blue-100 mt-1">
                                Sesuaikan stok obat
                            </p>
                        </div>

                        <button
                            @click="closeAdjustment"
                            class="w-12 h-12 rounded-full bg-white/20 hover:bg-white/30 transition text-2xl"
                        >
                            ✕
                        </button>
                    </div>

                    <!-- FORM -->
                    <form
                        @submit.prevent="saveAdjustment"
                        class="p-8 space-y-6"
                    >
                        <!-- OBAT -->
                        <div>
                            <label
                                class="block font-semibold text-slate-700 mb-2"
                            >
                                Obat
                            </label>

                            <input
                                :value="adjustmentForm.medicine_name"
                                disabled
                                class="w-full h-14 rounded-[20px] border border-slate-200 bg-slate-100 px-5 font-semibold text-slate-700"
                            />
                        </div>

                        <!-- STOCK -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label
                                    class="block font-semibold text-slate-700 mb-2"
                                >
                                    Stock Sekarang
                                </label>

                                <input
                                    :value="adjustmentForm.quantity_before"
                                    disabled
                                    class="w-full h-14 rounded-[20px] border border-slate-200 bg-slate-100 px-5 font-bold text-center text-blue-600"
                                />
                            </div>

                            <div>
                                <label
                                    class="block font-semibold text-slate-700 mb-2"
                                >
                                    Stock Baru *
                                </label>

                                <input
                                    v-model.number="
                                        adjustmentForm.quantity_after
                                    "
                                    type="number"
                                    min="0"
                                    required
                                    class="w-full h-14 rounded-[20px] border border-slate-200 bg-slate-50 px-5 text-center font-bold focus:outline-none focus:ring-4 focus:ring-blue-100"
                                />
                            </div>
                        </div>

                        <!-- TYPE -->
                        <div>
                            <label
                                class="block font-semibold text-slate-700 mb-2"
                            >
                                Tipe Adjustment *
                            </label>

                            <select
                                v-model="adjustmentForm.type"
                                required
                                class="w-full h-14 rounded-[20px] border border-slate-200 bg-slate-50 px-5 focus:outline-none focus:ring-4 focus:ring-blue-100"
                            >
                                <option value="">Pilih Tipe</option>

                                <option value="penambahan">
                                    ➕ Penambahan
                                </option>

                                <option value="pengurangan">
                                    ➖ Pengurangan
                                </option>

                                <option value="koreksi">🔄 Koreksi</option>
                            </select>
                        </div>

                        <!-- REASON -->
                        <div>
                            <label
                                class="block font-semibold text-slate-700 mb-2"
                            >
                                Alasan *
                            </label>

                            <select
                                v-model="adjustmentForm.reason"
                                required
                                class="w-full h-14 rounded-[20px] border border-slate-200 bg-slate-50 px-5 focus:outline-none focus:ring-4 focus:ring-blue-100"
                            >
                                <option value="">Pilih alasan</option>

                                <option value="opname">📋 Stock Opname</option>

                                <option value="rusak">💔 Barang Rusak</option>

                                <option value="hilang">🔍 Barang Hilang</option>

                                <option value="retur">↩️ Retur Supplier</option>

                                <option value="koreksi_data">
                                    📝 Koreksi Data
                                </option>
                            </select>
                        </div>

                        <!-- NOTES -->
                        <div>
                            <label
                                class="block font-semibold text-slate-700 mb-2"
                            >
                                Catatan
                            </label>

                            <textarea
                                v-model="adjustmentForm.notes"
                                rows="4"
                                placeholder="Tambahkan catatan..."
                                class="w-full rounded-[20px] border border-slate-200 bg-slate-50 px-5 py-4 resize-none focus:outline-none focus:ring-4 focus:ring-blue-100"
                            />
                        </div>

                        <!-- BUTTON -->
                        <div class="flex gap-3 pt-4">
                            <button
                                type="submit"
                                :disabled="savingAdjustment"
                                class="flex-1 h-14 rounded-[20px] bg-blue-600 hover:bg-blue-700 text-white font-bold transition disabled:bg-slate-400"
                            >
                                {{
                                    savingAdjustment
                                        ? "⏳ Menyimpan..."
                                        : "✅ Simpan Adjustment"
                                }}
                            </button>

                            <button
                                type="button"
                                @click="closeAdjustment"
                                class="flex-1 h-14 rounded-[20px] bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold transition"
                            >
                                ❌ Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- =========================
     STOCK OPNAME MODAL
========================= -->
            <div
                v-if="showStockOpname"
                class="fixed inset-0 z-[1000] flex items-center justify-center p-4"
            >
                <!-- BACKDROP -->
                <div
                    @click="closeStockOpname"
                    class="absolute inset-0 bg-black/50"
                ></div>

                <!-- CONTENT -->
                <div
                    class="relative bg-white rounded-[32px] shadow-2xl w-full max-w-6xl max-h-[90vh] overflow-hidden z-10"
                >
                    <!-- HEADER -->
                    <div
                        class="bg-orange-500 text-white px-8 py-6 flex justify-between items-center"
                    >
                        <div>
                            <h2 class="text-3xl font-bold">📋 Stock Opname</h2>

                            <p>Cocokkan stok fisik</p>
                        </div>

                        <button
                            @click="closeStockOpname"
                            class="w-12 h-12 rounded-full bg-white/20 hover:bg-white/30"
                        >
                            ✕
                        </button>
                    </div>

                    <!-- TABLE -->
                    <div class="overflow-y-auto max-h-[60vh]">
                        <table class="w-full">
                            <thead class="bg-slate-100 sticky top-0">
                                <tr>
                                    <th class="p-4 text-left">Obat</th>

                                    <th class="p-4 text-center">System</th>

                                    <th class="p-4 text-center">Fisik</th>

                                    <th class="p-4 text-center">Selisih</th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr
                                    v-for="item in stockOpnameForm.items"
                                    :key="item.stock_id"
                                    class="border-b"
                                >
                                    <td class="p-4">
                                        {{ item.medicine_name }}
                                    </td>

                                    <td class="p-4 text-center font-bold">
                                        {{ item.quantity_before }}
                                    </td>

                                    <td class="p-4">
                                        <input
                                            v-model.number="item.quantity_after"
                                            type="number"
                                            min="0"
                                            class="w-full h-12 rounded-xl border border-slate-300 px-4"
                                        />
                                    </td>

                                    <td
                                        class="p-4 text-center font-bold"
                                        :class="
                                            item.quantity_after >
                                            item.quantity_before
                                                ? 'text-green-600'
                                                : item.quantity_after <
                                                    item.quantity_before
                                                  ? 'text-red-600'
                                                  : 'text-slate-600'
                                        "
                                    >
                                        {{
                                            item.quantity_after -
                                            item.quantity_before
                                        }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- FOOTER -->
                    <div class="p-6 border-t flex gap-3">
                        <button
                            @click="saveStockOpname"
                            class="flex-1 h-14 rounded-[20px] bg-orange-500 hover:bg-orange-600 text-white font-bold"
                        >
                            {{
                                savingOpname
                                    ? "⏳ Menyimpan..."
                                    : "✅ Simpan Opname"
                            }}
                        </button>

                        <button
                            @click="closeStockOpname"
                            class="flex-1 h-14 rounded-[20px] bg-slate-200 hover:bg-slate-300 font-bold"
                        >
                            ❌ Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- space-y-6 -->
    </div>
    <Transition name="drawer">
        <div v-if="showStockDetail" class="fixed inset-0 z-[9999]">
            <div
                class="absolute inset-0 bg-black/50 backdrop-blur-sm"
                @click="closeStockDetail"
            />

            <aside
                class="absolute right-0 top-0 max-h-screen w-full max-w-3xl bg-white shadow-2xl overflow-y-auto"
            >
                <!-- HEADER -->

                <div
                    class="sticky top-0 bg-white border-b z-10 px-8 py-6 flex items-center justify-between"
                >
                    <div>
                        <h2 class="text-2xl font-bold text-slate-900">
                            {{ stockDetail?.medicine?.name }}
                        </h2>

                        <p class="text-slate-500">
                            {{ stockDetail?.medicine?.code }}
                        </p>
                    </div>

                    <div class="flex gap-2 mt-4">
                        <button
                            @click="activeDetailTab = 'info'"
                            :class="[
                                activeDetailTab === 'info'
                                    ? 'bg-emerald-600 text-white'
                                    : 'bg-slate-100 text-slate-600',
                                'px-4 py-2 rounded-xl text-sm font-semibold',
                            ]"
                        >
                            Informasi
                        </button>

                        <button
                            @click="activeDetailTab = 'mutation'"
                            :class="[
                                activeDetailTab === 'mutation'
                                    ? 'bg-emerald-600 text-white'
                                    : 'bg-slate-100 text-slate-600',
                                'px-4 py-2 rounded-xl text-sm font-semibold',
                            ]"
                        >
                            Mutasi
                        </button>

                        <button
                            @click="activeDetailTab = 'adjustment'"
                            :class="[
                                activeDetailTab === 'adjustment'
                                    ? 'bg-emerald-600 text-white'
                                    : 'bg-slate-100 text-slate-600',
                                'px-4 py-2 rounded-xl text-sm font-semibold',
                            ]"
                        >
                            Adjustment
                        </button>
                    </div>

                    <button
                        @click="closeStockDetail"
                        class="w-12 h-12 rounded-xl bg-slate-100 hover:bg-slate-200"
                    >
                        <svg
                            class="w-5 h-5 mx-auto"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>

                <!-- CONTENT -->

                <div v-if="activeDetailTab === 'info'" class="p-8 space-y-6">
                    <!-- SUMMARY -->

                    <div class="grid grid-cols-2 gap-4">
                        <div class="rounded-2xl bg-emerald-50 p-5">
                            <p class="text-sm text-slate-500">Stok Saat Ini</p>

                            <h3 class="text-3xl font-bold text-emerald-600">
                                {{ stockDetail?.quantity }}
                            </h3>
                        </div>

                        <div class="rounded-2xl bg-blue-50 p-5">
                            <p class="text-sm text-slate-500">
                                Nilai Persediaan
                            </p>

                            <h3 class="text-xl font-bold text-blue-600">
                                Rp
                                {{
                                    formatCurrency(
                                        (stockDetail?.quantity || 0) *
                                            (stockDetail?.medicine
                                                ?.base_price || 0),
                                    )
                                }}
                            </h3>
                        </div>
                    </div>

                    <div class="bg-blue-50 rounded-3xl p-6">
                        <h3 class="font-bold text-lg mb-4">
                            Prediksi Persediaan
                        </h3>

                        <div class="space-y-4">
                            <div>
                                <p class="text-slate-500">
                                    Rata-rata Penjualan Harian
                                </p>

                                <p class="font-bold text-blue-600">
                                    {{ stockDetail?.avg_daily_sales || 0 }}
                                    unit/hari
                                </p>
                            </div>

                            <div class="bg-white border rounded-3xl p-6">
                                <h3 class="font-bold text-lg mb-5">
                                    Ringkasan Persediaan
                                </h3>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-slate-500 text-sm">
                                            Minimum Stok
                                        </p>

                                        <p class="font-bold">
                                            {{
                                                stockDetail?.medicine
                                                    ?.stock_minimum
                                            }}
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-slate-500 text-sm">
                                            Tingkat Risiko
                                        </p>

                                        <p class="font-bold text-red-600">
                                            {{ stockDetail?.risk_level }}
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-slate-500 text-sm">
                                            Penjualan 30 Hari
                                        </p>

                                        <p class="font-bold">
                                            {{
                                                stockDetail?.qty_sold_30_days ||
                                                0
                                            }}
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-slate-500 text-sm">
                                            Perputaran Persediaan
                                        </p>

                                        <p class="font-bold">
                                            {{
                                                stockDetail?.inventory_turnover ||
                                                0
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <p class="text-slate-500">
                                    Perkiraan Stok Habis
                                </p>

                                <p class="font-bold text-red-600">
                                    {{
                                        stockDetail?.estimated_stockout_date ||
                                        "-"
                                    }}
                                </p>
                            </div>

                            <div>
                                <p class="text-slate-500">
                                    Rekomendasi Pemesanan
                                </p>

                                <p class="font-bold text-emerald-600">
                                    {{
                                        stockDetail?.recommended_order_qty || 0
                                    }}

                                    unit
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- INFO -->

                    <div class="bg-slate-50 rounded-3xl p-6 space-y-4">
                        <h3 class="font-bold text-lg">Informasi Produk</h3>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-slate-500 text-sm">Kategori</p>

                                <p class="font-semibold">
                                    {{
                                        stockDetail?.medicine?.category?.name ||
                                        "Tanpa kategori"
                                    }}
                                </p>
                            </div>

                            <div class="flex gap-2 mt-2">
                                <span
                                    class="px-2 py-1 rounded-lg bg-indigo-50 text-indigo-600 text-[11px] font-semibold"
                                >
                                    FIFO
                                </span>

                                <span
                                    class="px-2 py-1 rounded-lg bg-slate-100 text-slate-600 text-[11px]"
                                >
                                    Batch
                                </span>
                            </div>

                            <div>
                                <p class="text-slate-500 text-sm">Gudang</p>

                                <p class="font-semibold">
                                    {{ stockDetail?.warehouse?.name }}
                                </p>
                            </div>

                            <div>
                                <p class="text-slate-500 text-sm">Rak</p>

                                <p class="font-semibold">
                                    {{ stockDetail?.shelf?.code }}
                                </p>
                            </div>

                            <div>
                                <p class="text-slate-500 text-sm">
                                    Tanggal Kedaluwarsa
                                </p>

                                <p class="font-semibold">
                                    {{ formatDate(stockDetail?.expired_date) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- ACTION -->

                    <div class="grid grid-cols-3 gap-4">
                        <button
                            @click="openAdjustment(stockDetail)"
                            class="h-14 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-semibold"
                        >
                            Adjustment
                        </button>

                        <button
                            @click="openStockOpnameFromDetail"
                            class="h-14 rounded-2xl bg-orange-500 hover:bg-orange-600 text-white font-semibold"
                        >
                            Stock Opname
                        </button>

                        <button
                            @click="openMutation(stockDetail)"
                            class="h-14 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold"
                        >
                            Mutasi
                        </button>
                    </div>

                    <!-- HISTORY -->

                    <div class="bg-white border rounded-3xl p-6">
                        <h3 class="font-bold text-lg mb-4">
                            Riwayat Aktivitas
                        </h3>

                        <div class="space-y-4">
                            <div class="flex gap-4 items-start">
                                <div
                                    class="w-3 h-3 rounded-full bg-emerald-500 mt-2"
                                />

                                <div>
                                    <p class="font-medium">
                                        Stock diterima dari supplier
                                    </p>

                                    <p class="text-sm text-slate-500">
                                        15 Juni 2026
                                    </p>
                                </div>
                            </div>

                            <div class="flex gap-4 items-start">
                                <div
                                    class="w-3 h-3 rounded-full bg-blue-500 mt-2"
                                />

                                <div>
                                    <p class="font-medium">Adjustment stok</p>

                                    <p class="text-sm text-slate-500">
                                        20 Juni 2026
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="activeDetailTab === 'adjustment'" class="p-8">
                    <h3 class="text-xl font-bold mb-6">Riwayat Adjustment</h3>

                    <div
                        v-if="stockDetail?.adjustments?.length"
                        v-for="item in (stockDetail?.adjustments || [])"
                        :key="item.id"
                        class="border rounded-2xl p-5 mb-4"
                    >
                        <div class="flex justify-between">
                            <div>
                                <p class="font-semibold">
                                    {{ item.reason }}
                                </p>

                                <p class="text-sm text-slate-500">
                                    {{ item.notes }}
                                </p>
                            </div>

                            <div class="text-right">
                                <p class="font-bold text-blue-600">
                                    {{ item.quantity_before }}
                                    →
                                    {{ item.quantity_after }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div v-else class="text-center text-slate-500 py-10">
                        Belum ada data adjustment
                    </div>
                </div>
                <div v-if="activeDetailTab === 'mutation'" class="p-8">
                    <h3 class="text-xl font-bold mb-6">Riwayat Mutasi Stok</h3>

                    <div
                        v-if="stockDetail?.mutations?.length"
                        v-for="item in (stockDetail?.mutations || [])"
                        :key="item.id"
                        class="flex gap-4 pb-5 mb-5 border-b"
                    >
                        <div class="flex-1">
                            <p class="font-semibold">
                                {{ item.type }}
                            </p>

                            <p class="text-sm text-slate-500">
                                {{ item.notes }}
                            </p>

                            <p class="text-xs text-slate-400">
                                {{ formatDate(item.created_at) }}
                            </p>
                        </div>
                    </div>

                    <div v-else class="text-center text-slate-500 py-10">
                        Belum ada data mutasi
                    </div>
                </div>
            </aside>
        </div>
    </Transition>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from "vue";

import api from "@/services/api";
import dayjs from "dayjs";

import { ElMessage, ElMessageBox } from "element-plus";

const loading = ref(false);

const savingAdjustment = ref(false);

const savingOpname = ref(false);

const stocks = ref([]);

const warehouses = ref([]);

const showStockDetail = ref(false);

const stockDetail = ref(null);

const activeDetailTab = ref("info");

/* =========================
   FILTER & UI STATE
========================= */

const searchQuery = ref("");

const filterWarehouse = ref("");

const filterType = ref("");

const searchTimeout = ref(null);

const showAdjustmentForm = ref(false);

/* =========================
   FORM
========================= */

const defaultAdjustment = () => ({
    stock_id: "",

    medicine_name: "",

    quantity_before: 0,

    quantity_after: 0,

    type: "",

    reason: "",

    notes: "",
});

const adjustmentForm = ref(defaultAdjustment());

/* =========================
   FORMATTER
========================= */

const formatDate = (date) => {
    if (!date) return "-";

    return dayjs(date).format("DD MMM YYYY");
};

const isExpired = (date) => {
    if (!date) return false;

    return dayjs(date).isBefore(dayjs(), "day");
};

const isExpiringSoon = (date) => {
    if (!date) return false;

    return (
        dayjs(date).isAfter(dayjs()) &&
        dayjs(date).isBefore(dayjs().add(30, "day"))
    );
};

/* =========================
   API FETCH
========================= */

const fetchStocks = async () => {
    loading.value = true;

    try {
        let endpoint = "stocks";

        const params = {
            per_page: 100,
        };

        if (filterType.value === "low_stock") {
            endpoint = "stocks/low-stock";
        }

        if (filterType.value === "expired") {
            endpoint = "stocks/expired";
        }

        if (filterType.value === "expiring_soon") {
            endpoint = "stocks/expiring-soon";
        }

        if (filterWarehouse.value) {
            params.warehouse_id = filterWarehouse.value;
        }

        if (searchQuery.value) {
            params.search = searchQuery.value;
        }

        const response = await api.get(endpoint, { params });

        console.log("API RESPONSE", response.data);

        stocks.value =
            response?.data?.data?.data ||
            response?.data?.data ||
            response?.data ||
            [];

        console.log("STOCK RESULT", stocks.value);
    } catch (error) {
        console.error("FETCH STOCK ERROR", error);

        ElMessage.error(error.response?.data?.message || "Gagal memuat stok");

        stocks.value = [];
    } finally {
        loading.value = false;
    }
};
const totalStocks = computed(() => {
    return stocks.value.length;
});

const lowStockCount = computed(() => {
    return stocks.value.filter(
        (stock) => stock.quantity <= (stock.medicine?.stock_minimum || 0),
    ).length;
});

const expiredCount = computed(() => {
    return stocks.value.filter((stock) => isExpired(stock.expired_date)).length;
});

const expiringSoonCount = computed(() => {
    return stocks.value.filter((stock) => isExpiringSoon(stock.expired_date))
        .length;
});

/* =========================
   SEARCH AUTO
========================= */

const fetchWarehouses = async () => {
    try {
        console.log("LOAD WAREHOUSES");

        const response = await api.get("warehouses");

        console.log("FULL WAREHOUSE RESPONSE:", response);

        console.log("WAREHOUSE DATA:", response.data);

        // ambil semua kemungkinan struktur
        warehouses.value =
            response.data?.data?.data ||
            response.data?.data ||
            response.data ||
            [];

        console.log("FINAL WAREHOUSES:", warehouses.value);
    } catch (error) {
        console.error("WAREHOUSE ERROR:", error);

        ElMessage.error("Gagal memuat gudang");
    }
};

const handleSearch = () => {
    clearTimeout(searchTimeout.value);

    searchTimeout.value = setTimeout(() => {
        fetchStocks();
    }, 500);
};
/* =========================
   MODAL
========================= */

const selectedStock = ref(null);

const closeAdjustment = () => {
    showAdjustmentForm.value = false;
    selectedStock.value = null;

    adjustmentForm.value = defaultAdjustment();
};

const openAdjustment = (stock = null) => {
    // adjustment manual dari tombol header
    if (!stock) {
        adjustmentForm.value = defaultAdjustment();

        showAdjustmentForm.value = true;

        return;
    }

    selectedStock.value = stock;

    adjustmentForm.value = {
        stock_id: stock.stock_id ?? stock.id ?? "",

        medicine_name: stock.medicine?.name || "",

        quantity_before: Number(stock.quantity || 0),

        quantity_after: Number(stock.quantity || 0),

        type: "",
        reason: "",
        notes: "",
    };

    console.log("OPEN ADJUSTMENT", adjustmentForm.value);

    showAdjustmentForm.value = true;
};

const openStockDetail = async (stock) => {
    try {
        activeDetailTab.value = "info";

        const response = await api.get(`stocks/${stock.stock_id || stock.id}`);

        stockDetail.value = response.data?.data || response.data;

        showStockDetail.value = true;
    } catch (error) {
        console.error(error);

        ElMessage.error(
            error.response?.data?.message || "Gagal memuat detail stok",
        );
    }
};

const closeStockDetail = () => {
    showStockDetail.value = false;

    stockDetail.value = null;
};

/* =========================
   STOCK OPNAME
========================= */
const showStockOpname = ref(false);

const stockOpnameForm = ref({
    warehouse_id: "",
    items: [],
});

const openAdjustmentFromDetail = () => {
    const stock = stockDetail.value;

    closeStockDetail();

    openAdjustment(stock);
};

const openStockOpnameFromDetail = () => {
    closeStockDetail();

    openStockOpname();
};

console.log("WAREHOUSE SELECTED:", filterWarehouse.value);
const openStockOpname = async () => {
    try {
        if (!filterWarehouse.value) {
            stockOpnameForm.value = {
                warehouse_id:
                    filterWarehouse.value || warehouses.value?.[0]?.id || null,
                items: stocks.value.map((stock) => ({
                    stock_id: stock.stock_id || stock.id,
                    medicine_name: stock.medicine?.name || "-",
                    quantity_before: stock.quantity,
                    quantity_after: stock.quantity,
                })),
            };

            showStockOpname.value = true;
            return;
        }
        stockOpnameForm.value = {
            warehouse_id: filterWarehouse.value,

            items: stocks.value.map((stock) => ({
                stock_id: stock.stock_id || stock.id,

                medicine_name: stock.medicine?.name || "-",

                quantity_before: stock.quantity,

                quantity_after: stock.quantity,
            })),
        };

        showStockOpname.value = true;
    } catch (error) {
        console.error(error);

        ElMessage.error("Gagal membuka stock opname");
    }
};

const totalAlerts = computed(
    () => lowStockCount.value + expiredCount.value + expiringSoonCount.value,
);
const closeStockOpname = () => {
    showStockOpname.value = false;
};
/* =========================
   SAVE ADJUSTMENT
========================= */

const saveAdjustment = async () => {
    console.log("PAYLOAD ADJUSTMENT", adjustmentForm.value);

    try {
        // ======================
        // VALIDATION
        // ======================

        if (!adjustmentForm.value.stock_id) {
            return ElMessage.warning("Stock belum dipilih");
        }

        if (adjustmentForm.value.quantity_after < 0) {
            return ElMessage.warning("Stok tidak boleh minus");
        }

        if (!adjustmentForm.value.type) {
            return ElMessage.warning("Pilih tipe adjustment");
        }

        if (!adjustmentForm.value.reason) {
            return ElMessage.warning("Pilih alasan");
        }

        // ======================
        // CONFIRM
        // ======================

        await ElMessageBox.confirm(
            "Yakin ingin menyimpan adjustment stok?",
            "Konfirmasi",
            {
                type: "warning",
                confirmButtonText: "Ya",
                cancelButtonText: "Batal",
            },
        );

        savingAdjustment.value = true;

        // ======================
        // PAYLOAD
        // ======================

        const payload = {
            stock_id: Number(adjustmentForm.value.stock_id),

            quantity_after: Number(adjustmentForm.value.quantity_after),

            type: adjustmentForm.value.type,

            reason: adjustmentForm.value.reason,

            notes: adjustmentForm.value.notes || "",
        };

        console.log("FINAL PAYLOAD", payload);

        // ======================
        // API
        // ======================

        await api.post("stocks/adjustment", payload);

        ElMessage.success("Adjustment berhasil disimpan");

        closeAdjustment();

        await fetchStocks();
    } catch (error) {
        if (error === "cancel") return;

        console.error("SAVE ADJUSTMENT ERROR", error);

        console.log("BACKEND RESPONSE", error.response?.data);

        ElMessage.error(
            error.response?.data?.message || "Gagal menyimpan adjustment",
        );
    } finally {
        savingAdjustment.value = false;
    }
};
const saveStockOpname = async () => {
    try {
        savingOpname.value = true;

        const items = stockOpnameForm.value.items;

        // =========================
        // VALIDASI DATA KOSONG
        // =========================
        if (!items.length) {
            ElMessage.warning("Tidak ada data stock");

            return;
        }

        // =========================
        // VALIDASI MINUS
        // =========================
        const invalidQty = items.find((item) => item.quantity_after < 0);

        if (invalidQty) {
            ElMessage.error("Jumlah fisik tidak boleh minus");

            return;
        }

        // =========================
        // FILTER HANYA YANG BERUBAH
        // =========================
        const changedItems = items.filter(
            (item) =>
                Number(item.quantity_after) !== Number(item.quantity_before),
        );

        // =========================
        // TIDAK ADA PERUBAHAN
        // =========================
        if (!changedItems.length) {
            ElMessage.warning("Tidak ada perubahan stok");

            return;
        }

        // =========================
        // CONFIRM SAVE
        // =========================
        const confirmed = confirm(
            `Simpan ${changedItems.length} perubahan stok?`,
        );

        if (!confirmed) return;

        // =========================
        // API REQUEST
        // =========================
        await api.post("stocks/opname", {
            adjustments: changedItems.map((item) => ({
                stock_id: item.stock_id,

                quantity_after: item.quantity_after,
            })),
        });

        ElMessage.success("Stock opname berhasil");

        closeStockOpname();

        // refresh stock
        fetchStocks();
    } catch (error) {
        console.error(error);

        ElMessage.error(error.response?.data?.message || "Gagal stock opname");
    } finally {
        savingOpname.value = false;
    }
};

const totalQuantity = computed(() =>
    stocks.value.reduce((sum, stock) => sum + Number(stock.quantity || 0), 0),
);

const inventoryValue = computed(() =>
    stocks.value.reduce((sum, stock) => {
        const qty = Number(stock.quantity || 0);

        const price = Number(stock.medicine?.base_price) || 0;

        return sum + qty * price;
    }, 0),
);

const inventoryHealth = computed(() => {
    if (!stocks.value.length) return 100;

    const problematic = lowStockCount.value + expiredCount.value;

    return Math.max(
        0,
        Math.round(
            ((stocks.value.length - problematic) / stocks.value.length) * 100,
        ),
    );
});

const openMutation = () => {
    ElMessage.info("Fitur mutasi segera tersedia");
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat("id-ID").format(value || 0);
};
/* =========================
   LIFECYCLE
========================= */

onMounted(async () => {
    await fetchWarehouses();

    console.log("WAREHOUSES LOADED:", warehouses.value);

    await fetchStocks();
});

onUnmounted(() => {
    clearTimeout(searchTimeout.value);
});

watch([filterWarehouse, filterType, searchQuery], () => {
    clearTimeout(searchTimeout.value);

    searchTimeout.value = setTimeout(() => {
        fetchStocks();
    }, 300);
});

watch(stocks, (val) => {
    console.log("STOCKS UPDATED:", val);
});
</script>

<style scoped>
.drawer-enter-active,
.drawer-leave-active {
    transition: all 0.3s ease;
}

.drawer-enter-from,
.drawer-leave-to {
    opacity: 0;
}

.drawer-enter-from aside,
.drawer-leave-to aside {
    transform: translateX(100%);
}
</style>
