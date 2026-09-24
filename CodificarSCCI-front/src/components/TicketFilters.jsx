import {
  PER_PAGE_OPTIONS,
  PRIORITY_OPTIONS,
  STATUS_OPTIONS,
} from '../constants'

const selectClass = 'w-full rounded border border-gray-300 bg-white px-2 py-1.5 text-sm'

function Field({ label, children }) {
  return (
    <label className="block text-sm">
      <span className="mb-1 block text-gray-600">{label}</span>
      {children}
    </label>
  )
}

export default function TicketFilters({
  filters,
  perPage,
  responsibles,
  onFilterChange,
  onPerPageChange,
  onClear,
}) {
  return (
    <div className="mb-4 grid gap-3 rounded border border-gray-200 bg-white p-4 sm:grid-cols-2 lg:grid-cols-5">
      <Field label="Status">
        <select
          className={selectClass}
          value={filters.status}
          onChange={(e) => onFilterChange('status', e.target.value)}
        >
          <option value="">Todos</option>
          {STATUS_OPTIONS.map((option) => (
            <option key={option.value} value={option.value}>
              {option.label}
            </option>
          ))}
        </select>
      </Field>

      <Field label="Prioridade">
        <select
          className={selectClass}
          value={filters.priority}
          onChange={(e) => onFilterChange('priority', e.target.value)}
        >
          <option value="">Todas</option>
          {PRIORITY_OPTIONS.map((option) => (
            <option key={option.value} value={option.value}>
              {option.label}
            </option>
          ))}
        </select>
      </Field>

      <Field label="Responsável">
        <select
          className={selectClass}
          value={filters.responsible_id}
          onChange={(e) => onFilterChange('responsible_id', e.target.value)}
        >
          <option value="">Todos</option>
          {responsibles.map((responsible) => (
            <option key={responsible.id} value={responsible.id}>
              {responsible.name}
            </option>
          ))}
        </select>
      </Field>

      <Field label="Por página">
        <select
          className={selectClass}
          value={perPage}
          onChange={(e) => onPerPageChange(Number(e.target.value))}
        >
          {PER_PAGE_OPTIONS.map((option) => (
            <option key={option} value={option}>
              {option}
            </option>
          ))}
        </select>
      </Field>

      <div className="flex items-end">
        <button
          type="button"
          onClick={onClear}
          className="w-full rounded border border-gray-300 bg-gray-50 px-3 py-1.5 text-sm hover:bg-gray-100"
        >
          Limpar filtros
        </button>
      </div>
    </div>
  )
}
