<template>
  <div class="space-y-6">
    <h1 class="text-4xl font-bold">💰 Laporan Keuangan</h1>

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
            class="flex-1 px-4 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold shadow-sm"
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
        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-500 mx-auto mb-3"></div>
        <p class="text-gray-500 font-medium">Sedang menghitung kasir dan brankas...</p>
      </div>

      <div v-else class="space-y-6">
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="bg-white p-6 rounded-xl shadow-md border border-slate-100 border-l-4 border-l-green-500">
            <div class="flex justify-between items-start">
              <div>
                <p class="text-slate-500 font-bold text-xs uppercase tracking-wider mb-1">Total Pemasukan</p>
                <h3 class="text-3xl font-black text-green-600">Rp{{ formatPrice(report?.total_income) }}</h3>
              </div>
              <span class="text-3xl">📈</span>
            </div>
          </div>

          <div class="bg-white p-6 rounded-xl shadow-md border border-slate-100 border-l-4 border-l-red-500">
            <div class="flex justify-between items-start">
              <div>
                <p class="text-slate-500 font-bold text-xs uppercase tracking-wider mb-1">Total Pengeluaran</p>
                <h3 class="text-3xl font-black text-red-600">Rp{{ formatPrice(report?.total_expense) }}</h3>
              </div>
              <span class="text-3xl">📉</span>
            </div>
          </div>

          <div class="bg-white p-6 rounded-xl shadow-md border border-slate-100 border-l-4 border-l-blue-500">
            <div class="flex justify-between items-start">
              <div>
                <p class="text-slate-500 font-bold text-xs uppercase tracking-wider mb-1">Saldo Bersih (Net)</p>
                <h3 class="text-3xl font-black text-blue-600">Rp{{ formatPrice(report?.net_balance) }}</h3>
              </div>
              <span class="text-3xl">🏦</span>
            </div>
          </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-md border border-slate-100">
          <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-slate-800">📊 Arus Kas (Pemasukan vs Pengeluaran)</h2>
            <span class="px-3 py-1 bg-blue-50 text-blue-600 text-xs font-bold rounded-full animate-pulse">
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

// Komponen ChartJS
import { Bar } from 'vue-chartjs'
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js'

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale)

// State
const startDate = ref(dayjs().startOf('month').format('YYYY-MM-DD')) // Default: Awal bulan ini
const endDate = ref(dayjs().format('YYYY-MM-DD')) // Default: Hari ini
const loading = ref(false)
let refreshInterval = null

// Data Laporan Default (Anti Blank/NaN)
const report = ref({
  total_income: 0,
  total_expense: 0,
  net_balance: 0,
  chart_labels: [],
  chart_income: [],
  chart_expense: []
})

const formatPrice = (price) => {
  return new Intl.NumberFormat('id-ID').format(price || 0)
}

const fetchReport = async (showLoading = true) => {
  if (showLoading && report.value.chart_labels.length === 0) loading.value = true
  try {
    const response = await api.get('reports/financial', {
      params: {
        start_date: startDate.value,
        end_date: endDate.value,
      }
    })
    
    const responseData = response.data?.data || response.data || {}
    report.value = {
      total_income: responseData.total_income || 0,
      total_expense: responseData.total_expense || 0,
      net_balance: responseData.net_balance || 0,
      chart_labels: responseData.chart_labels || [],
      chart_income: responseData.chart_income || [],
      chart_expense: responseData.chart_expense || []
    }
  } catch (error) {
    if (showLoading) ElMessage.error('Gagal memuat laporan keuangan')
    console.error(error)
  } finally {
    loading.value = false
  }
}

// Data Grafik (Dua Batang: Pemasukan & Pengeluaran)
const chartData = computed(() => ({
  labels: report.value?.chart_labels || [],
  datasets: [
    {
      label: 'Pemasukan (Rp)',
      backgroundColor: '#10b981', // Hijau
      borderRadius: 4,
      data: report.value?.chart_income || []
    },
    {
      label: 'Pengeluaran (Rp)',
      backgroundColor: '#ef4444', // Merah
      borderRadius: 4,
      data: report.value?.chart_expense || []
    }
  ]
}))

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { position: 'top' } }
}

const downloadPDF = () => {
  const noPrintElements = document.querySelectorAll('.no-print');
  noPrintElements.forEach(el => el.style.display = 'none');

  const element = document.getElementById('report-printable');
  const options = {
    margin: 12,
    filename: `Laporan_Keuangan_${startDate.value}_to_${endDate.value}.pdf`,
    image: { type: 'jpeg', quality: 0.98 },
    html2canvas: { scale: 2, useCORS: true },
    jsPDF: { unit: 'mm', format: 'a4', orientation: 'landscape' } // Keuangan lebih bagus landscape
  };

  html2pdf().set(options).from(element).save().then(() => {
    noPrintElements.forEach(el => el.style.display = 'grid');
  });
}

onMounted(() => {
  fetchReport(true)
  refreshInterval = setInterval(() => { fetchReport(false) }, 30000)
})

onUnmounted(() => {
  if (refreshInterval) clearInterval(refreshInterval)
})
</script>

<style>
@media print { .no-print { display: none !important; } }
</style>