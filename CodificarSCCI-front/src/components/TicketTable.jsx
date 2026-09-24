import { Link } from 'react-router-dom'
import { formatDateTime } from '../utils/format'
import { PriorityBadge, StatusBadge } from './TicketBadges'

export default function TicketTable({ tickets }) {
  if (tickets.length === 0) {
    return (
      <p className="rounded border border-gray-200 bg-white p-6 text-center text-gray-500">
        Nenhum chamado encontrado.
      </p>
    )
  }

  return (
    <div className="overflow-x-auto rounded border border-gray-200 bg-white">
      <table className="min-w-full text-left text-sm">
        <thead className="bg-gray-100 text-gray-600">
          <tr>
            <th className="px-3 py-2">#</th>
            <th className="px-3 py-2">Título</th>
            <th className="px-3 py-2">Prioridade</th>
            <th className="px-3 py-2">Status</th>
            <th className="px-3 py-2">Responsável</th>
            <th className="px-3 py-2">Solicitante</th>
            <th className="px-3 py-2">Aberto em</th>
          </tr>
        </thead>
        <tbody>
          {tickets.map((ticket) => (
            <tr key={ticket.id} className="border-t border-gray-100 hover:bg-gray-50">
              <td className="px-3 py-2 text-gray-500">{ticket.id}</td>
              <td className="px-3 py-2 font-medium">
                <Link
                  to={`/tickets/${ticket.id}`}
                  className="text-indigo-700 hover:underline"
                >
                  {ticket.title}
                </Link>
              </td>
              <td className="px-3 py-2">
                <PriorityBadge priority={ticket.priority} />
              </td>
              <td className="px-3 py-2">
                <StatusBadge status={ticket.status} />
              </td>
              <td className="px-3 py-2">
                {ticket.responsible?.name ?? (
                  <span className="text-gray-400">Sem responsável</span>
                )}
              </td>
              <td className="px-3 py-2">{ticket.employee_email}</td>
              <td className="px-3 py-2 whitespace-nowrap">
                {formatDateTime(ticket.opened_at)}
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  )
}
