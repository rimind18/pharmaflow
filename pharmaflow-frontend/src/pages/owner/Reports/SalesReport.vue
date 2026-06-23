<template>
  <div class="space-y-6 animate-fadeIn">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
      <div>
        <h1 class="text-3xl font-bold text-slate-800 flex items-center gap-3">
          📈 Laporan Penjualan
        </h1>
        <p class="text-slate-500 mt-1 font-medium">Analisis pendapatan dan performa penjualan apotek secara real-time.</p>
      </div>
      <button 
        @click="exportPDF" 
        class="px-5 py-2.5 bg-rose-50 text-rose-600 font-bold rounded-xl hover:bg-rose-600 hover:text-white transition-all shadow-sm flex items-center gap-2"
      >
        <span>📄</span> Download PDF
      </button>
    </div>

    <div class="bg-white p-5 md:p-6 rounded-2xl shadow-sm border border-slate-100">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
        <div>
          <label class="block text-sm font-bold text-slate-700 mb-2">Periode</label>
          <select v-model="filters.period" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none bg-slate-50 hover:bg-white transition-colors cursor-pointer">
            <option value="daily">Harian</option>
            <option value="weekly">Mingguan</option>
            <option value="monthly">Bulanan</option>
            <option value="yearly">Tahunan</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-bold text-slate-700 mb-2">Dari Tanggal</label>
          <input type="date" v-model="filters.start_date" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none bg-slate-50 hover:bg-white transition-colors cursor-pointer">
        </div>
        <div>
          <label class="block text-sm font-bold text-slate-700 mb-2">Sampai Tanggal</label>
          <input type="date" v-model="filters.end_date" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none bg-slate-50 hover:bg-white transition-colors cursor-pointer">
        </div>
        <div>
          <button 
            @click="fetchReport" 
            :disabled="loading"
            class="w-full px-5 py-2.5 bg-emerald-600 text-white font-bold rounded-xl hover:bg-emerald-700 transition-all shadow-sm flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="loading" class="animate-spin">⏳</span>
            <span v-else>🔍</span>
            {{ loading ? 'Memuat...' : 'Terapkan Filter' }}
          </button>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 relative overflow-hidden group">
        <div class="absolute -right-6 -top-6 text-emerald-50 opacity-50 group-hover:scale-110 transition-transform duration-500">
          <span class="text-8xl">💰</span>
        </div>
        <div class="relative z-10">
          <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shadow-sm">💰</div>
            <p class="text-sm text-slate-500 font-bold uppercase tracking-wider">Total Penjualan</p>
          </div>
          <p class="text-3xl font-black text-slate-800 mt-2">Rp {{ formatPrice(stats.total_revenue) }}</p>
        </div>
      </div>
      
      <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 relative overflow-hidden group">
        <div class="absolute -right-6 -top-6 text-blue-50 opacity-50 group-hover:scale-110 transition-transform duration-500">
          <span class="text-8xl">🧾</span>
        </div>
        <div class="relative z-10">
          <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xl shadow-sm">🧾</div>
            <p class="text-sm text-slate-500 font-bold uppercase tracking-wider">Total Transaksi</p>
          </div>
          <p class="text-3xl font-black text-slate-800 mt-2">{{ stats.total_transactions || 0 }} <span class="text-lg font-bold text-slate-400">Struk</span></p>
        </div>
      </div>

      <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 relative overflow-hidden group">
        <div class="absolute -right-6 -top-6 text-purple-50 opacity-50 group-hover:scale-110 transition-transform duration-500">
          <span class="text-8xl">📊</span>
        </div>
        <div class="relative z-10">
          <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-full bg-purple-50 text-purple-600 flex items-center justify-center text-xl shadow-sm">📊</div>
            <p class="text-sm text-slate-500 font-bold uppercase tracking-wider">Rata-rata / Transaksi</p>
          </div>
          <p class="text-3xl font-black text-slate-800 mt-2">Rp {{ formatPrice(stats.average_transaction) }}</p>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      
      <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden lg:col-span-1 flex flex-col">
        <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
          <h2 class="font-bold text-slate-800 flex items-center gap-2">
            <span class="text-xl">🔥</span> Produk Terlaris
          </h2>
        </div>
        <div class="p-5 flex-1">
          <div v-if="loading" class="flex justify-center py-10">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-emerald-500"></div>
          </div>
          <div v-else-if="topProducts.length === 0" class="text-center text-slate-400 py-10 font-medium">
            <div class="text-4xl mb-2 opacity-50">🛒</div>
            Belum ada data penjualan.
          </div>
          <ul v-else class="space-y-4">
            <li v-for="(prod, index) in topProducts" :key="index" class="flex items-center justify-between p-3 rounded-xl hover:bg-slate-50 transition border border-transparent hover:border-slate-100">
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center font-black text-sm shadow-sm">
                  {{ index + 1 }}
                </div>
                <div>
                  <p class="font-bold text-slate-800 text-sm line-clamp-1">{{ prod.name || prod.medicine?.name || 'Obat' }}</p>
                  <p class="text-xs text-slate-500 font-medium">{{ prod.total_qty || 0 }} pcs terjual</p>
                </div>
              </div>
              <div class="font-black text-emerald-600 text-sm">
                Rp {{ formatPrice(prod.total_revenue || 0) }}
              </div>
            </li>
          </ul>
        </div>
      </div>

      <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden lg:col-span-2 flex flex-col">
        <div class="p-5 border-b border-slate-100 bg-slate-50/50">
          <h2 class="font-bold text-slate-800 flex items-center gap-2">
            <span class="text-xl">📑</span> Riwayat Transaksi Terbaru
          </h2>
        </div>
        <div class="overflow-x-auto p-5">
           <div v-if="loading" class="flex justify-center py-10">
             <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-emerald-500"></div>
           </div>
           <table v-else class="w-full text-left border-collapse">
             <thead>
               <tr class="border-b-2 border-slate-100 text-xs uppercase tracking-wider text-slate-500">
                 <th class="pb-3 font-bold">Tanggal & Waktu</th>
                 <th class="pb-3 font-bold">No. Invoice</th>
                 <th class="pb-3 font-bold text-center">Item</th>
                 <th class="pb-3 font-bold text-right">Total Nominal</th>
               </tr>
             </thead>
             <tbody>
               <tr v-if="sales.length === 0">
                 <td colspan="4" class="py-12 text-center text-slate-400 font-medium">
                    Tidak ada transaksi pada periode yang dipilih.
                 </td>
               </tr>
               <tr v-for="(sale, idx) in sales" :key="idx" class="border-b border-slate-50 hover:bg-emerald-50/30 transition group">
                 <td class="py-4 text-sm font-medium text-slate-600">{{ formatDateTime(sale.created_at || sale.date) }}</td>
                 <td class="py-4 text-sm font-bold text-emerald-600 cursor-pointer hover:underline">
                   {{ sale.invoice_number || sale.id }}
                 </td>
                 <td class="py-4 text-sm text-slate-600 text-center font-bold">
                   {{ sale.items_count || sale.total_items || 0 }}
                 </td>
                 <td class="py-4 text-sm font-black text-slate-800 text-right">
                   Rp {{ formatPrice(sale.grand_total || sale.total) }}
                 </td>
               </tr>
             </tbody>
           </table>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import { ElMessage } from 'element-plus'

