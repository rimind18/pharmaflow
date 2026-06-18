<template>
 <div class="w-full min-h-screen bg-slate-50 p-6">
  <div class="space-y-6">

    <!-- HEADER -->
<div
        class="bg-gradient-to-r from-green-600 to-emerald-600 rounded-3xl shadow-xl p-8 text-white"
      >
  <div
    class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5"
  >
    <div>
      <h1
        class="text-4xl font-bold mb-2"
      >
        📦 Manajemen Stok
      </h1>

       <p class="text-green-100 text-lg">
      
        Kelola stok obat,
        expiry, dan
        adjustment gudang
      </p>
    </div>

    <div
      class="flex flex-wrap gap-3"
    >
      <button
  @click="openStockOpname"
  :disabled="!filterWarehouse"
  class="px-6 py-3 rounded-[20px]
  bg-orange-500 text-white
  font-bold hover:bg-orange-600
  transition shadow-sm
  disabled:opacity-40
  disabled:cursor-not-allowed"
>
📋 Stock Opname
</button>

      <button
       @click="openAdjustment(null)"
        class="px-6 py-3 rounded-[20px] bg-blue-600 text-white font-bold hover:bg-blue-700 transition shadow-sm"
      >
        🔄 Adjustment
      </button>
    </div>
  </div>
</div>

   <div
  class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5"
