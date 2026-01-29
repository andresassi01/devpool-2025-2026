<template>
  <div class="level mb-4">
    <div class="level-left">
      <div class="field is-grouped is-grouped-multiline is-align-items-center">
        <p class="control">
          <button 
            class="button" 
            :class="quantidadeSelecionados > 0 ? 'is-danger' : 'is-light is-disabled'"
            :disabled="quantidadeSelecionados === 0" 
            @click="$emit('excluir-massa')"
            title="Excluir selecionados"
          >
            <span class="icon is-small">
              <i class="fas fa-trash"></i>
            </span>
            <span>Excluir</span>
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
}>();

defineEmits(['excluir-massa', 'incluir']);
</script>

<style scoped>
.button.is-disabled {
  opacity: 0.5;
  cursor: not-allowed;
  pointer-events: none;
}
</style>