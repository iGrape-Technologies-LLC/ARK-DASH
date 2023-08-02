<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type', 255)->default('sell');
            $table->integer('user_id')->unsigned();
            $table->integer('cart_id')->unsigned()->nullable();
            $table->string('status', 100);
            $table->double('total_shipping', 20, 6)->default(0);
            $table->double('total_discounts', 20, 6)->default(0);
            $table->double('total_articles', 20, 6);
            $table->double('total_paid', 20, 6);
            $table->integer('shipping_address_id')->unsigned()->nullable();       
            $table->string('receiver_name', 255)->nullable();
            $table->string('receiver_doc_number', 255)->nullable();

            $table->integer('afip_type_id')->unsigned()->nullable();
            $table->string('name_invoice', 255)->nullable();   
            $table->string('doc_number', 255)->nullable();         
            $table->string('notes', 255)->nullable();
            $table->enum('shipping_type', ['address', 'pick_up_carrier', 'pick_up_store'])->default('address');
            $table->string('shipping_address_extra', 255)->nullable();

            $table->timestamps();

            $table->foreign('user_id')->references('id')
                ->on('users');
            $table->foreign('cart_id')->references('id')
                ->on('carts');
            $table->foreign('afip_type_id')->references('id')
                ->on('afip_types');
            $table->foreign('shipping_address_id')->references('id')
                ->on('addresses');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}
