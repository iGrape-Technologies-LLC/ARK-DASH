<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDiscountArticleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('discount_article', function (Blueprint $table) {
            $table->integer('article_id')->unsigned();
            $table->integer('discount_id')->unsigned();
            
            $table->primary(['article_id', 'discount_id']);
            $table->foreign('article_id')->references('id')
                ->on('articles')->onDelete('cascade');
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
        Schema::dropIfExists('discount_article');
    }
}