>
  <!-- TOTAL -->
  <div
    class="bg-white rounded-[28px] border border-slate-200 p-6 shadow-sm"
  >
    <p
      class="text-slate-500 text-sm font-semibold"
    >
      Total Item
    </p>

    <h2
      class="text-4xl font-black text-slate-900 mt-3"
    >
      {{ totalStocks }}
    </h2>
  </div>

  <!-- LOW STOCK -->
  <div
    class="bg-white rounded-[28px] border border-orange-100 p-6 shadow-sm"
  >
    <p
      class="text-orange-500 text-sm font-semibold"
    >
      Stok Menipis
    </p>

    <h2
      class="text-4xl font-black text-orange-600 mt-3"
    >
      {{ lowStockCount }}
    </h2>
  </div>

  <!-- EXPIRED -->
  <div
    class="bg-white rounded-[28px] border border-red-100 p-6 shadow-sm"
  >
    <p
      class="text-red-500 text-sm font-semibold"
    >
      Expired
    </p>

    <h2
      class="text-4xl font-black text-red-600 mt-3"
    >
      {{ expiredCount }}
    </h2>
  </div>

  <!-- EXP SOON -->
  <div
    class="bg-white rounded-[28px] border border-yellow-100 p-6 shadow-sm"
  >
    <p
      class="text-yellow-600 text-sm font-semibold"
    >
      Exp Soon
    </p>

    <h2
      class="text-4xl font-black text-yellow-600 mt-3"
    >
      {{
        expiringSoonCount
      }}
    </h2>
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

      <p class="text-slate-500">
        Cari dan filter stok
      </p>
    </div>

    <button
      @click="fetchStocks"
      class="px-5 py-3 rounded-2xl bg-blue-600 text-white font-semibold hover:bg-blue-700 transition shadow-md"
    >
      🔄 Refresh
    </button>
  </div>

  <!-- GRID FILTER -->
  <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">

    <!-- Gudang -->
    <div>
      <label class="block text-sm font-semibold text-slate-700 mb-2">
        Gudang
      </label>

      <select
        v-model="filterWarehouse"
        class="w-full h-[56px] rounded-2xl border border-slate-300 px-4 outline-none focus:ring-4 focus:ring-blue-100"
      >
        <option value="">
          Semua Gudang
        </option>

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
      <label class="block text-sm font-semibold text-slate-700 mb-2">
        Filter
      </label>

      <select
        v-model="filterType"
        class="w-full h-[56px] rounded-2xl border border-slate-300 px-4 outline-none focus:ring-4 focus:ring-blue-100"
      >
        <option value="">
          Semua
        </option>

        <option value="low_stock">
          Stok Menipis
        </option>

        <option value="expired">
          Expired
        </option>

        <option value="expiring_soon">
          Exp Soon (30d)
        </option>
      </select>
    </div>

    <!-- Search -->
    <div class="xl:col-span-2">
      <label class="block text-sm font-semibold text-slate-700 mb-2">
        Cari Obat
      </label>

      <input
        v-model="searchQuery"
        type="text"
        placeholder="Cari nama obat..."
        class="w-full h-[56px] rounded-2xl border border-slate-300 px-5 outline-none focus:ring-4 focus:ring-emerald-100"
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
      <h2
        class="text-2xl font-bold text-slate-800"
      >
        📦 Daftar Stok
      </h2>

      <p
        class="text-slate-500 text-sm mt-1"
      >
        Total
        <span
          class="font-bold text-blue-600"
        >
          {{ stocks.length }}
        </span>
        stok ditemukan
      </p>
    </div>

    <button
      @click="fetchStocks"
      class="px-5 py-3 rounded-2xl bg-blue-600 text-white font-semibold hover:bg-blue-700 transition"
    >
      🔄 Refresh
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

    <p
      class="text-slate-500 mt-4 font-medium"
    >
      Memuat data stok...
    </p>
  </div>

  <!-- EMPTY -->
  <div
    v-else-if="stocks.length === 0"
    class="py-24 flex flex-col items-center justify-center"
  >
    <div class="text-7xl mb-4">
      📦
    </div>

    <h3
      class="text-2xl font-bold text-slate-700"
    >
      Tidak ada stok
    </h3>

    <p
      class="text-slate-500 mt-2"
    >
      Data stok belum tersedia
    </p>
  </div>

  <!-- TABLE -->
  <div
    v-else
    class="overflow-x-auto"
  >
    <table
      class="w-full min-w-[1300px]"
    >
      <thead
        class="bg-slate-50 border-b border-slate-200"
      >
        <tr>
          <th
            class="px-6 py-5 text-left font-bold text-slate-700"
          >
            Obat
          </th>

          <th
            class="px-6 py-5 text-left font-bold text-slate-700"
          >
            Gudang
          </th>

          <th
            class="px-6 py-5 text-left font-bold text-slate-700"
          >
            Rak
          </th>

          <th
            class="px-6 py-5 text-center font-bold text-slate-700"
          >
            Qty
          </th>

          <th
            class="px-6 py-5 text-center font-bold text-slate-700"
          >
            Expired
          </th>

          <th
            class="px-6 py-5 text-center font-bold text-slate-700"
          >
            Status
          </th>

          <th
            class="px-6 py-5 text-center font-bold text-slate-700"
          >
            Aksi
          </th>
        </tr>
      </thead>

      <tbody>
        <tr
          v-for="stock in stocks"
          :key="stock.stock_id || stock.id"
          class="border-b border-slate-100 hover:bg-blue-50 transition"
        >
          <!-- MEDICINE -->
          <td class="px-6 py-5">
            <div>
              <h3
                class="font-bold text-slate-800"
              >
                {{
                  stock.medicine
                    ?.name || '-'
                }}
              </h3>

              <p
                class="text-sm text-slate-400 mt-1"
              >
                {{
                  stock.medicine
                    ?.code || '-'
                }}
              </p>
            </div>
          </td>

          <!-- GUDANG -->
          <td
            class="px-6 py-5 font-medium text-slate-700"
          >
            {{
              stock.warehouse
                ?.name || '-'
            }}
          </td>

          <!-- RAK -->
          <td
            class="px-6 py-5 text-slate-700"
          >
            {{
              stock.shelf?.code ||
              '-'
            }}
          </td>

          <!-- QTY -->
          <td
            class="px-6 py-5 text-center"
          >
            <span
              :class="[
                'px-4 py-2 rounded-full text-sm font-bold text-white',
                stock.quantity === 0
                  ? 'bg-red-500'
                  : stock.quantity <= (stock.medicine?.stock_minimum || 0)
                  ? 'bg-orange-500'
                  : 'bg-emerald-500'
              ]"
            >
              {{
                stock.quantity
              }}
            </span>
          </td>

          <!-- EXPIRED -->
          <td
            class="px-6 py-5 text-center"
          >
            <span
              :class="[
                'px-4 py-2 rounded-full text-sm font-bold',
                isExpired(stock.expired_date)
                  ? 'bg-red-100 text-red-700'
                  : isExpiringSoon(stock.expired_date)
                  ? 'bg-orange-100 text-orange-700'
                  : 'bg-green-100 text-green-700'
              ]"
            >
              {{
                formatDate(stock.expired_date)
              }}
            </span>
          </td>

          <!-- STATUS -->
          <td
            class="px-6 py-5 text-center"
          >
            <span
              v-if="isExpired(stock.expired_date)"
              class="px-4 py-2 rounded-full bg-red-100 text-red-700 font-bold text-sm"
            >
              ❌ Expired
            </span>

            <span
              v-else-if="isExpiringSoon(stock.expired_date)"
              class="px-4 py-2 rounded-full bg-orange-100 text-orange-700 font-bold text-sm"
            >
              ⚠️ Soon
            </span>

            <span
              v-else-if="stock.quantity === 0"
              class="px-4 py-2 rounded-full bg-red-100 text-red-700 font-bold text-sm"
            >
              🔴 Habis
            </span>

            <span
              v-else-if="stock.quantity <= (stock.medicine?.stock_minimum || 0)"
              class="px-4 py-2 rounded-full bg-yellow-100 text-yellow-700 font-bold text-sm"
            >
              🟡 Menipis
            </span>

            <span
              v-else
              class="px-4 py-2 rounded-full bg-emerald-100 text-emerald-700 font-bold text-sm"
            >
              ✅ Normal
            </span>
          </td>

          <!-- ACTION -->
          <td
            class="px-6 py-5 text-center"
          >
            <button
                @click="openAdjustment(stock)"
                 class="px-4 py-2 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-semibold transition"
            >
              🔄 Adjust
            </button>
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
            v-model.number="adjustmentForm.quantity_after"
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
          <option value="">
            Pilih Tipe
          </option>

          <option value="penambahan">
            ➕ Penambahan
          </option>

          <option value="pengurangan">
            ➖ Pengurangan
          </option>

          <option value="koreksi">
            🔄 Koreksi
          </option>
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
          <option value="">
            Pilih alasan
          </option>

          <option value="opname">
            📋 Stock Opname
          </option>

          <option value="rusak">
            💔 Barang Rusak
          </option>

          <option value="hilang">
            🔍 Barang Hilang
          </option>

          <option value="retur">
            ↩️ Retur Supplier
          </option>

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
              ? '⏳ Menyimpan...'
              : '✅ Simpan Adjustment'
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
        <h2 class="text-3xl font-bold">
          📋 Stock Opname
        </h2>

        <p>
          Cocokkan stok fisik
        </p>
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
            <th class="p-4 text-left">
              Obat
            </th>

            <th class="p-4 text-center">
              System
            </th>

            <th class="p-4 text-center">
              Fisik
            </th>

            <th class="p-4 text-center">
              Selisih
            </th>
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
                item.quantity_after > item.quantity_before
                  ? 'text-green-600'
                  : item.quantity_after < item.quantity_before
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
            ? '⏳ Menyimpan...'
            : '✅ Simpan Opname'
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
</div> <!-- space-y-6 -->
</div> <!-- w-full min-h-screen -->

