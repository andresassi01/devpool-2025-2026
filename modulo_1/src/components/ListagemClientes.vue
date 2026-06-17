<template>
  <div class="box mt-5 table-container-fixed">
    <div v-if="isLoading" class="has-text-centered my-5">
      <button class="button is-loading is-ghost">Carregando</button>
    </div>

    <table class="table is-fullwidth is-striped is-hoverable table-fixed">
      <thead>
        <tr>
          <th style="width: 40px;">
            <input type="checkbox" @change="alternarTodos" :checked="selecionados.length === clientes.length && clientes.length > 0">
          </th>
          <th style="width: 80px;">ID</th>
          <th>Nome do Cliente</th>
          <th style="width: 100px;" class="has-text-centered">Ações</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="cliente in (clientes || [])" :key="cliente.id">
          <td><input type="checkbox" :value="cliente.id" v-model="selecionados"></td>
          <td><strong>#{{ cliente.id }}</strong></td>
          <td class="truncate" :title="cliente.nome">{{ cliente.nome || 'Sem nome' }}</td>
          <td class="has-text-centered">
            <div class="buttons is-centered">
              <button class="button is-small is-ghost" @click="$emit('visualizar', cliente.id)" title="Editar">
                <span class="icon"><i class="fas fa-edit"></i></span>
              </button>
              <button class="button is-small is-ghost has-text-danger" @click="$emit('excluir', cliente.id)" title="Excluir">
                <span class="icon"><i class="fas fa-trash"></i></span>
              </button>
            </div>
          </td>
        </tr>
        <tr v-if="(!clientes || clientes.length === 0) && !isLoading">
          <td colspan="4" class="has-text-centered py-6 is-size-5 has-text-grey">Nenhum cliente encontrado.</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';

const props = defineProps<{ clientes: any[], isLoading: boolean }>();
const emit = defineEmits(['visualizar', 'excluir', 'selecao-alterada']);

const selecionados = ref<number[]>([]);

// Reseta a seleção quando a lista de clientes muda
watch(() => props.clientes, () => { selecionados.value = []; });

// Notifica o componente pai sempre que a seleção mudar
watch(selecionados, (novosIds) => { emit('selecao-alterada', novosIds); });

const alternarTodos = (event: any) => {
  if (event.target.checked) selecionados.value = props.clientes.map((c: any) => c.id);
  else selecionados.value = [];
};
</script>

<style scoped>
.table-fixed { table-layout: fixed; width: 100%; }
.table td, .table th { vertical-align: middle !important; }
.truncate { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.table-container-fixed { min-height: 400px; }
.table.is-hoverable tbody tr:hover { background-color: #fafafa; }
</style>