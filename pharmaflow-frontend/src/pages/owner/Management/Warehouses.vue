<template>
  <div class="space-y-6 animate-fadeIn">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
      <div>
        <h1 class="text-3xl font-bold text-slate-800 flex items-center gap-3">
          🏢 Manajemen Gudang
        </h1>
        <p class="text-slate-500 mt-1 font-medium">Kelola lokasi penyimpanan, kapasitas gundang, dan distribusi stok obat.</p>
      </div>
      
      <button
        @click="openModal()"
        class="px-6 py-3 bg-emerald-600 text-white font-bold rounded-xl hover:bg-emerald-700 transition-all shadow-sm shadow-emerald-200 flex items-center gap-2"
      >
        ➕ Tambah Gudang
      </button>
    </div>

    <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 flex flex-col md:flex-row gap-4 justify-between items-center">
      <div class="relative w-full md:w-80">
        <input 
          v-model="searchQuery" 
          type="text" 
          placeholder="Cari nama atau lokasi gudang..." 
          class="w-full pl-10 pr-4 py-2 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 shadow-sm"
        >
        <span class="absolute left-3 top-2.5 text-slate-400">🔍</span>
      </div>
      
      <div class="text-sm font-bold text-slate-500 bg-slate-50 px-4 py-2 rounded-xl border border-slate-100">
        Total Gudang: <span class="text-emerald-600 text-base font-black">{{ filteredWarehouses.length }}</span>
      </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
      <div v-if="loading" class="flex flex-col items-center justify-center py-20">
        <div class="animate-spin rounded-full h-12 w-12 border-b-4 border-emerald-500 mb-4"></div>
        <p class="text-slate-500 font-medium">Memuat data gudang...</p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50 text-slate-600 text-sm border-b border-slate-100">
              <th class="p-4 font-semibold w-24">ID Gudang</th>
              <th class="p-4 font-semibold">Nama Gudang</th>
              <th class="p-4 font-semibold">Lokasi / Alamat</th>
              <th class="p-4 font-semibold">Keterangan / Kapasitas</th>
              <th class="p-4 font-semibold text-center">Status</th>
              <th class="p-4 font-semibold text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-if="filteredWarehouses.length === 0">
              <td colspan="6" class="p-12 text-center text-slate-400 font-medium">Tidak ada data gudang yang ditemukan.</td>
            </tr>
            
            <tr v-for="warehouse in filteredWarehouses" :key="warehouse.id" class="hover:bg-emerald-50/30 transition group">
              <td class="p-4 text-xs font-mono font-bold text-slate-400">#WH-{{ warehouse.id }}</td>
              <td class="p-4">
                <span class="font-bold text-slate-800 block text-base">{{ warehouse.name }}</span>
              </td>
              <td class="p-4 text-slate-600 font-medium text-sm">
                {{ warehouse.location || warehouse.address || '-' }}
              </td>
              <td class="p-4 text-slate-500 text-sm font-medium">
                {{ warehouse.description || 'Gudang penyimpanan obat umum' }}
              </td>
              <td class="p-4 text-center">
                <span v-if="warehouse.is_active ?? true" class="px-3 py-1 bg-emerald-100 text-emerald-700 font-bold rounded-full text-xs">AKTIF</span>
                <span v-else class="px-3 py-1 bg-slate-100 text-slate-500 font-bold rounded-full text-xs">NON-AKTIF</span>
              </td>
              <td class="p-4 text-center">
                <div class="flex gap-2 justify-center">
                  <button @click="openModal(warehouse)" class="px-3 py-1.5 bg-amber-50 text-amber-600 font-bold rounded-lg hover:bg-amber-600 hover:text-white transition shadow-sm text-sm">
                    ✏️ Edit
                  </button>
                  <button @click="deleteWarehouse(warehouse.id)" class="px-3 py-1.5 bg-rose-50 text-rose-600 font-bold rounded-lg hover:bg-rose-600 hover:text-white transition shadow-sm text-sm">
                    🗑️ Hapus
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <Teleport to="body">
      <div v-if="showModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center z-[99999] p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto relative animate-slideUp">
          
          <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold flex items-center gap-2 text-slate-800">
              <span class="text-emerald-600 text-2xl font-black">{{ editingId ? '✏️' : '➕' }}</span>
              {{ editingId ? 'Edit Data Gudang' : 'Buat Gudang Baru' }}
            </h2>
            <button @click="closeModal" class="text-slate-400 hover:text-red-500 font-bold text-2xl leading-none">&times;</button>
          </div>
          
          <form @submit.prevent="handleSubmit" class="space-y-4">
            <div>
              <label class="block text-sm font-bold text-slate-700 mb-1">Nama Gudang <span class="text-red-500">*</span></label>
              <input v-model="form.name" type="text" placeholder="Cth: Gudang Pusat B" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none font-medium" required>
            </div>

            <div>
              <label class="block text-sm font-bold text-slate-700 mb-1">Lokasi / Alamat Gudang <span class="text-red-500">*</span></label>
              <input v-model="form.location" type="text" placeholder="Cth: Lantai 2 Sayap Kiri" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none font-medium" required>
            </div>

            <div>
              <label class="block text-sm font-bold text-slate-700 mb-1">Keterangan Tambahan</label>
              <textarea v-model="form.description" rows="3" placeholder="Deskripsi mengenai kapasitas gudang atau kategori obat..." class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none resize-none font-medium"></textarea>
            </div>

            <div class="flex items-center gap-2 pt-2">
              <input v-model="form.is_active" type="checkbox" id="is_active" class="w-5 h-5 accent-emerald-600 rounded">
              <label war="is_active" class="text-sm font-bold text-slate-700 cursor-pointer select-none">Aktifkan Gudang Ini</label>
            </div>

            <div class="flex justify-end gap-4 mt-8 pt-4 border-t border-slate-100">
              <button type="button" @click="closeModal" class="px-4 py-2 font-bold text-slate-600 hover:text-slate-800 transition">Batal</button>
              <button type="submit" :disabled="isSubmitting" class="px-5 py-2 bg-emerald-600 text-white font-bold rounded-xl hover:bg-emerald-700 transition flex items-center gap-2 disabled:opacity-50">
                <span v-if="isSubmitting" class="animate-spin">⏳</span>
                <span v-else>💾</span>
                {{ isSubmitting ? 'Menyimpan...' : 'Simpan Gudang' }}
              </button>
            </div>
          </form>

        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/services/api'
