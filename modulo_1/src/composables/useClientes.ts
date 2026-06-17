import { ref } from 'vue';

// Centralizamos a base para facilitar a mudança para a porta do Go (ex: 8080)
const API_BASE_URL = 'http://localhost:8080/v1/clientes';

export function useClientes() {
  const clientes = ref({
    data: [],
    pagina: 1,
    temMais: false
  });

  const loading = ref(false);
  const filtros = ref({
    nome: '',
    ordem: 'nome'
  });

  const buscarClientes = async () => {
    loading.value = true;
    try {
      const params = new URLSearchParams({
        pagina: clientes.value.pagina.toString(),
        nome: filtros.value.nome,
        ordem: filtros.value.ordem
      });

      const response = await fetch(`${API_BASE_URL}?${params.toString()}`);
      const result = await response.json();

      // Ajuste se o seu Go retornar o array direto ou dentro de um objeto
      clientes.value.data = result.data || result || [];
      clientes.value.temMais = result.temMais || false;
    } catch (error) {
      console.error("Erro ao buscar clientes no Go:", error);
    } finally {
      loading.value = false;
    }
  };

  const trocarPagina = (novaPagina: number) => {
    clientes.value.pagina = novaPagina;
    buscarClientes();
  };

  const excluirCliente = async (id: number) => {
    try {
      const response = await fetch(`${API_BASE_URL}/${id}`, {
        method: 'DELETE'
      });

      if (response.status === 204) {
        return { success: true };
      }

      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.error || "Erro ao excluir");
      }

      return await response.json();
    } catch (error) {
      throw error;
    }
  };

  const salvarCliente = async (dados: { id?: number, nome: string }) => {
    const isEdicao = !!dados.id;
    // Padrão REST: POST para criar, PUT para atualizar
    const url = isEdicao ? `${API_BASE_URL}/${dados.id}` : API_BASE_URL;
    const method = isEdicao ? 'PUT' : 'POST';

    const response = await fetch(url, {
      method: method,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(dados)
    });
    return await response.json();
  };

  const buscarClientePorId = async (id: string) => {
    const response = await fetch(`${API_BASE_URL}/${id}`);
    return await response.json();
  };

  return {
    clientes,
    loading,
    filtros,
    buscarClientes,
    trocarPagina,
    excluirCliente,
    salvarCliente,
    buscarClientePorId
  };
}