<template>
  <div class="section has-text-centered">
    <div class="container" style="max-width: 600px; margin: 0 auto;">

      <div v-if="loading">
        <h2 class="title is-4">Finalizando integração com Bling...</h2>
        <button class="button is-loading is-large is-ghost">Processando</button>
        <p>Estamos estabelecendo uma conexão segura.</p>
      </div>

      <div v-else-if="sucesso" class="notification is-success">
        <h2 class="title is-5">Conexão realizada com sucesso!</h2>
        <p>Redirecionando para o painel...</p>
      </div>

      <div v-else class="notification is-danger">
        <h2 class="title is-5">Erro na Autenticação</h2>
        <p>{{ mensagemErro }}</p>
        <button class="button is-light mt-3" @click="router.push('/')">Tentar novamente</button>
      </div>

    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();

const loading = ref(true);
const sucesso = ref(false);
const mensagemErro = ref('');

onMounted(async () => {
  const code = route.query.code as string;
  const state = route.query.state as string;
  const savedState = localStorage.getItem('auth_state');

  // 1. Validação de Segurança básica
  if (!state || state !== savedState) {
    mensagemErro.value = 'Estado de segurança inválido. Inicie o login novamente.';
    loading.value = false;
    return;
  }

  if (!code) {
    router.push('/');
    return;
  }

  try {
    // 2. ENVIANDO PARA O PHP (Porta 88)
    const resposta = await fetch('http://localhost:88/api/auth/blingToken', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ code: code }),
      // CRITICAL: Permite que o navegador armazene o cookie HttpOnly enviado pelo PHP
      credentials: 'include' 
    });

    const dados = await resposta.json();

    if (resposta.ok) {
      // 3. SALVANDO O TOKEN REAL 
      // O PHP deve retornar o access_token no corpo da resposta
      const token = dados.data?.access_token || dados.access_token;
      
      if (token) {
        localStorage.setItem('bling_access_token', token);
      }

      // Mantemos a flag de logado para compatibilidade
      localStorage.setItem('usuario_logado', 'true');
      
      sucesso.value = true;
      loading.value = false;
      
      // Limpa o state de segurança usado
      localStorage.removeItem('auth_state');

      // 4. Redirecionamento
      setTimeout(() => {
        router.push('/produtos'); 
      }, 1500);
      
    } else {
      throw new Error(dados.message || 'Erro ao processar token no servidor.');
    }
  } catch (err: any) {
    console.error("Erro no Callback:", err);
    mensagemErro.value = err.message || 'Não foi possível conectar ao servidor PHP.';
    loading.value = false;
  }
});
</script>