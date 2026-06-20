<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'

const report = ref(null)
const loading = ref(false)

const formatRupiah = (value) => {
  return new Intl.NumberFormat('id-ID').format(
    value || 0
  )
}

const fetchReport = async () => {
  loading.value = true

  try {
    const response =
      await api.get('/reports/purchases')

    report.value =
      response.data.data

  } catch (error) {
    console.error(error)
  } finally {
    loading.value = false
  }
}

const exportPurchase = async () => {

  try {

    const response =
      await api.get(
        '/reports/purchases/export',
        {
          responseType: 'blob'
        }
      )

    const blob =
      new Blob([
        response.data
      ])

    const url =
      window.URL.createObjectURL(blob)

    const link =
      document.createElement('a')

    link.href = url

    link.download =
      'purchase_report.xlsx'

    document.body.appendChild(link)

    link.click()

    link.remove()

    window.URL.revokeObjectURL(url)

  } catch (error) {

    console.error(error)

    alert(
      'Export gagal'
    )
  }
}

onMounted(() => {
  fetchReport()
})
</script>

<template>

  <div class="p-6">

    <div class="flex justify-between items-center mb-6">

      <div>

        <h1 class="text-3xl font-bold">
          Purchase Report
        </h1>

        <p class="text-gray-500">
          Laporan pembelian supplier
        </p>

      </div>

   <div class="flex gap-2">

  <button
    @click="fetchReport"
    class="bg-blue-600 text-white px-4 py-2 rounded"
  >
    Refresh
  </button>

  <button
    @click="exportPurchase"
    class="bg-green-600 text-white px-4 py-2 rounded"
  >
    Export Excel
  </button>

</div>

    </div>

    <div
      v-if="loading"
      class="text-center py-10"
    >
      Loading...
    </div>

    <div v-else-if="report">

      <!-- KPI -->

      <div
        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6"
      >

        <div
          class="bg-white shadow rounded p-4"
        >
          <div class="text-gray-500">
            Total Purchase Order
          </div>

          <div
            class="text-2xl font-bold"
          >
            {{
              report.summary
                ?.total_purchase_orders
            }}
          </div>
        </div>

        <div
          class="bg-white shadow rounded p-4"
        >
          <div class="text-gray-500">
            Total Purchase Amount
          </div>

          <div
            class="text-2xl font-bold"
          >
            Rp {{ formatRupiah(report.summary?.total_purchase_amount) }}
          </div>
        </div>

        <div
          class="bg-white shadow rounded p-4"
        >
          <div class="text-gray-500">
            Total Items Purchased
          </div>

          <div
            class="text-2xl font-bold"
          >
            {{
              report.summary
                ?.total_items_purchased
            }}
          </div>
        </div>

        <div
          class="bg-white shadow rounded p-4"
        >
          <div class="text-gray-500">
            Average Purchase Value
          </div>

          <div
            class="text-2xl font-bold"
          >
            Rp {{ formatRupiah(report.summary?.average_purchase_value) }}
          </div>
        </div>

      </div>

      <!-- TOP SUPPLIER -->

      <div
        class="bg-white shadow rounded p-4 mb-6"
      >

        <h2
          class="text-xl font-bold mb-4"
        >
          Top Suppliers
        </h2>

        <table class="w-full">

          <thead>

            <tr>
              <th class="text-left">
                Supplier
              </th>

              <th class="text-left">
                Orders
              </th>

              <th class="text-left">
                Amount
              </th>
            </tr>

          </thead>

          <tbody>

            <tr
              v-for="supplier in report.top_suppliers"
              :key="supplier.supplier_id"
            >
              <td>
                {{
                  supplier.supplier?.name
                }}
              </td>

              <td>
                {{
                  supplier.total_orders
                }}
              </td>

              <td>
                Rp {{ formatRupiah(supplier.total_amount) }}
              </td>
            </tr>

          </tbody>

        </table>

      </div>

      <!-- TOP MEDICINES -->

      <div
        class="bg-white shadow rounded p-4"
      >

        <h2
          class="text-xl font-bold mb-4"
        >
          Top Medicines
        </h2>

        <table class="w-full">

          <thead>

            <tr>
              <th class="text-left">
                Medicine
              </th>

              <th class="text-left">
                Quantity
              </th>
            </tr>

          </thead>

          <tbody>

            <tr
              v-for="medicine in report.top_medicines"
              :key="medicine.medicine_id"
            >
              <td>
                {{
                  medicine.medicine?.name
                }}
              </td>

              <td>
                {{
                  medicine.total_quantity
                }}
              </td>
            </tr>

          </tbody>

        </table>

      </div>

    </div>

  </div>

</template>