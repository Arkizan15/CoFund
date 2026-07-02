<template>
  <button @click="beriTahuParent">Klik Aku!</button>
</template>

<script setup>
// Mendeklarasikan emit yang tersedia
const emit = defineEmits(['tombolDiklik'])

function beriTahuParent() {
  // Memicu event dan mengirimkan data tambahan (payload) ke Parent
  emit('tombolDiklik', console.log('Halo dari Child!'))
}
</script>