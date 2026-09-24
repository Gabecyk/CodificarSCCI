const API_URL = (
  import.meta.env.VITE_API_URL ?? 'http://localhost:8000/api'
).replace(/\/$/, '')

export class ApiError extends Error {
  constructor(message, status, errors = {}) {
    super(message)
    this.name = 'ApiError'
    this.status = status
    this.errors = errors
  }
}

function buildQuery(params = {}) {
  const search = new URLSearchParams()

  Object.entries(params).forEach(([key, value]) => {
    if (value !== '' && value !== null && value !== undefined) {
      search.set(key, value)
    }
  })

  const query = search.toString()
  return query ? `?${query}` : ''
}

export async function request(path, { method = 'GET', body, params, signal } = {}) {
  let response

  try {
    response = await fetch(`${API_URL}${path}${buildQuery(params)}`, {
      method,
      signal,
      headers: {
        Accept: 'application/json',
        ...(body ? { 'Content-Type': 'application/json' } : {}),
      },
      body: body ? JSON.stringify(body) : undefined,
    })
  } catch (error) {
    if (error.name === 'AbortError') throw error
    throw new ApiError(`Não foi possível conectar à API (${API_URL}).`, 0)
  }

  const data = await response.json().catch(() => null)

  if (!response.ok) {
    const message =
      response.status === 404
        ? 'Registro não encontrado.'
        : (data?.message ?? 'Erro ao comunicar com a API.')

    throw new ApiError(message, response.status, data?.errors ?? {})
  }

  return data
}
