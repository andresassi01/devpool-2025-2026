<template>
  <div class="container mt-5">
    <div class="level">
      <div class="level-left">
        <h1 class="title">{{ idEdicao ? 'Editar Venda' : 'Nova Venda' }}</h1>
      </div>
      <div class="level-right">
        <button @click="confirmarCancelamento" class="button is-light">
          Cancelar
        </button>
      </div>
    </div>

    <div class="box">
      <div class="columns">
        <div class="column is-8">
          <div class="field">
            <label class="label">Nome do Cliente</label>
            <div class="control">
              <input v-model="venda.nomeCliente" class="input" type="text" placeholder="Nome completo do cliente" required>
            </div>
          </div>
        </div>
        <div class="column is-4">
          <div class="field">
            <label class="label">Data da Venda</label>
            <div class="control">
              <input v-model="venda.dataVenda" class="input" type="date" :disabled="!!idEdicao" required>
            </div>
            <p v-if="idEdicao" class="help is-info">A data não pode ser alterada após a criação.</p>
          </div>
        </div>
      </div>

      <hr />

      <h2 class="subtitle">Pesquisar e Selecionar Produto</h2>
      <div class="columns">
        <div class="column is-8">
          <div class="field has-addons">
            <div class="control is-expanded">
              <input 
                v-model="termoBusca" 
                class="input" 
                type="text"
                placeholder="Digite o nome do produto (min. 3 letras)..."
                @keyup.enter="buscarProdutos"
              >
            </div>
            <div class="control">
             <button @click="buscarProdutos" class="button is-success" :class="{'is-loading': buscando}">
                <span class="icon"><i class="fas fa-search"></i></span>
                <span>Buscar</span>
              </button>
            </div>
          </div>
          
          <div v-if="produtosSugeridos.length > 0" class="panel is-info mt-1" style="position: absolute; width: 65%; z-index: 100; background: white; border: 1px solid #ccc;">
            <p class="panel-heading is-size-7">Produtos encontrados no Bling</p>
            <a 
              v-for="p in produtosSugeridos" 
              :key="p.id" 
              @click.prevent="selecionarProduto(p)" 
              class="panel-block is-active"
            >
              <span class="panel-icon"><i class="fas fa-box"></i></span>
              {{ p.nome }} - <strong>R$ {{ p.preco.toFixed(2) }}</strong>
            </a>
          </div>
        </div>

        <div class="column is-2">
          <input
            :value="itemEmEspera.produto_id ? formatarMoeda(itemEmEspera.precoUnitario) : ''"
            class="input is-static has-text-centered" readonly placeholder="Preço">
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
            <th style="width: 80px">ID</th>
            <th>Produto</th>
            <th style="width: 100px">Quantidade</th>
            <th style="width: 140px">Preço Unitário</th>
            <th>Subtotal</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in venda.itens" :key="index">
            <td class="has-text-grey">#{{ item.produto_id }}</td>
            <td>{{ item.nomeProduto }}</td>
            <td>
              <input v-model.number="item.quantidade" class="input is-small" type="number" min="1" @input="validarItemTabela(item)">
            </td>
            <td>
              <div class="field has-addons">
                <p class="control"><a class="button is-small is-static">R$</a></p>
                <p class="control is-expanded">
                  <input v-model.number="item.precoUnitario" class="input is-small" type="number" step="0.01" min="0"
                    @input="() => { validarItemTabela(item); limitarCasasDecimais(item, 'precoUnitario'); }">
                </p>
              </div>
            </td>
            <td class="is-vcentered">{{ formatarMoeda(item.quantidade * item.precoUnitario) }}</td>
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
                <input v-model.number="venda.percentualDesconto" class="input is-small has-text-right" type="number" min="0" max="100" @input="validarDesconto">
              </div>
            </div>
            <hr style="margin: 1rem 0; background-color: #ddd;">
            <div class="is-flex is-justify-content-space-between is-align-items-center">
              <span class="has-text-weight-bold is-size-5">Total Final</span>
              <span class="is-size-4 has-text-weight-bold has-text-success">{{ formatarMoeda(totalFinal) }}</span>
            </div>
            <div class="field mt-5">
              <button @click="salvarVenda" class="button is-primary is-fullwidth is-medium is-uppercase has-text-weight-bold"
                :class="{ 'is-loading': enviando }" :disabled="venda.itens.length === 0 || !venda.nomeCliente">
                {{ idEdicao ? 'Salvar Alterações' : 'Finalizar Venda' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <ConfirmarExclusao :ativo="modalCancelamentoAtivo" titulo="Confirmar Cancelamento"
      mensagem="As alterações não salvas serão perdidas. Deseja realmente sair?" textoBotaoConfirmar="Sim, sair"
      @fechar="modalCancelamentoAtivo = false" @confirmar="router.push('/vendas')" />

    <FeedbackNotificacao :ativo="notificacao.ativo" :mensagem="notificacao.mensagem" :tipo="notificacao.tipo" @fechar="notificacao.ativo = false" />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import FeedbackNotificacao from './FeedbackNotificacao.vue';
import ConfirmarExclusao from './ConfirmarExclusao.vue';

const router = useRouter();
const route = useRoute();
const idEdicao = route.params.id;

const termoBusca = ref('');
const produtosSugeridos = ref<any[]>([]);
const enviando = ref(false);
const buscando = ref(false);
const modalCancelamentoAtivo = ref(false);

const notificacao = ref({ ativo: false, mensagem: '', tipo: 'sucesso' });

const venda = ref({
  nomeCliente: '',
  dataVenda: new Date().toISOString().substr(0, 10),
  percentualDesconto: 0,
  itens: [] as any[]
});

const itemEmEspera = ref({ produto_id: null, nomeProduto: '', precoUnitario: 0 });

const dispararNotificacao = (msg: string, tipo = 'sucesso') => {
  notificacao.value = { ativo: true, mensagem: msg, tipo };
  setTimeout(() => notificacao.value.ativo = false, 3500);
};

// UC8: Busca via API com tratamento de loading
const buscarProdutos = async () => {
  if (termoBusca.value.length < 3) {
    dispararNotificacao("Digite pelo menos 3 letras para buscar", "aviso");
    produtosSugeridos.value = [];
    return;
  }
  
  buscando.value = true;
  try {
    const res = await fetch(`http://localhost:88/index.php/api/vendas/buscarProdutosNoBling?nome=${termoBusca.value}`, { credentials: 'include' });
    const dados = await res.json();
    produtosSugeridos.value = dados.data || []; // UC9: Popula o select/panel
    if (produtosSugeridos.value.length === 0) dispararNotificacao("Nenhum produto encontrado", "aviso");
  } catch (error) {
    dispararNotificacao("Erro ao buscar produtos", "erro");
  } finally {
    buscando.value = false;
  }
};

const selecionarProduto = (p: any) => {
  itemEmEspera.value = {
    produto_id: p.id,
    nomeProduto: p.nome,
    precoUnitario: p.preco
  };
  termoBusca.value = p.nome;
  produtosSugeridos.value = [];
};

const confirmarAdicao = () => {
  if (!itemEmEspera.value.produto_id) return;
  venda.value.itens.push({ ...itemEmEspera.value, quantidade: 1 });
  itemEmEspera.value = { produto_id: null, nomeProduto: '', precoUnitario: 0 };
  termoBusca.value = '';
};

const validarItemTabela = (item: any) => {
  if (item.quantidade < 1) item.quantidade = 1;
  if (item.precoUnitario < 0) item.precoUnitario = 0;
};

const limitarCasasDecimais = (obj: any, campo: string) => {
  const valor = obj[campo];
  if (valor !== null && valor !== undefined && valor.toString().includes('.')) {
    const [inteiro, decimal] = valor.toString().split('.');
    if (decimal.length > 2) obj[campo] = parseFloat(`${inteiro}.${decimal.substring(0, 2)}`);
  }
};

const validarDesconto = () => {
  if (venda.value.percentualDesconto < 0) venda.value.percentualDesconto = 0;
  else if (venda.value.percentualDesconto > 100) venda.value.percentualDesconto = 100;
};

const formatarMoeda = (valor: number) => valor.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });

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
      body: JSON.stringify({
        ...venda.value,
        subtotal: subtotal.value,
        totalComDesconto: totalFinal.value
      }),
      credentials: 'include'
    });

    if (res.ok) {
      dispararNotificacao("Venda salva com sucesso!");
      setTimeout(() => router.push({ path: '/vendas', query: { reset: 'true' } }), 1200);
    } else {
      const json = await res.json();
      dispararNotificacao(json.message || "Erro ao salvar", "erro");
    }
  } catch (error) {
    dispararNotificacao("Erro de conexão", "erro");
  } finally {
    enviando.value = false;
  }
};

const confirmarCancelamento = () => {
  const temConteudo = venda.value.itens.length > 0 || venda.value.nomeCliente.trim() !== '';
  if (temConteudo) modalCancelamentoAtivo.value = true;
  else router.push('/vendas');
};

const buscarDadosEdicao = async () => {
  if (!idEdicao) return;
  try {
    const res = await fetch(`http://localhost:88/index.php/api/vendas/show?id=${idEdicao}`, { credentials: 'include' });
    const json = await res.json();
    if (res.ok && json.data) {
      const v = json.data.venda;
      venda.value = {
        nomeCliente: v.nomeCliente,
        dataVenda: v.dataVenda,
        percentualDesconto: parseFloat(v.percentualDesconto) || 0,
        itens: json.data.itens.map((i: any) => ({
          produto_id: i.produto_id,
          nomeProduto: i.nomeProduto,
          quantidade: i.quantidade,
          precoUnitario: parseFloat(i.precoUnitario)
        }))
      };
    }
  } catch (error) {
    console.error("Erro ao carregar venda:", error);
  }
};

onMounted(buscarDadosEdicao);
</script>