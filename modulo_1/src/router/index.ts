import { createRouter, createWebHistory } from 'vue-router'
import Callback from '../views/Callback.vue'
import Landingpage from '../views/LandingPage.vue'
import Produtos from '../views/Produtos.vue'
import ProdutoForm from '../components/ProdutoForm.vue'

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
    component: Produtos
  },
  {
    path: '/produtos/novo',
    name: 'produtonovo',
    component: ProdutoForm
  },
  {
    path: '/produtos/editar/:id',
    name: 'produtoeditar',
    component: ProdutoForm,
    props: true
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to, _, next) => {
  const token = localStorage.getItem('bling_access_token');
  const rotasPublicas = ['home', 'callback'];

  if (!rotasPublicas.includes(to.name as string) && !token) {
    next({ name: 'home' });
  } else {
    next();
  }
});

export default router