<template>
  <BarraDeNavegacao />
  
  <main>
    <Hero id="inicio" />
    <Funcionalidades id="funcionalidades" />
    <Planos id="planos" />
    <Contato id="contato" />
  </main>

  <Rodape />
</template>

<script setup lang="ts">
import BarraDeNavegacao from './layout/BarraDeNavegacao.vue';
import Rodape from './layout/Rodape.vue';
import Hero from './sections/Hero.vue';
import Funcionalidades from './sections/Funcionalidades.vue';
import Planos from './sections/Planos.vue';
import Contato from './sections/Contato.vue';
</script>