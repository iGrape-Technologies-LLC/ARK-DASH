<?php

use Illuminate\Database\Seeder;
use App\Models\Subsidiary;

class SubsidiarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $subsidiary = new Subsidiary();
        $subsidiary->city_id = 1;
        $subsidiary->name = 'Sucursal Patagonia';
        $subsidiary->street = 'Av. San Martín';
        $subsidiary->street_number = '445';
        $subsidiary->floor = '1';
        $subsidiary->apartment = 'b';
        $subsidiary->postal_code = '8400';
        $subsidiary->save();

        $subsidiary = new Subsidiary();
        $subsidiary->city_id = 1;
        $subsidiary->name = 'Sucursal Rosario';
        $subsidiary->street = 'Cordoba';
        $subsidiary->street_number = '2035';                
        $subsidiary->postal_code = '2000';
        $subsidiary->save();

        
    }
}
