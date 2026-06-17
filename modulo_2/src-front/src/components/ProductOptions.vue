<template>
  <div class="options">
    <label>Cor:</label>
    <select :value="store.selectedColor" @change="handleChange">
      <option value="azul">Azul</option>
      <option value="preto">Preto</option>
    </select>
  </div>
</template>

<script setup lang="ts">
import { useProductStore } from '../stores/productStore'

const store = useProductStore()

const handleChange = (event: Event) => {
  const target = event.target as HTMLSelectElement
  store.setSelectedColor(target.value)
}
</script>

