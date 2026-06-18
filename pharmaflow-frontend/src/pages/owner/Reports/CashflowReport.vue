<template>
  <div class="space-y-6">
    <h1 class="text-4xl font-bold">💸 Laporan Arus Kas (Cashflow)</h1>

    <div id="report-printable" class="space-y-6">
      
      <div class="bg-white rounded-xl shadow-md p-5 grid grid-cols-1 md:grid-cols-4 gap-4 no-print">
        <div>
          <label class="block text-sm font-semibold mb-2 text-slate-700">Dari Tanggal</label>
          <input 
            v-model="startDate" 
            type="date" 
            @change="fetchReport(true)" 
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" 
          />
        </div>
        <div>
          <label class="block text-sm font-semibold mb-2 text-slate-700">Sampai Tanggal</label>
          <input 
            v-model="endDate" 
            type="date" 
            @change="fetchReport(true)" 
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" 
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
        <p class="text-gray-500 font-medium">Merekap buku kas...</p>
      </div>

      <div v-else class="space-y-6">
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
          <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-100 border-l-4 border-l-slate-400">
            <p class="text-slate-500 font-bold text-xs uppercase tracking-wider mb-1">Saldo Awal</p>
            <h3 class="text-2xl font-black text-slate-700">Rp{{ formatPrice(report?.initial_balance) }}</h3>
          </div>
          <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-100 border-l-4 border-l-green-500">
            <p class="text-slate-500 font-bold text-xs uppercase tracking-wider mb-1">Kas Masuk (In)</p>
            <h3 class="text-2xl font-black text-green-600">Rp{{ formatPrice(report?.cash_in) }}</h3>
          </div>
          <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-100 border-l-4 border-l-red-500">
            <p class="text-slate-500 font-bold text-xs uppercase tracking-wider mb-1">Kas Keluar (Out)</p>
            <h3 class="text-2xl font-black text-red-600">Rp{{ formatPrice(report?.cash_out) }}</h3>
          </div>
          <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-100 border-l-4 border-l-blue-500 bg-blue-50/30">
            <p class="text-slate-500 font-bold text-xs uppercase tracking-wider mb-1">Saldo Akhir</p>
            <h3 class="text-2xl font-black text-blue-700">Rp{{ formatPrice(report?.final_balance) }}</h3>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-md border border-slate-100 overflow-hidden">
          <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <h2 class="text-lg font-bold text-slate-800">📋 Rincian Transaksi Kas</h2>
            <span class="px-3 py-1 bg-blue-100 text-blue-700 text-xs font-bold rounded-full animate-pulse">
              ● Live Sync
            </span>
          </div>
          
          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-slate-100 text-slate-600 text-sm">
                  <th class="p-4 font-semibold w-1/6">Tanggal</th>
                  <th class="p-4 font-semibold w-2/6">Keterangan</th>
                  <th class="p-4 font-semibold w-1/6 text-center">Tipe</th>
                  <th class="p-4 font-semibold w-1/6 text-right">Nominal</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr v-if="!report?.transactions || report.transactions.length === 0">
                  <td colspan="4" class="p-8 text-center text-slate-400 font-medium">
                    Tidak ada pergerakan kas pada periode ini.
                  </td>
                </tr>
                <tr v-for="(trx, idx) in report?.transactions" :key="idx" class="hover:bg-slate-50 transition">
                  <td class="p-4 text-sm text-slate-600">{{ trx.date }}</td>
                  <td class="p-4 text-sm font-medium text-slate-800">{{ trx.description }}</td>
                  <td class="p-4 text-center">
                    <span :class="trx.type === 'in' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'" class="px-2 py-1 text-xs font-bold rounded">
                      {{ trx.type === 'in' ? 'MASUK' : 'KELUAR' }}
                    </span>
                  </td>
                  <td :class="trx.type === 'in' ? 'text-green-600' : 'text-red-600'" class="p-4 text-right font-bold">
                    {{ trx.type === 'in' ? '+' : '-' }} Rp{{ formatPrice(trx.amount) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import api from '@/services/api'
import dayjs from 'dayjs'
import { ElMessage } from 'element-plus'
import html2pdf from 'html2pdf.js'

// State Tanggal (Default: 7 Hari Terakhir)
const startDate = ref(dayjs().subtract(7, 'days').format('YYYY-MM-DD'))
const endDate = ref(dayjs().format('YYYY-MM-DD'))
const loading = ref(false)
let refreshInterval = null

// Struktur Data Penyelamat (Anti-NaN & Anti-Blank)
const report = ref({
  initial_balance: 0,
  cash_in: 0,
  cash_out: 0,
  final_balance: 0,
  transactions: []
})

// Format Rupiah
const formatPrice = (price) => {
  return new Intl.NumberFormat('id-ID').format(price || 0)
}

// Tarik Data dari Backend
const fetchReport = async (showLoading = true) => {
  if (showLoading && report.value.transactions.length === 0) loading.value = true
  try {
    const response = await api.get('reports/cashflow', {
      params: { start_date: startDate.value, end_date: endDate.value }
    })
    
    const responseData = response.data?.data || response.data || {}
    report.value = {
      initial_balance: responseData.initial_balance || 0,
      cash_in: responseData.cash_in || 0,
      cash_out: responseData.cash_out || 0,
      final_balance: responseData.final_balance || 0,
      transactions: responseData.transactions || []
    }
  } catch (error) {
    if (showLoading) ElMessage.error('Gagal memuat arus kas')
    console.error(error)
  } finally {
    loading.value = false
  }
}

// Fungsi Cetak PDF Resmi
const downloadPDF = () => {
  const noPrintElements = document.querySelectorAll('.no-print')
  noPrintElements.forEach(el => el.style.display = 'none')

  const element = document.getElementById('report-printable')
  const options = {
    margin: 12,
    filename: `Laporan_Cashflow_${startDate.value}_sd_${endDate.value}.pdf`,
    image: { type: 'jpeg', quality: 0.98 },
    html2canvas: { scale: 2, useCORS: true },
    jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
  }

  html2pdf().set(options).from(element).save().then(() => {
    noPrintElements.forEach(el => el.style.display = 'grid')
  })
}

// Live Sync Timer
onMounted(() => {
  fetchReport(true)
  refreshInterval = setInterval(() => fetchReport(false), 30000) // Sinkron tiap 30 detik
})

onUnmounted(() => {
  if (refreshInterval) clearInterval(refreshInterval)
})
</script>

<style>
@media print { .no-print { display: none !important; } }
</style>