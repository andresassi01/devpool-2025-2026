<template>
  <div class="box has-background-light">
    <div class="columns is-multiline">
      <div class="column is-4">
        <div class="field">
          <label class="label">Nome do Cliente</label>
          <div class="control">
            <input v-model="filtros.cliente" class="input" type="text" placeholder="Ex: João Silva..." @keyup.enter="emitirPesquisa">
          </div>
        </div>
      </div>
      <div class="column is-2">
        <div class="field">
          <label class="label">Data Início</label>
          <div class="control">
            <input v-model="filtros.dataInicio" class="input" type="date">
          </div>
        </div>
      </div>
      <div class="column is-2">
        <div class="field">
          <label class="label">Data Fim</label>
          <div class="control">
            <input v-model="filtros.dataFim" class="input" type="date">
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
                <option value="totalComDesconto">Valor Total</option>
              </select>
            </div>
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
import { reactive } from 'vue';

const props = defineProps({ isLoading: Boolean });
const emit = defineEmits(['pesquisar', 'limpar']);

const filtros = reactive({
  cliente: '',
  dataInicio: '',
  dataFim: '',
  ordem: 'dataVenda'
});

const emitirPesquisa = () => emit('pesquisar', { ...filtros });
const limparFiltros = () => {
  filtros.cliente = '';
  filtros.dataInicio = '';
  filtros.dataFim = '';
  filtros.ordem = 'dataVenda';
  emit('limpar');
};
</script>