import { ElMessage, ElMessageBox } from 'element-plus'

const warehouses = ref([])
const loading = ref(false)
const isSubmitting = ref(false)
const searchQuery = ref('')
const showModal = ref(false)
const editingId = ref(null)

const form = ref({
  name: '',
  location: '',
  description: '',
  is_active: true
})

// Filter pencarian live-time bertenaga Vue computed
const filteredWarehouses = computed(() => {
  if (!searchQuery.value) return warehouses.value
  const query = searchQuery.value.toLowerCase()
  return warehouses.value.filter(wh => 
    (wh.name && wh.name.toLowerCase().includes(query)) || 
    (wh.location && wh.location.toLowerCase().includes(query))
  )
})

// Ambil Data dari API Laravel
const fetchWarehouses = async () => {
  loading.value = true
  try {
    const response = await api.get('warehouses')
    // Pelindung jika format return data API dibungkus pagination atau object data.data
    const rawData = response.data?.data
    warehouses.value = (rawData && rawData.data) ? rawData.data : (rawData || response.data || [])
  } catch (error) {
    ElMessage.error('Gagal mengambil data gudang dari server.')
    console.error(error)
  } finally {
    loading.value = false
  }
}

// Buka Modal (Mode Tambah Baru / Edit Data)
const openModal = (warehouseItem = null) => {
  if (warehouseItem) {
    editingId.value = warehouseItem.id
    form.value = {
      name: warehouseItem.name,
      location: warehouseItem.location || warehouseItem.address || '',
      description: warehouseItem.description || '',
      is_active: warehouseItem.is_active ?? true
    }
  } else {
    editingId.value = null
    form.value = {
      name: '',
      location: '',
      description: '',
      is_active: true
    }
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  editingId.value = null
}

// Submit Data (Tambah & Update)
const handleSubmit = async () => {
  if (isSubmitting.value) return
  isSubmitting.value = true

  try {
    if (editingId.value) {
      await api.put(`warehouses/${editingId.value}`, form.value)
      ElMessage.success('Gudang berhasil diperbarui!')
    } else {
      await api.post('warehouses', form.value)
      ElMessage.success('Gudang baru berhasil dibuat!')
    }
    closeModal()
    await fetchWarehouses()
  } catch (error) {
    const msg = error.response?.data?.message || 'Gagal menyimpan data gudang.'
    ElMessage.error(msg)
  } finally {
    isSubmitting.value = false
  }
}

// Hapus Data Gudang
const deleteWarehouse = async (id) => {
  try {
    await ElMessageBox.confirm(
      'Apakah Anda yakin ingin menghapus gudang ini? Pastikan tidak ada stok obat yang tertinggal di gudang ini.',
      'Peringatan Sistem',
      {
        confirmButtonText: 'Ya, Hapus Gudang',
        cancelButtonText: 'Batal',
        type: 'warning',
      }
    )

    await api.delete(`warehouses/${id}`)
    ElMessage.success('Gudang berhasil dihapus dari sistem.')
    await fetchWarehouses()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('Gagal menghapus gudang. Kemungkinan gudang masih memiliki relasi stok fisik.')
    }
  }
}

onMounted(() => {
  fetchWarehouses()
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