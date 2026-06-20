```vue

<template>
  <div class="p-6">

    <div class="mb-6">
      <h1 class="text-3xl font-bold">
        Purchase Management
      </h1>

      <p class="text-gray-500">
        Kelola pembelian obat dari supplier
      </p>
    </div>

    <div class="bg-white rounded-xl shadow p-6">

      <div class="flex justify-between mb-4">
        <h2 class="font-semibold text-lg">
          Daftar Purchase Order
        </h2>

        <button
          @click="dialogVisible = true"
          class="bg-green-500 text-white px-4 py-2 rounded"
        >
          + Buat Purchase
        </button>
      </div>

      <table class="w-full">

        <thead>
          <tr>
            <th>No PO</th>
            <th>Supplier</th>
            <th>Tanggal</th>
            <th>Status</th>
            <th>Total</th>
            <th>Aksi</th>
          </tr>
        </thead>

        <tbody>

          <tr
            v-for="purchase in purchases"
            :key="purchase.id"
          >
            <td>{{ purchase.po_number }}</td>

            <td>
              {{ purchase.supplier?.name }}
            </td>

            <td>
              {{ purchase.purchase_date }}
            </td>

            <td>
              {{ purchase.status }}
            </td>

            <td>
              Rp {{ purchase.total_amount }}
            </td>

            <td class="flex gap-2">

              <button
                v-if="purchase.status === 'draft'"
                @click="approvePurchase(purchase.id)"
                class="bg-blue-500 text-white px-3 py-1 rounded"
              >
                Approve
              </button>

              <button
                v-else-if="purchase.status === 'approved'"
                @click="markOrdered(purchase.id)"
                class="bg-orange-500 text-white px-3 py-1 rounded"
              >
                Ordered
              </button>

              <button
                v-else-if="purchase.status === 'ordered'"
                @click="openReceiveDialog(purchase)"
                class="bg-green-500 text-white px-3 py-1 rounded"
              >
                Receive
              </button>

              <button
                @click="showDetail(purchase)"
                class="bg-gray-600 text-white px-3 py-1 rounded"
              >
                Detail
              </button>

            </td>
          </tr>

        </tbody>

      </table>

    </div>

    <!-- CREATE -->

    <el-dialog
      v-model="dialogVisible"
      title="Buat Purchase"
      width="800px"
    >

      <el-form label-position="top">

        <el-form-item label="Supplier">
          <el-select
            v-model="form.supplier_id"
            style="width:100%"
          >
            <el-option
              v-for="supplier in suppliers"
              :key="supplier.id"
              :label="supplier.name"
              :value="supplier.id"
            />
          </el-select>
        </el-form-item>
        
        <el-form-item label="Obat">
  <el-select
    v-model="form.items[0].medicine_id"
    style="width:100%"
  >
    <el-option
      v-for="medicine in medicines"
      :key="medicine.id"
      :label="medicine.name"
      :value="medicine.id"
    />
  </el-select>
</el-form-item>

<el-form-item label="Quantity">
  <el-input-number
    v-model="form.items[0].quantity"
    :min="1"
  />
</el-form-item>

<el-form-item label="Harga Beli">
  <el-input-number
    v-model="form.items[0].unit_price"
    :min="0"
  />
</el-form-item>

      </el-form>

      <template #footer>

        <el-button
          @click="dialogVisible = false"
        >
          Batal
        </el-button>

        <el-button
          type="primary"
          @click="createPurchase"
        >
          Simpan
        </el-button>

      </template>

    </el-dialog>

    <!-- RECEIVE -->

    <el-dialog
      v-model="receiveDialog"
      title="Receive Purchase"
      width="700px"
    >

      <el-form label-position="top">

        <el-form-item label="Warehouse">
          <el-select
            v-model="receiveForm.warehouse_id"
            style="width:100%"
          >
            <el-option
              v-for="warehouse in warehouses"
              :key="warehouse.id"
              :label="warehouse.name"
              :value="warehouse.id"
            />
          </el-select>
        </el-form-item>

      </el-form>

      <template #footer>

        <el-button
          @click="receiveDialog = false"
        >
          Batal
        </el-button>

        <el-button
          type="success"
          @click="receivePurchase"
        >
          Receive
        </el-button>

      </template>

    </el-dialog>

    <!-- DETAIL -->

    <el-dialog
      v-model="detailDialog"
      title="Purchase Detail"
      width="700px"
    >

      <div v-if="selectedPurchase">

        <p>
          <b>PO:</b>
          {{ selectedPurchase.po_number }}
        </p>

        <p>
          <b>Supplier:</b>
          {{ selectedPurchase.supplier?.name }}
        </p>

        <p>
          <b>Status:</b>
          {{ selectedPurchase.status }}
        </p>

        <p>
          <b>Total:</b>
          Rp {{ selectedPurchase.total_amount }}
        </p>

        <hr class="my-4">

<table class="w-full border">
  <thead>
    <tr class="bg-gray-100">
      <th class="p-2">Obat</th>
      <th class="p-2">Qty</th>
      <th class="p-2">Harga</th>
      <th class="p-2">Subtotal</th>
    </tr>
  </thead>

  <tbody>

    <tr
      v-for="item in selectedPurchase.items"
      :key="item.id"
    >
      <td class="p-2">
        {{ item.medicine?.name }}
      </td>

      <td class="p-2">
        {{ item.quantity }}
      </td>

      <td class="p-2">
        Rp {{ item.unit_price }}
      </td>

      <td class="p-2">
        Rp {{ item.quantity * item.unit_price }}
      </td>
    </tr>

  </tbody>
</table>

      </div>

    </el-dialog>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import api from '@/services/api'

const loading = ref(false)
const purchases = ref([])
const detailDialog = ref(false)
const selectedPurchase = ref(null)
const showDetail = (purchase) => {
    console.log('DETAIL CLICK', purchase)
  selectedPurchase.value = purchase
  detailDialog.value = true
}

const dialogVisible = ref(false)

const suppliers = ref([])
const medicines = ref([])
const receiveDialog = ref(false)

const warehouses = ref([])

const receiveForm = ref({
  purchase_id: null,
  warehouse_id: '',
  received_items: []
})
const form = ref({
  supplier_id: '',
  notes: '',
  items: [
    {
      medicine_id: '',
      quantity: 1,
      unit_price: 0
    }
  ]
})

const fetchSuppliers = async () => {
  try {
    const response = await api.get('/suppliers')

    suppliers.value =
      response.data?.data?.data ||
      response.data?.data ||
      []
  } catch (error) {
    console.error(error)
  }
}

const fetchMedicines = async () => {
  try {
    const response = await api.get('/medicines')

    medicines.value =
      response.data?.data?.data ||
      response.data?.data ||
      []
  } catch (error) {
    console.error(error)
  }
}

const fetchWarehouses = async () => {
  try {
    const response = await api.get('/warehouses')

    warehouses.value =
      response.data?.data?.data ||
      response.data?.data ||
      []
  } catch (error) {
    console.error(error)
  }
}

const openReceiveDialog = (purchase) => {
  console.log('RECEIVE', purchase)

  receiveForm.value = {
    purchase_id: purchase.id,
    warehouse_id: '',
    received_items: purchase.items.map(item => ({
      purchase_item_id: item.id,
      medicine_name: item.medicine?.name,
      quantity_ordered: item.quantity,
      quantity_received:
           item.quantity -
         (item.quantity_received || 0)
    }))
  }

  receiveDialog.value = true
}

const receivePurchase = async () => {
  try {
    await api.post(
      `/purchases/${receiveForm.value.purchase_id}/receive`,
      {
        warehouse_id:
          receiveForm.value.warehouse_id,

        received_items:
          receiveForm.value.received_items
      }
    )

    ElMessage.success(
      'Purchase berhasil diterima'
    )

    receiveDialog.value = false

    await fetchPurchases()
  } catch (error) {
    console.error(error)

    ElMessage.error(
      error.response?.data?.message ||
      'Gagal receive purchase'
    )
  }
}
  
const addItem = () => {
  form.value.items.push({
    medicine_id: '',
    quantity: 1,
    unit_price: 0
  })
}

const createPurchase = async () => {
  try {
    await api.post('/purchases', form.value)

    ElMessage.success(
      'Purchase berhasil dibuat'
    )

    dialogVisible.value = false

    form.value = {
      supplier_id: '',
      notes: '',
      items: [
        {
          medicine_id: '',
          quantity: 1,
          unit_price: 0
        }
      ]
    }

    fetchPurchases()
  } catch (error) {
    console.error(error)

    ElMessage.error(
      error.response?.data?.message ||
      'Gagal membuat purchase'
    )
  }
}

const approvePurchase = async (id) => {
  try {
    await api.post(`/purchases/${id}/approve`)

    await fetchPurchases()

    ElMessage.success(
      'Purchase berhasil di approve'
    )

  } catch (error) {

    console.log(error.response?.data)

    ElMessage.error(
      error.response?.data?.message ||
      'Gagal approve purchase'
    )
    await fetchPurchases()

console.log('REFRESHED')
console.log(purchases.value)
  }
}

const markOrdered = async (id) => {
  try {
    await api.post(`/purchases/${id}/ordered`)

    await fetchPurchases()

    ElMessage.success(
      'Purchase berhasil diubah menjadi ordered'
    )

  } catch (error) {

    console.log(error.response?.data)

    ElMessage.error(
      error.response?.data?.message ||
      'Gagal update purchase'
    )
    await fetchPurchases()

console.log('REFRESHED')
console.log(purchases.value)
  }
}

const fetchPurchases = async () => {
  loading.value = true

  try {
    const response = await api.get('/purchases')

console.log('FULL RESPONSE')
console.log(response.data)

purchases.value =
  response.data.data.data

console.log('PURCHASES')
console.log(purchases.value)

    console.log(
  'PURCHASE PERTAMA:',
  JSON.parse(
    JSON.stringify(
      purchases.value[0]
    )
  )
)

  } catch (error) {
    console.error(error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchPurchases()
  fetchSuppliers()
  fetchMedicines()
  fetchWarehouses()
})
</script>
```
