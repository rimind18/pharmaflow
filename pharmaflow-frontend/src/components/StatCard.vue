<template>
  <div :class="[bg, 'p-6 rounded-2xl border border-transparent shadow-sm']">
    <div class="flex items-center justify-between mb-2">
      <span class="text-sm font-bold text-slate-500 uppercase">{{ title }}</span>
      <span class="text-2xl">{{ icon }}</span>
    </div>
    <p :class="[color, 'text-2xl font-black']">{{ value }}</p>
  </div>
</template>
<script setup>
defineProps(['title', 'value', 'icon', 'color', 'bg'])
</script>