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

            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-white text-4xl font-black">
                        Master Produk Farmasi
                    </h1>

                    <p class="text-white mt-2">
                        Kelola seluruh produk, kategori, supplier dan harga
                        obat.
                    </p>
                </div>
            </div>
        </div>

        <!-- STATS -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">
            <!-- TOTAL OBAT -->
            <div class="bg-white rounded-2xl border p-6 shadow-sm">
                <div class="flex justify-between">
                    <div>
                        <p class="text-slate-500 text-sm">Total Obat</p>

                        <h2 class="text-3xl font-bold text-blue-600">
                            {{ stats.total_medicines }}
                        </h2>
                    </div>

                    <div
                        class="w-14 h-14 rounded-2xl bg-blue-100 flex items-center justify-center"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            class="w-7 h-7 text-blue-600"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375H14.25"
                            />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- KATEGORI -->
            <div class="bg-white rounded-2xl border p-6 shadow-sm">
                <div class="flex justify-between">
                    <div>
                        <p class="text-slate-500 text-sm">Kategori</p>

                        <h2 class="text-3xl font-bold text-emerald-600">
                            {{ categories.length }}
                        </h2>
                    </div>
                </div>
            </div>

            <!-- SUPPLIER -->
            <div class="bg-white rounded-2xl border p-6 shadow-sm">
                <div class="flex justify-between">
                    <div>
                        <p class="text-slate-500 text-sm">Supplier</p>

                        <h2 class="text-3xl font-bold text-orange-600">
                            {{ suppliers.length }}
                        </h2>
                    </div>
                </div>
            </div>

            <!-- PRODUK AKTIF -->
            <div class="bg-white rounded-2xl border p-6 shadow-sm">
                <div class="flex justify-between">
                    <div>
                        <p class="text-slate-500 text-sm">Produk Aktif</p>

                        <h2 class="text-3xl font-bold text-purple-600">
                            {{
                                medicines.filter(
                                    (item) => item.status === "aktif",
                                ).length
                            }}
                        </h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- FILTER SECTION -->
        <div
            class="bg-white rounded-[32px] border border-slate-200 shadow-sm p-6 space-y-5"
        >
            <div
                class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4"
            >
                <div class="flex items-center gap-3">
                    <Search class="w-6 h-6 text-emerald-600" />

                    <h2 class="text-2xl font-bold text-slate-900">
                        Filter Produk
                    </h2>
                </div>

                <div class="flex gap-3">
                    <button
                        @click="fetchMedicines"
                        class="h-12 px-5 rounded-xl bg-emerald-600 text-white flex items-center gap-2"
                    >
                        <RefreshCw class="w-4 h-4" />
                        Refresh
                    </button>
                </div>
            </div>

            <!-- FILTER GRID -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
                <!-- SEARCH -->
                <div class="xl:col-span-2">
                    <label
                        class="block text-sm font-semibold text-slate-700 mb-2"
                    >
                        Cari Obat
                    </label>

                    <div class="relative">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari nama obat / kode..."
                            class="w-full h-12 pl-12 pr-4 rounded-xl border border-slate-200 bg-white"
                        />
                        <Search
                            class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400"
                        />
                    </div>
                </div>

                <!-- CATEGORY -->
                <div>
                    <label
                        class="block text-sm font-semibold text-slate-700 mb-2"
                    >
                        Kategori
                    </label>

                    <select
                        v-model="filterCategory"
                        class="w-full h-14 rounded-[20px] border border-slate-200 bg-slate-50 px-4 focus:outline-none focus:ring-4 focus:ring-emerald-100"
                    >
                        <option value="">Semua</option>

                        <option
                            v-for="cat in categories"
                            :key="cat.id"
                            :value="cat.id"
                        >
                            {{ cat.name }}
                        </option>
                    </select>
                </div>

                <!-- SUPPLIER -->
                <div>
                    <label
                        class="block text-sm font-semibold text-slate-700 mb-2"
                    >
                        Supplier
                    </label>

                    <select
                        v-model="filterSupplier"
                        class="w-full h-14 rounded-[20px] border border-slate-200 bg-slate-50 px-4 focus:outline-none focus:ring-4 focus:ring-emerald-100"
                    >
                        <option value="">Semua</option>

                        <option
                            v-for="sup in suppliers"
                            :key="sup.id"
                            :value="sup.id"
                        >
                            {{ sup.name }}
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <!-- PREMIUM TABLE -->
        <div
            class="bg-white rounded-[32px] border border-slate-200 shadow-sm overflow-hidden"
        >
            <!-- HEADER -->
            <div
                class="px-7 py-6 border-b border-slate-100 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4"
            >
                <div>
                    <h2 class="text-2xl font-bold text-slate-900">
                        <Pill></Pill>Daftar Obat
                    </h2>

                    <p class="text-slate-500 text-sm mt-1">
                        Total
                        <span class="font-bold text-emerald-600">
                            {{ medicines.length }}
                        </span>
                        obat
                    </p>
                </div>

                <button
                    @click="openForm()"
                    class="h-12 px-5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold flex items-center gap-2"
                >
                    <Plus class="w-4 h-4" />
                    Tambah Produk
                </button>
            </div>

            <!-- TABLE -->
            <div class="w-full overflow-x-auto">
                <table class="w-full min-w-[1450px]">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th
                                class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500"
                            >
                                Produk
                            </th>

                            <th
                                class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500"
                            >
                                Kategori
                            </th>

                            <th
                                class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500"
                            >
                                Supplier
                            </th>

                            <th
                                class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-slate-500"
                            >
                                Harga
                            </th>

                            <th
                                class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wider text-slate-500"
                            >
                                Satuan
                            </th>

                            <th
                                class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wider text-slate-500"
                            >
                                Status
                            </th>

                            <th
                                class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wider text-slate-500"
                            >
                                Aksi
                            </th>
                        </tr>
                    </thead>

                    <tbody v-if="!loading">
                        <tr
                            v-for="medicine in medicines"
                            :key="medicine.id"
                            class="border-b border-slate-100 hover:bg-slate-50 transition-all duration-200"
                        >
                            <!-- OBAT -->
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-4">
                                    <div
                                        class="w-12 h-12 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-center"
                                    >
                                        <Pill
                                            class="w-5 h-5 text-emerald-600"
                                        />
                                    </div>

                                    <div>
                                        <h3
                                            class="font-semibold text-slate-900"
                                        >
                                            {{ medicine.name }}
                                        </h3>

                                        <p class="text-xs text-slate-400 mt-1">
                                            {{ medicine.code }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <!-- KATEGORI -->
                            <td class="px-6 py-5">
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-xl bg-blue-50 text-blue-700 text-xs font-semibold"
                                >
                                    {{ medicine.category?.name || "-" }}
                                </span>
                            </td>

                            <!-- SUPPLIER -->
                            <td class="px-6 py-5">
                                <div>
                                    <p class="font-medium text-slate-800">
                                        {{ medicine.supplier?.name || "-" }}
                                    </p>
                                </div>
                            </td>

                            <!-- HARGA -->
                            <td class="px-6 py-5 text-right">
                                <div>
                                    <p class="text-xs text-slate-400">Pokok</p>

                                    <p class="font-medium text-slate-700">
                                        Rp{{ formatPrice(medicine.base_price) }}
                                    </p>

                                    <p class="text-xs text-slate-400 mt-2">
                                        Jual
                                    </p>

                                    <p class="font-bold text-emerald-600">
                                        Rp{{
                                            formatPrice(medicine.selling_price)
                                        }}
                                    </p>
                                </div>
                            </td>

                            <td class="px-6 py-5 text-center">
                                <span
                                    class="inline-flex px-3 py-1 rounded-lg bg-slate-100 text-slate-700 text-xs font-semibold"
                                >
                                    {{ medicine.unit }}
                                </span>
                            </td>

                            <!-- STATUS -->
                            <td class="px-6 py-5 text-center">
                                <span
                                    v-if="medicine.status === 'aktif'"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-semibold"
                                >
                                    <CheckCircle class="w-3 h-3" />
                                    Aktif
                                </span>

                                <span
                                    v-else
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-red-50 text-red-700 border border-red-200 text-xs font-semibold"
                                >
                                    <X class="w-3 h-3" />
                                    Nonaktif
                                </span>
                            </td>

                            <!-- ACTION -->
                            <td class="px-6 py-5">
                                <div class="flex justify-center gap-2">
                                    <button
                                        @click="viewDetail(medicine)"
                                        class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 flex items-center justify-center transition"
                                    >
                                        <Eye class="w-4 h-4" />
                                    </button>

                                    <button
                                        @click="openForm(medicine)"
                                        class="w-9 h-9 rounded-xl bg-amber-50 hover:bg-amber-100 flex items-center justify-center transition"
                                    >
                                        <Pencil class="w-4 h-4" />
                                    </button>

                                    <button
                                        @click="archiveMedicine(medicine)"
                                        class="w-9 h-9 rounded-xl bg-red-50 hover:bg-red-100 flex items-center justify-center transition"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>

                    <!-- LOADING -->
                    <tbody v-else>
                        <tr>
                            <td colspan="9" class="py-20 text-center">
                                <div class="flex flex-col items-center gap-4">
                                    <!-- Spinner -->
                                    <div
                                        class="w-12 h-12 border-4 border-emerald-500 border-t-transparent rounded-full animate-spin"
                                    />

                                    <p class="text-slate-500 font-medium">
                                        Memuat data obat...
                                    </p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div
                    v-if="!loading && medicines.length === 0"
                    class="py-20 text-center"
                >
                    <Pill class="w-12 h-12 mx-auto text-slate-300" />

                    <h3 class="mt-4 font-semibold text-slate-700">
                        Belum ada produk
                    </h3>

                    <p class="text-slate-400 mt-1">
                        Tambahkan produk farmasi pertama.
                    </p>
                </div>
            </div>
        </div>

        <!-- DETAIL MODAL -->
        <div
            v-if="selectedMedicine"
            class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
        >
            <div
                class="bg-white rounded-3xl shadow-2xl w-full max-w-[1200px] max-h-[90vh] overflow-y-auto"
            >
                <!-- HEADER -->
                <div class="flex items-center gap-4">
                    <div
                        class="w-14 h-14 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-center"
                    >
                        <Pill class="w-7 h-7 text-emerald-600" />
                    </div>

                    <div>
                        <h2 class="text-2xl font-bold text-slate-900">
                            Detail Produk Farmasi
                        </h2>

                        <p class="text-sm text-slate-500">
                            Informasi master produk dan harga
                        </p>
                    </div>
                </div>

                <div
                    class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-emerald-600 via-green-600 to-teal-600 p-8 text-white"
                >
                    <div
                        class="absolute -right-10 -top-10 w-48 h-48 rounded-full bg-white/10"
                    />

                    <div
                        class="absolute right-16 bottom-0 w-24 h-24 rounded-full bg-white/10"
                    />
                    <div class="flex justify-between">
                        <div>
                            <p class="text-emerald-100 text-sm">Kode Produk</p>

                            <h2 class="text-3xl font-black">
                                {{ selectedMedicine.code }}
                            </h2>

                            <h3 class="mt-2 text-xl font-semibold">
                                {{ selectedMedicine.name }}
                            </h3>
                        </div>

                        <span
                            class="px-4 py-2 bg-white/20 rounded-xl font-semibold"
                        >
                            {{ selectedMedicine.unit }}
                        </span>
                    </div>
                </div>

                <!-- CONTENT -->
                <div class="p-8 space-y-6">
                    <!-- BASIC INFO -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div
                            class="bg-white border border-slate-200 rounded-3xl p-5 shadow-sm"
                        >
                            <p
                                class="text-xs uppercase text-slate-400 tracking-wider"
                            >
                                Nama Produk
                            </p>

                            <h3 class="text-xl font-bold text-gray-800">
                                {{ selectedMedicine.name }}
                            </h3>
                        </div>

                        <div
                            class="bg-white border border-slate-200 rounded-3xl p-5 shadow-sm"
                        >
                            <p class="text-sm text-gray-500 mb-1">Kode Obat</p>

                            <h3 class="text-xl font-bold text-gray-800">
                                {{ selectedMedicine.code }}
                            </h3>
                        </div>

                        <div
                            class="bg-white border border-slate-200 rounded-3xl p-5 shadow-sm"
                        >
                            <p class="text-sm text-gray-500 mb-1">Satuan</p>

                            <h3 class="font-semibold text-gray-700">
                                {{ selectedMedicine.unit }}
                            </h3>
                        </div>

                        <div
                            class="bg-white border border-slate-200 rounded-3xl p-5 shadow-sm"
                        >
                            <p class="text-sm text-gray-500 mb-1">Kategori</p>

                            <h3 class="font-semibold text-gray-700">
                                {{ selectedMedicine.category?.name || "-" }}
                            </h3>
                        </div>

                        <div
                            class="bg-white border border-slate-200 rounded-3xl p-5 shadow-sm"
                        >
                            <p class="text-sm text-gray-500 mb-1">Supplier</p>

                            <h3 class="font-semibold text-gray-700">
                                {{ selectedMedicine.supplier?.name || "-" }}
                            </h3>
                        </div>
                    </div>

                    <!-- PRICE -->
                    <div
                        class="rounded-3xl border border-emerald-100 bg-emerald-50 p-6"
                    >
                        <h3 class="text-lg font-bold text-slate-900 mb-5">
                            Informasi Harga
                        </h3>

                        <div class="grid grid-cols-3 gap-4">
                            <div class="bg-white rounded-2xl p-5">
                                <p class="text-sm text-slate-500">
                                    Harga Pokok
                                </p>

                                <h3
                                    class="text-xl font-bold text-slate-900 mt-2"
                                >
                                    Rp{{
                                        formatPrice(selectedMedicine.base_price)
                                    }}
                                </h3>
                            </div>

                            <div class="bg-white rounded-2xl p-5">
                                <p class="text-sm text-slate-500">Markup</p>

                                <h3
                                    class="text-xl font-bold text-amber-600 mt-2"
                                >
                                    {{ selectedMedicine.markup_percentage }}%
                                </h3>
                            </div>

                            <div class="bg-white rounded-2xl p-5">
                                <p class="text-sm text-slate-500">Harga Jual</p>

                                <h3
                                    class="text-xl font-black text-emerald-600 mt-2"
                                >
                                    Rp{{
                                        formatPrice(
                                            selectedMedicine.selling_price,
                                        )
                                    }}
                                </h3>
                            </div>
                        </div>
                    </div>

                    <!-- DESCRIPTION -->
                    <div
                        v-if="selectedMedicine.description"
                        class="bg-gray-50 rounded-3xl p-6"
                    >
                        <h3 class="text-xl font-bold mb-3">📝 Deskripsi</h3>

                        <p class="text-gray-700 leading-relaxed">
                            {{ selectedMedicine.description }}
                        </p>
                    </div>

                    <!-- FOOTER -->
                    <button
                        @click="selectedMedicine = null"
                        class="w-full py-4 rounded-2xl bg-gray-200 hover:bg-gray-300 transition font-bold text-gray-700"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>

        <!-- CREATE / EDIT MODAL -->
        <div
            v-if="showForm"
            class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
        >
            <div
                class="bg-white rounded-3xl shadow-2xl w-full max-w-5xl max-h-[90vh] overflow-y-auto"
            >
                <!-- HEADER -->
                <div
                    class="sticky top-0 bg-white border-b px-8 py-6 flex justify-between items-center"
                >
                    <div>
                        <h2 class="text-2xl font-bold text-slate-900">
                            {{
                                editingId
                                    ? "Edit Produk Farmasi"
                                    : "Tambah Produk Farmasi"
                            }}
                        </h2>

                        <p class="text-sm text-slate-500 mt-1">
                            Lengkapi informasi produk dengan benar
                        </p>

                        <p class="text-gray-500">
                            Isi data obat dengan lengkap
                        </p>
                    </div>

                    <button
                        @click="closeForm"
                        class="w-12 h-12 rounded-full bg-gray-100 hover:bg-gray-200 transition text-2xl"
                    >
                        ✕
                    </button>
                </div>

                <!-- FORM -->
                <form
                    @submit.prevent="saveMedicine"
                    class="sticky top-0 z-20 bg-white/95 backdrop-blur"
                >
                    <!-- BASIC -->
                    <div class="bg-slate-50 rounded-3xl p-6">
                        <h3 class="font-bold text-slate-900 mb-5">
                            Informasi Dasar
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block font-semibold mb-2">
                                    Kode Obat *
                                </label>

                                <input
                                    v-model="form.code"
                                    type="text"
                                    required
                                    class="w-full px-4 py-3 rounded-2xl border border-gray-300 focus:ring-4 focus:ring-green-200 outline-none"
                                />
                            </div>

                            <div>
                                <label class="block font-semibold mb-2">
                                    Nama Obat *
                                </label>

                                <input
                                    v-model="form.name"
                                    type="text"
                                    required
                                    class="w-full px-4 py-3 rounded-2xl border border-gray-300 focus:ring-4 focus:ring-green-200 outline-none"
                                />
                            </div>
                        </div>

                        <!-- DESCRIPTION -->
                        <div>
                            <label class="block font-semibold mb-2">
                                Deskripsi
                            </label>

                            <textarea
                                v-model="form.description"
                                rows="4"
                                class="w-full px-4 py-3 rounded-2xl border border-gray-300 focus:ring-4 focus:ring-green-200 outline-none resize-none"
                                placeholder="Deskripsi obat..."
                            />
                        </div>
                    </div>

                    <!-- CATEGORY + SUPPLIER -->
                    <div
                        class="bg-white border border-slate-200 rounded-3xl p-6"
                    >
                        <h3 class="font-bold text-slate-900 mb-5">
                            Klasifikasi Produk
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block font-semibold mb-2">
                                    Kategori *
                                </label>

                                <select
                                    v-model.number="form.category_id"
                                    required
                                    class="w-full px-4 py-3 rounded-2xl border border-gray-300 focus:ring-4 focus:ring-green-200 outline-none"
                                >
                                    <option value="">Pilih kategori</option>

                                    <option
                                        v-for="cat in categories"
                                        :key="cat.id"
                                        :value="cat.id"
                                    >
                                        {{ cat.name }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block font-semibold mb-2">
                                    Supplier *
                                </label>

                                <select
                                    v-model.number="form.supplier_id"
                                    required
                                    class="w-full px-4 py-3 rounded-2xl border border-gray-300 focus:ring-4 focus:ring-green-200 outline-none"
                                >
                                    <option value="">Pilih supplier</option>

                                    <option
                                        v-for="sup in suppliers"
                                        :key="sup.id"
                                        :value="sup.id"
                                    >
                                        {{ sup.name }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block font-semibold mb-2">
                                    Satuan *
                                </label>
                                <select
                                    v-model="form.unit"
                                    class="w-full h-12 rounded-xl border border-slate-200 px-4"
                                >
                                    <option value="tablet">Tablet</option>
                                    <option value="kapsul">Kapsul</option>
                                    <option value="strip">Strip</option>
                                    <option value="botol">Botol</option>
                                    <option value="tube">Tube</option>
                                    <option value="sachet">Sachet</option>
                                    <option value="vial">Vial</option>
                                    <option value="ampul">Ampul</option>
                                    <option value="pcs">PCS</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- PRICE -->
                    <div class="bg-emerald-50 rounded-3xl p-6">
                        <h3 class="text-xl font-bold mb-5 text-green-700">
                            💰 Harga Obat
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            <div>
                                <label class="block font-semibold mb-2">
                                    Harga Pokok *
                                </label>

                                <input
                                    v-model.number="form.base_price"
                                    type="number"
                                    min="0"
                                    required
                                    class="w-full px-4 py-3 rounded-2xl border border-gray-300 focus:ring-4 focus:ring-green-200 outline-none"
                                />
                            </div>

                            <div>
                                <label class="block font-semibold mb-2">
                                    Markup (%)
                                </label>

                                <input
                                    v-model.number="form.markup_percentage"
                                    type="number"
                                    min="0"
                                    class="w-full px-4 py-3 rounded-2xl border border-gray-300 focus:ring-4 focus:ring-green-200 outline-none"
                                />
                            </div>

                            <div>
                                <label class="block font-semibold mb-2">
                                    Harga Jual
                                </label>

                                <input
                                    :value="calculateSellingPrice"
                                    readonly
                                    class="w-full px-4 py-3 rounded-2xl border border-gray-200 bg-gray-100 font-bold text-green-700"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- STATUS -->
                    <div class="bg-gray-50 rounded-2xl p-5">
                        <label class="flex items-center justify-between">
                            <div>
                                <h4 class="font-semibold">Status Produk</h4>

                                <p class="text-sm text-slate-500">
                                    Produk dapat digunakan dalam transaksi
                                </p>
                            </div>

                            <input
                                v-model="form.status"
                                type="checkbox"
                                true-value="aktif"
                                false-value="tidak_aktif"
                                class="toggle"
                            />
                        </label>
                    </div>

                    <!-- ACTION BUTTON -->
                    <div class="flex gap-3 pt-4">
                        <button
                            type="submit"
                            :disabled="savingForm"
                            class="flex-1 h-14 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold flex items-center justify-center gap-2"
                        >
                            <Save class="w-4 h-4" />

                            <span>Simpan Produk</span>
                        </button>

                        <button
                            type="button"
                            @click="closeForm"
                            class="flex-1 h-14 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold flex items-center justify-center gap-2"
                        >
                            <X class="w-4 h-4" />
                            <span>Tutup</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from "vue";
import api from "@/services/api";
import {
    Package2,
    Plus,
    Search,
    RefreshCw,
    Eye,
    Pencil,
    Trash2,
    Save,
    X,
    BadgeDollarSign,
    FileText,
    Pill,
    CheckCircle,
} from "lucide-vue-next";

import { ElMessage, ElMessageBox } from "element-plus";

/* =========================
   STATE
========================= */

const medicines = ref([]);
const categories = ref([]);
const suppliers = ref([]);

const loading = ref(false);
const savingForm = ref(false);

const showForm = ref(false);
const selectedMedicine = ref(null);

const editingId = ref(null);

const searchQuery = ref("");

const filterCategory = ref("");

const filterSupplier = ref("");

const stats = ref({
    total_medicines: 0,
    total_categories: 0,
    total_suppliers: 0,
    active_medicines: 0,
});

/* =========================
   FORM
========================= */

const defaultForm = () => ({
    code: "",
    name: "",
    description: "",

    category_id: "",
    supplier_id: "",

    base_price: 0,
    markup_percentage: 25,
    unit: "pcs",

    status: "aktif",
});

const form = ref(defaultForm());

/* =========================
   COMPUTED
========================= */

const calculateSellingPrice = computed(() => {
    const base = Number(form.value.base_price) || 0;

    const markup = Number(form.value.markup_percentage) || 0;

    return Math.round(base + (base * markup) / 100);
});

/* =========================
   HELPERS
========================= */

const formatPrice = (price) => {
    return new Intl.NumberFormat("id-ID").format(price || 0);
};
const formatDate = (date) => {
    if (!date) return "-";

    return new Date(date).toLocaleDateString("id-ID", {
        day: "2-digit",
        month: "long",
        year: "numeric",
    });
};
/* =========================
   FETCH MEDICINES
========================= */

const fetchMedicines = async () => {
    loading.value = true;

    try {
        const params = {
            per_page: 100,
        };

        if (searchQuery.value) {
            params.search = searchQuery.value;
        }

        if (filterCategory.value) {
            params.category_id = filterCategory.value;
        }

        if (filterSupplier.value) {
            params.supplier_id = filterSupplier.value;
        }

        const response = await api.get("/medicines", { params });
        medicines.value = response.data.data?.data || [];

        stats.value = {
            total_medicines: medicines.value.length,

            total_categories: categories.value.length,

            total_suppliers: suppliers.value.length,

            active_medicines: medicines.value.filter(
                (item) => item.status === "aktif",
            ).length,
        };
    } catch (error) {
        console.error("ERROR MEDICINES:", error);

        console.log("Response:", error.response?.data);

        ElMessage.error(
            error.response?.data?.message ||
                error.message ||
                "Gagal memuat data obat",
        );
    } finally {
        loading.value = false;
    }
};

/* =========================
   FETCH CATEGORY
========================= */

const fetchCategories = async () => {
    try {
        const response = await api.get("/categories", {
            params: {
                per_page: 100,
            },
        });

        categories.value = response.data.data?.data || [];
    } catch (error) {
        console.error(error);
    }
};

/* =========================
   FETCH SUPPLIER
========================= */

const fetchSuppliers = async () => {
    try {
        const response = await api.get("/suppliers", {
            params: {
                per_page: 100,
            },
        });

        suppliers.value = response.data.data?.data || [];
    } catch (error) {
        console.error(error);
    }
};
/* =========================
   OPEN FORM
========================= */

const openForm = (medicine = null) => {
    if (medicine) {
        editingId.value = medicine.id;

        form.value = {
            code: medicine.code || "",

            name: medicine.name || "",

            description: medicine.description || "",

            category_id: medicine.category_id || "",

            supplier_id: medicine.supplier_id || "",

            base_price: Number(medicine.base_price) || 0,

            markup_percentage: Number(medicine.markup_percentage) || 25,

            unit: medicine.unit || "pcs",

            status: medicine.status || "aktif",
        };
    } else {
        editingId.value = null;

        form.value = defaultForm();
    }

    showForm.value = true;
};

/* =========================
   CLOSE FORM
========================= */

const closeForm = () => {
    showForm.value = false;

    editingId.value = null;

    form.value = defaultForm();
};
/* =========================
   SAVE MEDICINE
========================= */

const saveMedicine = async () => {
    savingForm.value = true;

    try {
        const payload = {
            ...form.value,
            base_price: Number(form.value.base_price),
            markup_percentage: Number(form.value.markup_percentage),
            selling_price: calculateSellingPrice.value,
        };

        if (editingId.value) {
            await api.put(`/medicines/${editingId.value}`, payload);

            ElMessage.success("Obat berhasil diperbarui");
        } else {
            await api.post("/medicines", payload);

            ElMessage.success("Obat berhasil ditambahkan");
        }

        showForm.value = false;

        editingId.value = null;

        form.value = defaultForm();

        await fetchMedicines();
    } catch (error) {
        console.error(error);

        ElMessage.error(
            error.response?.data?.message || "Gagal menyimpan obat",
        );
    } finally {
        savingForm.value = false;
    }
};

/* =========================
   DETAIL
========================= */

const viewDetail = (medicine) => {
    selectedMedicine.value = medicine;
};

const archiveMedicine = async (medicine) => {
    try {
        await ElMessageBox.confirm(`Arsipkan ${medicine.name}?`, "Konfirmasi", {
            confirmButtonText: "Ya",
            cancelButtonText: "Batal",
            type: "warning",
        });

        await api.delete(`/medicines/${medicine.id}`);

        ElMessage.success("Obat berhasil diarsipkan");

        fetchMedicines();
    } catch (error) {
        if (error !== "cancel") {
            console.error(error);

            ElMessage.error("Gagal mengarsipkan obat");
        }
    }
};

/* =========================
   RESET FILTER
========================= */
const resetFilters = () => {
    searchQuery.value = "";
    filterCategory.value = "";
    filterSupplier.value = "";

    fetchMedicines();
};

/* =========================
   REFRESH DATA
========================= */

const refreshData = async () => {
    await Promise.all([fetchMedicines(), fetchCategories(), fetchSuppliers()]);
};

/* =========================
   WATCH SEARCH
========================= */

let searchTimeout = null;

watch(searchQuery, (value) => {
    clearTimeout(searchTimeout);

    searchTimeout = setTimeout(() => {
        fetchMedicines();
    }, 500);
});

/* =========================
   CLEANUP
========================= */

onUnmounted(() => {
    clearTimeout(searchTimeout);
});

/* =========================
   WATCH FILTER
========================= */

watch([filterCategory, filterSupplier], () => {
    fetchMedicines();
});

/* =========================
   MOUNTED
========================= */

onMounted(async () => {
    await refreshData();
});
</script>

<style scoped>
.page-wrapper {
    @apply space-y-8;
}

.card-premium {
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(18px);

    border: 1px solid rgba(226, 232, 240, 0.7);

    box-shadow: 0 10px 40px rgba(15, 23, 42, 0.08);

    transition: all 0.3s ease;
}

.card-premium:hover {
    transform: translateY(-2px);
}

.stat-card {
    @apply relative overflow-hidden rounded-[30px] p-7;
}

.stat-card::before {
    content: "";

    position: absolute;
    top: -30px;
    right: -30px;

    width: 120px;
    height: 120px;

    border-radius: 999px;

    background: rgba(255, 255, 255, 0.2);
}

.glass-filter {
    background: linear-gradient(
        135deg,
        rgba(255, 255, 255, 0.95),
        rgba(248, 250, 252, 0.9)
    );

    backdrop-filter: blur(20px);

    border: 1px solid rgba(226, 232, 240, 0.7);

    box-shadow: 0 12px 40px rgba(15, 23, 42, 0.06);
}

.filter-label {
    @apply block text-sm font-bold text-slate-700 mb-2;
}

.premium-input {
    width: 100%;

    border-radius: 20px;

    border: 1px solid rgb(226 232 240);

    background: white;

    padding: 14px 18px;

    font-size: 14px;
    font-weight: 500;

    color: rgb(30 41 59);

    transition: all 0.25s ease;
}

.premium-input:focus {
    outline: none;

    border-color: rgb(16 185 129);

    box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.12);
}

.premium-input::placeholder {
    color: rgb(148 163 184);
}

.table-head {
    @apply px-6 py-5 text-sm font-black uppercase tracking-wider;
}

.table-cell {
    @apply px-6 py-5 text-sm text-slate-700;
}

.stock-badge {
    @apply px-4 py-2 rounded-full text-xs font-black text-white inline-flex justify-center items-center min-w-[55px];
}

.action-btn {
    @apply w-11 h-11 rounded-2xl text-white flex items-center justify-center transition font-bold shadow-md;
}

.action-btn:hover {
    transform: translateY(-2px) scale(1.04);
}

.form-label {
    @apply block text-sm font-bold text-slate-700 mb-2;
}

.detail-card {
    @apply rounded-[24px] bg-slate-50 p-5 border border-slate-200;
}

.detail-label {
    @apply text-sm text-slate-500 mb-1 font-semibold;
}

.detail-value {
    @apply text-lg font-bold text-slate-900;
}

/* SCROLLBAR */
::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: transparent;
}

::-webkit-scrollbar-thumb {
    background: rgba(148, 163, 184, 0.4);

    border-radius: 999px;
}

::-webkit-scrollbar-thumb:hover {
    background: rgba(100, 116, 139, 0.6);
}

/* RESPONSIVE */
@media (max-width: 1024px) {
    .table-head,
    .table-cell {
        @apply px-4 py-4 text-xs;
    }

    .premium-input {
        padding: 12px 14px;
    }
}

@media (max-width: 768px) {
    .stat-card {
        @apply p-5;
    }

    .action-btn {
        @apply w-9 h-9 text-xs;
    }
}
</style>