</template>

<script setup>
import {
  ref,
  computed,
  watch,
  onMounted,
  onUnmounted
} from 'vue'

import api from '@/services/api'
import dayjs from 'dayjs'

import {
  ElMessage,
  ElMessageBox
} from 'element-plus'


const loading = ref(false)

const savingAdjustment = ref(false)

const savingOpname = ref(false)

const stocks = ref([])

const warehouses =
  ref([])

/* =========================
   FILTER & UI STATE
========================= */

const searchQuery =
  ref('')

const filterWarehouse =
  ref('')

const filterType =
  ref('')

const searchTimeout =
  ref(null)

const showAdjustmentForm =
  ref(false)

/* =========================
   FORM
========================= */

const defaultAdjustment =
  () => ({
    stock_id: '',

    medicine_name: '',

    quantity_before: 0,

    quantity_after: 0,

    type: '',

    reason: '',

    notes: '',
  })

const adjustmentForm =
  ref(defaultAdjustment())

/* =========================
   FORMATTER
========================= */

const formatDate = (
  date
) => {
  if (!date) return '-'

  return dayjs(date)
    .format(
      'DD MMM YYYY'
    )
}

const isExpired = (
  date
) => {
  if (!date)
    return false

  return dayjs(date)
    .isBefore(
      dayjs(),
      'day'
    )
}

const isExpiringSoon =
  (date) => {
    if (!date)
      return false

    return (
      dayjs(date).isAfter(
        dayjs()
      ) &&
      dayjs(date).isBefore(
        dayjs().add(
          30,
          'day'
        )
      )
    )
  }


/* =========================
   API FETCH
========================= */

const fetchStocks = async () => {
  loading.value = true

  try {
    let endpoint = 'stocks'

    const params = {
      per_page: 100
    }

    if (filterType.value === 'low_stock') {
      endpoint = 'stocks/low-stock'
    }

    if (filterType.value === 'expired') {
      endpoint = 'stocks/expired'
    }

    if (filterType.value === 'expiring_soon') {
      endpoint = 'stocks/expiring-soon'
    }

    if (filterWarehouse.value) {
      params.warehouse_id =
        filterWarehouse.value
    }

    if (searchQuery.value) {
      params.search =
        searchQuery.value
    }

    const response =
      await api.get(
        endpoint,
        { params }
      )

    console.log(
      'API RESPONSE',
      response.data
    )

    stocks.value =
      response?.data?.data?.data ||
      response?.data?.data ||
      response?.data ||
      []

    console.log(
      'STOCK RESULT',
      stocks.value
    )

  } catch (error) {

    console.error(
      'FETCH STOCK ERROR',
      error
    )

    ElMessage.error(
      error.response?.data?.message ||
      'Gagal memuat stok'
    )

    stocks.value = []

  } finally {
    loading.value = false
  }
}
const totalStocks =
  computed(() => {
    return stocks.value.length
  })

