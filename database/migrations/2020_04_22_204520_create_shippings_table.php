<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShippingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shippings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('shipping_method_id')->unsigned();
            $table->integer('transaction_id')->unsigned();
            $table->double('amount', 20, 6);
            $table->string('tracking_code')->nullable();
            $table->string('status', 100)->nullable();
            $table->timestamps();

            $table->foreign('shipping_method_id')->references('id')
                ->on('shipping_methods');
            $table->foreign('transaction_id')->references('id')
                ->on('transactions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shippings');
    }
}
