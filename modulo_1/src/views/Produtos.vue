<template>
  <BarraDeNavegacao />
  <section class="section">
    <div class="container">
      
      <h1 class="title mb-5">Gestão de Produtos</h1>
      
      <FiltroProdutos 
        :is-loading="carregando" 
        @pesquisar="handlePesquisa" 
        @limpar="handleLimpar" 
      />

      <AcoesProdutos 
        :quantidade-selecionados="produtosSelecionados.length" 
        @excluir-massa="excluirEmMassa"
        @incluir="irParaInclusao" 
      />

      <FeedbackNotificacao 
        :ativo="erro" 
        :mensagem="mensagemErro" 
        tipo="erro" 
        @fechar="erro = false" 
      />

      <ListagemProdutos 
        :produtos="produtos" 
        :is-loading="carregando" 
        :selecionados="produtosSelecionados"
        :selecionou-todos="selecionouTodos" 
        :dropdown-aberto="dropdownAberto" 
        @editar="irParaEdicao"
        @excluir="confirmarExclusao" 
        @toggle-todos="alternarTodos" 
        @toggle-dropdown="alternarDropdown"
        @update:selecionados="handleUpdateSelecionados" 
      />

      <Paginacao 
        :pagina-atual="pagina" 
        :tem-mais="temMaisPaginas" 
        :is-loading="carregando"
        @mudar-pagina="trocarPagina" 
      />
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, reactive, computed } from 'vue';
import { useRouter } from 'vue-router';

import Paginacao from '../components/Paginacao.vue';
import FiltroProdutos from '../components/FiltroProdutos.vue';
import AcoesProdutos from '../components/AcoesProdutos.vue';
import ListagemProdutos from '../components/ListagemProdutos.vue';
import FeedbackNotificacao from '../components/FeedbackNotificacao.vue';
import BarraDeNavegacao from '../layout/BarraDeNavegacao.vue';

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

const confirmarExclusao = async (id: number) => {
  if (confirm("Deseja realmente excluir este produto?")) {
    console.log("Excluir individual:", id);
  }
};

const excluirEmMassa = () => {
  if (confirm(`Deseja excluir ${produtosSelecionados.value.length} produtos selecionados?`)) {
    console.log("Excluir IDs:", produtosSelecionados.value);
  }
};

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

onMounted(() => {
  buscarProdutos();
  window.addEventListener('click', fecharDropdownExterno);
  window.scrollTo(0, 0);
});

onUnmounted(() => {
  window.removeEventListener('click', fecharDropdownExterno);
});
</script>

