<template>
  <BarraDeNavegacao />
  <section class="section">
    <div class="container">
      <div class="level mb-5">
        <div class="level-left">
          <h1 class="title">Gestão de Vendas</h1>
        </div>
        <div class="level-right">
          <router-link to="/vendas/novo" class="button is-primary">
            <span class="icon"><i class="fas fa-plus"></i></span>
            <span>Nova Venda</span>
          </router-link>
        </div>
      </div>

      <FiltroVendas :is-loading="loading" @pesquisar="handlePesquisa" @limpar="handleLimpar" />

      <ListagemVendas :vendas="vendas.data || []" :is-loading="loading" @visualizar="irParaDetalhes" />

      <Paginacao v-if="vendas.data && vendas.data.length > 0" :pagina-atual="vendas.pagina || 1"
        :tem-mais="vendas.temMais || false" :is-loading="loading" @mudar-pagina="trocarPagina" />
    </div>
  </section>
</template>

<script setup lang="ts">
import { onMounted } from 'vue';
import { useVendas } from '../composables/useVendas';
import BarraDeNavegacao from '../layout/BarraDeNavegacao.vue';
import FiltroVendas from '../components/FiltroVendas.vue';
import ListagemVendas from '../components/ListagemVendas.vue';
import Paginacao from '../components/Paginacao.vue';

const { vendas, loading, filtros, buscarVendas, trocarPagina } = useVendas();

const handlePesquisa = (novosFiltros: any) => {
  Object.assign(filtros.value, novosFiltros);
  buscarVendas();
};

const handleLimpar = () => {
  buscarVendas();
};

const irParaDetalhes = (id: number) => {
  console.log('Ver itens da venda:', id);
};

onMounted(buscarVendas);
</script>