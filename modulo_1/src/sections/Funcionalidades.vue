<template>
  <section class="section" id="funcionalidades">
    <div class="container">
      <div class="columns is-multiline">
        <div v-for="feature in funcionalidadesMock" :key="feature.titulo" class="column is-4">
          <div class="feature-card">
            <div class="icon-wrapper mb-4">
              <span class="icon is-large has-text-success">
                <i :class="[feature.icone, 'fa-3x']"></i>
              </span>
            </div>
            <h3 class="title is-4 mb-3">{{ feature.titulo }}</h3>
            <p class="has-text-grey">{{ feature.descricao }}</p>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
const funcionalidadesMock = [
  { titulo: "Gestão Financeira", descricao: "Controle de fluxo de caixa, contas a pagar e receber com relatórios automatizados", icone: "fas fa-chart-pie" },
  { titulo: "Controle de Estoque", descricao: "Gestão inteligente de inventário com alertas de reposição e histórico de movimentações.", icone: "fas fa-boxes" },
  { titulo: "Emissão de Notas", descricao: "Emita notas fiscais de produto e serviço diretamente pelo sistema com um clique.", icone: "fas fa-file-invoice-dollar" },
  { titulo: "CRM de Vendas", descricao: "Gerencie seu funil de vendas e mantenha o histórico completo de contato com seus clientes.", icone: "fas fa-users" },
  { titulo: "Segurança de Dados", descricao: "Backups diários automáticos e criptografia de ponta a ponta para seus dados.", icone: "fas fa-shield-alt" },
  { titulo: "Suporte Especializado", descricao: "Time de suporte pronto para atender via chat ou ticket em horário comercial.", icone: "fas fa-headset" }
];
</script>

<style scoped>
.feature-card {
  padding: 2.5rem;
  background: #fff;
  border-radius: 12px;
  height: 100%;
  border: 1px solid #f0f0f0;
  text-align: center;
  transition: all 0.3s ease;
}

.feature-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 15px 30px rgba(0,0,0,0.08);
  border-color: #2ecc71;
}

.icon-wrapper {
  display: flex;
  justify-content: center;
  align-items: center;
}
</style>