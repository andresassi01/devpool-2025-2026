import { ref, reactive, computed } from 'vue';
import { useRouter } from 'vue-router';

interface Produto {
    id: number;
    codigo: string;
    nome: string;
    preco: number;
    situacao: string;
}

export function useProdutos() {
    const router = useRouter();

    const produtos = ref<Produto[]>([]);
    const carregando = ref(false);
    const erro = ref(false);
    const mensagemErro = ref('');
    const pagina = ref(1);
    const temMaisPaginas = ref(false);
    const LIMITE_POR_PAGINA = 10;
    const produtosSelecionados = ref<number[]>([]);
    const dropdownAberto = ref<number | null>(null);

    const modalAtivo = ref(false);
    const idParaExcluir = ref<number | null>(null);
    const isMassa = ref(false);
    const mostrarFeedback = ref(false);
    const mensagemFeedback = ref('');
    const tipoFeedback = ref<'erro' | 'sucesso'>('sucesso');
    const tipoAcao = ref<'excluir' | 'restaurar'>('excluir');

    const filtrosIniciais = {
        nome: '',
        sku: '',
        dataInicio: '',
        dataFim: '',
        situacao: '1'
    };
    const filtrosAtivos = reactive({ ...filtrosIniciais });

    const selecionouTodos = computed(() => {
        return produtos.value.length > 0 && produtosSelecionados.value.length === produtos.value.length;
    });

    const mensagemModal = computed(() => {
        const acao = tipoAcao.value === 'restaurar' ? 'restaurar' : 'excluir';
        if (isMassa.value) {
            return `Deseja ${acao} os ${produtosSelecionados.value.length} produtos selecionados?`;
        }
        return `Deseja ${acao} este produto?`;
    });

    const buscarProdutos = async () => {
        if (carregando.value) return;
        carregando.value = true;
        erro.value = false;
        dropdownAberto.value = null;

        try {
            // Sincronização exata dos nomes com o seu ProdutosController.php
            const queryParams = new URLSearchParams({
                pagina: pagina.value.toString(),
                limite: LIMITE_POR_PAGINA.toString(),
                nome: filtrosAtivos.nome,
                sku: filtrosAtivos.sku,         // No PHP: $_GET['sku']
                situacao: filtrosAtivos.situacao,   // No PHP: $_GET['situacao']
                dataInicio: filtrosAtivos.dataInicio, // No PHP: $_GET['dataInicio']
                dataFim: filtrosAtivos.dataFim        // No PHP: $_GET['dataFim']
            });

            const resposta = await fetch('http://localhost:88/index.php/api/produtos?' + queryParams.toString(), {
                method: 'GET',
                headers: { 'Accept': 'application/json' },
                credentials: 'include'
            });
            const dados = await resposta.json();

            if (resposta.ok) {
                // Atualiza a lista que vai para o ListagemProdutos.vue
                produtos.value = dados.data || [];
                temMaisPaginas.value = produtos.value.length === LIMITE_POR_PAGINA;
            } else if (resposta.status === 401) {
                router.push('/');
            } else {
                throw new Error(dados.message || 'Erro ao buscar produtos');
            }
        } catch (err: any) {
            erro.value = true;
            console.error("Erro na busca:", err.message);
            produtos.value = [];
        } finally {
            carregando.value = false;
            // Salva o estado para quando você voltar de uma edição
            sessionStorage.setItem('ultimo_estado_filtro', JSON.stringify({
                filtros: filtrosAtivos,
                pagina: pagina.value
            }));
        }
    };

    const executarAlteracaoSituacao = async (id: number | null, novaSituacao: 'A' | 'E', emMassa = false) => {
        carregando.value = true;
        const ids = emMassa ? [...produtosSelecionados.value] : [id];

        try {
            const token = localStorage.getItem('bling_access_token');

            const promises = ids.map(idAtual =>
                fetch(`https://www.bling.com.br/Api/v3/produtos/${idAtual}/situacoes`, {
                    method: 'PATCH',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ situacao: novaSituacao })
                })
            );

            await Promise.allSettled(promises);

            tipoFeedback.value = 'sucesso';
            mensagemFeedback.value = novaSituacao === 'A' ? 'Produto(s) restaurado(s)!' : 'Produto(s) excluído(s)!';
            mostrarFeedback.value = true;

            modalAtivo.value = false;
            produtosSelecionados.value = [];

            produtos.value = produtos.value.filter(p => !ids.includes(p.id));
            await buscarProdutos();

            setTimeout(() => { mostrarFeedback.value = false; }, 3000);
        } catch (err: any) {
            erro.value = true;
            mensagemErro.value = "Erro na operação: " + err.message;
        } finally {
            carregando.value = false;
        }
    };


    const handlePesquisa = (novosFiltros: any) => {
        // Mescla os filtros do componente filho para o estado global do composable
        Object.assign(filtrosAtivos, novosFiltros);
        pagina.value = 1; // Reseta página
        produtosSelecionados.value = []; // Limpa seleção
        buscarProdutos(); // Chama o PHP
    };

    const handleLimpar = () => {
        // 1. Volta os filtros para o estado inicial (vazio)
        Object.assign(filtrosAtivos, filtrosIniciais);

        // 2. Reseta paginação e seleção
        pagina.value = 1;
        produtosSelecionados.value = [];

        // 3. Limpa o cache da sessão para não voltar o filtro ao dar F5
        sessionStorage.removeItem('ultimo_estado_filtro');

        // 4. ESSENCIAL: Busca a lista limpa do servidor
        buscarProdutos();
    };

    const trocarPagina = (novaPagina: number) => {
        if (novaPagina < 1 || (novaPagina > pagina.value && !temMaisPaginas.value)) return;
        pagina.value = novaPagina;
        produtosSelecionados.value = [];
        // 5. Busca os dados da nova página mantendo os filtros ativos
        buscarProdutos();
    };

    const alternarTodos = () => {
        produtosSelecionados.value = selecionouTodos.value ? [] : produtos.value.map(p => p.id);
    };

    const handleUpdateSelecionados = (id: number) => {
        const index = produtosSelecionados.value.indexOf(id);
        if (index === -1) produtosSelecionados.value.push(id);
        else produtosSelecionados.value.splice(index, 1);
    };

    const alternarDropdown = (id: number) => {
        dropdownAberto.value = dropdownAberto.value === id ? null : id;
    };

    const fecharDropdownExterno = (event: MouseEvent) => {
        const target = event.target as HTMLElement;
        if (!target.closest('.dropdown-trigger')) dropdownAberto.value = null;
    };

    const fecharModal = () => {
        if (carregando.value) return;
        modalAtivo.value = false;
        idParaExcluir.value = null;
        isMassa.value = false;
    };

    const prepararExclusaoIndividual = (id: number) => {
        idParaExcluir.value = id;
        tipoAcao.value = 'excluir';
        isMassa.value = false;
        modalAtivo.value = true;
    };

    const prepararExclusaoMassa = () => {
        if (produtosSelecionados.value.length === 0) return;
        tipoAcao.value = 'excluir';
        isMassa.value = true;
        modalAtivo.value = true;
    };

    const prepararRestauracaoIndividual = (id: number) => {
        idParaExcluir.value = id;
        tipoAcao.value = 'restaurar';
        isMassa.value = false;
        modalAtivo.value = true;
    };

    const prepararRestauracaoMassa = () => {
        if (produtosSelecionados.value.length === 0) return;
        tipoAcao.value = 'restaurar';
        isMassa.value = true;
        modalAtivo.value = true;
    };

    const confirmarAcaoModal = () => {
        const situacao = tipoAcao.value === 'restaurar' ? 'A' : 'E';
        executarAlteracaoSituacao(idParaExcluir.value, situacao, isMassa.value);
    };

    return {
        produtos, carregando, erro, mensagemErro, pagina, temMaisPaginas,
        produtosSelecionados, dropdownAberto, filtrosAtivos, modalAtivo,
        mostrarFeedback, mensagemFeedback, tipoFeedback, mensagemModal, selecionouTodos, tipoAcao,
        buscarProdutos, handlePesquisa, handleLimpar, trocarPagina, alternarTodos,
        handleUpdateSelecionados, alternarDropdown, fecharDropdownExterno,
        fecharModal, confirmarAcaoModal, prepararExclusaoIndividual, prepararExclusaoMassa, prepararRestauracaoIndividual, prepararRestauracaoMassa
    };
}