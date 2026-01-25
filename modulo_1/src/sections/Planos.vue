<template>
  <section class="section" id="planos">
    <div class="container has-text-centered mb-6">
      <h2 class="title is-2 has-text-weight-bold">Nossos Planos</h2>
      <div class="field has-addons is-justify-content-center mt-5">
        <p class="control">
          <button class="button is-rounded px-6" :class="{ 'is-dark': !isAnual }" @click="isAnual = false">Mensal</button>
        </p>
        <p class="control">
          <button class="button is-rounded px-6" :class="{ 'is-dark': isAnual }" @click="isAnual = true">Anual</button>
        </p>
      </div>
    </div>

    <div class="container">
      <div class="columns is-centered is-variable is-8 is-vcentered">
        <div v-for="plano in planosMock" :key="plano.titulo" class="column is-4">
          <CardPlano 
            :titulo="plano.titulo"
            :preco="isAnual ? plano.precoAnual : plano.precoMensal"
            :periodo="isAnual ? 'ano' : 'mês'"
            :funcionalidades="plano.funcionalidades"
            :destaque="plano.destaque"
          />
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import CardPlano from '../components/CardPlano.vue'; 

const isAnual = ref(false);

const planosMock = [
  { titulo: "Essencial", precoMensal: 49, precoAnual: 468, funcionalidades: ["Acesso básico", "1 Usuário", "Suporte via e-mail"], destaque: false },
  { titulo: "Pro", precoMensal: 99, precoAnual: 948, funcionalidades: ["Acesso completo", "5 Usuários", "Suporte 24h", "Relatórios avançados", "API de Integração"], destaque: true },
  { titulo: "Business", precoMensal: 199, precoAnual: 1908, funcionalidades: ["Multi-empresas", "Usuários ilimitados", "Consultoria dedicada", "Treinamento VIP"], destaque: false }
];
</script>