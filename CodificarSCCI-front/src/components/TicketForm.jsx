import { useState } from 'react'
import { Link } from 'react-router-dom'
import { PRIORITY_OPTIONS, STATUS_OPTIONS } from '../constants'

const inputClass =
  'w-full rounded border border-gray-300 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none'

function Field({ label, error, hint, children }) {
  return (
    <label className="block">
      <span className="mb-1 block text-sm font-medium text-gray-700">{label}</span>
      {children}
      {hint && !error && <span className="mt-1 block text-xs text-gray-500">{hint}</span>}
      {error && <span className="mt-1 block text-xs text-red-600">{error}</span>}
    </label>
  )
}

export default function TicketForm({
  initialValues,
  responsibles,
  isEdit = false,
  submitting = false,
  errors = {},
  onSubmit,
  cancelTo,
}) {
  const [values, setValues] = useState(initialValues)

  const handleChange = (event) => {
    const { name, value } = event.target
    setValues((current) => ({ ...current, [name]: value }))
  }

  const handleSubmit = (event) => {
    event.preventDefault()

    const payload = {
      title: values.title,
      description: values.description,
      employee_email: values.employee_email,
      priority: values.priority,
      responsible_id: values.responsible_id === '' ? null : Number(values.responsible_id),
    }

    if (isEdit) payload.status = values.status

    onSubmit(payload)
  }

  const fieldError = (name) => errors[name]?.[0]

  return (
    <form
      onSubmit={handleSubmit}
      className="space-y-4 rounded border border-gray-200 bg-white p-6"
    >
      <Field label="Título" error={fieldError('title')}>
        <input
          name="title"
          value={values.title}
          onChange={handleChange}
          maxLength={255}
          required
          className={inputClass}
        />
      </Field>

      <Field label="Descrição" error={fieldError('description')}>
        <textarea
          name="description"
          value={values.description}
          onChange={handleChange}
          rows={5}
          required
          className={inputClass}
        />
      </Field>

      <Field label="E-mail do solicitante" error={fieldError('employee_email')}>
        <input
          type="email"
          name="employee_email"
          value={values.employee_email}
          onChange={handleChange}
          required
          className={inputClass}
        />
      </Field>

      <div className="grid gap-4 sm:grid-cols-2">
        <Field label="Prioridade" error={fieldError('priority')}>
          <select
            name="priority"
            value={values.priority}
            onChange={handleChange}
            className={inputClass}
          >
            {PRIORITY_OPTIONS.map((option) => (
              <option key={option.value} value={option.value}>
                {option.label}
              </option>
            ))}
          </select>
        </Field>

        {isEdit && (
          <Field label="Status" error={fieldError('status')}>
            <select
              name="status"
              value={values.status}
              onChange={handleChange}
              className={inputClass}
            >
              {STATUS_OPTIONS.map((option) => (
                <option key={option.value} value={option.value}>
                  {option.label}
                </option>
              ))}
            </select>
          </Field>
        )}
      </div>

      <Field
        label="Responsável"
        error={fieldError('responsible_id')}
        hint="Em automático, o chamado vai para quem tem menos chamados em aberto (abertos + em andamento)."
      >
        <select
          name="responsible_id"
          value={values.responsible_id}
          onChange={handleChange}
          className={inputClass}
        >
          <option value="">Atribuir automaticamente</option>
          {responsibles.map((responsible) => (
            <option key={responsible.id} value={responsible.id}>
              {responsible.name}
            </option>
          ))}
        </select>
      </Field>

      <div className="flex gap-3 pt-2">
        <button
          type="submit"
          disabled={submitting}
          className="rounded bg-indigo-700 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-800 disabled:opacity-50"
        >
          {submitting ? 'Salvando...' : isEdit ? 'Salvar alterações' : 'Criar chamado'}
        </button>
        <Link
          to={cancelTo}
          className="rounded border border-gray-300 px-4 py-2 text-sm hover:bg-gray-50"
        >
          Cancelar
        </Link>
      </div>
    </form>
  )
}
