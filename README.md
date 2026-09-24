# CodificarSCCI

Sistema de Controle de Chamados Internos — Permite que funcionários abram chamados internos (ex.: "impressora não funciona", "preciso de uma cadeira nova") e que o time de suporte os acompanhe, com distribuição automática de chamados entre os responsáveis.

## Estrutura do repositório

- [`api/`](api/README.md) — backend (PHP/Laravel). É onde está toda a documentação técnica: arquitetura, como rodar (Docker ou local), endpoints, seeders e testes.
- [`CodificarSCCI-front/`](CodificarSCCI-front/README.md) — frontend (React + Vite + Tailwind): telas de listagem, detalhe, criação e edição de chamados.

## Como subir tudo em (Docker)

Único pré-requisito: **Docker** com Docker Compose. Não precisa de PHP, Composer, Node ou Postgres na máquina.

```bash
docker compose up -d --build
```

Na raiz do repositório, esse comando sobe o projeto `codificarscci` com todos os serviços. As migrations rodam e o banco é populado sozinho (3 responsáveis e 2 chamados de exemplo) na primeira subida:

| Serviço | URL | O que é |
|---|---|---|
| `front` | http://localhost:5173 | Interface React (build de produção servido pelo nginx) |
| `app` | http://localhost:8000/api | API Laravel |
| `db` | localhost:5432 | PostgreSQL (dados persistem no volume `codificarscci_pgdata`) |
| `adminer` | http://localhost:8080 | Cliente de banco pelo navegador (servidor `db`, usuário `postgres`, senha `root`, banco `codificar`) |

Comandos úteis:

```bash
docker compose logs -f app            # acompanhar os logs da API
docker compose exec app php artisan test   # testes da API (usam SQLite em memória, não tocam no Postgres)
docker compose down                   # para tudo, mantendo os dados
docker compose down -v                # para tudo e apaga os dados
```

Sem Docker, veja como rodar cada parte no [README da API](api/README.md) e no [README do front](CodificarSCCI-front/README.md).
