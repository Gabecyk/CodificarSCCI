# CodificarSCCI — Frontend

Interface web do **Sistema de Controle de Chamados Internos**. Consome a [API Laravel](../api/README.md) deste repositório.

## Telas

| Rota | Tela | O que faz |
|---|---|---|
| `/` | Lista de chamados | Tabela paginada com filtros por status, prioridade e responsável, e seleção de itens por página |
| `/tickets/new` | Novo chamado | Formulário de criação; o responsável pode ser escolhido manualmente ou deixado em "Atribuir automaticamente" |
| `/tickets/:id` | Detalhe do chamado | Todos os dados do chamado, com atalho para edição |
| `/tickets/:id/edit` | Editar chamado | Mesmo formulário, com o campo de status habilitado (na criação todo chamado nasce `open`) |

Erros de validação da API (`422`) aparecem embaixo de cada campo; falhas de rede/`404` viram mensagens na tela.

## Como rodar

### Opção 1 — Docker, junto com a API (recomendado)

Na **raiz do repositório**, um único comando sobe API, banco e front (só precisa do Docker):

```bash
docker compose up -d --build
```

O front fica em http://localhost:5173, servido pelo nginx a partir de um build de produção (`Dockerfile` multi-stage: o Node gera o `dist/` e só ele vai para a imagem final). A URL da API vista pelo navegador (`http://localhost:8000/api`) é embutida no build pelo argumento `VITE_API_URL` do `docker-compose.yml`. Depois de editar o código, `docker compose up -d --build front` gera um novo build.

### Opção 2 — Desenvolvimento local (hot reload)

Pré-requisitos: **Node.js 20+** e a **API rodando** (por exemplo, `cd api && docker compose up -d --build`; ver o [README da API](../api/README.md)).

```bash
cd CodificarSCCI-front
npm install
npm run dev
```

Acesse http://localhost:5173 (se o container `front` estiver de pé, pare-o antes com `docker compose stop front` para liberar a porta).

Por padrão o front chama `http://localhost:8000/api`. Para apontar para outra URL, copie `.env.example` para `.env` e ajuste `VITE_API_URL`.

Outros comandos:

```bash
npm test         # testes unitários (Vitest)
npm run lint     # oxlint
npm run build    # build de produção em dist/
npm run preview  # serve o build localmente
```

## Stack e decisões técnicas

| Tecnologia | Por quê |
|---|---|
| **React 19 + Vite** | Setup rápido; o estado local com hooks (`useState`/`useEffect`) basta para esse tamanho do problema, sem precisar de uma lib de estado global. |
| **React Router** | Cada tela tem URL própria (link direto para um chamado, botão "voltar" do navegador funcionando). |
| **Tailwind CSS 4** | Framework CSS moderno. |
| **`fetch` nativo** (`src/api`) | A API tem 5 endpoints; um wrapper fino (`client.js`) que normaliza erros (`ApiError` com `status` e `errors` por campo) evita uma dependência como axios. |
| **Vitest + Testing Library** | Vitest reaproveita a configuração do Vite (sem Babel/Jest à parte); Testing Library testa o que o usuário vê e faz (rótulos, botões, textos), não detalhes de implementação. |

### Organização

```
src/
├── api/          # client.js (fetch + ApiError) e endpoints.js (uma função por endpoint)
├── components/   # componentes reutilizáveis: Layout, TicketTable, TicketFilters, TicketForm, badges...
├── hooks/        # useResponsibles (carrega a lista uma vez e compartilha entre telas)
├── pages/        # uma página por rota; cuidam de dados, loading e erro
├── utils/        # formatação de data
└── constants.js  # opções/labels de status e prioridade (fonte única)
```

Páginas concentram estado e chamadas à API; componentes de apresentação recebem dados e callbacks por props. Requisições usam `AbortController` para cancelar respostas obsoletas ao trocar filtro/página/rota.

## Testes

```bash
npm test
```

São **11 testes unitários** A API é sempre simulada (`fetch` e `endpoints` mockados), então rodam sem o backend no ar.

| Arquivo | O que garante |
|---|---|
| `src/api/client.test.js` (4) | Contrato com a API: query string ignora filtros vazios; corpo enviado como JSON com os headers certos; `422` vira `ApiError` com os erros por campo; falha de rede vira `ApiError` com mensagem amigável. |
| `src/components/TicketForm.test.jsx` (4) | Payload enviado ao salvar: "Atribuir automaticamente" manda `responsible_id: null` (item 4.1) e o status não aparece na criação; escolha manual manda o id numérico (item 4.2); na edição o formulário vem preenchido e permite mudar o status; erros de validação da API aparecem embaixo do campo. |
| `src/pages/TicketListPage.test.jsx` (3) | Listagem (item 5): mostra os chamados com link para o detalhe, responsável e paginação; filtrar por status busca de novo na API a partir da página 1; mensagem de erro quando a API está fora do ar. |

