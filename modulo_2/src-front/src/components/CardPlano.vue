<template>
  <div class="card" :class="{ 'card-destaque': destaque, 'plan-card-normal': !destaque }">
    <div class="card-content p-6 has-text-centered">
      <span v-if="destaque" class="tag is-success is-medium mb-4 has-text-weight-bold">RECOMENDADO</span>
      <p class="title is-3" :class="{'has-text-white': destaque}">{{ titulo }}</p>
      
      <div class="price-wrapper mb-5">
        <p class="subtitle is-2 mb-0" :class="{'has-text-white': destaque}">
          <span class="is-size-5">R$</span> {{ preco }}
        </p>
        <p class="is-size-6" :class="destaque ? 'has-text-white' : 'has-text-grey'">
          por {{ periodo }}
        </p>
      </div>

      <ul class="mb-6 has-text-left pricing-list">
        <li v-for="item in funcionalidades" :key="item" class="mb-3 is-flex is-align-items-center">
          <span class="icon is-small mr-3 has-text-success">
            <i class="fas fa-check"></i>
          </span>
          <span :class="{'has-text-white': destaque}">{{ item }}</span>
        </li>
      </ul>

      <a class="button is-fullwidth is-large" 
         :class="destaque ? 'is-white is-outlined' : 'is-dark'" 
         href="#contato">
        <strong>Assinar Agora</strong>
      </a>
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  titulo: string;
  preco: number;
  periodo: string;
  funcionalidades: string[];
  destaque: boolean;
}>();
</script>

<style scoped>
.plan-card-normal {
  height: 100%;
  border-radius: 20px;
  border: 1px solid #eee;
  transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.plan-card-normal:hover {
  transform: translateY(-10px);
  box-shadow: 0 20px 40px rgba(0,0,0,0.08);
}

.card-destaque {
  background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
  border-radius: 20px;
  transform: scale(1.08); 
  box-shadow: 0 25px 50px rgba(39, 174, 96, 0.3);
  z-index: 2;
  transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.card-destaque:hover {
  transform: scale(1.11) translateY(-5px);
}

.pricing-list {
  list-style: none;
  margin-left: 0;
}

@media screen and (max-width: 1023px) {
  .card-destaque {
    transform: scale(1);
    margin: 3rem 0;
  }
  .card-destaque:hover {
    transform: translateY(-5px);
  }
}
</style>