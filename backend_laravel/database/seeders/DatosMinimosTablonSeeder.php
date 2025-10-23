<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatosMinimosTablonSeeder extends Seeder
{
    public function run(): void
    {
        // curso + seccion
        $cursoId = DB::table('cursos')->insertGetId([
            'codigo'=>'QUI101','nombre'=>'Química I','periodo'=>'2025-I'
        ]);
        $secId = DB::table('secciones')->insertGetId([
            'curso_id'=>$cursoId,'nombre'=>'A'
        ]);

        // laboratorio + turno
        $labId = DB::table('laboratorios')->insertGetId([
            'codigo'=>'LAB-Q1','nombre'=>'Lab Química 1','aforo'=>30,'ubicacion'=>'Pabellón A'
        ]);
        $turnoId = DB::table('laboratorios_turnos')->insertGetId([
            'laboratorio_id'=>$labId,'nombre'=>'Mañana','hora_inicio'=>'08:00:00','hora_fin'=>'10:00:00'
        ]);

        // práctica
        $pracId = DB::table('practicas')->insertGetId([
            'seccion_id'=>$secId,'titulo'=>'Práctica 1: Titulación',
            'descripcion'=>'Ácidos y bases','fecha'=>date('Y-m-d'),
            'laboratorio_id'=>$labId,'turno_id'=>$turnoId,'habilitada'=>1
        ]);

        // grupo + delegado (alumno demo)
        $alumnoId = DB::table('users')->where('email','alumno@lab.test')->value('id');
        $grupoId = DB::table('grupos')->insertGetId([
            'seccion_id'=>$secId,'nombre'=>'A1','delegado_usuario_id'=>$alumnoId
        ]);
        DB::table('alumnos_grupo')->insert([
            'grupo_id'=>$grupoId,'alumno_id'=>$alumnoId
        ]);

        // solicitud para que aparezca en el tablón
        DB::table('solicitudes')->insert([
            'practica_id'=>$pracId,
            'laboratorio_id'=>$labId,
            'grupo_id'=>$grupoId,
            'delegado_id'=>$alumnoId,
            'estado'=>'PENDIENTE',
            'prioridad'=>'MEDIA',
            'observaciones'=>'2 buretas y 500 ml NaOH 0.1M',
            'creado_por'=>$alumnoId
        ]);
    }
}
