<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'

const mutations = ref([])
const loading = ref(false)

const fetchData = async () => {
  loading.value = true

  try {

    const response =
      await api.get('/reports/stock-mutations')

    mutations.value =
      response.data.data.data || []

  } catch (error) {

    console.error(error)

  } finally {

    loading.value = false
  }
}

onMounted(() => {
  fetchData()
})
</script>

<template>

<div class="p-6">

  <div class="mb-6">

    <h1 class="text-3xl font-bold">
      Stock Mutation Report
    </h1>

    <p class="text-gray-500">
      Riwayat perubahan stok obat
    </p>

  </div>

  <div
    v-if="loading"
    class="text-center py-10"
  >
    Loading...
  </div>

  <div
    v-else
    class="bg-white rounded-xl shadow p-6"
  >

    <table class="w-full">

      <thead>
  <tr class="border-b">
    <th class="text-left py-3 px-2">ID</th>
    <th class="text-left py-3 px-2">Type</th>
    <th class="text-left py-3 px-2">Before</th>
    <th class="text-left py-3 px-2">Change</th>
    <th class="text-left py-3 px-2">After</th>
    <th class="text-left py-3 px-2">Notes</th>
    <th class="text-left py-3 px-2">Date</th>
  </tr>
</thead>

      <tbody>

        <tr
          v-for="item in mutations"
          :key="item.id"
          class="border-b"
        >

          <td class="py-3">
            {{ item.id }}
          </td>

          <td class="py-3">
            {{ item.type }}
          </td>

          <td class="py-3">
            {{ item.quantity_before }}
          </td>

          <td class="py-3 text-green-600 font-semibold">
            +{{ item.quantity_change }}
          </td>

          <td class="py-3">
            {{ item.quantity_after }}
          </td>

          <td class="py-3">
            {{ item.notes }}
          </td>

          <td class="py-3">
            {{ item.created_at }}
          </td>

        </tr>

      </tbody>

    </table>

  </div>

</div>

</template>