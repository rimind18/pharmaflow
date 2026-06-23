<template>
  <div class="space-y-6 animate-fadeIn">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
      <div>
        <h1 class="text-3xl font-bold text-slate-800 flex items-center gap-3">
          🔔 Pusat Notifikasi
        </h1>
        <p class="text-slate-500 mt-1 font-medium">Pantau peringatan stok, transaksi, dan pembaruan sistem.</p>
      </div>
      <button
        @click="markAllAsRead"
        :disabled="unreadCount === 0 || loading"
        class="px-5 py-2.5 bg-emerald-50 text-emerald-600 font-bold rounded-xl hover:bg-emerald-600 hover:text-white transition-all shadow-sm disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
      >
        <span>✓</span> Tandai Semua Dibaca
      </button>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
      <div v-if="loading" class="flex flex-col items-center justify-center py-20">
        <div class="animate-spin rounded-full h-12 w-12 border-b-4 border-emerald-500 mb-4"></div>
        <p class="text-slate-500 font-medium">Memuat daftar notifikasi...</p>
      </div>

      <div v-else-if="notifications.length === 0" class="flex flex-col items-center justify-center py-20 text-center px-4">
        <div class="text-6xl mb-4 opacity-50">📭</div>
        <h3 class="text-xl font-bold text-slate-700 mb-1">Belum Ada Notifikasi</h3>
        <p class="text-slate-500 font-medium">Saat ini tidak ada pemberitahuan baru untuk Anda.</p>
      </div>

      <div v-else class="divide-y divide-slate-100">
        <div
          v-for="item in notifications"
          :key="item.id"
          @click="!item.is_read ? markAsRead(item.id) : null"
          class="p-5 sm:p-6 transition-colors flex gap-4 items-start relative group"
          :class="!item.is_read ? 'bg-emerald-50/40 hover:bg-emerald-50 cursor-pointer' : 'hover:bg-slate-50'"
        >
          <div class="w-12 h-12 rounded-full flex-shrink-0 flex items-center justify-center text-2xl shadow-sm"
               :class="getIconStyle(item.type)">
            {{ getIcon(item.type) }}
          </div>

          <div class="flex-1 min-w-0 pt-1">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-1 sm:gap-4 mb-1">
              <h4 class="text-base font-bold truncate" :class="!item.is_read ? 'text-slate-800' : 'text-slate-600'">
                {{ item.title || 'Pemberitahuan Sistem' }}
              </h4>
              <span class="text-xs font-bold whitespace-nowrap" :class="!item.is_read ? 'text-emerald-600' : 'text-slate-400'">
                {{ formatTime(item.created_at) }}
              </span>
            </div>
            <p class="text-sm leading-relaxed" :class="!item.is_read ? 'text-slate-700 font-medium' : 'text-slate-500'">
              {{ item.message }}
            </p>
          </div>

          <div v-if="!item.is_read" class="w-3 h-3 bg-emerald-500 rounded-full flex-shrink-0 mt-3 shadow-sm shadow-emerald-200 animate-pulse"></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/services/api'
import { ElMessage } from 'element-plus'

const notifications = ref([])
const loading = ref(false)

// Hitung berapa notif yang belum dibaca (titik hijau)
const unreadCount = computed(() => {
  return notifications.value.filter(n => !n.is_read).length
})

// Fungsi Panggil Data dari Backend Laravel
const fetchNotifications = async () => {
  loading.value = true
  try {
    const response = await api.get('notifications')
    // Sesuaikan format data API Laravel
    const rawData = response.data?.data
    notifications.value = (rawData && rawData.data) ? rawData.data : (rawData || [])
  } catch (error) {
    ElMessage.error('Gagal memuat notifikasi dari server.')
    console.error("Error Fetch Notifications:", error)
  } finally {
    loading.value = false
  }
}

// Fungsi Tandai 1 Dibaca
const markAsRead = async (id) => {
  try {
    // Ubah UI seketika biar responsif
    const notifIndex = notifications.value.findIndex(n => n.id === id)
    if (notifIndex !== -1) {
      notifications.value[notifIndex].is_read = true
    }
    
    // Tembak ke API Background
    await api.post(`notifications/${id}/read`)
  } catch (error) {
    console.error('Gagal menandai dibaca:', error)
  }
}

// Fungsi Tandai Semua Dibaca
const markAllAsRead = async () => {
  try {
    loading.value = true
    // Ubah UI seketika
    notifications.value.forEach(n => n.is_read = true)
    
    // Tembak ke API
    await api.post('notifications/mark-all-read')
    ElMessage.success('Semua notifikasi telah ditandai dibaca.')
  } catch (error) {
    ElMessage.error('Gagal menandai semua dibaca.')
  } finally {
    loading.value = false
  }
}

// ==========================================
// FORMAT & STYLING HELPER
// ==========================================
const formatTime = (dateString) => {
  if (!dateString) return '-'
  const date = new Date(dateString)
  return date.toLocaleDateString('id-ID', {
    day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute:'2-digit'
  })
}

// Warna dan Icon otomatis menyesuaikan jenis notifikasi (Tipe/Role)
const getIcon = (type) => {
  const typeStr = String(type || '').toLowerCase();
  if (typeStr.includes('stock_low')) return '⚠️'
  if (typeStr.includes('stock_out')) return '🚨'
  if (typeStr.includes('expired')) return '💀'
  if (typeStr.includes('expiring')) return '⏳'
  if (typeStr.includes('order') || typeStr.includes('purchase')) return '📦'
  if (typeStr.includes('payment') || typeStr.includes('finance')) return '💰'
  return '🔔'
}

const getIconStyle = (type) => {
  const typeStr = String(type || '').toLowerCase();
  if (typeStr.includes('out') || typeStr.includes('expired')) return 'bg-rose-100 text-rose-600 border border-rose-200'
  if (typeStr.includes('low') || typeStr.includes('expiring')) return 'bg-amber-100 text-amber-600 border border-amber-200'
  if (typeStr.includes('order') || typeStr.includes('payment')) return 'bg-blue-100 text-blue-600 border border-blue-200'
  return 'bg-emerald-100 text-emerald-600 border border-emerald-200'
}

onMounted(() => {
  fetchNotifications()
})
</script>

<style scoped>
.animate-fadeIn { animation: fadeIn 0.3s ease-out forwards; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
</style>