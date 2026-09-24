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

Pré-requisitos: **Node.js 20+** e a **API rodando** (ver [README da API](../api/README.md); com Docker: `cd api && docker compose up -d --build && docker compose exec app php artisan db:seed`).

```bash
cd CodificarSCCI-front
npm install
npm run dev
```

Acesse http://localhost:5173.

Por padrão o front chama `http://localhost:8000/api`. Para apontar para outra URL, copie `.env.example` para `.env` e ajuste `VITE_API_URL`.

Outros comandos:

```bash
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

### Trade-offs

- **Sem testes automatizados no front** nesta versão; a validação dos fluxos (criar com atribuição automática/manual, erro 422, editar status, filtro, 404) foi feita de ponta a ponta na API real.
