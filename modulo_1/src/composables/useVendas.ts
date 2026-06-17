import { ref, computed } from 'vue';

// ==========================================
// 1. INTERFACES (Blindagem TypeScript)
// ==========================================

export interface ItemVenda {
  produto_id: number | null;
  nomeProduto: string;
  quantidade: number;
  precoUnitario: number;
}

export interface VendaFormState {
  nomeCliente: string;
  dataVenda: string;
  percentualDesconto: number;
  itens: ItemVenda[];
}

export interface VendaListagem {
  id: number;
  numero: string;
  nomeCliente: string;
  dataVenda: string;
  totalComDesconto: number;
  situacao: string;
}

interface RespostaVendas {
  data: VendaListagem[];
  pagina: number;
  temMais: boolean;
}

interface RetornoAPI {
  sucesso: boolean;
  mensagem?: string;
  dados?: any;
}

// ==========================================
// 2. COMPOSABLE CENTRAL
// ==========================================

export function useVendas() {
  const API_BASE = 'http://localhost:88/index.php/api/vendas';

  // --- ESTADOS DA LISTAGEM ---
  const vendas = ref<RespostaVendas>({ data: [], pagina: 1, temMais: false });
  const loading = ref(false);
  const filtros = ref({ cliente: '', dataInicio: '', dataFim: '', ordem: 'dataVenda' });

  // --- ESTADOS DO FORMULÁRIO ---
  const vendaAtiva = ref<VendaFormState>({
    nomeCliente: '',
    dataVenda: new Date().toISOString().substring(0, 10),
    percentualDesconto: 0,
    itens: []
  });
  const produtosSugeridos = ref<any[]>([]);
  const itemEmEspera = ref<ItemVenda>({ produto_id: null, nomeProduto: '', quantidade: 1, precoUnitario: 0 });
  const buscandoBling = ref(false);
  const enviandoForm = ref(false);

  // --- CÁLCULOS AUTOMÁTICOS (COMPUTED) ---
  
  /**
   * Calcula o subtotal somando (quantidade * precoUnitario) de cada item.
   */
  const valorSubtotal = computed(() => {
    return vendaAtiva.value.itens.reduce((acc, item) => acc + (item.quantidade * item.precoUnitario), 0);
  });

  /**
   * Calcula o total final aplicando o percentual de desconto sobre o subtotal.
   */
  const valorTotalFinal = computed(() => {
    const desconto = valorSubtotal.value * (vendaAtiva.value.percentualDesconto / 100);
    return valorSubtotal.value - desconto;
  });

  // ==========================================
  // 3. MÉTODOS DE LISTAGEM
  // ==========================================

  /**
   * Busca as vendas registradas com base nos filtros e paginação atuais.
   * Rota PHP: GET /api/vendas
   */
  const buscarVendas = async () => {
    loading.value = true;
    try {
      const queryParams = new URLSearchParams({
        ...filtros.value,
        pagina: vendas.value.pagina.toString(),
        limite: '10'
      }).toString();

      const response = await fetch(`${API_BASE}?${queryParams}`, { credentials: 'include' });
      const json = await response.json();
      
      vendas.value = {
        data: json.data?.data || [],
        pagina: Number(json.data?.pagina) || 1,
        temMais: !!json.data?.temMais
      };
    } catch (error) {
      console.error("Erro ao buscar vendas:", error);
    } finally {
      loading.value = false;
    }
  };

  /**
   * Executa a exclusão de uma venda.
   * Rota PHP: DELETE /api/vendas/destroy
   * @param id ID da venda
   * @param confirmar Define se o prompt nativo deve ser chamado (false para deleção em lote)
   */
  const excluirVenda = async (id: number, confirmar = true): Promise<boolean> => {
    if (confirmar && !confirm("Deseja excluir?")) return false;
    try {
      const response = await fetch(`${API_BASE}/destroy?id=${id}`, { method: 'DELETE', credentials: 'include' });
      if (response.ok) {
        vendas.value.data = vendas.value.data.filter(v => v.id !== id);
        return true;
      }
      return false;
    } catch (error) {
      return false;
    }
  };

  // ==========================================
  // 4. MÉTODOS DO FORMULÁRIO E BLING
  // ==========================================

  /**
   * Busca produtos no Bling via back-end a partir de um termo de 3 ou mais letras.
   * Rota PHP: GET /api/vendas/buscarProdutosNoBling
   * @param termo Nome do produto para buscar
   */
  const buscarProdutosNoBling = async (termo: string): Promise<RetornoAPI> => {
    buscandoBling.value = true;
    try {
      const res = await fetch(`${API_BASE}/buscarProdutosNoBling?nome=${termo}`, { credentials: 'include' });
      const dados = await res.json();
      produtosSugeridos.value = dados.data || [];
      return { sucesso: true, dados: produtosSugeridos.value };
    } catch (error) {
      console.error("Erro na busca Bling:", error);
      return { sucesso: false, mensagem: "Erro ao buscar produtos no Bling." };
    } finally {
      buscandoBling.value = false;
    }
  };

  /**
   * Salva a venda atual. Se houver ID, faz UPDATE, caso contrário faz STORE.
   * Envia o subtotal e totalComDesconto calculados no Front-end.
   * Rota PHP: POST /api/vendas/store OU POST /api/vendas/update
   * @param idEdicao (Opcional) ID da venda sendo editada
   */
  const persistirVenda = async (idEdicao?: string | string[]): Promise<RetornoAPI> => {
    enviandoForm.value = true;
    const url = idEdicao ? `${API_BASE}/update?id=${idEdicao}` : `${API_BASE}/store`;

    try {
      const payload = {
        ...vendaAtiva.value,
        subtotal: valorSubtotal.value,
        totalComDesconto: valorTotalFinal.value
      };

      const res = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
        credentials: 'include'
      });

      if (res.ok) return { sucesso: true };
      const json = await res.json();
      return { sucesso: false, mensagem: json.message || "Erro ao salvar venda" };
    } catch (error) {
      return { sucesso: false, mensagem: "Erro de conexão com o servidor." };
    } finally {
      enviandoForm.value = false;
    }
  };

  /**
   * Carrega os dados de uma venda existente para edição e preenche o estado reativo.
   * Rota PHP: GET /api/vendas/show
   * @param id ID da venda a ser recuperada
   */
  const carregarVendaParaEdicao = async (id: string | string[]) => {
    try {
      const res = await fetch(`${API_BASE}/show?id=${id}`, { credentials: 'include' });
      const json = await res.json();
      if (res.ok && json.data) {
        const v = json.data.venda;
        vendaAtiva.value = {
          nomeCliente: v.nomeCliente,
          dataVenda: v.dataVenda,
          percentualDesconto: parseFloat(v.percentualDesconto) || 0,
          itens: json.data.itens.map((i: any) => ({
            produto_id: i.produto_id,
            nomeProduto: i.nomeProduto,
            quantidade: i.quantidade,
            precoUnitario: parseFloat(i.precoUnitario)
          }))
        };
      }
    } catch (error) {
      console.error("Erro ao carregar venda:", error);
    }
  };

  // ==========================================
  // 5. FUNÇÕES UTILITÁRIAS E REGRAS DE NEGÓCIO
  // ==========================================

  const formatarMoeda = (valor: number) => {
    const num = Number(valor);
    if (isNaN(num)) return 'R$ 0,00';
    return num.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
  };

  const aplicarRegrasItem = (item: ItemVenda) => {
    if (item.quantidade < 1) item.quantidade = 1;
    if (item.precoUnitario < 0) item.precoUnitario = 0;
  };

  return {
    // Estados Listagem
    vendas, loading, filtros,
    // Estados Formulário
    vendaAtiva, produtosSugeridos, itemEmEspera, buscandoBling, enviandoForm,
    // Computed
    valorSubtotal, valorTotalFinal,
    // Ações Listagem
    buscarVendas, excluirVenda, trocarPagina: (p: number) => { vendas.value.pagina = p; buscarVendas(); },
    // Ações Formulário/Bling
    buscarProdutosNoBling, persistirVenda, carregarVendaParaEdicao,
    // Utilitários
    formatarMoeda, aplicarRegrasItem
  };
}