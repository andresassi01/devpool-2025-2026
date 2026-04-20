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
        $mapaSituacao = [
            '2' => '1', // Ativos
            '3' => '2', // Inativos
            '4' => '3', // Excluídos
            '5' => '0'  // Todos
        ];

        if (isset($mapaSituacao[$situacao]) && $situacao !== '1') {
            $urlBling .= "&situacao=" . $mapaSituacao[$situacao];
        }

        // 4. Filtro de Data (Bling V3 exige YYYY-MM-DD e recomenda timestamp)
        // Removida a trava de precisar das duas. Se tiver uma, ele já filtra.
        if (!empty($dataInicio)) {
            $urlBling .= "&dataAlteracaoInicial=" . $dataInicio . " 00:00:00";
        }
        if (!empty($dataFim)) {
            $urlBling .= "&dataAlteracaoFinal=" . $dataFim . " 23:59:59";
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
