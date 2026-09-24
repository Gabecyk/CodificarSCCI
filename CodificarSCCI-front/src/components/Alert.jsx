const STYLES = {
  error: 'border-red-300 bg-red-50 text-red-800',
  success: 'border-green-300 bg-green-50 text-green-800',
  info: 'border-blue-300 bg-blue-50 text-blue-800',
}

export default function Alert({ type = 'info', children }) {
  return (
    <div
      role={type === 'error' ? 'alert' : 'status'}
      className={`mb-4 rounded border px-4 py-3 text-sm ${STYLES[type]}`}
    >
      {children}
    </div>
  )
}
