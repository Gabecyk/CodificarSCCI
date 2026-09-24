import { useEffect, useState } from 'react'
import { useNavigate, useParams } from 'react-router-dom'
import { createTicket, getTicket, updateTicket } from '../api/endpoints'
import Alert from '../components/Alert'
import TicketForm from '../components/TicketForm'
import { useResponsibles } from '../hooks/useResponsibles'

const EMPTY_VALUES = {
  title: '',
  description: '',
  employee_email: '',
  priority: 'medium',
  responsible_id: '',
  status: 'open',
}

function toFormValues(ticket) {
  return {
    title: ticket.title,
    description: ticket.description,
    employee_email: ticket.employee_email,
    priority: ticket.priority,
    responsible_id: ticket.responsible_id ?? '',
    status: ticket.status,
  }
}

export default function TicketFormPage() {
  const { id } = useParams()
  const isEdit = Boolean(id)
  const navigate = useNavigate()

  const {
    responsibles,
    loading: loadingResponsibles,
    error: responsiblesError,
  } = useResponsibles()

  const [ticket, setTicket] = useState(null)
  const [loadingTicket, setLoadingTicket] = useState(isEdit)
  const [loadError, setLoadError] = useState(null)

  const [submitting, setSubmitting] = useState(false)
  const [fieldErrors, setFieldErrors] = useState({})
  const [submitError, setSubmitError] = useState(null)

  useEffect(() => {
    if (!isEdit) return

    const controller = new AbortController()

    getTicket(id, controller.signal)
      .then((response) => setTicket(response.data))
      .catch((err) => {
        if (err.name !== 'AbortError') setLoadError(err.message)
      })
      .finally(() => {
        if (!controller.signal.aborted) setLoadingTicket(false)
      })

    return () => controller.abort()
  }, [id, isEdit])

  const handleSubmit = async (payload) => {
    setSubmitting(true)
    setFieldErrors({})
    setSubmitError(null)

    try {
      const response = isEdit
        ? await updateTicket(id, payload)
        : await createTicket(payload)

      navigate(`/tickets/${response.data.id}`, {
        state: {
          message: isEdit
            ? 'Chamado atualizado com sucesso.'
            : 'Chamado criado com sucesso.',
        },
      })
    } catch (err) {
      if (err.status === 422) {
        setFieldErrors(err.errors)
        setSubmitError('Corrija os campos destacados e tente novamente.')
      } else {
        setSubmitError(err.message)
      }
      setSubmitting(false)
    }
  }

  const title = isEdit ? `Editar chamado #${id}` : 'Novo chamado'

  if (loadError) {
    return <Alert type="error">{loadError}</Alert>
  }

  if (loadingResponsibles || loadingTicket) {
    return <p className="py-8 text-center text-gray-500">Carregando...</p>
  }

  return (
    <section className="mx-auto max-w-2xl">
      <h1 className="mb-4 text-2xl font-semibold">{title}</h1>

      {responsiblesError && (
        <Alert type="error">
          Não foi possível carregar os responsáveis ({responsiblesError}). A
          atribuição automática continua disponível.
        </Alert>
      )}
      {submitError && <Alert type="error">{submitError}</Alert>}

      <TicketForm
        initialValues={isEdit ? toFormValues(ticket) : EMPTY_VALUES}
        responsibles={responsibles}
        isEdit={isEdit}
        submitting={submitting}
        errors={fieldErrors}
        onSubmit={handleSubmit}
        cancelTo={isEdit ? `/tickets/${id}` : '/'}
      />
    </section>
  )
}
