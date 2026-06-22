<template>
  <div class="space-y-6 animate-fadeIn">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
      <div>
        <h1 class="text-3xl font-bold text-slate-800 flex items-center gap-3">
          📦 Management Stok
        </h1>
        <p class="text-slate-500 mt-1 font-medium">Pantau ketersediaan barang dan lakukan penyesuaian stok fisik.</p>
      </div>
      
      <div class="relative w-full md:w-72">
        <input 
          v-model="searchQuery" 
          type="text" 
          placeholder="Cari nama atau kode..." 
          class="w-full pl-10 pr-4 py-2 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 shadow-sm transition-all"
        >
        <span class="absolute left-3 top-2.5 text-slate-400">🔍</span>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
        <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl">💊</div>
        <div>
          <p class="text-sm text-slate-500 font-bold">Total Baris Stok</p>
          <p class="text-2xl font-black text-slate-800">{{ stocks.length }}</p>
        </div>
      </div>
      <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
        <div class="w-12 h-12 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center text-2xl">⚠️</div>
        <div>
          <p class="text-sm text-slate-500 font-bold">Stok Menipis</p>
          <p class="text-2xl font-black text-slate-800">{{ lowStockCount }}</p>
        </div>
      </div>
      <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
        <div class="w-12 h-12 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center text-2xl">🚨</div>
        <div>
          <p class="text-sm text-slate-500 font-bold">Stok Habis</p>
          <p class="text-2xl font-black text-slate-800">{{ outOfStockCount }}</p>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
      <div v-if="loading" class="flex flex-col items-center justify-center py-20">
        <div class="animate-spin rounded-full h-12 w-12 border-b-4 border-emerald-500 mb-4"></div>
        <p class="text-slate-500 font-medium">Memuat data inventaris...</p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50 text-slate-600 text-sm border-b border-slate-100">
              <th class="p-4 font-semibold w-24">ID Stok</th>
              <th class="p-4 font-semibold">Informasi Obat & Gudang</th>
              <th class="p-4 font-semibold text-center">Batas Min.</th>
              <th class="p-4 font-semibold text-center">Sisa Stok Fisik</th>
              <th class="p-4 font-semibold text-center">Status</th>
              <th class="p-4 font-semibold text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-if="filteredStocks.length === 0">
              <td colspan="6" class="p-12 text-center text-slate-400">Tidak ada data stok yang ditemukan.</td>
            </tr>
            
            <tr v-for="item in filteredStocks" :key="item.id" class="hover:bg-emerald-50/30 transition group">
              <td class="p-4 text-xs font-mono font-bold text-slate-400">#STK-{{ item.id }}</td>
              <td class="p-4">
                <span class="font-bold text-slate-800 block">{{ getMedName(item) }}</span>
                <span class="text-xs font-semibold text-slate-500">
                  {{ getWarehouseName(item) }} • {{ getMedCode(item) }}
                </span>
              </td>
              <td class="p-4 text-center text-slate-500 font-bold">{{ getStockMin(item) }}</td>
              <td class="p-4 text-center">
                <span class="text-xl font-black text-slate-700">{{ item.quantity || 0 }}</span>
              </td>
              <td class="p-4 text-center">
                <span v-if="isOutOfStock(item)" class="px-3 py-1 bg-rose-100 text-rose-700 font-bold rounded-full text-xs">HABIS</span>
                <span v-else-if="isLowStock(item)" class="px-3 py-1 bg-amber-100 text-amber-700 font-bold rounded-full text-xs">MENIPIS</span>
                <span v-else class="px-3 py-1 bg-emerald-100 text-emerald-700 font-bold rounded-full text-xs">AMAN</span>
              </td>
              <td class="p-4 text-center">
                <button @click="openModal(item)" class="px-4 py-1.5 bg-emerald-50 text-emerald-600 font-bold rounded-lg hover:bg-emerald-600 hover:text-white transition shadow-sm">
                  Sesuaikan
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="showModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center z-[999] p-4">
      <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto relative animate-slideUp">
        
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-xl font-bold flex items-center gap-2 text-slate-800">
            <span class="text-emerald-600 text-2xl font-black">⚙️</span>
            Sesuaikan Stok Obat
          </h2>
          <button @click="closeModal" class="text-slate-400 hover:text-red-500 font-bold text-2xl leading-none">&times;</button>
        </div>
        
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-bold text-slate-700 mb-1">Nama Obat Target</label>
            <input type="text" :value="getMedName(selectedStock)" disabled class="w-full px-4 py-2 border border-slate-200 bg-slate-50 text-slate-600 rounded-xl outline-none font-bold cursor-not-allowed">
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-bold text-slate-700 mb-1">Sisa Stok Saat Ini</label>
              <input type="text" :value="`${selectedStock?.quantity || 0} ${getMedUnit(selectedStock)}`" disabled class="w-full px-4 py-2 border border-slate-200 bg-slate-50 text-slate-600 rounded-xl outline-none font-bold cursor-not-allowed">
            </div>
            <div>
              <label class="block text-sm font-bold text-slate-700 mb-1">Jenis Penyesuaian <span class="text-red-500">*</span></label>
              <select v-model="form.type" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none bg-white">
                <option value="in">➕ Stok Masuk (Tambah)</option>
                <option value="out">➖ Stok Keluar (Kurangi)</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-sm font-bold text-slate-700 mb-1">Jumlah (Qty) Penyesuaian <span class="text-red-500">*</span></label>
            <input v-model.number="form.quantity" type="number" min="1" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none">
          </div>

          <div>
            <label class="block text-sm font-bold text-slate-700 mb-1">Keterangan / Alasan <span class="text-red-500">*</span></label>
            <textarea v-model="form.notes" rows="3" placeholder="Cth: Barang datang, Expired, Rusak, dll" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none resize-none"></textarea>
          </div>
        </div>
        
        <div class="flex justify-end gap-4 mt-8">
          <button @click="closeModal" class="px-4 py-2 font-bold text-slate-600 hover:text-slate-800 transition">Batal</button>
          <button @click="submitStock" :disabled="isSubmitting" class="px-5 py-2 bg-emerald-600 text-white font-bold rounded-xl hover:bg-emerald-700 transition flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
            <span v-if="isSubmitting" class="animate-spin">⏳</span>
            <span v-else>💾</span>
            {{ isSubmitting ? 'Menyimpan...' : 'Simpan Data' }}
          </button>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/services/api'
