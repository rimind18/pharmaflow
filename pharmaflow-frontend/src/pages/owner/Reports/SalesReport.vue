<template>
  <div class="space-y-6">
    <h1 class="text-4xl font-bold">📈 Laporan Penjualan</h1>

    <div id="report-printable" class="space-y-6">
      
      <div class="bg-white rounded-lg shadow-md p-4 grid grid-cols-4 gap-4 no-print">
        <div>
          <label class="block text-sm font-semibold mb-2">Periode</label>
          <select v-model="reportPeriod" @change="fetchReport" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="daily">📅 Harian</option>
            <option value="weekly">📆 Mingguan</option>
            <option value="monthly">📊 Bulanan</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-semibold mb-2">Dari Tanggal</label>
          <input v-model="startDate" type="date" @change="fetchReport" class="w-full px-3 py-2 border border-gray-300 rounded-lg" />
        </div>
        <div>
          <label class="block text-sm font-semibold mb-2">Sampai Tanggal</label>
          <input v-model="endDate" type="date" @change="fetchReport" class="w-full px-3 py-2 border border-gray-300 rounded-lg" />
        </div>
        <div class="flex items-end gap-2">
          <button @click="fetchReport(true)" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold">🔍 Filter</button>
          <button @click="downloadPDF" class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold">📥 PDF</button>
        </div>
      </div>

      <div v-if="loading" class="text-center py-8">
        <p class="text-lg text-gray-600">⏳ Memuat laporan...</p>
      </div>

      <div v-else class="space-y-6">
         <div class="grid grid-cols-4 gap-6">
           <div class="bg-gradient-to-br from-green-50 to-green-100 border-l-4 border-green-600 rounded-lg shadow-md p-6">
             <p class="text-gray-600 text-sm font-semibold mb-2">Total Penjualan</p>
             <p class="text-3xl font-bold text-green-600">Rp{{ formatPrice(report.total_sales) }}</p>
           </div>
           <div class="bg-gradient-to-br from-blue-50 to-blue-100 border-l-4 border-blue-600 rounded-lg shadow-md p-6">
             <p class="text-gray-600 text-sm font-semibold mb-2">Rata-rata Transaksi</p>
             <p class="text-3xl font-bold text-blue-600">Rp{{ formatPrice(report.average_transaction) }}</p>
           </div>
         </div>

         <div class="bg-white rounded-lg shadow-md p-6">
           <h2 class="text-2xl font-bold mb-6">🔥 Produk Terlaris</h2>
           <table class="w-full">
             <tbody class="divide-y">
               <tr v-for="(product, idx) in report.top_products" :key="idx">
                 <td class="px-6 py-4 font-semibold">{{ idx + 1 }}. {{ product.medicine.name }}</td>
                 <td class="px-6 py-4 text-center">{{ product.total_quantity }}</td>
                 <td class="px-6 py-4 text-right font-bold text-green-600">Rp{{ formatPrice(product.total_revenue) }}</td>
               </tr>
             </tbody>
           </table>
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

// State
const reportPeriod = ref('daily')
const startDate = ref(dayjs().format('YYYY-MM-DD'))
const endDate = ref(dayjs().format('YYYY-MM-DD'))
const loading = ref(false)
let refreshInterval = null

// Inisialisasi report dengan nilai default agar tidak error (NaN)
const report = ref({
  total_sales: 0,
  transaction_count: 0,
  average_transaction: 0,
  total_discount: 0,
  total_quantity: 0,
  top_products: [],
  by_payment_status: [],
  daily_breakdown: [],
})

const formatPrice = (price) => {
  return new Intl.NumberFormat('id-ID').format(price || 0)
}

const fetchReport = async (showLoading = true) => {
  if (showLoading) loading.value = true
  try {
    const response = await api.get('reports/sales', {
      params: {
        period: reportPeriod.value,
        start_date: startDate.value,
        end_date: endDate.value,
      }
    })
    report.value = response.data.data || report.value
  } catch (error) {
    if (showLoading) ElMessage.error('Gagal memuat laporan')
  } finally {
    if (showLoading) loading.value = false
  }
}

const downloadPDF = () => {
  // 1. Sembunyikan tombol filter secara manual agar tidak ikut terpotret
  const noPrintElements = document.querySelectorAll('.no-print');
  noPrintElements.forEach(el => el.style.display = 'none');

  const element = document.getElementById('report-printable');
  
  const options = {
    margin: 10,
    filename: `Laporan_Penjualan_${dayjs().format('YYYY-MM-DD')}.pdf`,
    image: { type: 'jpeg', quality: 0.98 },
    html2canvas: { scale: 2, useCORS: true },
    jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
  };

  // 2. Generate PDF
  html2pdf().set(options).from(element).save().then(() => {
    // 3. Tampilkan kembali tombol setelah PDF selesai dibuat
    noPrintElements.forEach(el => el.style.display = 'grid');
  });
}

onMounted(() => {
  fetchReport(true)
  refreshInterval = setInterval(() => {
    fetchReport(false)
  }, 30000)
})

onUnmounted(() => {
  if (refreshInterval) clearInterval(refreshInterval)
})
</script>

<style>
/* CSS Ajaib: Menghilangkan elemen saat dicetak */
@media print {
  .no-print {
    display: none !important;
  }
}
</style>