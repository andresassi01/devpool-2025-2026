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
import { reactive, ref, watch } from 'vue';

const props = defineProps({ isLoading: Boolean });
const emit = defineEmits(['pesquisar', 'limpar']);

const erroData = ref(false);

const filtros = reactive({
  cliente: '',
  dataInicio: '',
  dataFim: '',
  ordem: 'dataVenda'
});

// Garante preenchimento de ambos os campos
const validarDatas = () => {
  const { dataInicio, dataFim } = filtros;
  
  // Se ambos vazios, ok
  if (!dataInicio && !dataFim) {
    erroData.value = false;
    return true;
  }

  // Se um existe e o outro não, erro
  if ((dataInicio && !dataFim) || (!dataInicio && dataFim)) {
    erroData.value = true;
    return false;
  }

  erroData.value = false;
  return true;
};

// FUNCIONALIDADE IGUAL AO FILTRO PRODUTOS:
// Watcher para Data Início: Se ficar maior que a Fim, empurra a Fim para frente
watch(() => filtros.dataInicio, (novaDataInicio) => {
  if (novaDataInicio && filtros.dataFim && new Date(novaDataInicio) > new Date(filtros.dataFim)) {
    filtros.dataFim = novaDataInicio;
  }
  validarDatas();
});

// Watcher para Data Fim: Se ficar menor que a Início, puxa a Início para trás
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
  // Resetamos todas as propriedades do objeto reactive de uma só vez
  Object.assign(filtros, {
    cliente: '',
    dataInicio: '',
    dataFim: '',
    ordem: 'dataVenda'
  });

  // Forçamos o estado do erro para falso
  erroData.value = false;

  // Avisamos o componente pai para recarregar a lista sem filtros
  emit('limpar');
};
</script>