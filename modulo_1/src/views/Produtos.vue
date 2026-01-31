<template>
  <BarraDeNavegacao />
  <section class="section">
    <div class="container">

      <h1 class="title mb-5">Gestão de Produtos</h1>

      <FiltroProdutos :is-loading="carregando" @pesquisar="handlePesquisa" @limpar="handleLimpar" />

      <AcoesProdutos :quantidade-selecionados="produtosSelecionados.length" @excluir-massa="prepararExclusaoMassa"
        @incluir="irParaInclusao" />

      <FeedbackNotificacao :ativo="erro" :mensagem="mensagemErro" tipo="erro" @fechar="erro = false" />

      <ListagemProdutos :produtos="produtos" :is-loading="carregando" :selecionados="produtosSelecionados"
        :selecionou-todos="selecionouTodos" :dropdown-aberto="dropdownAberto" @editar="irParaEdicao"
        @excluir="prepararExclusaoIndividual" @toggle-todos="alternarTodos" @toggle-dropdown="alternarDropdown"
        @update:selecionados="handleUpdateSelecionados" />

      <Paginacao :pagina-atual="pagina" :tem-mais="temMaisPaginas" :is-loading="carregando"
        @mudar-pagina="trocarPagina" />
    </div>
  </section>
  <ConfirmarExclusao :ativo="modalAtivo" :mensagem="mensagemModal" :is-loading="carregando"
    texto-botao-confirmar="Sim, excluir" @fechar="fecharModal" @confirmar="confirmarAcaoModal" />
  <FeedbackNotificacao :ativo="mostrarFeedback" :mensagem="mensagemFeedback" :tipo="tipoFeedback"
    @fechar="mostrarFeedback = false" />
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, reactive, computed, nextTick } from 'vue';
import { useRouter } from 'vue-router';

import Paginacao from '../components/Paginacao.vue';
import FiltroProdutos from '../components/FiltroProdutos.vue';
import AcoesProdutos from '../components/AcoesProdutos.vue';
import ListagemProdutos from '../components/ListagemProdutos.vue';
import FeedbackNotificacao from '../components/FeedbackNotificacao.vue';
import BarraDeNavegacao from '../layout/BarraDeNavegacao.vue';
import ConfirmarExclusao from '../components/ConfirmarExclusao.vue';

interface Produto {
  id: number;
  codigo: string;
  nome: string;
  preco: number;
  situacao: string;
}

const router = useRouter();
const produtos = ref<Produto[]>([]);
const carregando = ref(false);
const erro = ref(false);
const mensagemErro = ref('');

const pagina = ref(1);
const temMaisPaginas = ref(false);
const LIMITE_POR_PAGINA = 10;
const produtosSelecionados = ref<number[]>([]);
const dropdownAberto = ref<number | null>(null);

const modalAtivo = ref(false);
const idParaExcluir = ref<number | null>(null);
const isMassa = ref(false);
const mostrarFeedback = ref(false);
const mensagemFeedback = ref('');
const tipoFeedback = ref<'erro' | 'sucesso'>('sucesso');

const filtrosIniciais = {
  nome: '',
  sku: '',
  dataInicio: '',
  dataFim: '',
  situacao: '1'
};
const filtrosAtivos = reactive({ ...filtrosIniciais });

const selecionouTodos = computed(() => {
  return produtos.value.length > 0 && produtosSelecionados.value.length === produtos.value.length;
});

const alternarTodos = () => {
  produtosSelecionados.value = selecionouTodos.value
    ? []
    : produtos.value.map(p => p.id);
};

const handleUpdateSelecionados = (id: number) => {
  const index = produtosSelecionados.value.indexOf(id);
  if (index === -1) {
    produtosSelecionados.value.push(id);
  } else {
    produtosSelecionados.value.splice(index, 1);
  }
};

const alternarDropdown = (id: number) => {
  dropdownAberto.value = dropdownAberto.value === id ? null : id;
};

const fecharDropdownExterno = (event: MouseEvent) => {
  const target = event.target as HTMLElement;
  if (!target.closest('.dropdown-trigger')) {
    dropdownAberto.value = null;
  }
};

const irParaInclusao = () => router.push('/produtos/novo');
const irParaEdicao = (id: number) => router.push(`/produtos/editar/${id}`);


const handlePesquisa = (novosFiltros: any) => {
  Object.assign(filtrosAtivos, novosFiltros);
  pagina.value = 1;
  produtosSelecionados.value = [];
  buscarProdutos();
};

const handleLimpar = () => {
  Object.assign(filtrosAtivos, filtrosIniciais);
  pagina.value = 1;
  produtosSelecionados.value = [];
  buscarProdutos();
};

const trocarPagina = (novaPagina: number) => {
  if (novaPagina < 1 || (novaPagina > pagina.value && !temMaisPaginas.value)) return;
  pagina.value = novaPagina;
  produtosSelecionados.value = [];
  buscarProdutos();
};



