<template>
  <nav class="navbar is-fixed-top">
    <div class="container">
      <div class="navbar-brand">
        <router-link class="navbar-item" to="/">
          <img src="../assets/images/logo.svg" alt="Logo">
        </router-link>
      </div>

      <div class="navbar-menu" :class="{ 'is-active': menuAtivo }">
        <div class="navbar-end">
          <template v-if="route.path === '/'">
            <a class="navbar-item nav-link" href="#inicio">Início</a>
            <a class="navbar-item nav-link" href="#funcionalidades">Funcionalidades</a>
            <a class="navbar-item nav-link" href="#planos">Planos</a>
            <a class="navbar-item nav-link" href="#contato">Contato</a>
          </template>
          <div class="navbar-item">
            <div class="buttons">
              <a v-if="!token" class="button is-dark is-rounded" @click="iniciarLogin">
                <strong>ENTRAR</strong>
              </a>

              <div v-else class="navbar-item has-dropdown is-hoverable container-conta">
                <a class="nav-link label-conta">
                  <i class="fas fa-user-circle mr-2"></i>
                  <span>Minha Conta</span>
                </a>

                <div class="navbar-dropdown is-right">
                  <router-link class="navbar-item" to="/produtos">Painel de Produtos</router-link>
                  <router-link class="navbar-item" to="/vendas">Minhas Vendas</router-link>
                  <hr class="navbar-divider">
                  <a class="navbar-item has-text-danger" @click="logout">Sair</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup lang="ts">
import { ref, watch, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { loginNoBling } from '../services/auth'; 

const route = useRoute();
const router = useRouter();
const menuAtivo = ref(false);
const token = ref<string | null>(null);

const atualizarStatusSessao = () => {
  token.value = localStorage.getItem('bling_access_token');
};

// Função para limpar o estado local sem precisar chamar o logout do servidor de novo
const limparSessaoLocal = () => {
  localStorage.removeItem('bling_access_token');
  localStorage.removeItem('usuario_logado');
  token.value = null;
  
  // Se o usuário estiver em uma página que exige login, redireciona para a home
  if (route.meta.requiresAuth) {
    router.push('/');
  }
};

onMounted(async () => {
  // 1. Checagem imediata pelo LocalStorage (visual rápido)
  atualizarStatusSessao();

  // 2. Validação Real (Sincronização com o Cookie do Backend)
  if (token.value) {
    try {
      const response = await fetch('http://localhost:88/api/auth/check', {
        method: 'GET',
        credentials: 'include' // Obrigatório para enviar o cookie HttpOnly
      });

      if (!response.ok) {
        // Se o PHP retornar 401 ou erro, o cookie expirou.
        limparSessaoLocal();
      }
    } catch (error) {
      console.error("Erro ao validar sessão no carregamento:", error);
    }
  }
});

watch(() => route.path, () => {
  atualizarStatusSessao();
  menuAtivo.value = false;
});

const iniciarLogin = () => loginNoBling();

const logout = async () => {
  try {
    // 1. Avisa o PHP para deletar o cookie
    await fetch('http://localhost:88/api/auth/logout', { 
      method: 'POST',
      credentials: 'include'
    });
  } catch (e) {
    console.error("Erro ao avisar servidor do logout:", e);
  } finally {
    // 2. Limpa o front independente do sucesso da rede
    limparSessaoLocal();
    router.push('/');
  }
};
</script>

<style scoped>
.navbar {
  min-height: 80px;
  box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
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

.navbar-dropdown {
  border-radius: 8px;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
}

.navbar-dropdown .navbar-item {
  padding: 0.7rem 1.5rem;
  font-size: 0.95rem;
  transition: background 0.2s ease;
}

.navbar-dropdown .navbar-item:hover {
  background-color: #f5f5f5;
}

.container-conta {
  padding: 0 !important;
  height: 80px;
  display: flex !important;
  align-items: center !important;
}

.label-conta {
  display: flex !important;
  align-items: center !important;
  justify-content: center;
  height: 100%;
  padding-top: 0 !important;
  padding-bottom: 0 !important;
  line-height: 1 !important;
}

.label-conta i {
  font-size: 1.2rem;
  display: inline-block;
  vertical-align: middle;
  margin-top: -2px;
}

@media screen and (max-width: 1023px) {
  .navbar-menu {
    background-color: white;
  }
}
</style>