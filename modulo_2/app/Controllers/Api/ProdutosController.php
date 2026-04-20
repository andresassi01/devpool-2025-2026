<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Middleware\AuthBlingMiddleware;

class ProdutosController extends Controller
{
    public function __construct()
    {
        $this->middleware(AuthBlingMiddleware::class);
    }

    public function index()
    {
        // Prioriza o token da sessão que é mais confiável em redirecionamentos
        $accessToken = $_SESSION['bling_access_token'] ?? $_COOKIE['bling_token'] ?? '';

        // Captura e sanitização dos filtros
        $pagina = $_GET['pagina'] ?? 1;
        $nome = $_GET['nome'] ?? null;
        $sku = $_GET['sku'] ?? null;
        $situacao = $_GET['situacao'] ?? '1';
        $dataInicio = $_GET['dataInicio'] ?? null;
        $dataFim = $_GET['dataFim'] ?? null;

        // URL Base
        $urlBling = BLING_API_URL . "/produtos?pagina={$pagina}&limite=10";

        // 1. Filtro de Nome
        if (!empty($nome)) {
            $urlBling .= "&nome=" . urlencode($nome);
        }

        // 2. Filtro de SKU (No Bling V3 o parâmetro é 'codigo')
        if (!empty($sku)) {
            $urlBling .= "&codigo=" . urlencode($sku);
        }

        // 3. Mapeamento de Situação
        $mapa = [
            '2' => '2', // Ativos
            '3' => '3', // Inativos
            '4' => '4', // Excluídos
            '5' => '5'  // Todos
        ];

        if (isset($mapa[$situacao])) {

            $urlBling .= "&criterio=" . $mapa[$situacao];
        }

        if (!empty($dataInicio)) {
            $urlBling .= "&dataAlteracaoInicial=" . urlencode(trim($dataInicio) . " 00:00:00");
        }
        if (!empty($dataFim)) {
            $urlBling .= "&dataAlteracaoFinal=" . urlencode(trim($dataFim) . " 23:59:59");
        }

        $ch = curl_init($urlBling);
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
            // Retorna os dados para o useProdutos.ts
            return $this->jsonResponse($dados['data'] ?? [], 'Sucesso!');
        }

        // Caso a API retorne erro ou vazio
        return $this->jsonResponse($dados, 'Erro API Bling: ' . $httpCode, $httpCode);
    }
}
