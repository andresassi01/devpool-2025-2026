<template>
  <button :disabled="store.isButtonDisabled">Adicionar ao Carrinho</button>
</template>

<script setup lang="ts">
import { useProductStore } from '../stores/productStore'

const store = useProductStore()
</script>

