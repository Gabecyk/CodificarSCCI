# CodificarSCCI — API

Backend do **Sistema de Controle de Chamados Internos**. Permite que funcionários abram chamados (título, descrição, prioridade, status) e que o time de suporte os acompanhe, com atribuição automática ao responsável menos sobrecarregado.

## Sumário

- [Stack e decisões técnicas](#stack-e-decisões-técnicas)
- [Arquitetura](#arquitetura)
- [Rodando o projeto](#rodando-o-projeto)
  - [Opção 1 — Docker (recomendado)](#opção-1--docker-recomendado)
  - [Opção 2 — Ambiente local (PHP + Composer + Postgres)](#opção-2--ambiente-local-php--composer--postgres)
- [Endpoints da API](#endpoints-da-api)
- [Seeders](#seeders)
- [Testes](#testes)

## Stack e decisões técnicas

| Tecnologia | Por quê |
|---|---|
| **PHP 8.2 + Laravel 12** | Framework maduro para uma API REST rápida de construir com qualidade — validação, Eloquent ORM, container de injeção de dependência, testes integrados de fábrica. Reduz atrito de configuração e deixa o tempo disponível focado nas regras de negócio do desafio (arquitetura, distribuição automática, testes) em vez de infraestrutura. |
| **PostgreSQL** | Banco relacional robusto e gratuito, com bom suporte a `ENUM`/constraints e ótima integração com Docker — sem motivo para usar algo mais pesado neste cenário. |
| **Docker + docker-compose** | Atende diretamente o requisito de "executável localmente por qualquer pessoa do time" sem exigir PHP/Postgres instalados na máquina — só Docker. Ver [Opção 1](#opção-1--docker-recomendado). |
| **PHPUnit (testes de Feature)** | O próprio desafio pede foco em fundamentos ("testes, SOLID, DRY" > features extras). Os testes usam SQLite em memória (`phpunit.xml`), então rodam isolados do Postgres de desenvolvimento — rápidos e sem efeito colateral. |
| **Adminer** (via Docker) | Visualização do banco pelo navegador sem precisar instalar pgAdmin/DBeaver na máquina — reduzir fricção pra quem só quer conferir os dados. |

## Arquitetura

O backend segue uma arquitetura em camadas, pensada para deixar a lógica de negócio testável e desacoplada do Eloquent/HTTP:

```
Controller → Action → Repository (interface) → Model (Eloquent)
                ↓
        Requester (FormRequest, validação)
                ↓
        Resource (formata a resposta JSON)
```

## Rodando o projeto

### Opção 1 — Docker (recomendado)

Único pré-requisito: **Docker** e **Docker Compose** instalados. Não precisa de PHP, Composer ou Postgres na máquina.

```bash
cd api
docker compose up -d --build
```

Isso sobe três containers:

| Serviço | URL | O que é |
|---|---|---|
| `app` | http://localhost:8000 | API Laravel (migrations rodam automaticamente na subida) |
| `db` | localhost:5432 | PostgreSQL (dados persistem no volume `pgdata`) |
| `adminer` | http://localhost:8080 | Cliente de banco pelo navegador (servidor `db`, usuário `postgres`, senha `root`, banco `codificar`) |

Popule o banco com os responsáveis e chamados de exemplo (ver [Seeders](#seeders)):

```bash
docker compose exec app php artisan db:seed
```

Rodar os testes:

```bash
docker compose exec app php artisan test
```

Parar tudo (mantendo os dados) / apagando os dados:

```bash
docker compose down        # mantém o volume do Postgres
docker compose down -v     # apaga também os dados
```

### Opção 2 — Ambiente local (PHP + Composer + Postgres)

Pré-requisitos: **PHP 8.2+**, **Composer**, **PostgreSQL** rodando localmente.

```bash
cd api
composer install
cp .env.example .env
php artisan key:generate
```

Edite o `.env` com as credenciais do seu Postgres local:

```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=codificar
DB_USERNAME=postgres
DB_PASSWORD=sua_senha
```

Crie o banco `codificar` no Postgres, depois rode as migrations e os seeders:

```bash
php artisan migrate --seed
```

Suba o servidor:

```bash
php artisan serve
```

A API fica disponível em `http://127.0.0.1:8000`.

## Endpoints da API

| Método | Rota | Descrição |
|---|---|---|
| `GET` | `/api/tickets` | Lista chamados, com paginação e filtros opcionais (`status`, `priority`, `responsible_id`) |
| `POST` | `/api/tickets` | Cria um chamado. Nasce sempre com status `open`; se `responsible_id` não for enviado, é atribuído automaticamente ao responsável com menos chamados em aberto |
| `GET` | `/api/tickets/{id}` | Detalhe de um chamado |
| `PUT` | `/api/tickets/{id}` | Atualiza um chamado (título, descrição, prioridade, status, responsável) |
| `GET` | `/api/responsibles` | Lista os responsáveis disponíveis para atribuição |

Todas as respostas são JSON. Erros de validação retornam `422` com o detalhe por campo; recurso não encontrado retorna `404`.

## Seeders

O `DatabaseSeeder` popula:

- **`ResponsibleSeeder`** 
- **`TicketSeeder`** 

```bash
php artisan db:seed
# ou, via Docker:
docker compose exec app php artisan db:seed
```

> Os seeders não são idempotentes (`email` é `unique` em `responsibles`) — rodar duas vezes sem resetar o banco gera erro de chave duplicada. Para recomeçar do zero: `php artisan migrate:fresh --seed`.

## Testes

```bash
php artisan test
# ou, via Docker:
docker compose exec app php artisan test
```

Cobertura atual (`tests/Feature`):

- **`LeastBusyAgentStrategyTest`** — valida a distribuição automática, confirma que o responsável com menos chamados em aberto é escolhido, e que chamados `resolved`/`closed` não contam nessa carga.
- **`ResponsibleActionTest`** — cobre a atribuição automática vs. manual, com `responsible_id` nulo, atribui automaticamente; com `responsible_id` já preenchido, respeita a escolha manual.
- **`TicketControllerTest`** — teste de integração do endpoint `POST /api/tickets`: criação com sucesso (`201` + persistência no banco) e validação de campos obrigatórios/inválidos (`422`).
- **`StoreTicketTest`** — confirma que um chamado criado via factory persiste corretamente com o `responsible_id` informado.
- **`IndexTicketTest`** — confirma que `GET /api/tickets` retorna os chamados existentes com os campos esperados (`title`, `description`, `employee_email`, `priority`, `status`, `responsible_id`).
- **`UpdateTicketTest`** — confirma que atualizar o `status` de um chamado persiste corretamente no banco.
