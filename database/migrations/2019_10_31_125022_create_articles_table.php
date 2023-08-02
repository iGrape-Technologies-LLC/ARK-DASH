<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->increments('id');
            $table->text('sku')->nullable();
            $table->string('title');
            $table->mediumText('description')->nullable();
            $table->double('price', 20, 6)->nullable();
            $table->integer('stock')->nullable();
            $table->integer('visits_count')->default(0);
            $table->integer('currency_id')->unsigned();
            $table->boolean('active')->default(true);
            $table->boolean('featured')->default(false);
            $table->integer('user_id')->unsigned();            
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('user_id')->references('id')
                ->on('users');
            $table->foreign('currency_id')->references('id')
                ->on('currencies');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('articles');
    }
}
