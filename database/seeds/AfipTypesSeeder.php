<?php

use Illuminate\Database\Seeder;
use App\Models\AfipType;

class AfipTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        AfipType::create([
        	'name' => 'Responsable Monotibuto'
        ]);

        AfipType::create([
        	'name' => 'IVA Responsable Inscripto'
        ]);

        AfipType::create([
        	'name' => 'IVA Sujeto Excento'
        ]);

        AfipType::create([
        	'name' => 'Consumidor Final'
        ]);

        AfipType::create([
        	'name' => 'IVA No Alcanzado'
        ]);

        AfipType::create([
        	'name' => 'IVA Liberado - Ley N° 19.640'
        ]);
    }
}
