<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

// --- Controllers ---
use App\Http\Controllers\Api\TablonController;
use App\Http\Controllers\Api\SolicitudesController;

use App\Http\Controllers\Api\InsumosController;
use App\Http\Controllers\Api\CategoriasInsumoController;
use App\Http\Controllers\Api\LaboratoriosController;

use App\Http\Controllers\Api\SolicitudFlowController;        // si ya lo usas
use App\Http\Controllers\Api\SolicitudesFlowController;      // (usaremos ESTE para el flujo)
use App\Http\Controllers\Api\SolicitudesReadController;      // detalle lectura solicitud
use App\Http\Controllers\Api\SolicitudItemsController;       // agregar/quitar items

use App\Http\Controllers\Api\DelegadoController;
use App\Http\Controllers\Api\ComentariosController;

use App\Http\Controllers\Api\ReportesController;

use App\Http\Controllers\Api\PrestamosController;
use App\Http\Controllers\Api\DevolucionesController;
use App\Http\Controllers\Api\DevolucionesAdjuntosController;

use App\Http\Controllers\Api\NotificacionesController;
use App\Http\Controllers\Api\AlertasController;

use App\Http\Controllers\Api\LotesController;
use App\Http\Controllers\Api\KardexController;
use App\Http\Controllers\Api\InventarioController;

use App\Http\Controllers\Api\CatalogosController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\SearchController;

use App\Http\Controllers\Api\PracticasController;
use App\Http\Controllers\Api\PracticaMaterialController;
use App\Http\Controllers\Api\PracticaActionsController;
use App\Http\Controllers\Api\PracticaAdjuntosController;
use App\Http\Controllers\Api\SolicitudAdjuntosController;

use App\Http\Controllers\Api\CursosController;
use App\Http\Controllers\Api\SeccionesController;
use App\Http\Controllers\Api\GruposController;

use App\Http\Controllers\Api\EquiposController;
use App\Http\Controllers\Api\EquiposReadController; // kardex/mto de equipos

// =========================
//   PUBLIC (si aplica)
// =========================

// Ejemplo: devolver el usuario autenticado (requiere sanctum)
Route::middleware('auth:sanctum')->get('/user', fn(Request $r) => $r->user());


