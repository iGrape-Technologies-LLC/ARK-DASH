<?php

use Illuminate\Database\Seeder;
use Modules\Shipping\Database\Seeders\ShippingMethodsTableSeeder;
use Modules\Checkout\Database\Seeders\PaymentMethodsTableSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            // PaymentMethodsTableSeeder::class,
            // AfipTypesSeeder::class,
            PermissionsTableSeeder::class,
            CitiesSeeder::class,
        	// ExampleDataSeeder::class,
        	UsersTableSeeder::class,
            //VehicleBrandsSeeder::class,
            // CurrencySeeder::class,
            //ExampleArticleSeeder::class,
            // ShippingMethodsTableSeeder::class,
            // StatusSeeder::class,
            // SubsidiarySeeder::class,
            // WhatsappSeeder::class
        ]);
    }
}
