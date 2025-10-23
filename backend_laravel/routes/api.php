<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TablonController;
use App\Http\Controllers\Api\SolicitudesController;
use Spatie\Permission\Middlewares\RoleMiddleware;
use App\Http\Controllers\Api\InsumosController;
use App\Http\Controllers\Api\CategoriasController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/categorias',        [CategoriasController::class, 'index']);   // listar (con búsqueda simple)
    Route::post('/categorias',       [CategoriasController::class, 'store']);   // crear (admin|tecnico)
    Route::put('/categorias/{id}',   [CategoriasController::class, 'update']);  // actualizar (admin|tecnico)
    Route::delete('/categorias/{id}',[CategoriasController::class, 'destroy']); // eliminar (admin|tecnico)
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/insumos',        [InsumosController::class, 'index']);   // listar + filtrar + paginar
    Route::post('/insumos',       [InsumosController::class, 'store']);   // crear
    Route::get('/insumos/{id}',   [InsumosController::class, 'show']);    // detalle
    Route::put('/insumos/{id}',   [InsumosController::class, 'update']);  // actualizar
    Route::delete('/insumos/{id}',[InsumosController::class, 'destroy']); // eliminar
});

Route::middleware('auth:sanctum')->get('/user', fn(Request $r) => $r->user());

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/tablon', [TablonController::class, 'index'])
        ->middleware('role:admin|tecnico|alumno');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/tablon', [TablonController::class, 'index']);

    Route::post('/grupos/{grupoId}/solicitudes', [SolicitudesController::class, 'store'])
        ->whereNumber('grupoId');

    Route::get('/solicitudes/{id}', [SolicitudesController::class, 'show'])
        ->whereNumber('id');

    Route::patch('/solicitudes/{id}/estado', [SolicitudesController::class, 'cambiarEstado'])
        ->whereNumber('id');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/solicitudes', [SolicitudesController::class, 'store']);               // delegado/admin
    Route::get('/solicitudes/{id}', [SolicitudesController::class, 'show']);            // cualquiera con acceso
    Route::patch('/solicitudes/{id}/estado', [SolicitudesController::class, 'cambiarEstado']); // tecnico/admin
});