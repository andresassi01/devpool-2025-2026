<template>
  <div class="modal" :class="{ 'is-active': ativo }">
    <div class="modal-background" @click="$emit('fechar')"></div>
    <div class="modal-card">
      <header class="modal-card-head">
        <p class="modal-card-title has-text-danger">
          <span class="icon is-small mr-2">
            <i class="fas fa-exclamation-triangle"></i>
          </span>
          {{ titulo }}
        </p>
        <button class="delete" aria-label="close" @click="$emit('fechar')"></button>
      </header>
      
      <section class="modal-card-body">
        <div class="content">
          <p class="is-size-5">{{ mensagem }}</p>
        </div>
      </section>
      
      <footer class="modal-card-foot is-justify-content-end">
        <button class="button" @click="$emit('fechar')" :disabled="isLoading">
          Cancelar
        </button>
        <button 
          class="button is-danger" 
          :class="{ 'is-loading': isLoading }" 
          @click="$emit('confirmar')"
          type="button"
        >
          <span class="icon is-small">
            <i class="fas fa-trash"></i>
          </span>
          <span>{{ textoBotaoConfirmar }}</span>
        </button>
      </footer>
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps({
  ativo: { type: Boolean, required: true },
  titulo: { type: String, default: 'Confirmar exclusão' },
  mensagem: { type: String, required: true },
  textoBotaoConfirmar: { type: String, default: 'Sim, excluir' },
  isLoading: { type: Boolean, default: false }
});

defineEmits(['fechar', 'confirmar']);
</script>