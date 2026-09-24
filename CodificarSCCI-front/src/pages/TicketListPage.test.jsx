import { render, screen, waitFor } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { MemoryRouter } from 'react-router-dom'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import { ApiError } from '../api/client'
import { listResponsibles, listTickets } from '../api/endpoints'
import TicketListPage from './TicketListPage'

vi.mock('../api/endpoints', () => ({
  listTickets: vi.fn(),
  listResponsibles: vi.fn(),
}))

const tickets = [
  {
    id: 1,
    title: 'Impressora quebrada',
    employee_email: 'maria@empresa.com',
    priority: 'high',
    status: 'open',
    responsible: { id: 1, name: 'John Doe' },
    opened_at: '2026-09-24T14:00:00Z',
  },
  {
    id: 2,
    title: 'Cadeira nova',
    employee_email: 'joao@empresa.com',
    priority: 'low',
    status: 'resolved',
    responsible: null,
    opened_at: '2026-09-23T10:00:00Z',
  },
]

const paginated = (data) => ({
  data,
  meta: { current_page: 1, last_page: 1, total: data.length },
})

function renderPage() {
  render(
    <MemoryRouter>
      <TicketListPage />
    </MemoryRouter>,
  )
}

describe('TicketListPage', () => {
  beforeEach(() => {
    vi.resetAllMocks()
    listResponsibles.mockResolvedValue({ data: [{ id: 1, name: 'John Doe' }] })
  })

  it('lista os chamados com link para o detalhe, responsável e paginação', async () => {
    listTickets.mockResolvedValue(paginated(tickets))

    renderPage()

    expect(await screen.findByRole('link', { name: 'Impressora quebrada' })).toHaveAttribute(
      'href',
      '/tickets/1',
    )
    expect(screen.getByText('Cadeira nova')).toBeInTheDocument()
    expect(screen.getByText('Sem responsável')).toBeInTheDocument()
    expect(screen.getByText('Página 1 de 1 · 2 chamado(s)')).toBeInTheDocument()
  })

  it('ao filtrar por status, busca de novo na API a partir da página 1', async () => {
    listTickets.mockResolvedValue(paginated(tickets))
    const user = userEvent.setup()

    renderPage()
    await screen.findByText('Cadeira nova')

    await user.selectOptions(screen.getByLabelText('Status'), 'resolved')

    await waitFor(() =>
      expect(listTickets).toHaveBeenLastCalledWith(
        expect.objectContaining({ status: 'resolved', page: 1 }),
        expect.anything(),
      ),
    )
  })

  it('mostra a mensagem de erro quando a API está indisponível', async () => {
    listTickets.mockRejectedValue(
      new ApiError('Não foi possível conectar à API (http://localhost:8000/api).', 0),
    )

    renderPage()

    expect(await screen.findByRole('alert')).toHaveTextContent('Não foi possível conectar à API')
  })
})
