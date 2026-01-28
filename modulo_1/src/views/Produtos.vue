<template>
  <section class="section">
    <div class="container">
      <div class="level">
        <div class="level-left">
          <h1 class="title">Gestão de Produtos</h1>
        </div>
        <div class="level-right">
          <button class="button is-primary" @click="buscarProdutos">
            <span class="icon"><i class="fas fa-sync"></i></span>
            <span>Atualizar</span>
          </button>
        </div>
      </div>

      <div class="field is-grouped mb-5">
        <p class="control is-expanded">
          <input v-model="filtroNome" class="input" type="text" placeholder="Filtrar por nome...">
        </p>
        <p class="control">
          <input v-model="filtroSKU" class="input" type="text" placeholder="Filtrar por SKU...">
        </p>
      </div>

      <div v-if="carregando" class="has-text-centered my-6">
        <button class="button is-loading is-large is-ghost">Carregando</button>
      </div>

      <div v-else-if="erro" class="notification is-danger">
        <button class="delete" @click="erro = false"></button>
        {{ mensagemErro }}
      </div>

      <div v-else class="box">
        <table class="table is-fullwidth is-striped is-hoverable">
          <thead>
            <tr>
              <th>Código (SKU)</th>
              <th>Nome</th>
              <th>Preço</th>
              <th>Situação</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="produto in produtosFiltrados" :key="produto.id">
              <td>{{ produto.codigo }}</td>
              <td>{{ produto.nome }}</td>
              <td>{{ Number(produto.preco).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }) }}</td>
              <td>
                <span :class="['tag', produto.situacao === 'A' ? 'is-success' : 'is-danger']">
                  {{ produto.situacao === 'A' ? 'Ativo' : 'Inativo' }}
                </span>
              </td>
            </tr>
            <tr v-if="produtosFiltrados.length === 0">
              <td colspan="4" class="has-text-centered">Nenhum produto encontrado.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <Paginacao :pagina-atual="pagina" :tem-mais="temMaisPaginas" @mudar-pagina="trocarPagina" />
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import Paginacao from '../components/Paginacao.vue';

const router = useRouter();
const produtos = ref<any[]>([]);
const carregando = ref(false);
const erro = ref(false);
const mensagemErro = ref('');
const pagina = ref(1);
const temMaisPaginas = ref(true);

const filtroNome = ref('');
const filtroSKU = ref('');

const trocarPagina = (novaPagina: number) => {
  pagina.value = novaPagina;
  buscarProdutos();
};

const buscarProdutos = async () => {
  carregando.value = true;
  erro.value = false;

  const token = localStorage.getItem('bling_access_token');

  if (!token) {
    router.push('/');
    return;
  }

  try {
    let url = `/Api/v3/produtos?pagina=${pagina.value}&limite=10`;

    if (filtroNome.value) url += `&nome=${encodeURIComponent(filtroNome.value)}`;
    if (filtroSKU.value) url += `&codigo=${encodeURIComponent(filtroSKU.value)}`;

    const resposta = await fetch(url, {
      method: 'GET',
      headers: { 'Authorization': `Bearer ${token}` }
    });

    const dados = await resposta.json();

    if (resposta.ok) {
      produtos.value = dados.data || [];
      temMaisPaginas.value = produtos.value.length === 10;
    } else {
      throw new Error(dados.error?.description || 'Erro ao buscar produtos');
    }
  } catch (err: any) {
    erro.value = true;
    mensagemErro.value = err.message;
  } finally {
    carregando.value = false;
  }
};

const produtosFiltrados = computed(() => {
  return produtos.value;
});

onMounted(buscarProdutos);
</script>