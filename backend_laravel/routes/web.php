<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ProfileController;

Route::get('/', fn() => Inertia::render('Welcome'));

Route::middleware(['auth','verified'])->group(function () {
    Route::get('/dashboard', fn() => Inertia::render('Dashboard'))->name('dashboard');

    // Pantallas principales
    Route::get('/tablon',          fn() => Inertia::render('Tablon'))->name('tablon.index');
    Route::get('/insumos',         fn() => Inertia::render('Insumos/Index'))->name('insumos.index');
    Route::get('/prestamos',       fn() => Inertia::render('Prestamos/Index'))->name('prestamos.index'); // si aún no existe, créala vacía
    Route::get('/reportes',        fn() => Inertia::render('Reportes'))->name('reportes.index');
    Route::get('/notificaciones',  fn() => Inertia::render('Notificaciones'))->name('notificaciones.index');
    Route::get('/academico',       fn() => Inertia::render('Academico'))->name('academico.index');

    // Perfil (Breeze)
    Route::get('/profile',  [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',[ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile',[ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
