<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h1 class="text-4xl font-bold">👥 Manajemen Karyawan</h1>
      <button
        @click="openForm()"
        class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold"
      >
        ➕ Tambah Karyawan
      </button>
    </div>

   <!-- Search & Filter -->
<div class="bg-white rounded-lg shadow-md p-4 grid grid-cols-3 gap-4">

  <div>
    <label class="block text-sm font-semibold mb-2">Cari</label>
    <input
      v-model="searchQuery"
      type="text"
      placeholder="Nama / Email"
      @keyup.enter="fetchEmployees"
      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
    />
  </div>

  <div>
    <label class="block text-sm font-semibold mb-2">Status</label>
    <select
  v-model="filterStatus"
  @change="fetchEmployees"
  class="w-full px-3 py-2 border border-gray-300 rounded-lg"
>
  <option value="">Semua</option>
  <option value="aktif">✅ Aktif</option>
  <option value="tidak_aktif">❌ Tidak Aktif</option>
</select>
  </div>

  <div class="flex items-end">
    <button
      @click="fetchEmployees"
      class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold"
    >
      🔍 Cari
    </button>
  </div>

</div>

    <!-- Loading -->
    <div v-if="loading" class="text-center py-8">
      <p class="text-lg text-gray-600">⏳ Memuat karyawan...</p>
    </div>

    <!-- Table -->
    <div v-else class="bg-white rounded-lg shadow-md overflow-hidden">
      <table class="w-full">
        <thead class="bg-gray-200">
          <tr>
            <th class="px-6 py-3 text-left font-semibold">Nama</th>
            <th class="px-6 py-3 text-left font-semibold">Email</th>
            <th class="px-6 py-3 text-left font-semibold">Telepon</th>
            <th class="px-6 py-3 text-left font-semibold">Departemen</th>
            <th class="px-6 py-3 text-left font-semibold">Status</th>
            <th class="px-6 py-3 text-left font-semibold">Bergabung</th>
            <th class="px-6 py-3 text-center font-semibold">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-for="employee in employees" :key="employee.id" class="hover:bg-gray-50">
            <td class="px-6 py-4">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold">
                  {{ employee.user?.name?.charAt(0).toUpperCase() || '?' }}
                </div>
                <p class="font-semibold">{{ employee.user?.name || '-' }}</p>
              </div>
            </td>
            <td class="px-6 py-4 text-sm">{{ employee.user?.email || '-' }}</td>
            <td class="px-6 py-4 text-sm">{{ employee.user?.phone || '-' }}</td>
            <td class="px-6 py-4">
              <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-semibold">
  {{ employee.position || '-' }}
</span>
            </td>
            <td class="px-6 py-4">
              <span :class="[
  'px-3 py-1 rounded-full font-semibold text-white text-sm',
  employee.user?.is_active ? 'bg-green-500' : 'bg-red-500'
]">
  {{ employee.user?.is_active ? '✅ Aktif' : '❌ Tidak Aktif' }}
</span>
            </td>
            <td class="px-6 py-4 text-sm">{{ formatDate(employee.created_at) }}</td>
            <td class="px-6 py-4 text-center">
              <div class="flex gap-2 justify-center">
                <button
                  @click="openForm(employee)"
                  class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 transition text-sm"
                >
                  ✏️ Edit
                </button>
                <button
                  @click="deleteEmployee(employee.id)"
                  class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 transition text-sm"
                >
                  🗑️ Hapus
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Form Modal -->
    <div v-if="showForm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-lg shadow-lg p-8 max-w-2xl w-full max-h-96 overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-2xl font-bold">{{ editingId ? '✏️ Edit Karyawan' : '➕ Tambah Karyawan' }}</h2>
          <button
            @click="showForm = false"
            class="text-gray-600 hover:text-gray-800 font-bold text-2xl"
          >
            ×
          </button>
        </div>

        <form @submit.prevent="saveEmployee" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-gray-700 font-semibold mb-2">Nama *</label>
              <input
                v-model="form.name"
                type="text"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                required
              />
            </div>

            <div>
              <label class="block text-gray-700 font-semibold mb-2">Email *</label>
              <input
                v-model="form.email"
                type="email"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                required
              />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-gray-700 font-semibold mb-2">Telepon *</label>
              <input
                v-model="form.phone"
                type="tel"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                required
              />
            </div>

            <div>
              <label class="block text-gray-700 font-semibold mb-2">Departemen *</label>
              <select
                v-model="form.department"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                required
              >
                <option value="">Pilih Departemen</option>
                <option value="kasir">Kasir</option>
                <option value="inventory">Inventory</option>
                <option value="admin">Admin</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">

  <div>
    <label class="block text-gray-700 font-semibold mb-2">
      Posisi *
    </label>

    <select
      v-model="form.position"
      class="w-full px-4 py-2 border border-gray-300 rounded-lg"
      required
    >
      <option value="">Pilih Posisi</option>
      <option value="Kasir">Kasir</option>
      <option value="Apoteker">Apoteker</option>
      <option value="Admin">Admin</option>
    </select>
  </div>

  <div>
    <label class="block text-gray-700 font-semibold mb-2">
      Tanggal Bergabung *
    </label>

    <input
      type="date"
      v-model="form.hire_date"
      class="w-full px-4 py-2 border border-gray-300 rounded-lg"
      required
    />
  </div>