const buscarProdutos = async () => {
  if (carregando.value) return;

  carregando.value = true;
  erro.value = false;
  dropdownAberto.value = null;

  const token = localStorage.getItem('bling_access_token');
  if (!token) {
    router.push('/');
    return;
  }

  try {
    const params = new URLSearchParams({
      pagina: pagina.value.toString(),
      limite: LIMITE_POR_PAGINA.toString()
    });

    if (filtrosAtivos.nome) params.append('nome', filtrosAtivos.nome);
    if (filtrosAtivos.sku) params.append('codigo', filtrosAtivos.sku);
    if (filtrosAtivos.situacao) params.append('criterio', filtrosAtivos.situacao);
    if (filtrosAtivos.dataInicio) params.append('dataAlteracaoInicial', filtrosAtivos.dataInicio);
    if (filtrosAtivos.dataFim) params.append('dataAlteracaoFinal', filtrosAtivos.dataFim);

    const resposta = await fetch(`/Api/v3/produtos?${params.toString()}`, {
      method: 'GET',
      headers: { 'Authorization': `Bearer ${token}` }
    });

    const dados = await resposta.json();

    if (resposta.ok) {
      produtos.value = dados.data || [];
      temMaisPaginas.value = produtos.value.length === LIMITE_POR_PAGINA;
    } else {
      if (resposta.status === 401) {
        localStorage.removeItem('bling_access_token');
        router.push('/');
        return;
      }

      if (resposta.status === 404) {
        produtos.value = [];
        temMaisPaginas.value = false;
      } else {
        throw new Error(dados.error?.description || 'Erro ao buscar produtos');
      }
    }
  } catch (err: any) {
    erro.value = true;
    mensagemErro.value = err.message;
    produtos.value = [];
    temMaisPaginas.value = false;
  } finally {
    carregando.value = false;
  }
};

const mensagemModal = computed(() => {
  if (isMassa.value) {
    return `Tem certeza que deseja excluir o(s) ${produtosSelecionados.value.length} produto(s) selecionado(s)?`;
  }
  return "Tem certeza que deseja excluir este produto?.";
});

const prepararExclusaoIndividual = (id: number) => {
  idParaExcluir.value = id;
  isMassa.value = false;
  modalAtivo.value = true;
};

const prepararExclusaoMassa = () => {
  if (produtosSelecionados.value.length === 0) return;
  isMassa.value = true;
  modalAtivo.value = true;
};

const fecharModal = () => {
  if (carregando.value) return;
  modalAtivo.value = false;
  idParaExcluir.value = null;
  isMassa.value = false;
};

const confirmarAcaoModal = () => {
  if (isMassa.value) {
    executarExclusaoEmMassa();
  } else if (idParaExcluir.value) {
    executarExclusaoLogica(idParaExcluir.value);
  }
};

const executarExclusaoLogica = async (id: number) => {
  const token = localStorage.getItem('bling_access_token');
  carregando.value = true;
  try {
    const resposta = await fetch(`/Api/v3/produtos/${id}/situacoes`, {
      method: 'PATCH',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ situacao: 'E' })
    });

    if (resposta.ok) {
      modalAtivo.value = false;
      idParaExcluir.value = null;
      await nextTick();
      produtos.value = produtos.value.filter(p => p.id !== id);
      tipoFeedback.value = 'sucesso';
      mensagemFeedback.value = 'Produto excluído com sucesso!';
      mostrarFeedback.value = true;
      console.log('Tentando mostrar feedback:', mensagemFeedback.value);
      mostrarFeedback.value = true;
      setTimeout(() => {
        mostrarFeedback.value = false;
      }, 3000);
    } else {
      const dados = await resposta.json();
      throw new Error(dados.error?.description || 'Erro ao alterar situação');
    }
  } catch (err: any) {
    erro.value = true;
    mensagemErro.value = "Não foi possível remover: " + err.message;
    modalAtivo.value = false;
  } finally {
    carregando.value = false;
  }
};

const executarExclusaoEmMassa = async () => {
  const selecionados = [...produtosSelecionados.value];
  if (selecionados.length === 0) return;

  carregando.value = true;
  const token = localStorage.getItem('bling_access_token');

  try {
    const promises = selecionados.map(id =>
      fetch(`/Api/v3/produtos/${id}/situacoes`, {
        method: 'PATCH',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ situacao: 'E' })
      })
    );

    await Promise.allSettled(promises);

    produtos.value = produtos.value.filter(p => !selecionados.includes(p.id));
    modalAtivo.value = false;
    produtosSelecionados.value = [];

    await buscarProdutos();

    tipoFeedback.value = "sucesso";
    mensagemFeedback.value = "Exclusão realizada com sucesso!";
    mostrarFeedback.value = true;

    setTimeout(() => { mostrarFeedback.value = false; }, 4000);

  } catch (err: any) {
    console.error("Erro na exclusão em massa:", err);
    erro.value = true;
    mensagemErro.value = "Ocorreu um erro ao processar a exclusão em massa.";
    modalAtivo.value = false;
  } finally {
    carregando.value = false;
  }
};


onMounted(() => {
  buscarProdutos();
  window.addEventListener('click', fecharDropdownExterno);
  window.scrollTo(0, 0);
});

onUnmounted(() => {
  window.removeEventListener('click', fecharDropdownExterno);
});
</script>