const loading = ref(false)
const sales = ref([])
const topProducts = ref([])

// Default filter: Bulan ini
const today = new Date()
const firstDay = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0]
const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0).toISOString().split('T')[0]

const filters = ref({
  period: 'monthly',
  start_date: firstDay,
  end_date: lastDay
})

const stats = ref({
  total_revenue: 0,
  total_transactions: 0,
  average_transaction: 0
})

const fetchReport = async () => {
  loading.value = true
  try {
    const response = await api.get('reports/sales', { params: filters.value })
    const data = response.data?.data || response.data
    
    // Auto binding pelindung null
    stats.value = {
      total_revenue: data.total_revenue || data.stats?.total_revenue || 0,
      total_transactions: data.total_transactions || data.stats?.total_transactions || 0,
      average_transaction: data.average_transaction || data.stats?.average_transaction || 0
    }
    
    sales.value = data.sales || data.data || []
    topProducts.value = data.top_products || []

  } catch (error) {
    ElMessage.error('Gagal memuat data laporan penjualan.')
    console.error(error)
  } finally {
    loading.value = false
  }
}

const exportPDF = async () => {
  try {
    ElMessage.info('Menyiapkan dokumen...');
    // Sesuaikan parameter filter dengan yang dikirim ke API
    const response = await api.get('reports/sales/export', { 
      params: filters.value,
      responseType: 'blob' 
    });

    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `Laporan_Penjualan_${filters.value.start_date}_ke_${filters.value.end_date}.xlsx`);
    document.body.appendChild(link);
    link.click();
    
    ElMessage.success('Laporan berhasil diunduh!');
  } catch (error) {
    ElMessage.error('Gagal mengunduh laporan.');
  }
}

// ==========================================
// FORMATTER HELPER
// ==========================================
const formatPrice = (value) => {
  const val = Number(value) || 0;
  return new Intl.NumberFormat('id-ID').format(val)
}

const formatDateTime = (dateString) => {
  if (!dateString) return '-'
  const date = new Date(dateString)
  return date.toLocaleDateString('id-ID', {
    day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute:'2-digit'
  })
}

onMounted(() => {
  fetchReport()
})
</script>

<style scoped>
.animate-fadeIn { animation: fadeIn 0.4s ease-out forwards; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
</style>