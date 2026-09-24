import { afterEach, describe, expect, it, vi } from 'vitest'
import { ApiError, request } from './client'

const jsonResponse = (status, body) => ({
  ok: status >= 200 && status < 300,
  status,
  json: () => Promise.resolve(body),
})

const mockFetch = (implementation) => {
  const fetchMock = vi.fn(implementation)
  vi.stubGlobal('fetch', fetchMock)
  return fetchMock
}

describe('api client', () => {
  afterEach(() => {
    vi.unstubAllGlobals()
  })

  it('monta a query string ignorando parâmetros vazios', async () => {
    const fetchMock = mockFetch(() => Promise.resolve(jsonResponse(200, { data: [] })))

    await request('/tickets', {
      params: { status: 'open', priority: '', responsible_id: null, page: 2 },
    })

    expect(fetchMock.mock.calls[0][0]).toMatch(/\/tickets\?status=open&page=2$/)
  })

  it('envia o corpo como JSON com os headers corretos', async () => {
    const fetchMock = mockFetch(() => Promise.resolve(jsonResponse(201, { data: {} })))

    await request('/tickets', { method: 'POST', body: { title: 'Teste' } })

    const [, options] = fetchMock.mock.calls[0]
    expect(options.method).toBe('POST')
    expect(options.headers).toMatchObject({
      Accept: 'application/json',
      'Content-Type': 'application/json',
    })
    expect(options.body).toBe(JSON.stringify({ title: 'Teste' }))
  })

  it('lança ApiError com os erros por campo quando a API responde 422', async () => {
    mockFetch(() =>
      Promise.resolve(
        jsonResponse(422, {
          message: 'Dados inválidos.',
          errors: { title: ['O título do chamado é obrigatório.'] },
        }),
      ),
    )

    const error = await request('/tickets', { method: 'POST', body: {} }).catch((e) => e)

    expect(error).toBeInstanceOf(ApiError)
    expect(error.status).toBe(422)
    expect(error.errors).toEqual({ title: ['O título do chamado é obrigatório.'] })
  })

  it('lança ApiError com status 0 quando não consegue conectar à API', async () => {
    mockFetch(() => Promise.reject(new TypeError('Failed to fetch')))

    const error = await request('/tickets').catch((e) => e)

    expect(error).toBeInstanceOf(ApiError)
    expect(error.status).toBe(0)
    expect(error.message).toMatch(/conectar/i)
  })
})
