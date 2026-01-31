<template>
  <BarraDeNavegacao />
  <section class="section">
    <div class="container">
      <nav class="breadcrumb" aria-label="breadcrumbs">
        <ul>
          <li><router-link to="/produtos">Produtos</router-link></li>
          <li class="is-active">
            <a href="#" aria-current="page">{{ emEdicao ? 'Editar' : 'Novo' }} Produto</a>
          </li>
        </ul>
      </nav>

      <h1 class="title">{{ emEdicao ? 'Editar Produto' : 'Novo Produto' }}</h1>

      <div class="box">
        <form @submit.prevent="salvarProduto">
          <div class="columns is-multiline">
            
            <div class="column is-8">
              <div class="field">
                <label class="label">Nome do Produto *</label>
                <div class="control">
                  <input 
                    v-model="produto.nome" 
                    class="input" 
                    type="text" 
                    placeholder="Ex: Camiseta Algodão" 
                    required
                  >
                </div>
              </div>
            </div>

            <div class="column is-4">
              <div class="field">
                <label class="label">Código (SKU) *</label>
                <div class="control">
                  <input 
                    v-model="produto.codigo" 
                    class="input" 
                    type="text" 
                    placeholder="Ex: CAM-001" 
                    required
                  >
                </div>
              </div>
            </div>

            <div class="column is-3">
              <div class="field">
                <label class="label">Tipo *</label>
                <div class="control">
                  <div class="select is-fullwidth">
                    <select v-model="produto.tipo" required>
                      <option value="P">Produto</option>
                      <option value="S">Serviço</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>

            <div class="column is-3">
              <div class="field">
                <label class="label">Formato *</label>
                <div class="control">
                  <div class="select is-fullwidth">
                    <select v-model="produto.formato" :disabled="ehVariacao" required>
                      <option value="S">Simples</option>
                      <option value="V" disabled>Com Variações</option>
                      <option value="E" disabled>Com Composição</option>
                    </select>
                  </div>
                  <p v-if="ehVariacao" class="help is-info">
                    Variações não podem ter o formato alterado.
                  </p>
                </div>
              </div>
            </div>

            <div class="column is-3">
              <div class="field">
                <label class="label">Preço de Venda *</label>
                <div class="control has-icons-left">
                  <input 
                    v-model.number="produto.preco" 
                    class="input" 
                    type="number" 
                    step="0.01" 
                    placeholder="0,00"
                    required
                  >
                  <span class="icon is-small is-left">R$</span>
                </div>
              </div>
            </div>

            <div class="column is-3">
              <div class="field">
                <label class="label">Situação *</label>
                <div class="control">
                  <div class="select is-fullwidth">
                    <select v-model="produto.situacao" required>
                      <option value="A">Ativo</option>
                      <option value="I">Inativo</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <FeedbackNotificacao 
            :ativo="feedback.ativo" 
            :mensagem="feedback.mensagem" 
            :tipo="feedback.tipo"
            @fechar="feedback.ativo = false" 
          />

          <div class="field is-grouped mt-5">
            <div class="control">
              <button type="submit" class="button is-primary" :class="{ 'is-loading': enviando }">
                Salvar Produto
              </button>
            </div>
            <div class="control">
              <button type="button" class="button is-link is-light" @click="$router.push('/produtos')">
                Cancelar
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, onMounted, reactive, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import FeedbackNotificacao from '../components/FeedbackNotificacao.vue';
import BarraDeNavegacao from '../layout/BarraDeNavegacao.vue';

const route = useRoute();
const router = useRouter();

const emEdicao = computed(() => !!route.params.id);
const enviando = ref(false);
const ehVariacao = ref(false);

const produto = reactive({
  nome: '',
  codigo: '',
  preco: 0,
  situacao: 'A',
  tipo: 'P',
  formato: 'S'
});

const feedback = reactive({
  ativo: false,
  mensagem: '',
  tipo: 'erro' as 'erro' | 'sucesso'
});

const notificar = (msg: string, tipo: 'erro' | 'sucesso') => {
  feedback.mensagem = msg;
  feedback.tipo = tipo;
  feedback.ativo = true;
};

const validarFormulario = (): boolean => {
  const camposVazios: string[] = [];

  if (!produto.nome.trim()) camposVazios.push("Nome");
  if (!produto.codigo.trim()) camposVazios.push("Código (SKU)");
  
  if (camposVazios.length > 0) {
    notificar(`Preencha os campos obrigatórios: ${camposVazios.join(", ")}.`, "erro");
    return false;
  }

  if (produto.preco <= 0) {
    notificar("O preço de venda deve ser maior que zero.", "erro");
    return false;
  }
  
  return true;
};

const carregarDados = async () => {
  if (!emEdicao.value) return;

  const token = localStorage.getItem('bling_access_token');
  try {
    const resposta = await fetch(`/Api/v3/produtos/${route.params.id}`, {
      headers: { 'Authorization': `Bearer ${token}` }
    });
    const dados = await resposta.json();

    if (resposta.ok && dados.data) {
      const p = dados.data;
      
      const idPai = p.variacao?.produtoPai?.id;
      ehVariacao.value = !!idPai || p.formato === "V";

      Object.assign(produto, {
        nome: p.nome,
        codigo: p.codigo,
        preco: p.preco,
        situacao: p.situacao,
        tipo: p.tipo,
        formato: ehVariacao.value ? 'V' : p.formato
      });
    }
  } catch (err) {
    notificar("Erro ao carregar os dados do produto.", "erro");
  }
};

const salvarProduto = async () => {
  if (!validarFormulario()) return;

  enviando.value = true;
  feedback.ativo = false;

  const token = localStorage.getItem('bling_access_token');

  try {
    const url = emEdicao.value ? `/Api/v3/produtos/${route.params.id}` : '/Api/v3/produtos';
    const metodo = emEdicao.value ? 'PUT' : 'POST';

    const resposta = await fetch(url, {
      method: metodo,
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(produto)
    });

    const dados = await resposta.json();

    console.log("Resposta do Bling:", dados);

    if (!resposta.ok) {
      let msgErro = 'Erro desconhecido ao salvar o produto.';

      if (dados.error) {
        if (typeof dados.error === 'string') {
           msgErro = dados.error;
        } 
        else if (dados.error.description) {
           msgErro = dados.error.description;
        } 
        else if (dados.error.fields && dados.error.fields.length > 0) {
           const campoErro = dados.error.fields[0];
           msgErro = campoErro.msg || campoErro.message || campoErro.description || JSON.stringify(campoErro);
        }
      }

      const erroCompleto = JSON.stringify(dados).toLowerCase();
      
      if (erroCompleto.includes("código") || erroCompleto.includes("sku")) {
        if (erroCompleto.includes("existe") || erroCompleto.includes("duplicado") || erroCompleto.includes("cadastrado")) {
           throw new Error("Já existe um produto com o código SKU preenchido!");
        }
      }

      throw new Error(msgErro);
    }

    notificar(`Produto ${emEdicao.value ? 'atualizado' : 'cadastrado'} com sucesso!`, 'sucesso');
    setTimeout(() => router.push('/produtos'), 1500);
    sessionStorage.removeItem('ultimo_estado_filtro');

  } catch (err: any) {
    const mensagemFinal = err.message || "Erro inesperado (mensagem vazia).";
    notificar(mensagemFinal, 'erro');
  } finally {
    enviando.value = false;
  }
};

onMounted(carregarDados);
</script>
