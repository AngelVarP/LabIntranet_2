<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersDemoSeeder extends Seeder {
  public function run(): void {
    $prof = User::firstOrCreate(
      ['email' => 'prof@lab.test'],
      ['name'=>'Profesor Demo','password'=>Hash::make('Prof1234')]
    ); $prof->syncRoles(['profesor']);

    $tec = User::firstOrCreate(
      ['email' => 'tec@lab.test'],
      ['name'=>'Tecnico Demo','password'=>Hash::make('Tec1234')]
    ); $tec->syncRoles(['tecnico']);

    $alu = User::firstOrCreate(
      ['email' => 'alumno@lab.test'],
      ['name'=>'Alumno Demo','password'=>Hash::make('Alu1234')]
    ); $alu->syncRoles(['alumno']);
  }
}
