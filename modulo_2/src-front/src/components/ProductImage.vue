<template>
  <div>
    <img :src="store.currentImage" alt="Imagem do Produto" width="200" />
  </div>
</template>

<script setup lang="ts">
import { useProductStore } from '../stores/productStore'

const store = useProductStore()
</script>

