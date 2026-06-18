<template>
  <div class="space-y-6 animate-fadeIn">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
      <div>
        <h1 class="text-3xl font-bold text-slate-800 flex items-center gap-3">
          💊 Management Obat
        </h1>
        <p class="text-slate-500 mt-1 font-medium">Kelola daftar obat terintegrasi dengan database asli.</p>
      </div>
      
      <div class="flex items-center gap-3 w-full md:w-auto">
        <div class="relative w-full md:w-64">
          <input 
            v-model="searchQuery" 
            type="text" 
            placeholder="Cari nama atau kode..." 
            class="w-full pl-10 pr-4 py-2 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 shadow-sm"
          >
          <span class="absolute left-3 top-2.5 text-slate-400">🔍</span>
        </div>
        
        <button 
          @click="openModal('add')"
          class="px-5 py-2.5 bg-emerald-600 text-white font-bold rounded-xl hover:bg-emerald-700 transition shadow-sm whitespace-nowrap flex items-center gap-2"
        >
          <span>➕</span> Tambah Obat
        </button>
      </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
      <div v-if="loading" class="flex flex-col items-center justify-center py-20">
        <div class="animate-spin rounded-full h-12 w-12 border-b-4 border-emerald-500 mb-4"></div>
        <p class="text-slate-500 font-medium">Menarik data dari database Laravel...</p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50 text-slate-600 text-sm border-b border-slate-100">
              <th class="p-4 font-semibold w-24">Kode</th>
              <th class="p-4 font-semibold">Nama Obat</th>
              <th class="p-4 font-semibold">Kategori</th>
              <th class="p-4 font-semibold text-center">Stok Total</th>
              <th class="p-4 font-semibold text-right">Harga Dasar</th>
              <th class="p-4 font-semibold text-right">Harga Jual (Est)</th>
              <th class="p-4 font-semibold text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-if="filteredMedicines.length === 0">
              <td colspan="7" class="p-12 text-center">
                <div class="text-5xl mb-3">🗄️</div>
                <h3 class="text-lg font-bold text-slate-700">Database Kosong</h3>
                <p class="text-slate-500">Belum ada data obat atau pencarian tidak cocok.</p>
              </td>
            </tr>
            
            <tr v-for="(medicine, index) in filteredMedicines" :key="medicine.id" class="hover:bg-emerald-50/30 transition group">
              <td class="p-4 text-xs font-mono text-slate-500">{{ medicine.code }}</td>
              <td class="p-4 font-bold text-slate-800">{{ medicine.name }}</td>
              <td class="p-4 text-slate-600">
                <span class="px-2.5 py-1 bg-slate-100 text-slate-600 text-xs font-bold rounded-md">
                  {{ medicine.category?.name || 'ID: ' + medicine.category_id }}
                </span>
              </td>
              <td class="p-4 text-center">
                <span :class="medicine.is_low_stock ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700'" class="px-3 py-1 font-bold rounded-full text-sm">
                  {{ medicine.total_stock || 0 }} {{ medicine.unit }}
                </span>
              </td>
              <td class="p-4 text-right text-slate-500 font-medium">Rp{{ formatPrice(medicine.base_price) }}</td>
              <td class="p-4 text-right text-emerald-600 font-bold">
                Rp{{ formatPrice(medicine.base_price + (medicine.base_price * medicine.markup_percentage / 100)) }}
              </td>
              <td class="p-4 text-center">
                <div class="flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition">
                  <button @click="openModal('edit', medicine)" class="p-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100" title="Edit">✏️</button>
                  <button @click="deleteMedicine(medicine.id)" class="p-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100" title="Hapus">🗑️</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="showModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center z-[999] p-4">
      
      <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl p-6 max-h-[90vh] overflow-y-auto relative">
        
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-xl font-bold flex items-center gap-2 text-slate-800">
            <span class="text-emerald-600 text-2xl font-black">➕</span>
            {{ modalMode === 'add' ? 'Tambah Obat Baru' : 'Edit Data Obat' }}
          </h2>
          <button @click="closeModal" class="text-slate-400 hover:text-red-500 font-bold text-2xl leading-none">&times;</button>
        </div>
        
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-bold text-slate-700 mb-1">Nama Obat <span class="text-red-500">*</span></label>
            <input v-model="form.name" type="text" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none">
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-bold text-slate-700 mb-1">ID Kategori <span class="text-red-500">*</span></label>
              <input v-model.number="form.category_id" type="number" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none">
            </div>
            <div>
              <label class="block text-sm font-bold text-slate-700 mb-1">ID Supplier <span class="text-red-500">*</span></label>
              <input v-model.number="form.supplier_id" type="number" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none">
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-bold text-slate-700 mb-1">Harga Dasar (Modal) <span class="text-red-500">*</span></label>
              <input v-model.number="form.base_price" type="number" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none">
            </div>
            <div>
              <label class="block text-sm font-bold text-slate-700 mb-1">Markup (%) <span class="text-red-500">*</span></label>
              <input v-model.number="form.markup_percentage" type="number" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none">
            </div>
          </div>

          <div class="grid grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-bold text-slate-700 mb-1">Satuan <span class="text-red-500">*</span></label>
              <input v-model="form.unit" type="text" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none" placeholder="Pcs">
            </div>
            <div>
              <label class="block text-sm font-bold text-slate-700 mb-1">Stok Min <span class="text-red-500">*</span></label>
              <input v-model.number="form.stock_minimum" type="number" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none">
            </div>
            <div>
              <label class="block text-sm font-bold text-slate-700 mb-1">Stok Max <span class="text-red-500">*</span></label>
              <input v-model.number="form.stock_maximum" type="number" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none">
            </div>
          </div>
        </div>
        
        <div class="flex justify-end gap-4 mt-8">
          <button @click="closeModal" class="px-4 py-2 font-bold text-slate-600 hover:text-slate-800 transition">Batal</button>
          <button @click="saveMedicine" class="px-5 py-2 bg-emerald-600 text-white font-bold rounded-xl hover:bg-emerald-700 transition flex items-center gap-2">
            💾 Simpan Data
          </button>
        </div>

      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/services/api'
