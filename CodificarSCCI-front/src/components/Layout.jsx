import { Link, NavLink, Outlet } from 'react-router-dom'

const navClass = ({ isActive }) =>
  `rounded px-3 py-1 text-sm ${isActive ? 'bg-white/20 font-semibold' : 'hover:bg-white/10'}`

export default function Layout() {
  return (
    <div className="min-h-screen bg-gray-50 text-gray-900">
      <header className="bg-indigo-700 text-white">
        <div className="mx-auto flex max-w-6xl items-center justify-between px-4 py-3">
          <Link to="/" className="text-lg font-semibold">
            SCCI · Chamados Internos
          </Link>
          <nav className="flex gap-2">
            <NavLink to="/" end className={navClass}>
              Chamados
            </NavLink>
            <NavLink to="/tickets/new" className={navClass}>
              Novo chamado
            </NavLink>
          </nav>
        </div>
      </header>

      <main className="mx-auto max-w-6xl px-4 py-6">
        <Outlet />
      </main>
    </div>
  )
}
