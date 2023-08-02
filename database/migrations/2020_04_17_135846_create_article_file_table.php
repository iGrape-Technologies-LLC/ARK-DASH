<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticleFileTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('article_file', function (Blueprint $table) {
            $table->integer('article_id')->unsigned();
            $table->integer('file_id')->unsigned();
            
            $table->primary(['article_id', 'file_id']);
            $table->foreign('article_id')->references('id')
                ->on('articles')->onDelete('cascade');
            $table->foreign('file_id')->references('id')
                ->on('files')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('article_file');
    }
}
