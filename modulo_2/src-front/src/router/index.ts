import { createRouter, createWebHistory } from 'vue-router'
import Callback from '../views/Callback.vue'
import Landingpage from '../views/LandingPage.vue'
import Produtos from '../views/Produtos.vue'
import ProdutoForm from '../components/ProdutoForm.vue'
import VendaForm from '../components/VendaForm.vue'

const routes = [
  {
    path: '/',
    name: 'home',
    component: Landingpage
  },
  {
    path: '/callback',
    name: 'callback',
    component: Callback
  },
  {
    path: '/produtos',
    name: 'produtos',
    component: Produtos,
    meta: { requiresAuth: true }
  },
  {
    path: '/produtos/novo',
    name: 'produtonovo',
    component: ProdutoForm,
    meta: { requiresAuth: true }
  },
  {
    path: '/produtos/editar/:id',
    name: 'produtoeditar',
    component: ProdutoForm,
    props: true,
    meta: { requiresAuth: true }
  },
  {
    path: '/vendas',
    name: 'vendas',
    component: () => import('../views/Vendas.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/vendas/novo',
    name: 'VendaForm',
    component: VendaForm,
    meta: { requiresAuth: true }
  },
  {
    path: '/vendas/editar/:id',
    name: 'vendaeditar',
    component: VendaForm, 
    props: true, 
    meta: { requiresAuth: true }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach(async (to, _, next) => {
  const requiresAuth = to.matched.some(record => record.meta.requiresAuth);

  if (requiresAuth) {
    try {
      // Fazemos uma chamada rápida ao backend para validar o COOKIE
      const response = await fetch('http://localhost:88/index.php/api/auth/check', {
        method: 'GET',
        credentials: 'include' // OBRIGATÓRIO para enviar o cookie
      });

      if (response.ok) {
        next(); // Cookie existe e é válido
      } else {
        // Se o PHP retornar 401 ou 403, limpamos o storage por garantia e barramos
        localStorage.removeItem('bling_access_token');
        next({ name: 'home' });
      }
    } catch (error) {
      console.error("Erro na validação de sessão:", error);
      next({ name: 'home' });
    }
  } else {
    next();
  }
});

export default router