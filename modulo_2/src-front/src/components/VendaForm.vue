<template>
  <div class="container mt-5">
    <div class="level">
      <div class="level-left">
        <h1 class="title">{{ idEdicao ? 'Editar Venda' : 'Nova Venda' }}</h1>
      </div>
      <div class="level-right">
        <router-link to="/vendas" class="button is-light">Voltar</router-link>
      </div>
    </div>

    <div class="box">
      <div class="columns">
        <div class="column is-8">
          <div class="field">
            <label class="label">Nome do Cliente</label>
            <div class="control">
              <input v-model="venda.nomeCliente" class="input" type="text" placeholder="Nome completo do cliente"
                required>
            </div>
          </div>
        </div>
        <div class="column is-4">
          <div class="field">
            <label class="label">Data da Venda</label>
            <div class="control">
              <input v-model="venda.dataVenda" class="input" type="date" required>
            </div>
          </div>
        </div>
      </div>
      <hr />
      <h2 class="subtitle">Pesquisar e Selecionar Produto</h2>
      <div class="columns">
        <div class="column is-6">
          <div class="field">
            <div class="control is-expanded">
              <input v-model="termoBusca" @input="buscarProdutos" class="input" type="text"
                placeholder="Pesquisar produto (min. 3 caracteres)...">
            </div>
            <div v-if="produtosSugeridos.length > 0" class="panel is-info position-absolute w-100" style="z-index: 10;">
              <a v-for="p in produtosSugeridos" :key="p.id" @click.prevent="selecionarProduto(p)" class="panel-block">
                {{ p.nome }} - <strong>R$ {{ p.preco.toFixed(2) }}</strong>
              </a>
            </div>
          </div>
        </div>

        <div class="column is-2">
          <input v-model.number="itemEmEspera.quantidade" class="input" type="number" min="1" placeholder="Quantidade">
        </div>
        <div class="column is-2">
          <input :value="itemEmEspera.precoUnitario ? 'R$ ' + itemEmEspera.precoUnitario.toFixed(2) : ''"
            class="input is-static" readonly>
        </div>
        <div class="column is-2">
          <button @click="confirmarAdicao" class="button is-success is-fullwidth" :disabled="!itemEmEspera.produto_id">
            Adicionar
          </button>
        </div>
      </div>

      <table class="table is-fullwidth is-striped mt-4">
        <thead>
          <tr>
            <th>Produto</th>
            <th style="width: 100px">Quantidade</th>
            <th style="width: 140px">Preço Unitário</th>
            <th>Subtotal</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in venda.itens" :key="index">
            <td>{{ item.nomeProduto }}</td>
            <td><input v-model.number="item.quantidade" class="input is-small" type="number" min="1"></td>
            <td>
              <div class="field has-addons">
                <p class="control">
                  <a class="button is-small is-static">
                    R$
                  </a>
                </p>
                <p class="control is-expanded">
                  <input v-model.number="item.precoUnitario" class="input is-small" type="number" step="0.01" min="0">
                </p>
              </div>
            </td>
            <td>R$ {{ (item.quantidade * item.precoUnitario).toFixed(2) }}</td>
            <td><button @click="removerItem(index)" class="button is-small is-danger is-light">Remover</button></td>
          </tr>
        </tbody>
      </table>
      <hr />

      <div class="columns is-justify-content-end">
        <div class="column is-4">
          <div class="box is-shadowless" style="background-color: #f9f9f9; border: 1px solid #ededed;">

            <div class="is-flex is-justify-content-space-between is-align-items-center mb-3">
              <span class="has-text-grey">Subtotal</span>
              <span class="is-size-5 has-text-weight-medium">{{ formatarMoeda(subtotal) }}</span>
            </div>

            <div class="is-flex is-justify-content-space-between is-align-items-center mb-3">
              <span class="has-text-grey">Desconto (%)</span>
              <div class="control" style="width: 80px;">
                <input v-model.number="venda.percentualDesconto" class="input is-small has-text-right" type="number"
                  min="0" max="100" @input="validarDesconto">
              </div>
            </div>

            <hr style="margin: 1rem 0; background-color: #ddd;">

            <div class="is-flex is-justify-content-space-between is-align-items-center">
              <span class="has-text-weight-bold is-size-5">Total Final</span>
              <span class="is-size-4 has-text-weight-bold has-text-success">
                {{ formatarMoeda(totalFinal) }}
              </span>
            </div>

            <div class="field mt-5">
              <button @click="salvarVenda"
                class="button is-primary is-fullwidth is-medium is-uppercase has-text-weight-bold"
                :class="{ 'is-loading': enviando }" :disabled="venda.itens.length === 0 || !venda.nomeCliente">
                {{ idEdicao ? 'Salvar Alterações' : 'Finalizar Venda' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';

const router = useRouter();
const route = useRoute();
const idEdicao = route.params.id;

const termoBusca = ref('');
const produtosSugeridos = ref<any[]>([]);
const enviando = ref(false);

const venda = ref({
  nomeCliente: '',
  dataVenda: new Date().toISOString().substr(0, 10), // Data padrão hoje
  percentualDesconto: 0,
  itens: [] as any[]
});

// Estado para o item que está sendo selecionado antes de clicar no botão "Adicionar"
const itemEmEspera = ref({
  produto_id: null,
  nomeProduto: '',
  quantidade: null as number | null,
  precoUnitario: 0
});

const buscarProdutos = async () => {
  if (termoBusca.value.length < 3) { produtosSugeridos.value = []; return; }
  const res = await fetch(`http://localhost:88/index.php/api/vendas/buscarProdutosNoBling?nome=${termoBusca.value}`, { credentials: 'include' });
  const dados = await res.json();
  produtosSugeridos.value = dados.data || [];
};

const selecionarProduto = (p: any) => {
  itemEmEspera.value = {
    produto_id: p.id,
    nomeProduto: p.nome,
    quantidade: 1,
    precoUnitario: p.preco
  };
  termoBusca.value = p.nome;
  produtosSugeridos.value = [];
};

const confirmarAdicao = () => {
  if (!itemEmEspera.value.produto_id || !itemEmEspera.value.quantidade || itemEmEspera.value.quantidade < 1) return;
  venda.value.itens.push({ ...itemEmEspera.value });
  // Limpa o espera
  itemEmEspera.value = { produto_id: null, nomeProduto: '', quantidade: null, precoUnitario: 0 };
  termoBusca.value = '';
};

const validarDesconto = () => {
  if (venda.value.percentualDesconto < 0) {
    venda.value.percentualDesconto = 0;
  } else if (venda.value.percentualDesconto > 100) {
    venda.value.percentualDesconto = 100;
  }
};

const formatarMoeda = (valor: number) => {
  return valor.toLocaleString('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  });
};

const removerItem = (index: number) => { venda.value.itens.splice(index, 1); };

const subtotal = computed(() => venda.value.itens.reduce((acc, item) => acc + (item.quantidade * item.precoUnitario), 0));
const totalFinal = computed(() => subtotal.value - (subtotal.value * (venda.value.percentualDesconto / 100)));

const salvarVenda = async () => {
  enviando.value = true;
  const url = idEdicao
    ? `http://localhost:88/index.php/api/vendas/update?id=${idEdicao}`
    : 'http://localhost:88/index.php/api/vendas/store';

  try {
    const res = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ ...venda.value, subtotal: subtotal.value, totalComDesconto: totalFinal.value }),
      credentials: 'include'
    });
    if (res.ok) { alert('Sucesso!'); router.push('/vendas'); }
  } finally { enviando.value = false; }
};

onMounted(async () => {
  if (idEdicao) {
    const res = await fetch(`http://localhost:88/index.php/api/vendas/show?id=${idEdicao}`, { credentials: 'include' });
    const json = await res.json();
    if (res.ok) {
      const v = json.data.venda;
      venda.value = {
        nomeCliente: v.nomeCliente,
        dataVenda: v.dataVenda, // O autopreenchimento da data
        percentualDesconto: parseFloat(v.percentualDesconto),
        itens: json.data.itens.map((i: any) => ({
          produto_id: i.produto_id,
          nomeProduto: i.nomeProduto,
          quantidade: i.quantidade,
          precoUnitario: parseFloat(i.precoUnitario)
        }))
      };
    }
  }
});
</script>