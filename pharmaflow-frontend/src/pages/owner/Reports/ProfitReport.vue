<template>
  <div class="space-y-6">
    <h1 class="text-4xl font-bold">🔬 Laporan Profit & Margin</h1>

    <div id="report-printable" class="space-y-6">
      
      <div class="bg-white rounded-xl shadow-md p-5 grid grid-cols-1 md:grid-cols-4 gap-4 no-print">
        <div>
          <label class="block text-sm font-semibold mb-2 text-slate-700">Dari Tanggal</label>
          <input 
            v-model="startDate" 
            type="date" 
            @change="fetchReport(true)" 
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500" 
          />
        </div>
        <div>
          <label class="block text-sm font-semibold mb-2 text-slate-700">Sampai Tanggal</label>
          <input 
            v-model="endDate" 
            type="date" 
            @change="fetchReport(true)" 
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500" 
          />
        </div>
        <div class="md:col-span-2 flex items-end gap-2">
          <button 
            @click="fetchReport(true)" 
            class="flex-1 px-4 py-2.5 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition font-semibold shadow-sm"
          >
            🔍 Filter
          </button>
          <button 
            @click="downloadPDF" 
            class="flex-1 px-4 py-2.5 bg-slate-800 text-white rounded-lg hover:bg-slate-900 transition font-semibold shadow-sm flex items-center justify-center gap-2"
          >
            📥 Cetak PDF
          </button>
        </div>
      </div>

      <div v-if="loading" class="text-center py-12 bg-white rounded-xl shadow-sm border">
        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-emerald-500 mx-auto mb-3"></div>
        <p class="text-gray-500 font-medium">Sedang meracik data profit...</p>
      </div>

      <div v-else class="space-y-6">
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
          <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-100 border-l-4 border-l-green-500">
            <p class="text-slate-500 font-bold text-xs uppercase tracking-wider mb-1">Total Pendapatan</p>
            <h3 class="text-2xl font-black text-slate-800">Rp{{ formatPrice(report?.total_revenue) }}</h3>
          </div>

          <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-100 border-l-4 border-l-red-500">
            <p class="text-slate-500 font-bold text-xs uppercase tracking-wider mb-1">Total Biaya Beli (COGS)</p>
            <h3 class="text-2xl font-black text-slate-800">Rp{{ formatPrice(report?.total_cost) }}</h3>
          </div>

          <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-100 border-l-4 border-l-blue-500">
            <p class="text-slate-500 font-bold text-xs uppercase tracking-wider mb-1">Total Net Profit</p>
            <h3 class="text-2xl font-black text-emerald-600">Rp{{ formatPrice(report?.total_profit) }}</h3>
          </div>

          <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-100 border-l-4 border-l-purple-500">
            <p class="text-slate-500 font-bold text-xs uppercase tracking-wider mb-1">Profit Margin</p>
            <h3 class="text-2xl font-black text-purple-600">{{ report?.margin || 0 }}%</h3>
          </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-md border border-slate-100">
          <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-slate-800">📊 Grafik Tren Pendapatan Harian</h2>
            <span class="px-3 py-1 bg-emerald-50 text-emerald-600 text-xs font-bold rounded-full animate-pulse">
              ● Live Sync
            </span>
          </div>
          <div class="h-80 relative">
            <Bar :data="chartData" :options="chartOptions" />
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import api from '@/services/api'
import dayjs from 'dayjs'
import { ElMessage } from 'element-plus'
import html2pdf from 'html2pdf.js'

// Import & Registrasi Komponen ChartJS
import { Bar } from 'vue-chartjs'
import { 
  Chart as ChartJS, 
  Title, 
  Tooltip, 
  Legend, 
  BarElement, 
  CategoryScale, 
  LinearScale 
} from 'chart.js'

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale)

// State & Integrasi Waktu
const startDate = ref(dayjs().subtract(30, 'days').format('YYYY-MM-DD'))
const endDate = ref(dayjs().format('YYYY-MM-DD'))
const loading = ref(false)
let refreshInterval = null

// Struktur Data Standar (Penjaga dari Error Blank / NaN)
const report = ref({
  total_revenue: 0,
  total_cost: 0,
  total_profit: 0,
  margin: 0,
  chart_labels: [],
  chart_values: []
})

// Pemformat Mata Uang Rupiah
const formatPrice = (price) => {
  return new Intl.NumberFormat('id-ID').format(price || 0)
}

// Ambil Data dari API Backend PharmaFlow
const fetchReport = async (showLoading = true) => {
  if (showLoading && report.value.chart_labels.length === 0) loading.value = true
  try {
    const response = await api.get('reports/profit', {
      params: {
        start_date: startDate.value,
        end_date: endDate.value,
      }
    })
    
    // Gabungkan data dari backend dengan fallback objek aman
    const responseData = response.data?.data || response.data || {}
    report.value = {
      total_revenue: responseData.total_revenue || 0,
      total_cost: responseData.total_cost || 0,
      total_profit: responseData.total_profit || 0,
      margin: responseData.margin || 0,
      chart_labels: responseData.chart_labels || [],
      chart_values: responseData.chart_values || []
    }
  } catch (error) {
    if (showLoading) ElMessage.error('Gagal memuat laporan profit')
    console.error(error)
  } finally {
    loading.value = false
  }
}

// Konfigurasi Reaktif Komponen Grafik
const chartData = computed(() => ({
  labels: report.value?.chart_labels || [],
  datasets: [{
    label: 'Pendapatan (Rp)',
    backgroundColor: '#10b981', // Hijau Emerald khas PharmaFlow
    borderRadius: 6,
    data: report.value?.chart_values || []
  }]
}))

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: true,
      position: 'top'
    }
  }
}

// Fungsi Cetak Laporan PDF
const downloadPDF = () => {
  const noPrintElements = document.querySelectorAll('.no-print');
  // Sembunyikan tombol filter
  noPrintElements.forEach(el => el.style.display = 'none');

  const element = document.getElementById('report-printable');
  const options = {
    margin: 12,
    filename: `Laporan_Profit_${startDate.value}_to_${endDate.value}.pdf`,
    image: { type: 'jpeg', quality: 0.98 },
    html2canvas: { scale: 2, useCORS: true },
    jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
  };

  html2pdf().set(options).from(element).save().then(() => {
    // Munculkan kembali tombol setelah cetak selesai
    noPrintElements.forEach(el => el.style.display = 'grid');
  });
}

// Siklus Eksekusi Real-time Polling
onMounted(() => {
  fetchReport(true)

  // Auto update senyap setiap 30 detik tanpa memicu spinner loading
  refreshInterval = setInterval(() => {
    fetchReport(false)
  }, 30000)
})

onUnmounted(() => {
  if (refreshInterval) clearInterval(refreshInterval)
})
</script>

<style>
/* Proteksi CSS untuk tombol filter agar bersih saat diprint */
@media print {
  .no-print {
    display: none !important;
  }
}
</style>