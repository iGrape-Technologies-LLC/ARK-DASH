<?php

use Illuminate\Database\Seeder;
use App\Models\Status;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $status = new Status();
        $status->name = 'Para armar';
        $status->color = '#7b19ff';
        $status->priority = 1;
        $status->save();

        $status = new Status();
        $status->name = 'Para entregar';
        $status->color = '#a6c81c';
        $status->priority = 2;
        $status->save();

        $status = new Status();
        $status->name = 'En correo';
        $status->color = '#1434cc';
        $status->priority = 3;
        $status->save();

        $status = new Status();
        $status->name = 'Entregado';
        $status->color = '#22af46';
        $status->priority = 4;
        $status->save();
    }
}
