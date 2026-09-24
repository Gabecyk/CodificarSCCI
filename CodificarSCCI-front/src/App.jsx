import { Link, Route, Routes } from 'react-router-dom'
import Layout from './components/Layout'
import TicketDetailPage from './pages/TicketDetailPage'
import TicketFormPage from './pages/TicketFormPage'
import TicketListPage from './pages/TicketListPage'

function NotFoundPage() {
  return (
    <div className="py-12 text-center">
      <h1 className="mb-2 text-2xl font-semibold">Página não encontrada</h1>
      <Link to="/" className="text-indigo-700 hover:underline">
        Voltar para a lista de chamados
      </Link>
    </div>
  )
}

export default function App() {
  return (
    <Routes>
      <Route element={<Layout />}>
        <Route index element={<TicketListPage />} />
        <Route path="tickets/new" element={<TicketFormPage />} />
        <Route path="tickets/:id" element={<TicketDetailPage />} />
        <Route path="tickets/:id/edit" element={<TicketFormPage />} />
        <Route path="*" element={<NotFoundPage />} />
      </Route>
    </Routes>
  )
}
