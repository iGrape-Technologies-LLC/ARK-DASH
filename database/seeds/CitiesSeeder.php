<?php

use Illuminate\Database\Seeder;
use App\Models\State;
use App\Models\City;
use App\Models\Country;

class CitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $country = Country::create([
            'name' => 'Argentina',
            'code' => 'AR',
        ]);

        $state = new State();
        $state->name = 'Buenos Aires';
        $state->country_id = $country->id;
        $state->save();
        $state = new State();
        $state->name = 'Buenos Aires-GBA';
        $state->country_id = $country->id;
        $state->save();
        $state = new State();
        $state->name = 'Capital Federal';
        $state->country_id = $country->id;
        $state->save();
        $state = new State();
        $state->name = 'Catamarca';
        $state->country_id = $country->id;
        $state->save();
        $state = new State();
        $state->name = 'Chaco';
        $state->country_id = $country->id;
        $state->save();
        $state = new State();
        $state->name = 'Chubut';
        $state->country_id = $country->id;
        $state->save();
        $state = new State();
        $state->name = 'Córdoba';
        $state->country_id = $country->id;
        $state->save();
        $state = new State();
        $state->name = 'Corrientes';
        $state->country_id = $country->id;
        $state->save();
        $state = new State();
        $state->name = 'Entre Ríos';
        $state->country_id = $country->id;
        $state->save();
        $state = new State();
        $state->name = 'Formosa';
        $state->country_id = $country->id;
        $state->save();
        $state = new State();
        $state->name = 'Jujuy';
        $state->country_id = $country->id;
        $state->save();
        $state = new State();
        $state->name = 'La Pampa';
        $state->country_id = $country->id;
        $state->save();
        $state = new State();
        $state->name = 'La Rioja';
        $state->country_id = $country->id;
        $state->save();
        $state = new State();
        $state->name = 'Mendoza';
        $state->country_id = $country->id;
        $state->save();
        $state = new State();
        $state->name = 'Misiones';
        $state->country_id = $country->id;
        $state->save();
        $state = new State();
        $state->name = 'Neuquén';
        $state->country_id = $country->id;
        $state->save();
        $state = new State();
        $state->name = 'Río Negro';
        $state->country_id = $country->id;
        $state->save();
        $state = new State();
        $state->name = 'Salta';
        $state->country_id = $country->id;
        $state->save();
        $state = new State();
        $state->name = 'San Juan';
        $state->country_id = $country->id;
        $state->save();
        $state = new State();
        $state->name = 'San Luis';
        $state->country_id = $country->id;
        $state->save();
        $state = new State();
        $state->name = 'Santa Cruz';
        $state->country_id = $country->id;
        $state->save();
        $state = new State();
        $state->name = 'Santa Fe';
        $state->country_id = $country->id;
        $state->save();
        $state = new State();
        $state->name = 'Santiago del Estero';
        $state->country_id = $country->id;
        $state->save();
        $state = new State();
        $state->name = 'Tierra del Fuego';
        $state->country_id = $country->id;
        $state->save();
        $state = new State();
        $state->name = 'Tucumán';
        $state->country_id = $country->id;
        $state->save();

        $city = new City();
        $city->name = "Bariloche";
        $city->state_id = 17;
        $city->save();
        $city = new City();
        $city->name = "Rosario";
        $city->state_id = 22;
        $city->save();
    }
}
