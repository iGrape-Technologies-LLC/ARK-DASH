<?php

use Illuminate\Database\Seeder;
use App\Models\Currency;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $currency = new Currency();
        $currency->code = 'ARS';
        $currency->description = 'Pesos';
        $currency->symbol = '$';
        $currency->save();

        $currency = new Currency();
        $currency->code = 'USD';
        $currency->description = 'Dólares';
        $currency->symbol = 'USD';
        $currency->save();
    }
}