// =========================
//   AUTENTICADAS
// =========================
Route::middleware(['auth:sanctum'])->group(function () {

    // ---------------------------------
    // Dashboard / Resúmenes
    // ---------------------------------
    Route::get('/dashboard/kpis',    [DashboardController::class,'kpis']);
    Route::get('/dashboard/series',  [DashboardController::class,'series']);
    Route::get('/dashboard/resumen', [DashboardController::class,'resumen']);

    // ---------------------------------
    // Lookups / Búsquedas rápidas
    // ---------------------------------
    Route::get('/lookups/laboratorios',              [SearchController::class,'laboratorios']);
    Route::get('/lookups/cursos',                    [SearchController::class,'cursos']);
    Route::get('/lookups/secciones',                 [SearchController::class,'secciones']); // ?curso_id=
    Route::get('/lookups/grupos',                    [SearchController::class,'grupos']);    // ?seccion_id=
    Route::get('/lookups/categorias-insumo',         [SearchController::class,'categoriasInsumo']);
    Route::get('/lookups/insumos/buscar',            [SearchController::class,'insumosBuscar']);   // ?q=
    Route::get('/lookups/equipos/buscar',            [SearchController::class,'equiposBuscar']);   // ?q=
    Route::get('/search/insumos',                    [SearchController::class,'insumos']);         // ?q=
    Route::get('/search/equipos',                    [SearchController::class,'equipos']);         // ?q=
    Route::get('/search/users',                      [SearchController::class,'users']);           // ?q=&role=

    // ---------------------------------
    // Catálogos (laboratorios / categorías de insumo)
    // ---------------------------------
    Route::get   ('/laboratorios',         [LaboratoriosController::class,'index']);
    Route::post  ('/laboratorios',         [LaboratoriosController::class,'store']);   // admin|tecnico
    Route::put   ('/laboratorios/{id}',    [LaboratoriosController::class,'update']);  // admin|tecnico
    Route::delete('/laboratorios/{id}',    [LaboratoriosController::class,'destroy']); // admin|tecnico

    Route::get   ('/categorias-insumo',       [CategoriasInsumoController::class,'index']);
    Route::post  ('/categorias-insumo',       [CategoriasInsumoController::class,'store']);   // admin|tecnico
    Route::put   ('/categorias-insumo/{id}',  [CategoriasInsumoController::class,'update']);  // admin|tecnico
    Route::delete('/categorias-insumo/{id}',  [CategoriasInsumoController::class,'destroy']); // admin|tecnico
    Route::get('solicitudes/mias', [SolicitudesController::class, 'mias']);
    
    Route::get('stats/home', function () {
        $safeCount = fn($table, $col=null, $vals=null) =>
            DB::connection()->getSchemaBuilder()->hasTable($table)
                ? ( $col ? DB::table($table)->whereIn($col, (array)$vals)->count()
                         : DB::table($table)->count() )
                : 0;

        return [
            'sol_pendientes'   => $safeCount('solicitudes', 'estado', ['PENDIENTE']),
            'prestamos_abiertos'=> $safeCount('prestamos', 'estado', ['ABIERTO','PARCIAL']),
            'insumos'          => $safeCount('insumos'),
            'equipos'          => $safeCount('equipos'),
        ];
    });


    // ---------------------------------
    // Cursos / Secciones / Grupos
    // ---------------------------------
    // Cursos
    Route::get   ('/cursos',        [CursosController::class,'index']);
    Route::get   ('/cursos/{id}',   [CursosController::class,'show']);
    Route::post  ('/cursos',        [CursosController::class,'store']);   // admin|profesor
    Route::put   ('/cursos/{id}',   [CursosController::class,'update']);  // admin|profesor
    Route::delete('/cursos/{id}',   [CursosController::class,'destroy']); // admin|profesor (+guardas)

    // Secciones
    Route::get   ('/secciones',        [SeccionesController::class,'index']); // ?curso_id=
    Route::get   ('/secciones/{id}',   [SeccionesController::class,'show']);
    Route::post  ('/secciones',        [SeccionesController::class,'store']);   // admin|profesor
    Route::put   ('/secciones/{id}',   [SeccionesController::class,'update']);  // admin|profesor
    Route::delete('/secciones/{id}',   [SeccionesController::class,'destroy']); // admin|profesor

    // Profesores por sección
    Route::get ('/secciones/{id}/profesores',          [SeccionesController::class,'profesores']);
    Route::post('/secciones/{id}/profesores/asignar',  [SeccionesController::class,'asignarProfesor']); // admin|profesor
    Route::post('/secciones/{id}/profesores/revocar',  [SeccionesController::class,'revocarProfesor']); // admin|profesor

    // Grupos
    Route::get   ('/grupos',        [GruposController::class,'index']);   // ?seccion_id=
    Route::get   ('/grupos/{id}',   [GruposController::class,'show']);
    Route::post  ('/grupos',        [GruposController::class,'store']);   // admin|profesor
    Route::put   ('/grupos/{id}',   [GruposController::class,'update']);  // admin|profesor
    Route::delete('/grupos/{id}',   [GruposController::class,'destroy']); // admin|profesor

    // Miembros del grupo (alumnos)
    Route::get ('/grupos/{id}/alumnos',          [GruposController::class,'alumnos']);
    Route::post('/grupos/{id}/alumnos/agregar',  [GruposController::class,'agregarAlumno']); // admin|profesor
    Route::post('/grupos/{id}/alumnos/quitar',   [GruposController::class,'quitarAlumno']);  // admin|profesor

    // Delegado
    Route::post('/grupos/{grupo}/delegado/asignar', [DelegadoController::class,'asignar']); // admin|profesor
    Route::post('/grupos/{grupo}/delegado/revocar', [DelegadoController::class,'revocar']); // admin|profesor

    // ---------------------------------
    // Insumos + Inventario + Lotes + Kardex
    // ---------------------------------
    // CRUD Insumos
    Route::get   ('/insumos',        [InsumosController::class, 'index']);
    Route::post  ('/insumos',        [InsumosController::class, 'store']);   // admin|tecnico
    Route::get   ('/insumos/{id}',   [InsumosController::class, 'show']);
    Route::put   ('/insumos/{id}',   [InsumosController::class, 'update']);  // admin|tecnico
    Route::delete('/insumos/{id}',   [InsumosController::class, 'destroy']); // admin|tecnico

    // Detalle Insumo (cabecera + lotes + kardex)
    Route::get('/insumos/{id}/detalle', [InsumosController::class,'detalle']);

    // Lotes de Insumo
    Route::get ('/insumos/{insumo}/lotes',             [LotesController::class,'listar']);
    Route::post('/insumos/{insumo}/lotes',             [LotesController::class,'crear']);    // crear lote
    Route::post('/insumos/{insumo}/lotes/{loteId}',    [LotesController::class,'ajustar']);  // +/- / transferir

    // Stock/Kardex/Ajuste por Insumo
    Route::get ('/insumos/{id}/stock',   [InventarioController::class,'stockPorInsumo']);  // ?laboratorio_id=
    Route::get ('/insumos/{id}/kardex',  [InventarioController::class,'kardexPorInsumo']); // ?laboratorio_id=&desde=&hasta=
    Route::post('/insumos/{id}/ajuste',  [InventarioController::class,'ajustarStock']);    // body: items...

    // Kardex general
    Route::get('/kardex', [KardexController::class,'index']); // ?tipo_item=&laboratorio_id=&item_id=&desde=&hasta=

    Route::get ('solicitudes',              [SolicitudesController::class, 'index']);     // LISTAR (para Profesor)
    Route::post('solicitudes',              [SolicitudesController::class, 'store']);     // CREAR
    Route::get ('solicitudes/{id}',         [SolicitudesController::class, 'show']);      // DETALLE
    Route::post('solicitudes/{id}/estado',  [SolicitudesController::class, 'cambiarEstado']);

    // Alertas
    Route::get('/alertas/caducidad', [AlertasController::class,'caducidad']); // ?dias=30
    Route::get('/alertas/minimos',   [AlertasController::class,'bajoMinimo']);

    // ---------------------------------
    // Equipos
    // ---------------------------------
    // Detalle (nuestro método en EquiposController)
    Route::get('/equipos/{id}/detalle', [EquiposController::class,'detalle']);
    // Kardex/Mantenimiento (lectura) en EquiposReadController
    Route::get('/equipos/{id}/kardex',  [EquiposReadController::class,'kardex']);          // ?desde=&hasta=
    Route::get('/equipos/{id}/mto',     [EquiposReadController::class,'mantenimientos']);  // ?estado=&desde=&hasta=

    // ---------------------------------
    // Prácticas + Material + Acciones
    // ---------------------------------
    Route::get   ('/practicas',         [PracticasController::class,'index']);
    Route::get   ('/practicas/{id}',    [PracticasController::class,'show']);
    Route::post  ('/practicas',         [PracticasController::class,'store']);   // admin|profesor
    Route::put   ('/practicas/{id}',    [PracticasController::class,'update']);  // admin|profesor
    Route::delete('/practicas/{id}',    [PracticasController::class,'destroy']); // admin|profesor

    Route::get   ('/practicas/{id}/material',                   [PracticaMaterialController::class,'listar']);
    Route::post  ('/practicas/{id}/material',                   [PracticaMaterialController::class,'upsert']);   // bulk upsert
    Route::delete('/practicas/{id}/material/{tipo}/{itemId}',   [PracticaMaterialController::class,'eliminar']);

    Route::post('/practicas/{id}/crear-solicitud', [PracticaActionsController::class,'crearSolicitud']);

    // Adjuntos de práctica
    Route::get   ('/practicas/{id}/adjuntos',        [PracticaAdjuntosController::class,'listar']);
    Route::post  ('/practicas/{id}/adjuntos',        [PracticaAdjuntosController::class,'subir']);      // multipart
    Route::delete('/practicas/adjuntos/{aid}',       [PracticaAdjuntosController::class,'eliminar']);

    // ---------------------------------
    // Solicitudes (CRUD mínimo + lectura + items + adjuntos)
    // ---------------------------------
    // Crear/Ver/Cambiar estado (soft)
    Route::post ('/solicitudes',              [SolicitudesController::class, 'store']);        // delegado/admin
    Route::get  ('/solicitudes/{id}',         [SolicitudesController::class, 'show']);         // con acceso
    Route::patch('/solicitudes/{id}/estado',  [SolicitudesController::class, 'cambiarEstado']); // si lo usas

    // Lectura extendida
    Route::get('/solicitudes/{id}/detalle', [SolicitudesReadController::class,'show']);

    // Items de solicitud
    Route::post('/solicitudes/{id}/items/agregar', [SolicitudItemsController::class,'agregar']);
    Route::post('/solicitudes/{id}/items/quitar',  [SolicitudItemsController::class,'quitar']);

    // Adjuntos de solicitud
    Route::get   ('/solicitudes/{id}/adjuntos',     [SolicitudAdjuntosController::class,'listar']);
    Route::post  ('/solicitudes/{id}/adjuntos',     [SolicitudAdjuntosController::class,'subir']);     // multipart
    Route::delete('/solicitudes/adjuntos/{aid}',    [SolicitudAdjuntosController::class,'eliminar']);

    // Flujo de estados (USAR: SolicitudesFlowController)
    Route::post('/solicitudes/{id}/aprobar',   [SolicitudesFlowController::class,'aprobar']);   // profesor|admin
    Route::post('/solicitudes/{id}/rechazar',  [SolicitudesFlowController::class,'rechazar']);  // profesor|admin
    Route::post('/solicitudes/{id}/preparar',  [SolicitudesFlowController::class,'preparar']);  // tecnico|admin
    Route::post('/solicitudes/{id}/entregar',  [SolicitudesFlowController::class,'entregar']);  // tecnico|admin
    Route::post('/solicitudes/{id}/cerrar',    [SolicitudesFlowController::class,'cerrar']);    // tecnico|admin

    // ---------------------------------
    // Tablón (vista)
    // ---------------------------------
    Route::get('/tablon', [TablonController::class, 'index'])
        ->middleware('role:admin|tecnico|alumno');

    // ---------------------------------
    // Préstamos / Devoluciones
    // ---------------------------------
    Route::post('/prestamos',                 [PrestamosController::class,'store']);          // crear
    Route::post('/prestamos/{id}/agregar',    [PrestamosController::class,'agregarEquipo']);  // agregar equipo(s)
    Route::get ('/prestamos/{id}',            [PrestamosController::class,'show']);           // ver
    Route::post('/prestamos/{id}/cerrar',     [PrestamosController::class,'cerrar']);         // cerrar

    Route::post('/devoluciones/{prestamo}',   [DevolucionesController::class,'registrar']);   // registrar devolución

    // Adjuntos de Devolución
    Route::get   ('/devoluciones/{id}/adjuntos',      [DevolucionesAdjuntosController::class,'index']);
    Route::post  ('/devoluciones/{id}/adjuntos',      [DevolucionesAdjuntosController::class,'store']);
    Route::delete('/devoluciones/adjuntos/{adjuntoId}', [DevolucionesAdjuntosController::class,'destroy']);

    // ---------------------------------
    // Notificaciones
    // ---------------------------------
    Route::get ('/notificaciones',               [NotificacionesController::class,'index']);        // ?solo_no_leidas=1
    Route::post('/notificaciones/{id}/leer',     [NotificacionesController::class,'marcarLeida']);
    Route::post('/notificaciones/leer-todas',    [NotificacionesController::class,'marcarTodas']);

    // ---------------------------------
    // Reportes
    // ---------------------------------
    Route::get('/reportes/insumos.csv',         [ReportesController::class,'insumosCsv']);
    Route::get('/reportes/solicitudes.csv',     [ReportesController::class,'solicitudesCsv']);
    Route::get('/reportes/devoluciones.csv',    [ReportesController::class,'devolucionesCsv']);      // filtros
    Route::get('/reportes/devoluciones/incidencias', [ReportesController::class,'devolucionesIncidencias']);
    Route::get('/reportes/kardex.csv',          [ReportesController::class,'kardexCsv']);
    Route::get('/reportes/prestamos.csv',       [ReportesController::class,'prestamosCsv']);

    // (PDFs)
    Route::get('/reportes/stock',               [ReportesController::class,'stockPdf']);
    Route::get('/reportes/solicitudes',         [ReportesController::class,'solicitudesPdf']);
});
Route::middleware('auth:sanctum')->get('/me', function (Request $r) {
    $u = $r->user();
    // Si usas Spatie:
    $roles = method_exists($u, 'getRoleNames') ? $u->getRoleNames() : collect();
    return [
        'id'    => $u->id,
        'name'  => $u->name,
        'email' => $u->email,
        'roles' => $roles->values(),
    ];
});
Route::middleware('auth:sanctum')->group(function () {
    // REST básico para Equipos
    Route::get   ('equipos',          [EquiposController::class, 'index']);
    Route::post  ('equipos',          [EquiposController::class, 'store']);
    Route::get   ('equipos/{id}',     [EquiposController::class, 'show']);
    Route::put   ('equipos/{id}',     [EquiposController::class, 'update']);
    Route::delete('equipos/{id}',     [EquiposController::class, 'destroy']);

    // Lookup de laboratorios (si aún no lo tenías)
    Route::get('lookups/laboratorios', function () {
        return DB::table('laboratorios')->orderBy('nombre')->get(['id','nombre']);
    });
});