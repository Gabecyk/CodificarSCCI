export function formatDateTime(isoString) {
  if (!isoString) return '-'

  return new Date(isoString).toLocaleString('pt-BR', {
    dateStyle: 'short',
    timeStyle: 'short',
  })
}
