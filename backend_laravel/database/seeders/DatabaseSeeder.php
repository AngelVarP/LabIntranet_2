<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
            AdminUserSeeder::class,
            UsersDemoSeeder::class,
            DatosMinimosTablonSeeder::class,
            // DatosMinimosTablonSeeder::class // <- lo ejecutas luego si quieres
        ]);
    }
}
