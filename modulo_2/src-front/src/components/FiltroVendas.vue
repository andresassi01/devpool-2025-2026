<template>
  <div class="box has-background-light">
    <div class="columns is-multiline">
      <div class="column is-4">
        <div class="field">
          <label class="label">Nome do Cliente</label>
          <div class="control has-icons-left">
            <input 
              v-model="filtros.cliente" 
              class="input" 
              type="text" 
              placeholder="Ex: João Silva..." 
              @keyup.enter="emitirPesquisa"
            >
            <span class="icon is-small is-left">
              <i class="fas fa-user"></i>
            </span>
          </div>
        </div>
      </div>
      <div class="column is-2">
        <div class="field">
          <label class="label">Data Início</label>
          <div class="control">
            <input 
              v-model="filtros.dataInicio" 
              class="input" 
              :class="{'is-danger': erroData}" 
              type="date"
            >
          </div>
        </div>
      </div>
      <div class="column is-2">
        <div class="field">
          <label class="label">Data Fim</label>
          <div class="control">
            <input 
              v-model="filtros.dataFim" 
              class="input" 
              :class="{'is-danger': erroData}" 
              type="date"
            >
          </div>
        </div>
      </div>
      <div class="column is-4">
        <div class="field">
          <label class="label">Ordenar por</label>
          <div class="control">
            <div class="select is-fullwidth">
              <select v-model="filtros.ordem" @change="emitirPesquisa">
                <option value="dataVenda">Data da Venda</option>
                <option value="totalComDesconto">Maior Valor Total</option>
              </select>
            </div>
          </div>
        </div>
      </div>
    </div>

    <p v-if="erroData" class="help is-danger mb-3">
      <i class="fas fa-exclamation-triangle mr-1"></i>
      Para filtrar por data, preencha o início e o fim corretamente.
    </p>

    <div class="field is-grouped is-grouped-right mt-4">
      <p class="control">
        <button class="button is-light" @click="limparFiltros" :disabled="isLoading">
          <span class="icon"><i class="fas fa-eraser"></i></span>
          <span>Limpar</span>
        </button>
      </p>
      <p class="control">
        <button 
          class="button is-primary" 
          @click="emitirPesquisa" 
          :class="{ 'is-loading': isLoading }"
          :disabled="erroData"
        >
          <span class="icon"><i class="fas fa-search"></i></span>
          <span>Filtrar</span>
        </button>
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref, watch, onMounted } from 'vue';
import { useRoute, useRouter, onBeforeRouteLeave } from 'vue-router';

const props = defineProps({ isLoading: Boolean });
const emit = defineEmits(['pesquisar', 'limpar']);

const route = useRoute();
const router = useRouter();
const erroData = ref(false);

const filtros = reactive({
  cliente: '',
  dataInicio: '',
  dataFim: '',
  ordem: 'dataVenda'
});

onMounted(() => {
  // Recupera o cache se existir
  const salvo = sessionStorage.getItem('cache_filtros_vendas');
  
  if (salvo) {
    Object.assign(filtros, JSON.parse(salvo));
    // Limpamos o cache imediatamente após ler. 
    // Assim, se o usuário fechar a aba ou mudar de menu agora, o filtro não volta.
    sessionStorage.removeItem('cache_filtros_vendas');
    emitirPesquisa();
  }

  // Lógica de reset por query string (mantida por segurança)
  if (route.query.reset === 'true') {
    limparFiltros();
    router.replace({ query: {} });
  }
});

// A MÁGICA REVISADA: Agora ele aceita qualquer destino dentro de /vendas
onBeforeRouteLeave((to) => {
  // Se o destino começar com /vendas, ele mantém o filtro (cobre form, novo, edicao, etc)
  const permanecendoNoModuloVendas = to.path.startsWith('/vendas');
  
  // Se estivermos saindo da listagem mas ficando no módulo (indo pro form), salva.
  if (permanecendoNoModuloVendas) {
    sessionStorage.setItem('cache_filtros_vendas', JSON.stringify(filtros));
  } else {
    // Se for para /dashboard, /produtos, etc., mata o filtro.
    sessionStorage.removeItem('cache_filtros_vendas');
  }
});

const validarDatas = () => {
  const { dataInicio, dataFim } = filtros;
  if (!dataInicio && !dataFim) {
    erroData.value = false;
    return true;
  }
  if ((dataInicio && !dataFim) || (!dataInicio && dataFim)) {
    erroData.value = true;
    return false;
  }
  erroData.value = false;
  return true;
};

watch(() => filtros.dataInicio, (novaDataInicio) => {
  if (novaDataInicio && filtros.dataFim && new Date(novaDataInicio) > new Date(filtros.dataFim)) {
    filtros.dataFim = novaDataInicio;
  }
  validarDatas();
});

watch(() => filtros.dataFim, (novaDataFim) => {
  if (novaDataFim && filtros.dataInicio && new Date(novaDataFim) < new Date(filtros.dataInicio)) {
    filtros.dataInicio = novaDataFim;
  }
  validarDatas();
});

const emitirPesquisa = () => {
  if (validarDatas()) {
    emit('pesquisar', { ...filtros });
  }
};

const limparFiltros = () => {
  Object.assign(filtros, {
    cliente: '',
    dataInicio: '',
    dataFim: '',
    ordem: 'dataVenda'
  });
  erroData.value = false;
  sessionStorage.removeItem('cache_filtros_vendas');
  emit('limpar');
};
</script>