const lowStockCount =
  computed(() => {
    return stocks.value.filter(
      stock =>
        stock.quantity <=
        (stock.medicine?.stock_minimum || 0)
    ).length
  })

const expiredCount =
  computed(() => {
    return stocks.value.filter(
      stock =>
        isExpired(
          stock.expired_date
        )
    ).length
  })

const expiringSoonCount =
  computed(() => {
    return stocks.value.filter(
      stock =>
        isExpiringSoon(
          stock.expired_date
        )
    ).length
  })

/* =========================
   SEARCH AUTO
========================= */

const fetchWarehouses = async () => {
  try {
    console.log('LOAD WAREHOUSES')

    const response = await api.get('warehouses')

    console.log(
      'FULL WAREHOUSE RESPONSE:',
      response
    )

    console.log(
      'WAREHOUSE DATA:',
      response.data
    )

    // ambil semua kemungkinan struktur
    warehouses.value =
      response.data?.data?.data ||
      response.data?.data ||
      response.data ||
      []

    console.log(
      'FINAL WAREHOUSES:',
      warehouses.value
    )

  } catch (error) {
    console.error(
      'WAREHOUSE ERROR:',
      error
    )

    ElMessage.error(
      'Gagal memuat gudang'
    )
  }
}

const handleSearch = () => {
  clearTimeout(
    searchTimeout.value
  )

  searchTimeout.value =
    setTimeout(() => {
      fetchStocks()
    }, 500)
}
/* =========================
   MODAL
========================= */

const selectedStock = ref(null)

const closeAdjustment = () => {
  showAdjustmentForm.value = false
  selectedStock.value = null

  adjustmentForm.value =
    defaultAdjustment()
}

const openAdjustment = (stock = null) => {

  // adjustment manual dari tombol header
  if (!stock) {

    adjustmentForm.value =
      defaultAdjustment()

    showAdjustmentForm.value =
      true

    return
  }

  selectedStock.value = stock

  adjustmentForm.value = {
    stock_id:
      stock.stock_id ??
      stock.id ??
      '',

    medicine_name:
      stock.medicine?.name || '',

    quantity_before:
      Number(stock.quantity || 0),

    quantity_after:
      Number(stock.quantity || 0),

    type: '',
    reason: '',
    notes: '',
  }

  console.log(
    'OPEN ADJUSTMENT',
    adjustmentForm.value
  )

  showAdjustmentForm.value =
    true
}
/* =========================
   STOCK OPNAME
========================= */
const showStockOpname =
  ref(false)

const stockOpnameForm =
  ref({
    warehouse_id: '',
    items: []
  })

console.log(
  'WAREHOUSE SELECTED:',
  filterWarehouse.value
)
const openStockOpname =
  async () => {
    try {

      if (!filterWarehouse.value) {

  stockOpnameForm.value = {
    warehouse_id: 1,
    items: stocks.value.map(
      stock => ({
        stock_id: stock.stock_id || stock.id,
        medicine_name:
          stock.medicine?.name || '-',
        quantity_before:
          stock.quantity,
        quantity_after:
          stock.quantity
      })
    )
  }

  showStockOpname.value = true
  return
}
      stockOpnameForm.value =
        {
          warehouse_id:
            filterWarehouse.value,

          items:
            stocks.value.map(
              stock => ({
                stock_id:
                  stock.stock_id || stock.id,

                medicine_name:
                  stock.medicine
                    ?.name || '-',

                quantity_before:
                  stock.quantity,

                quantity_after:
                  stock.quantity
              })
            )
        }

      showStockOpname.value =
        true

    } catch (error) {
      console.error(error)

      ElMessage.error(
        'Gagal membuka stock opname'
      )
    }
  }

const closeStockOpname =
  () => {
    showStockOpname.value =
      false
  }
/* =========================
   SAVE ADJUSTMENT
========================= */

