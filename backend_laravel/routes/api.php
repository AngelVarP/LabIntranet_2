<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TablonController;
use App\Http\Controllers\Api\SolicitudesController;
use Spatie\Permission\Middlewares\RoleMiddleware;

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