import { ElMessage, ElMessageBox } from 'element-plus'

const medicines = ref([])
const loading = ref(false)
const searchQuery = ref('')
const showModal = ref(false)
const modalMode = ref('add') 
const form = ref({
  id: null,
  name: '',
  category_id: 1, 
  supplier_id: 1, 
  base_price: 0,
  markup_percentage: 20, 
  unit: 'Pcs',
  stock_minimum: 10,
  stock_maximum: 100
})

const formatPrice = (price) => {
  return new Intl.NumberFormat('id-ID').format(price || 0)
}

const filteredMedicines = computed(() => {
  if (!searchQuery.value) return medicines.value
  const query = searchQuery.value.toLowerCase()
  return medicines.value.filter(med => 
    med.name.toLowerCase().includes(query) || 
    (med.code && med.code.toLowerCase().includes(query))
  )
})

const fetchMedicines = async () => {
  loading.value = true
  try {
    const response = await api.get('medicines')
    const rawData = response.data?.data
    medicines.value = (rawData && rawData.data) ? rawData.data : (rawData || [])
  } catch (error) {
    ElMessage.error('Gagal terhubung ke Database!')
    console.error(error)
  } finally {
    loading.value = false
  }
}

const openModal = (mode, data = null) => {
  modalMode.value = mode
  if (mode === 'edit' && data) {
    form.value = { ...data } 
  } else {
    form.value = { 
      id: null, name: '', category_id: 1, supplier_id: 1, 
      base_price: 0, markup_percentage: 20, unit: 'Pcs', 
      stock_minimum: 10, stock_maximum: 100 
    }
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
}

const saveMedicine = async () => {
  try {
    const loadingMessage = ElMessage({ message: 'Menyimpan data...', type: 'info', duration: 0 })
    if (modalMode.value === 'add') {
      await api.post('medicines', form.value)
      loadingMessage.close()
      ElMessage.success('Obat berhasil ditambahkan ke Database!')
    } else {
      await api.put(`medicines/${form.value.id}`, form.value)
      loadingMessage.close()
      ElMessage.success('Data obat berhasil diperbarui!')
    }
    closeModal()
    fetchMedicines() 
  } catch (error) {
    ElMessage.closeAll()
    if (error.response && error.response.status === 422) {
      const errors = error.response.data.errors
      let errorMessage = 'Gagal menyimpan. Cek kembali data Anda:<br>'
      for (const field in errors) { errorMessage += `- ${errors[field][0]}<br>` }
      ElMessage({ message: errorMessage, type: 'error', dangerouslyUseHTMLString: true, duration: 5000 })
    } else {
      ElMessage.error('Terjadi kesalahan pada server')
    }
  }
}

const deleteMedicine = (id) => {
  ElMessageBox.confirm('Yakin ingin menghapus obat ini?', 'Peringatan', {
    confirmButtonText: 'Ya, Hapus!',
    cancelButtonText: 'Batal',
    type: 'warning',
  }).then(async () => {
    try {
      await api.delete(`medicines/${id}`)
      ElMessage.success('Obat dihapus dari database.')
      fetchMedicines()
    } catch (error) {
      ElMessage.error('Gagal menghapus obat')
    }
  }).catch(() => {})
}

onMounted(() => {
  fetchMedicines()
})
</script>