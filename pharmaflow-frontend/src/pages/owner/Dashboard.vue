<template>
  <div class="space-y-8">
    <div>
      <h1 class="text-3xl font-bold text-gray-800">📊 Dashboard</h1>
      <p class="text-gray-500 mt-1">Ringkasan performa apotek Anda</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
      <div class="flex gap-6 items-end">
        <div class="flex-1">
          <label class="block text-sm font-bold text-gray-700 mb-2">Dari Tanggal</label>
          <input v-model="startDate" type="date" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
        </div>
        <div class="flex-1">
          <label class="block text-sm font-bold text-gray-700 mb-2">Sampai Tanggal</label>
          <input v-model="endDate" type="date" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
        </div>
        <button @click="fetchDashboardData" class="px-8 py-3 bg-green-600 text-white font-bold rounded-lg hover:bg-green-700 transition shadow-md">
          🔍 Filter
        </button>
        <button @click="resetFilter" class="px-4 py-3 bg-gray-100 text-gray-600 font-bold rounded-lg hover:bg-gray-200 transition shadow-sm" title="Tampilkan Data Lifetime">
          🔄 Reset
        </button>
      </div>
    </div>

    <div v-if="loading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-4 border-green-600"></div>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <div class="bg-white p-6 rounded-xl shadow-sm border-t-4 border-green-500 hover:shadow-md transition">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-sm font-bold text-gray-500 mb-1">Total Pendapatan</p>
            <h3 class="text-3xl font-black text-green-600">Rp{{ formatPrice(stats.revenue || 0) }}</h3>
          </div>
          <span class="text-3xl">💰</span>
        </div>
      </div>

      <div class="bg-white p-6 rounded-xl shadow-sm border-t-4 border-blue-500 hover:shadow-md transition">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-sm font-bold text-gray-500 mb-1">Total Pesanan</p>
            <h3 class="text-3xl font-black text-blue-600">{{ stats.total_orders || 0 }}</h3>
          </div>
          <span class="text-3xl">📦</span>
        </div>
      </div>

      <div class="bg-white p-6 rounded-xl shadow-sm border-t-4 border-red-500 hover:shadow-md transition">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-sm font-bold text-gray-500 mb-1">Total Pengeluaran</p>
            <h3 class="text-3xl font-black text-red-600">Rp{{ formatPrice(stats.expense || 0) }}</h3>
          </div>
          <span class="text-3xl">💸</span>
        </div>
      </div>

      <div class="bg-white p-6 rounded-xl shadow-sm border-t-4 border-purple-500 hover:shadow-md transition">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-sm font-bold text-gray-500 mb-1">Profit</p>
            <h3 class="text-3xl font-black text-purple-600">Rp{{ formatPrice(stats.profit || 0) }}</h3>
            <p class="text-xs font-semibold mt-2" :class="(stats.margin || 0) >= 0 ? 'text-green-500' : 'text-red-500'">
              Margin: {{ (stats.margin || 0).toFixed(1) }}%
            </p>
          </div>
          <span class="text-3xl">📈</span>
        </div>
      </div>
    </div>

    <div v-if="!loading" class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between hover:shadow-md transition">
        <div>
          <p class="text-sm font-bold text-gray-500 mb-1">Total Produk</p>
          <h3 class="text-4xl font-black text-blue-600">{{ stats.total_products || 0 }}</h3>
        </div>
        <div class="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center text-2xl">💊</div>
      </div>

      <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between hover:shadow-md transition">
        <div>
          <p class="text-sm font-bold text-gray-500 mb-1">Stok Menipis</p>
          <h3 class="text-4xl font-black text-orange-500">{{ stats.low_stock || 0 }}</h3>
        </div>
        <div class="w-16 h-16 bg-orange-50 rounded-full flex items-center justify-center text-2xl">⚠️</div>
      </div>

      <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between hover:shadow-md transition">
        <div>
          <p class="text-sm font-bold text-gray-500 mb-1">Barang Expired</p>
          <h3 class="text-4xl font-black text-red-600">{{ stats.expired_stock || 0 }}</h3>
        </div>
        <div class="w-16 h-16 bg-red-50 rounded-full flex items-center justify-center text-2xl">🗑️</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'

const loading = ref(true)
const startDate = ref('')
const endDate = ref('')
const stats = ref({})

const formatPrice = (price) => {
  return new Intl.NumberFormat('id-ID').format(price)
}

const fetchDashboardData = async () => {
  loading.value = true
  try {
    const params = {}
    if (startDate.value) params.start_date = startDate.value
    if (endDate.value) params.end_date = endDate.value

    const response = await api.get('dashboard/summary', { params })
    stats.value = response.data.data || response.data || {}
  } catch (error) {
    console.error('Gagal mengambil data dashboard:', error)
  } finally {
    loading.value = false
  }
}

const resetFilter = () => {
  startDate.value = ''
  endDate.value = ''
  fetchDashboardData()
}

onMounted(() => {
  fetchDashboardData()
})
</script>