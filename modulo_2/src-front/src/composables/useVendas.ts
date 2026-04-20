import { ref } from 'vue';

interface Venda {
  id: number;
  numero: string;
  nomeCliente: string;
  dataVenda: string;
  totalComDesconto: number;
  situacao: string;
}

interface RespostaVendas {
  data: Venda[];
  pagina: number;
  temMais: boolean;
}

export function useVendas() {
  const vendas = ref<RespostaVendas>({
    data: [],
    pagina: 1,
    temMais: false
  });

  const loading = ref(false);

  const filtros = ref({
    cliente: '',
    dataInicio: '',
    dataFim: '',
    ordem: 'dataVenda'
  });

  const buscarVendas = async () => {
    loading.value = true;
    try {
      const queryParams = new URLSearchParams({
        cliente: filtros.value.cliente,
        dataInicio: filtros.value.dataInicio,
        dataFim: filtros.value.dataFim,
        ordem: filtros.value.ordem,
        pagina: vendas.value.pagina.toString(),
        limite: '10'
      }).toString();

      const response = await fetch(`http://localhost:88/index.php/api/vendas?${queryParams}`, {
        credentials: 'include'
      });

      const json = await response.json();
      const dados = json.data;

      vendas.value = {
        data: dados.data || [],
        pagina: Number(dados.pagina) || 1,
        temMais: !!dados.temMais
      };

    } catch (error) {
      console.error("Erro ao buscar vendas:", error);
    } finally {
      loading.value = false;
    }
  };

  const trocarPagina = (novaPagina: number) => {
    vendas.value.pagina = novaPagina;
    buscarVendas();
  };

  const aplicarFiltros = () => {
    vendas.value.pagina = 1; // Sempre volta para a página 1 em nova busca
    buscarVendas();
  };

  const excluirVenda = async (id: number, confirmar = true): Promise<boolean> => {
    if (confirmar && !confirm("Deseja excluir?")) return false;

    try {
      const response = await fetch(`http://localhost:88/index.php/api/vendas/destroy?id=${id}`, {
        method: 'DELETE',
        credentials: 'include'
      });

      if (response.ok) {
        vendas.value.data = vendas.value.data.filter(v => v.id !== id);
        return true; // Retorno explícito
      }
      return false; // Retorno explícito
    } catch (error) {
      return false; // Retorno explícito
    }
  };

  return {
    vendas,
    loading,
    filtros,
    buscarVendas,
    trocarPagina,
    aplicarFiltros,
    excluirVenda
  };
}