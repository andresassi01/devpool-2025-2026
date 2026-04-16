<?php

namespace App\Controllers\Api;

use App\Core\Controller;

class ProdutosController extends Controller
{
    public function index()
    {
        $accessToken = $_COOKIE['bling_token'] ?? '';

        if (empty($accessToken)) {
            if (session_status() === PHP_SESSION_NONE) session_start();
            $accessToken = $_SESSION['token'] ?? $_SESSION['bling_access_token'] ?? '';
        }

        if (empty($accessToken)) {
            return $this->jsonResponse([], 'PHP acessado com sucesso, mas o Cookie "bling_token" nao chegou aqui.', 401);
        }

        // Filtros
        $pagina = $_GET['pagina'] ?? 1;
        $urlBling = "https://www.bling.com.br/Api/v3/produtos?pagina={$pagina}&limite=10";

        $ch = curl_init($urlBling);
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
            return $this->jsonResponse($dados['data'] ?? [], 'Sucesso!');
        }

        return $this->jsonResponse($dados, 'Erro na API do Bling: ' . $httpCode, $httpCode);
    }
}
