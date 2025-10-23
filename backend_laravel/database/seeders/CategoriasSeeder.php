<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriasSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $base = [
            ['nombre'=>'Vidriería', 'descripcion'=>null, 'activo'=>1, 'created_at'=>$now, 'updated_at'=>$now],
            ['nombre'=>'Reactivos', 'descripcion'=>null, 'activo'=>1, 'created_at'=>$now, 'updated_at'=>$now],
            ['nombre'=>'Equipos',   'descripcion'=>null, 'activo'=>1, 'created_at'=>$now, 'updated_at'=>$now],
        ];
        foreach ($base as $c) {
            $exists = DB::table('categorias')->where('nombre',$c['nombre'])->exists();
            if (!$exists) DB::table('categorias')->insert($c);
        }
    }
}
