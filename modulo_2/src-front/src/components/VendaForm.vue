<template>
  <div class="container mt-5">
    <div class="level">
      <div class="level-left">
        <h1 class="title">Nova Venda</h1>
      </div>
      <div class="level-right">
        <router-link to="/vendas" class="button is-light">
          Voltar
        </router-link>
      </div>
    </div>

    <div class="box">
      <div class="field">
        <label class="label">Nome do Cliente</label>
        <div class="control">
          <input v-model="venda.nomeCliente" class="input" type="text" placeholder="Nome completo do cliente" required>
        </div>
      </div>

      <hr />

      <h2 class="subtitle">Adicionar Itens</h2>
      <div class="field has-addons">
        <div class="control is-expanded">
          <input 
            v-model="termoBusca" 
            @input="buscarProdutos" 
            class="input" 
            type="text" 
            placeholder="Pesquisar produto no Bling (min. 3 caracteres)..."
          >
        </div>
      </div>

      <div v-if="produtosSugeridos.length > 0" class="panel is-info mb-4">
        <a 
          v-for="p in produtosSugeridos" 
          :key="p.id" 
          @click.prevent="adicionarItem(p)" 
          class="panel-block"
        >
          {{ p.nome }} - <strong>R$ {{ p.preco.toFixed(2) }}</strong>
        </a>
      </div>

      <table class="table is-fullwidth is-striped">
        <thead>
          <tr>
            <th>Produto</th>
            <th style="width: 100px">Qtd</th>
            <th>Unitário</th>
            <th>Subtotal</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in venda.itens" :key="index">
            <td>{{ item.nomeProduto }}</td>
            <td>
              <input v-model.number="item.quantidade" class="input is-small" type="number" min="1">
            </td>
            <td>R$ {{ item.precoUnitario.toFixed(2) }}</td>
            <td>R$ {{ (item.quantidade * item.precoUnitario).toFixed(2) }}</td>
            <td>
              <button @click="removerItem(index)" class="button is-small is-danger is-light">Remover</button>
            </td>
          </tr>
          <tr v-if="venda.itens.length === 0">
            <td colspan="5" class="has-text-centered text-muted">Nenhum produto adicionado.</td>
          </tr>
        </tbody>
      </table>

      <hr />

      <div class="columns is-justify-content-end">
        <div class="column is-4">
          <div class="field is-horizontal">
            <div class="field-label is-normal">
              <label class="label">Subtotal</label>
            </div>
            <div class="field-body">
              <div class="control">
                <input :value="'R$ ' + subtotal.toFixed(2)" class="input is-static" readonly>
              </div>
            </div>
          </div>

          <div class="field is-horizontal">
            <div class="field-label is-normal">
              <label class="label">Desconto(%)</label>
            </div>
            <div class="field-body">
              <div class="control">
                <input v-model.number="venda.percentualDesconto" class="input" type="number" min="0" max="100">
              </div>
            </div>
          </div>

          <div class="field is-horizontal">
            <div class="field-label is-normal">
              <label class="label fw-bold">Total Final</label>
            </div>
            <div class="field-body">
              <div class="control">
                <input :value="'R$ ' + totalFinal.toFixed(2)" class="input is-static has-text-weight-bold has-text-success" readonly>
              </div>
            </div>
          </div>

          <div class="field mt-5">
            <button 
              @click="salvarVenda" 
              class="button is-primary is-fullwidth is-large"
              :disabled="venda.itens.length === 0 || !venda.nomeCliente"
              :class="{'is-loading': enviando}"
            >
              Finalizar Venda
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const termoBusca = ref('');
const produtosSugeridos = ref<any[]>([]);
const enviando = ref(false);

const venda = ref({
  nomeCliente: '',
  percentualDesconto: 0, 
  itens: [] as any[]
});


const buscarProdutos = async () => {
  if (termoBusca.value.length < 3) {
    produtosSugeridos.value = [];
    return;
  }
  try {
    const res = await fetch(`http://localhost:88/index.php/api/vendas/buscarProdutosNoBling?nome=${termoBusca.value}`, {
      credentials: 'include'
    });
    const dados = await res.json();
    produtosSugeridos.value = dados.data || [];
  } catch (e) {
    console.error("Erro na busca:", e);
  }
};

const adicionarItem = (p: any) => {
  venda.value.itens.push({
    produto_id: p.id,
    nomeProduto: p.nome,
    quantidade: 1,
    precoUnitario: p.preco
  });
  termoBusca.value = '';
  produtosSugeridos.value = [];
};

const removerItem = (index: number) => {
  venda.value.itens.splice(index, 1);
};

const subtotal = computed(() => {
  return venda.value.itens.reduce((acc, item) => acc + (item.quantidade * item.precoUnitario), 0);
});

const totalFinal = computed(() => {
  return subtotal.value - (subtotal.value * (venda.value.percentualDesconto / 100));
});

const salvarVenda = async () => {
  enviando.value = true;
  try {
    const res = await fetch('http://localhost:88/index.php/api/vendas/store', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        ...venda.value,
        subtotal: subtotal.value,
        totalComDesconto: totalFinal.value
      }),
      credentials: 'include' 
    });
    
    if (res.ok) {
      alert('Venda finalizada com sucesso!');
      router.push('/vendas');
    } else {
      const erro = await res.json();
      alert('Erro ao salvar: ' + (erro.message || 'Verifique os dados.'));
    }
  } catch (error) {
    alert('Erro de conexão com o servidor.');
  } finally {
    enviando.value = false;
  }
};
</script>

<style scoped>
.is-static {
  border: none;
  box-shadow: none;
  background-color: transparent;
}
</style>