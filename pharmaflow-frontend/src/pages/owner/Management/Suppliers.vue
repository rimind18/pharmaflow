<template>
  <div class="space-y-6 animate-fadeIn">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-slate-800">🚚 Management Supplier</h1>
        <p class="text-slate-500 mt-1">Kelola database distributor obat Anda.</p>
      </div>
      <button @click="openModal('add')" class="px-5 py-2.5 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition">
        ➕ Tambah Supplier
      </button>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
      <table class="w-full text-left">
        <thead>
          <tr class="bg-slate-50 text-slate-600 text-sm border-b border-slate-100">
            <th class="p-4 w-20 text-center">ID</th>
            <th class="p-4">Nama Perusahaan</th>
            <th class="p-4">Kontak Person</th>
            <th class="p-4">Alamat Lengkap</th>
            <th class="p-4 text-center">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-if="suppliers.length === 0">
            <td colspan="5" class="p-8 text-center text-slate-500">Belum ada data supplier.</td>
          </tr>
          <tr v-for="sup in suppliers" :key="sup.id" class="hover:bg-slate-50 transition">
            <td class="p-4 text-center font-bold text-blue-500">#{{ sup.id }}</td>
            <td class="p-4 font-bold text-slate-800">{{ sup.name }}</td>
            <td class="p-4 text-sm">
              <span class="font-bold text-slate-700">{{ sup.contact_person }}</span><br>
              <span class="text-slate-500">{{ sup.phone }}</span>
            </td>
            <td class="p-4 text-sm text-slate-600">
              {{ sup.address }}<br>
              <span class="text-xs text-slate-400">{{ sup.city }}, {{ sup.province }}</span>
            </td>
            <td class="p-4 text-center">
              <button @click="openModal('edit', sup)" class="p-2 bg-orange-50 text-orange-600 rounded-lg mr-2" title="Edit">✏️</button>
              <button @click="deleteSupplier(sup.id)" class="p-2 bg-red-50 text-red-600 rounded-lg" title="Hapus">🗑️</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="showModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl p-6 max-h-[90vh] overflow-y-auto relative">
        
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-xl font-bold text-slate-800">
            {{ modalMode === 'add' ? '➕ Tambah Supplier' : '✏️ Edit Supplier' }}
          </h2>
          <button @click="showModal = false" class="text-slate-400 hover:text-red-500 text-3xl leading-none">&times;</button>
        </div>
        
        <div class="grid grid-cols-2 gap-4">
          <div class="col-span-2">
            <label class="block text-sm font-bold text-slate-700 mb-1">Nama Perusahaan <span class="text-red-500">*</span></label>
            <input v-model="form.name" placeholder="PT Maju Sehat" class="w-full px-4 py-2 border rounded-xl outline-none focus:ring-2 focus:ring-blue-500">
          </div>
          
          <div>
            <label class="block text-sm font-bold text-slate-700 mb-1">Nama Kontak <span class="text-red-500">*</span></label>
            <input v-model="form.contact_person" placeholder="Budi Santoso" class="w-full px-4 py-2 border rounded-xl outline-none focus:ring-2 focus:ring-blue-500">
          </div>
          
          <div>
            <label class="block text-sm font-bold text-slate-700 mb-1">Nomor Telepon <span class="text-red-500">*</span></label>
            <input v-model="form.phone" placeholder="0812xxxxxx" class="w-full px-4 py-2 border rounded-xl outline-none focus:ring-2 focus:ring-blue-500">
          </div>
          
          <div class="col-span-2">
            <label class="block text-sm font-bold text-slate-700 mb-1">Email</label>
            <input v-model="form.email" placeholder="email@perusahaan.com" class="w-full px-4 py-2 border rounded-xl outline-none focus:ring-2 focus:ring-blue-500">
          </div>
          
          <div class="col-span-2">
            <label class="block text-sm font-bold text-slate-700 mb-1">Alamat Lengkap <span class="text-red-500">*</span></label>
            <textarea v-model="form.address" placeholder="Jalan Raya..." rows="2" class="w-full px-4 py-2 border rounded-xl outline-none focus:ring-2 focus:ring-blue-500"></textarea>
          </div>
          
          <div>
            <label class="block text-sm font-bold text-slate-700 mb-1">Kota <span class="text-red-500">*</span></label>
            <input v-model="form.city" placeholder="Jakarta Pusat" class="w-full px-4 py-2 border rounded-xl outline-none focus:ring-2 focus:ring-blue-500">
          </div>
          
          <div>
            <label class="block text-sm font-bold text-slate-700 mb-1">Provinsi <span class="text-red-500">*</span></label>
            <input v-model="form.province" placeholder="DKI Jakarta" class="w-full px-4 py-2 border rounded-xl outline-none focus:ring-2 focus:ring-blue-500">
          </div>
          
          <div>
            <label class="block text-sm font-bold text-slate-700 mb-1">Kode Pos <span class="text-red-500">*</span></label>
            <input v-model="form.postal_code" placeholder="10110" class="w-full px-4 py-2 border rounded-xl outline-none focus:ring-2 focus:ring-blue-500">
          </div>
        </div>

        <div class="flex justify-end gap-3 mt-8">
          <button @click="showModal = false" class="px-5 py-2 text-slate-600 font-bold hover:bg-slate-100 rounded-xl transition">Batal</button>
          <button @click="saveSupplier" class="px-6 py-2 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition">
            💾 Simpan Supplier
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

const suppliers = ref([])
const showModal = ref(false)
const modalMode = ref('add')
const form = ref({})

// Inisialisasi form default agar bersih saat tambah data
const resetForm = () => {
  form.value = { 
    id: null, name: '', contact_person: '', phone: '', email: '', 
    address: '', city: '', province: '', postal_code: '' 
  }
}

const fetchSuppliers = async () => {
  try {
    const res = await api.get('suppliers')
    suppliers.value = res.data.data.data || res.data.data || []
  } catch (e) { 
    ElMessage.error('Gagal memuat data supplier') 
  }
}

const saveSupplier = async () => {
  try {
    if (modalMode.value === 'add') {
      await api.post('suppliers', form.value)
    } else {
      await api.put(`suppliers/${form.value.id}`, form.value)
    }
    ElMessage.success('Data supplier tersimpan!')
    showModal.value = false
    fetchSuppliers()
  } catch (e) {
    if (e.response && e.response.status === 422) {
      const errors = e.response.data.errors
      let msg = 'Gagal menyimpan:<br>'
      Object.keys(errors).forEach(key => {
        msg += `- ${errors[key][0]}<br>`
      })
      ElMessage({ message: msg, type: 'error', dangerouslyUseHTMLString: true })
    } else {
      ElMessage.error('Terjadi kesalahan pada server')
    }
  }
}

const deleteSupplier = (id) => {
  ElMessageBox.confirm('Yakin ingin menghapus supplier ini?', 'Peringatan', {
    confirmButtonText: 'Ya, Hapus',
    cancelButtonText: 'Batal',
    type: 'warning'
  }).then(async () => {
    try {
      await api.delete(`suppliers/${id}`)
      ElMessage.success('Supplier dihapus')
      fetchSuppliers()
    } catch (e) {
      ElMessage.error(e.response?.data?.message || 'Gagal menghapus supplier')
    }
  }).catch(() => {})
}

const openModal = (mode, data = null) => {
  modalMode.value = mode
  if (data) {
    form.value = { ...data }
  } else {
    resetForm()
  }
  showModal.value = true
}

onMounted(fetchSuppliers)
</script>