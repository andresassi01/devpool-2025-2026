<template>
  <div class="box mt-5 table-container-fixed">
    <div v-if="isLoading" class="has-text-centered my-5">
      <button class="button is-loading is-ghost">Carregando</button>
    </div>

    <table class="table is-fullwidth is-striped is-hoverable table-fixed">
      <thead>
        <tr>
          <th style="width: 40px;">
            <input type="checkbox" @change="alternarTodos"
              :checked="selecionados.length === vendas.length && vendas.length > 0">
          </th>
          <th style="width: 80px;">ID</th>
          <th>Cliente</th>
          <th style="width: 150px;">Data</th>
          <th style="width: 150px;">Total</th>
          <th style="width: 100px;" class="has-text-centered">Ações</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="venda in (vendas || [])" :key="venda.id">
          <td>
            <input type="checkbox" :value="venda.id" v-model="selecionados">
          </td>

          <td><strong>#{{ venda.id }}</strong></td>

          <td class="truncate" :title="venda.nomeCliente">
            {{ venda.nomeCliente || 'Cliente não identificado' }}
          </td>

          <td>
            {{ venda.dataVenda ? new Date(venda.dataVenda + 'T00:00:00').toLocaleDateString('pt-BR') : '---' }}
          </td>

          <td class="has-text-weight-bold">
            {{ formatarMoeda(venda.totalComDesconto) }}
          </td>

          <td class="has-text-centered">
            <div class="buttons is-centered">
              <button class="button is-small is-ghost" @click="$emit('visualizar', venda.id)" title="Editar">
                <span class="icon"><i class="fas fa-edit"></i></span>
              </button>

              <button class="button is-small is-ghost has-text-danger" @click="$emit('excluir', venda.id)"
                title="Excluir">
                <span class="icon"><i class="fas fa-trash"></i></span>
              </button>
            </div>
          </td>
        </tr>

        <tr v-if="(!vendas || vendas.length === 0) && !isLoading">
          <td colspan="6" class="has-text-centered py-6 is-size-5 has-text-grey">
            Nenhuma venda encontrada com os filtros selecionados.
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup lang="ts">

import { ref, watch } from 'vue';

const props = defineProps<{
  vendas: any[],
  isLoading: boolean
}>();

const emit = defineEmits(['visualizar', 'excluir', 'selecao-alterada']);

const selecionados = ref<number[]>([]);

const formatarMoeda = (valor: any) => {
  const numero = Number(valor);
  if (isNaN(numero)) return 'R$ 0,00';

  return numero.toLocaleString('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  });
};

// Limpa a seleção quando a lista de vendas mudar
watch(() => props.vendas, () => {
  selecionados.value = [];
});

// Avisa o pai sobre as mudanças
watch(selecionados, (novosIds) => {
  emit('selecao-alterada', novosIds);
});

const alternarTodos = (event: any) => {
  if (event.target.checked) {
    // CORREÇÃO: Tipando o parâmetro 'v' como any para o erro 7006
    selecionados.value = props.vendas.map((v: any) => v.id);
  } else {
    selecionados.value = [];
  }
};

</script>

<style scoped>
.table-fixed {
  table-layout: fixed;
  width: 100%;
}

.table td,
.table th {
  vertical-align: middle !important;
}

.truncate {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.table-container-fixed {
  min-height: 500px;
}

.table.is-hoverable tbody tr:hover {
  background-color: #fafafa;
}
</style>