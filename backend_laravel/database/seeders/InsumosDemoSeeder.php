<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InsumosDemoSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['insumos','categorias_insumo','insumo_lotes','laboratorios'] as $t) {
            if (!Schema::hasTable($t)) {
                $this->command?->warn("Saltando: falta tabla '{$t}'.");
                return;
            }
        }

        // Asegura laboratorio
        DB::table('laboratorios')->updateOrInsert(
            ['codigo'=>'LAB-01'],
            ['nombre'=>'Lab Química 1']
        );
        $labId = DB::table('laboratorios')->where('codigo','LAB-01')->value('id');

        // Asegura categoría
        DB::table('categorias_insumo')->updateOrInsert(['nombre'=>'Vidriería'], []);
        $catId = DB::table('categorias_insumo')->where('nombre','Vidriería')->value('id');

        // Asegura insumo
        DB::table('insumos')->updateOrInsert(
            ['codigo'=>'VP-250'],
            [
                'nombre'=>'Vaso precipitado 250 ml',
                'unidad'=>'u',
                'categoria_id'=>$catId,
                'stock_minimo'=>5,
                'activo'=>1,
            ]
        );
        $insId = DB::table('insumos')->where('codigo','VP-250')->value('id');

        // Lote demo si no hay stock aún
        $hasLote = DB::table('insumo_lotes')->where('insumo_id',$insId)->exists();
        if (!$hasLote) {
            DB::table('insumo_lotes')->insert([
                'insumo_id'=>$insId,
                'laboratorio_id'=>$labId,
                'lote'=>'L-001',
                'caducidad'=>null,
                'cantidad'=>10,
            ]);
        }

        $this->command?->info('InsumosDemoSeeder OK');
    }
}
