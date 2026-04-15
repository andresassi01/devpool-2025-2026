<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Models\Venda; 

class VendasController extends Controller
{
    public function index()
    {
        // 1. Verificação de Token
      /*  $token = $_COOKIE['bling_token'] ?? null;
        if (!$token) {
            return $this->jsonResponse([], 'Sessão encerrada. Faça login novamente.', 401);
        }*/
 
        try {
            // 2. Captura de filtros enviados pelo Vue
            $cliente = $_GET['cliente'] ?? null;
            $dataInicio = $_GET['dataInicio'] ?? null;
            $dataFim = $_GET['dataFim'] ?? null;
            $ordem = $_GET['ordem'] ?? 'dataVenda'; // R6
            
            // 3. Montagem da Query SQL 
            $sql = "SELECT id, numero, nomeCliente, dataVenda, totalComDesconto, situacao FROM vendas WHERE 1=1";
            $params = [];

            // Filtro por nome do cliente 
            if ($cliente) {
                $sql .= " AND nomeCliente LIKE :cliente";
                $params['cliente'] = "%$cliente%";
            }

            // Filtro por período de datas
            if ($dataInicio && $dataFim) {
                $sql .= " AND dataVenda BETWEEN :inicio AND :fim";
                $params['inicio'] = $dataInicio;
                $params['fim'] = $dataFim;
            }

            // 4. Ordenação
            $colunaOrdem = ($ordem === 'total') ? 'totalComDesconto' : 'dataVenda';
            $sql .= " ORDER BY $colunaOrdem DESC";

            // 5. Execução (Usando o driver de banco do Core)
            $db = new Venda(); 
            $vendas = $db->query($sql, $params);

            return $this->jsonResponse($vendas, 'Sucesso');

        } catch (\Exception $e) {
            return $this->jsonResponse(null, 'Erro ao buscar vendas locais: ' . $e->getMessage(), 500);
        }
    }
}