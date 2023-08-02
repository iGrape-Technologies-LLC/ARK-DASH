<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('payment_method_id')->unsigned();
            $table->integer('transaction_id')->unsigned();
            $table->double('amount', 20, 6);
            $table->string('status', 100);
            $table->string('notes', 255)->nullable();
            $table->string('token')->nullable();
            $table->timestamps();

            $table->foreign('payment_method_id')->references('id')
                ->on('payment_methods');
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
        Schema::dropIfExists('payments');
    }
}
