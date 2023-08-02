<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNoticeNoticeCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notice_notice_category', function (Blueprint $table) {
            $table->integer('notice_id')->unsigned();
            $table->integer('notice_category_id')->unsigned();

            $table->primary(['notice_id', 'notice_category_id']);
            $table->foreign('notice_id')->references('id')
                ->on('notices')->onDelete('cascade');
            $table->foreign('notice_category_id')->references('id')
                ->on('notice_categories');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notice_notice_category');
    }
}
