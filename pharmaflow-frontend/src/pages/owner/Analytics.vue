<template>
  <div class="space-y-6 animate-fadeIn">
    <div class="flex justify-between items-end bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
      <div>
        <h1 class="text-3xl font-bold text-slate-900 flex items-center gap-3">
          📉 Analytics
        </h1>
        <p class="text-slate-500 mt-1 font-medium">Analisis mendalam performa dan tren penjualan apotek.</p>
      </div>
      <button 
        @click="fetchAnalytics" 
        class="px-5 py-2.5 bg-emerald-50 text-emerald-600 font-bold rounded-xl hover:bg-emerald-100 transition flex items-center gap-2"
      >
        <span>🔄</span> Refresh Data
      </button>
    </div>

    <div v-if="loading" class="flex justify-center items-center py-20">
      <div class="animate-spin rounded-full h-12 w-12 border-b-4 border-emerald-500"></div>
    </div>

    <div v-else class="space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 border-t-4 border-t-blue-500 hover:-translate-y-1 transition">
          <p class="text-slate-500 text-sm font-bold mb-1">Rata-rata Nilai Transaksi</p>
          <h3 class="text-3xl font-black text-slate-800">Rp{{ formatPrice(analyticsData.avg_transaction || 0) }}</h3>
        </div>
        
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 border-t-4 border-t-emerald-500 hover:-translate-y-1 transition">
          <p class="text-slate-500 text-sm font-bold mb-1">Total Produk Terjual</p>
          <h3 class="text-3xl font-black text-slate-800">
            {{ analyticsData.total_items_sold || 0 }} <span class="text-base font-medium text-slate-500">Items</span>
          </h3>
        </div>
        
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 border-t-4 border-t-orange-500 hover:-translate-y-1 transition">
          <p class="text-slate-500 text-sm font-bold mb-1">Tingkat Konversi (Estimasi)</p>
          <h3 class="text-3xl font-black text-slate-800">{{ analyticsData.conversion_rate || 0 }}%</h3>
        </div>
      </div>

      <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-100">
        <div class="flex items-center justify-between mb-8">
          <h2 class="text-xl font-bold text-slate-800">🏆 5 Obat Paling Laris</h2>
          <span class="px-3 py-1 bg-blue-50 text-blue-600 text-xs font-bold rounded-full">Bulan Ini</span>
        </div>
        
        <div v-if="analyticsData.top_medicines && analyticsData.top_medicines.length > 0" class="space-y-6">
          <div v-for="(item, index) in analyticsData.top_medicines" :key="index" class="group">
            <div class="flex justify-between text-sm mb-2">
              <span class="font-bold text-slate-700 flex items-center gap-2">
                <span class="text-slate-400">#{{ index + 1 }}</span>
                {{ item.name }}
              </span>
              <span class="text-emerald-600 font-bold bg-emerald-50 px-2 py-0.5 rounded">{{ item.total_sold }} Terjual</span>
            </div>
            <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden">
              <div 
                class="bg-emerald-500 h-3 rounded-full transition-all duration-1000 group-hover:bg-emerald-400" 
                :style="{ width: `${item.percentage || (100 - (index * 15))}%` }"
              ></div>
            </div>
          </div>
        </div>

        <div v-else class="text-center py-12">
          <div class="text-5xl mb-3">📊</div>
          <p class="text-slate-500 font-medium">Belum ada data penjualan obat yang cukup untuk dianalisis.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'

// --- State ---
const loading = ref(true)
const analyticsData = ref({})

// --- Formatters ---
const formatPrice = (price) => {
  return new Intl.NumberFormat('id-ID').format(price)
}

// --- Fetch Data ---
const fetchAnalytics = async () => {
  loading.value = true
  try {
    // Mengambil data dari endpoint analytics di backend
    const response = await api.get('dashboard/analytics') 
    analyticsData.value = response.data.data || response.data || {}
  } catch (error) {
    console.error('Gagal mengambil data analytics:', error)
  } finally {
    loading.value = false
  }
}

// --- Lifecycle ---
onMounted(() => {
  fetchAnalytics()
})
</script>

<style scoped>
.animate-fadeIn {
  animation: fadeIn 0.4s ease-out forwards;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>