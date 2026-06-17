<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Models\Venda;
use App\Middleware\AuthBlingMiddleware;

/**
 * Controller responsável pela gestão de vendas no banco de dados local
 * e integração de busca de produtos via API externa (Bling).
 */
class VendasController extends Controller
{
    /**
     * Aplica o Middleware de autenticação para todas as rotas de vendas.
     */
    public function __construct()
    {
        $this->middleware(AuthBlingMiddleware::class);
    }

    /**
     * Lista as vendas com filtros de cliente, período e ordenação.
     * Implementa paginação lógica para o Frontend.
     * * @return jsonResponse
     */
    public function index()
    {
        try {
            $cliente = $_GET['cliente'] ?? null;
            $dataInicio = $_GET['dataInicio'] ?? null;
            $dataFim = $_GET['dataFim'] ?? null;
            $ordem = $_GET['ordem'] ?? 'dataVenda';

            $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
            $limite = 10;
            $offset = ($pagina - 1) * $limite;
            $limiteParaCheck = $limite + 1;

            // Validação de consistência de datas
            if (($dataInicio && !$dataFim) || (!$dataInicio && $dataFim)) {
                return $this->jsonResponse(null, 'Informe o período completo (Início e Fim).', 400);
            }
            if ($dataInicio && $dataFim && strtotime($dataInicio) > strtotime($dataFim)) {
                return $this->jsonResponse(null, 'A data inicial não pode ser maior que a final.', 400);
            }

            $sql = "SELECT id, numero, nomeCliente, dataVenda, totalComDesconto, situacao FROM vendas WHERE 1=1";
            $params = [];

            if ($cliente) {
                $sql .= " AND nomeCliente LIKE :cliente";
                $params['cliente'] = "%$cliente%";
            }

            if ($dataInicio && $dataFim) {
                $sql .= " AND dataVenda BETWEEN :inicio AND :fim";
                $params['inicio'] = $dataInicio;
                $params['fim'] = $dataFim;
            }

            $colunasPermitidas = ['dataVenda', 'totalComDesconto', 'nomeCliente'];
            $colunaOrdem = in_array($ordem, $colunasPermitidas) ? $ordem : 'dataVenda';
            $sql .= " ORDER BY $colunaOrdem DESC, id DESC LIMIT $limiteParaCheck OFFSET $offset";

            $db = new Venda();
            $vendas = $db->query($sql, $params);

            $temMais = count($vendas) > $limite;
            if ($temMais) array_pop($vendas);

            return $this->jsonResponse([
                'data' => $vendas,
                'temMais' => $temMais,
                'pagina' => $pagina
            ], 'Sucesso');
        } catch (\Exception $e) {
            return $this->jsonResponse(null, 'Erro ao buscar vendas: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Registra uma nova venda e seus itens no banco de dados.
     * Delega o cálculo final de totais para o Model (Regra de Negócio).
     * * @return jsonResponse
     */
    public function store()
    {
        $this->validateRequestMethods(['POST']);
        $data = $this->getRequestData();

        if (empty($data['nomeCliente']) || empty($data['itens'])) {
            return $this->jsonResponse([], 'Dados incompletos.', 400);
        }

        try {
            $vendaModel = new Venda();
            $dadosVenda = [
                'numero' => $data['numero'] ?? null,
                'nomeCliente' => $data['nomeCliente'],
                'dataVenda'   => $data['dataVenda'] ?? date('Y-m-d'),
                'percentualDesconto' => (float)($data['percentualDesconto'] ?? 0)
            ];

            $id = $vendaModel->salvarVendaCompleta($dadosVenda, $data['itens']);
            return $this->jsonResponse(['id' => $id], 'Venda cadastrada com sucesso!', 201);
        } catch (\Exception $e) {
            return $this->jsonResponse([], 'Erro ao salvar venda: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Busca produtos diretamente na API do Bling para preenchimento da venda.
     * * @return jsonResponse
     */
    public function buscarProdutosNoBling()
    {
        $nome = $_GET['nome'] ?? '';
        if (strlen($nome) < 3) {
            return $this->jsonResponse([], 'Digite pelo menos 3 caracteres');
        }

        // O token é recuperado do cookie/sessão validado pelo Middleware
        $accessToken = $_COOKIE['bling_token'] ?? $_SESSION['bling_access_token'] ?? '';

        $url = BLING_API_URL . "/produtos?nome=" . urlencode($nome) . "&limite=10";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$accessToken}",
            "Accept: application/json"
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $dados = json_decode($response, true);
       
        if ($httpCode === 200) {
            $formatados = array_map(fn($item) => [
                'id' => $item['id'],
                'nome' => $item['nome'],
                'preco' => $item['preco']
            ], $dados['data'] ?? []);

            return $this->jsonResponse($formatados, 'Sucesso');
        }

        return $this->jsonResponse([], 'Erro ao buscar no Bling', $httpCode);
    }

    /**
     * Exibe detalhes de uma venda específica e seus itens.
     * * @param int|null $id
     * @return jsonResponse
     */
    public function show($id = null)
    {
        try {
            $id = $id ?? ($_GET['id'] ?? null);
            if (!$id) return $this->jsonResponse(null, 'ID não fornecido', 400);

            $vendaModel = new Venda();
            $venda = $vendaModel->query("SELECT * FROM vendas WHERE id = :id", ['id' => $id]);

            if (!$venda) return $this->jsonResponse(null, 'Venda não encontrada', 404);

            $itens = $vendaModel->query("SELECT * FROM vendas_itens WHERE venda_id = :id", ['id' => $id]);

            return $this->jsonResponse([
                'venda' => $venda[0],
                'itens' => $itens
            ], 'Sucesso');
        } catch (\Exception $e) {
            return $this->jsonResponse(null, 'Erro: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove uma venda e seus itens associados.
     * * @return jsonResponse
     */
    public function destroy()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) return $this->jsonResponse(null, 'ID não encontrado', 400);

        try {
            $model = new Venda();
            $model->excluirVendaCompleta($id);
            return $this->jsonResponse(null, 'Venda removida com sucesso!', 200);
        } catch (\Exception $e) {
            return $this->jsonResponse(null, 'Erro no banco: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Atualiza os dados de uma venda e sincroniza seus itens.
     * * @return jsonResponse
     */
    public function update()
    {
        $id = $_GET['id'] ?? null;
        $data = $this->getRequestData();

        if (!$id || empty($data['nomeCliente']) || empty($data['itens'])) {
            return $this->jsonResponse([], 'Dados incompletos.', 400);
        }

        try {
            $vendaModel = new Venda();
            $dadosVenda = [
                'nomeCliente' => $data['nomeCliente'],
                'dataVenda'   => $data['dataVenda'] ?? date('Y-m-d'),
                'percentualDesconto' => (float)($data['percentualDesconto'] ?? 0)
            ];

            $vendaModel->atualizarVenda($id, $dadosVenda, $data['itens']);
            return $this->jsonResponse(['id' => $id], 'Venda atualizada com sucesso!');
        } catch (\Exception $e) {
            return $this->jsonResponse([], 'Erro ao atualizar: ' . $e->getMessage(), 500);
        }
    }
}