import { ElMessage } from 'element-plus'

const stocks = ref([])
const loading = ref(false)
const isSubmitting = ref(false)
const searchQuery = ref('')
const showModal = ref(false)
const selectedStock = ref(null)

const form = ref({
  type: 'in', 
  quantity: 1,
  notes: ''
})

// ==========================================
// KUNCI ANTI-ERROR (FUNGSI PELINDUNG DATA KOSONG)
// ==========================================
const getMedName = (item) => {
  if (item && item.medicine && item.medicine.name) return item.medicine.name;
  return 'Obat Tidak Diketahui';
}

const getMedCode = (item) => {
  if (item && item.medicine && item.medicine.code) return item.medicine.code;
  return '-';
}

const getMedUnit = (item) => {
  if (item && item.medicine && item.medicine.unit) return item.medicine.unit;
  return 'Pcs';
}

const getWarehouseName = (item) => {
  if (item && item.warehouse && item.warehouse.name) return item.warehouse.name;
  return 'Gudang Utama';
}

const getStockMin = (item) => {
  if (item && item.medicine && item.medicine.stock_minimum) return Number(item.medicine.stock_minimum);
  return 10; 
}

const isOutOfStock = (item) => {
  return (item.quantity || 0) <= 0;
}

const isLowStock = (item) => {
  const qty = item.quantity || 0;
  if (qty <= 0) return false;
  return qty <= getStockMin(item);
}

// ==========================================
// HITUNGAN DASHBOARD BAWAAN VUE
// ==========================================
const lowStockCount = computed(() => {
  return stocks.value.filter(s => isLowStock(s)).length
})

const outOfStockCount = computed(() => {
  return stocks.value.filter(s => isOutOfStock(s)).length
})

const filteredStocks = computed(() => {
  if (!searchQuery.value) return stocks.value
  const query = searchQuery.value.toLowerCase()
  return stocks.value.filter(item => 
    getMedName(item).toLowerCase().includes(query) || 
    getMedCode(item).toLowerCase().includes(query)
  )
})

// ==========================================
// FUNGSI UTAMA API
// ==========================================
const fetchStocks = async () => {
  loading.value = true
  try {
    const response = await api.get('stocks')
    const rawData = response.data?.data
    stocks.value = (rawData && rawData.data) ? rawData.data : (rawData || [])
  } catch (error) {
    ElMessage.error('Gagal memuat data stok dari server.')
  } finally {
    loading.value = false
  }
}

const openModal = (stockItem) => {
  selectedStock.value = stockItem
  form.value = {
    type: 'in',
    quantity: 1,
    notes: ''
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  selectedStock.value = null
}

const submitStock = async () => {
  if (form.value.quantity <= 0) {
    ElMessage.warning('Jumlah harus lebih dari 0!')
    return
  }
  if (!form.value.notes) {
    ElMessage.warning('Alasan penyesuaian wajib diisi!')
    return
  }

  if (isSubmitting.value) return;
  isSubmitting.value = true;

  const currentStock = selectedStock.value?.quantity || 0;
  let newQuantity = currentStock;

  if (form.value.type === 'in') {
    newQuantity = currentStock + form.value.quantity;
  } else {
    newQuantity = currentStock - form.value.quantity;
  }

  if (newQuantity < 0) {
    ElMessage.error('Error: Stok akhir tidak boleh minus!');
    isSubmitting.value = false;
    return;
  }

  const payload = {
    stock_id: selectedStock.value.id,
    quantity_after: newQuantity,
    type: form.value.type === 'in' ? 'penambahan' : 'pengurangan',
    reason: form.value.notes,
    notes: form.value.notes
  };

  try {
    await api.post('stocks/adjustment', payload);
    ElMessage.success('Sempurna! Stok berhasil diperbarui.');
    closeModal();
    await fetchStocks();
  } catch (error) {
    console.error("DEBUG ERROR:", error.response?.data);
    const errors = error.response?.data?.errors;
    let errorMsg = error.response?.data?.message || 'Gagal mengupdate stok.';
    
    if (errors) {
       errorMsg = Object.values(errors).flat().join('<br>');
    }
    ElMessage({ message: errorMsg, type: 'error', dangerouslyUseHTMLString: true });
  } finally {
    isSubmitting.value = false;
  }
}

onMounted(() => {
  fetchStocks()
})
</script>

<style scoped>
.animate-fadeIn { animation: fadeIn 0.3s ease-out forwards; }
.animate-slideUp { animation: slideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
@keyframes slideUp { 
  from { opacity: 0; transform: scale(0.95) translateY(20px); } 
  to { opacity: 1; transform: scale(1) translateY(0); } 
}
</style>