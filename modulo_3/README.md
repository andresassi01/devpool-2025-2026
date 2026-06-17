# Módulo 3 — Microsserviço em Go

Este módulo introduz **microsserviços em Go** e demonstra **integração HTTP
entre stacks**: uma API Go (DDD) consumida pela API PHP do `modulo_2` via
cliente HTTP (Guzzle).

## Estrutura

```
modulo_3/
└── golang-web-api/         # Microsserviço Go (DDD)
    ├── cmd/web_api/        # Entrypoint + DI manual
    ├── internal/
    │   ├── presentation/   # Handlers Gin + router
    │   ├── application/    # Services, mappers, messages
    │   ├── domain/         # Entidades + interfaces de repositório
    │   └── infrastructure/ # MySQL, config, repositórios concretos
    ├── docker-compose.yml  # MySQL standalone (porta 3308)
    └── Makefile
```

## Pré-requisitos

- Go 1.21+
- Docker + Docker Compose
- `modulo_2` rodando para a integração ponta-a-ponta

## Como rodar o microsserviço Go

```bash
cd modulo_3/golang-web-api
make dev-init   # sobe MySQL em 3308 e prepara .env
make run        # inicia API em http://localhost:8080
```

Endpoints expostos:

- `GET  /v1/health` — health check
- `POST /v1/tasks` — cria task (body: `{"title": "...", "description": "..."}`)
- `GET  /v1/tasks` — lista tasks

## Integração com `modulo_2`

O `modulo_2` ganhou:

- `app/Client/BaseClient.php` — wrapper Guzzle com tratamento de erros
- `app/Client/TaskClient.php` — cliente concreto que chama `/v1/tasks`
- `app/Controllers/Api/TaskController.php` — controller que orquestra as chamadas
- `GOLANG_API_URL` no `.env` (default `http://host.docker.internal:8080`)
- `extra_hosts: host.docker.internal:host-gateway` no `docker-compose.yml`,
  permitindo que o container PHP alcance o Go rodando no host

### Fluxo

```
Browser → modulo_2 (PHP) → TaskClient (Guzzle) → modulo_3 (Go) → MySQL
```

### Endpoints expostos pelo modulo_2

- `GET  http://localhost:88/api/task/index`  → lista tasks via Go
- `POST http://localhost:88/api/task/store`  → cria task via Go

## Testando ponta-a-ponta

1. Suba o Go: `cd modulo_3/golang-web-api && make dev-init && make run`
2. Suba o PHP: `cd modulo_2 && docker compose up -d`
3. Crie uma task:
   ```bash
   curl -X POST http://localhost:88/api/task/store \
     -H 'Content-Type: application/json' \
     -d '{"title":"Estudar Go","description":"Modulo 3"}'
   ```
4. Liste tasks: `curl http://localhost:88/api/task/index`
