<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('city_id')->unsigned();            
            $table->integer('user_id')->unsigned();
            $table->string('name', 255);
            $table->string('street', 255);
            $table->integer('street_number');
            $table->string('floor', 50)->nullable();
            $table->string('apartment', 50)->nullable();
            $table->string('address_extra', 100)->nullable();
            $table->string('postal_code', 100);
            $table->string('doc_number', 255)->nullable();
            //$table->integer('afip_type_id')->unsigned()->nullable();
            //$table->string('name_invoice', 255)->nullable();            
            //$table->string('notes', 255)->nullable();
            $table->timestamps();

            $table->foreign('city_id')->references('id')
                ->on('cities');
            //$table->foreign('afip_type_id')->references('id')
                //->on('afip_types');
            $table->foreign('user_id')->references('id')
                ->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('addresses');
    }
}
