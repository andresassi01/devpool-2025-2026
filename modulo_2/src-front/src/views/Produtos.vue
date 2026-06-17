<template>
  <BarraDeNavegacao />
  <section class="section">
    <div class="container">
      <h1 class="title mb-5">Gestão de Produtos</h1>

      <FiltroProdutos :is-loading="carregando" :filtros-pai="filtrosAtivos" @pesquisar="handlePesquisa"
        @limpar="handleLimpar" />

      <AcoesProdutos :quantidade-selecionados="produtosSelecionados.length"
        :modo-lixeira="filtrosAtivos.situacao === '4'" @excluir-massa="prepararExclusaoMassa"
        @restaurar-massa="prepararRestauracaoMassa" @incluir="irParaInclusao" />

      <FeedbackNotificacao :ativo="erro" :mensagem="mensagemErro" tipo="erro" @fechar="erro = false" />

      <ListagemProdutos :produtos="produtos" :is-loading="carregando" :selecionados="produtosSelecionados"
        :selecionou-todos="selecionouTodos" :dropdown-aberto="dropdownAberto" @editar="irParaEdicao"
        @excluir="prepararExclusaoIndividual" @restaurar="prepararRestauracaoIndividual" @toggle-todos="alternarTodos" @toggle-dropdown="alternarDropdown"
        @update:selecionados="handleUpdateSelecionados" />

      <Paginacao :pagina-atual="pagina" :tem-mais="temMaisPaginas" :is-loading="carregando"
        @mudar-pagina="trocarPagina" />
    </div>
  </section>

  <ConfirmarExclusao :ativo="modalAtivo" :mensagem="mensagemModal" :is-loading="carregando"
    :texto-botao-confirmar="tipoAcao === 'restaurar' ? 'Sim, restaurar' : 'Sim, excluir'" @fechar="fecharModal" @confirmar="confirmarAcaoModal" />

  <FeedbackNotificacao :ativo="mostrarFeedback" :mensagem="mensagemFeedback" :tipo="tipoFeedback"
    @fechar="mostrarFeedback = false" />
</template>

<script setup lang="ts">
import { onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { useProdutos } from '../composables/useProdutos';

// Componentes
import Paginacao from '../components/Paginacao.vue';
import FiltroProdutos from '../components/FiltroProdutos.vue';
import AcoesProdutos from '../components/AcoesProdutos.vue';
import ListagemProdutos from '../components/ListagemProdutos.vue';
import FeedbackNotificacao from '../components/FeedbackNotificacao.vue';
import BarraDeNavegacao from '../layout/BarraDeNavegacao.vue';
import ConfirmarExclusao from '../components/ConfirmarExclusao.vue';

const router = useRouter();
const {
  produtos, carregando, erro, mensagemErro, pagina, temMaisPaginas,
  produtosSelecionados, dropdownAberto, filtrosAtivos, modalAtivo,
  mostrarFeedback, mensagemFeedback, tipoFeedback, mensagemModal, selecionouTodos, tipoAcao,
  buscarProdutos, handlePesquisa, handleLimpar, trocarPagina, alternarTodos,
  handleUpdateSelecionados, alternarDropdown, fecharDropdownExterno,
  fecharModal, confirmarAcaoModal, prepararExclusaoIndividual, prepararExclusaoMassa, prepararRestauracaoIndividual, prepararRestauracaoMassa
} = useProdutos();

const irParaInclusao = () => router.push('/produtos/novo');
const irParaEdicao = (id: number) => router.push(`/produtos/editar/${id}`);

onMounted(() => {
  window.addEventListener('click', fecharDropdownExterno);
  window.scrollTo(0, 0);

  const estadoSalvo = sessionStorage.getItem('ultimo_estado_filtro');
  if (estadoSalvo) {
    try {
      const { filtros, pagina: paginaSalva } = JSON.parse(estadoSalvo);
      Object.assign(filtrosAtivos, filtros);
      pagina.value = paginaSalva || 1;
    } catch (e) {
      sessionStorage.removeItem('ultimo_estado_filtro');
    }
  }
  buscarProdutos();
});

onUnmounted(() => {
  window.removeEventListener('click', fecharDropdownExterno);
});
</script>