<?php

namespace App\Controllers\Api;

use App\Core\Controller;

class AuthController extends Controller
{
    // Esta é a função que o Callback.vue (Front) vai chamar
    public function blingToken()
    {
        $this->validateRequestMethods(['POST']);
        $data = $this->getRequestData();
        $code = $data['code'] ?? null;

        if (!$code) {
            return $this->jsonResponse([], 'Code não fornecido', 400);
        }

        // 1. Configurações do App no Bling
        $clientId = "8b83f5e78848c75f558c581cf69aed1c93aed7f7";
        $clientSecret = "304eabc95c4f3b3ee855ee356d098739db66a32cafc088914cab7e15d83a";
        $credentials = base64_encode($clientId . ':' . $clientSecret);

        // 2. Chamada cURL para trocar o CODE pelo TOKEN
        $ch = curl_init('https://www.bling.com.br/Api/v3/oauth/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['grant_type' => 'authorization_code', 'code' => $code]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Basic ' . $credentials,
            'Content-Type: application/x-www-form-urlencoded'
        ]);

        $response = curl_exec($ch);
        $dataToken = json_decode($response, true);
        

        if (isset($dataToken['access_token'])) {
            $accessToken = $dataToken['access_token'];

            // 3. SETAR O COOKIE
            setcookie('bling_token', $accessToken, [
                'expires' => time() + ($dataToken['expires_in'] ?? 7200),
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax'
            ]);

            // 4. RETORNO PARA O VUE 
            return $this->jsonResponse([
                'access_token' => $accessToken,
                'expires_in' => $dataToken['expires_in'] ?? 7200
            ], 'Autenticação realizada com sucesso');
        }

        return $this->jsonResponse($dataToken, 'Erro ao obter token do Bling', 401);
    }

    public function logout()
    {
        $this->validateRequestMethods(['POST']);
        
        // Limpa o cookie
        setcookie('bling_token', '', time() - 3600, '/');
        
        return $this->jsonResponse([], 'Logout efetuado com sucesso', 200);
    }
}