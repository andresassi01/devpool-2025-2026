<template>
  <div class="box has-background-light">
    <div class="columns">
      <div class="column is-6">
        <div class="field">
          <label class="label">Nome do Produto</label>
          <div class="control">
            <input v-model="filtros.nome" class="input" type="text" placeholder="Ex: Camiseta..." @keyup.enter="emitirPesquisa">
          </div>
        </div>
      </div>
      <div class="column is-6">
        <div class="field">
          <label class="label">SKU (Código)</label>
          <div class="control">
            <input v-model="filtros.sku" class="input" type="text" placeholder="Ex: CAM-001" @keyup.enter="emitirPesquisa">
          </div>
        </div>
      </div>
    </div>

    <div class="columns mb-0"> <div class="column is-3">
        <div class="field">
          <label class="label">Data Alteração (Início)</label>
          <div class="control">
            <input v-model="filtros.dataInicio" class="input" :class="{'is-danger': erroData}" type="date">
          </div>
        </div>
      </div>
      <div class="column is-3">
        <div class="field">
          <label class="label">Data Alteração (Fim)</label>
          <div class="control">
            <input v-model="filtros.dataFim" class="input" :class="{'is-danger': erroData}" type="date">
          </div>
        </div>
      </div>
      <div class="column is-6">
        <div class="field">
          <label class="label">Critério / Situação</label>
          <div class="control">
            <div class="select is-fullwidth">
              <select v-model="filtros.situacao">
                <option value="1">Últimos incluídos</option>
                <option value="2">Ativos</option>
                <option value="3">Inativos</option>
                <option value="4">Excluídos</option>
                <option value="5">Todos</option>
              </select>
            </div>
          </div>
        </div>
      </div>
    </div>

    <p v-if="erroData" class="help is-danger animate__animated animate__fadeIn">
      * Informe o período completo (Início e Fim) para filtrar por data.
    </p>

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
          <span>Pesquisar</span>
        </button>
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, watch, ref } from 'vue';

const props = defineProps({
  isLoading: Boolean,
  filtrosPai: Object
});

const emit = defineEmits(['pesquisar', 'limpar']);
const erroData = ref(false); 

const filtros = reactive({
  nome: '',
  sku: '',
  dataInicio: '',
  dataFim: '',
  situacao: '1'
});

watch(() => props.filtrosPai, (novoEstado) => {
  if (novoEstado) {
    Object.assign(filtros, novoEstado);
  }
}, { immediate: true, deep: true });

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

const validarDatas = () => {
  const inicio = !!filtros.dataInicio;
  const fim = !!filtros.dataFim;
  erroData.value = (inicio !== fim);
  return !erroData.value;
};

const emitirPesquisa = () => {
  if (validarDatas()) {
    emit('pesquisar', { ...filtros });
  }
};

const limparFiltros = () => {
  filtros.nome = '';
  filtros.sku = '';
  filtros.dataInicio = '';
  filtros.dataFim = '';
  filtros.situacao = '1';
  erroData.value = false;
  emit('limpar');
};
</script>