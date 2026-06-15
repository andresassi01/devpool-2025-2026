<template>
  <BarraDeNavegacao />
  <section class="section">
    <div class="container">
      <div class="level mb-5">
        <div class="level-left">
          <h1 class="title">Gestão de Clientes</h1>
        </div>
        <div class="level-right">
          <button v-if="idsSelecionados.length > 0" class="button is-danger is-outlined mr-2" @click="abrirModalExclusaoLote">
            <span class="icon"><i class="fas fa-trash-alt"></i></span>
            <span>Excluir ({{ idsSelecionados.length }})</span>
          </button>
          <router-link to="/clientes/novo" class="button is-primary">
            <span class="icon"><i class="fas fa-plus"></i></span>
            <span>Novo Cliente</span>
          </router-link>
        </div>
      </div>

      <FiltroClientes :is-loading="loading" @pesquisar="handlePesquisa" @limpar="handleLimpar" />

      <ListagemClientes 
        :clientes="clientes.data || []" 
        :is-loading="loading" 
        @visualizar="irParaEdicao"
        @excluir="abrirModalExclusao" 
      />

      <Paginacao 
        v-if="clientes.data && clientes.data.length > 0" 
        :pagina-atual="clientes.pagina || 1"
        :tem-mais="clientes.temMais || false" 
        :is-loading="loading" 
        @mudar-pagina="trocarPagina" 
      />
    </div>

    <FeedbackNotificacao :ativo="notificacao.ativo" :mensagem="notificacao.mensagem" :tipo="notificacao.tipo" @fechar="notificacao.ativo = false" />
    <ConfirmarExclusao 
      :ativo="modalExclusao.ativo" 
      :titulo="modalExclusao.titulo" 
      :mensagem="modalExclusao.mensagem"
      :is-loading="loading" 
      @fechar="modalExclusao.ativo = false" 
      @confirmar="executarExclusao" 
    />
  </section>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useClientes } from '../composables/useClientes'; 
import BarraDeNavegacao from '../layout/BarraDeNavegacao.vue';
import FiltroClientes from '../components/FiltroClientes.vue';
import ListagemClientes from '../components/ListagemClientes.vue';
import Paginacao from '../components/Paginacao.vue';
import FeedbackNotificacao from '../components/FeedbackNotificacao.vue';
import ConfirmarExclusao from '../components/ConfirmarExclusao.vue';

const { clientes, loading, filtros, buscarClientes, trocarPagina, excluirCliente } = useClientes();
const router = useRouter();
const idsSelecionados = ref<number[]>([]);

const handlePesquisa = (novosFiltros: any) => {
  Object.assign(filtros.value, novosFiltros);
  clientes.value.pagina = 1;
  buscarClientes();
};

const handleLimpar = () => {
  filtros.value = { nome: '', ordem: 'nome' }; // Ajuste conforme seu filtro
  clientes.value.pagina = 1;
  buscarClientes();
};

const irParaEdicao = (id: number) => router.push(`/clientes/editar/${id}`);

const notificacao = ref({ ativo: false, mensagem: '', tipo: 'sucesso' });
const dispararNotificacao = (msg: string, tipo = 'sucesso') => {
  notificacao.value = { ativo: true, mensagem: msg, tipo };
  setTimeout(() => notificacao.value.ativo = false, 3500);
};

const modalExclusao = ref({ ativo: false, titulo: '', mensagem: '', idParaExcluir: null as number | null, isLote: false });

const abrirModalExclusao = (id: number) => {
  modalExclusao.value = { ativo: true, titulo: 'Confirmar Exclusão', mensagem: `Tem certeza que deseja excluir o cliente #${id}?`, idParaExcluir: id, isLote: false };
};

const abrirModalExclusaoLote = () => {
  modalExclusao.value = { ativo: true, titulo: 'Excluir em Lote', mensagem: `Você selecionou ${idsSelecionados.value.length} clientes. Deseja excluir todos?`, idParaExcluir: null, isLote: true };
};

const executarExclusao = async () => {
  loading.value = true;
  try {
    if (modalExclusao.value.isLote) {
      for (const id of idsSelecionados.value) await excluirCliente(id);
      dispararNotificacao("Clientes excluídos!");
      idsSelecionados.value = [];
    } else if (modalExclusao.value.idParaExcluir) {
      await excluirCliente(modalExclusao.value.idParaExcluir);
      dispararNotificacao("Cliente excluído!");
    }
    await buscarClientes();
  } catch (error) {
    dispararNotificacao("Erro na exclusão", "erro");
  } finally {
    loading.value = false;
    modalExclusao.value.ativo = false;
  }
};

onMounted(buscarClientes);
</script>