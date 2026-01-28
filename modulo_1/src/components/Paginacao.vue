<template>
  <nav class="pagination is-centered is-rounded mt-5" role="navigation" aria-label="pagination">
    <button 
      class="pagination-previous" 
      :disabled="paginaAtual === 1" 
      @click="$emit('mudarPagina', paginaAtual - 1)"
    >
      <span class="icon"><i class="fas fa-chevron-left"></i></span>
    </button>

    <button 
      class="pagination-next" 
      :disabled="!temMais" 
      @click="$emit('mudarPagina', paginaAtual + 1)"
    >
      <span class="icon"><i class="fas fa-chevron-right"></i></span>
    </button>

    <ul class="pagination-list">
      <li v-for="n in paginasVisiveis" :key="n">
        <a 
          class="pagination-link" 
          :class="{ 'is-current': n === paginaAtual }"
          @click="$emit('mudarPagina', n)"
        >
          {{ n }}
        </a>
      </li>
    </ul>
  </nav>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
  paginaAtual: number;
  temMais: boolean;
}>();

defineEmits(['mudarPagina']);

const paginasVisiveis = computed(() => {
  const atual = props.paginaAtual;
  const range = [];

  let inicio = Math.max(1, atual - 2);
  
  for (let i = inicio; i <= atual; i++) {
    range.push(i);
  }

  if (props.temMais) {
    range.push(atual + 1);
  }

  return range;
});
</script>