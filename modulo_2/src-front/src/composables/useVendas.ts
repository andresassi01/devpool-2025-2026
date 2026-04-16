import { ref } from 'vue';

interface Venda {
  id: number;
  numero: string;
  nomeCliente: string;
  dataVenda: string;
  totalComDesconto: number;
  situacao: string;
}

export function useVendas() {
  const vendas = ref<Venda[]>([]);
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
      const params = new URLSearchParams(filtros.value).toString();
      
      const response = await fetch(`http://localhost:88/index.php/api/vendas?${params}`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json'
        },
        credentials: 'include'
      });

      if (!response.ok) {
        throw new Error(`Erro no servidor: ${response.status}`);
      }

      const dados = await response.json();

      vendas.value = dados.data || [];

    } catch (error) {
      console.error("Erro ao buscar vendas no banco local:", error);
      vendas.value = [];
    } finally {
      loading.value = false;
    }
  };

  return { 
    vendas, 
    loading, 
    filtros, 
    buscarVendas 
  };
}