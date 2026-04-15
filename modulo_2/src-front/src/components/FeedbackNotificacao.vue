<template>
  <transition name="fade">
    <div v-if="ativo" :class="['notification-toast', tipo === 'sucesso' ? 'is-success' : 'is-danger']" class="notification">
      <button class="delete" @click="$emit('fechar')"></button>
      <div class="is-flex is-align-items-center">
        <span class="icon mr-2">
          <i :class="tipo === 'sucesso' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle'"></i>
        </span>
        <p><strong>{{ mensagem }}</strong></p>
      </div>
    </div>
  </transition>
</template>

<script setup lang="ts">
defineProps({
  ativo: { type: Boolean, required: true },
  mensagem: { type: String, required: true },
  tipo: { type: String, default: 'sucesso' }
});
defineEmits(['fechar']);
</script>

<style scoped>
.notification-toast {
  position: fixed;
  top: 100px; 
  right: 20px;
  z-index: 9999;
  min-width: 300px;
  max-width: 450px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.fade-enter-active, .fade-leave-active {
  transition: all 0.4s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
  transform: translateX(50px);
}
</style>