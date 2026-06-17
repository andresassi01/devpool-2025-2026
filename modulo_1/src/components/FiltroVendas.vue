<template>
  <div class="box has-background-light">
    <div class="columns is-multiline">
      <div class="column is-4">
        <div class="field">
          <label class="label">Nome do Cliente</label>
          <div class="control has-icons-left">
            <input v-model="filtros.cliente" class="input" type="text" placeholder="Ex: João Silva..." @keyup.enter="emitirPesquisa">
            <span class="icon is-small is-left"><i class="fas fa-user"></i></span>
          </div>
        </div>
      </div>
      <div class="column is-2">
        <div class="field">
          <label class="label">Data Início</label>
          <div class="control">
            <input v-model="filtros.dataInicio" class="input" :class="{'is-danger': erroData}" type="date">
          </div>
        </div>
      </div>
      <div class="column is-2">
        <div class="field">
          <label class="label">Data Fim</label>
          <div class="control">
            <input v-model="filtros.dataFim" class="input" :class="{'is-danger': erroData}" type="date">
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
        <button class="button is-primary" @click="emitirPesquisa" :class="{ 'is-loading': isLoading }" :disabled="erroData">
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

const filtros = reactive({ cliente: '', dataInicio: '', dataFim: '', ordem: 'dataVenda' });

onMounted(() => {
  const salvo = sessionStorage.getItem('cache_filtros_vendas');
  if (salvo) {
    Object.assign(filtros, JSON.parse(salvo));
    sessionStorage.removeItem('cache_filtros_vendas');
    emitirPesquisa();
  }

  if (route.query.reset === 'true') {
    limparFiltros();
    router.replace({ query: {} });
  }
});

onBeforeRouteLeave((to) => {
  const permanecendoNoModuloVendas = to.path.startsWith('/vendas');
  if (permanecendoNoModuloVendas) sessionStorage.setItem('cache_filtros_vendas', JSON.stringify(filtros));
  else sessionStorage.removeItem('cache_filtros_vendas');
});

const validarDatas = () => {
  const { dataInicio, dataFim } = filtros;
  if (!dataInicio && !dataFim) return (erroData.value = false, true);
  if ((dataInicio && !dataFim) || (!dataInicio && dataFim)) return (erroData.value = true, false);
  return (erroData.value = false, true);
};

watch(() => filtros.dataInicio, (nova) => {
  if (nova && filtros.dataFim && new Date(nova) > new Date(filtros.dataFim)) filtros.dataFim = nova;
  validarDatas();
});

watch(() => filtros.dataFim, (nova) => {
  if (nova && filtros.dataInicio && new Date(nova) < new Date(filtros.dataInicio)) filtros.dataInicio = nova;
  validarDatas();
});

const emitirPesquisa = () => { if (validarDatas()) emit('pesquisar', { ...filtros }); };

const limparFiltros = () => {
  Object.assign(filtros, { cliente: '', dataInicio: '', dataFim: '', ordem: 'dataVenda' });
  erroData.value = false;
  sessionStorage.removeItem('cache_filtros_vendas');
  emit('limpar');
};
</script>