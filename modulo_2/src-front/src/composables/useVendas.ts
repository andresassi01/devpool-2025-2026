import { ref } from 'vue';

// Definindo a interface baseada na tabela MySQL
interface Venda {
  id: number;
  numero: string;
  nomeCliente: string;
  dataVenda: string;
  totalComDesconto: number;
  situacao: string;
}

export function useVendas() {
  // Agora dizemos que vendas é um array do tipo Venda
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
      const response = await fetch(`http://localhost:88/api/vendas?${params}`, {
        credentials: 'include'
      });
      const dados = await response.json();

      // O TS agora aceita atribuir os dados porque definimos o tipo no ref
      vendas.value = dados.data || [];
    } catch (error) {
      console.error("Erro ao buscar vendas:", error);
    } finally {
      loading.value = false;
    }
  };

  return { vendas, loading, filtros, buscarVendas };
}