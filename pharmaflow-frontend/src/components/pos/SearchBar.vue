<template>
    <div
        class="bg-white rounded-3xl p-6 border shadow-sm"
    >
        <input
            :value="modelValue"
            @input="$emit('update:modelValue',$event.target.value)"
            placeholder="Cari obat..."
            class="
                w-full
                h-12
                rounded-2xl
                border
                px-4
                outline-none
            "
        />
    </div>
</template>

<script setup>
defineProps({
    modelValue: String
});

defineEmits([
    "update:modelValue"
]);
</script>