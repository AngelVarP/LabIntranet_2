<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1) Crear categorias si no existe
        if (!Schema::hasTable('categorias')) {
            Schema::create('categorias', function (Blueprint $t) {
                $t->id();
                $t->string('nombre', 120);
                $t->string('descripcion', 255)->nullable();
                $t->boolean('activo')->default(true);
                $t->timestamps();
            });
        }

        // 2) Asegurar columnas mínimas en insumos y agregar categoria_id si falta
        if (Schema::hasTable('insumos')) {
            Schema::table('insumos', function (Blueprint $t) {
                if (!Schema::hasColumn('insumos', 'stock')) {
                    $t->decimal('stock', 10, 2)->default(0);
                }
                if (!Schema::hasColumn('insumos', 'minimo')) {
                    $t->decimal('minimo', 10, 2)->default(0);
                }
                if (!Schema::hasColumn('insumos', 'unidad')) {
                    $t->string('unidad', 20)->nullable();
                }
                if (!Schema::hasColumn('insumos', 'categoria_id')) {
                    // sin "after", para no depender del orden de columnas
                    $t->foreignId('categoria_id')->nullable()->constrained('categorias');
                }
            });
        }
    }

    public function down(): void
    {
        // Quita solo la FK de insumos si existe
        if (Schema::hasTable('insumos') && Schema::hasColumn('insumos', 'categoria_id')) {
            Schema::table('insumos', function (Blueprint $t) {
                // nombre del constraint se resuelve con helper en Laravel 12:
                $t->dropConstrainedForeignId('categoria_id');
            });
        }

        if (Schema::hasTable('categorias')) {
            Schema::drop('categorias');
        }
    }
};
