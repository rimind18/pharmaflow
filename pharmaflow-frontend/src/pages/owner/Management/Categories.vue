<template>
  <div class="space-y-6 animate-fadeIn">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-slate-800">📂 Management Kategori</h1>
        <p class="text-slate-500 mt-1">Kelola klasifikasi dan jenis obat apotek Anda.</p>
      </div>
      <button @click="openModal('add')" class="px-5 py-2.5 bg-emerald-600 text-white font-bold rounded-xl hover:bg-emerald-700 transition">
        ➕ Tambah Kategori
      </button>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
      <table class="w-full text-left">
        <thead>
          <tr class="bg-slate-50 text-slate-600 text-sm border-b border-slate-100">
            <th class="p-4 w-20 text-center">ID</th>
            <th class="p-4">Kategori</th>
            <th class="p-4">Deskripsi</th>
            <th class="p-4 text-center">Item</th>
            <th class="p-4 text-center">Status</th>
            <th class="p-4 text-center">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-if="categories.length === 0">
            <td colspan="6" class="p-8 text-center text-slate-500">Belum ada kategori.</td>
          </tr>
          <tr v-for="cat in categories" :key="cat.id" class="hover:bg-slate-50">
            <td class="p-4 text-center font-bold text-slate-400">#{{ cat.id }}</td>
            <td class="p-4">
              <div class="flex items-center gap-3">
                <div v-if="cat.icon && cat.icon.length <= 4" class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl border border-emerald-100">{{ cat.icon }}</div>
                <div v-else class="w-10 h-10 rounded-lg bg-slate-50 flex items-center justify-center text-xl border border-slate-200">📁</div>
                <span class="font-bold text-slate-800">{{ cat.name }}</span>
              </div>
            </td>
            <td class="p-4 text-slate-500 text-sm">{{ cat.description || '-' }}</td>
            <td class="p-4 text-center font-bold text-blue-600">{{ cat.medicines_count || 0 }}</td>
            <td class="p-4 text-center">
              <span :class="cat.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'" class="px-3 py-1 text-xs font-bold rounded-full">
                {{ cat.is_active ? 'AKTIF' : 'NONAKTIF' }}
              </span>
            </td>
            <td class="p-4 text-center">
              <button @click="openModal('edit', cat)" class="p-2 bg-blue-50 text-blue-600 rounded-lg mr-2">✏️</button>
              <button @click="deleteCategory(cat.id)" class="p-2 bg-red-50 text-red-600 rounded-lg">🗑️</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="showModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
      
      <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto relative">
        
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-xl font-bold flex items-center gap-2 text-slate-800">
            <span class="text-indigo-600 text-2xl font-black">➕</span>
            {{ modalMode === 'add' ? 'Tambah Kategori Baru' : 'Edit Kategori' }}
          </h2>
          <button @click="closeModal" class="text-slate-400 hover:text-red-500 font-bold text-2xl leading-none">&times;</button>
        </div>
        
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-bold text-slate-700 mb-1">Nama Kategori <span class="text-red-500">*</span></label>
            <input v-model="form.name" type="text" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none">
          </div>
          
          <div>
            <label class="block text-sm font-bold text-slate-700 mb-1">Ikon / Emoji (Opsional)</label>
            <div class="flex gap-3">
              <div class="w-11 h-11 shrink-0 rounded-xl bg-slate-50 border border-slate-300 flex items-center justify-center text-xl overflow-hidden">
                <span v-if="form.icon && form.icon.length <= 4">{{ form.icon }}</span>
                <span v-else>📁</span>
              </div>
              <input v-model="form.icon" type="text" placeholder="Paste emoji disini (💊, 💉, 🌿)" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none">
            </div>
          </div>

          <div>
            <label class="block text-sm font-bold text-slate-700 mb-1">Deskripsi</label>
            <textarea v-model="form.description" rows="3" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none"></textarea>
          </div>
          
          <div v-if="modalMode === 'edit'" class="pt-2">
            <label class="block text-sm font-bold text-slate-700 mb-1">Status Kategori</label>
            <select v-model="form.is_active" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none">
              <option :value="true">Aktif</option>
              <option :value="false">Nonaktif</option>
            </select>
          </div>
        </div>
        
        <div class="flex justify-end gap-4 mt-8">
          <button @click="closeModal" class="px-4 py-2 font-bold text-slate-600 hover:text-slate-800 transition">Batal</button>
          <button @click="saveCategory" class="px-5 py-2 bg-emerald-600 text-white font-bold rounded-xl hover:bg-emerald-700 transition flex items-center gap-2">
            💾 Simpan Data
          </button>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import { ElMessage, ElMessageBox } from 'element-plus'

const categories = ref([])
const showModal = ref(false)
const modalMode = ref('add')

const form = ref({ 
  id: null, 
  name: '', 
  description: '', 
  icon: '',
  is_active: true
})

const resetForm = () => {
  form.value = { id: null, name: '', description: '', icon: '', is_active: true }
}

const fetchCategories = async () => {
  try {
    const res = await api.get('categories')
    categories.value = res.data.data.data || res.data.data || []
  } catch (e) { 
    ElMessage.error('Gagal memuat daftar kategori') 
  }
}

const saveCategory = async () => {
  try {
    if (modalMode.value === 'add') {
      await api.post('categories', form.value)
    } else {
      await api.put(`categories/${form.value.id}`, form.value)
    }
    ElMessage.success('Kategori berhasil disimpan!')
    showModal.value = false
    fetchCategories()
  } catch (e) {
    if (e.response && e.response.status === 422) {
      const errors = e.response.data.errors
      let msg = 'Gagal menyimpan:<br>'
      Object.keys(errors).forEach(key => {
        msg += `- ${errors[key][0]}<br>`
      })
      ElMessage({ message: msg, type: 'error', dangerouslyUseHTMLString: true })
    } else {
      ElMessage.error('Terjadi kesalahan server.')
    }
  }
}

const deleteCategory = (id) => {
  ElMessageBox.confirm('Yakin ingin menghapus kategori ini?', 'Peringatan', {
    confirmButtonText: 'Ya, Hapus!',
    cancelButtonText: 'Batal',
    type: 'error',
    center: true
  }).then(async () => {
    try {
      await api.delete(`categories/${id}`)
      ElMessage.success('Kategori berhasil dihapus')
      fetchCategories()
    } catch (e) {
      ElMessage.error(e.response?.data?.message || 'Gagal menghapus kategori.')
    }
  }).catch(() => {})
}

const openModal = (mode, data = null) => {
  modalMode.value = mode
  if (data) {
    form.value = { ...data, is_active: Boolean(data.is_active) }
  } else {
    resetForm()
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
}

onMounted(fetchCategories)
</script>