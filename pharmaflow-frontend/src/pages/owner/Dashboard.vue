<template>
  <div class="space-y-6 animate-fadeIn">
    <div>
      <h1 class="text-3xl font-bold text-slate-800">Dashboard Owner</h1>
      <p class="text-slate-500 font-medium">Ringkasan performa apotek Anda secara real-time.</p>
    </div>

    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
      <div>
        <label class="block text-sm font-bold text-slate-700 mb-2">Dari Tanggal</label>
        <input type="date" v-model="filters.start_date" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none">
      </div>
      <div>
        <label class="block text-sm font-bold text-slate-700 mb-2">Sampai Tanggal</label>
        <input type="date" v-model="filters.end_date" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none">
      </div>
      <button @click="fetchData" class="px-5 py-2.5 bg-emerald-600 text-white font-bold rounded-xl hover:bg-emerald-700 transition shadow-sm">
        Terapkan Filter
      </button>
    </div>

    <div v-if="loading" class="text-center py-20 text-emerald-600 font-bold">Memuat data dashboard...</div>

    <div v-else class="space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <StatCard title="Total Pendapatan" :value="'Rp ' + formatPrice(stats.sales.total_revenue)" icon="💰" color="text-emerald-600" bg="bg-emerald-50" />
        <StatCard title="Total Pengeluaran" :value="'Rp ' + formatPrice(stats.financial.total_expenses)" icon="📉" color="text-rose-600" bg="bg-rose-50" />
        <StatCard title="Profit Bersih" :value="'Rp ' + formatPrice(stats.financial.profit)" icon="🚀" color="text-blue-600" bg="bg-blue-50" />
        <StatCard title="Margin Profit" :value="stats.financial.profit_margin" icon="📈" color="text-purple-600" bg="bg-purple-50" />
      </div>

      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <StatCard title="Total Stok" :value="stats.inventory.total_stock + ' Pcs'" icon="📦" color="text-amber-600" bg="bg-amber-50" />
        <StatCard title="Obat Menipis" :value="stats.inventory.low_stock_count" icon="⚠️" color="text-orange-600" bg="bg-orange-50" />
        <StatCard title="Barang Expired" :value="stats.inventory.expired_count" icon="💀" color="text-red-600" bg="bg-red-50" />
        <StatCard title="Total Obat" :value="stats.inventory.total_medicines" icon="💊" color="text-sky-600" bg="bg-sky-50" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import StatCard from '@/components/StatCard.vue' // Pastikan King punya komponen StatCard, kalau belum, buat di bawah

const loading = ref(false)
const stats = ref({
  sales: { total_revenue: 0 },
  inventory: { total_stock: 0, low_stock_count: 0, expired_count: 0, total_medicines: 0 },
  financial: { total_expenses: 0, profit: 0, profit_margin: '0%' },
  orders: { pending: 0 }
})

const filters = ref({
  start_date: new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0],
  end_date: new Date().toISOString().split('T')[0]
})

const formatPrice = (val) => new Intl.NumberFormat('id-ID').format(val || 0)

const fetchData = async () => {
  loading.value = true
  try {
    const res = await api.get('dashboard/summary', { params: filters.value })
    stats.value = res.data.data
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

onMounted(fetchData)
</script>