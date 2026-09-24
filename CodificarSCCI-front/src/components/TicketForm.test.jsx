import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { MemoryRouter } from 'react-router-dom'
import { describe, expect, it, vi } from 'vitest'
import TicketForm from './TicketForm'

const responsibles = [
  { id: 1, name: 'John Doe' },
  { id: 2, name: 'Jane Smith' },
]

const emptyValues = {
  title: '',
  description: '',
  employee_email: '',
  priority: 'medium',
  responsible_id: '',
  status: 'open',
}

function renderForm(props = {}) {
  const onSubmit = vi.fn()

  render(
    <MemoryRouter>
      <TicketForm
        initialValues={emptyValues}
        responsibles={responsibles}
        onSubmit={onSubmit}
        cancelTo="/"
        {...props}
      />
    </MemoryRouter>,
  )

  return { onSubmit, user: userEvent.setup() }
}

async function fillRequiredFields(user) {
  await user.type(screen.getByLabelText(/título/i), 'Impressora quebrada')
  await user.type(screen.getByLabelText(/descrição/i), 'Não imprime')
  await user.type(screen.getByLabelText(/e-mail/i), 'maria@empresa.com')
}

describe('TicketForm', () => {
  it('na criação, envia responsible_id nulo (atribuição automática) e não expõe o status', async () => {
    const { onSubmit, user } = renderForm()

    await fillRequiredFields(user)
    await user.click(screen.getByRole('button', { name: 'Criar chamado' }))

    expect(onSubmit).toHaveBeenCalledWith({
      title: 'Impressora quebrada',
      description: 'Não imprime',
      employee_email: 'maria@empresa.com',
      priority: 'medium',
      responsible_id: null,
    })
    expect(screen.queryByLabelText(/^status/i)).not.toBeInTheDocument()
  })

  it('envia o id numérico quando o responsável é escolhido manualmente', async () => {
    const { onSubmit, user } = renderForm()

    await fillRequiredFields(user)
    await user.selectOptions(screen.getByLabelText(/^responsável/i), 'Jane Smith')
    await user.click(screen.getByRole('button', { name: 'Criar chamado' }))

    expect(onSubmit).toHaveBeenCalledWith(expect.objectContaining({ responsible_id: 2 }))
  })

  it('na edição, vem preenchido e permite alterar o status', async () => {
    const { onSubmit, user } = renderForm({
      isEdit: true,
      initialValues: {
        title: 'Computador travou',
        description: 'Trava ao abrir o navegador',
        employee_email: 'joao@empresa.com',
        priority: 'high',
        responsible_id: 2,
        status: 'open',
      },
    })

    await user.selectOptions(screen.getByLabelText(/^status/i), 'resolved')
    await user.click(screen.getByRole('button', { name: 'Salvar alterações' }))

    expect(onSubmit).toHaveBeenCalledWith({
      title: 'Computador travou',
      description: 'Trava ao abrir o navegador',
      employee_email: 'joao@empresa.com',
      priority: 'high',
      responsible_id: 2,
      status: 'resolved',
    })
  })

  it('exibe embaixo do campo o erro de validação devolvido pela API', () => {
    renderForm({ errors: { title: ['O título do chamado é obrigatório.'] } })

    expect(screen.getByText('O título do chamado é obrigatório.')).toBeInTheDocument()
  })
})
