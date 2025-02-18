<?php

namespace Database\Seeders;

use App\Models\QuienRecibe;
use Illuminate\Database\Seeder;

class RecibeMaterialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        QuienRecibe::create([
            'nombre' => 'Registro Cierre de Proyecto',
        ]);
    }
}
