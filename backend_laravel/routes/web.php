<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;

Route::get('/', fn () => inertia('Welcome'));

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', fn () => inertia('Dashboard'))->name('dashboard');

    // Vistas principales
    Route::get('/tablon', fn () => inertia('Tablon'))
        ->middleware('role:admin|tecnico|alumno')
        ->name('tablon.index');

    Route::get('/insumos', fn () => inertia('Insumos/Index'))
        ->middleware('role:admin|tecnico')
        ->name('insumos.index');

    Route::get('/prestamos', fn () => inertia('Prestamos/Index'))
        ->middleware('role:admin|tecnico')
        ->name('prestamos.index');

    Route::get('/reportes', fn () => inertia('Reportes/Index'))
        ->middleware('role:admin|profesor|tecnico')
        ->name('reportes.index');

    Route::get('/notificaciones', fn () => inertia('Notificaciones'))
        ->name('notificaciones.index');

    Route::get('/academico', fn () => inertia('Academico'))
        ->middleware('role:admin|profesor')
        ->name('academico.index');

    // Crear va ANTES que {id}
    Route::get('/solicitudes/crear', fn () => inertia('SolicitudCrear'))
        ->middleware('role:admin|alumno')
        ->name('solicitudes.create');

    Route::get('/solicitudes/{id}', fn () => inertia('Solicitudes/Show'))
        ->middleware('role:admin|profesor|tecnico|alumno')
        ->name('solicitudes.show');

    Route::get('/alumno/solicitudes', fn () => inertia('Alumno/MisSolicitudes'))
        ->middleware('role:admin|alumno')
        ->name('alumno.solicitudes');

    Route::get('/profesor/solicitudes', fn () => inertia('Profesor/Solicitudes'))
        ->middleware('role:admin|profesor')
        ->name('profesor.solicitudes');

    Route::get('/prestamos/{id}', fn () => inertia('Prestamos/Show'))
        ->middleware('role:admin|tecnico')
        ->name('prestamos.show');

    Route::get('/kardex', fn () => inertia('Kardex/Index'))
        ->middleware('role:admin|tecnico')
        ->name('kardex.index');

    Route::get('/equipos', fn () => inertia('Equipos'))
        ->middleware('role:admin|tecnico')
        ->name('equipos.index');

    // Perfil
    Route::get('/profile',  [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile',[ProfileController::class, 'destroy'])->name('profile.destroy');

    // Profesor: gestión de delegado por sección/grupo
    Route::get('/profesor/delegados', fn () => inertia('Profesor/Delegados'))
        ->middleware('role:admin|profesor')
        ->name('profesor.delegados');
    Route::get('/profesor/solicitudes', fn () => inertia('Profesor/Solicitudes'))
        ->middleware('role:admin|profesor')
        ->name('profesor.solicitudes');

    Route::get('/alumno/solicitudes', fn () => inertia('Alumno/MisSolicitudes'))
        ->middleware('role:admin|alumno')
        ->name('alumno.solicitudes');

    Route::get('/reportes', fn () => inertia('Reportes/Index'))
        ->middleware('role:admin|profesor|tecnico')
        ->name('reportes.index');



});

require __DIR__.'/auth.php';