const saveAdjustment = async () => {

  console.log(
    'PAYLOAD ADJUSTMENT',
    adjustmentForm.value
  )

  try {

    // ======================
    // VALIDATION
    // ======================

    if (
      !adjustmentForm.value.stock_id
    ) {

      return ElMessage.warning(
        'Stock belum dipilih'
      )
    }

    if (
      adjustmentForm.value
        .quantity_after < 0
    ) {

      return ElMessage.warning(
        'Stok tidak boleh minus'
      )
    }

    if (
      !adjustmentForm.value.type
    ) {

      return ElMessage.warning(
        'Pilih tipe adjustment'
      )
    }

    if (
      !adjustmentForm.value.reason
    ) {

      return ElMessage.warning(
        'Pilih alasan'
      )
    }

    // ======================
    // CONFIRM
    // ======================

    await ElMessageBox.confirm(
      'Yakin ingin menyimpan adjustment stok?',
      'Konfirmasi',
      {
        type: 'warning',
        confirmButtonText: 'Ya',
        cancelButtonText: 'Batal'
      }
    )

    savingAdjustment.value =
      true

    // ======================
    // PAYLOAD
    // ======================

    const payload = {

      stock_id:
        Number(
          adjustmentForm.value
            .stock_id
        ),

      quantity_after:
        Number(
          adjustmentForm.value
            .quantity_after
        ),

      type:
        adjustmentForm.value
          .type,

      reason:
        adjustmentForm.value
          .reason,

      notes:
        adjustmentForm.value
          .notes || ''
    }

    console.log(
      'FINAL PAYLOAD',
      payload
    )

    // ======================
    // API
    // ======================

    await api.post(
      'stocks/adjustment',
      payload
    )

    ElMessage.success(
      'Adjustment berhasil disimpan'
    )

    closeAdjustment()

    await fetchStocks()

  } catch (error) {

    if (
      error === 'cancel'
    ) return

    console.error(
      'SAVE ADJUSTMENT ERROR',
      error
    )

    console.log(
      'BACKEND RESPONSE',
      error.response?.data
    )

    ElMessage.error(
      error.response?.data
        ?.message ||
      'Gagal menyimpan adjustment'
    )

  } finally {

    savingAdjustment.value =
      false
  }
}
const saveStockOpname = async () => {
  try {

    savingOpname.value = true

    const items =
      stockOpnameForm.value.items

    // =========================
    // VALIDASI DATA KOSONG
    // =========================
    if (!items.length) {

      ElMessage.warning(
        'Tidak ada data stock'
      )

      return
    }

    // =========================
    // VALIDASI MINUS
    // =========================
    const invalidQty =
      items.find(
        item =>
          item.quantity_after < 0
      )

    if (invalidQty) {

      ElMessage.error(
        'Jumlah fisik tidak boleh minus'
      )

      return
    }

    // =========================
    // FILTER HANYA YANG BERUBAH
    // =========================
    const changedItems =
      items.filter(
        item =>
          Number(item.quantity_after)
          !==
          Number(item.quantity_before)
      )

    // =========================
    // TIDAK ADA PERUBAHAN
    // =========================
    if (!changedItems.length) {

      ElMessage.warning(
        'Tidak ada perubahan stok'
      )

      return
    }

    // =========================
    // CONFIRM SAVE
    // =========================
    const confirmed =
      confirm(
        `Simpan ${changedItems.length} perubahan stok?`
      )

    if (!confirmed) return

    // =========================
    // API REQUEST
    // =========================
    await api.post(
      'stocks/opname',
      {
        
        adjustments:
          changedItems.map(
            item => ({
              stock_id:
                item.stock_id,

              quantity_after:
                item.quantity_after
            })
          )
      }
    )

    ElMessage.success(
      'Stock opname berhasil'
    )

    closeStockOpname()

    // refresh stock
    fetchStocks()

  } catch (error) {

    console.error(error)

    ElMessage.error(
      error.response?.data?.message
      ||
      'Gagal stock opname'
    )

  } finally {

    savingOpname.value = false
  }
}
/* =========================
   LIFECYCLE
========================= */

onMounted(async () => {
  await fetchWarehouses()

  console.log(
    'WAREHOUSES LOADED:',
    warehouses.value
  )

  await fetchStocks()
})

onUnmounted(() => {
  clearTimeout(
    searchTimeout.value
  )
})

watch(
  [
    filterWarehouse,
    filterType,
    searchQuery
  ],
  () => {
    clearTimeout(searchTimeout.value)

    searchTimeout.value =
      setTimeout(() => {
        fetchStocks()
      }, 300)
  }
)

watch(stocks, (val) => {
  console.log('STOCKS UPDATED:', val)
})

</script>

