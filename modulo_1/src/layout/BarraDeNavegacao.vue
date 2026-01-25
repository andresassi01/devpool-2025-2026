<template>
  <nav class="navbar is-fixed-top" role="navigation" aria-label="main navigation">
    <div class="container">
      <div class="navbar-brand">
        <a class="navbar-item logo-container" href="#">
          <img class="logo-custom" src="../assets/images/logo.png" alt="Logo">
        </a>

        <a role="button" class="navbar-burger" :class="{ 'is-active': menuAtivo }" @click="menuAtivo = !menuAtivo">
          <span aria-hidden="true"></span>
          <span aria-hidden="true"></span>
          <span aria-hidden="true"></span>
        </a>
      </div>

      <div class="navbar-menu" :class="{ 'is-active': menuAtivo }">
        <div class="navbar-end">
          <a class="navbar-item nav-link" href="#inicio">Início</a>
          <a class="navbar-item nav-link" href="#funcionalidades">Funcionalidades</a>
          <a class="navbar-item nav-link" href="#planos">Planos</a>
          <a class="navbar-item nav-link" href="#contato">Contato</a>
          
          <div class="navbar-item">
            <div class="buttons">
              <a class="button is-dark is-rounded" href="/login">
                <strong>ENTRAR</strong>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup lang="ts">
import { ref } from 'vue';
const menuAtivo = ref(false);
</script>

<style scoped>
.navbar {
  min-height: 80px; 
  box-shadow: 0 2px 15px rgba(0,0,0,0.05);
  display: flex;
  align-items: center;
  background-color: white;
}

.nav-link {
  font-weight: 500;
  color: #4a4a4a !important;
  margin: 0 5px;
  transition: all 0.3s ease;
  position: relative;
  padding: 0.5rem 1.2rem;
}

.nav-link:hover {
  color: #27ae60 !important;
  background-color: transparent !important;
}

.nav-link::after {
  content: '';
  position: absolute;
  width: 0;
  height: 2px;
  bottom: 5px;
  left: 50%;
  background-color: #2ecc71; 
  transition: all 0.3s ease;
  transform: translateX(-50%);
}

.nav-link:hover::after {
  width: 60%;
}
.logo-custom {
  max-height: 4rem !important; 
  height: auto;
  width: auto;
}


.navbar-item img {
  max-height: 2.5rem;
}

@media screen and (max-width: 1023px) {
  .navbar-menu {
    background-color: white;
  }
}
</style>