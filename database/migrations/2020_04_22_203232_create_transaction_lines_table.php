<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionLinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction_lines', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('article_property_id')->unsigned();
            $table->integer('transaction_id')->unsigned();
            $table->double('price', 20, 6);
            $table->integer('quantity');
            $table->integer('discount_id')->unsigned()->nullable();
            $table->double('discount_amount', 20, 6)->default(0);
            $table->timestamps();

            $table->foreign('article_property_id')->references('id')
                ->on('article_properties');
            $table->foreign('transaction_id')->references('id')
                ->on('transactions')->onDelete('cascade');
            $table->foreign('discount_id')->references('id')
                ->on('discounts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transaction_lines');
    }
}
