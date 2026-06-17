<template>
  <div>
    <h3>Componentes Desacoplados com Pinia</h3>
    <ProductImage />
    <ProductOptions />
    <AddToCartButton />
    <p class="legenda">Os componentes se comunicam através da store (Pinia).</p>
  </div>
</template>

<script setup lang="ts">

import ProductImage from './ProductImage.vue'
import ProductOptions from './ProductOptions.vue'
import AddToCartButton from './AddToCartButton.vue'

</script>

<style scoped>
.legenda {
  font-style: italic;
  font-size: 0.9em;
  color: #555;
}
</style>

