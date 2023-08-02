<?php

use Illuminate\Database\Seeder;
use App\Models\Whatsapp;

class WhatsappSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $wp = new Whatsapp();
        $wp->name = 'Consultas';
        $wp->phone = '+5493415437987';
        $wp->hour_from = '08:00';
        $wp->hour_to = '18:30';
        $wp->save();

        $wp = new Whatsapp();
        $wp->name = 'Ventas';
        $wp->phone = '+5493415437987';
        $wp->hour_from = '08:00';
        $wp->hour_to = '18:30';
        $wp->save();        
    }
}
