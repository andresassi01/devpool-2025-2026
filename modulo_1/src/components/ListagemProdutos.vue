<template>
  <div class="box mt-5 table-container-fixed">
    <div v-if="isLoading" class="has-text-centered my-5">
      <button class="button is-loading is-ghost">Carregando</button>
    </div>

    <table class="table is-fullwidth is-striped is-hoverable table-fixed">
      <thead>
        <tr>
          <th style="width: 40px;">
            <input 
              type="checkbox" 
              :checked="selecionouTodos" 
              @change="$emit('toggle-todos')"
            >
          </th>
          <th style="width: 160px;">Código (SKU)</th>
          <th>Nome</th>
          <th style="width: 130px;">Preço</th>
          <th style="width: 120px;" class="has-text-centered">Situação</th>
          <th style="width: 80px;" class="has-text-centered">Ações</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="produto in produtos" :key="produto.id">
          <td>
            <input 
              type="checkbox" 
              :checked="selecionados.includes(produto.id)"
              @change="$emit('update:selecionados', produto.id)"
            >
          </td>
          <td class="truncate" :title="produto.codigo">
            <strong>{{ produto.codigo }}</strong>
          </td>
          <td class="truncate" :title="produto.nome">
            {{ produto.nome }}
          </td>
          <td class="truncate">
            {{ formatarPreco(produto.preco) }}
          </td>
          <td class="has-text-centered">
            <span :class="getClassSituacao(produto.situacao)">
              {{ getLabelSituacao(produto.situacao) }}
            </span>
          </td>
          <td class="has-text-centered">
            <div class="dropdown is-right" :class="{ 'is-active': dropdownAberto === produto.id }">
              <div class="dropdown-trigger">
                <button class="button is-small is-ghost" @click.stop="$emit('toggle-dropdown', produto.id)">
                  <span class="icon is-small">⋮</span>
                </button>
              </div>
              <div class="dropdown-menu" role="menu">
                <div class="dropdown-content">
                  <a @click="$emit('editar', produto.id)" class="dropdown-item">
                     Editar
                  </a>
                  <hr class="dropdown-divider">
                  <a @click="$emit('excluir', produto.id)" class="dropdown-item has-text-danger">
                     Excluir
                  </a>
                </div>
              </div>
            </div>
          </td>
        </tr>

        <tr v-if="produtos.length === 0 && !isLoading">
          <td colspan="6" class="has-text-centered py-6 is-size-5 has-text-grey">
            Nenhum produto encontrado com os filtros selecionados.
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  produtos: any[];
  isLoading: boolean;
  selecionados: number[];
  selecionouTodos: boolean;
  dropdownAberto: number | null;
}>();

defineEmits([
  'editar', 
  'excluir', 
  'toggle-todos', 
  'update:selecionados', 
  'toggle-dropdown'
]);

const formatarPreco = (valor: any) => {
  return Number(valor).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
};

const getClassSituacao = (situacao: string) => {
  const map: Record<string, string> = { 'A': 'is-success', 'I': 'is-warning', 'E': 'is-danger' };
  return ['tag', map[situacao] || 'is-light'];
};

const getLabelSituacao = (situacao: string) => {
  const map: Record<string, string> = { 'A': 'Ativo', 'I': 'Inativo', 'E': 'Excluído' };
  return map[situacao] || situacao;
};
</script>

<style scoped>
.table-fixed { table-layout: fixed; width: 100%; }
.table td, .table th { vertical-align: middle !important; }
.truncate { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.table-container-fixed { min-height: 550px; }
.dropdown-item { cursor: pointer; }
</style>