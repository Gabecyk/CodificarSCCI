export default function Pagination({ meta, onPageChange }) {
  if (!meta) return null

  const { current_page: current, last_page: last, total } = meta

  return (
    <div className="mt-4 flex items-center justify-between text-sm">
      <span className="text-gray-600">
        Página {current} de {last} · {total} chamado(s)
      </span>
      <div className="flex gap-2">
        <button
          type="button"
          onClick={() => onPageChange(current - 1)}
          disabled={current <= 1}
          className="rounded border border-gray-300 bg-white px-3 py-1 disabled:opacity-40"
        >
          Anterior
        </button>
        <button
          type="button"
          onClick={() => onPageChange(current + 1)}
          disabled={current >= last}
          className="rounded border border-gray-300 bg-white px-3 py-1 disabled:opacity-40"
        >
          Próxima
        </button>
      </div>
    </div>
  )
}
