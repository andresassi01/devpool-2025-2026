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

      <ListagemVendas :vendas="vendas.data || []" :is-loading="loading" @visualizar="irParaEdicao"
        @excluir="excluirVenda" />

      <Paginacao v-if="vendas.data && vendas.data.length > 0" :pagina-atual="vendas.pagina || 1"
        :tem-mais="vendas.temMais || false" :is-loading="loading" @mudar-pagina="trocarPagina" />
    </div>
  </section>
</template>

<script setup lang="ts">
import { onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useVendas } from '../composables/useVendas';
import BarraDeNavegacao from '../layout/BarraDeNavegacao.vue';
import FiltroVendas from '../components/FiltroVendas.vue';
import ListagemVendas from '../components/ListagemVendas.vue';
import Paginacao from '../components/Paginacao.vue';

const { vendas, loading, filtros, buscarVendas, trocarPagina, excluirVenda } = useVendas();
const router = useRouter();

const handlePesquisa = (novosFiltros: any) => {
  Object.assign(filtros.value, novosFiltros);
  vendas.value.pagina = 1;
  buscarVendas();
};

const handleLimpar = () => {
  filtros.value = {
    cliente: '',
    dataInicio: '',
    dataFim: '',
    ordem: 'dataVenda'
  };
  vendas.value.pagina = 1;
  buscarVendas();
};

const irParaEdicao = (id: number) => {
  // Redireciona para a tela de cadastro, mas passando o ID
  router.push(`/vendas/editar/${id}`);
};

onMounted(buscarVendas);
</script>