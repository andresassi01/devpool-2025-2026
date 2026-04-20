<template>
  <BarraDeNavegacao />
  <section class="section">
    <div class="container">
      <div class="level mb-5">
        <div class="level-left">
          <h1 class="title">Gestão de Vendas</h1>
        </div>
        <div class="level-right">
          <button v-if="idsSelecionados.length > 0" class="button is-danger is-outlined mr-2"
            @click="abrirModalExclusaoLote">
            <span class="icon"><i class="fas fa-trash-alt"></i></span>
            <span>Excluir ({{ idsSelecionados.length }})</span>
          </button>
          <router-link to="/vendas/novo" class="button is-primary">
            <span class="icon"><i class="fas fa-plus"></i></span>
            <span>Nova Venda</span>
          </router-link>

        </div>
      </div>

      <FiltroVendas :is-loading="loading" @pesquisar="handlePesquisa" @limpar="handleLimpar" />

      <ListagemVendas :vendas="vendas.data || []" :is-loading="loading" @visualizar="irParaEdicao"
        @excluir="abrirModalExclusao" @selecao-alterada="ids => idsSelecionados = ids" />

      <Paginacao v-if="vendas.data && vendas.data.length > 0" :pagina-atual="vendas.pagina || 1"
        :tem-mais="vendas.temMais || false" :is-loading="loading" @mudar-pagina="trocarPagina" />
    </div>

    <FeedbackNotificacao :ativo="notificacao.ativo" :mensagem="notificacao.mensagem" :tipo="notificacao.tipo"
      @fechar="notificacao.ativo = false" />

    <ConfirmarExclusao :ativo="modalExclusao.ativo" :titulo="modalExclusao.titulo" :mensagem="modalExclusao.mensagem"
      :is-loading="loading" @fechar="modalExclusao.ativo = false" @confirmar="executarExclusao" />
  </section>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useVendas } from '../composables/useVendas';
import BarraDeNavegacao from '../layout/BarraDeNavegacao.vue';
import FiltroVendas from '../components/FiltroVendas.vue';
import ListagemVendas from '../components/ListagemVendas.vue';
import Paginacao from '../components/Paginacao.vue';
import FeedbackNotificacao from '../components/FeedbackNotificacao.vue';
import ConfirmarExclusao from '../components/ConfirmarExclusao.vue';

const { vendas, loading, filtros, buscarVendas, trocarPagina, excluirVenda } = useVendas();
const router = useRouter();
const idsSelecionados = ref<number[]>([]);

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

const notificacao = ref({
  ativo: false,
  mensagem: '',
  tipo: 'sucesso'
});

const dispararNotificacao = (msg: string, tipo = 'sucesso') => {
  notificacao.value = { ativo: true, mensagem: msg, tipo };
  setTimeout(() => notificacao.value.ativo = false, 3500);
};

// Estados para o Modal de Exclusão
const modalExclusao = ref({
  ativo: false,
  titulo: '',
  mensagem: '',
  idParaExcluir: null as number | null,
  isLote: false
});

// Função que abre o modal para exclusão individual
const abrirModalExclusao = (id: number) => {
  modalExclusao.value = {
    ativo: true,
    titulo: 'Confirmar Exclusão',
    mensagem: `Tem certeza que deseja excluir a venda #${id}?`,
    idParaExcluir: id,
    isLote: false
  };
};

// Função que abre o modal para exclusão em lote
const abrirModalExclusaoLote = () => {
  const total = idsSelecionados.value.length;
  modalExclusao.value = {
    ativo: true,
    titulo: 'Excluir em Lote',
    mensagem: `Você selecionou ${total} vendas. Deseja excluir todas permanentemente?`,
    idParaExcluir: null,
    isLote: true
  };
};

const executarExclusao = async () => {
  loading.value = true;
  try {
    if (modalExclusao.value.isLote) {
      let sucessos = 0;
      for (const id of idsSelecionados.value) {
        // Mudamos de "const ok = await..." para apenas await
        await excluirVenda(id, false); 
        sucessos++;
      }
      dispararNotificacao(`${sucessos} vendas excluídas!`);
      idsSelecionados.value = [];
    } else if (modalExclusao.value.idParaExcluir) {
      await excluirVenda(modalExclusao.value.idParaExcluir, false);
      dispararNotificacao("Venda excluída!");
    }
    await buscarVendas();
  } catch (error) {
    dispararNotificacao("Erro na exclusão", "erro");
  } finally {
    loading.value = false;
    modalExclusao.value.ativo = false;
  }
};

onMounted(buscarVendas);
</script>