<template>
  <div class="container mt-5">
    <div class="level">
      <div class="level-left">
        <h1 class="title">{{ idEdicao ? 'Editar Venda' : 'Nova Venda' }}</h1>
      </div>
      <div class="level-right">
        <button @click="confirmarCancelamento" class="button is-light">Cancelar</button>
      </div>
    </div>

    <div class="box">
      <div class="columns">
        <div class="column is-8">
          <div class="field">
            <label class="label">Nome do Cliente</label>
            <div class="control">
              <input v-model="vendaAtiva.nomeCliente" class="input" type="text" placeholder="Nome completo do cliente" required>
            </div>
          </div>
        </div>
        <div class="column is-4">
          <div class="field">
            <label class="label">Data da Venda</label>
            <div class="control">
              <input v-model="vendaAtiva.dataVenda" class="input" type="date" :disabled="!!idEdicao" required>
            </div>
            <p v-if="idEdicao" class="help is-info">A data não pode ser alterada após a criação.</p>
          </div>
        </div>
      </div>

      <hr />

      <h2 class="subtitle">Pesquisar e Selecionar Produto</h2>
      <div class="columns">
        <div class="column is-8" style="position: relative;">
          <div class="field has-addons">
            <div class="control is-expanded">
              <input 
                v-model="termoBusca" 
                class="input" 
                type="text"
                placeholder="Digite o nome do produto (min. 3 letras)..."
                @keyup.enter="executarBuscaBling"
              >
            </div>
            <div class="control">
             <button @click="executarBuscaBling" class="button is-success" :class="{'is-loading': buscandoBling}">
                <span class="icon"><i class="fas fa-search"></i></span>
                <span>Buscar</span>
              </button>
            </div>
          </div>
          
          <div v-if="produtosSugeridos.length > 0" class="panel is-info mt-1 painel-flutuante">
            <p class="panel-heading is-size-7">Produtos encontrados no Bling</p>
            <a 
              v-for="p in produtosSugeridos" 
              :key="p.id" 
              @click.prevent="selecionarProduto(p)" 
              class="panel-block is-active"
            >
              <span class="panel-icon"><i class="fas fa-box"></i></span>
              {{ p.nome }} - <strong>{{ formatarMoeda(p.preco) }}</strong>
            </a>
          </div>
        </div>

        <div class="column is-2">
          <input
            :value="itemEmEspera.produto_id ? formatarMoeda(itemEmEspera.precoUnitario) : ''"
            class="input is-static has-text-centered" readonly placeholder="Preço">
        </div>
        <div class="column is-2">
          <button @click="confirmarAdicaoTabela" class="button is-success is-fullwidth" :disabled="!itemEmEspera.produto_id">
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
          <tr v-for="(item, index) in vendaAtiva.itens" :key="index">
            <td class="has-text-grey">#{{ item.produto_id }}</td>
            <td>{{ item.nomeProduto }}</td>
            <td>
              <input v-model.number="item.quantidade" class="input is-small" type="number" min="1" @input="aplicarRegrasItem(item)">
            </td>
            <td>
              <div class="field has-addons">
                <p class="control"><a class="button is-small is-static">R$</a></p>
                <p class="control is-expanded">
                  <input v-model.number="item.precoUnitario" class="input is-small" type="number" step="0.01" min="0" @input="aplicarRegrasItem(item)">
                </p>
              </div>
            </td>
            <td class="is-vcentered">{{ formatarMoeda(item.quantidade * item.precoUnitario) }}</td>
            <td><button @click="vendaAtiva.itens.splice(index, 1)" class="button is-small is-danger is-light">Remover</button></td>
          </tr>
        </tbody>
      </table>

      <hr />

      <div class="columns is-justify-content-end">
        <div class="column is-4">
          <div class="box is-shadowless box-resumo">
            <div class="is-flex is-justify-content-space-between is-align-items-center mb-3">
              <span class="has-text-grey">Subtotal</span>
              <span class="is-size-5 has-text-weight-medium">{{ formatarMoeda(valorSubtotal) }}</span>
            </div>
            <div class="is-flex is-justify-content-space-between is-align-items-center mb-3">
              <span class="has-text-grey">Desconto (%)</span>
              <div class="control" style="width: 80px;">
                <input v-model.number="vendaAtiva.percentualDesconto" class="input is-small has-text-right" type="number" min="0" max="100">
              </div>
            </div>
            <hr style="margin: 1rem 0; background-color: #ddd;">
            <div class="is-flex is-justify-content-space-between is-align-items-center">
              <span class="has-text-weight-bold is-size-5">Total Final</span>
              <span class="is-size-4 has-text-weight-bold has-text-success">{{ formatarMoeda(valorTotalFinal) }}</span>
            </div>
            <div class="field mt-5">
              <button 
                @click="executarSalvamento" 
                class="button is-primary is-fullwidth is-medium is-uppercase has-text-weight-bold"
                :class="{ 'is-loading': enviandoForm }" 
                :disabled="vendaAtiva.itens.length === 0 || !vendaAtiva.nomeCliente">
                {{ idEdicao ? 'Salvar Alterações' : 'Finalizar Venda' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <ConfirmarExclusao 
      :ativo="modalCancelamentoAtivo" 
      titulo="Confirmar Cancelamento"
      mensagem="As alterações não salvas serão perdidas. Deseja realmente sair?" 
      textoBotaoConfirmar="Sim, sair"
      @fechar="modalCancelamentoAtivo = false" 
      @confirmar="router.push('/vendas')" 
    />
    <FeedbackNotificacao :ativo="notificacao.ativo" :mensagem="notificacao.mensagem" :tipo="notificacao.tipo" @fechar="notificacao.ativo = false" />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useVendas } from '../composables/useVendas';
import FeedbackNotificacao from './FeedbackNotificacao.vue';
import ConfirmarExclusao from './ConfirmarExclusao.vue';

const router = useRouter();
const route = useRoute();
const idEdicao = route.params.id as string;

const { 
  vendaAtiva, produtosSugeridos, itemEmEspera, 
  buscandoBling, enviandoForm, valorSubtotal, valorTotalFinal,
  buscarProdutosNoBling, persistirVenda, carregarVendaParaEdicao, 
  formatarMoeda, aplicarRegrasItem 
} = useVendas();

const termoBusca = ref('');
const modalCancelamentoAtivo = ref(false);
const notificacao = ref({ ativo: false, mensagem: '', tipo: 'sucesso' });

const dispararNotificacao = (msg: string, tipo = 'sucesso') => {
  notificacao.value = { ativo: true, mensagem: msg, tipo };
  setTimeout(() => notificacao.value.ativo = false, 3500);
};

const executarBuscaBling = async () => {
  if (termoBusca.value.length < 3) {
    dispararNotificacao("Digite pelo menos 3 letras para buscar", "aviso");
    produtosSugeridos.value = [];
    return;
  }
  const result = await buscarProdutosNoBling(termoBusca.value);
  if (!result.sucesso) dispararNotificacao(result.mensagem || '', "erro");
  else if (produtosSugeridos.value.length === 0) dispararNotificacao("Nenhum produto encontrado", "aviso");
};

const selecionarProduto = (p: any) => {
  itemEmEspera.value = { produto_id: p.id, nomeProduto: p.nome, precoUnitario: p.preco, quantidade: 1 };
  termoBusca.value = p.nome;
  produtosSugeridos.value = [];
};

const confirmarAdicaoTabela = () => {
  if (!itemEmEspera.value.produto_id) return;
  vendaAtiva.value.itens.push({ ...itemEmEspera.value });
  itemEmEspera.value = { produto_id: null, nomeProduto: '', quantidade: 1, precoUnitario: 0 };
  termoBusca.value = '';
};

const executarSalvamento = async () => {
  // A validação para não passar desconto maior que 100 é garantida antes do submit
  if(vendaAtiva.value.percentualDesconto > 100) vendaAtiva.value.percentualDesconto = 100;
  
  const result = await persistirVenda(idEdicao);
  
  if (result.sucesso) {
    dispararNotificacao("Venda salva com sucesso!");
    setTimeout(() => router.push({ path: '/vendas', query: { reset: 'true' } }), 1200);
  } else {
    dispararNotificacao(result.mensagem || "Erro ao salvar", "erro");
  }
};

const confirmarCancelamento = () => {
  const temConteudo = vendaAtiva.value.itens.length > 0 || vendaAtiva.value.nomeCliente.trim() !== '';
  if (temConteudo) modalCancelamentoAtivo.value = true;
  else router.push('/vendas');
};

onMounted(() => {
  if (idEdicao) carregarVendaParaEdicao(idEdicao);
});
</script>

<style scoped>
.painel-flutuante {
  position: absolute;
  width: 100%;
  z-index: 1000;
  background: white;
  border: 1px solid #ccc;
  box-shadow: 0px 4px 6px rgba(0,0,0,0.1);
  max-height: 250px;
  overflow-y: auto;
}
.box-resumo {
  background-color: #f9f9f9; 
  border: 1px solid #ededed;
}
</style>