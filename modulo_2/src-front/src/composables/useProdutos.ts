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

        // O front-end usa o token salvo no storage para produtos
        const token = localStorage.getItem('bling_access_token');
        if (!token) {
            router.push('/');
            return;
        }

        try {
            const params = new URLSearchParams({
                pagina: pagina.value.toString(),
                limite: LIMITE_POR_PAGINA.toString()
            });

            if (filtrosAtivos.nome) params.append('nome', filtrosAtivos.nome);
            if (filtrosAtivos.sku) params.append('codigo', filtrosAtivos.sku);
            if (filtrosAtivos.situacao) params.append('criterio', filtrosAtivos.situacao);
            if (filtrosAtivos.dataInicio) params.append('dataAlteracaoInicial', filtrosAtivos.dataInicio);
            if (filtrosAtivos.dataFim) params.append('dataAlteracaoFinal', filtrosAtivos.dataFim);

            // Chamada direta para o Bling
            const resposta = await fetch(`https://www.bling.com.br/Api/v3/produtos?${params.toString()}`, {
                method: 'GET',
                headers: { 
                    'Accept': 'application/json'
                },
                credentials: 'include'
            });

            const dados = await resposta.json();

            if (resposta.ok) {
                produtos.value = dados.data || [];
                temMaisPaginas.value = produtos.value.length === LIMITE_POR_PAGINA;
            } else if (resposta.status === 401) {
                localStorage.removeItem('bling_access_token');
                router.push('/');
            } else if (resposta.status === 404) {
                produtos.value = [];
                temMaisPaginas.value = false;
            } else {
                throw new Error(dados.error?.description || 'Erro ao buscar produtos');
            }
        } catch (err: any) {
            erro.value = true;
           // mensagemErro.value = err.message;
            console.error("Erro na busca de produtos:", err.message);
            produtos.value = [];
            temMaisPaginas.value = false;
        } finally {
            carregando.value = false;
            sessionStorage.setItem('ultimo_estado_filtro', JSON.stringify({
                filtros: filtrosAtivos,
                pagina: pagina.value
            }));
        }
    };

    const executarAlteracaoSituacao = async (id: number | null, novaSituacao: 'A' | 'E', emMassa = false) => {
        const token = localStorage.getItem('bling_access_token');
        if (!token) return;

        carregando.value = true;
        const ids = emMassa ? [...produtosSelecionados.value] : [id];

        try {
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
        Object.assign(filtrosAtivos, novosFiltros);
        pagina.value = 1;
        produtosSelecionados.value = [];
        buscarProdutos();
    };

    const handleLimpar = () => {
        Object.assign(filtrosAtivos, filtrosIniciais);
        pagina.value = 1;
        produtosSelecionados.value = [];
        sessionStorage.removeItem('ultimo_estado_filtro');
        buscarProdutos();
    };

    const trocarPagina = (novaPagina: number) => {
        if (novaPagina < 1 || (novaPagina > pagina.value && !temMaisPaginas.value)) return;
        pagina.value = novaPagina;
        produtosSelecionados.value = [];
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