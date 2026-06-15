<template>
  <div class="container mt-5">
    <div class="level">
      <div class="level-left">
        <h1 class="title">{{ idEdicao ? 'Editar Cliente' : 'Novo Cliente' }}</h1>
      </div>
      <div class="level-right">
        <button @click="confirmarCancelamento" class="button is-light">Cancelar</button>
      </div>
    </div>

    <div class="box">
      <div class="field">
        <label class="label">Nome do Cliente</label>
        <div class="control">
          <input v-model="cliente.nome" class="input" type="text" placeholder="Digite o nome completo...">
        </div>
      </div>

      <div class="field mt-5">
        <button @click="executarSalvamento" 
                class="button is-primary is-medium"
                :class="{ 'is-loading': carregando }"
                :disabled="!cliente.nome">
          {{ idEdicao ? 'Salvar Alterações' : 'Cadastrar Cliente' }}
        </button>
      </div>
    </div>

    <ConfirmarExclusao :ativo="modalCancelamentoAtivo" titulo="Confirmar Cancelamento"
      mensagem="As alterações não salvas serão perdidas. Deseja realmente sair?" textoBotaoConfirmar="Sim, sair"
      @fechar="modalCancelamentoAtivo = false" @confirmar="router.push('/clientes')" />
      
    <FeedbackNotificacao :ativo="notificacao.ativo" :mensagem="notificacao.mensagem" :tipo="notificacao.tipo"
      @fechar="notificacao.ativo = false" />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useClientes } from '../composables/useClientes';
import FeedbackNotificacao from './FeedbackNotificacao.vue';
import ConfirmarExclusao from './ConfirmarExclusao.vue';

const router = useRouter();
const route = useRoute();
const idEdicao = route.params.id as string;

const { salvarCliente, buscarClientePorId } = useClientes();

const cliente = ref<{ id?: number; nome: string }>({ nome: '' });
const carregando = ref(false);
const modalCancelamentoAtivo = ref(false);
const notificacao = ref({ ativo: false, mensagem: '', tipo: 'sucesso' });

const dispararNotificacao = (msg: string, tipo = 'sucesso') => {
  notificacao.value = { ativo: true, mensagem: msg, tipo };
  setTimeout(() => notificacao.value.ativo = false, 3500);
};

const executarSalvamento = async () => {
  carregando.value = true;
  try {
    await salvarCliente(cliente.value);
    dispararNotificacao("Cliente salvo com sucesso!");
    setTimeout(() => router.push('/clientes'), 1000);
  } catch (e) {
    dispararNotificacao("Erro ao salvar cliente", "erro");
  } finally {
    carregando.value = false;
  }
};

const confirmarCancelamento = () => {
  // Se o nome não estiver vazio, pergunta se quer sair
  if (cliente.value.nome.trim() !== '') {
    modalCancelamentoAtivo.value = true;
  } else {
    router.push('/clientes');
  }
};

onMounted(async () => {
  if (idEdicao) {
    const data = await buscarClientePorId(idEdicao);
    cliente.value = data;
  }
});
</script>