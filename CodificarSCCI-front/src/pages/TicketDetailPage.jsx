import { useEffect, useState } from 'react'
import { Link, useLocation, useParams } from 'react-router-dom'
import { getTicket } from '../api/endpoints'
import Alert from '../components/Alert'
import { PriorityBadge, StatusBadge } from '../components/TicketBadges'
import { useResponsibles } from '../hooks/useResponsibles'
import { formatDateTime } from '../utils/format'

function Detail({ label, children }) {
  return (
    <div>
      <dt className="text-xs uppercase tracking-wide text-gray-500">{label}</dt>
      <dd className="mt-1">{children}</dd>
    </div>
  )
}

export default function TicketDetailPage() {
  const { id } = useParams()

  return <TicketDetail key={id} id={id} />
}

function TicketDetail({ id }) {
  const location = useLocation()

  const [ticket, setTicket] = useState(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)

  const { responsibles, loading: loadingResponsibles } = useResponsibles()

  useEffect(() => {
    const controller = new AbortController()

    getTicket(id, controller.signal)
      .then((response) => setTicket(response.data))
      .catch((err) => {
        if (err.name !== 'AbortError') setError(err.message)
      })
      .finally(() => {
        if (!controller.signal.aborted) setLoading(false)
      })

    return () => controller.abort()
  }, [id])

  if (loading) {
    return <p className="py-8 text-center text-gray-500">Carregando chamado...</p>
  }

  if (error) {
    return (
      <>
        <Alert type="error">{error}</Alert>
        <Link to="/" className="text-indigo-700 hover:underline">
          Voltar para a lista
        </Link>
      </>
    )
  }

  const responsible = responsibles.find((item) => item.id === ticket.responsible_id)

  return (
    <section>
      {location.state?.message && (
        <Alert type="success">{location.state.message}</Alert>
      )}

      <div className="mb-4 flex items-center justify-between">
        <h1 className="text-2xl font-semibold">
          #{ticket.id} · {ticket.title}
        </h1>
        <div className="flex gap-2">
          <Link
            to="/"
            className="rounded border border-gray-300 px-4 py-2 text-sm hover:bg-gray-50"
          >
            Voltar
          </Link>
          <Link
            to={`/tickets/${ticket.id}/edit`}
            className="rounded bg-indigo-700 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-800"
          >
            Editar
          </Link>
        </div>
      </div>

      <div className="space-y-6 rounded border border-gray-200 bg-white p-6">
        <dl className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          <Detail label="Status">
            <StatusBadge status={ticket.status} />
          </Detail>
          <Detail label="Prioridade">
            <PriorityBadge priority={ticket.priority} />
          </Detail>
          <Detail label="Responsável">
            {ticket.responsible_id
              ? loadingResponsibles
                ? '...'
                : (responsible?.name ?? `Responsável #${ticket.responsible_id}`)
              : 'Sem responsável'}
          </Detail>
          <Detail label="Solicitante">{ticket.employee_email}</Detail>
          <Detail label="Aberto em">{formatDateTime(ticket.opened_at)}</Detail>
          <Detail label="Última atualização">{formatDateTime(ticket.updated_at)}</Detail>
        </dl>

        <div>
          <h2 className="text-xs uppercase tracking-wide text-gray-500">Descrição</h2>
          <p className="mt-1 whitespace-pre-wrap">{ticket.description}</p>
        </div>
      </div>
    </section>
  )
}
