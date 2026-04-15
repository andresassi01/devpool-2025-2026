import { createRouter, createWebHistory } from 'vue-router'
import Callback from '../views/Callback.vue'
import Landingpage from '../views/LandingPage.vue'
import Produtos from '../views/Produtos.vue'
import ProdutoForm from '../components/ProdutoForm.vue'
// Importar aqui a sua futura View de Vendas
// import Vendas from '../views/Vendas.vue' 

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
    meta: { requiresAuth: true } // Marca que precisa de login
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
  }
  
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to, _, next) => {
  const token = localStorage.getItem('bling_access_token');

  // Verifica se a rota destino tem a marcação de 'requiresAuth'
  if (to.matched.some(record => record.meta.requiresAuth) && !token) {
    next({ name: 'home' });
  } else {
    next();
  }
});

export default router