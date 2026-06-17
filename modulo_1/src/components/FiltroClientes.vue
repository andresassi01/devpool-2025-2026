<template>
  <div class="box has-background-light">
    <div class="columns is-multiline">
      <div class="column is-12">
        <div class="field">
          <label class="label">Nome do Cliente</label>
          <div class="control has-icons-left">
            <input 
              v-model="filtros.nome" 
              class="input" 
              type="text" 
              placeholder="Ex: João Silva..." 
              @keyup.enter="emitirPesquisa"
            >
            <span class="icon is-small is-left"><i class="fas fa-user"></i></span>
          </div>
        </div>
      </div>
    </div>

    <div class="field is-grouped is-grouped-right mt-4">
      <p class="control">
        <button class="button is-light" @click="limparFiltros" :disabled="isLoading">
          <span class="icon"><i class="fas fa-eraser"></i></span>
          <span>Limpar</span>
        </button>
      </p>
      <p class="control">
        <button class="button is-primary" @click="emitirPesquisa" :class="{ 'is-loading': isLoading }">
          <span class="icon"><i class="fas fa-search"></i></span>
          <span>Filtrar</span>
        </button>
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, onMounted } from 'vue';
import { useRoute, useRouter, onBeforeRouteLeave } from 'vue-router';

const props = defineProps({ isLoading: Boolean });
const emit = defineEmits(['pesquisar', 'limpar']);

const route = useRoute();
const router = useRouter();

// Filtro simplificado apenas com nome
const filtros = reactive({ nome: '' });

onMounted(() => {
  // Mantemos o cache para quando o usuário sair e voltar para a lista
  const salvo = sessionStorage.getItem('cache_filtros_clientes');
  if (salvo) {
    Object.assign(filtros, JSON.parse(salvo));
    sessionStorage.removeItem('cache_filtros_clientes');
    emitirPesquisa();
  }

  if (route.query.reset === 'true') {
    limparFiltros();
    router.replace({ query: {} });
  }
});

onBeforeRouteLeave((to) => {
  // Só mantém o cache se navegar para dentro do mesmo módulo
  const permanecendoNoModuloClientes = to.path.startsWith('/clientes');
  if (permanecendoNoModuloClientes) {
    sessionStorage.setItem('cache_filtros_clientes', JSON.stringify(filtros));
  } else {
    sessionStorage.removeItem('cache_filtros_clientes');
  }
});

const emitirPesquisa = () => {
  emit('pesquisar', { ...filtros });
};

const limparFiltros = () => {
  filtros.nome = '';
  sessionStorage.removeItem('cache_filtros_clientes');
  emit('limpar');
};
</script>