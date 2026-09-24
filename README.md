# CodificarSCCI

Sistema de Controle de Chamados Internos — Permite que funcionários abram chamados internos (ex.: "impressora não funciona", "preciso de uma cadeira nova") e que o time de suporte os acompanhe, com distribuição automática de chamados entre os responsáveis.

## Estrutura do repositório

- [`api/`](api/README.md) — backend (PHP/Laravel). É onde está toda a documentação técnica: arquitetura, como rodar (Docker ou local), endpoints, seeders e testes.
- [`CodificarSCCI-front/`](CodificarSCCI-front/README.md) — frontend (React + Vite + Tailwind): telas de listagem, detalhe, criação e edição de chamados.

## Como subir tudo

1. **API** (Docker): `cd api && docker compose up -d --build && docker compose exec app php artisan db:seed` — detalhes no [README da API](api/README.md).
2. **Frontend**: `cd CodificarSCCI-front && npm install && npm run dev` — abre em http://localhost:5173. Detalhes no [README do front](CodificarSCCI-front/README.md).
