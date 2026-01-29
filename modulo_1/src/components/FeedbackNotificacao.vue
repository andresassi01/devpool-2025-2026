<template>
  <div v-if="ativo" class="notification mt-5" :class="classeTipo">
    <button class="delete" @click="$emit('fechar')"></button>
    {{ mensagem }}
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
  ativo: boolean;
  mensagem: string;
  tipo?: 'erro' | 'sucesso' | 'aviso'; 
}>();

defineEmits(['fechar']);

const classeTipo = computed(() => {
  switch (props.tipo) {
    case 'sucesso': return 'is-success';
    case 'aviso': return 'is-warning';
    default: return 'is-danger'; 
  }
});
</script>