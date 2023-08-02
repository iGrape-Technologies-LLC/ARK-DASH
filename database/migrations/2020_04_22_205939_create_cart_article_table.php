<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCartArticleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cart_article_property', function (Blueprint $table) {
            $table->integer('article_property_id')->unsigned();
            $table->integer('cart_id')->unsigned();
            $table->integer('quantity');
            
            $table->primary(['article_property_id', 'cart_id']);
            $table->foreign('article_property_id')->references('id')
                ->on('article_properties')->onDelete('cascade');
            $table->foreign('cart_id')->references('id')
                ->on('carts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cart_article');
    }
}
