<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ProfileController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => \Illuminate\Foundation\Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware(['auth','verified'])->group(function () {
    Route::get('/dashboard', fn() => Inertia::render('Dashboard'))->name('dashboard');
    Route::get('/notificaciones', fn() => Inertia::render('Notificaciones'))->name('notificaciones.index');
    Route::get('/academico', fn() => Inertia::render('Academico'))->name('academico.index');
    Route::get('/prestamos', fn() => Inertia::render('Prestamos'))->name('prestamos.index');
    Route::get('/reportes', fn () => Inertia::render('Reportes'))->name('reportes.index');

    Route::get('/tablon',    fn() => Inertia::render('Tablon'))->name('tablon.index');
    Route::get('/insumos',   fn() => Inertia::render('Insumos/Index'))->name('insumos.index');

    Route::get('/profile',  [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',[ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile',[ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::middleware(['auth','verified'])
    ->get('/prestamos', fn() => Inertia::render('Prestamos/Index'))
    ->name('prestamos.index');
    Route::middleware(['auth','verified'])
    ->get('/reportes', fn() => Inertia::render('Reportes/Index'))
    ->name('reportes.index');

});

require __DIR__.'/auth.php';
