<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCombinationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('combinations', function (Blueprint $table) {
            $table->integer('property_value_id')->unsigned();
            $table->integer('article_property_id')->unsigned();

            $table->primary(['property_value_id', 'article_property_id']);
            $table->foreign('property_value_id')->references('id')
                ->on('property_values');
            $table->foreign('article_property_id')->references('id')
                ->on('article_properties')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('combinations');
    }
}
