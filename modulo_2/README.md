# API REST em PHP com padrão MVC

Este diretório contém a estrutura de **API REST** utilizando o padrão **MVC (Model-View-Controller)** em PHP puro, configurado para rodar em Docker e ser utilizado no módulo 2 do desenvolvimento do devpool.

## O que é uma API?

**API (Application Programming Interface)** é uma interface que permite a comunicação entre diferentes sistemas. Uma **API REST** utiliza o protocolo HTTP para receber requisições e retornar dados, geralmente no formato JSON.

Por exemplo, quando você acessa `/api/usuarios`, a API processa a requisição e retorna uma lista de usuários em formato JSON:

```json
{
    "status": "success",
    "data": [
        { "id": 1, "nome": "João" },
        { "id": 2, "nome": "Maria" }
    ]
}
```

## O que é o padrão MVC?

**MVC (Model-View-Controller)** é um padrão de arquitetura que separa a aplicação em três camadas:

| Camada | Responsabilidade | Neste projeto |
|--------|------------------|---------------|
| **Model** | Representa os dados e a lógica de acesso ao banco de dados | `app/Models/` |
| **View** | Apresenta os dados ao usuário (em APIs, é a resposta JSON) | Resposta JSON |
| **Controller** | Recebe as requisições, processa e coordena Model e View | `app/Controllers/` |

### Fluxo de uma requisição

```
Requisição HTTP → Controller → Model (busca dados) → Controller → Resposta JSON
```

**Exemplo prático:**

1. Usuário faz requisição `GET /api/exemplo/show/1`
2. O **Controller** (`ExemploController`) recebe a requisição
3. O Controller chama o **Model** (`Exemplo`) para buscar o registro com ID 1
4. O Model consulta o banco de dados e retorna os dados
5. O Controller formata e retorna a resposta JSON

## Requisitos

Antes de rodar o projeto, é necessário ter instalado:

