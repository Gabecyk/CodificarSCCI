import { useEffect, useState } from 'react'
import { listResponsibles } from '../api/endpoints'

export function useResponsibles() {
  const [responsibles, setResponsibles] = useState([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)

  useEffect(() => {
    const controller = new AbortController()

    listResponsibles(controller.signal)
      .then((response) => setResponsibles(response.data))
      .catch((err) => {
        if (err.name !== 'AbortError') setError(err.message)
      })
      .finally(() => {
        if (!controller.signal.aborted) setLoading(false)
      })

    return () => controller.abort()
  }, [])

  return { responsibles, loading, error }
}