</div>

          <div>
            <label class="block text-gray-700 font-semibold mb-2">Alamat</label>
            <textarea
              v-model="form.address"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 h-20"
            ></textarea>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-gray-700 font-semibold mb-2">Kota</label>
              <input
                v-model="form.city"
                type="text"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
              />
            </div>

            <div>
              <label class="block text-gray-700 font-semibold mb-2">Provinsi</label>
              <input
                v-model="form.province"
                type="text"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
              />
            </div>
          </div>

          <div v-if="!editingId">
            <label class="block text-gray-700 font-semibold mb-2">Password *</label>
            <input
              v-model="form.password"
              type="password"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
              required
            />
          </div>

          <div>
            <label class="flex items-center gap-2 cursor-pointer">
              <input
                v-model="form.is_active"
                type="checkbox"
                class="w-5 h-5"
              />
              <span class="font-semibold">Status Aktif</span>
            </label>
          </div>

          <div class="flex gap-2 pt-4">
            <button
              type="submit"
              :disabled="savingForm"
              class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:bg-gray-400 transition font-semibold"
            >
              {{ savingForm ? '⏳ Saving...' : '✅ Simpan' }}
            </button>
            <button
              type="button"
              @click="showForm = false"
              class="flex-1 px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400 transition font-semibold"
            >
              ❌ Batal
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import dayjs from 'dayjs'
import { ElMessage, ElMessageBox } from 'element-plus'

const employees = ref([])
const loading = ref(false)
const savingForm = ref(false)
const showForm = ref(false)
const editingId = ref(null)
const searchQuery = ref('')
const filterStatus = ref('')
const filterDepartment = ref('')

const form = ref({
  name: '',
  email: '',
  phone: '',
  department: '',

  position: '',
  hire_date: '',

  address: '',
  city: '',
  province: '',
  password: '',
  is_active: true,
  status: 'aktif'
})

const formatDate = (date) => {
  return dayjs(date).format('DD/MM/YYYY')
}

const getDepartmentLabel = (dept) => {
  const labels = {
    'kasir': '💵 Kasir',
    'inventory': '📦 Inventory',
    'admin': '⚙️ Admin',
  }
  return labels[dept] || dept
}

const fetchEmployees = async () => {
  loading.value = true

  try {
    const params = {
      per_page: 100
    }

    // Search
    if (searchQuery.value) {
      params.search = searchQuery.value
    }

    // Status filter
    if (filterStatus.value) {
      params.status = filterStatus.value
    }

    // Position filter
    if (filterDepartment.value) {
      params.position = filterDepartment.value
    }

    const response = await api.get('/employees', {
      params
    })

    console.log('DATA EMPLOYEES', response.data)

    employees.value = response.data.data.data || []

  } catch (error) {
    console.error(error)

    ElMessage.error(
      error.response?.data?.message ||
      'Gagal memuat karyawan'
    )
  } finally {
    loading.value = false
  }
}

const openForm = (employee = null) => {

console.log(employee)

  if (employee) {

    editingId.value = employee.id

    form.value = {
      ...employee,
      name: employee.user?.name || '',
      email: employee.user?.email || '',
      phone: employee.user?.phone || '',
      address: employee.user?.address || '',
      city: employee.user?.city || '',
      province: employee.user?.province || '',
      is_active: employee.user?.is_active ?? true,

      position: employee.position || '',
      hire_date: employee.hire_date
        ? employee.hire_date.substring(0, 10)
        : ''
    }
  } else {
    editingId.value = null
    resetForm()
  }

  showForm.value = true
}

const saveEmployee = async () => {
  console.log('EDITING ID =', editingId.value)

  savingForm.value = true

  try {
    const data = { ...form.value }

    if (editingId.value && !data.password) {
      delete data.password
    }

    if (editingId.value) {
      console.log('UPDATE MODE')

      await api.put(
        `/employees/${editingId.value}`,
        data
      )

      ElMessage.success('Karyawan berhasil diperbarui')
    } else {
      console.log('CREATE MODE')

      await api.post('/employees', data)

      ElMessage.success('Karyawan berhasil ditambahkan')
    }

    showForm.value = false

    await fetchEmployees()

  } catch (error) {
    console.log(error.response?.data)

    ElMessage.error(
      error.response?.data?.message ||
      'Gagal menyimpan karyawan'
    )
  } finally {
    savingForm.value = false
  }
}

const resetForm = () => {
  form.value = {
    name: '',
    email: '',
    phone: '',
    department: '',
    position: '',
    hire_date: '',
    address: '',
    city: '',
    province: '',
    password: '',
    is_active: true,
    status: 'aktif'
  }
}

const deleteEmployee = async (id) => {
  try {
    await ElMessageBox.confirm(
      'Apakah Anda yakin ingin menghapus karyawan ini?',
      'Warning',
      {
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        type: 'warning',
      }
    )

    await api.delete(`employees/${id}`)
    ElMessage.success('Karyawan berhasil dinonaktifkan')
    fetchEmployees()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('Gagal menghapus karyawan')
    }
  }
}

onMounted(() => {
  fetchEmployees()
})
</script>