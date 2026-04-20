<?php 

namespace App\Middleware;

use App\Core\Middleware;

/**
 * Middleware de Autenticação via Bling
 * * Este componente intercepta as requisições para garantir que o usuário
 * possua um token de acesso válido (via Cookie ou Sessão) antes de acessar a API.
 */
class AuthBlingMiddleware extends Middleware
{
    /**
     * Valida a existência do token de acesso.
     * * @return void|jsonResponse Retorna erro 401 caso o token não seja encontrado.
     */
    public function handle()
    {
        // Verifica se o navegador enviou o cookie ou se o token está na sessão
        $tokenCookie = $_COOKIE['bling_token'] ?? null;
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $tokenSessao = $_SESSION['bling_access_token'] ?? null;

        // Se não houver token em nenhum dos dois, barra a requisição
        if (!$tokenCookie && !$tokenSessao) {
            return $this->jsonResponse(
                [], 
                "Sessão expirada ou não autenticada no Bling. Por favor, realize o login.", 
                401
            );
        }
    }
}