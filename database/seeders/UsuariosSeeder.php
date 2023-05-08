<?php

namespace Database\Seeders;

use App\Models\Usuario;
use Illuminate\Database\Seeder;

class UsuariosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Usuario::create([
            'nombre' => 'Jonathan',
            'usuario' => 'jonathan',
            'password' => bcrypt('1234'),
            'activo' => 1,
        ])->assignRole('admin');

        Usuario::create([
            'nombre' => 'Ramon',
            'usuario' => 'ramon',
            'password' => bcrypt('1234'),
            'activo' => 1,
        ])->assignRole('inventario');
    }
}
