# CodificarSCCI — API

Backend do **Sistema de Controle de Chamados Internos**. Permite que funcionários abram chamados (título, descrição, prioridade, status) e que o time de suporte os acompanhe, com atribuição automática ao responsável menos sobrecarregado.

## Sumário

- [Stack e decisões técnicas](#stack-e-decisões-técnicas)
- [Arquitetura](#arquitetura)
- [Regras de negócio](#regras-de-negócio)
- [Decisões e trade-offs](#decisões-e-trade-offs)
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

- **Controllers** (`app/Http/Controllers/Api`) são finos: recebem a requisição já validada, chamam uma Action e devolvem um Resource. Nenhuma regra de negócio aqui.
- **Actions** (`app/Actions`) concentram cada caso de uso (`ListTicketsAction`, `CreateTicketAction`, `ShowTicketAction`, `UpdateTicketAction`, `ListResponsibleAction`). Uma classe por operação mantém a responsabilidade única (**S** do SOLID) e permite testar a regra sem passar pelo HTTP.
- **Repositories** (`app/Repositories/Contracts` + `Eloquent`) escondem o acesso a dados atrás de uma interface. As Actions dependem da abstração, não do Eloquent (**D** do SOLID, inversão de dependência); o vínculo interface → implementação fica no `AppServiceProvider`. Hoje existe uma única implementação (Eloquent) — o ganho é isolamento e testabilidade, não trocar de banco.
- **Requesters** (`app/Http/Requesters`) são `FormRequest`s por operação (`StoreTicketRequest`, `UpdateTicketRequest`): a validação e as mensagens em português ficam fora do controller.
- **Resources** (`app/Http/Resources`) definem o formato do JSON. O contrato da API não fica preso à estrutura da tabela.
- **Enums** (`TicketStatus`, `TicketPriority`) eliminam strings soltas: o mesmo enum valida a entrada, faz o cast no model e define o que é "em aberto".
- **Strategy de distribuição** (`app/Services/TicketAssignment`): a `AssignmentStrategyInterface` tem uma implementação, `LeastBusyAgentStrategy`, escolhida no `AppServiceProvider`. Para criar outra regra de distribuição (por prioridade...) basta implementar a interface e trocar o vínculo, sem alterar as Actions (**O** do SOLID, aberto/fechado).
- **Middleware `ForceJsonResponse`** (`app/Http/Middleware`), aplicado ao grupo `api`, força `Accept: application/json`. Sem ele, um cliente que esquece esse header recebe um *redirect* HTML em vez de um `422` em JSON quando a validação falha — problema que apareceu na prática ao testar pelo Postman.

## Regras de negócio

### Distribuição automática

A `LeastBusyAgentStrategy` roda depois de criar **e** depois de atualizar um chamado:

1. Se o chamado já tem `responsible_id` (o usuário escolheu manualmente), a escolha é respeitada e nada muda.
2. Caso contrário, ele vai para o responsável com **menos chamados em aberto**. Em caso de empate, atribui o de menor `id`.

A atribuição manual não tem uma classe própria: escolher o responsável é apenas enviar `responsible_id` no payload. No front, a opção "Atribuir automaticamente" envia `responsible_id: null`.

### O que é "em aberto"

O modelo de status é: `open` → `in_progress` → `resolved` → `closed`.

**Um chamado está "em aberto" quando o status é `open` ou `in_progress`.** `resolved` e `closed` são considerados concluídos e não entram na carga de ninguém.

Justificativa:

- **`in_progress` conta como carga.** O objetivo do cliente é equilibrar o trabalho: quem está no meio de um atendimento continua ocupado. Se só `open` contasse, quem pegou vários chamados e já começou a trabalhar apareceria como "livre" e receberia ainda mais.
- **`resolved` não conta.** O trabalho do responsável terminou; falta apenas confirmação ou encerramento formal. Contá-lo penalizaria quem resolve rápido.
- **`closed` não conta**, por ser estado terminal.

### Ciclo de vida

- Todo chamado **nasce `open`**: o campo `status` não é aceito na criação (`StoreTicketRequest`) e o banco aplica o padrão. Só a edição (`PUT`) altera o status.
- `opened_at` é preenchido pelo banco no momento da criação.

## Decisões e trade-offs

- **Sem cadastro de responsáveis** (itens 3.2 e 3.3): eles só precisam existir e ser selecionáveis, então há apenas listagem (`GET /api/responsibles`) e seeder.
- **`APP_DEBUG=true` e credenciais simples** no Docker são só para desenvolvimento local.

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
