<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatosMinimosTablonSeeder extends Seeder
{
    private function withTimestamps(string $table, array $data): array
    {
        // Solo agrega timestamps si la tabla tiene esas columnas
        if (Schema::hasColumn($table, 'created_at')) {
            $data['created_at'] = now();
        }
        if (Schema::hasColumn($table, 'updated_at')) {
            $data['updated_at'] = now();
        }
        return $data;
    }

    public function run(): void
    {
        // ===== CURSOS =====
        DB::table('cursos')->updateOrInsert(
            ['codigo' => 'QUI101'],
            $this->withTimestamps('cursos', [
                'nombre'  => 'Química I',
                'periodo' => '2025-I',
            ])
        );
        $cursoId = DB::table('cursos')->where('codigo','QUI101')->value('id');

        // ===== SECCIONES =====
        DB::table('secciones')->updateOrInsert(
            ['curso_id' => $cursoId, 'nombre' => 'A'],
            $this->withTimestamps('secciones', [
                'turno' => 'Mañana',
            ])
        );
        $seccionId = DB::table('secciones')
            ->where('curso_id',$cursoId)->where('nombre','A')->value('id');

        // ===== PRACTICAS =====
        DB::table('practicas')->updateOrInsert(
            ['seccion_id' => $seccionId, 'titulo' => 'Práctica 1: Volumetría'],
            $this->withTimestamps('practicas', [
                'laboratorio_id' => 1,
            ])
        );
        $practicaId = DB::table('practicas')
            ->where('seccion_id',$seccionId)->where('titulo','Práctica 1: Volumetría')->value('id');

        // ===== GRUPOS =====
        DB::table('grupos')->updateOrInsert(
            ['seccion_id' => $seccionId, 'nombre' => 'Grupo 1'],
            $this->withTimestamps('grupos', [])
        );
        $grupoId = DB::table('grupos')
            ->where('seccion_id',$seccionId)->where('nombre','Grupo 1')->value('id');

        // ===== INSUMOS (demo) =====
        DB::table('insumos')->updateOrInsert(
            ['codigo' => 'VP-250'],
            $this->withTimestamps('insumos', [
                'nombre' => 'Vaso precipitado 250ml',
                'unidad' => 'u',
                'stock'  => 20,
                'minimo' => 5,
            ])
        );
        $vasoId = DB::table('insumos')->where('codigo','VP-250')->value('id');

        DB::table('insumos')->updateOrInsert(
            ['codigo' => 'HCL-1L'],
            $this->withTimestamps('insumos', [
                'nombre' => 'Ácido clorhídrico 1L',
                'unidad' => 'L',
                'stock'  => 5,
                'minimo' => 1,
            ])
        );
        $hclId = DB::table('insumos')->where('codigo','HCL-1L')->value('id');

        // ===== SOLICITUD e ITEMS =====
        DB::table('solicitudes')->updateOrInsert(
            ['grupo_id'=>$grupoId,'practica_id'=>$practicaId,'estado'=>'PENDIENTE'],
            $this->withTimestamps('solicitudes', [
                'comentario' => 'Pedido inicial demo',
            ])
        );
        $solId = DB::table('solicitudes')
            ->where('grupo_id',$grupoId)->where('practica_id',$practicaId)->where('estado','PENDIENTE')->value('id');

        DB::table('solicitud_items')->updateOrInsert(
            ['solicitud_id'=>$solId,'insumo_id'=>$vasoId],
            $this->withTimestamps('solicitud_items', [
                'cantidad'=>2,'unidad'=>'u',
            ])
        );
        DB::table('solicitud_items')->updateOrInsert(
            ['solicitud_id'=>$solId,'insumo_id'=>$hclId],
            $this->withTimestamps('solicitud_items', [
                'cantidad'=>0.5,'unidad'=>'L',
            ])
        );
    }
}
