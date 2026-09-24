import { PRIORITY_LABELS, STATUS_LABELS } from '../constants'

const STATUS_STYLES = {
  open: 'bg-blue-100 text-blue-800',
  in_progress: 'bg-amber-100 text-amber-800',
  resolved: 'bg-green-100 text-green-800',
  closed: 'bg-gray-200 text-gray-700',
}

const PRIORITY_STYLES = {
  low: 'bg-gray-100 text-gray-700',
  medium: 'bg-yellow-100 text-yellow-800',
  high: 'bg-orange-100 text-orange-800',
  critical: 'bg-red-100 text-red-800',
}

function Badge({ className, children }) {
  return (
    <span
      className={`inline-block rounded-full px-2 py-0.5 text-xs font-medium ${className}`}
    >
      {children}
    </span>
  )
}

export function StatusBadge({ status }) {
  return (
    <Badge className={STATUS_STYLES[status] ?? 'bg-gray-100'}>
      {STATUS_LABELS[status] ?? status}
    </Badge>
  )
}

export function PriorityBadge({ priority }) {
  return (
    <Badge className={PRIORITY_STYLES[priority] ?? 'bg-gray-100'}>
      {PRIORITY_LABELS[priority] ?? priority}
    </Badge>
  )
}
