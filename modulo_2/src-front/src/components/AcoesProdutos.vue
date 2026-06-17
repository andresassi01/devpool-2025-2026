<template>
  <div class="level mb-4">
    <div class="level-left">
      <div class="field is-grouped is-grouped-multiline is-align-items-center">
        <p class="control">
          <button v-if="modoLixeira" class="button is-success" :disabled="quantidadeSelecionados === 0" @click="$emit('restaurar-massa')">
            <span class="icon"><i class="fas fa-undo"></i></span>
          </button>
          <button v-else class="button is-danger" :disabled="quantidadeSelecionados === 0" @click="$emit('excluir-massa')">
            <span class="icon"><i class="fas fa-trash"></i></span>
          </button>
        </p>
        <p v-if="quantidadeSelecionados > 0" class="control has-text-weight-semibold mr-3">
          {{ quantidadeSelecionados }} {{ quantidadeSelecionados === 1 ? 'selecionado' : 'selecionados' }}
        </p>
      </div>
    </div>

    <div class="level-right">
      <button class="button is-primary" @click="$emit('incluir')">
        <span class="icon is-small">
          <i class="fas fa-plus"></i>
        </span>
        <span>Incluir produto</span>
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  quantidadeSelecionados: number;
  modoLixeira: boolean;
}>();

defineEmits(['excluir-massa', 'incluir', 'restaurar-massa']);
</script>

<style scoped>
.button.is-disabled {
  opacity: 0.5;
  cursor: not-allowed;
  pointer-events: none;
}
</style>