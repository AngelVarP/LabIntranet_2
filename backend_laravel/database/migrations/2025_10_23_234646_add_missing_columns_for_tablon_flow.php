<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // SECCIONES: agregar 'turno' si falta
        if (Schema::hasTable('secciones')) {
            Schema::table('secciones', function (Blueprint $t) {
                if (!Schema::hasColumn('secciones', 'turno')) {
                    $t->string('turno', 30)->nullable(); // Mañana/Tarde/Noche
                }
                if (!Schema::hasColumn('secciones', 'created_at')) $t->timestamp('created_at')->nullable();
                if (!Schema::hasColumn('secciones', 'updated_at')) $t->timestamp('updated_at')->nullable();
            });
        }

        // PRACTICAS: agregar 'laboratorio_id' si lo usa el seeder/controlador
        if (Schema::hasTable('practicas')) {
            Schema::table('practicas', function (Blueprint $t) {
                if (!Schema::hasColumn('practicas', 'laboratorio_id')) {
                    $t->foreignId('laboratorio_id')->nullable()->constrained('laboratorios');
                }
                if (!Schema::hasColumn('practicas', 'created_at')) $t->timestamp('created_at')->nullable();
                if (!Schema::hasColumn('practicas', 'updated_at')) $t->timestamp('updated_at')->nullable();
            });
        }

        // CURSOS/GRUPOS: por si no tienen timestamps y el seeder los agrega a veces
        foreach (['cursos','grupos'] as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $t) use ($table) {
                    if (!Schema::hasColumn($table, 'created_at')) $t->timestamp('created_at')->nullable();
                    if (!Schema::hasColumn($table, 'updated_at')) $t->timestamp('updated_at')->nullable();
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('secciones')) {
            Schema::table('secciones', function (Blueprint $t) {
                if (Schema::hasColumn('secciones', 'turno')) $t->dropColumn('turno');
                if (Schema::hasColumn('secciones', 'created_at')) $t->dropColumn('created_at');
                if (Schema::hasColumn('secciones', 'updated_at')) $t->dropColumn('updated_at');
            });
        }

        if (Schema::hasTable('practicas')) {
            Schema::table('practicas', function (Blueprint $t) {
                if (Schema::hasColumn('practicas', 'laboratorio_id')) {
                    $t->dropConstrainedForeignId('laboratorio_id');
                }
                if (Schema::hasColumn('practicas', 'created_at')) $t->dropColumn('created_at');
                if (Schema::hasColumn('practicas', 'updated_at')) $t->dropColumn('updated_at');
            });
        }

        foreach (['cursos','grupos'] as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $t) use ($table) {
                    if (Schema::hasColumn($table, 'created_at')) $t->dropColumn('created_at');
                    if (Schema::hasColumn($table, 'updated_at')) $t->dropColumn('updated_at');
                });
            }
        }
    }
};
