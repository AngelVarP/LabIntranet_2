<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatosMinimosTablonSeeder extends Seeder
{
    public function run(): void
    {
        $required = [
            'users','cursos','secciones','laboratorios','laboratorios_turnos',
            'practicas','grupos','solicitudes','solicitud_estados_historial'
        ];
        foreach ($required as $t) {
            if (!Schema::hasTable($t)) {
                $this->command?->warn("Saltando: falta tabla '{$t}'.");
                return;
            }
        }

        // Si ya hay solicitudes, no hacemos nada
        if (DB::table('solicitudes')->exists()) {
            $this->command?->info('Ya hay solicitudes. NO-OP.');
            return;
        }

        $now = now();

        // Usuario delegado (demo) – intenta con alumno@lab.test, si no toma el primero
        $userId = DB::table('users')->where('email','alumno@lab.test')->value('id')
                  ?? DB::table('users')->min('id');

        // Curso
        DB::table('cursos')->updateOrInsert(
            ['codigo'=>'QUI101'],
            ['nombre'=>'Química I','periodo'=>'2025-I','creado_at'=>$now]
        );
        $cursoId = DB::table('cursos')->where('codigo','QUI101')->value('id');

        // Sección
        DB::table('secciones')->updateOrInsert(
            ['curso_id'=>$cursoId,'nombre'=>'A'],
            []
        );
        $seccionId = DB::table('secciones')
            ->where('curso_id',$cursoId)->where('nombre','A')->value('id');

        // Laboratorio + turno
        DB::table('laboratorios')->updateOrInsert(
            ['codigo'=>'LAB-01'],
            ['nombre'=>'Lab Química 1','aforo'=>30,'ubicacion'=>'Pabellón Q','creado_at'=>$now]
        );
        $labId = DB::table('laboratorios')->where('codigo','LAB-01')->value('id');

        DB::table('laboratorios_turnos')->updateOrInsert(
            ['laboratorio_id'=>$labId,'nombre'=>'Mañana','hora_inicio'=>'08:00:00','hora_fin'=>'10:00:00'],
            []
        );
        $turnoId = DB::table('laboratorios_turnos')
            ->where('laboratorio_id',$labId)->where('nombre','Mañana')->value('id');

        // Práctica
        DB::table('practicas')->updateOrInsert(
            ['seccion_id'=>$seccionId,'titulo'=>'Práctica 1: Titulación'],
            ['descripcion'=>'Demo','fecha'=>today(),'laboratorio_id'=>$labId,'turno_id'=>$turnoId,'habilitada'=>1,'creado_at'=>$now]
        );
        $practicaId = DB::table('practicas')
            ->where('seccion_id',$seccionId)->where('titulo','Práctica 1: Titulación')->value('id');

        // Grupo
        DB::table('grupos')->updateOrInsert(
            ['seccion_id'=>$seccionId,'nombre'=>'A1'],
            ['delegado_usuario_id'=>$userId]
        );
        $grupoId = DB::table('grupos')->where('seccion_id',$seccionId)->where('nombre','A1')->value('id');

        // Solicitud (una fila demo)
        DB::table('solicitudes')->updateOrInsert(
            ['practica_id'=>$practicaId,'grupo_id'=>$grupoId,'estado'=>'PENDIENTE'],
            [
                'laboratorio_id'=>$labId,
                'delegado_id'=>$userId,
                'prioridad'=>'MEDIA',
                'observaciones'=>'Pedido demo',
                'creado_por'=>$userId,
                'creado_at'=>$now,
                'actualizado_at'=>$now,
            ]
        );
        $solId = DB::table('solicitudes')
            ->where('practica_id',$practicaId)->where('grupo_id',$grupoId)->where('estado','PENDIENTE')
            ->value('id');

        // Historial de estado
        DB::table('solicitud_estados_historial')->updateOrInsert(
            ['solicitud_id'=>$solId,'estado'=>'PENDIENTE','usuario_id'=>$userId],
            ['comentario'=>'Creada por seeder','creado_at'=>$now]
        );

        $this->command?->info("Seed demo OK: solicitud #{$solId}");
    }
}
