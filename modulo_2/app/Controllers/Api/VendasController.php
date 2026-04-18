<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Models\Venda;

class VendasController extends Controller
{

    public function index()
    {
        try {
            // Capturar parâmetros
            $cliente = $_GET['cliente'] ?? null;
            $dataInicio = $_GET['dataInicio'] ?? null;
            $dataFim = $_GET['dataFim'] ?? null;
            $ordem = $_GET['ordem'] ?? 'dataVenda';

            // Paginação
            $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
            $limite = 10;
            $offset = ($pagina - 1) * $limite;

            // Validação de Datas
            if (($dataInicio && !$dataFim) || (!$dataInicio && $dataFim)) {
                return $this->jsonResponse(null, 'Informe o período completo (Início e Fim) para filtrar por data.', 400);
            }
            if ($dataInicio && $dataFim && strtotime($dataInicio) > strtotime($dataFim)) {
                return $this->jsonResponse(null, 'A data inicial não pode ser maior que a data final.', 400);
            }

            // Montar a SQL
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
            $sql .= " ORDER BY $colunaOrdem DESC, id DESC";

            // Adicionar Paginação na SQL 
            $sql .= " LIMIT $limite OFFSET $offset";

            $db = new Venda();
            $vendas = $db->query($sql, $params);

            // Lógica para o botão "Próximo" do Vue
            $temMais = count($vendas) === $limite;

            return $this->jsonResponse([
                'data' => $vendas,
                'temMais' => $temMais,
                'pagina' => $pagina
            ], 'Sucesso');
        } catch (\Exception $e) {
            return $this->jsonResponse(null, 'Erro ao buscar vendas: ' . $e->getMessage(), 500);
        }
    }


    public function store()
    {
        $this->validateRequestMethods(['POST']);
        $data = $this->getRequestData();

        if (empty($data['nomeCliente']) || empty($data['itens']) || count($data['itens']) === 0) {
            return $this->jsonResponse([], 'Dados incompletos: Nome do cliente e itens são obrigatórios.', 400);
        }

        $subtotal = 0;
        foreach ($data['itens'] as $item) {
            $subtotal += ($item['quantidade'] * $item['precoUnitario']);
        }

        $descontoPercentual = (float)($data['percentualDesconto'] ?? 0);
        $valorDesconto = $subtotal * ($descontoPercentual / 100);
        $totalFinal = $subtotal - $valorDesconto;

        try {
            $vendaModel = new Venda();

            $dadosVenda = [
                'numero' => $data['numero'] ?? null,
                'nomeCliente' => $data['nomeCliente'],
                'dataVenda'   => $data['dataVenda'] ?? date('Y-m-d'),
                'subtotal'    => $subtotal,
                'valorDesconto' => $valorDesconto,
                'percentualDesconto' => $descontoPercentual,
                'totalComDesconto' => $totalFinal,
                'situacao' => 'Em aberto'
            ];

            $id = $vendaModel->salvarVendaCompleta($dadosVenda, $data['itens']);

            return $this->jsonResponse(['id' => $id], 'Venda cadastrada com sucesso!', 201);
        } catch (\Exception $e) {
            return $this->jsonResponse([], 'Erro ao salvar venda: ' . $e->getMessage(), 500);
        }
    }

    public function buscarProdutosNoBling()
    {
        $nome = $_GET['nome'] ?? '';
        if (strlen($nome) < 3) {
            return $this->jsonResponse([], 'Digite pelo menos 3 caracteres');
        }

        $accessToken = $_COOKIE['bling_token'] ?? '';
        if (!$accessToken) {
            return $this->jsonResponse([], 'Não autenticado', 401);
        }

        $url = "https://www.bling.com.br/Api/v3/produtos?nome=" . urlencode($nome) . "&limite=10";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$accessToken}",
            "Accept: application/json"
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $dados = json_decode($response, true);

        if ($httpCode === 200) {
            $formatados = array_map(function ($item) {
                return [
                    'id' => $item['id'],
                    'nome' => $item['nome'],
                    'preco' => $item['preco']
                ];
            }, $dados['data'] ?? []);

            return $this->jsonResponse($formatados, 'Sucesso');
        }

        return $this->jsonResponse([], 'Erro ao buscar no Bling', $httpCode);
    }
}
