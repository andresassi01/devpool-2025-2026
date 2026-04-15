<template>
  <div class="field is-grouped is-grouped-centered mt-6">
    <p class="control">
      <button 
        class="button is-light" 
        :disabled="paginaAtual <= 1 || isLoading"
        @click="$emit('mudar-pagina', paginaAtual - 1)"
      >
        <span class="icon"><i class="fas fa-chevron-left"></i></span>
        <span>Anterior</span>
      </button>
    </p>

    <p class="control">
      <span class="button is-static is-dark">
        Página {{ paginaAtual }}
      </span>
    </p>

    <p class="control">
      <button 
        class="button is-light" 
        :disabled="!temMais || isLoading"
        @click="$emit('mudar-pagina', paginaAtual + 1)"
      >
        <span>Próxima</span>
        <span class="icon"><i class="fas fa-chevron-right"></i></span>
      </button>
    </p>
  </div>
</template>

<script setup lang="ts">
interface Props {
  paginaAtual?: number;
  temMais?: boolean;
  isLoading?: boolean;
}

withDefaults(defineProps<Props>(), {
  paginaAtual: 1,
  temMais: false,
  isLoading: false
});

defineEmits<{
  (e: 'mudar-pagina', pagina: number): void
}>();
</script>

<style scoped>
.field.is-grouped .control:not(:first-child):not(:last-child) .button {
  border-radius: 0;
}
.field.is-grouped .control:first-child .button {
  border-bottom-right-radius: 0;
  border-top-right-radius: 0;
}
.field.is-grouped .control:last-child .button {
  border-bottom-left-radius: 0;
  border-top-left-radius: 0;
}
</style>