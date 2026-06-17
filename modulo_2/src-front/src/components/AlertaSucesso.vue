<template>
  <div class="modal" :class="{ 'is-active': exibir }">
    <div class="modal-background" @click="$emit('fechar')"></div>
    <div class="modal-content">
      <div class="box has-text-centered p-6">
        <span class="icon is-large has-text-success mb-6">
          <i class="fas fa-check-circle fa-4x"></i>
        </span>
        <h3 class="title is-4 mt-4">Mensagem Enviada!</h3>
        <p class="subtitle is-6 mb-5">
          Recebemos seus dados. <br>
          Nossa equipe entrará em contato em breve.
        </p>
        <button class="button is-success is-fullwidth is-medium" @click="$emit('fechar')">
          FECHAR
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{ exibir: boolean }>();
defineEmits(['fechar']);
</script>

<style scoped>
.modal-content {
  max-width: 420px;
  padding: 1.5rem;
}
.box {
  border-radius: 20px;
  border: none;
}
</style>