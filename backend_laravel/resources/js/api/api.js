import axios from 'axios'
axios.defaults.withCredentials = true

export async function ensureCsrf(){ try{ await axios.get('/sanctum/csrf-cookie') }catch{} }

export const API = {
  tablon: '/api/tablon',

  solicitudes: '/api/solicitudes',
  // acciones: POST /api/solicitudes/{id}/aprobar|rechazar|preparar|entregar|cerrar
  // alternativo: PATCH /api/solicitudes/{id}/estado

  prestamos: '/api/prestamos',
  devoluciones: '/api/devoluciones', // POST /api/devoluciones/{prestamo}

  notificaciones: '/api/notificaciones',
  lookLabs: '/api/lookups/laboratorios',
  lookCursos: '/api/lookups/cursos',
  lookInsumos: '/api/lookups/insumos/buscar',
  lookEquipos: '/api/lookups/equipos/buscar',

  kardex: '/api/kardex',

  dashResumen: '/api/dashboard/resumen',
  statsHome: '/api/stats/home',
  misSolicitudes: '/api/solicitudes/mias',
  reportPdfSolicitudes: '/api/reportes/solicitudes',
  reportPdfStock: '/api/reportes/stock',
  reportCsvInsumos: '/api/reportes/insumos.csv',
  reportCsvPrestamos: '/api/reportes/prestamos.csv',
  reportCsvKardex: '/api/reportes/kardex.csv',
  reportCsvSolicitudes: '/api/reportes/solicitudes.csv',
}
