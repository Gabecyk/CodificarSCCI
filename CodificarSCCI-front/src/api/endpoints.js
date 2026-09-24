import { request } from './client'

export const listTickets = (params, signal) =>
  request('/tickets', { params, signal })

export const getTicket = (id, signal) => request(`/tickets/${id}`, { signal })

export const createTicket = (payload) =>
  request('/tickets', { method: 'POST', body: payload })

export const updateTicket = (id, payload) =>
  request(`/tickets/${id}`, { method: 'PUT', body: payload })

export const listResponsibles = (signal) =>
  request('/responsibles', { signal })
