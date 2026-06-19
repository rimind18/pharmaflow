<template>
  <div
    class="
    rounded-[32px]
    bg-gradient-to-r
    from-emerald-600
    to-emerald-500
    text-white
    p-6
    shadow-xl
    "
  >
    <div class="flex justify-between items-center">

      <div>
        <p class="uppercase text-emerald-100 tracking-widest text-xs">
          PharmaFlow POS
        </p>

        <h1 class="text-3xl font-bold mt-2">
          POS Kasir
        </h1>

        <p class="text-emerald-100 mt-1">
          {{ cashierName }}
        </p>
      </div>

      <div class="flex gap-4">

        <div class="bg-white/10 rounded-2xl px-5 py-4">
          <p class="text-xs">Transaksi</p>
          <h3 class="text-2xl font-bold">
            {{ transactions }}
          </h3>
        </div>

        <div class="bg-white/10 rounded-2xl px-5 py-4">
          <p class="text-xs">Pendapatan</p>
          <h3 class="text-2xl font-bold">
            Rp{{ revenue }}
          </h3>
        </div>

      </div>

    </div>
  </div>
</template>

<script setup>
defineProps({
  cashierName: String,
  transactions: Number,
  revenue: Number
})
</script>