<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CategoriasSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasTable('categorias_insumo')) {
            $this->command?->warn("Saltando: falta 'categorias_insumo'.");
            return;
        }

        foreach (['Vidriería','Reactivos','Material menor'] as $n) {
            DB::table('categorias_insumo')->updateOrInsert(['nombre'=>$n], []);
        }

        $this->command?->info('CategoriasSeeder OK');
    }
}