- [Docker](https://www.docker.com/get-started)
- [Docker Compose](https://docs.docker.com/compose/install/)

## Como Executar

> OBS: lembre de executar os comandos estando sempre na pasta `modulo_2`, para que funcione

### 1. Preparando o ambiente

Faça uma cópia do arquivo `.env.example` e renomeie para `.env`:

```bash
cp .env.example .env
```

### 2. Iniciar o Projeto

Execute o comando para iniciar os containers:

```bash
docker compose up -d
```

### 3. Acessando a API

Após o build ser concluído, a API estará disponível em:

```
http://localhost:88
```

## Estrutura de Diretórios

```
projeto-mvc-php/
├── .docker/                # Configurações do Docker
├── app/                    # Código principal da aplicação
│   ├── Controllers/        # Controllers da API
│   │   └── Api/            # Controllers de endpoints
│   ├── Core/               # Classes base (Controller, Core, Model)
│   ├── Middleware/         # Middlewares de autenticação
│   ├── Models/             # Models (entidades do banco)
│   └── Supports/           # Classes de suporte (Criptografia, Logs, etc)
├── config/                 # Configurações da aplicação
│   └── config.php          # Arquivo principal de configuração
├── docker-compose.yml      # Configuração do Docker Compose
├── index.php               # Ponto de entrada da aplicação
└── README.md               # Este arquivo
```

## Como Funciona o Roteamento

O projeto implementa um sistema de roteamento automático baseado em namespaces. A URL é mapeada diretamente para os controllers e métodos.

### Estrutura das Rotas

```
http://localhost:88/api/{controller}/{método}/{parâmetros}
```

### Exemplos

| URL | Controller | Método | Descrição |
|-----|------------|--------|-----------|
| `/api/exemplo` | ExemploController | index() | Lista todos |
| `/api/exemplo/show/1` | ExemploController | show(1) | Busca por ID |
| `/api/exemplo/store` | ExemploController | store() | Cria novo (POST) |
| `/api/exemplo/update/1` | ExemploController | update(1) | Atualiza (PUT) |
| `/api/exemplo/delete/1` | ExemploController | delete(1) | Remove (DELETE) |

## Criando um Novo Controller

Para criar um novo endpoint, basta criar um controller na pasta `app/Controllers/Api/`.

Exemplo: Criar um `UserController`:

```php
<?php

namespace App\Controllers\Api;

use App\Core\Controller;

class UserController extends Controller
{
    public function index()
    {
        return $this->jsonResponse(['message' => 'Lista de usuários']);
    }

    public function show(int $id)
    {
        return $this->jsonResponse(['message' => 'Usuário ' . $id]);
    }

    public function store()
    {
        $this->validateRequestMethods(['POST']);
        $data = $this->getRequestData();

        return $this->jsonResponse(['message' => 'Usuário criado', 'data' => $data]);
    }
}
```

Após criar o controller, as rotas já estarão disponíveis automaticamente:

- `GET /api/user` → `UserController::index()`
- `GET /api/user/show/1` → `UserController::show(1)`
- `POST /api/user/store` → `UserController::store()`

## Usando Middlewares

**Middlewares** são filtros que executam **antes** da ação do controller. São úteis para:

- Verificar se o usuário está autenticado
- Validar permissões de acesso
- Registrar logs de requisições

### Como usar um Middleware

No construtor do controller, chame o método `middleware()` passando a classe:

```php
<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Middleware\AuthApiMiddleware;

class MeuController extends Controller
{
    public function __construct()
    {
        // Executa o middleware antes de qualquer ação
        $this->middleware(AuthApiMiddleware::class);
    }

    public function index()
    {
        // Só chega aqui se o middleware permitir
        return $this->jsonResponse(['message' => 'Usuário autenticado!']);
    }
}
```

### Como criar um Middleware

Crie uma classe em `app/Middleware/` que estenda `Middleware`:

```php
<?php

namespace App\Middleware;

use App\Core\Middleware;

class MeuMiddleware extends Middleware
{
    public function handle()
    {
        // Sua lógica de validação aqui
        if (!$this->usuarioTemPermissao()) {
            return $this->jsonResponse([], 'Acesso negado', 403);
        }

        // Se não retornar nada, a requisição continua normalmente
    }

    private function usuarioTemPermissao()
    {
        // Implemente sua lógica
        return true;
    }
}
```

### Fluxo com Middleware

```
Requisição HTTP → Middleware (valida) → Controller → Resposta JSON
                      ↓
              Se falhar, retorna erro
```

## Autenticação

O projeto inclui um sistema de autenticação por **sessão** para proteger rotas. O `ExemploController` utiliza o middleware `AuthApiMiddleware` que verifica se o usuário está autenticado.

### Credenciais padrão

| Campo | Valor |
|-------|-------|
| Usuário | `devpool` |
| Senha | `asdf000` |

### Como fazer login

Faça uma requisição POST para `/api/auth/login`:

```bash
curl --location 'http://localhost:88/api/auth/login' \
--header 'Content-Type: application/json' \
--data '{
    "username": "devpool",
    "password": "asdf000"
}'
```

**Resposta de sucesso:**

```json
{
    "data": {
        "email": "devpool@mail.com",
        "name": "DevPool",
        "token": "1234567890"
    },
    "message": "Autenticação efetuada com sucesso"
}
```

### Como fazer logout

```bash
curl --location --request POST 'http://localhost:88/api/auth/logout'
```

### Testando rotas protegidas

Após fazer login, a sessão fica ativa e você pode acessar as rotas protegidas:

```bash
# Listar todos os registros (rota protegida)
curl --location 'http://localhost:88/api/exemplo'
```

**Importante:** Se estiver usando ferramentas como Postman ou Insomnia, certifique-se de que os cookies estão sendo enviados nas requisições para manter a sessão ativa.

## Métodos Úteis do Controller Base

| Método | Descrição |
|--------|-----------|
| `$this->jsonResponse($data, $message, $status)` | Retorna resposta JSON |
| `$this->getRequestData()` | Obtém dados do body da requisição |
| `$this->validateRequestMethods(['GET', 'POST'])` | Valida método HTTP permitido |
| `$this->middleware(MeuMiddleware::class)` | Executa um middleware antes da ação |

## Configuração

O arquivo `config/config.php` contém as configurações principais:

```php
define('DEFAULT_CONTROLLER', 'Api\\Exemplo');  // Controller padrão
define('URL_BASE', 'http://localhost:88');     // URL base da API
define('MAINTENANCE', 0);                       // Modo manutenção (0 = desligado)
```

## Dicas para Iniciantes

1. **Comece pelo ExemploController**: Analise o arquivo `app/Controllers/Api/ExemploController.php` para entender como funciona um CRUD completo.

2. **Entenda o Model**: Veja `app/Models/Exemplo.php` para entender como interagir com o banco de dados.

3. **Use o Postman ou Insomnia**: Ferramentas como Postman facilitam testar os endpoints da API.

4. **Leia os arquivos Core**: Os arquivos em `app/Core/` são a base do framework e ajudam a entender como tudo funciona.

---

Qualquer dúvida, estou à disposição! :)
