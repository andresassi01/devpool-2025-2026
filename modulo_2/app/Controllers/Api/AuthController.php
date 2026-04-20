<?php

namespace App\Controllers\Api;

use App\Core\Controller;

/**
 * Controller responsável pela autenticação via OAuth2 com a API do Bling.
 * Gerencia a troca de código por token, controle de sessão e cookies.
 */
class AuthController extends Controller
{
    /**
     * Realiza a troca do 'code' fornecido pelo Bling pelo Access Token.
     * Este método é chamado após o redirecionamento do fluxo de autorização.
     * * @return jsonResponse
     */
    public function blingToken()
    {
        $this->validateRequestMethods(['POST']);
        $data = $this->getRequestData();
        $code = $data['code'] ?? null;

        if (!$code) {
            return $this->jsonResponse([], 'Código de autorização não fornecido', 400);
        }

        // Credenciais codificadas conforme exigência do Bling (Basic Auth)
        $credentials = base64_encode(BLING_CLIENT_ID . ':' . BLING_CLIENT_SECRET);

        $ch = curl_init(BLING_API_URL . '/oauth/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'authorization_code', 
            'code' => $code
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Basic ' . $credentials,
            'Content-Type: application/x-www-form-urlencoded'
        ]);

        $response = curl_exec($ch);
        $dataToken = json_decode($response, true);
        

        if (isset($dataToken['access_token'])) {
            $accessToken = $dataToken['access_token'];
            $expiresIn = $dataToken['expires_in'] ?? 7200;

            // Define o cookie de segurança para uso do Frontend/Backend
            setcookie('bling_token', $accessToken, [
                'expires' => time() + $expiresIn,
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax'
            ]);

            // Armazena também na sessão para redundância e uso de Middlewares
            if (session_status() === PHP_SESSION_NONE) session_start();
            $_SESSION['bling_access_token'] = $accessToken;

            return $this->jsonResponse([
                'access_token' => $accessToken,
                'expires_in' => $expiresIn
            ], 'Autenticação realizada com sucesso');
        }

        return $this->jsonResponse($dataToken, 'Erro ao obter token do Bling', 401);
    }

    /**
     * Finaliza a sessão do usuário limpando cookies e dados de sessão.
     * * @return jsonResponse
     */
    public function logout()
    {
        $this->validateRequestMethods(['POST']);

        // Remove o cookie definindo uma data de expiração no passado
        setcookie('bling_token', '', time() - 3600, '/');
        
        if (session_status() === PHP_SESSION_NONE) session_start();
        unset($_SESSION['bling_access_token']);

        return $this->jsonResponse([], 'Logout efetuado com sucesso', 200);
    }

    /**
     * Verifica se existe uma sessão ativa (Token válido).
     * Útil para o Vue Router decidir se o usuário pode acessar rotas protegidas.
     * * @return jsonResponse
     */
    public function check()
    {
        $this->validateRequestMethods(['GET']);

        if (isset($_COOKIE['bling_token']) || isset($_SESSION['bling_access_token'])) {
            return $this->jsonResponse(['logado' => true], 'Sessão ativa');
        }

        return $this->jsonResponse(['logado' => false], 'Sessão expirada ou inexistente', 401);
    }
}