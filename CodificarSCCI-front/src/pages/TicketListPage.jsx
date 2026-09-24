import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { listTickets } from '../api/endpoints'
import Alert from '../components/Alert'
import Pagination from '../components/Pagination'
import TicketFilters from '../components/TicketFilters'
import TicketTable from '../components/TicketTable'
import { useResponsibles } from '../hooks/useResponsibles'

const EMPTY_FILTERS = { status: '', priority: '', responsible_id: '' }

export default function TicketListPage() {
  const [filters, setFilters] = useState(EMPTY_FILTERS)
  const [perPage, setPerPage] = useState(15)
  const [page, setPage] = useState(1)

  const [tickets, setTickets] = useState([])
  const [meta, setMeta] = useState(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)

  const { responsibles } = useResponsibles()

  useEffect(() => {
    const controller = new AbortController()

    listTickets({ ...filters, per_page: perPage, page }, controller.signal)
      .then((response) => {
        setTickets(response.data)
        setMeta(response.meta)
        setError(null)
      })
      .catch((err) => {
        if (err.name !== 'AbortError') setError(err.message)
      })
      .finally(() => {
        if (!controller.signal.aborted) setLoading(false)
      })

    return () => controller.abort()
  }, [filters, perPage, page])

  const handleFilterChange = (name, value) => {
    setLoading(true)
    setFilters((current) => ({ ...current, [name]: value }))
    setPage(1)
  }

  const handlePerPageChange = (value) => {
    setLoading(true)
    setPerPage(value)
    setPage(1)
  }

  const handlePageChange = (value) => {
    setLoading(true)
    setPage(value)
  }

  const handleClear = () => {
    setLoading(true)
    setFilters({ ...EMPTY_FILTERS })
    setPage(1)
  }

  return (
    <section>
      <div className="mb-4 flex items-center justify-between">
        <h1 className="text-2xl font-semibold">Chamados</h1>
        <Link
          to="/tickets/new"
          className="rounded bg-indigo-700 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-800"
        >
          Novo chamado
        </Link>
      </div>

      <TicketFilters
        filters={filters}
        perPage={perPage}
        responsibles={responsibles}
        onFilterChange={handleFilterChange}
        onPerPageChange={handlePerPageChange}
        onClear={handleClear}
      />

      {error && <Alert type="error">{error}</Alert>}

      {loading ? (
        <p className="py-8 text-center text-gray-500">Carregando chamados...</p>
      ) : (
        !error && (
          <>
            <TicketTable tickets={tickets} />
            <Pagination meta={meta} onPageChange={handlePageChange} />
          </>
        )
      )}
    </section>
  )
}
