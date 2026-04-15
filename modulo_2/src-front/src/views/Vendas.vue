<template>
  <div class="container mt-5">
    <div class="level">
      <div class="level-left">
        <h1 class="title">Vendas</h1>
      </div>
      <div class="level-right">
        <router-link to="/vendas/novo" class="button is-primary">
          Nova Venda
        </router-link>
      </div>
    </div>

    <div class="box">
      <div class="columns">
        <div class="column is-4">
          <input v-model="filtros.cliente" class="input" type="text" placeholder="Buscar por cliente...">
        </div>
        <div class="column is-3">
          <input v-model="filtros.dataInicio" class="input" type="date">
        </div>
        <div class="column is-3">
          <input v-model="filtros.dataFim" class="input" type="date">
        </div>
        <div class="column is-2">
          <button class="button is-link is-fullwidth" @click="buscarVendas">
            Filtrar
          </button>
        </div>
      </div>
    </div>

    <table class="table is-fullwidth is-striped is-hoverable">
      <thead>
        <tr>
          <th>Número</th>
          <th>Cliente</th>
          <th>Data</th>
          <th>Total</th>
          <th>Situação</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="venda in vendas" :key="venda.id">
          <td>{{ venda.numero }}</td>
          <td>{{ venda.nomeCliente }}</td>
          <td>{{ new Date(venda.dataVenda).toLocaleDateString('pt-BR') }}</td>
          <td>R$ {{ Number(venda.totalComDesconto).toFixed(2) }}</td>
          <td><span class="tag is-info">{{ venda.situacao }}</span></td>
          <td>
            <button class="button is-small is-light">Ver itens</button>
          </td>
        </tr>
        <tr v-if="vendas.length === 0 && !loading">
          <td colspan="6" class="has-text-centered">Nenhuma venda encontrada.</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue';
import { useVendas } from '../composables/useVendas';

const { vendas, loading, filtros, buscarVendas } = useVendas();

onMounted(buscarVendas);
</script>